import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const sent = [];
const token = 'a'.repeat(64);
class Xhr {
    headers = {};
    open() {}
    send() {}
    setRequestHeader(name, value) { this.headers[name] = value; }
}
const window = { fetch: (input, init) => { sent.push({ input, init }); } };
const context = {
    window, XMLHttpRequest: Xhr, Request, Headers, URL, WeakMap,
    location: { href: 'https://dabs.example/index.php', origin: 'https://dabs.example' },
    document: { querySelector: () => ({ content: token }), addEventListener() {} },
};
vm.runInNewContext(readFileSync(new URL('../js/request-security.js', import.meta.url), 'utf8'), context);
window.fetch('ajax_notes.php', { method: 'POST', headers: { Accept: 'application/json' } });
assert.equal(sent.at(-1).init.headers.get('X-DABS-CSRF'), token);
assert.equal(sent.at(-1).init.headers.get('Accept'), 'application/json');
window.fetch('https://external.example/', { method: 'POST' });
assert.equal(sent.at(-1).init.headers, undefined);
window.fetch('ajax_notes.php');
assert.equal(sent.at(-1).init.headers, undefined);
window.fetch(new Request('https://dabs.example/ajax_notes.php', { method: 'POST', headers: { Accept: 'application/json' } }));
assert.equal(sent.at(-1).init.headers.get('X-DABS-CSRF'), token);
const xhr = new Xhr();
xhr.open('POST', 'ajax_notes.php'); xhr.send();
assert.equal(xhr.headers['X-DABS-CSRF'], token);
const external = new Xhr();
external.open('POST', 'https://external.example/'); external.send();
assert.equal(external.headers['X-DABS-CSRF'], undefined);
// Reusing an XHR must not keep the same-origin flag.
xhr.headers = {}; xhr.open('POST', 'https://external.example/'); xhr.send();
assert.equal(xhr.headers['X-DABS-CSRF'], undefined);
console.log('CSRF transport checks passed.');
