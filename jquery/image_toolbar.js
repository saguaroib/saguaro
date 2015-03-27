//RePod - Appends a toolbar for image posts with links to resources.
$(document).ready(function() { repod.image_toolbar.init(); });
try { repod; } catch(e) { repod = {}; }
repod.image_toolbar = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.image_toolbar.update);
		repod.infinite_scroll && repod.infinite_scroll.callme.push(repod.image_toolbar.update);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_search_enabled") ? repod_jsuite_getCookie("repod_image_search_enabled") === "true" : true,
			selector: ".postimg"
		}
		repod.suite_settings && repod.suite_settings.info.push({mode:'modern',menu:{category:'Images',read:this.config.enabled,variable:'repod_image_search_enabled',label:'Image search',hover:''}});
		this.update();
	},
	update: function() {
		if (repod.image_toolbar.config.enabled) {
			$(repod.image_toolbar.config.selector).each(function() {
				if ($(this).parent().siblings("span.filesize").find(".searchgoogle").length == 0) {
					$(this).parent().siblings("span.filesize").append(repod.image_toolbar.format($(this)));
				}
			});
		}
	},
	format: function(a) {
		var url = location.href.substring(0, location.href.lastIndexOf("/")) + "/" + $(a).parent().attr('href');
		return " <a class='searchgoogle' href='http://www.google.com/searchbyimage?image_url="+ url +"'><img src='jquery/gis.jpg' alt='[Google]' /></a> <a class='searchiqdb' href='http://iqdb.org/?url="+ url +"'><img src='jquery/iqdb.jpg' alt='[IQDB]' /></a>";
	}
};