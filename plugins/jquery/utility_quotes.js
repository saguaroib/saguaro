//RePod - Various things involving quotes.

RePod.Quotes = {
	init: function() {
		RePod.ThreadUpdater && RePod.ThreadUpdater.callme.push(RePod.Quotes.backlinks.update);
		this.backlinks.config = {
			enabled: RePod.isReady() && RePod.getItem("quoteBacklinks") === "true",
			prefix: "bl_"
		}
		this.inline_expansion.config = {
			enabled: RePod.isReady() && RePod.getItem("inlineQuotes") === "true",
			selector: "a.inline_quote"
		}
		this.hover.config = {
			enabled: RePod.isReady() && RePod.getItem("hoverQuotes") === "true",
			selector: "a.inline_quote",
			div_class: "hover_post"
		}
		this.config = {
			in_thread: ($("div.post op").length == 1) ? true : false
		}
		if (RePod) {
			RePod.info.push({menu:{category:'Quotes & Replying',read:this.backlinks.config.enabled,variable:'hoverQuotes',label:'Quote preview',hover:'Enable inline quote previews'}});
			RePod.info.push({menu:{category:'Quotes & Replying',read:this.inline_expansion.config.enabled,variable:'quoteBacklinks',label:'Backlinks',hover:'Show who has replied to a post'}});
			RePod.info.push({menu:{category:'Quotes & Replying',read:this.hover.config.enabled,variable:'inlineQuotes',label:'Inline quote links',hover:'Clicking quote links will inline expand the quoted post, shift-clicking bypasses the inlining'}});
		}
		this.update();
	},
	update: function() {
		$("a:contains('>>')").attr("class","inline_quote");
		/*this.config.in_thread && */RePod.Quotes.backlinks.update(); RePod.Quotes.inline_expansion.update(); RePod.Quotes.hover.update();
	},
	backlinks: {
		config: {},
		update: function() {
			$("a:contains('>>'):not(.inline_quote)").attr("class","inline_quote");
			if (RePod.Quotes.backlinks.config.enabled) {
				var prefix = RePod.Quotes.backlinks.config.prefix;
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
			this.config.enabled && $(document).on("click", this.config.selector, function(e) { RePod.Quotes.inline_expansion.check(e,$(this)); });
		},
		check: function(event,e) {
			event.preventDefault();
			if (event.shiftKey || !RePod.Quotes.config.in_thread) { window.location = $(e).attr("href"); }
			else {
				var temp = ($(e).is('.quotelink')) ? $(e).attr("href").split("#") : $(e).attr("href").split("#");
				var target_thread = temp[1];
				var target_post = temp[1];
				var target = ($(e).parent().attr("class") == "quotelink") ? $(e).parent() : $(e);
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
			var clone = $("a[href='javascript:insert('"+target_post+"')']:first").parent().clone(true);
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
			if (RePod.Quotes.hover.config.enabled) {
				$(document).on("mouseover", RePod.Quotes.hover.config.selector, function(e) { RePod.Quotes.hover.display_hover(e,$(this)); });
				$(document).on("mouseout", RePod.Quotes.hover.config.selector, function() { RePod.Quotes.hover.kill_hover(); });
			}
		},
		display_hover: function(event,e) {
			var temp = ($(e).is('.quotelink')) ? $(e).attr("href").split("#") : $(e).attr("href").split("#");
			var target_thread = temp[1]; var target_post = temp[1];
			var in_thread = ($("a[href='javascript:insert('"+target_post+"')']:first").length) ? true : false;
			if (in_thread) {
				event.preventDefault();
				var target = ($(e).parent().attr("class") == "quotelink") ? $(e).parent() : $(e);
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