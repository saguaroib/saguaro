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

function l(e) {
    var P = getCookie("saguaro_pass"),
        //N = getCookie("saguaro_name"),
        E = getCookie("saguaro_email"),
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

function getCookie(name) { //plebmode activated http://www.the-art-of-web.com/javascript/getcookie/
    var re = new RegExp(name + "=([^;]+)");
    var value = re.exec(document.cookie);
    return (value != null) ? unescape(value[1]) : null;
  }

//Report popup
function reppop(a) {
    day = new Date();
    id = day.getTime();
    window.open(a, id, "toolbar=0,scrollbars=0,location=0,status=1,menubar=0,resizable=1,width=685,height=200");
    return false;
}

function set_cookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function del_cookie(name) {
    document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
}
