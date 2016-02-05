//Hiding threads. h-here I g-go...

$(document).ready(function() {
    repod.thread_hiding.init();
});
try {
    repod;
} catch (e) {
    repod = {};
}

repod.thread_hiding = {
	init: function() {
		
		repod.suite_settings && repod.suite_settings.info.push({
			mode: 'modern', //wut
			menu: {
				category: 'Filtering and Hiding',
				variable: "repod_thread_hiding_enabled",
				label: 'Thread hiding',
				hover: 'Replace >> with clickable buttons to hide posts'
			}
		});
		
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_hiding_enabled") ? repod_jsuite_getCookie("repod_thread_hiding_enabled") === "true" : true,
			selector: "sideArrows"
		}
		
	}
	
}