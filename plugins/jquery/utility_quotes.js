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

Various things involving quotes.
*/
$(document).ready(function() { repod.utility_quotes.init(); });
try { repod; } catch(e) { repod = {}; }
repod.utility_quotes = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.utility_quotes.backlinks.update);
		this.backlinks.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_utility_quotes_backlinks") ? repod_jsuite_getCookie("repod_utility_quotes_backlinks") === "true" : true,
			prefix: "bl_"
		}
		this.inline_expansion.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_utility_quotes_inline") ? repod_jsuite_getCookie("repod_utility_quotes_inline") === "true" : true,
			selector: "a.inline_quote"
		}
		this.hover.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_utility_quotes_hover") ? repod_jsuite_getCookie("repod_utility_quotes_hover") === "true" : true,
			selector: "a.inline_quote",
			div_class: "hover_post"
		}
		this.config = {
			in_thread: ($("div.post op").length == 1) ? true : false
		}
		if (repod.suite_settings) {
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.backlinks.config.enabled,variable:'repod_utility_quotes_hover',label:'Quote preview',hover:'Enable inline quote previews'}});
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.inline_expansion.config.enabled,variable:'repod_utility_quotes_backlinks',label:'Backlinks',hover:'Show who has replied to a post'}});
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.hover.config.enabled,variable:'repod_utility_quotes_inline',label:'Inline quote links',hover:'Clicking quote links will inline expand the quoted post, shift-clicking bypasses the inlining'}});
		}
		this.update();
	},
	update: function() {
		$("a:contains('>>')").attr("class","inline_quote");
		/*this.config.in_thread && */repod.utility_quotes.backlinks.update(); repod.utility_quotes.inline_expansion.update(); repod.utility_quotes.hover.update();
	},
	backlinks: {
		config: {},
		update: function() {
			$("a:contains('>>'):not(.inline_quote)").attr("class","inline_quote");
			if (repod.utility_quotes.backlinks.config.enabled) {
				var prefix = repod.utility_quotes.backlinks.config.prefix;
				$("a.quotejs:odd").each(function () {
					var this_post = $(this).text();
					var num = $("a.inline_quote:not('.backlink')").filter(function(index) { return $(this).text() === ">>"+this_post; }).length; //http://stackoverflow.com/a/6673805
					if (num > 0) {
						if ($("#"+prefix+this_post).length == 0) { $(this).after(" <div style='display:inline; font-size: 0.8em !important;' class='backlinks' id='bl_"+this_post+"'>Replies: </div>"); }
						$("a.inline_quote:not('.backlink')").filter(function(index) { return $(this).text() === ">>"+this_post; }).each(function () {
							var target_post = $(this).parent().parent().parent().children("a.quotejs:odd").text();
							if (target_post.length !== 0 && $("a[href='#"+target_post+"']").length == 0) { $("#bl_"+this_post).append("<a href='#"+target_post+"' class='inline_quote backlink'>&gt;&gt;"+target_post+"</a> "); }
						});
					}
				});
			}
		}
	},
	inline_expansion: {
		config: {},
		update: function() {
			this.config.enabled && $(document).on("click", this.config.selector, function(e) { repod.utility_quotes.inline_expansion.check(e,$(this)); });
		},
		check: function(event,e) {
			event.preventDefault();
			if (event.shiftKey || !repod.utility_quotes.config.in_thread) { window.location = $(e).attr("href"); }
			else {
				var temp = ($(e).is('.backlink')) ? $(e).attr("href").split("#") : $(e).attr("href").split("?")[1].split("=")[1].split("#");
				var target_thread = temp[0];
				var target_post = temp[1];
				var target = ($(e).parent().attr("class") == "unkfunc") ? $(e).parent() : $(e);
				var target = ($(e).is(".backlink")) ? $(e).parent().parent().children("blockquote") : target;
				var bl = !!$(e).is(".backlink");
				if (!$(target).siblings("div#inline_"+target_post).length && !$(target).children("#inline_"+target_post).length) {
					this.expand(target,target_post,bl);
				} else {
					this.shrink(target,target_post,bl);
				}
			}
		},
		expand: function(target,target_post,bl) {
			$("div.hover_post").remove();
			var clone = $("a[href='javascript:insert('>>"+target_post+"')']:first").parent().clone(true);
			clone.find("div.inline_post").prev().remove().next().remove().remove();
			clone.find("a > img.imgpost").remove();
			if (bl) {
				$(target).prepend("<div id='inline_"+target_post+"' class='inline_post reply' style='display:inline-block;border-style:solid;border-width:1px'></div><br />");
				$(target).children("#inline_"+target_post).html(clone);
			} else {
				$(target).after("<br /><div id='inline_"+target_post+"' class='inline_post reply' style='display:inline-block;border-style:solid;border-width:1px'></div><br />");
				$(target).siblings("#inline_"+target_post).html(clone);
			}	
		},
		shrink: function(target,target_post,bl) {
			if (bl) {
				$(target).children("#inline_"+target_post).next().remove();
				$(target).children("#inline_"+target_post).remove();
			} else {
				$(target).siblings("#inline_"+target_post).prev().remove();
				$(target).siblings("#inline_"+target_post).next().remove();
				$(target).siblings("#inline_"+target_post).remove();
			}
		}
	},
	hover: {
		config: {},
		update: function() {
			if (repod.utility_quotes.hover.config.enabled) {
				$(document).on("mouseover", repod.utility_quotes.hover.config.selector, function(e) { repod.utility_quotes.hover.display_hover(e,$(this)); });
				$(document).on("mouseout", repod.utility_quotes.hover.config.selector, function() { repod.utility_quotes.hover.kill_hover(); });
			}
		},
		display_hover: function(event,e) {
			var temp = ($(e).is('.backlink')) ? $(e).attr("href").split("#") : $(e).attr("href").split("?")[1].split("=")[1].split("#");
			var target_thread = temp[0]; var target_post = temp[1];
			var in_thread = ($("a[href='javascript:insert('>>"+target_post+"')']:first").length) ? true : false;
			if (in_thread) {
				event.preventDefault();
				var target = ($(e).parent().attr("class") == "unkfunc") ? $(e).parent() : $(e);
				if (!$(target).siblings("div#hover"+target_post).length) {
					var clone = $("a[href='javascript:insert('>>"+target_post+"')']:first").parent().clone(true);
					clone.find("div.inline_post").prev().remove().next().remove().remove();
					if (clone.find(".postimg").attr("old-src")) { clone.find(".postimg").css({"max-height":"","max-width":"","src":clone.find(".postimg").attr("old-src"),"height":clone.find(".postimg").attr("old-height"),"width":clone.find(".postimg").attr("old-width")}); }
					$("body").append("<div id='hover_"+target_post+"' class='hover_post reply' style='display:block;border-style:solid;border-width:1px;position:fixed;left:"+($(e).offset().left + $(e).outerWidth() + 5)+"px'></div>");
					$("div.hover_post").append(clone).css("top",$(e).offset().top - $(window).scrollTop() - ($("body > div.hover_post").outerHeight()/2)+"px");
				} 
			}
		},
		kill_hover: function() { $("div.hover_post").remove(); }
	}
};