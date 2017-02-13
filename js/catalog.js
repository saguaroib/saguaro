var SaguaroCat = {};

SaguaroCat.init = function() {
    SaguaroCat.build();
    //SaguaroCat.appendToolbar();
    //SaguaroCat.applySettings();

    /*document.getElementById('opComment').addEventListener('change', SaguaroCat.onOptionChange, false);
    document.getElementById('imageSize').addEventListener('change', SaguaroCat.onOptionChange, false);
    document.getElementById('typeFilter').addEventListener('change', SaguaroCat.onOptionChange, false);*/
};

SaguaroCat.onOptionChange = function() {
    var settings;

    settings = {
        opComment: document.getElementById('opComment').value,
        imageSize: document.getElementById('imageSize').value,
        typeFilter: document.getElementById('typeFilter').value
    };

    $.setItem('sug-cat-settings', JSON.stringify(settings));
    SaguaroCat.applySettings();
};

SaguaroCat.applySettings = function() {
    var settings, container, xls; 
    container = document.getElementById('catalog_container');
    settings = $.getItem('sug-cat-settings');
    if (!settings) {
        return;
    }

    settings = JSON.parse(settings);

    xls = "comment-"+ settings.opComment + " " + "img-" + settings.imageSize;

    container.className = xls;
    
};

SaguaroCat.build = function() {
    var i, z, item;

    z = "";
    for (i in catalog.threads) {
            item = catalog.threads[i];

            z += "<div class='catalog-item' id='ci" + item.no + "'>";
            z += "<a href='//" + window.SaguaroResDirSource + item.no + ".html'>";
            item.spoiler = false;

            if (/SPOILER<>/.test(item.sub)) {
                item.spoiler = true;
                item.sub = item.sub.replace("SPOILER<>", "");
            }

            if (item.fsize === undefined) {
                if (!item.filedeleted /*item.file && item.tn_w && item.tn_h*/) {
                    var tn_w, tn_h;
                    tn_w = (item.tn_w > 150) ? 150 : item.tn_w;
                    tn_h = (item.tn_h > 150) ? 150 : item.tn_h;
                    
                    imgLoc = window.SaguaroThumbSource + item.imgurl;
                    
                    if (item.spoiler !== false) {
                        z += "<img class='catalog-thumb' src='" + this.imgs + "spoiler.png' style='height:150px; width:150px;'>";
                    } else {
                        z += "<img class='catalog-thumb' src='" + imgLoc + "' height='" + tn_h + "px' width='" + tn_w + "px'>";
                    }
                } else {
                    z += "<img class='noimg' src='" + /*imgs.icos.none + */"' alt='No image available'>";
                }
            } else {
                z += "<img class='deleted' src='" + this.imgs + "'>";
            }
            z += "</a>";
            item.r = (item.r || 0);
            item.i = (item.i || 0);
            z += "<span class='catalog-stats' id='cs" + item.no + "' title='Reply count / Image count'> R: " + item.r + " / I: " +  item.i + "</span>";
            if (item.sub) {
                z += "<strong>" + item.sub + "</strong>: ";
            }
            z += (item.teaser) ? "<span class='catalog-com' id='cc" + item.no + "'>" + item.teaser + "</span>" : "";

            z += "</div>";
            ++i;
    }

    document.getElementById("catalog_container").innerHTML = z;
};

SaguaroCat.appendToolbar = function() {
    var el, html;
    
    toolbar = document.getElementById("ctrl-top");
    
    html = "<div id='featureBar' style='float:right;'>Sort by:<select id='typeFilter'><option value='bump'>Bump order</option><option value='reply'>Reply count</option><option value='last'>Last reply</option><option value='date'>Creation date</option></select>";
    html += " OP comment: <select id='opComment'><option value='on'>Show</option><option value='off'>Hide</option></select>";
    html += " Image size: <select id='imageSize'><option value='norm'>Normal</option><option value='small'>Small</option><option value='med'>Medium</option><option value='large'>Large</option></select>";
    html += "</div>";
    
    toolbar.innerHTML += html;
};

document.addEventListener("DOMContentLoaded", SaguaroCat.init);