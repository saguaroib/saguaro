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