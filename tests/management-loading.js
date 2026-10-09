const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const handlers = {};
const attributes = {};
const images = [{}];
let overlay;
let mode = 'success';
const region = {appendChild(node) { overlay = node; }, setAttribute(name, value) { attributes[name] = value; }};
const response = {clone() { return {arrayBuffer() { return Promise.resolve(new ArrayBuffer(0)); }}; }};
const document = {
  body: {}, nodeType:9,
  querySelector() { return region; }, querySelectorAll() { return images; },
  createElement() { return {setAttribute() {}}; },
  addEventListener(name, fn) { handlers[name] = fn; }
};
const window = {
  jQuery() { return {ajaxSend(fn) { handlers.send = fn; }, ajaxComplete(fn) { handlers.complete = fn; }}; },
  fetch() { if (mode === 'throw') throw new Error('sync'); if (mode === 'reject') return Promise.reject(new Error('network')); return Promise.resolve(response); }
};
const context = {window, jQuery:window.jQuery, document, location:{href:'http://localhost/tenant/',origin:'http://localhost'}, URL, MutationObserver:class {observe() {}}, Array};
vm.runInNewContext(fs.readFileSync(__dirname + '/../public/assets/js/management-loading.js', 'utf8'), context);
handlers.DOMContentLoaded();
assert.strictEqual(images[0].loading, 'lazy');
const first = {}, second = {};
handlers.send(null, first, {url:'/api/one'}); handlers.send(null, second, {url:'/api/two'});
assert.strictEqual(overlay.hidden, false);
handlers.complete(null, first); assert.strictEqual(attributes['aria-busy'], 'true');
handlers.complete(null, second); assert.strictEqual(overlay.hidden, true);
const external = {};
handlers.send(null, external, {url:'https://example.test/api'}); handlers.complete(null, external);
assert.strictEqual(overlay.hidden, true);
(async function () {
  assert.strictEqual(await window.fetch('/api/data'), response);
  assert.strictEqual(attributes['aria-busy'], 'false');
  mode = 'reject'; await assert.rejects(window.fetch('/api/error'));
  assert.strictEqual(overlay.hidden, true);
  mode = 'throw'; assert.throws(() => window.fetch('/api/error'));
  assert.strictEqual(attributes['aria-busy'], 'false');
  console.log('PASS shared skeleton concurrency, cleanup, fetch failures and lazy images');
}()).catch(error => { console.error(error); process.exit(1); });
