/*
This Hello World example file is for scripts that use object literals/equivalents. This defines properties in the relevant namespace.
If you do not plan on or are currently working on a script not using object literals refer to the appropriate Hellow World example file for differences.

Everything in this file is an example and is not meant to enforce any standards. Settings/thread-updater compliance is not required.
More info: the readme. Local testing/debugging without server: Scratchpad, browser console.
*/

$(document).ready(function() {
	repod.hello_world.init();
});

try { repod; } catch(a) { repod = {}; } //If our desired namespace doesn't exist, create it or we'll crash.
repod.hello_world = {
	init: function() {
		//Define some basic config variables here for use later, accessible at repod.hello_world.config.
		//The key names don't have to match the variables, but they should be accessing the same 
		this.config = {
			hello_world_o_enabled: repod.suite_settings && !!repod_jsuite_getCookie("hello_world_o_enabled") ? repod_jsuite_getCookie("hello_world_o_enabled") === "true" : false,
			hello_world_o_multitext_enabled: repod.suite_settings && !!repod_jsuite_getCookie("hello_world_o_multitext_enabled") ? repod_jsuite_getCookie("hello_world_o_multitext_enabled") === "true" : false,
			hello_world_o_textarea_enabled: repod.suite_settings && !!repod_jsuite_getCookie("hello_world_o_textarea_enabled") ? repod_jsuite_getCookie("hello_world_o_textarea_enabled") === "true" : false,
			hello_world_o_function_enabled: repod.suite_settings && !!repod_jsuite_getCookie("hello_world_o_function_enabled") ? repod_jsuite_getCookie("hello_world_o_function_enabled") === "true" : false
		}
		//Due to not using eval() in settings the compromise made was introducing a "read" key to the "menu" key that contains the enabled status.
		//This results in having to push the settings information later.
		if (repod.suite_settings) { 
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_enabled',label:'Text example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'text',title:'Hello World',variable:'hello_world_o_demo',placeholder:'Fill out, enable this option, save.'}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_multitext_enabled',label:'Multitext example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'multitext',title:'Hello World',variable:{prefix:'hello_world_o_multitext_example_',data:['Enter something','unique in','each box']}}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_textarea_enabled',label:'Textarea example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'textarea',title:'Hello World',variable:'hello_world_o_textarea_example',placeholder:'Enter a good chunk of text here. Maybe multiple lines?'}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_info_enabled',label:'Info example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'info',title:'Hello World',variable:'<center><strong>Sup.</strong><br />Info popups don\'t accept input, they show text and HTML formatting instead!</center>'}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_function_enabled',label:'Function example',hover:'It\'s a secret'},popup:{label:'[Enable and click]',type:'function',title:'Hello World',variable:'repod.hello_world.unique_function'}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_function2_enabled',label:'Another function example',hover:'It\'s a secret'},popup:{label:'[Reset!]',type:'function',title:'Hello World',variable:'hello_world_o_clear'}});
			repod.suite_settings.info.push({menu:{category:'Hello World (Objects)',variable:'hello_world_o_info2_enabled',label:'Another info example',hover:'It\'s a secret'},popup:{label:'[Edit]',type:'info',title:'Hello World',variable:'<center><strong>Is the text example enabled?</strong><br />'+this.config.hello_world_o_enabled+'<br /><strong>First multitext input?</strong><br />'+(!!repod_jsuite_getCookie("hello_world_o_multitext_example_0")?"\'"+repod_jsuite_getCookie("hello_world_o_multitext_example_0")+"\'":"There is none!")+'</center>'}});
		}
		this.post_init(); //Proceed.
	},
	post_init: function() {
		//Validate which settings were enabled and respond acordingly.
		this.config.hello_world_o_enabled && alert(repod_jsuite_getCookie("hello_world_o_demo")); //If the text type example was enabled, alert the input.
		//If the multitext type example was enabled, alert each input.
		if (this.config.hello_world_o_multitext_enabled) { for (i=0;i<repod_jsuite_getCookie("hello_world_o_multitext_example_amount");i++) { alert("multitext box "+i+": "+repod_jsuite_getCookie("hello_world_o_multitext_example_"+i)); } }
		this.config.hello_world_o_textarea_enabled && alert(repod_jsuite_getCookie("hello_world_o_textarea_example")); //If the textarea type example was enabled, alert the input.
	},
	unique_function: function() {
		//Only execute if it has actually been enabled.
		if (repod.hello_world.config.hello_world_o_function_enabled) alert('This alert is from the Hello World Objects example file, called by the function command! Hopefully.');
	},
	reset: function() {
		if (typeof repod_jsuite_setCookie == "function") {
			repod_jsuite_setCookie("hello_world_o_enabled","",-1);
			repod_jsuite_setCookie("hello_world_o_multitext_enabled","",-1);
			repod_jsuite_setCookie("hello_world_o_textarea_enabled","",-1);
			repod_jsuite_setCookie("hello_world_o_info_enabled","",-1);
			repod_jsuite_setCookie("hello_world_o_function_enabled","",-1);
			for (i=0;i<repod_jsuite_getCookie("hello_world_o_multitext_example_"+"amount");i++) {	repod_jsuite_setCookie("hello_world_o_multitext_example_"+i,"",-1); }
			repod_jsuite_setCookie("hello_world_o_multitext_example_"+"amount","",-1);
			alert("All hello world-related cookies removed! Will reload.");
			location.reload();
		}
	}
};