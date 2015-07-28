if( !window.LC) window.LC = {
    _baseUrl : null,
    _version : null,
    _tpl : null,
    _tmptpl :[],   //临时模版，在多个模版共存时
    _data: null,
    _main : null,
    _siteView : null,
    _s_time:0,
    _s_field : null,
    _JSfile:{},
    _ajaxRequest: null,
    init:function(config){
        LC._baseUrl = config.baseUrl;
        LC._siteView = config.siteView;
        LC._action = config.ctrolAction;
        LC._version = config.version;     //版本号
        LC._menu  = config.nomenu;
    },


    /**
     * ajax获取模版
     */
    loadTpl:function(page, battr, depend){
        if(battr >= 1 && LC._tmptpl[page] ){
            var s = LC._tmptpl[page].indexOf('<!-- -->');
            var e = LC._tmptpl[page].indexOf('<!-- end -->');  //jquery弹窗 的时候 弹窗html放在此标识之后 防止生成n个html弹窗
            LC._tpl = LC._tmptpl[page].substring(s, e);
        }else if(LC._tmptpl[page]){
            LC._tpl = LC._tmptpl[page];
//            LC.parseTpl(depend,-1);
        }else{
            var urlTpl = LC._siteView + page + '.html?' + LC._version;
            $.ajax({
                type: "get",
                url: urlTpl,
                async:false,
                dataType:'html',
                success: function(data, textStatus){
                    LC._tpl = data;
                    LC._tmptpl[page] = data;
//                    LC.parseTpl(depend,-1);
                    if(LC._tmptpl.length > 10 ){//缓存最近10个模版
                        LC._tmptpl.pop();
                    }
                }
            });
        }

    },

    /**
     * ajax获取数据
     */
    loadData:function(burl,postData, callback, param){
        if(!param){
           param = {};
        }
        if(!postData){
            postData = {};
        }
        var method = typeof param.method == 'undefined' ? 'post' :  param.method;
        var url = typeof param.url == 'undefined' ? LC._action + burl : param.url;
        var dataType = typeof param.dataType == 'undefined' ? 'json' : param.dataType;
        postData = LC.getUrlParam(postData);
        LC._ajaxRequest = $.ajax({
            type: method,
            url: url,
            async:true,
            data:postData,
            dataType:dataType,
            beforeSend:function(){
                $("#loading").show();
            },
            success: function(data, textStatus){
                if(! LC._ajaxRequest){
                    return;
                }
                if(dataType == 'jsonp'){
                    LC._data = {};
                    LC._data.data = data;
                    if (LC._data.data)
                        LC._data.data.window = window;
                }else if(data.status == 99){
                	LCPAGE.overTime(window.location.hash.replace(/[~'!<>@#$%^&*()-+_=:]/g, ''));
                }else{
                    LC._data = data;
                    if (LC._data.data)
                        LC._data.data.window = window;
                }
                if(typeof callback == 'function'){
                    callback(data);
                }
                if(typeof param.loading == 'undefined'){
                    $("#loading").hide();
                }
            },
            error:function(jqXHR, textStatus, errorThrown){
            	 $("#loading").hide();
                 LCPAGE.tips("亲！不好意思哟，出错了哦！");
                 console.log(jqXHR, textStatus, errorThrown);
            }
        });
    },
    /**
     * 每次点击操作都会改变url
     */
    setR:function(r){
        var hash = window.location.hash;
        if(hash.indexOf('?') != -1){
            hash = hash.replace(/\#(\S*)\?/i, r + '?');
        }else{
            hash = r;
        }
        window.location.hash = hash;
    },
    getUrlParam:function(params){
        window.location.hash.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
             params[key] = value;
        });
        return params;
    },
    /**
     * divID 获取divID 下的所有数据
     */
    show:function(obj, divID, depend, data, callback, param){
        if(!param ){
            param = {};
        }
        var showData = true;
        var burl = typeof obj == 'object'? $(obj).attr('burl') : '';
        var page = typeof obj == 'object' ? $(obj).attr('page') : param.page;
        var battr = $(obj).attr('battr');
        var by = typeof obj == 'object'? $(obj).attr('by') : '';
        var col = typeof obj == 'object'? $(obj).attr('col') : '';
        var postData = ( col && by ) ? {'orderby':col+' '+by} : {};
        if(battr >= 1){
            postData = LC.getFormDataById(divID);
        }
        if(typeof data == 'object' ){
            for(var i in data){
                postData[i] = data[i];
            }
        }
        LC.loadTpl(page, battr, depend);
        if(showData){
            LC.loadData(burl, postData, function(){
                if(typeof callback == 'function'){
                    callback();
                }
                LC.parseTpl(depend, battr, param.callback);
            }, param);
        }else{
            if(typeof callback == 'function'){
                callback();
            }else{
                 LC.parseTpl(depend, battr);
                 $("#loading").hide();
            }
        }
    },
    /**
     * 将模板与数据整合
     */
    parseTpl:function(depend, battr, callback){//解析模板  模板解析统一调用方法
        var obj = {};
        if(!depend){
            depend = 'showDiv';
        }
        obj = $("#" + depend);
        if(battr >= 1){//
            obj = $(obj).find('.datashow');
        }
        if(! LC._tpl){
            LC._tpl = {};
        }
        if(! LC._data || battr == -1){
            LC._data = {};
            LC._data.data = {};
            LC._data.data.window = window;
        }
        var tpl = new jSmart( LC._tpl );
		var res = tpl.fetch( LC._data.data );//battr=-1 初始化图表
		$(obj).html(res);
        if(typeof callback == 'function'){
            callback();
        }
        return;
    },
    /**
     * 分页方法
     */
    page:function(pg, json){
		var id = "lcsubmit";
		var showDiv = 'showDiv';
        LC.show($("#" + id), id, showDiv, {'p':pg});
    },
	//获取指定dom下的所有表单数据
	getFormDataById:function(div){
		var json = {};
        var val = '';
        var key = '';
        $("#" + div).find("input,select,textarea").each(function(){
            if(($(this).attr('type') == 'radio') && ($(this).attr('checked') != 'checked')){
                return true;
            }
			if(($(this).attr('type') == 'checkbox') && ($(this).attr('checked') != 'checked')){
                return true;
            }
			if($(this).attr('type') == 'checkbox'){
				val = $(this).val();
				key = $(this).attr('name');
				json[key] = json[key] ? json[key] + ',' + val : val;
				return true;
			}
            val = $(this).val();
            key = $(this).attr('name');
            if(typeof key  == 'undefined' || (val ==0 && $(this).attr('hasAll') == 1)){
                return true;
            }
            json[key] = val;
        });
		return json;
	},
    /**
     * ls js 配置文件
     */
    getConfig:function(k){
        return typeof LCconfig[k] != 'undefined' ? LCconfig[k] : '';
    },
    /**
     * 获取#号后的方法
     */
    getHash:function(){
        var hash = window.location.hash;

        if(hash.indexOf('?') != -1){
            hash = hash.match(/\#(\S*)(\?)/i);
        }else{
            hash = hash.match(/\#(\S*)/i);
        }
        return hash;
    },
    loadJs:function(jsFile){
        if(jsFile.substring(0,4) != 'http'){
            jsFile = LC._baseUrl + "Public/js/" + jsFile;
        }
        if(typeof LC._JSfile[jsFile] == 'undefined'){
            $("body").append("<script type='text/javascript' src='" + jsFile + ".js'></script>");
            LC._JSfile[jsFile] = 1;
        }
    }
}
