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

Allows user to inject custom CSS rules.

*/
$(document).ready(function() { repod.custom_css.init(); });
try { repod; } catch(a) { repod = {}; }
repod.custom_css = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("custom_css_enabled") ? repod_jsuite_getCookie("custom_css_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'custom_css_enabled',label:'Custom CSS',hover:'Include your own CSS rules'},popup:{label:'[Edit]',title:'Custom CSS',type:'textarea',variable:'custom_css_defined',placeholder:'Input custom CSS here.'}});
		this.update();
	},
	update: function() {
		if (repod.custom_css.config.enabled) {
			$("<style type='text/css'>"+repod_jsuite_getCookie("custom_css_defined")+"</style>").appendTo("head");
		}
	}
}