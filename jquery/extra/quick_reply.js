//RePod - Lets you reply, quickly. Uses the based jQuery Form Plugin.
$(document).ready(function() { repod.quick_reply.init(); });
try { repod; } catch(a) { repod = {}; }
repod.quick_reply = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_quick_reply_enabled") ? repod_jsuite_getCookie("repod_quick_reply_enabled") === "true" : true,
			persist: repod.suite_settings && !!repod_jsuite_getCookie("repod_quick_reply_persist") ? repod_jsuite_getCookie("repod_quick_reply_persist") === "true" : false,
			autoreload: repod.suite_settings && !!repod_jsuite_getCookie("repod_quick_reply_autoreload") ? repod_jsuite_getCookie("repod_quick_reply_autoreload") === "true" : false,	
			op: ($("span.op_post").length == 1) ? parseInt($("span.op_post > a.qu").eq(1).text()) : false
		}
		this.details = { baseurl: "", qrbasetitle: "", basename: "", baseemail: "" }
		if (repod.suite_settings) {
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'repod_quick_reply_enabled',label:'Quick reply',hover:'Enable inline reply box'}});
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.persist,variable:'repod_quick_reply_persist',label:'Persistent quick reply',hover:'Keep quick reply window open after posting'}});
			repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.autoreload,variable:'repod_quick_reply_autoreload',label:'Auto-reload without thread updater',hover:'Auto-reload the page after posting if thread updater is not enabled'}});
		}
		this.update();
	},
	update: function() {
		if (this.config.op) {
			$(document).on("click", "a.qu:odd", function(e) {
				e.preventDefault();
				if ($("div#repod_jquery_quick_reply_container").length > 0) {
					insertAtCaret("repod_jquery_quick_reply_textarea",">>"+$(this).text()+"\n");
				} else {	
					repod.quick_reply.spawn_window($(this).text());
				}
			});
		}
	},
	spawn_window: function(input) {
		input = (input) ? ">>"+input+"\n" : "";	var op = this.config.op; 
		var qreply_clone = $("div.postarea > form").clone();
		qreply_clone.find("form").attr({"id":"repod_jquick_reply_form"});
		qreply_clone.find("td").css({"padding":"0px","margin":"0px"})
		qreply_clone.find("div.rules").parent().parent().remove();
		qreply_clone.find("input").each(function() {
			$(this).attr("placeholder",$(this).parent().prev("td").text()).removeAttr("size");
			if ($(this).attr('type') !== 'submit') { $(this).css("width","300px"); }
			if ($(this).attr('type') == 'password') { $(this).css("width","70px"); }
			if ($(this).attr('type') == 'text' && $(this).attr("name") == "sub") { $(this).css("width","225px"); }
			if ($(this).attr('name') == 'num') {
				$(this).css({"width":"100px","margin-left":"2px"}).attr("placeholder","Captcha");
				var captcha = $(this).parent().prev("td").find("img").clone();
				repod.quick_reply.details.baseurl = captcha.attr("src");
				captcha.attr({"src":repod.quick_reply.details.baseurl+"?"+new Date().getTime(),"id":"qr_captcha"}).css({"cursor":"pointer","vertical-align":"middle","margin-left":"2px"});
				$(this).parent().prev("td").remove();
				$(this).before(captcha);
			} else {
				$(this).parent().prev("td").remove();
			}
		});
		repod.quick_reply.details.qrbasetitle = "Quick Reply - Thread No. "+op;
		qreply_clone.find("textarea").attr("id","repod_jquery_quick_reply_textarea").removeAttr("cols").css({"width":"300px","min-width":"300px"}).parent().prev("td").remove();
		$("div#repod_jquery_quick_reply_container").remove();
		$("body").append("<div style='max-width:310px;position:fixed;right:0px;top:100px' id='repod_jquery_quick_reply_container' class='reply'><div id='repod_jquery_quick_reply_container_title' class='theader' style='text-align:center;width:100%;cursor:move'><small><strong>"+repod.quick_reply.details.qrbasetitle+"</strong></small><img id='r_qr_close' style='float:right;cursor:pointer;position:relative;right:5px;font-size:small' src='jquery/close.jpg' title='Close' alt='[X]'></div></div>")
		$("div#repod_jquery_quick_reply_container").append("<span style='max-width:300px' id='repod_jquery_quick_reply_window'></span>");
		$("span#repod_jquery_quick_reply_window").append(qreply_clone);
		$("img#qr_captcha").on("click", function() { $(this).attr("src",repod.quick_reply.details.baseurl+"?"+new Date().getTime()); });	
		$("#repod_jquery_quick_reply_container_title > img#r_qr_close").on("click", function() { $("div#repod_jquery_quick_reply_container").remove(); });
		if (jQuery.ui) { $("div#repod_jquery_quick_reply_container").draggable({ handle:'div#repod_jquery_quick_reply_container_title'}); } //Bind jQuery UI draggable function.
		else { $("#repod_jquery_quick_reply_container").css({"right":"0px","bottom":"100px","top":""}); } //If we don't have jQuery UI, just stick in in the bottom-right corner.
		insertAtCaret("repod_jquery_quick_reply_textarea",input);
		var options = { success: repod.quick_reply.callbacks.reply_success, resetForm: true, uploadProgress: repod.quick_reply.callbacks.upload_progress, beforeSubmit:repod.quick_reply.callbacks.beforeSubmit};
		$('#repod_jquery_quick_reply_window > form').ajaxForm(options); 
	},
	callbacks: {
		reply_success: function() {
			if (repod.quick_reply.config.persist !== true) {
				$("div#repod_jquery_quick_reply_container").remove();
				if (typeof repod.thread_updater.load_thread_url == "function") {
					repod.thread_updater.load_thread_url();
				} else {
					repod.quick_reply.config.autoreload && location.reload(); 
				}
			} 
			else {
				repod.thread_updater.load_thread_url();
				$("#repod_jquery_quick_reply_container_title > small > strong").text(repod.quick_reply.details.qrbasetitle);
				repod.quick_reply.details.basename && $("#repod_jquery_quick_reply_container").find("input[name=name]").val(repod.quick_reply.details.basename);
				repod.quick_reply.details.baseemail && $("#repod_jquery_quick_reply_container").find("input[name=email]").val(repod.quick_reply.details.baseemail);
				$("#repod_jquery_quick_reply_container").find("input[type=submit]").removeAttr("disabled");
			}
		},
		upload_progress: function(e, pos, total, pC) {
			$("#repod_jquery_quick_reply_container").find("input[type=submit]").attr("disabled","disabled");
			$("#repod_jquery_quick_reply_container_title > small > strong").text("Uploading... "+pC+"%");
		},
		beforeSubmit: function(arr, $form, options) {
			repod.quick_reply.details.basename = $("#repod_jquery_quick_reply_container").find("input[name=name]").val();
			repod.quick_reply.details.baseemail = $("#repod_jquery_quick_reply_container").find("input[name=email]").val();
		}
		
	}
}

//http://stackoverflow.com/a/1064139
function insertAtCaret(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
    	"ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
    	txtarea.focus();
    	var range = document.selection.createRange();
    	range.moveStart ('character', -txtarea.value.length);
    	strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
    	txtarea.focus();
    	var range = document.selection.createRange();
    	range.moveStart ('character', -txtarea.value.length);
    	range.moveStart ('character', strPos);
    	range.moveEnd ('character', 0);
    	range.select();
    }
    else if (br == "ff") {
    	txtarea.selectionStart = strPos;
    	txtarea.selectionEnd = strPos;
    	txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}