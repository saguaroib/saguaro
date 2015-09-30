$(document).ready(function() {
    $(".filesize").click(function(e) {
        e.preventDefault();
        bgmod_save($(this).find("a").attr('href'));
        bgmod_set();
    });
    
    function bgmod_save(a) {
        localStorage['bgmod_url'] = a;
    }
    
    function bgmod_set() {
        if (localStorage['bgmod_url']) {
            $("body").css({
                "background": "#FFF url('" + localStorage['bgmod_url'] + "') center/cover no-repeat fixed"
            });
        }
    }
    
    bgmod_set();
});