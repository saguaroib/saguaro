//RePod - Attempts to update threads without reloading the page via jQuery and AJAX.
//Interconnects with other features which are called upon adding new content to the DOM, or not.
//Want a custom function called when new posts are added? Push it to repod.thread_updater.callme.
RePod.ThreadUpdater = {
    init: function() {
        this.config = {
            enabled: RePod.isReady() && RePod.getItem("threadUpdater") === "true",
            auto_update: RePod && RePod.getItem("threadUpdaterAutoUpdate") === "true",
            auto_scroll: RePod && RePod.getItem("threadUpdaterAutoScroll") === "true",
        }
        this.advanced = {
            current_min_delay: 10,
            current_max_delay: 10,
            step_timeout: 5,
            max_timeout: 150,
            timer: "",
            base_title: document.title,
            total_new: 0
        }
        if (RePod) {
            RePod.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.enabled,
                    variable: 'threadUpdater',
                    label: 'Thread updater',
                    hover: 'Enable inline thread updating'
                }
            });
            RePod.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.auto_update,
                    variable: 'threadUpdaterAutoUpdate',
                    label: 'Auto-update',
                    hover: 'Always auto-update threads'
                }
            });
            RePod.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.auto_scroll,
                    variable: 'threadUpdaterAutoScroll',
                    label: 'Auto-scroll on update',
                    hover: 'Automatically scroll the page on update'
                }
            });
        }
        this.update();
    },
    update: function() {
        if (RePod.ThreadUpdater.config.enabled && $("div.theader").length) {
            $("a:contains('Return')").after(" / <input type='checkbox' id='updater_checkbox' " + ((RePod.ThreadUpdater.config.auto_update) ? "checked" : "") + "></input> <label for='updater_checkbox'>Auto</label> <a class='update_button' href=''>Update</a> <span class='updater_timer'></span> <span class='updater_status'></span>");
        }
        $("a.update_button").on("click", function(e) {
            e.preventDefault();
            RePod.ThreadUpdater.load_thread_url();
        });
        $("input#updater_checkbox").on("click", function(event) {
            if (this.checked) {
                RePod.ThreadUpdater.timer.start();
            } else {
                RePod.ThreadUpdater.timer.stop();
            }
        });
        RePod.ThreadUpdater.config.auto_update && RePod.ThreadUpdater.timer.start();
    },
    timer: {
        check: function() {
            var timer_count = parseInt($("span.updater_timer").first().text());
            if (timer_count > 1) {
                timer_count--;
                $("span.updater_timer").text(timer_count);
            } else if (timer_count <= 1) {
                RePod.ThreadUpdater.load_thread_url();
                $("span.updater_timer").text("Updating...");
            }
        },
        start: function() {
            RePod.ThreadUpdater.advanced.current_max_delay = 10;
            $("span.updater_timer").text(RePod.ThreadUpdater.advanced.current_max_delay);
            RePod.ThreadUpdater.advanced.timer = setInterval(RePod.ThreadUpdater.timer.check, 1000);
            $("input#updater_checkbox").prop('checked', true);
        },
        stop: function() {
            $("span.updater_timer").text("");
            clearInterval(RePod.ThreadUpdater.advanced.timer);
            $("input#updater_checkbox").prop('checked', false);
        }
    },
    load_thread_url: function(url) {
        url = (url) ? url : location.href;
        var do_scroll = ($(window).scrollTop() + $(window).height() == repod_jsuite_getDocHeight()) ? true : false;
        $.ajax({
            url: url,
            success: function(result) {
                var counter = 0;
                console.log("test2");
                $(result).find('postContainer.replyContainer').each(function() {
                    if ($("div.theader").length) {
                        counter++;
                        RePod.ThreadUpdater.advanced.total_new++;
                        document.title = "(" + RePod.ThreadUpdater.advanced.total_new + ") " + RePod.ThreadUpdater.advanced.base_title;
                        $("div.thread").append($(this));
                    }
                });
                if (counter > 0) {
                    console.log("test");
                    RePod.ThreadUpdater.advanced.max_delay = RePod.ThreadUpdater.advanced.min_delay;
                    RePod.ThreadUpdater.callme.bind();
                    if (RePod.ThreadUpdater.config.auto_scroll) {
                        if (!tu_isVisible() && do_scroll) {
                            $('html, body').scrollTop($(document).height() - $(window).height());
                        }
                    }
                } else {
                    RePod.ThreadUpdater.advanced.current_max_delay += (RePod.ThreadUpdater.advanced.current_max_delay < RePod.ThreadUpdater.advanced.max_timeout) ? RePod.ThreadUpdater.advanced.step_timeout : 0;
                }
                $("span.updater_timer").text(RePod.ThreadUpdater.advanced.current_max_delay);
            }
        });
    },
    callme: {
        cache: [],
        push: function(a) {
            this.cache.push(a);
        },
        callthem: function() {

        },
        bind: function(input) {
            $.each(RePod.ThreadUpdater.callme.cache, function(a, b) {
                b();
            });
        }
    }
}

//http://www.raymondcamden.com/index.cfm/2013/5/28/Using-the-Page-Visibility-API
function tu_isVisible() {
    if ("webkitHidden" in document) return !document.webkitHidden;
    if ("mozHidden" in document) return !document.mozHidden;
    if ("hidden" in document) return !document.hidden;
    //worse case, just return true
    return true;
}

//http://stackoverflow.com/questions/3898130/how-to-check-if-a-user-has-scrolled-to-the-bottom
function repod_jsuite_getDocHeight() {
    var D = document;
    return Math.max(
        Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    );
}

$(window).scroll(function() {
    if ($(window).scrollTop() + $(window).height() == repod_jsuite_getDocHeight()) {
        document.title = RePod.ThreadUpdater.advanced.base_title;
        RePod.ThreadUpdater.advanced.total_new = 0;
    }
});