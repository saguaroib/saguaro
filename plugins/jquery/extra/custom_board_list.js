//RePod - Allows user to set a custom list of boards to be displayed at the top/bottom board listings.
$(document).ready(function() { repod.custom_boardlist.init(); });
try { repod; } catch(a) { repod = {}; }
repod.custom_boardlist = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("custom_boardlist_enabled") ? repod_jsuite_getCookie("custom_boardlist_enabled") === "true" : false,
			original: $("span.boardlist:first").html()
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'custom_boardlist_enabled',label:'Custom board list',hover:'Only show selected boards in top and bottom board lists'},popup:{label:'[Edit]',title:'Custom Board List',type:'text',variable:'custom_boardlist_defined',placeholder:'Example: a b c http://u.rl|Display'}});
		this.update();
	},
	update: function() {
		if (this.config.enabled && !!repod_jsuite_getCookie("custom_boardlist_defined")) {
			$(".boardlist").html(this.format());
			$(".custom_boardlist_all").on("click", function() { $(".boardlist").html(repod.custom_boardlist.config.original); });
		}
	},
	format: function(a) {
		var c_bl_a = repod_jsuite_getCookie("custom_boardlist_defined").split(" ");
		$.each(c_bl_a,function(i,v) {
			var r = /([a-z0-9]+:\/\/\S+\.[a-z]{2,}\/?\S*?)\|(.+)/i;
			if (r.test(v)) {
				r = r.exec(v); c_bl_a[i] = "<a href='"+r[1]+"'>"+r[2]+"</a>";
			} else {
				c_bl_a[i] = "<a href='../"+v+"'>"+v+"</a>";
			}
		});
		c_bl_a = "["+c_bl_a.join(" / ")+"] [<span class='custom_boardlist_all' style='cursor:pointer' title='Show original'>...</span>]";
		return c_bl_a;
	}
}