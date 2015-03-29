//RePod - When browsing the index and having reached the bottom, loads the next page. Requires cached page extension to be ".html" or configured below.
$(document).ready(function() { repod.infinite_scroll.init(); });
try { repod; } catch(a) { repod = {}; }
repod.infinite_scroll = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_infinite_scroll") ? repod_jsuite_getCookie("repod_infinite_scroll") === "true" : false,
			page_ext: ".html",
			can_load: true,
			sensitivity: 500,
			page_num: 0,
			max_page_num: 0,
			last_thread_count: 0
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'repod_infinite_scroll',label:'Use infinite scroll',hover:'Enable infinite scroll, so reaching the bottom of the board index will load subsequent pages'}});
		this.update();
	},
	update: function() {
		if (this.config.enabled) {
			var page_num = $("table.pages > tbody > tr > td:eq(1)").clone();
			var max_page_num = page_num.clone();
			page_num.find('a').remove();
			repod.infinite_scroll.config.page_num = parseInt(page_num.text().replace(/\D/g,''));
			repod.infinite_scroll.config.max_page_num = parseInt(max_page_num.find('a').last().text().replace(/\D/g,''));
			repod.infinite_scroll.config.last_thread_count = $('span.thread').length;
			function getDocHeight() {
				var D = document;
				return Math.max(
					Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
					Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
					Math.max(D.body.clientHeight, D.documentElement.clientHeight)
				);
			}

			$(window).scroll(function() {
				if($(window).scrollTop() + $(window).height() + repod.infinite_scroll.config.sensitivity >= getDocHeight() && repod.infinite_scroll.config.can_load) {
					//Load the next page.
					repod.infinite_scroll.config.can_load = false;
					if (repod.infinite_scroll.config.page_num < repod.infinite_scroll.config.max_page_num) {
						repod.infinite_scroll.config.page_num++;
						repod.infinite_scroll.load_page_url(repod.infinite_scroll.config.page_num+repod.infinite_scroll.config.page_ext);
					}
				}
			});
		}
	},
	load_page_url: function(url) {
		$.ajax({url:url,success:function(result){
			$(result).find('span.thread').each(function(){ $('span.thread').last().after($(this)).after("<br clear='left' /><hr />"); });
			$('span.thread:eq('+(repod.infinite_scroll.config.last_thread_count)+')').prepend("<span style='position:absolute; right:3px;'>[Page "+repod.infinite_scroll.config.page_num+"]</span>");
			repod.infinite_scroll.config.last_thread_count = $('span.thread').length;
			repod.infinite_scroll.callme.bind();
			repod.infinite_scroll.config.can_load = true;
		}});
	},
	callme: {
		cache: [],
		push: function(a) { this.cache.push(a); },
		bind: function(input) {
			$.each(repod.infinite_scroll.callme.cache, function(a,b) { b(); });	
		}
	}
};