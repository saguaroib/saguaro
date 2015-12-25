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
        N = getCookie("saguaro_name"),
        E = getCookie("saguaro_email"),
        i;
    with(document) {
        for (i = 0; i < forms.length; i++) {
            if (document.forms.contrib) {
                document.forms.contrib.pwd.value = P;
            }
            if (document.forms.contrib.name) {
                document.forms.contrib.name.value = N;
            }
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

//Admin popup
function popup(vars) {
    day = new Date();
    id = day.getTime();
    var newWindow;
    var props = 'scrollBars=yes,resizable=no,toolbar=no,menubar=no,location=no,directories=no,width=400,height=360';
    eval('popup' + id + ' = window.open("admin.php?mode=ban&"+vars, "' + id + '", props);');
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

function toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'block')
        e.style.display = 'none';
    else
        e.style.display = 'block';
}

function checkBrowser() {
    this.ver = navigator.appVersion
    this.dom = document.getElementById ? 1 : 0
    this.ie5 = (this.ver.indexOf("MSIE 5") > -1 && this.dom) ? 1 : 0;
    this.ie4 = (document.all && !this.dom) ? 1 : 0;
    this.ns5 = (this.dom && parseInt(this.ver) >= 5) ? 1 : 0;
    this.ns4 = (document.layers && !this.dom) ? 1 : 0;
    this.bw = (this.ie5 || this.ie4 || this.ns4 || this.ns5)
    return this
}
bw = new checkBrowser()

function swap(div, div2) {
    var el = document.getElementById(div);
    var el2 = document.getElementById(div2);
    var tmp = el.style.display;
    el.style.display = el2.style.display;
    el2.style.display = tmp;
}

function more(div, div2, nest) {
    obj = bw.dom ? document.getElementById(div).style : bw.ie4 ? document.all[div].style : bw.ns4 ? nest ? document[nest].document[div] : document[div] : 0;
    obj2 = bw.dom ? document.getElementById(div2).style : bw.ie4 ? document.all[div2].style : bw.ns4 ? nest ? document[nest].document[div2] : document[div2] : 0;
    if (obj.display == '') {
        obj.display = 'none';
        obj2.display = 'none';
    } else {
        obj.display = '';
        obj2.display = '';
    }
}