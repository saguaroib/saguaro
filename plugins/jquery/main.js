function insert(text)
{
	var textarea=document.forms.contrib.com;
	if(textarea)
	{
		if(textarea.createTextRange && textarea.caretPos) // IE
		{
			var caretPos=textarea.caretPos;
			caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==" "?text+" ":text;
		}
		else if(textarea.setSelectionRange) // Firefox
		{
			var start=textarea.selectionStart;
			var end=textarea.selectionEnd;
			textarea.value=textarea.value.substr(0,start)+text+textarea.value.substr(end);
			textarea.setSelectionRange(start+text.length,start+text.length);
		}
		else
		{
			textarea.value+=text+" ";
		}
		textarea.focus();
	}
}

function l(e) {
    var P = getCookie("saguaro_pass"),
        N = getCookie("saguaro_name"),
        i;
    with(document) {
        for (i = 0; i < forms.length; i++) {
            if (forms[i].pwd) with(forms[i]) {
                pwd.value = P;
            }
            if (forms[i].name) with(forms[i]) {
                name.value = N;
            }
        }
    }
};
onload = l;

function getCookie(key, tmp1, tmp2, xx1, xx2, xx3) {
    tmp1 = " " + document.cookie + ";";
    xx1 = xx2 = 0;
    len = tmp1.length;
    while (xx1 < len) {
        xx2 = tmp1.indexOf(";", xx1);
        tmp2 = tmp1.substring(xx1 + 1, xx2);
        xx3 = tmp2.indexOf("=");
        if (tmp2.substring(0, xx3) == key) {
            return (unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1)));
        }
        xx1 = xx2 + 1;
    }
    return ("");
}

function reppop(a) {
	day=new Date();
	id=day.getTime();
	window.open(a,id,"toolbar=0,scrollbars=0,location=0,status=1,menubar=0,resizable=1,width=685,height=200");
	return false;
}

function set_cookie(name,value,days) {
	if(days) {
		var date=new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires="; expires="+date.toGMTString();
	} else expires="";
	document.cookie=name+"="+value+expires+"; path=/";
}

function del_cookie(name) {
	document.cookie = name +'=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
} 

function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
}

function checkBrowser(){
	this.ver=navigator.appVersion
	this.dom=document.getElementById?1:0
	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom)?1:0;
	this.ie4=(document.all && !this.dom)?1:0;
	this.ns5=(this.dom && parseInt(this.ver) >= 5) ?1:0;
	this.ns4=(document.layers && !this.dom)?1:0;
	this.bw=(this.ie5 || this.ie4 || this.ns4 || this.ns5)
	return this
}
bw=new checkBrowser()

function swap(div,div2) {
	var el = document.getElementById(div);
	var el2 = document.getElementById(div2);
	var tmp = el.style.display;
	el.style.display = el2.style.display;
	el2.style.display = tmp;
}
function more(div,div2,nest){
	obj=bw.dom?document.getElementById(div).style:bw.ie4?document.all[div].style:bw.ns4?nest?document[nest].document[div]:document[div]:0;
	obj2=bw.dom?document.getElementById(div2).style:bw.ie4?document.all[div2].style:bw.ns4?nest?document[nest].document[div2]:document[div2]:0;
	if(obj.display=='') {
		obj.display='none';
		obj2.display='none';
	} else {
		obj.display='';
		obj2.display='';
	}
}

function popup(vars) {
  day = new Date();
	id = day.getTime();
	var newWindow;
	var props = 'scrollBars=yes,resizable=no,toolbar=no,menubar=no,location=no,directories=no,width=400,height=360';
	eval('popup'+id+' = window.open("admin.php?mode=ban&"+vars, "'+id+'", props);');
}