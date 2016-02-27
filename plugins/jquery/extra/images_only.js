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

Hide non-images posts when browsing.
*/
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