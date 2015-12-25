//RePod - Allows user to inject custom CSS rules.
$(document).ready(function() { repod.custom_css.init(); });
try { repod; } catch(a) { repod = {}; }
repod.custom_css = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("custom_css_enabled") ? repod_jsuite_getCookie("custom_css_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'custom_css_enabled',label:'Custom CSS',hover:'Include your own CSS rules'},popup:{label:'[Edit]',title:'Custom CSS',type:'textarea',variable:'custom_css_defined',placeholder:'Input custom CSS here.'}});
		this.update();
	},
	update: function() {
		if (repod.custom_css.config.enabled) {
			$("<style type='text/css'>"+repod_jsuite_getCookie("custom_css_defined")+"</style>").appendTo("head");
		}
	}
}