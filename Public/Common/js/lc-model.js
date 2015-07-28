//基类
window.LCPAGE = function() {
    return {
        /**
         * 根据url获取数据
         */
        getDataByurl: function() {
            var hash = LC.getHash();
            if (hash != null) {
                hash = hash[1];
                hash = hash.split('-');
                var id = hash[0];
                LCPAGE.menu(Gjson[id].son);
                $(".current").removeClass("current");
                $('a[data-id="' + hash[0] + '"]').addClass('current');
                $('li[data-id="' + hash[1] + '"]').addClass('current');
                var menu = $('#category li');
                menu.removeClass('none');
            } else {
                for (id in Gjson) {
                    if (id == 1) { //默认为系统管理
                        LCPAGE.menu(Gjson[id].son);
                        LCPAGE.addStyle(id);
                        break;
                    }
                }
            }
        },
        /**
         * 给选定值添加样式
         */
        addStyle: function(id) {
            var cId = '';
            if (typeof Gjson[id].son[0].son == "undefined") {
                cId = Gjson[id].son[0].id;
            } else {
                cId = Gjson[id].son[0].son[0].id;
            }
            var r = id + '-' + cId;
            LC.setR(r);

            $(".current").removeClass("current");
            $('a[data-id="' + id + '"]').addClass('current');
            $('li[data-id="' + cId + '"]').addClass('current');
            var menu = $('#category li');
            menu.removeClass('none');
        },

        /**
         * menu菜单生成树
         */
        menu: function(cateInfo) {
            var render = [];
            var getTree = function(data) {
                render.push("<ul>");
                if (data && data.length > 0) {
                    for (var content in data) {
                        var node = data[content];
                        if (node.status == 2) {
                            return true;
                        }
                        if (node.son && node.son.length > 0) { //存在子类
                            render.push('<li class="none li' + node.key + '"><h3><i class="i_icon2"></i>' + node.name + '</h3></li>');
                            arguments.callee(node.son);
                        } else {
                            render.push('<li class="none li' + node.key + '" menu="sub-' + node.key + '"  data-id="' + node.id + '"><a burl="' + node.url + '" page="' + node.page + '" href="javascript:void(0)" >' + node.name + '</a></li>');
                        }
                    }
                }
                render.push('</ul>');
            }(cateInfo);
            $("#category").html(render.join(''));
            render = [];
        },

        /**
         * 获取category 下的li 链接,发送相应请求
         */
        liClick: function() {
            var ajaxPath = $("#category .current").find("a");
            LC.show(ajaxPath, '', 'showDiv');
            LCPAGE.setBreadcrumb();
        },

        getUrlParam: function(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
            var r = window.location.search.substr(1).match(reg);
            if (r != null) return unescape(r[2]);
            return null;
        },

        /**
         * append弹层
         *
         */
        appendPop: function(showid, callback) {
            var level = showid ? showid : $(".popDiv").length;
            var popId = 'popLevel' + level;
            var showId = "showContent" + level;
            if ($("#" + popId).length == 0) {
                var str = $("<div class='popDiv' id='" + popId + "'></div>");
                str.html("<div class='pop1'><span></span><a href='javascript:void(0)' class='cls' data-id='" + popId + "'></a></div><div class='pop2 content' id='" + showId + "'></div>");
                str.appendTo($("#popLock"));
            } else {
                $("#" + popId).show();
            }
            if (typeof callback == 'function') {
                callback();
            }
            return showId;
        },

        /**
         * 显示确认框
         *
         */
        showConfirm: function(content, obj, param, callback) {
            var pop = $("#popConfirm > .popDiv");
            pop.find("div").eq(1).find("div").eq(0).html(content);
            $("#popConfirm").fadeIn(350);
        },
        /**
         * 显示弹层
         */
        showPop: function() {
            $("#popLock").fadeIn(350);
            $("#popLock").css('height', window.screen.height);
        },


        /**
         * 关闭弹层
         */
        closePop: function(popObj) {
            if (typeof popObj == 'function') { //移除当前层
                popObj();
                $("#popLock .popDiv").last().remove();
                $("#popLock .popDiv").length == 1 & $(".ajaxbox").fadeOut(350);
                return;
            }
            var popId = $(popObj).data("id");
            if (popId) { //移除指定id下的层
                $("#" + popId).remove();
                $("#popLock .popDiv").length == 0 && $(".ajaxbox").fadeOut(350);
            } else {
                $(popObj).parents('.ajaxbox').first().hide();
            }
        },

        /**
         * 设置网站导航
         */
        setBreadcrumb: function() {
            var hash = LC.getHash()[1];
            hash = hash.split('-');
            var curParent = $(".subnav .current");
            var nav_cont = "<span>" + $(".nav_cont .current").text() + "</span>";
            var subnav = "<a data-id=" + curParent.data("id") + ">" + curParent.text() + "</a>";
            var subnavFirst = "<a data-id=" + curParent.parents("ul").first().find(":first-child").data("id") + ">" + curParent.parent().prev().text() + "</a>";
            $("#breadcrumb").html(nav_cont + ' > ' + subnavFirst + '> ' + subnav);

        },
        /**
         * 警告框
         * @param {type} msg
         * @returns {undefined}
         */
        tips: function(msg) {
            $(".tips p").text(msg);
            $(".tips").animate({
                marginTop: 0,
                opacity: 'show'
            }, 1000);
            setTimeout(function() {
                $(".tips").animate({
                    marginTop: -5,
                    opacity: 'hide'
                }, "slow");
            }, 3000);
        },
        /**
         * 错误提示框
         * @param {type} msg
         * @returns {undefined}
         */
        msg: function(panel, t) {
            $("#" + panel).show().append(t).addClass('ui-state-highlight');
            setTimeout(function() {
                $("#" + panel).removeClass('ui-state-highlight').text('').hide();
            }, 3000);
        },
		/**
    	 * 登录超时
    	 */
    	overTime:function(url){
		    $('#dialog-logout').dialog({
		        modal: true,
		        show:"slide",
		        hide:"slide",
		        title: '登录超时',
		        resizable: true,
		        height:150,
		        buttons:{
		            '确定': function() {
		            	window.location = '/Login/login/refurl/'+url;
		            }
		        }
		    });
    	},

        /**
         * 弹框提示信息
         */
        dlgMsg: function(tip, title) {
            var style = 'font-family:宋体, Verdana, Arial, Helvetica, sans-serif;font-size:12px;font-weight:bold;text-align:center;padding:0 15px;line-height:20px;margin:15px 10px;';
            var title = title || "温馨提示";

            $('<div title="' + title + '"><p style="' + style + '">' + tip + '</p></div>').dialog({
                modal: true,
                //show: 'slide',
                dialogClass: "alert",
                hide: 'slide',
                buttons: {
                    '关闭': function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
    }
}();
