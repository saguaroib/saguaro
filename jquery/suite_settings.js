$(document).ready(function() { repod.suite_settings.init(); });
repod_suite_settings_pusher = []; //Legacy support. New scripts should push their information to repod.suite_settings.info instead.
try { repod; } catch(e) { repod = {}; }
repod.suite_settings = {
	init: function() {
		this.config = {
			width: 300, //Width of settings window and any windows spawned by it, in pixels. (default: 300)
			multi_suffix: "amount", //Suffix to combine with the prefix provided in multi*-type popups. e.g.: prefix is "test_" and suffix is "amount", the cookie containing the amount of enumerated from this prefix will be "test_amount" (default: amount)
			//At the moment multi_suffix isn't applied. The suffix applied is hard coded to "amount".
			pre_categories: ["Images","Quotes & Replying","Monitoring","Navigation","Miscellaneous"] //Categories that should be spawned in this order before everything else.
		}
		$("span.adminbar").prepend("[<a href='#' id='repod_jquery_suite_settings_open'>Settings</a>]");
		$("a#repod_jquery_suite_settings_open").click(function() { repod.suite_settings.spawn.settings_window(); });
	},
	spawn: {
		settings_window: function() {
			$("body").append("<div id='settings_container' style='position:fixed;top:0px;left:0px;width:100%;height:100%;display:table;background-color:rgba(0,0,0,0.25);'><div style='display:table-cell;vertical-align:middle;height:inherit'><div id='settings_window' class='reply' style='max-height:480px;width:"+repod.suite_settings.config.width+"px;overflow:auto;margin-left:auto;margin-right:auto;border-style:solid;border-width:1px;padding:5px 0px 5px 0px;text-align:center;'></div></div></div>");
			$("#settings_window").append("<strong>Settings</strong> <img id='close' style='float:right;cursor:pointer;position:relative;top:5px;right:5px;' src='jquery/close.jpg' title='Close' alt='[X]'></img><hr/><div id='populated_settings' style='text-align:left;padding:0px 3px 0px 3px;'></div><hr /><input type='submit' value='Save'> <input type='submit' value='Reset'></input><br /><span style='font-size:10px'>Requires cookies. See source for integration instructions.</span>");
			$("#settings_container").on("click", function() { $(this).remove(); });
			$("#settings_window").on("click", function(event) {	event.stopPropagation(); });
			$("img#close").on("click", function() { $("div#settings_container").remove(); });
			repod.suite_settings.populate.settings_window();
		},
		popup: function(popup_data) {
			if (popup_data["type"] !== "function") {
				$("body").append("<div id='settings_popup_container' style='position:fixed;top:0px;left:0px;width:100%;height:100%;display:table;background-color:rgba(0,0,0,0.25);'><div style='display:table-cell;vertical-align:middle;height:inherit'><div id='settings_popup_window' class='reply' style='max-height:480px;width:"+repod.suite_settings.config.width+"px;overflow:auto;margin-left:auto;margin-right:auto;border-style:solid;border-width:1px;padding:5px 0px 5px 0px;text-align:center;'></div></div></div>");
				$("#settings_popup_window").append("<strong>"+popup_data["title"]+"</strong> <img id='close' style='float:right;cursor:pointer;position:relative;top:5px;right:5px;' src='jquery/close.jpg' title='Close' alt='[X]'></img><hr/><div id='pop_content_area' style='text-align:left;padding:0px 3px 0px 3px;'></div><hr />");
				$("#settings_popup_container").on("click", function() { $(this).remove(); });
				$("#settings_popup_window").on("click", function(event) { event.stopPropagation(); });
				$("#settings_popup_window").append(repod.suite_settings.populate.popup_footer(popup_data["type"]));
				$("#settings_popup_window > input[value='Save']").data(popup_data).on("click",function() { repod.suite_settings.data_manip.save.popup($(this).data()); });;
				$("#settings_popup_window > input[value='Reset']").data(popup_data).on("click",function() { repod.suite_settings.data_manip.reset.popup($(this).data()); });;
				$("#settings_popup_window > #pop_content_area").html(repod.suite_settings.populate.popup(popup_data));
				$("#settings_popup_window > img#close, #settings_popup_window > input[value='Close']").on("click", function() { $("div#settings_popup_window").remove(); });
			} else {
				repod_jsuite_executeFunctionByName(popup_data["variable"],window);
			}
		}
	},
	populate: {
		settings_window: function() {
			$.each(repod.suite_settings.config.pre_categories, function(a,b) { repod.suite_settings.populate.spawn_category(b); });
			this.iterate(repod_suite_settings_pusher);
			this.iterate(repod.suite_settings.info.cache);
			$("#populated_settings > div > strong").on("click", function() { $("#"+$(this).text().replace(/[\A\W]/g,"-")+" > span").slideToggle(); });
			$("#settings_window > strong").on("click", function() { if ($("#populated_settings > div > span").first().css("display") == "none") { $("#populated_settings > div > span").slideDown(); } else { $("#populated_settings > div > span").slideUp(); } });
			$("div.grouptoggle > a").on("click", function() { $("#"+$(this).parent().prev("strong").text().replace(/[\A\W]/g,"-")+" > span > input[type='checkbox']").prop("checked",($(this).text() == "On")?"checked":""); });
			$("#settings_window > input[value='Save']").on("click", function() { repod.suite_settings.data_manip.save.settings_window(); });
			$("#settings_window > input[value='Reset']").on("click", function() { repod.suite_settings.data_manip.reset.settings_window(); });
			$(".settings_popup").on("click", function() { repod.suite_settings.spawn.popup($(this).data()); });
		},
		iterate: function(a) {
			$.each(a,function(a,b) {
				if (b['menu']) {
					var cat, cat_safe, tvar, name, desc;
					var cat = b['menu']['category']; var cat_safe = cat.replace(/[\A\W]/g,"-");
					var tvar = b['menu']['variable']; var name = b['menu']['label']; var desc = (b['menu']['hover']) ? b['menu']['hover'].replace(/'/g, "&apos;").replace(/"/g, "&quot;") : "";
					if (cat && tvar && name) {
						repod.suite_settings.populate.spawn_category(cat);
						$("#populated_settings > #"+cat_safe).show();
						if (b['menu']['read']) { var c = (b['menu']['read'] === true) ? "checked" : "" } else { var c = (repod_jsuite_getCookie(tvar) === "true") ? "checked" : ""; }
						var popup = (b['popup']) ? b['popup'] : "";
						var n = $(".settings_popup").size();
						popup = (popup['label'] && popup['title'] && popup['type'] && popup['variable']) ? " <a href='#' class='settings_popup' id='settings_popup_"+n+"'>"+popup['label']+"</a>" : "";
						$("#populated_settings > #"+cat_safe+" > span").append("<input id='"+tvar+"' "+c+" type='checkbox'><label for='"+tvar+"' title='"+desc+"'>"+name+"</label>"+popup+"<br />");
						popup !== "" && $("#settings_popup_"+n).data(b['popup']);
					}
				}
			});
		},
		spawn_category: function(cat,cat_safe) {
			cat_safe = (cat_safe) ? cat_safe : cat.replace(/[\A\W]/g,"-");
			!$("#populated_settings > #"+cat_safe).length && $("#populated_settings").append("<div id='"+cat_safe+"' style='display:none'><strong>"+cat+"</strong> <div class='grouptoggle' style='font-size:11px;display:inline'>(<a href='#'>On</a> | <a href='#'>Off</a>)</div><br /><span></span></div>"); 
		},
		popup: function(popup_data) {
			//Shortcuts:
			var v = popup_data["variable"]; // Variable name/"variable" key.
			var d = (repod_jsuite_getCookie(v)) ? repod_jsuite_getCookie(v) : ""; //Variable contents.
			var p = (popup_data['placeholder']) ? popup_data['placeholder'] : ""; //Specified placeholder.
			var w = ($("#settings_popup_window > #pop_content_area").width() - parseInt($("#settings_popup_window > #pop_content_area").css("padding-right")) - parseInt($("#settings_popup_window > #pop_content_area").css("padding-left"))); //Fancy inner width.
			var output = "";
			if (popup_data['type'] == "textarea") { output = '<textarea id="'+popup_data["variable"]+'" placeholder="'+p+'" style="width:'+w+'px">'+d+'</textarea>'; }
			if (popup_data['type'] == "text") { output = '<input id="'+v+'" type="text" placeholder="'+p+'" value="'+d+'" style="width:'+w+'px"></input>'; }
			if (popup_data['type'] == "info") { output = v; } 
			if (popup_data['type'] == "multitext") {
				if (popup_data['variable']['prefix'] && popup_data['variable']['data'].length > 0) {
					$.each(popup_data['variable']['data'],function(a,b) {
						d = (repod_jsuite_getCookie(popup_data['variable']['prefix']+a)) ? repod_jsuite_getCookie(popup_data['variable']['prefix']+a) : "";
						output += '<input id="'+popup_data['variable']['prefix']+a+'" type="text" placeholder="'+popup_data['variable']['data'][a]+'" value="'+d+'" style="width:'+w+'px;margin-bottom:2px;"></input>';
					});
				} //else { output = "This multitext is not formatted properly."; }
			}			
			return output;				
		},
		popup_footer: function (type) {
			var o;
			if (type == "info") { o = "<input type='submit' value='Close'>"; }
			else {
				o = "<input type='submit' value='Save'> <input type='submit' value='Reset'></input>";				
			}			
			return o;
		}
	},
	data_manip: {
		save: {
			settings_window: function() {
				$("#populated_settings > div > span > input[type='checkbox']").each(function() {
					v1 = $(this).attr("id"); v2 = (($(this).prop("checked")) ? true : false);
					repod_jsuite_setCookie(v1,v2,7);
				});
				location.reload();
			},
			popup: function(popup_data) {
				var c = 0;
				$("#settings_popup_window > #pop_content_area > :input").each(function() {
					c++; // Original joke.
					repod_jsuite_setCookie($(this).attr("id"),$(this).val(),7);
				});
				if (c > 0 && popup_data['type'] == 'multitext') { repod_jsuite_setCookie(popup_data['variable']['prefix']+"amount",c,7); }
				$("div#settings_popup_container").remove();
			}
		},
		reset: {
			settings_window: function() { $("#populated_settings > div > span > input[type='checkbox']").each(function() { v1 = $(this).attr("id"); repod_jsuite_setCookie(v1,"",-1); });	location.reload(); },
			popup: function(popup_data) {
				$("#settings_popup_window > #pop_content_area > :input").each(function() {
					repod_jsuite_setCookie($(this).attr("id"),"",-1);
				});
				$("div#settings_popup_container").remove();
			}
		}
	},
	info: {
		cache: [],
		push: function(a) { this.cache.push(a); }
	}
};

//http://www.w3schools.com/js/js_cookies.asp
//Do not remove, but feel free to optimize.
function repod_jsuite_setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}
function repod_jsuite_getCookie(c_name) {
	var c_value = document.cookie;
	var c_start = c_value.indexOf(" " + c_name + "=");
	if (c_start == -1) {
		c_start = c_value.indexOf(c_name + "=");
	}
	if (c_start == -1) {
		c_value = null;
	} else {
		c_start = c_value.indexOf("=", c_start) + 1;
		var c_end = c_value.indexOf(";", c_start);
		if (c_end == -1) {
			c_end = c_value.length;
		}
		c_value = unescape(c_value.substring(c_start,c_end));
	}
	return c_value;
}
//http://stackoverflow.com/a/359910
function repod_jsuite_executeFunctionByName(functionName, context /*, args */) {
	var args = [].slice.call(arguments).splice(2);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(this);
}