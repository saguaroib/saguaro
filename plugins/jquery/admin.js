$(document).ready(function() { Admin.init(); });

Admin = {
	
	init: function() {
		try (repod.suite_settings) { Admin.repodHook.init();} catch { console.log("User suite not found, skipping.");}
		$(".cmd").click(function() { 
			var action = $(this).attr('data-cmd'), data = $(this).attr('data-id');
			Admin.cmd(action, data);
		});
	},
	
	mod: {
		del: function(data) {
			if (data) {
				$.ajax({
					url: location.href.split("/").slice(-1) + "?mode=adel&no=" + data,
					success: function() { $('#tr' + data).remove(); $("#" + data + "a").remove();$("#" + data + "b").remove();},
					error: function() { Admin.msg("Connection error.", 1);}
				});
			} else {
				Admin.msg("Error! No post # given!", 1)
			}
		},
		
		ban: function(data) {
		},
		
	},
	
	update: {
		index: function() {
			$.ajax({
				url: "imgboard.php",
				success: function() { Admin.msg("Updated index.");  },
				error: function() { Admin.msg("Connection error.", 1);}
			});
		}
	},
	
	msg: function(string, type) {
		var css = (type) ? "style='background-color:red; color:white;text-align:center;width: 300px;border-radius: 10px;margin: auto;'" : "style='background-color:green; color:white;text-align:center;width: 300px;border-radius: 10px;margin: auto;'";
		$("body").prepend("<div class='alert' " + css + ">" + string + "</div>");
		setTimeout(function(){
		$(".alert").remove(); },3000);
	},
	
	cmd: function(action, data) {
		switch(action) {
			case 'toggle':
				Admin.panel.toggle();
				break;
			case 'close':
				Admin.panel.close();
				break;
			case 'del-post':
				Admin.mod.del(data);
				break;
			case 'ban-usr':
				Admin.ban.open(data);
				break;
			case 'update-index':
				Admin.update.index();
			default:
				console.log("Error");
				break;
		}
	},
	
	repodHook: {
		config: {
			//Hook into RePod's suite.
		}
	}
}

//Admin popup
function popup(vars) {
    day = new Date();
    id = day.getTime();
    var newWindow;
    var props = 'scrollBars=yes,resizable=no,toolbar=no,menubar=no,location=no,directories=no,width=400,height=360';
    eval('popup' + id + ' = window.open("admin.php?mode=ban&"+vars, "' + id + '", props);');
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