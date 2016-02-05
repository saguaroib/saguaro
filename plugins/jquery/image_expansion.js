//RePod - Expands images with their source inside their parent element up to certain dimensions.
RePod.ImageExpand = {
    init: function() {
        this.config = {
            enabled: RePod.isReady() && RePod.getItem("imageExpansion") === "true",
            selector: ".postimg"
        }
        RePod && RePod.info.push({
            menu: {
                category: 'Images',
                read: this.config.enabled,
                variable: 'imageExpansion',
                label: 'Image expansion',
                hover: 'Images expand inline on click.'
            }
        });
        this.update();
    },
    update: function() {
        this.config.enabled && $("div.threadnav").length && $(document).on("click", this.config.selector, function(event) {
            RePod.ImageExpand.check(event, $(this))
        });
    },
    check: function(event, e) {
        event.preventDefault();
        if(/\.webm$/.test($(e).parent().attr("href"))) {
            $(e).data("o-s") ? this.shrinkVideo(e) : this.expandVideo(e);
        } else {
            $(e).data("o-s") ? this.shrink(e) : this.expand(e);
        }
        $("#img_hover_element").remove();
    },
    expand: function(e) {
        $(e).data({
            "o-h": $(e).css("height"),
            "o-w": $(e).css("width"),
            "o-s": $(e).attr("src")
        }).css({
            "max-width": (Math.round($("body").width() - ($(e).parent().parent().offset().left * 2))),
            "width": "auto",
            "height": "auto"
        });
        var mp = $(e).parent().attr("href");
        mp !== $(e).attr("src") && $(e).attr("src", mp);
    },
    shrink: function(e) {
        $(e).attr("src", $(this).data("o-s"));
        $(e).css({
            "max-height": "",
            "max-width": "",
            "width": $(e).data("o-w")
        }).attr("src", $(e).data("o-s")).removeData();
    },
    expandVideo: function(e) {
        $(e).data({
            "o-s": true,
            "name": $(e).attr("src").split("/").pop().split(".")[0]
        }).hide();
        $(e).parent().after("<video class='expandedwebm-" + $(e).data("name") + "' loop autoplay controls src='" + $(e).parent().attr("href") + "'></video>");
    },
    shrinkVideo: function(e) {
        $(e).data({
            "o-s": false
        }).show();
        $(".expandedwebm-" + $(e).data("name")).remove();
    }
};
