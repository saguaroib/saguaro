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