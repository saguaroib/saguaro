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
*/$(document).ready(function() { repod.image_hover.init(); });
try { repod; } catch(a) { repod = {}; }
repod.image_hover = {
	init: function() {
		//typeof repod_thread_updater_calls == "object" && repod_thread_updater_calls.push(repod_image_hover_bindings);
		//typeof repod_infinite_scroll_calls == "object" && repod_infinite_scroll_calls.push(repod_image_hover_bindings);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_hover_enabled") ? repod_jsuite_getCookie("repod_image_hover_enabled") === "true" : true,
			selector: ".postimg"
		}
		repod.suite_settings && repod.suite_settings.info.push({mode:'modern',menu:{category:'Images',read:this.config.enabled,variable:'repod_image_hover_enabled',label:'Image hover',hover:'Expand images on hover, limited to browser size'}});
		this.update();
	},
	update: function() {
        var that = this;
		if (this.config.enabled) {
			$(document).on("mouseover", this.config.selector, function() { that.display($(this)); });
			$(document).on("mouseout", this.config.selector, function() { that.remove_display() });
		}
	},
	display: function(e) {
		if (!$(e).data("o-s")) {
            var element = $('<div id="img_hover_element" />');
            var css = {right:"0px",top:"0px",position:"fixed",width:"auto",height:"auto","max-height":"100%","max-width":Math.round($("body").width() - ($(e).offset().left + $(e).outerWidth(true)) + 20) + "px"}
            
            if (/\.webm$/.test($(e).parent().attr("href"))) {
                $(element).append("<video class='expandedwebm-" + $(e).data("name") +"' loop autoplay src='" + $(e).parent().attr("href") + "'></video>");
            } else {
                var img = $("<img src='"+$(e).parent().attr("href")+"'/>");
                $(img).css(css);
                $(element).append(img);
            }
            
            $(element).css(css);
            $("body").append(element);
		}
	},
	remove_display: function() { $("#img_hover_element").remove(); }
};