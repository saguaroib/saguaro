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
Allows user to set a custom list of boards to be displayed at the top/bottom board listings.
*/
$(document).ready(function() { repod.thread_stats.init(); });
try { repod; } catch(a) { repod = {}; }
repod.thread_stats = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.thread_stats.update);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_stats_enabled") ? repod_jsuite_getCookie("repod_thread_stats_enabled") === "true" : true
		};
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Monitoring',read:this.config.enabled,variable:'repod_thread_stats_enabled',label:'Thread statistics',hover:'Display post and counts at the top and bottom of the page'}});
		this.update();
	},
	update: function() {
		if (repod.thread_stats.config.enabled && $("div.theader").length) {
            $("div.threadnav").css("float","left");
			$("span#repod_thread_stats_container").length == 0 && $("div.threadnav").after("&nbsp;<span id='repod_thread_stats_container'></span>");
			$("span#repod_thread_stats_container").html(repod.thread_stats.format());
		}
	},
	format: function() { return "[" + $("div.post.reply").length + " replies] [" + $("a > img.postimg").length + " images]"; }
};