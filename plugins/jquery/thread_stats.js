//RePod - Shows thread statistics (number of replies, number of image replies out of all replies) above and below threads.
RePod.ThreadStats = {
    init: function() {
        repod.thread_updater && repod.thread_updater.callme.push(RePod.ThreadStats.update);
        this.config = {
            enabled: RePod.isReady() && RePod.getItem("threadStats") === "true"
        };
        RePod && RePod.info.push({
            menu: {
                category: 'Monitoring',
                read: this.config.enabled,
                variable: 'threadStats',
                label: 'Thread statistics',
                hover: 'Display post/image counts in a thread.'
            }
        });
        this.update();
    },
    update: function() {
        if (RePod.ThreadStats.config.enabled && $("div.theader").length) {
            $("div.threadnav").css("float", "left");
            $("span#repod_thread_stats_container").length == 0 && $("div.threadnav").after("&nbsp;<span id='repod_thread_stats_container'></span>");
            $("span#repod_thread_stats_container").html(RePod.ThreadStats.format());
        }
    },
    format: function() {
        return "[" + $("div.post.reply").length + " replies] [" + $("a > img.postimg").length + " images]";
    }
};