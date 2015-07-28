$(document).ready(function() {
    window.pageParam = {};
    /**
     * 以下代码与分类与url定位相关
     *
     */
    $(".menu-value").click(function() {
        var mid = $(this).data('id');
        LCPAGE.menu(Gjson[mid].son);
        LCPAGE.addStyle(mid);
    });

    //左边导航的伸展功能实现
    $('#category').delegate('li', 'click', function(e) {
        ($(this).attr('class') == 'li2') ? $(this).next('ul').toggle(): '';
        if ($(this).data("id")) {
            $("#category li .current").removeClass('current');
            var mid = $(this).data('id');
            $('li [data-id="' + mid + '"]').addClass('current');

            var hMid = $('#nav li .current').data('id');
            var r = hMid + "-" + mid;
            LC.setR(r);
        }
        //		event.stopPropagation();
    });

    //导航菜单
    $('#breadcrumb').delegate('a', 'click', function(e) {
        if ($(this).data("id")) {
            $("#category li .current").removeClass('current');
            var mid = $(this).data('id');
            $('li [data-id="' + mid + '"]').addClass('current');

            var hMid = $('#nav li .current').data('id');
            var r = hMid + "-" + mid;
            LC.setR(r);
        }
        //		event.stopPropagation();
    });


    //数据保存 搜索
    $(document).delegate("#lcsubmit input[type='submit']", 'click', function() {
        var postParam = $(this).parents("#lcsubmit");
        var showId = $(this).parents(".content").attr("id");
        var paramId = $(this).parents(".form").attr("id"); //由于form表单内id 唯一, 而此参数是拿某个id内的所有参数,当出现弹层时,会有两个#lcsubmit,故加.form类
        if (window.pageFunc && typeof(window.pageFunc) == 'function') {
            param = {
                callback: window.pageFunc
            }
        } else param = {};
        LC.show($(postParam), paramId, showId, null, null, param);
        return false;
    });

    //新增tr
    $(document).delegate(".addTr", 'click', function() {
        var curDivObj = $(this).parents(".toolbar").next();
        var showObj = curDivObj.find(".dataTab").children().first().clone();
        showObj.prependTo(curDivObj.find("tbody"));
        showObj.find("input,select[disabled='disabled']").attr("disabled", false);
        showObj.find("input,select[disabled='disabled']").val("");
        showObj.show();
    });

    //数据修改
    $(document).delegate(".editTr", 'click', function() {
        var isedit = $(this).attr('isedit');
        var trObj = $(this).parents("tr").first().find("input,select");
        if (isedit == '0') {
            $(this).text("保存");
            $(this).attr('isedit', 1);
            trObj.attr("disabled", false);
        } else {
            $(this).text("编辑");
            $('.saveInfo').click();
            $(this).attr('isedit', 0);
            trObj.attr("disabled", "disabled");
        }
    });

    //全选 /全不选
    $(document).delegate(".check", 'click', function() {
        var isc = $(this).attr('isc');
        var checked = (isc == 1) ? true : (isc == 2 ? true : false);
        if (isc == 2) {
            $(this).attr('isc', 3);
        } else if (isc == 3) {
            $(this).attr('isc', 2);
        }
        var trArr = $(this).parents(".contab").find("tbody tr");
        for (var i = 0; i < trArr.length; i++) {
            var trobj = $(trArr[i]);
            if (trobj.find('> td').length <= 1) { //此验证主要为防止嵌套table而提交重复数据
                continue;
            }
            trobj.find("input[type='checkbox']").each(function(k, v) {
                $(v).prop('checked', checked);
            });
        }
    });


    //编辑
    $(document).delegate(".bantcheditTr", 'click', function() {
        var status = $(this).attr("isedit");
        (status == 1) ? $(this).attr("isedit", 0): $(this).attr("isedit", 1);
        var set = (status == 1) ? false : 'disabled';
        var trArr = $(this).parents(".content").find("tbody tr");
        for (var i = 0; i < trArr.length; i++) {
            var trobj = $(trArr[i]);
            if (trobj.find('> td').length <= 1) { //此验证主要为防止嵌套table而提交重复数据
                continue;
            }
            trobj.find("input,select").each(function(k, v) {
                $(v).attr("disabled", set);
            });
        }
    });


    //数据保存
    $(document).delegate(".saveInfo", 'click', function() {
        var param = {};
        var trArr = $(this).parents(".content").find(".dataTable").find("tbody tr");
        for (var i = 0; i < trArr.length; i++) {
            var trobj = $(trArr[i]);
            if (trobj.find('> td').length <= 1) { //此验证主要为防止嵌套table而提交重复数据
                continue;
            }
            var key = null; //初始化key为null,后面代码判断key,验证是否将当行的参数加入paran数组
            trobj.find("input,select").not(":disabled").each(function(k, v) {
                var val = $(v).val();
                key = 'ekey[' + i + '][' + $(v).attr('name') + ']';
                if ($(v).attr('name') != 'ids[]') { //checkbox过滤
                    param[key] = val;
                }
            });
        }
        //提交数组至后端
        var burl = $(this).attr('burl');
        LC.loadData(burl, param, '', '');
        var trObj = $(".contab tbody tr").find("input,select").not(":disabled");
        $(trObj).attr("disabled", "disabled");
    });

    //帮助信息
    $(document).delegate(".helpInfo", 'click', function() {
        LCPAGE.dlgMsg($(this).attr('data'));
    });

    // 删除确认操作,删除成功则获取隐藏该行记录
    $(document).delegate(".dropTr", 'click', function() {
        var curTrObj = $(this);
        LCPAGE.showConfirm("您确认删除？");
        $('#popConfirm #confirm').unbind('click').click(function() {
            var del = curTrObj.attr("burl");
            var post = {};
            curTrObj.parents("tr").children().first().find("input[type='hidden']").each(function(key, val) {
                post[$(val).attr("name")] = $(val).val();
            });
            LC.loadData(del, post, '', '');
            //初始化下 //第一次删除会提示错误
            LC._data.code = 0;
            if (LC._data.code === 0) {
                curTrObj.parent().parent().html('');
            } else {
                LCPAGE.tips("亲！不好意思,删除失败咯！");
                $(".popDiv").fadeIn(350);
            }
        });
    });

    //页面加载弹层请求
    $(document).delegate(".popData,#popData", "click", function() {
        var showPopId = LCPAGE.appendPop();
        var self = $(this);
        LC.show($(this), '', showPopId, '', function() {
            var title = self.data("name") ? self.data("name") : '';
            $("#popLock").find(".pop1 > span").text(title);
            LCPAGE.showPop();
        }, {
            callback: function() {
                var popX = ($("#showDiv").width() - $("#" + showPopId).width()) / 2;
                popX += $(".sidebar").width();
                $("#" + showPopId).parents(".popDiv").first().css({
                    "margin-left": popX,
                    position: "fixed"
                });
            }
        });
        return;
    });

    //点击选中当前行
    $(document).delegate('tbody tr', 'click', function(e) {
        $(this).removeClass('divGray');
        $(this).addClass('t_stay').siblings().removeClass('t_stay');
    });

    //消除弹窗
    $(document).delegate(".cls,#cls", "click", function() {
        LCPAGE.closePop(this);
    });

    //以下代码与分类管理有关
    $(document).delegate("#dataTable .ctdDetail", "click", function() {
        if ($(this).text() == '查看') {
            $(this).text("关闭");
            $(this).parents("tr").css('background', '#1E90FF').next().show();
        } else {
            $(this).text("查看");
            $(this).parents("tr").first().css('background', '').next().find("> td").length == 1 && $(this).parents("tr").next().hide();
        }
    });

    //hash改变触发事件
    $(window).bind('hashchange', function() {
        //清空pageFunc函数
        window.pageFunc = {};

        LCPAGE.getDataByurl();
        LCPAGE.liClick();
    });

    //取消ajax操作
    $(document).delegate(".ajaxabort", 'click', function() {
        if (LC._ajaxRequest) {
            LC._ajaxRequest.abort();
            LCPAGE.tips("你取消了操作！");
        }
        $("#loading").hide();
    });

    /**
     * Tips 内容获取
     */
    $(document).delegate('.getTipContent', 'mouseover', function() {
        var infoUrl = $.trim($(this).attr('infoUrl'));

        if ($(this).attr('ajaxed')) return false;
        $(this).attr('ajaxed', 1);
        var _this = this;

        LC.loadData(infoUrl, null, function(data) {
            $(_this).tooltipster('content', data)
        }, {
            dataType: 'html'
        })
    });

    /**
     * 信息弹出框编辑
     */
    $(document).delegate('.editTrPop', 'click', function() {
        var infoUrl = $.trim($(this).attr('infoUrl'));
        var saveUrl = $.trim($(this).attr('saveUrl'));
        var dialogWrap = $.trim($(this).attr('wrap')) || 'editFormDialog';

        //两个必须参数
        if (!infoUrl || !saveUrl) return false;

        var dialogOpt = {
            title: $(this).attr('title'),
            height: $(this).attr('height'),
            width: $(this).attr('width'),
        }
        if ($(this).attr('callback')) {
            var attr = $(this).attr('callback').split('.');
            var callback = window;
            for(var i in attr) {
                if (attr[i] == 'window') continue;
                if (callback[attr[i]]) callback = callback[attr[i]];
            }
            if (callback != window) dialogOpt.callback = callback;
        }

        LC.loadData(infoUrl, null, function(data) {
            showDialog(saveUrl, data, dialogWrap, dialogOpt);
        }, {
            dataType: 'html'
        })
    });

    window.showDialog = function(saveUrl, formHtml, wrap, opt) {
        wrap = wrap || 'editFormDialog';
        formHtml = $('#' + wrap).html(formHtml);

        var btn = {
            '关闭': function() {
                $(this).dialog('close');
            },
            '保存': function() {
                if (!$('#' + wrap + ' #editForm').valid()) return false;

                LC.loadData(saveUrl, $('#' + wrap + ' #editForm').serializeArray(), function(data) {
                    if (data.status != 1) {
                        LCPAGE.msg('menuTips', data.info.toString());
                    } else {
                        $("#" + wrap).dialog('close');
                        $("#lcsubmit input[type='submit']").click();
                        if (opt.callback && typeof(opt.callback) == 'function')
                            opt['callback'](data.data);
                    }
                }, {
                    dataType: 'json'
                })
            },
        }
        if (!$('#' + wrap + ' #editForm').size()) {
            delete btn['保存']
        }

        $('#' + wrap).dialog({
            autoOpen: true,
            modal: true,
            show: "slide",
            hide: "slide",
            title: opt.title,
            resizable: true,
            height: opt.height || 'auto',
            width: opt.width,
            beforeClose: function() {
                $('#' + wrap).html('');
                $(this).dialog("destroy");
                wrap = null;
            },
            buttons: btn
        });
    }

    /**
     * 表单验证
     *
     * 需要验证的表单添加needvalid类
     * 内置验证类型请参考：
     *  http://jqueryvalidation.org/documentation/
     *  #list-of-built-in-validation-methods
     */
    $.validator.setDefaults({
        errorClass: 'form-error',
        errorElement: 'div',
        focusCleanup: true,
    });
    $('.needvalid').validate();
});
