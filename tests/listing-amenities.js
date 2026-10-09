const assert=require('assert'),fs=require('fs'),vm=require('vm');
const elements={};const document={getElementById(id){return elements[id] || (elements[id]={value:'',innerHTML:'',classList:{add(){},remove(){}}});}};
let payload,shown=0;const context=vm.createContext({document,Set,String,fetch(){return Promise.resolve({json(){return Promise.resolve(payload);}});},bootstrap:{Modal:class {show(){shown++;}}},Feedback:{fire(){throw new Error('Unexpected error notification');}}});
const source=fs.readFileSync('app/views/components/listing_controls.php','utf8');vm.runInContext(source.match(/function openAmenitiesModal[\s\S]*?(?=function saveAmenities)/)[0],context);
async function open(selected,catalog){payload={success:true,all:catalog,selected_ids:selected};context.openAmenitiesModal(1,'Fixture property');await new Promise(setImmediate);return elements.amenitiesGrid.innerHTML;}
function checked(html,id){return new RegExp('id="am_'+id+'" checked').test(html);}
(async()=>{
const catalog=[{id:'5',name:'Wi-Fi',icon:'wifi'},{id:'8',name:'Parking',icon:'parking'}];
let html=await open([5],catalog);assert(checked(html,5));assert(!checked(html,8));
html=await open(['8'],catalog.map(item=>({...item,id:Number(item.id)})));assert(!checked(html,5));assert(checked(html,8));
html=await open(['5','8'],catalog);assert(checked(html,5));assert(checked(html,8));
html=await open([],catalog);assert(!checked(html,5));assert(!checked(html,8));assert.equal(shown,4);
console.log('PASS amenity modal restores mixed-type saved IDs and refreshes checked/unchecked selections on reopen');
})().catch(error=>{console.error(error);process.exitCode=1;});
