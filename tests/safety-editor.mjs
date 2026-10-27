import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';
// Exercise the actual editor script with its external editor unavailable.
const nodes=new Map();
const node=id=>{
  if(!nodes.has(id)) nodes.set(id,{style:{},value:'',innerHTML:'',handlers:{},focus(){},addEventListener(k,fn){this.handlers[k]=fn;}});
  return nodes.get(id);
};
node('safetyContent').innerHTML='<p>TEST Company A — keep the access route clear.</p>';
let start,success=false;const requests=[];
vm.runInNewContext(readFileSync(new URL('../js/safety.js',import.meta.url),'utf8'),{
  document:{addEventListener:(name,fn)=>{start=fn;},getElementById:node},
  fetch:async(url,init)=>{requests.push({url,init});return {ok:success,status:success?200:503,json:async()=>({ok:true})};},
  confirm:()=>true,alert:()=>{},
});
start();
node('editSafetyBtn').handlers.click();
assert.equal(node('safetyEditor').value,node('safetyContent').innerHTML);
node('safetyEditor').value='<p>TEST revised briefing.</p>';
node('safetyEditor').handlers.input();
node('saveSafetyBtn').handlers.click();
await new Promise(r=>setImmediate(r));
assert.equal(node('safetyEditContainer').style.display,'block','Failed save must keep the editor open');
assert.equal(node('safetyEditor').value,'<p>TEST revised briefing.</p>');
assert.equal(requests[0].url,'ajax_safety.php');
assert.equal(requests[0].init.method,'POST');
assert(requests[0].init.body.includes(encodeURIComponent('<p>TEST revised briefing.</p>')));
success=true;
node('saveSafetyBtn').handlers.click();
await new Promise(r=>setImmediate(r));
assert.equal(node('safetyContent').innerHTML,'<p>TEST revised briefing.</p>');
assert.equal(node('safetyEditContainer').style.display,'none');
node('editSafetyBtn').handlers.click();
node('cancelSafetyBtn').handlers.click();
assert.equal(node('safetyEditContainer').style.display,'none');
console.log('Safety editor: fallback, POST, failed-save retention, successful save and cancel pass.');
