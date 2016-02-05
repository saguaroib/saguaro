//RePod - Appends a toolbar for image posts with links to resources.
RePod.ImageToolbar = {
    init: function() {
        //repod.thread_updater && repod.thread_updater.callme.push(repod.image_toolbar.update);
        //repod.infinite_scroll && repod.infinite_scroll.callme.push(repod.image_toolbar.update);
        this.config = {
            enabled: RePod.isReady() && RePod.getItem("imageToolbar") === "true",
            selector: "div.post"
        }
        RePod && RePod.info.push({
            mode: 'modern',
            menu: {
                category: 'Images',
                read: this.config.enabled,
                variable: 'imageToolbar',
                label: 'Image search',
                hover: ''
            }
        });
        this.update();
    },
    update: function() {
        if (this.config.enabled) {
            $(RePod.ImageToolbar.config.selector).each(function() {
                $(this).find(".postInfo").append(RePod.ImageToolbar.format($(this)));
            });
        }

        //Binds
        $(document).on("click", "a.menu.closed", function(e) {
            e.preventDefault();
            RePod.ImageToolbar.menu.open(this);
        });

        $(document).on("click", "a.menu.open", function(e) {
            e.preventDefault();
            RePod.ImageToolbar.menu.close();
            $(this).removeClass("open").addClass("closed");
        });

        $(document).on("click", "div.menu a[data-cmd=report]", function(e) {
            e.preventDefault();
            RePod.ImageToolbar.report(this);
        });
    },
    menu: {
        open: function(a) {
            this.close(); //Close existing menu.
            $(a).removeClass("closed").addClass("open");

            var t = $(a).parent().parent().parent(),
                o = $(a).position();

            //Generate and display menu.
            $('body').append(
                $(this.gen(a, t)).css({
                    'position': 'absolute',
                    'top': (o.top + $(a).height()) + "px",
                    'left': o.left + "px",
                    'border': '1px solid black',
                    'padding': '3px',
                    'margin': '3px'
                })
            );
        },
        close: function() {
            $(".menu.gen").remove();
            $("a.menu.open").removeClass("open").addClass("closed");
        },
        getInfo: function(a) {
            return {
                'no': $(a).find(".postInfo > input[type=checkbox]").attr("name"),
                'image': $(a).find("div.fileThumb > a").attr("href"),
                'thumb': $(a).find("div.fileThumb > a > img").attr("src")
            }
        },
        gen: function(a, target) {
            var temp = $('<div />', {
                    class: 'menu gen reply'
                }),
                info = this.getInfo(target);

            temp.append($('<div />').append($("<a />", {
                'data-cmd': 'report',
                'data-target': info.no,
                'href': '#',
                'text': 'Report this post'
            })));

            if (info.image) {
                temp.append($('<hr />'));

                var ext = {
                    'Google': '//www.google.com/searchbyimage?image_url={url}',
                    'IQDB': '//iqdb.org/?url={url}',
                    'Waifu2X': '//waifu2x.booru.pics/Home/fromlink?denoise=1&scale=2&url={url}'
                }

                $.each(ext, function(name, path) {
                    var path2 = ((new RegExp("^" + location.protocol).test(info.image)) ? "" : location.protocol) + info.image,
                        path = path.replace("{url}", path2),
                        item = $('<div />').append(
                            $("<a />", {
                                'text': name,
                                'href': path,
                                'target': '_blank'
                            })
                        );

                    temp.append(item);
                });
            }

            return temp[0].outerHTML;
        }
    },
    format: function(a) {
        return "<a data-target='" + $(a).attr("id") + "' href='#' class='menu closed'>▶</a>"
    },
    report: function(a) {
        var no = $(a).data('target');
        
        console.log(no);
    }
};