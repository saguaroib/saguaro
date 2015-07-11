$(document).ready(function() {
    $(".filesize").click(function(e) {
        e.preventDefault();
        $("body").css({
            "background": "#FFF url('" + $(this).find("a").attr('href') + "') center/cover no-repeat fixed"
        });
    });
});