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
$(document).ready(function() { repod.styleswitch.init(); });
try { repod; } catch(e) { repod = {}; }

repod.styleswitch = {
    init: function() {
        this.stylesheet_cache = $("link[rel$=stylesheet].togglesheet"); //Eventually change to a safer selector.
        
        //$("#switchform").remove(); //Literally removing the competition

        this.ready();
    },
    ready: function() {
        this.enableSheet(this.readSaved());
        this.injectSelector();
    },
    disableAllSheets: function() {
        $.each(this.stylesheet_cache, function(i,obj) {
            $(obj).prop("disabled", true);
        })
    },
    enableSheet: function(input) {
        var that = this;

        $.each(this.stylesheet_cache, function(index,obj) {
            if ($(obj).attr("title") === input) {
                that.enable(obj);
            }
        });
    },
    enable: function(input) {
        if (input) {
            this.disableAllSheets();

            $(input).prop("disabled", false);

            this.saveCurrent();
        }
    },
    getCurrentSheet: function() {
        var t;

        $.each(this.stylesheet_cache, function(i,obj) {
            if ($(obj).prop("disabled") === false) {
                t = $(obj).attr("title");
            }
        });

        return t;
    },
    setRandomSheet: function() {
        var r = Math.floor(Math.random() * this.stylesheet_cache.length),
            o = $(this.stylesheet_cache[r]).attr("title"); //Ehhhh.

        this.enableSheet(o);
    },
    generateSelector: function() {
        var box = $("<select />", {class: "styleswitcher"}),
            that = this;

        $.each(this.stylesheet_cache, function(i,obj) {
            var title = $(obj).attr("title"),
                slctd = (title === that.getCurrentSheet()),
                child = $("<option />", {text: title, selected: slctd});

            box.append(child);
        });

        box = $("<div />").append(box);

        return $(box)[0];
    },
    injectSelector: function() {
        var selector = this.generateSelector();

        $(".delsettings").after(selector);
        this.bindSelector(selector);
    },
    bindSelector: function(selector) {
        var that = this;

        $(selector).find("select").change(function() {
            that.enableSheet(this.value);
        });
    },
    saveCurrent: function() {
        localStorage["current_css"] = this.getCurrentSheet(); 
    },
    readSaved: function() {
        return localStorage["current_css"];
    }
};