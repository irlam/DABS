import assert from 'node:assert/strict';
import {spawn,execFileSync} from 'node:child_process';
import {readFileSync,mkdirSync,rmSync} from 'node:fs';
import {createServer} from 'node:net';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=fileURLToPath(new URL('..',import.meta.url));
const {chromium}=await import(process.env.DABS_PLAYWRIGHT_MODULE||'playwright');
const php=process.env.DABS_TEST_PHP||'php';
const vendors=process.env.DABS_TEST_VENDOR_DIR||path.join(root,'node_modules');
const fixtures=JSON.parse(execFileSync(php,['tests/browser-fixtures.php'],{cwd:root,encoding:'utf8'}));
const processes=[];
const freePort=()=>new Promise(resolve=>{const s=createServer();s.listen(0,'127.0.0.1',()=>{const p=s.address().port;s.close(()=>resolve(p));});});
async function server(site,role='admin') {
  const port=await freePort();
  const child=spawn(php,['-S','127.0.0.1:'+port,'-t',root,'tests/browser-router.php'],{
    cwd:root,env:{...process.env,DABS_UI_FIXTURE:'1',DABS_FIXTURE_DB:fixtures.directory+'/'+site+'.sqlite',DABS_FIXTURE_ROLE:role},stdio:'ignore'});
  processes.push(child);
  const url='http://127.0.0.1:'+port;
  for(let n=0;n<60;n++){try{const r=await fetch(url);if(r.ok)return url;}catch{}await new Promise(r=>setTimeout(r,50));}
  throw Error('Fixture server did not start');
}
let browser;
try {
  const [alpha,beta,viewer]=await Promise.all([server('alpha'),server('beta'),server('alpha','viewer')]);
  browser=await chromium.launch({headless:true,...(process.env.DABS_TEST_CHROMIUM?{executablePath:process.env.DABS_TEST_CHROMIUM,args:['--no-sandbox','--disable-dev-shm-usage','--use-gl=angle','--use-angle=swiftshader','--single-process','--no-zygote']}: {})});
  const context=await browser.newContext({viewport:{width:1440,height:1000},timezoneId:'America/Los_Angeles'});
  const external=[];
  await context.route('**/*',async route=>{
    const u=new URL(route.request().url());
    if(u.hostname==='127.0.0.1')return route.continue();
    external.push(u.origin);
    // Weather is a deterministic fixture; no real API key or email is sent.
    if(u.hostname==='api.openweathermap.org'){
      const weather={weather:[{id:802,description:'TEST partly cloudy',icon:'03d'}],main:{temp:12,feels_like:11,temp_min:10,temp_max:13,humidity:70},wind:{speed:3,deg:180},dt:Math.floor(Date.now()/1000),name:'TEST Manchester',sys:{sunrise:Math.floor(Date.now()/1000)-3600,sunset:Math.floor(Date.now()/1000)+3600}};
      return route.fulfill({json:{...weather,list:Array.from({length:40},(_,i)=>({...weather,dt:weather.dt+i*10800}))}});
    }
    if(u.hostname==='openweathermap.org')return route.fulfill({contentType:'image/svg+xml',body:'<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><circle cx="32" cy="32" r="18" fill="#f5a524"/></svg>'});
    let file,type;
    if(u.hostname==='code.jquery.com'){file='jquery/dist/jquery.min.js';type='application/javascript';}
    else if(u.hostname==='cdn.jsdelivr.net'&&u.pathname.includes('/bootstrap@')){
      file='bootstrap/dist/'+(u.pathname.endsWith('.css')?'css/bootstrap.min.css':'js/bootstrap.bundle.min.js');type=file.endsWith('.css')?'text/css':'application/javascript';
    }else if(u.hostname==='cdnjs.cloudflare.com'&&u.pathname.includes('/select2/')){file='select2/dist/'+(u.pathname.endsWith('.css')?'css/select2.min.css':'js/select2.min.js');type=file.endsWith('.css')?'text/css':'application/javascript';}
    else if(u.hostname==='fonts.googleapis.com'||u.pathname.endsWith('/css/all.min.css'))return route.fulfill({contentType:'text/css',body:'/* Fixture uses system fonts. */'});
    if(file)return route.fulfill({contentType:type,body:readFileSync(path.join(vendors,file))});
    return route.abort();
  });
  const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(alpha);
  assert.equal(await page.evaluate(()=>formatUKDate('2030-04-18')),'18/04/2030','Calendar dates must not shift with browser timezone');
  try {await page.waitForFunction(()=>window.tinymce?.get('safetyEditor')?.initialized&&window.tinymce?.get('notesEditor')?.initialized);}
  catch(e){throw Error('Editor setup failed: '+JSON.stringify(errors)+'; '+(await page.locator('body').innerText()).slice(0,1200));}
  await page.getByRole('navigation',{name:'Construction Suite'}).waitFor();
  assert(await page.getByRole('link',{name:'← Back to Suite'}).count());
  assert((await page.locator('#projectName').innerText()).includes('TEST Riverside Training Project'));
  assert(!(await page.locator('body').innerText()).includes('TEST Hilltop Training Project'));
  await page.locator('#userMenu').click();assert(await page.locator('.dropdown-menu.show').count());await page.locator('#userMenu').click();
  assert.equal(await page.locator('#activityDate').count(),1,'Date picker IDs must be unique');
  await page.locator('#addActivityBtn').click();await page.locator('#activityModal').waitFor({state:'visible'});
  assert(await page.locator('#activityContractors.select2-hidden-accessible').count());
  await page.locator('#activityModal .btn-close').click();await page.locator('#activityModal').waitFor({state:'hidden'});
  // A future activity must belong to that day's briefing, including after editing.
  const selectedDay='2030-04-18';
  await page.locator('#activityDate').fill(selectedDay);
  await page.locator('#activityDate').dispatchEvent('change');
  await page.locator('#addActivityBtn').click();
  await page.locator('#activityModal').waitFor({state:'visible'});
  assert.equal(await page.locator('#activityBriefingId').inputValue(),'0');
  await page.locator('#activityTitle').fill('TEST future report assignment');
  const createdResponse=page.waitForResponse(r=>r.url().includes('/ajax_activities.php')&&r.request().method()==='POST');
  await page.locator('#activityForm button[type="submit"]').click();
  const created=await (await createdResponse).json();assert.equal(created.ok,true);
  const activityResult=await page.evaluate(async({id,date})=>{
    const read=await (await fetch('ajax_activities.php?action=get&id='+id)).json();
    const csrf=document.querySelector('meta[name="dabs-csrf"]').content;
    const post=async values=>{const r=await fetch('ajax_activities.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-DABS-CSRF':csrf},body:new URLSearchParams(values)});return {status:r.status,body:await r.json()};};
    const mismatch=await post({action:'add',briefing_id:'1',date,title:'TEST mismatched briefing'});
    const edited=await post({action:'update',id,briefing_id:read.activity.briefing_id,date,title:'TEST future edited'});
    const invalidDate=await post({action:'add',briefing_id:'0',date:'2030-02-31',title:'TEST invalid date'});
    return {read,mismatch,edited,invalidDate};
  },{id:created.id,date:selectedDay});
  assert.notEqual(Number(activityResult.read.activity.briefing_id),1);
  assert.equal(activityResult.read.activity.date,selectedDay);
  assert.equal(activityResult.mismatch.status,422);
  assert.equal(activityResult.mismatch.body.ok,false);
  assert.equal(activityResult.edited.body.ok,true);
  assert.equal(activityResult.invalidDate.status,422);
  // Exercise the separate schedule's project context and keyboard target too.
  const schedule=await context.newPage();await schedule.goto(alpha+'/activity_schedule_tabs.php');
  assert((await schedule.locator('.page-header').innerText()).includes('TEST Riverside Training Project'));
  assert.equal(await schedule.locator('#dabs-workspace[tabindex="-1"]').count(),1);
  await schedule.close();
  await page.reload();
  await page.locator('#editSafetyBtn').click();
  await page.evaluate(()=>tinymce.get('safetyEditor').setContent('<p>TEST Alpha briefing saved in browser.</p>'));
  const saved=page.waitForResponse(r=>r.url().includes('/ajax_safety.php')&&r.request().method()==='POST');
  await page.locator('#saveSafetyBtn').click();assert.equal((await (await saved).json()).ok,true);
  await page.reload();assert((await page.locator('#safetyContent').innerText()).includes('TEST Alpha briefing saved in browser.'));
  const other=await context.newPage();await other.goto(beta);
  assert((await other.locator('#projectName').innerText()).includes('TEST Hilltop Training Project'));
  assert(!(await other.locator('#safetyContent').innerText()).includes('TEST Alpha briefing saved in browser.'));
  const readonly=await context.newPage();await readonly.goto(viewer);
  const denied=await readonly.evaluate(async()=>{
    const csrf=document.querySelector('meta[name="dabs-csrf"]').content;
    const r=await fetch('ajax_safety.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-DABS-CSRF':csrf},body:'action=update&safety=FORBIDDEN'});
    return r.status;
  });assert.equal(denied,403);
  mkdirSync(path.join(root,'test-output'),{recursive:true});
  await page.screenshot({path:path.join(root,'test-output/dabs-desktop.jpg'),fullPage:true});
  await page.setViewportSize({width:390,height:844});
  await page.screenshot({path:path.join(root,'test-output/dabs-mobile.jpg'),fullPage:true});
  const overflow=await page.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth,nodes:[...document.querySelectorAll('body *')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&r.right>innerWidth+1&&r.left>=0;}).slice(0,12).map(e=>({tag:e.tagName,id:e.id,classes:e.className,right:e.getBoundingClientRect().right}))}));
  assert(overflow.scroll<=overflow.width,'390px viewport must not overflow: '+JSON.stringify(overflow));
  assert.deepEqual(errors,[],'Actual dashboard scripts must not throw');
  assert(!external.includes('https://cdn.tiny.cloud'));
  console.log(JSON.stringify({ok:true,fixture_databases:2,binding_checks:fixtures.binding_checks,local_editor:true,bootstrap_dropdown:true,safety_save_reload:true,separate_database_data:true,viewer_write_status:denied,mobile_width:390,page_errors:errors,production_isolation_verified:false}));
}finally{
  if(browser)await browser.close();
  for(const p of processes)p.kill();
  rmSync(fixtures.directory,{recursive:true,force:true});
}
