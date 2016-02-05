
Admin = {
    init: function() {
        this.config = {
            enabled: Core.isReady(),
            selector: ".postimg"
        }
        Core && Core.info.push({
            mode: 'modern',
            menu: {
                category: 'Administrator',
                read: this.config.enabled,
                variable: 'adminToggle',
                label: 'Admin features',
                hover: 'This is an example of admin jquery options.'
            }
        });
        this.update();
    },
    update: function() {
        if(this.config.enabled) {
			//do things
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

Admin.init();
