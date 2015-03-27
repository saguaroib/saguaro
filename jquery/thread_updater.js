//RePod - Attempts to update threads without reloading the page via jQuery and AJAX.
//Interconnects with other features which are called upon adding new content to the DOM, or not.
//Want a custom function called when new posts are added? Push it to repod.thread_updater.callme.
$(document).ready(function() { repod.thread_updater.init(); });
repod_suite_settings_pusher = []; //Legacy support. New scripts should push their information to repod.thread_updater.callme instead.
try { repod; } catch(a) { repod = {}; }
repod.thread_updater = {
	init:function() {
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_updater_enabled") ? repod_jsuite_getCookie("repod_thread_updater_enabled") === "true" : true,
			auto_update: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_updater_auto_update") ? repod_jsuite_getCookie("repod_thread_updater_auto_update") === "true" : false,
			auto_scroll: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_updater_auto_scroll") ? repod_jsuite_getCookie("repod_thread_updater_auto_scroll") === "true" : false,
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
		if (repod.suite_settings) {
			repod.suite_settings.info.push({menu:{category:'Monitoring',read:this.config.enabled,variable:'repod_thread_updater_enabled',label:'Thread updater',hover:'Enable inline thread updating'}});
			repod.suite_settings.info.push({menu:{category:'Monitoring',read:this.config.auto_update,variable:'repod_thread_updater_auto_update',label:'Auto-update by default',hover:'Always auto-update threads'}});
			repod.suite_settings.info.push({menu:{category:'Monitoring',read:this.config.auto_scroll,variable:'repod_thread_updater_auto_scroll',label:'Auto-scroll with auto-updated posts',hover:'Automatically scroll the page when new posts are added'}});
		}
		this.update();
	},
	update: function() {
		if (repod.thread_updater.config.enabled && $("span.op_post > a[title='Quote']").length == 1) {
			$("a:contains('Return')").after(" / <input type='checkbox' id='updater_checkbox' "+((repod.thread_updater.config.auto_update) ? "checked" : "")+"></input> <label for='updater_checkbox'>Auto</label> <a class='update_button' href=''>Update</a> <span class='updater_timer'></span> <span class='updater_status'></span>");
		}
		$("a.update_button").on("click",function(e) { e.preventDefault(); repod.thread_updater.load_thread_url(); });
		$("input#updater_checkbox").on("click",function(event) {
			if (this.checked) {
				repod.thread_updater.timer.start();
			} else {
				repod.thread_updater.timer.stop();
			}
		});
		repod.thread_updater.config.auto_update && repod.thread_updater.timer.start();
	},
	timer: {
		check: function() {
			var timer_count = parseInt($("span.updater_timer").first().text());
			if (timer_count > 1) {
				timer_count--;
				$("span.updater_timer").text(timer_count);
			} else if (timer_count <= 1) {
				repod.thread_updater.load_thread_url();
				$("span.updater_timer").text("Updating...");
			}
		},
		start: function() {
			repod.thread_updater.advanced.current_max_delay = 10;
			$("span.updater_timer").text(repod.thread_updater.advanced.current_max_delay);
			repod.thread_updater.advanced.timer = setInterval(repod.thread_updater.timer.check,1000);
			$("input#updater_checkbox").prop('checked', true);
		},
		stop: function() {
			$("span.updater_timer").text("");
			clearInterval(repod.thread_updater.advanced.timer);
			$("input#updater_checkbox").prop('checked', false);
		}
	},
	load_thread_url: function(url) {
		url = (url) ? url : location.href;
		var do_scroll = ($(window).scrollTop() + $(window).height() == repod_jsuite_getDocHeight()) ? true : false;
		$.ajax({url:url,success:function(result){
			var counter = 0;
			$(result).find('span.thread > table').each(function(){
				if (!$("a[href='javascript:insert('>>"+$(this).find("a.qu[title='Quote']").text()+"')']:first").length) {
					counter++; repod.thread_updater.advanced.total_new++; document.title = "("+repod.thread_updater.advanced.total_new+") "+repod.thread_updater.advanced.base_title;
					$("body > form > span.thread").append($(this));
				}
			});
			if (counter > 0) {
				repod.thread_updater.advanced.max_delay = repod.thread_updater.advanced.min_delay;
				repod.thread_updater.callme.bind();
				if (repod.thread_updater.config.auto_scroll) {
					if (!tu_isVisible() && do_scroll) {
						$('html, body').scrollTop( $(document).height() - $(window).height() );
					}
				}
			} else { 
				repod.thread_updater.advanced.current_max_delay += (repod.thread_updater.advanced.current_max_delay < repod.thread_updater.advanced.max_timeout) ? repod.thread_updater.advanced.step_timeout : 0;
			}
			$("span.updater_timer").text(repod.thread_updater.advanced.current_max_delay);
		}});
	},
	callme: {
		cache: [],
		push: function(a) { this.cache.push(a); },
		callthem: function() {
		
		},
		bind: function(input) {
			$.each(repod.thread_updater.callme.cache, function(a,b) { b(); });	
		}
	}
}

//http://www.raymondcamden.com/index.cfm/2013/5/28/Using-the-Page-Visibility-API
function tu_isVisible() {
	if("webkitHidden" in document) return !document.webkitHidden;
	if("mozHidden" in document) return !document.mozHidden;
	if("hidden" in document) return !document.hidden;  
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
	if($(window).scrollTop() + $(window).height() == repod_jsuite_getDocHeight()) {
		document.title = repod.thread_updater.advanced.base_title; repod.thread_updater.advanced.total_new = 0;
	}
});