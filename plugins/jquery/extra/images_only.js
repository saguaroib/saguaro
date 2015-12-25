//RePod - Hide non-images posts when browsing.
$(document).ready(function() { repod.hide_images.init(); });
try { repod; } catch(a) { repod = {}; }
repod.hide_images = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("r_jq_hide_images") ? repod_jsuite_getCookie("r_jq_hide_images") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Images',read:this.config.enabled,variable:'r_jq_hide_images',label:'Hide non-image posts',hover:'Exluding OP, hide posts that do not contain images'}});
		repod.thread_updater && repod.thread_updater.callme.push(repod.hide_images.update);
		this.update();
	},
	update: function() {
		repod.hide_images.config.enabled && $("div.postContainer.replyContainer:not(:has(img))").css("display","none");
	}
};