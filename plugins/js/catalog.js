function id(el) {
    return document.getElementById(el);
}
function cls(el) {
    return document.getElementsByClassName(el);
}
function remid(el) {
    document.getElementById(el).remove();
    return;
}
function toggleid(el) {
    if (id(el)) {
        remid(el);
        return false;
    } else {
        return true;
    }
}

function connect() {
    var pages, board, teste, i;

    board = window.location.href.split("/");
    i = new XMLHttpRequest();
    i.onreadystatechange = function() {
        if (i.readyState == 4 && i.status == 200) {
            var pages = JSON.parse(i.responseText);
            buildCatalog(board[3], pages);
        }
    }
    
    i.open("GET", "//api.w4ch.org/" + board[3] + "/catalog.json", true);
    i.send();
}

function buildCatalog(board, pages) {
    var pageCount, threadCount, catalogBody, z, curr, imgDir, fileDeleted, imgs;

    pageCount = pages.length;
    catalogBody = document.getElementById("catalog_container");
    
    imgDir = "//t.w4ch.org/" + board + "/";
    imgs = "//static.w4ch.org/css/imgs/";
    
    //imgs.icos = {
        stuck = "sticky.gif";
        locked = "closed.gif";
        deleted = "filedeleted.gif";
        none = "nofile.gif";
        spoiler = "spoiler.png";
    //}
    
    for (key in imgs.icos) {
        imgs.icos[key] = imgs + imgs.icos[key]; 
    }
    n = 0;
    for (n; n < pageCount; n++) {
        threadCount = false;
        threadCount = pages[n].threads.length;
        var page = pages[n];
        m = 0;
        for (m; m < threadCount; ++m) {
            curr = page.threads[m];

            z = "";
            z += "<div class='catalog-item' id='ci" + curr.no + "'>";

            z += "<a href='//w4ch.org/" + board + "/res/" + curr.no + "'>";
            curr.spoiler = false;

            if (/SPOILER<>/.test(curr.sub)) {
                curr.spoiler = true;
                curr.sub = curr.sub.replace("SPOILER<>", "");
            }

            if (curr.fsize >= 0) {
                if (curr.md5) {
                    var tn_w, tn_h;
                    tn_w = (curr.tn_w > 150) ? 150 : curr.tn_w;
                    tn_h = (curr.tn_h > 150) ? 150 : curr.tn_h;
                    
                    if (curr.spoiler !== false) {
                        z += "<img class='catalog-thumb' src='" + imgs + "spoiler.png' style='height:150px; width:150px;'>";
                    } else {
                        z += "<img class='catalog-thumb' src='" + imgDir + curr.tim + "s.jpg' data-md5='" + curr.md5 + "' height='" + tn_h + "px' width='" + tn_w + "px'>";
                    }
                } else {
                    z += "<img class='noimg' src='" + imgs.icos.none + "'>";
                }
            } else {
                z += "<img class='deleted' src='" + imgs + deleted + "'>";
            }
            z += "</a>";
            curr.replies = (curr.replies) ? curr.replies : 0;
            curr.images = (curr.images) ? curr.images : 0;
            z += "<span class='catalog-stats' id='cs" + curr.no + "' title='Reply count / Image count'> R: " + curr.replies + " / I: " +  curr.images + "</span>";
            if (curr.sub) {
                z += "<b>" + curr.sub + "</b>: ";
            }
            z += (curr.com) ? "<span class='catalog-com' id='cc" + curr.no + "'>" + curr.com + "</span>" : "";

            z += "</div>";

            catalogBody.innerHTML += z;
        }
        //alert(pages[n].threads.length);
    }
}

function cleanup() {
    var mobile = (screen.width < 495) ? true : false;

    var el;
    el = id("postForm");
    el.style.display = "none";
}

function appendToolbar() {
    var el;
    
    el = id("ctrl-top");
    
    el.innerHTML += "[<a href='javascript:void(0);' id='searchTrigger'>Search</a>]";
    id("searchTrigger").addEventListener("click", function() {
        if (toggleid("catalogSearch")) {
            el.innerHTML += " <input type='text' id='catalogSearch'>";
        }
        id("catalogSearch").addEventListener("onchange", searchCatalog());
    });
}

function searchCatalog() {
    var needle;
    needle = id("catalogSearch").value;
    console.log(needle);
}

function showSummary() {
    console.log("tes");
}

function removeSummary() {
    console.log("tes2");
}

function setupSummaries() {
    var i, z;
    z = cls("catalog-thumb");
    for (i = 0; i < z.length; i++) {
        z[i].addEventListener("onmouseover", showSummary(), false);
        z[i].addEventListener("onmouseout", removeSummary(), false);
    }
}

function toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'inherit')
        e.style.display = 'none';
    else
        e.style.display = 'inherit';
}

function init() {
    connect();
    appendToolbar();
    cleanup();
    setupSummaries();
}

document.addEventListener("DOMContentLoaded", function(){
    init();
});