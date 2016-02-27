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

When browsing the index and having reached the bottom, loads the next page. Requires cached page extension to be ".html" or configured below.

*/
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
			repod.infinite_scroll.config.last_thread_count = $('div.thread').length;
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
			$(result).find('div.thread').each(function(){ $('div.thread').last().after($(this)).after("<br clear='left' /><hr />"); });
			$('div.thread:eq('+(repod.infinite_scroll.config.last_thread_count)+')').prepend("<span style='position:absolute; right:3px;'>[Page "+repod.infinite_scroll.config.page_num+"]</span>");
			repod.infinite_scroll.config.last_thread_count = $('div.thread').length;
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