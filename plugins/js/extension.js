/* 
Original Saguaro jQuery suite by RePod of team saguaro
LICENSE: MIT - https://github.com/RePod/saguaro-jquery/blob/master/LICENSE
AUTHORS: RePod - https://github.com/RePod/saguaro-jquery/
*/
$(document).ready(function() { 
    RePod.Menu.init(); 
    RePod.PostStats.init();
    RePod.ImgExpand.init();
    RePod.ImgHover.init
    RePod.ThreadUpdater.init();
    RePod.ImgSearch.init();
    RePod.HideNoImg.init();
    RePod.HideThumbs.init();
    RePod.CustomCSS.init();
    RePod.BoardList.init();
    RePod.QR.init();
    RePod.InlineEmbed.init();
    RePod.HideThread.init();
});

RePod = {
    Menu: {
        init: function() {
            this.config = {
                width: 300, //Width of settings window and any windows spawned by it, in pixels. (default: 300)
                multi_suffix: "amount", //Suffix to combine with the prefix provided in multi*-type popups. e.g.: prefix is "test_" and suffix is "amount", the cookie containing the amount of enumerated from this prefix will be "test_amount" (default: amount)
                //At the moment multi_suffix isn't applied. The suffix applied is hard coded to "amount".
                pre_categories: ["Images","Quotes & Replying","Monitoring","Navigation","Miscellaneous"] //Categories that should be spawned in this order before everything else.
            }
            $("span.linkBar").prepend("[<a href='#' id='repod_jquery_suite_settings_open'>Settings</a>]");
            $("a#repod_jquery_suite_settings_open").click(function() { RePod.Menu.spawn.settings_window(); });
            $("div.cmd").on("click", function() {
               var data = $(this).attr("data-id");
               var action = $(this).attr("data-cmd");
               RePod.run-cmd(action, data);
            });
        },
        spawn: {
            settings_window: function() {
                $("body").append("<div id='settings_container' style='position:fixed;top:0px;left:0px;width:100%;height:100%;display:table;background-color:rgba(0,0,0,0.25);'><div style='display:table-cell;vertical-align:middle;height:inherit'><div id='settings_window' class='reply' style='max-height:480px;width:"+RePod.Menu.config.width+"px;overflow:auto;margin-left:auto;margin-right:auto;border-style:solid;border-width:1px;padding:5px 0px 5px 0px;text-align:center;'></div></div></div>");
                $("#settings_window").append("<strong>Settings</strong> <img id='close' style='float:right;cursor:pointer;position:relative;top:5px;right:5px;' src='" + jsPath + "/close.jpg' title='Close' alt='[X]'></img><hr/><div id='populated_settings' style='text-align:left;padding:0px 3px 0px 3px;margin:0px 10px;'></div><hr /><input type='submit' value='Save'> <input type='submit' value='Reset'></input><br /><span style='font-size:8px'>Utilizes Local Storage. See source for integration instructions.</span>");
                $("#settings_container").on("click", function() { $(this).remove(); });
                $("#settings_window").on("click", function(event) {	event.stopPropagation(); });
                $("img#close").on("click", function() { $("div#settings_container").remove(); });
                RePod.Menu.populate.settings_window();
            },
            popup: function(popup_data) {
                if (popup_data["type"] !== "function") {
                    $("body").append("<div id='settings_popup_container' style='position:fixed;top:0px;left:0px;width:100%;height:100%;display:table;background-color:rgba(0,0,0,0.25);'><div style='display:table-cell;vertical-align:middle;height:inherit'><div id='settings_popup_window' class='reply' style='max-height:480px;width:"+RePod.Menu.config.width+"px;overflow:auto;margin-left:auto;margin-right:auto;border-style:solid;border-width:1px;padding:5px 0px 5px 0px;text-align:center;'></div></div></div>");
                    $("#settings_popup_window").append("<strong>"+popup_data["title"]+"</strong> <img id='close' style='float:right;cursor:pointer;position:relative;top:5px;right:5px;' src='" + jsPath + "/close.jpg' title='Close' alt='[X]'></img><hr/><div id='pop_content_area' style='text-align:left;padding:0px 3px 0px 3px;'></div><hr />");
                    $("#settings_popup_container").on("click", function() { $(this).remove(); });
                    $("#settings_popup_window").on("click", function(event) { event.stopPropagation(); });
                    $("#settings_popup_window").append(RePod.Menu.populate.popup_footer(popup_data["type"]));
                    $("#settings_popup_window > input[value='Save']").data(popup_data).on("click",function() { RePod.Menu.data_manip.save.popup($(this).data()); });;
                    $("#settings_popup_window > input[value='Reset']").data(popup_data).on("click",function() { RePod.Menu.data_manip.reset.popup($(this).data()); });;
                    $("#settings_popup_window > #pop_content_area").html(RePod.Menu.populate.popup(popup_data));
                    $("#settings_popup_window > img#close, #settings_popup_window > input[value='Close']").on("click", function() { $("div#settings_popup_window").remove(); });
                } else {
                    repod_jsuite_executeFunctionByName(popup_data["variable"],window);
                }
            }
        },
        populate: {
            settings_window: function() {
                $.each(RePod.Menu.config.pre_categories, function(a,b) { RePod.Menu.populate.spawn_category(b); });
                this.iterate(RePod.Menu.info.cache);
                $("#populated_settings > div > strong").on("click", function() { $("#"+$(this).text().replace(/[\A\W]/g,"-")+" > span").slideToggle(); });
                $("#settings_window > strong").on("click", function() { if ($("#populated_settings > div > span").first().css("display") == "none") { $("#populated_settings > div > span").slideDown(); } else { $("#populated_settings > div > span").slideUp(); } });
                $("div.grouptoggle > a").on("click", function() { $("#"+$(this).parent().prev("strong").text().replace(/[\A\W]/g,"-")+" > span > input[type='checkbox']").prop("checked",($(this).text() == "On")?"checked":""); });
                $("#settings_window > input[value='Save']").on("click", function() { RePod.Menu.data_manip.save.settings_window(); });
                $("#settings_window > input[value='Reset']").on("click", function() { RePod.Menu.data_manip.reset.settings_window(); });
                $(".settings_popup").on("click", function() { RePod.Menu.spawn.popup($(this).data()); });
            },
            iterate: function(a) {
                $.each(a,function(a,b) {
                    if (b['menu']) {
                        var cat, cat_safe, tvar, name, desc;
                        var cat = b['menu']['category']; var cat_safe = cat.replace(/[\A\W]/g,"-");
                        var tvar = b['menu']['variable']; var name = b['menu']['label']; var desc = (b['menu']['hover']) ? b['menu']['hover'].replace(/'/g, "&apos;").replace(/"/g, "&quot;") : "";
                        if (cat && tvar && name) {
                            RePod.Menu.populate.spawn_category(cat);
                            $("#populated_settings > #"+cat_safe).show();
                            if (b['menu']['read']) { var c = (b['menu']['read'] === true) ? "checked" : "" } else { var c = (getItem(tvar) === "true") ? "checked" : ""; }
                            var popup = (b['popup']) ? b['popup'] : "";
                            var n = $(".settings_popup").size();
                            popup = (popup['label'] && popup['title'] && popup['type'] && popup['variable']) ? " <a href='#' class='settings_popup' id='settings_popup_"+n+"'>"+popup['label']+"</a>" : "";
                            $("#populated_settings > #"+cat_safe+" > span").append("<input id='"+tvar+"' "+c+" type='checkbox'><label for='"+tvar+"'>"+name + popup +"</label><br><span style='font-size:11px;font-style:italic;margin-left:6%;'>"+desc+"</span><br />").css("display", "none");
                            popup !== "" && $("#settings_popup_"+n).data(b['popup']);
                        }
                    }
                });
            },
            spawn_category: function(cat,cat_safe) {
                cat_safe = (cat_safe) ? cat_safe : cat.replace(/[\A\W]/g,"-");
                !$("#populated_settings > #"+cat_safe).length && $("#populated_settings").append("<div id='"+cat_safe+"' style='display:none'>[<strong>"+cat+"</strong>]<div class='grouptoggle' style='font-size:11px;display:inline;'> <a href='#'>On</a> | <a href='#'>Off</a></div><br /><span></span></div>"); 
            },
            popup: function(popup_data) {
                //Shortcuts:
                var v = popup_data["variable"]; // Variable name/"variable" key.
                var d = (getItem(v)) ? getItem(v) : ""; //Variable contents.
                var p = (popup_data['placeholder']) ? popup_data['placeholder'] : ""; //Specified placeholder.
                var w = ($("#settings_popup_window > #pop_content_area").width() - parseInt($("#settings_popup_window > #pop_content_area").css("padding-right")) - parseInt($("#settings_popup_window > #pop_content_area").css("padding-left"))); //Fancy inner width.
                var output = "";
                if (popup_data['type'] == "textarea") { output = '<textarea id="'+popup_data["variable"]+'" placeholder="'+p+'" style="width:'+w+'px">'+d+'</textarea>'; }
                if (popup_data['type'] == "text") { output = '<input id="'+v+'" type="text" placeholder="'+p+'" value="'+d+'" style="width:'+w+'px"></input>'; }
                if (popup_data['type'] == "info") { output = v; } 
                if (popup_data['type'] == "multitext") {
                    if (popup_data['variable']['prefix'] && popup_data['variable']['data'].length > 0) {
                        $.each(popup_data['variable']['data'],function(a,b) {
                            d = (getItem(popup_data['variable']['prefix']+a)) ? getItem(popup_data['variable']['prefix']+a) : "";
                            output += '<input id="'+popup_data['variable']['prefix']+a+'" type="text" placeholder="'+popup_data['variable']['data'][a]+'" value="'+d+'" style="width:'+w+'px;margin-bottom:2px;"></input>';
                        });
                    } //else { output = "This multitext is not formatted properly."; }
                }			
                return output;				
            },
            popup_footer: function (type) {
                var o;
                if (type == "info") { o = "<input type='submit' value='Close'>"; }
                else {
                    o = "<input type='submit' value='Save'> <input type='submit' value='Reset'></input>";				
                }			
                return o;
            }
        },
        data_manip: {
            save: {
                settings_window: function() {
                    $("#populated_settings > div > span > input[type='checkbox']").each(function() {
                        v1 = $(this).attr("id"); v2 = (($(this).prop("checked")) ? true : false);
                        repod_jsuite_setCookie(v1,v2,7);
                    });
                    location.reload();
                },
                popup: function(popup_data) {
                    var c = 0;
                    $("#settings_popup_window > #pop_content_area > :input").each(function() {
                        c++; // Original joke.
                        repod_jsuite_setCookie($(this).attr("id"),$(this).val(),7);
                    });
                    if (c > 0 && popup_data['type'] == 'multitext') { repod_jsuite_setCookie(popup_data['variable']['prefix']+"amount",c,7); }
                    $("div#settings_popup_container").remove();
                }
            },
            reset: {
                settings_window: function() { $("#populated_settings > div > span > input[type='checkbox']").each(function() { v1 = $(this).attr("id"); repod_jsuite_setCookie(v1,"",-1); });	location.reload(); },
                popup: function(popup_data) {
                    $("#settings_popup_window > #pop_content_area > :input").each(function() {
                        repod_jsuite_setCookie($(this).attr("id"),"",-1);
                    });
                    $("div#settings_popup_container").remove();
                }
            }
        },
        info: {
            cache: [],
            push: function(a) { this.cache.push(a); }
        }
    },
    
    PostStats: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("repod_thread_stats_enabled") ? getItem("repod_thread_stats_enabled") === "true" : true
            };
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Monitoring',read:this.config.enabled,variable:'repod_thread_stats_enabled',label:'Thread statistics',hover:'Display reply & image count'}});
            this.update();
        },
        update: function() {
            if (RePod.PostStats.config.enabled && $("div.theader").length) {
                $("div.navLinks").css("float","left");
                $("span#repod_thread_stats_container").length == 0 && $("div.navLinks").after("&nbsp;<span id='repod_thread_stats_container'></span>");
                $("span#repod_thread_stats_container").html(RePod.PostStats.format());
            }
        },
        format: function() { return "[" + $("div.post.reply").length + " replies] [" + $("a > img.postimg").length + " images]";  }
    },
    
    ImgExpand: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("repod_ImgExpand_enabled") ? getItem("repod_ImgExpand_enabled") === "true" : true,
                selector: ".postimg"
            }
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Images',read:this.config.enabled,variable:'repod_ImgExpand_enabled',label:'Image expansion',hover:'Enable inline image expansion.'}});
            this.update();
        },
        update: function() {
            var that = this;
            this.config.enabled && $("div.threadnav").length && $(document).on("click", this.config.selector, function(event) { that.check_image(event,$(this)) });
        },
        check_image: function(event,e) {
            event.preventDefault();
            if (/\.webm$/.test($(e).parent().attr("href"))) {
                $(e).data("o-s") ? this.shrink_video(e) : this.expand_video(e);
            } else {
                $(e).data("o-s") ? this.shrink_image(e) : this.expand_image(e);
            }
            $("#img_hover_element").remove();
        },
        expand_image: function(e) {
            $(e).data({"o-h":$(e).css("height"),"o-w":$(e).css("width"),"o-s":$(e).attr("src")}).css({"max-width":(Math.round($("body").width() - ($(e).parent().parent().offset().left * 2))),"width":"auto","height":"auto"});
            var mp = $(e).parent().attr("href"); mp !== $(e).attr("src") && $(e).attr("src",mp);
        },
        shrink_image: function(e) {
            $(e).attr("src",$(this).data("o-s"));
            $(e).css({"max-height":"","max-width":"","width":$(e).data("o-w")}).attr("src",$(e).data("o-s")).removeData();
        },
        expand_video: function(e) {
            $(e).data({"o-s": true, "name": $(e).attr("src").split("/").pop().split(".")[0]}).hide();
            $(e).parent().after("<video class='expandedwebm-" + $(e).data("name") +"' loop autoplay controls src='" + $(e).parent().attr("href") + "'></video>");
        },
        shrink_video: function(e) {
            $(e).data({"o-s": false}).show();
            $(".expandedwebm-" + $(e).data("name")).remove();
        }
    },
    
    ImgHover: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !Core.isMobile() && !!getItem("repod_ImgHover_enabled") ? getItem("repod_ImgHover_enabled") === "true" : true,
                selector: ".postimg"
            }
            RePod.Menu && RePod.Menu.info.push({mode:'modern',menu:{category:'Images',read:this.config.enabled,variable:'repod_ImgHover_enabled',label:'Image hover',hover:'Expand images on mouseover.'}});
            this.update();
        },
        update: function() {
            var that = this;
            if (this.config.enabled) {
                $(document).on("mouseover", this.config.selector, function() { that.display($(this)); });
                $(document).on("mouseout", this.config.selector, function() { that.remove_display() });
            }
        },
        display: function(e) {
            if (!$(e).data("o-s")) {
                var element = $('<div id="img_hover_element" />');
                var css = {right:"0px",top:"0px",position:"fixed",width:"auto",height:"auto","max-height":"100%","max-width":Math.round($("body").width() - ($(e).offset().left + $(e).outerWidth(true)) + 20) + "px"}
                
                if (/\.webm$/.test($(e).parent().attr("href"))) {
                    $(element).append("<video class='expandedwebm-" + $(e).data("name") +"' loop autoplay src='" + $(e).parent().attr("href") + "'></video>");
                } else {
                    var img = $("<img src='"+$(e).parent().attr("href")+"'/>");
                    $(img).css(css);
                    $(element).append(img);
                }
                
                $(element).css(css);
                $("body").append(element);
            }
        },
        remove_display: function() { $("#img_hover_element").remove(); }
    },
    
    ImgSearch: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("repod_image_search_enabled") ? getItem("repod_image_search_enabled") === "true" : true,
                selector: "div.post"
            }
            RePod.Menu && RePod.Menu.info.push({
                mode: 'modern',
                menu: {
                    category: 'Images',
                    read: this.config.enabled,
                    variable: 'repod_image_search_enabled',
                    label: 'Image search',
                    hover: 'Post settings toolbox.'
                }
            });
            this.update();
        },
        update: function() {
            var that = this;
            if (that.config.enabled) {
                $(that.config.selector).each(function() {
                    $(this).find(".postInfo").append(that.format($(this)));
                });
            }

            //Binds
            $(document).on("click", "a.menu.closed", function(e) {
                e.preventDefault();
                that.menu.open(this);
            });

            $(document).on("click", "a.menu.open", function(e) {
                e.preventDefault();
                that.menu.close();
                $(this).removeClass("open").addClass("closed");
            });

            $(document).on("click", "div.menu a[data-cmd=report]", function(e) {
                e.preventDefault();
                that.report(this);
            });
        },
        menu: {
            open: function(a) {
                this.close(); //Close existing menu.
                $(a).removeClass("closed").addClass("open");

                var t = $(a).parent().parent().parent(),
                    o = $(a).position();

                //Generate and display menu.
                $('body').append(
                    $(this.gen(a, t)).css({
                        'position': 'absolute',
                        'top': (o.top + $(a).height()) + "px",
                        'left': o.left + "px",
                        'border': '1px solid black',
                        'padding': '3px',
                        'margin': '3px'
                    })
                );
            },
            close: function() {
                $(".menu.gen").remove();
                $("a.menu.open").removeClass("open").addClass("closed");
            },
            getInfo: function(a) {
                return {
                    'no': $(a).find(".postInfo > input[type=checkbox]").attr("name"),
                    'image': $(a).find("div.fileThumb > a").attr("href"),
                    'thumb': $(a).find("div.fileThumb > a > img").attr("src")
                }
            },
            gen: function(a, target) {
                var temp = $('<div />', {
                        class: 'menu gen reply'
                    }),
                    info = this.getInfo(target);

                temp.append($('<div />').append($("<a />", {
                    'data-cmd': 'report',
                    'data-target': info.no,
                    'href': '#',
                    'text': 'Report this post'
                })));

                if (info.image) {
                    temp.append($('<hr />'));

                    var ext = {
                        'Google': '//www.google.com/searchbyimage?image_url={url}',
                        'IQDB': '//iqdb.org/?url={url}',
                        'Waifu2X': '//waifu2x.booru.pics/Home/fromlink?denoise=1&scale=2&url={url}'
                    }

                    $.each(ext, function(name, path) {
                        var path2 = ((new RegExp("^" + location.protocol).test(info.image)) ? "" : location.protocol) + info.image,
                            path = path.replace("{url}", path2),
                            item = $('<div />').append(
                                $("<a />", {
                                    'text': name,
                                    'href': path,
                                    'target': '_blank'
                                })
                            );

                        temp.append(item);
                    });
                }

                return temp[0].outerHTML;
            }
        },
        format: function(a) {
            return " <a data-target='" + $(a).attr("id") + "' href='#' class='menu closed'>&#9776;</a>"
        },
        report: function(a) {
            var no = $(a).data('target');
            
            Core.frame.toggle(site + "/" + board + "/" + phpself + '?mode=report&no=' + no, 225, 685, "Report", no);
        }
    },
    
    HideNoImg: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("r_jq_HideNoImg") ? getItem("r_jq_HideNoImg") === "true" : false
            }
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Images',read:this.config.enabled,variable:'r_jq_HideNoImg',label:'Hide non-image posts',hover:'Hide all posts without images.'}});
            RePod.Updater && RePod.Updater.callme.push(RePod.HideNoImg.update);
            this.update();
        },
        update: function() {
            RePod.HideNoImg.config.enabled && $("div.postContainer.replyContainer:not(:has(img))").css("display","none");
        }
    },
    
    HideThumbs: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("r_jq_HideThumbs") ? getItem("r_jq_HideThumbs") === "true" : false
            }
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Images',read:this.config.enabled,variable:'r_jq_HideThumbs',label:'Hide image thumbnails',hover:'Hide thumbnails while browsing.'}});
            RePod.Updater && RePod.Updater.callme.push(RePod.HideThumbs.update);
            this.update();
        },
        update: function() {
            RePod.HideThumbs.config.enabled && $("div.file").css("display","none") && $("div.fileThumb").css("display","none");
        }
    },
    
    HideThread: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("repod_hideThread") ? getItem("repod_hideThread") === "true" : false
            }
            RePod.Menu && RePod.Menu.info.push({
                menu: {
                    category: 'Filtering and Hiding',
                    read: this.config.enabled,
                    variable: 'repod_hideThread',
                    label: 'Enable thread hiding',
                    hover: 'Hide threads on a page.'
                }
            });
            RePod.Menu && RePod.Menu.info.push({
                menu: {
                    category: 'Filtering and Hiding',
                    read: this.config.enabled,
                    variable: 'repod_hideThreadStubs',
                    label: 'Hide thread stubs',
                    hover: 'Remove hidden thread stubs from view.'
                }
            });
            this.update();
        },
        
        hide: function() {
            
        },
        
        update: function() {
            if (RePod.HideThread.enabled) {
                $("div.sideArrows").each(function() {
                    var did = $(this).attr("id").substring(2);
                    $(this).html("<a class='cmd' data-id='" + did + "' data-cmd='hide-thread' >[-]</a>");
                });
            }
        }
    },
    
    InlineEmbed: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !Core.isMobile() && !!getItem("repod_yt_embed_expander") ? getItem("repod_yt_embed_expander") === "true" : true
            };
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Images',read:this.config.enabled,variable:'repod_yt_embed_expander',label:'Play YouTube videos inline',hover:'Embed videos inline when clicked'}});
            if (RePod.InlineEmbed.config.enabled) {
                $("a.video").on("click", this.expand());
            }
        },
        expand: function() {
            $(this).replaceWith('<iframe class="postimg video open" style="margin: 0px 20px" type="text/html" '+
				'width="'+ 250 +'" height="'+ 250 +'" src="//www.youtube.com/embed/' + $(this).attr('v-id') +
				'?autoplay=1&html5=1'+  +'" allowfullscreen frameborder="0"/>');
        }
    },
    
    CustomCSS: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && getItem("CustomCSS_enabled") ? getItem("CustomCSS_enabled") === "true" : false
            }
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'CustomCSS_enabled',label:'Custom CSS',hover:'Include your own CSS rules'},popup:{label:'[Edit]',title:'Custom CSS',type:'textarea',variable:'CustomCSS_defined',placeholder:'Input custom CSS here.'}});
            this.update();
        },
        update: function() {
            if (RePod.CustomCSS.config.enabled) {
                $("<style type='text/css'>"+getItem("CustomCSS_defined")+"</style>").appendTo("head");
            }
        }
    },
    
    BoardList: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("BoardList_enabled") ? getItem("BoardList_enabled") === "true" : false,
                original: $("span.boardlist:first").html()
            }
            RePod.Menu && RePod.Menu.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'BoardList_enabled',label:'Custom board list',hover:'Only show selected boards in top and bottom board lists'},popup:{label:'[Edit]',title:'Custom Board List',type:'text',variable:'BoardList_defined',placeholder:'Example: a b c http://u.rl|Display'}});
            this.update();
        },
        update: function() {
            if (this.config.enabled && !!getItem("BoardList_defined")) {
                $(".boardlist").html(this.format());
                $(".BoardList_all").on("click", function() { $(".boardlist").html(RePod.BoardList.config.original); });
            }
        },
        format: function(a) {
            var c_bl_a = getItem("BoardList_defined").split(" ");
            $.each(c_bl_a,function(i,v) {
                var r = /([a-z0-9]+:\/\/\S+\.[a-z]{2,}\/?\S*?)\|(.+)/i;
                if (r.test(v)) {
                    r = r.exec(v); c_bl_a[i] = "<a href='"+r[1]+"'>"+r[2]+"</a>";
                } else {
                    c_bl_a[i] = "<a href='../"+v+"'>"+v+"</a>";
                }
            });
            c_bl_a = "["+c_bl_a.join(" / ")+"] [<span class='BoardList_all' style='cursor:pointer' title='Show original'>...</span>]";
            return c_bl_a;
        }
    },
    
    ThreadUpdater: {
        init: function() {
        this.config = {
            enabled: RePod && !!getItem("repod_thread_updater_enabled") ? getItem("repod_thread_updater_enabled") === "true" : true,
            auto_update: RePod && !!getItem("repod_thread_updater_auto_update") ? getItem("repod_thread_updater_auto_update") === "true" : false,
            auto_scroll: RePod && !!getItem("repod_thread_updater_auto_scroll") ? getItem("repod_thread_updater_auto_scroll") === "true" : false,
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
        if (RePod.Menu) {
            RePod.Menu.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.enabled,
                    variable: 'repod_thread_updater_enabled',
                    label: 'Thread updater',
                    hover: 'Enable inline thread updating'
                }
            });
            RePod.Menu.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.auto_update,
                    variable: 'repod_thread_updater_auto_update',
                    label: 'Auto-update by default',
                    hover: 'Always auto-update threads'
                }
            });
            RePod.Menu.info.push({
                menu: {
                    category: 'Monitoring',
                    read: this.config.auto_scroll,
                    variable: 'repod_thread_updater_auto_scroll',
                    label: 'Auto-scroll with auto-updated posts',
                    hover: 'Automatically scroll the page when new posts are added'
                }
            });
        }
        this.update();
        },
        update: function() {
            if ($("div.theader").length) {
                $("a:contains('Return')").after(" / <input type='checkbox' id='updater_checkbox' " + ((RePod.ThreadUpdater.config.auto_update) ? "checked" : "") + "></input> <label for='updater_checkbox'>Auto</label> <a class='update_button' href=''>Update</a> <span class='updater_timer'></span> <span class='updater_status'></span>");
            }
            $("a.update_button").on("click", function(e) {
                e.preventDefault();
                RePod.ThreadUpdater.load_thread_url();
            });
            $("input#updater_checkbox").on("click", function(event) {
                if (this.checked) {
                    RePod.ThreadUpdater.timer.start();
                } else {
                    RePod.ThreadUpdater.timer.stop();
                }
            });
            RePod.ThreadUpdater.config.auto_update && RePod.ThreadUpdater.timer.start();
        },
        timer: {
            check: function() {
                var timer_count = parseInt($("span.updater_timer").first().text());
                if (timer_count > 1) {
                    timer_count--;
                    $("span.updater_timer").text(timer_count);
                } else if (timer_count <= 1) {
                    RePod.ThreadUpdater.load_thread_url();
                    $("span.updater_timer").text("Updating...");
                }
            },
            start: function() {
                RePod.ThreadUpdater.advanced.current_max_delay = 10;
                $("span.updater_timer").text(RePod.ThreadUpdater.advanced.current_max_delay);
                RePod.ThreadUpdater.advanced.timer = setInterval(RePod.ThreadUpdater.timer.check, 1000);
                $("input#updater_checkbox").prop('checked', true);
            },
            stop: function() {
                $("span.updater_timer").text("");
                clearInterval(RePod.ThreadUpdater.advanced.timer);
                $("input#updater_checkbox").prop('checked', false);
            }
        },
        load_thread_url: function(url) {
            url = location.href;
            var do_scroll = ($(window).scrollTop() + $(window).height() == repod_jsuite_getDocHeight()) ? true : false;
            $.ajax({
                url: url,
                success: function(result) {
                    var counter = 0;
                    $(result).find('div.thread > div.postContainer.replyContainer').each(function() {
                        if ($("div.theader").length) {
                            counter++;
                            RePod.ThreadUpdater.advanced.total_new++;
                            document.title = "(" + RePod.ThreadUpdater.advanced.total_new + ") " + RePod.ThreadUpdater.advanced.base_title;
                            $("body > form > div.thread").append($(this));
                        }
                    });
                    if (counter > 0) {
                        RePod.ThreadUpdater.advanced.max_delay = RePod.ThreadUpdater.advanced.min_delay;
                        RePod.ThreadUpdater.callme.bind();
                        if (RePod.ThreadUpdater.config.auto_scroll) {
                            if (!tu_isVisible() && do_scroll) {
                                $('html, body').scrollTop($(document).height() - $(window).height());
                            }
                        }
                    } else {
                        RePod.ThreadUpdater.advanced.current_max_delay += (RePod.ThreadUpdater.advanced.current_max_delay < RePod.ThreadUpdater.advanced.max_timeout) ? RePod.ThreadUpdater.advanced.step_timeout : 0;
                    }
                    $("span.updater_timer").text(RePod.ThreadUpdater.advanced.current_max_delay);
                }
            });
        },
        callme: {
            cache: [],
            push: function(a) {
                this.cache.push(a);
            },
            callthem: function() {

            },
            bind: function(input) {
                $.each(RePod.ThreadUpdater.callme.cache, function(a, b) {
                    b();
                });
            }
        }
    },
    
    QR: {
        init: function() {
            this.config = {
                enabled: RePod.Menu && !!getItem("repod_QR_enabled") ? getItem("repod_QR_enabled") === "true" : true,
                persist: RePod.Menu && !!getItem("repod_QR_persist") ? getItem("repod_QR_persist") === "true" : false,
                autoreload: RePod.Menu && !!getItem("repod_QR_autoreload") ? getItem("repod_QR_autoreload") === "true" : false,
                op: $("a.quotejs").closest(".thread").attr("id").substring(1)
            }
            this.details = {
                baseurl: "",
                qrbasetitle: "",
                basename: "",
                baseemail: ""
            }
            if (RePod.Menu) {
                RePod.Menu.info.push({
                    menu: {
                        category: 'Quotes & Replying',
                        read: this.config.enabled,
                        variable: 'repod_QR_enabled',
                        label: 'Quick reply',
                        hover: 'Enable inline reply box'
                    }
                });
                RePod.Menu.info.push({
                    menu: {
                        category: 'Quotes & Replying',
                        read: this.config.persist,
                        variable: 'repod_QR_persist',
                        label: 'Persistent quick reply',
                        hover: 'Keep quick reply window open after posting'
                    }
                });
                RePod.Menu.info.push({
                    menu: {
                        category: 'Quotes & Replying',
                        read: this.config.autoreload,
                        variable: 'repod_QR_autoreload',
                        label: 'Auto-reload without thread updater',
                        hover: 'Auto-reload the page after posting.'
                    }
                });
            }
            this.update();
        },
        update: function() {
            if (this.config.op) {
                $(document).on("click", "a.quotejs", function(e) {
                    e.preventDefault();
                    if ($("div#repod_jquery_QR_container").length > 0) {
                        insertAtCaret("repod_jquery_QR_textarea", ">>" + $(this).text() + "\n");
                    } else {
                        RePod.QR.spawn_window($(this).text());
                    }
                });
            }
        },
        spawn_window: function(input) {
            input = (input) ? ">>" + input + "\n" : "";
            var op = this.config.op;
            var qreply_clone = $("div.postarea > form").clone();
            qreply_clone.find("form").attr({
                "id": "repod_jQR_form"
            });
            qreply_clone.find("td").css({
                "padding": "0px",
                "margin": "0px"
            })
            qreply_clone.find("div.rules").parent().parent().remove();
            qreply_clone.find("input").each(function() {
                $(this).attr("placeholder", $(this).parent().prev("td").text()).removeAttr("size");
                if ($(this).attr('type') !== 'submit') {
                    $(this).css("width", "300px");
                }
                if ($(this).attr('type') == 'password') {
                    $(this).css("width", "70px");
                }
                if ($(this).attr('type') == 'text' && $(this).attr("name") == "sub") {
                    $(this).css("width", "225px");
                }
                if ($(this).attr('name') == 'num') {
                    $(this).css({
                        "width": "100px",
                        "margin-left": "2px"
                    }).attr("placeholder", "Captcha");
                    var captcha = $(this).parent().prev("td").find("img").clone();
                    RePod.QR.details.baseurl = captcha.attr("src");
                    captcha.attr({
                        "src": RePod.QR.details.baseurl + "?" + new Date().getTime(),
                        "id": "qr_captcha"
                    }).css({
                        "cursor": "pointer",
                        "vertical-align": "middle",
                        "margin-left": "2px"
                    });
                    $(this).parent().prev("td").remove();
                    $(this).before(captcha);
                } else {
                    $(this).parent().prev("td").remove();
                }
            });
            RePod.QR.details.qrbasetitle = "Quick Reply - Thread No. " + op;
            qreply_clone.find("textarea").attr("id", "repod_jquery_QR_textarea").removeAttr("cols").css({
                "width": "300px",
                "min-width": "300px"
            }).parent().prev("td").remove();
            $("div#repod_jquery_QR_container").remove();
            $("body").append("<div style='max-width:310px;position:fixed;right:0px;top:100px' id='repod_jquery_QR_container' class='reply'><div id='repod_jquery_QR_container_title' class='theader' style='text-align:center;width:100%;cursor:move'><small><strong>" + RePod.QR.details.qrbasetitle + "</strong></small><img id='r_qr_close' style='float:right;cursor:pointer;position:relative;right:5px;font-size:small' src='" + jsPath + "/close.jpg' title='Close' alt='[X]'></div></div>")
            $("div#repod_jquery_QR_container").append("<span style='max-width:300px' id='repod_jquery_QR_window'></span>");
            $("span#repod_jquery_QR_window").append(qreply_clone);
            $("table").append("<input type='hidden' name='resto' value='" + op + "'>");
            $("img#qr_captcha").on("click", function() {
                $(this).attr("src", RePod.QR.details.baseurl + "?" + new Date().getTime());
            });
            $("#repod_jquery_QR_container_title > img#r_qr_close").on("click", function() {
                $("div#repod_jquery_QR_container").remove();
            });
            if (jQuery.ui) {
                $("div#repod_jquery_QR_container").draggable({
                    handle: 'div#repod_jquery_QR_container_title'
                });
            } //Bind jQuery UI draggable function.
            else {
                $("#repod_jquery_QR_container").css({
                    "right": "0px",
                    "bottom": "100px",
                    "top": ""
                });
            } //If we don't have jQuery UI, just stick in in the bottom-right corner.
            insertAtCaret("repod_jquery_QR_textarea", input);
            var options = {
                success: RePod.QR.callbacks.reply_success,
                resetForm: true,
                uploadProgress: RePod.QR.callbacks.upload_progress,
                beforeSubmit: RePod.QR.callbacks.beforeSubmit
            };
            $('#repod_jquery_QR_window > form').ajaxForm(options);
        },
        callbacks: {
            reply_success: function() {
                if (RePod.QR.config.persist !== true) {
                    $("div#repod_jquery_QR_container").remove();
                    if (typeof RePod.Updater.load_thread_url == "function") {
                        RePod.Updater.load_thread_url();
                    } else {
                        RePod.QR.config.autoreload && location.reload();
                    }
                } else {
                    RePod.Updater.load_thread_url();
                    $("#repod_jquery_QR_container_title > small > strong").text(RePod.QR.details.qrbasetitle);
                    RePod.QR.details.basename && $("#repod_jquery_QR_container").find("input[name=name]").val(RePod.QR.details.basename);
                    RePod.QR.details.baseemail && $("#repod_jquery_QR_container").find("input[name=email]").val(RePod.QR.details.baseemail);
                    $("#repod_jquery_QR_container").find("input[type=submit]").removeAttr("disabled");
                }
            },
            upload_progress: function(e, pos, total, pC) {
                $("#repod_jquery_QR_container").find("input[type=submit]").attr("disabled", "disabled");
                $("#repod_jquery_QR_container_title > small > strong").text("Uploading... " + pC + "%");
            },
            beforeSubmit: function(arr, $form, options) {
                RePod.QR.details.basename = $("#repod_jquery_QR_container").find("input[name=name]").val();
                RePod.QR.details.baseemail = $("#repod_jquery_QR_container").find("input[name=email]").val();
            }

        }
    },
    
    run_cmd: function(action, data) {
        switch(action) {
            case 'hide-thread': 
                RePod.HideThread.hide(data);
                break;
            default:
                break;
        }
    }
};

function repod_jsuite_setCookie(c_name,value,exdays) {
    if (exdays == -1) {
        localStorage.removeItem(c_name);
    } else {
        localStorage[c_name] = value; 
    }
}
function getItem(c_name) {
    return localStorage[c_name];
}

//http://stackoverflow.com/a/1064139
function insertAtCaret(areaId, text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
        "ff" : (document.selection ? "ie" : false));
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
    } else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        range.moveStart('character', strPos);
        range.moveEnd('character', 0);
        range.select();
    } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}

//http://www.raymondcamden.com/index.cfm/2013/5/28/Using-the-Page-Visibility-API
function tu_isVisible() {
    if ("webkitHidden" in document) return !document.webkitHidden;
    if ("mozHidden" in document) return !document.mozHidden;
    if ("hidden" in document) return !document.hidden;
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

//http://stackoverflow.com/a/359910
function repod_jsuite_executeFunctionByName(functionName, context /*, args */) {
	var args = [].slice.call(arguments).splice(2);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(this);
}
