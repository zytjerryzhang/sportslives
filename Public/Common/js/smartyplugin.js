jSmart.prototype.registerPlugin(
    'modifier',
    'lcsum',
    function(v1, v2)
    {
        return parseInt(v1) + parseInt(v2);
    }
);
jSmart.prototype.registerPlugin(
    'modifier',
    'equation',
    function( v1, v2)
    {
        return v1 * v2;
    }
   );
jSmart.prototype.registerPlugin(
    'modifier',
    'string_format',
    function(s)
    {
        return s;
    }
);
jSmart.prototype.registerPlugin(
    'modifier',
    'dateformat',
    function(time,showMis, format)
    {
        if(!time || time == 0){
            return '无';
        }
        var d, s;
        var last = '';
        if(typeof(format) == 'undefined'){
            format = '-';
            last = ' ';
        }
        d = new Date(time*1000);
        s = (d.getFullYear()) + format;
        s += ("0"+(d.getMonth()+1)).slice(-2) + format;
        s += ("0"+d.getDate()).slice(-2) + last;
        if(showMis != 1){
            s += ("0"+d.getHours()).slice(-2) + ":";
            s += ("0"+d.getMinutes()).slice(-2) + ":";
            s += ("0"+d.getSeconds()).slice(-2);
        }
        return s
    }
);
jSmart.prototype.registerPlugin(
    'modifier',
    'subtract',
    function(v1, v2)
    {
        return parseInt(v1) - parseInt(v2);
    }
);
jSmart.prototype.registerPlugin(
    'modifier',
    'deconv',
    function(v1, v2)
    {
        return parseInt(v1) / parseInt(v2);
    }
)
jSmart.prototype.registerPlugin(
    'modifier',
    'max',
    function(v1, v2)
    {
        return v1 < v2 ? v1 : v2;
    }
)
jSmart.prototype.registerPlugin(
    'modifier',
    'pasreName',
    function(i, dynamicid, k)
    {
        if(!i){
            return '';
        }
        if(dynamicid > 0){
             k = k ? k : 'dpname';
             return typeof LC._dynamicData[dynamicid] != 'undefined' ? LC._dynamicData[dynamicid][i][k]: i;
        }
        return typeof VAR[dynamicid] != 'undefined' ? VAR[dynamicid][i] : i;
    }
)
jSmart.prototype.registerPlugin(
    'modifier',
    'pint',
    function(i, kw, format)
    {
        var i = tmpi = parseInt(i);
        if(format){
            var delimiter = ","; // replace comma if desired
            i = new String(i);
            var a = i.split('.',2)
            var val = parseInt(a[0]);
            if(isNaN(val)) { return ''; }
            var minus = '';
            if(val < 0) { minus = '-'; }
            val = Math.abs(val);
            var n = new String(val);
            var a = [];
            while(n.length > 3)
            {
                var nn = n.substr(n.length-3);
                a.unshift(nn);
                n = n.substr(0,n.length-3);
            }
            if(n.length > 0) { a.unshift(n); }
            n = a.join(delimiter);
            i = minus + n;
        }
        if(tmpi < 0 || tmpi == kw){
            i = "<em>" + i + "</em>";
        }
        return i;

    }
)
jSmart.prototype.registerPlugin(
    'modifier',
    'parseSite',
    function(i)
    {
        return typeof LC._aSite[i] != 'undefined' ?  LC._aSite[i] : i;

    }
)

jSmart.prototype.registerPlugin(
    'modifier',
    'current',
    function(obj, key)
    {
        if(typeof obj == 'object' || typeof obj == 'array'){
            for(var i in obj){
                var val = key ? obj[i][key] : obj[i];
                return val;
            }
        }
        var d = new Date();
        var s = (d.getFullYear()) + '-';
        s += ("0"+(d.getMonth()+1)).slice(-2) + '-';
        s += ("0"+d.getDate()).slice(-2);
        return s;
    }
)
jSmart.prototype.registerPlugin(
    'modifier',
    'viewImage',
    function(str)
    {
        if(str.length == 0 || str.indexOf(",") == -1){

            return '' ;
        }
        var img = '';
        var imgdir = '../public/images/app/' + $.cookie('gid') + '/';
        str = str.split(',');
        for(var i in str){
            img += "<img src='" + imgdir + 'slotmachine_' + str[i] + ".png'>"
        }
        return img;

    }
)



