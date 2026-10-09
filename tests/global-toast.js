const assert=require('assert'),fs=require('fs'),vm=require('vm');
const timers=[],calls=[],nodes=[];let stack=null,loading=false,closed=0;
const document={querySelector(){return null;},getElementById(){return stack;},body:{appendChild(el){stack=el;}},createElement(){return {classList:{add(){}},setAttribute(k,v){this[k]=v;},appendChild(el){nodes.push(el);},addEventListener(){},querySelector(){return null;},remove(){}};}};
const sentinel=Promise.resolve({isConfirmed:false});
const window={Swal:{fire(...args){calls.push(args);return sentinel;},isLoading(){return loading;},close(){closed++;loading=false;}}};
const context=vm.createContext({window,document,Promise,navigator:{},requestAnimationFrame(fn){fn();},setTimeout(fn,ms){timers.push({fn,ms});return timers.length;},clearTimeout(){}});
const source=fs.readFileSync('public/assets/js/toast.js','utf8');vm.runInContext(source,context);context.ToastStack=window.ToastStack;
(async()=>{
window.ToastStack.success('<img src=x onerror=bad()>','<script>bad()</script>');
assert(nodes[0].innerHTML.includes('&lt;img'));assert(!nodes[0].innerHTML.includes('<script>'));assert.equal(nodes[0].role,'status');
window.ToastStack.error('Denied');assert.equal(nodes[1].role,'alert');
const engine=window.ToastStack;vm.runInContext(source,context);assert.strictEqual(window.ToastStack,engine);
assert.strictEqual(window.Feedback.fire({icon:'warning',showCancelButton:true}),sentinel);assert.equal(calls.length,1);assert.equal(nodes.length,2);
assert.strictEqual(window.Feedback.fire({input:'text'}),sentinel);assert.strictEqual(window.Feedback.fire({didOpen(){}}),sentinel);
loading=true;let resolved=false;const result=window.Feedback.fire({icon:'success',title:'Saved',text:'Done',timer:1500}).then(()=>resolved=true);
assert.equal(closed,1);assert.equal(nodes.length,3);assert.equal(resolved,false);const delay=timers[timers.length-1];assert.equal(delay.ms,1500);delay.fn();await result;assert.equal(resolved,true);
window.Feedback.fire('Denied','Permission required','error');assert.equal(nodes[3].role,'alert');assert.equal(calls.length,3);
console.log('PASS global toast escaping, shared initialization, confirmation preservation, loading cleanup and delayed callbacks');
})().catch(error=>{console.error(error);process.exitCode=1;});
