Core = {
    init: function() {
        extraScripts();
        l();
        this.style.init();
        this.isMobile();
    },
    
    request: function(type, url, heads) {
        var c = new XMLHttpRequest();
        c.open(type, url, true);
        
        if (heads) {
            for (item in heads) {
                c.setRequestHeader(item, heads[item]);
            }
        }
        c.send(null);
        return c;
    },
    
    report: {
        file: function() {
            var o = document.getElementsByTagName('INPUT');
            for (var i = 0; i < o.length; i++) {
                if (o[i].type == 'checkbox' && o[i].checked && o[i].value == 'delete') {
                    var url = site + "/" + board + "/" + phpself + '?mode=report&no=' + o[i].name;
                    var data = o[i].name;
                    if (Core.isMobile()) {
                        day = new Date();
                        id = day.getTime();
                        window.open(a, id, "toolbar=0,scrollbars=0,location=0,status=1,menubar=0,resizable=1,width=685,height=225");
                    } else {
                        return Core.frame.toggle(url, 685, 225, "Report", data);
                    }
                }
            }
        }
    },
    
    frame: {
        toggle: function(url, height, width, type, data) {
            if ($("div.inlineFrame").length == 1) {
                this.close();
            } else {
                this.open(url, height, width, type, data);  
            } 
        },
        open: function(url, height, width, type, data) {
            $("body").append('<div class="inlineFrame" style="position:fixed;top:50px;border:1px solid black;"><div class="postblock" style="text-align:center; border-left:none; border-top:none;border-right:none;">' + type + ' No.' + data +' [<a class="cmd" onclick="Core.frame.close()" >Close</a>]</div><iframe src="' + url + '" width="' + width + '" height="' + height + '" frameborder="0"></iframe></div>');
        },
        close: function() {
            $("div.inlineFrame").remove();
        }
    },

    style: {
        init: function() {
            var active = this.getActive();
            /*this.setActive("Tomorrow");
            this.unset("Saguaba");*/
        },
        
        unset: function(set) {
            $("link[title='Saguaba']").attr("rel", "alternate stylesheet");
        },
        
        setActive: function(set) {
            $("link[title='" + set + "']").attr("rel", "stylesheet");
        },

        getActive: function() {
            var active = Core.cookie.get('saguaro_stylesheet');
            var default_css = (styleGroup == "nsfw") ? "Saguaba" : "Sagurichan";
            return (active) ? active : default_css;
        }
    },
    
    cookie: {
        set: function() {
            
        },
        get: function(name) {
            var re = new RegExp(name + "=([^;]+)");
            var value = re.exec(document.cookie);
            return (value != null) ? unescape(value[1]) : false;
        },
        remove: function(name) {
            document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
        }
    },
    
    isMobile: function() {
        return (/Mobile|Android|Dolfin|Opera Mobi|PlayStation Vita|Nintendo DS/.test(navigator.userAgent)) ? true : false;
    }
};


function insert(text) {
    var textarea = document.forms.contrib.com;
    if (document.selection) {
        textarea.focus();
        var sel = document.selection.createRange();
        sel.text = ">>" + text + "\n";
    } else if (textarea.selectionStart || textarea.selectionStart == "0") {
        var startPos = textarea.selectionStart;
        var endPos = textarea.selectionEnd;
        textarea.value = textarea.value.substring(0, startPos) + ">>" + text + "\n" + textarea.value.substring(endPos, textarea.value.length);
    } else {
        textarea.value += ">>" + text + "\n";
    }
}

function l() {
    var P = Core.cookie.get("saguaro_pass"),
        N = Core.cookie.get("saguaro_name"),
        E = Core.cookie.get("saguaro_email"),
        i;
    with(document) {
        for (i = 0; i < forms.length; i++) {
            if (document.forms.contrib) {
                document.forms.contrib.pwd.value = P;
            }
            /*if (document.forms.contrib.name) {
                //document.forms.contrib.name.value = N;
            }*/
            if (document.forms.delform.pwd) {
                document.forms.delform.pwd.value = P;
            }
        }
    }
}

function set_cookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

//Report popup
function reppop(a) {
    day = new Date();
    id = day.getTime();
    window.open(a, id, "toolbar=0,scrollbars=0,location=0,status=1,menubar=0,resizable=1,width=685,height=225");
    return false;
}

function extraScripts() {
	var x;
	x = Core.cookie.get("loadThis");
	if (x !== null) {
        $('head').append('<script src="' + jsPath + '/' + x + '.js" type="text/javascript"></script>' );
    }
}

function toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'inherit')
        e.style.display = 'none';
    else
        e.style.display = 'inherit';
}

Core.init();