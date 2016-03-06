//RePod - Forces long posts to wrap at 75% of total page width instead of 100%.
$(document).ready(function() { repod.post_wrap.init(); });
try { repod; } catch(e) { repod = {}; }
repod.post_wrap = { 
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.post_wrap.update);
		repod.infinite_scroll && repod.infinite_scroll.callme.push(repod.post_wrap.update);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_force_post_wrap") ? repod_jsuite_getCookie("repod_force_post_wrap") === "true" : true,
			selector: ".reply"
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'repod_force_post_wrap',label:'Force long posts to wrap',hover:'Long posts will wrap at 75\% screen width'}});
		this.update();
	},
	update: function() {
		if (repod.post_wrap.config.enabled) {
			$(repod.post_wrap.config.selector).parent().parent().parent().css("max-width","75%");
		}
	}
};
