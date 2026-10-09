const assert=require('assert'),fs=require('fs'),vm=require('vm');
let confirmed=false,options;const window={location:{href:'current-page'}};
const context=vm.createContext({window,Number,encodeURIComponent,Feedback:{fire(input){options=input;return Promise.resolve({isConfirmed:confirmed});}}});
vm.runInContext(fs.readFileSync('public/assets/js/upgrade-prompt.js','utf8'),context);
(async()=>{
await window.showUpgradeRequired(7,'yearly');assert.equal(options.showCancelButton,true);assert.equal(options.confirmButtonText,'View plans');assert.equal(window.location.href,'current-page');
confirmed=true;await window.showUpgradeRequired(7,'yearly');assert.equal(window.location.href,'/tenant/?url=admin/upgrade&subscription_id=7&cycle=yearly');
await window.showUpgradeRequired();assert.equal(window.location.href,'/tenant/?url=admin/upgrade');
console.log('PASS upgrade prompt cancellation, confirmation and subscription/billing context');
})().catch(error=>{console.error(error);process.exitCode=1;});
