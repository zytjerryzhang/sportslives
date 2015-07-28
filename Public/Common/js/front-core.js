if (!window.FR) window.FR = {
    _ajaxRequest: null,
    init: function() {},

    /**
     * ajax获取数据
     */
    loadData: function(url, postData, callback, param) {
        postData = postData || {};
        param = param || {};
        var method = typeof param.method == 'undefined' ? 'post' : param.method;
        var url = typeof param.url == 'undefined' ? url : param.url;
        var dataType = typeof param.dataType == 'undefined' ? 'json' : param.dataType;
        FR._ajaxRequest = $.ajax({
            type: method,
            url: url,
            async: false,
            data: postData,
            dataType: dataType,
            beforeSend: function() {},
            success: function(data, textStatus) {
                if (typeof callback == 'function') {
                    callback(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
            }
        });
    }
}
