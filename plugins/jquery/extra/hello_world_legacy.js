/*
The MIT License (MIT)
Copyright (c) 2013-2014 RePod
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
ADDITIONALLY, THIS FILE WAS ORIGINALLY CREATED FOR THE SAGUARO IMAGEBOARD SOFTWARE.
THE ORIGINAL SOFTWARE MAY BE FOUND AT: https://github.com/spootTheLousy/saguaro
Allows user to set a custom list of boards to be displayed at the top/bottom board listings.
*/

/*
This Hello World example file is for scripts that do not use object literals/equivalents. This defines variables in the global namespace
If you plan on or are currently working on a script using object literals refer to the appropriate Hellow World example file for differences.

Everything in this file is an example and is not meant to enforce any standards. Settings/thread-updater compliance is not required.
More info: the readme. Local testing/debugging without server: Scratchpad, browser console.
*/

//Should be globally unique variables. We have these globally defined to be accessed elsewhere in the script and by other scripts.
var hello_world_enabled; 
var hello_world_multitext_enabled;
var hello_world_textarea_enabled;
var hello_world_function_enabled;
$(document).ready(function() {
	//If you want your script to run after the thread updater updates the page you must push the function to it.
	//Checking the typeof the destination to ensure it exists, else we'll crash.
	//typeof repod_thread_updater_calls == "object" && repod_thread_updater_calls.push(unique_function_name);
	
	//If you want (portions of) your script to be toggleable you must push settings information to the relevant settings area.
	//Check if the destination exists. I'm lazy, but assume if suite_settings is available in my namespace, so is info. Excellent role model.
	//For more information on the arrays being pushed or the popup types refer to the suite settings documentation.
	if (repod.suite_settings) { 
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_enabled',label:'Text example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'text',title:'Hello World',variable:'hello_world_demo',placeholder:'Fill out, enable this option, save.'}});
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_multitext_enabled',label:'Multitext example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'multitext',title:'Hello World',variable:{prefix:'hello_world_multitext_example_',data:['Enter something','unique in','each box']}}});
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_textarea_enabled',label:'Textarea example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'textarea',title:'Hello World',variable:'hello_world_textarea_example',placeholder:'Enter a good chunk of text here. Maybe multiple lines?'}});
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_info_enabled',label:'Info example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'info',title:'Hello World',variable:'<center><strong>Sup.</strong><br />Info popups don\'t accept input, they show text and HTML formatting instead!</center>'}});
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_function_enabled',label:'Function example',hover:'It\'s a secret'},popup:{label:'[Enable and click]',type:'function',title:'Hello World',variable:'unique_function_name'}});
		repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_function2_enabled',label:'Another function example',hover:'It\'s a secret'},popup:{label:'[Reset!]',type:'function',title:'Hello World',variable:'hello_world_clear'}});
	}
	
	//Attempt to read the cookies of the variables and determine their values. If they cannot be read (or don't exist), use a default value.
	//Checking the typeof "repod_jsuite_getCookie" to ensure it exists, else we'll crash.
	//Assign the true/false boolean to the global variables defined earlier. Alternatively, define global variables here or where desired.
	hello_world_enabled = repod.suite_settings && !!repod_jsuite_getCookie("hello_world_enabled") ? repod_jsuite_getCookie("hello_world_enabled") === "true" : false;
	hello_world_multitext_enabled = repod.suite_settings && !!repod_jsuite_getCookie("hello_world_multitext_enabled") ? repod_jsuite_getCookie("hello_world_multitext_enabled") === "true" : false;
	hello_world_textarea_enabled = repod.suite_settings && !!repod_jsuite_getCookie("hello_world_textarea_enabled") ? repod_jsuite_getCookie("hello_world_textarea_enabled") === "true" : false;
	hello_world_function_enabled = repod.suite_settings && !!repod_jsuite_getCookie("hello_world_function_enabled") ? repod_jsuite_getCookie("hello_world_function_enabled") === "true" : false;
	
	//Validate which settings were enabled and respond acordingly.
	hello_world_enabled && alert(repod_jsuite_getCookie("hello_world_demo")); //If the text type example was enabled, alert the input.
	//If the multitext type example was enabled, alert each input.
	if (hello_world_multitext_enabled) { for (i=0;i<repod_jsuite_getCookie("hello_world_multitext_example_amount");i++) {	alert("multitext box "+i+": "+repod_jsuite_getCookie("hello_world_multitext_example_"+i)); } }
	hello_world_textarea_enabled && alert(repod_jsuite_getCookie("hello_world_textarea_example")); //If the textarea type example was enabled, alert the input.
	
	//Depeding on when the info popup is pushed it can display dynamic information staticly. 
	repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Hello World',variable:'hello_world_info2_enabled',label:'Another info example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'info',title:'Hello World',variable:'<center><strong>Is the text example enabled?</strong><br />'+hello_world_enabled+'<br /><strong>First multitext input?</strong><br />'+(!!repod_jsuite_getCookie("hello_world_multitext_example_0")?"\'"+repod_jsuite_getCookie("hello_world_multitext_example_0")+"\'":"There is none!")+'</center>'}});
});

function unique_function_name() {
	//Only execute if it has actually been enabled.
	if (hello_world_function_enabled) { alert('This alert is from the Hello World Legacy example file, called by the function command! Hopefully.'); }
}

function hello_world_clear() {
	if (typeof repod_jsuite_setCookie == "function") {
		repod_jsuite_setCookie("hello_world_enabled","",-1);
		repod_jsuite_setCookie("hello_world_multitext_enabled","",-1);
		repod_jsuite_setCookie("hello_world_textarea_enabled","",-1);
		repod_jsuite_setCookie("hello_world_info_enabled","",-1);
		repod_jsuite_setCookie("hello_world_function_enabled","",-1);
		for (i=0;i<repod_jsuite_getCookie("hello_world_multitext_example_"+"amount");i++) {	repod_jsuite_setCookie("hello_world_multitext_example_"+i,"",-1); }
		repod_jsuite_setCookie("hello_world_multitext_example_"+"amount","",-1);
		alert("All hello world-related cookies removed! Will reload.");
		location.reload();
	}
}