//RePod - Expands images with their source inside their parent element up to certain dimensions.
$(document).ready(function() { repod.image_expansion.init(); });
try { repod; } catch(a) { repod = {}; }
repod.image_expansion = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_expansion_enabled") ? repod_jsuite_getCookie("repod_image_expansion_enabled") === "true" : true,
			selector: ".postimg"
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Images',read:this.config.enabled,variable:'repod_image_expansion_enabled',label:'Image expansion',hover:'Enable inline image expansion, limited to browser width'}});
		this.update();
	},
	update: function() {
		this.config.enabled && $(document).on("click", this.config.selector, function(event) { repod.image_expansion.check_image(event,$(this)) });
	},
	check_image: function(event,e) {
		event.preventDefault();
        if (/\.webm$/.test($(e).parent().attr("href"))) {
            $(e).data("o-s") ? this.shrink_video(e) : this.expand_video(e);
        } else {
            $(e).data("o-s") ? this.shrink_image(e) : this.expand_image(e);
        }
		$("#img_hover_element").remove();
	},
	expand_image: function(e) {
		$(e).data({"o-h":$(e).css("height"),"o-w":$(e).css("width"),"o-s":$(e).attr("src")}).css({"max-width":(Math.round($("body").width() - ($(e).parent().parent().offset().left * 2))),"width":"auto","height":"auto"});
		var mp = $(e).parent().attr("href"); mp !== $(e).attr("src") && $(e).attr("src",mp);
	},
	shrink_image: function(e) {
		$(e).attr("src",$(this).data("o-s"));
		$(e).css({"max-height":"","max-width":"","width":$(e).data("o-w")}).attr("src",$(e).data("o-s")).removeData();
	},
    expand_video: function(e) {
        $(e).data({"o-s": true, "name": $(e).attr("src").split("/").pop().split(".")[0]}).hide();
        $(e).parent().after("<video class='expandedwebm-" + $(e).data("name") +"' loop autoplay controls src='" + $(e).parent().attr("href") + "'></video>");
    },
    shrink_video: function(e) {
        $(e).data({"o-s": false}).show();
        $(".expandedwebm-" + $(e).data("name")).remove();
    }
};