window.pageFunc = {
    /*索引的key除l r的部分*/
    key: function(val) {
        if (parseFloat(val) == val)
            return '' + parseInt(val * 10);

        if ((typeof val == 'string') && val.match(/^\d\d:(3|0)0:00$/))
            return '' + parseInt(this.barOpt.valueParse(val) * 10);

        return '';
    },

    /*新增场地回调函数*/
    addSiteCallback: function(data) {
        self = window.pageFunc;
        self.un = [];
        $('#price_form .site_name:visible').each(function(){
            if (this.value == "0" || this.value == "-1") return;
            self.un.push(this.value);
        });
        self.sn[data.id] = data.name;
        self.un.push(data.id);
        $(self2).prepend('<option value="' + data.id + '">' + data.name + '</option>').val(data.id);
        self.updateSnOpt();
    },

    /*属性管理回调函数*/
    addAttrCallback: function(data) {
        self = window.pageFunc;
        self.un = [];
        $('#price_form .site_name:visible').each(function(){
            if (this.value == "0" || this.value == "-1") return;
            self.un.push(this.value);
        });
        self.sn[data.id] = data.name;
        self.un.push(data.id);
        $(self2).prepend('<option value="' + data.id + '">' + data.name + '</option>').val(data.id);
        self.updateSnOpt();
    },

    /*处理时间段删除*/
    delCallback: function(barRang) {
        var oldRang = {}
        $.extend(true, oldRang, this.rang);
        var newRang = this.setRang(barRang);

        //获取新增时间段
        var delRang = [];
        for (var k in oldRang.sl) {
            if (newRang.sl[k] != oldRang.sl[k]) {
                delRang = this.getRangBylval(oldRang.sl[k], oldRang);
                break;
            }
        }
        //被删除时间段的左右时间段
        var delRangIndex = oldRang.a.indexOf(delRang[0]);
        var lRang = delRangIndex == 0 ? [] : this.getRangBylval(oldRang.a[delRangIndex - 1], oldRang);
        var rRang = (delRangIndex + 1) == oldRang.a.length ? [] : this.getRangBylval(oldRang.a[delRangIndex + 1], oldRang);

        var newEmpty = [];
        var oldTab = $('#price_wrap').clone();
        if (lRang != [] && lRang[2] == false) {
            oldTab.find('.td_' + this.lkey(lRang[0])).remove();
            newEmpty[0] = lRang[0];
        } else newEmpty[0] = delRang[0];

        if (rRang != [] && rRang[2] == false) {
            oldTab.find('.td_' + this.lkey(rRang[0])).remove();
            newEmpty[1] = rRang[1];
        } else newEmpty[1] = delRang[1];

        newEmpty[2] = false;
        oldTab.find('.td_' + this.lkey(delRang[0])).replaceWith(this.createTd(newEmpty));
        $('#price_wrap').replaceWith(oldTab);
    },

    /*处理时间段的改变*/
    changeCallback: function(barRang) {
        //暂停此功能开发
        return;
        var oldRang = {}
        $.extend(true, oldRang, this.rang);
        var newRang = this.setRang(barRang);


        var oldLval = null,
            newLval = null,
            oldRval = null,
            newRval = null;
        for (var i in oldRang.sl) {
            if (newRang.sl.indexOf(oldRang.sl[i]) == -1) oldLval = oldRang.sl[i];
            if (oldRang.sl.indexOf(newRang.sl[i]) == -1) newLval = newRang.sl[i];
            if (newRang.sr.indexOf(oldRang.sr[i]) == -1) oldRval = oldRang.sr[i];
            if (oldRang.sr.indexOf(newRang.sr[i]) == -1) newRval = newRang.sr[i];
        }

        var oldTab = $('#price_wrap').clone();

        //移动
        if (oldLval != null && oldRval != null) {}

        //右侧长度变化
        if (oldLval == null && oldRval != null) {
            if (newRval == this.max || newRang.sl.indexOf(newRval) != -1) {
                oldTab.find('.td_' + this.lkey(oldRval)).remove();
            } else {
                oldTab.find('.td_' + this.lkey(oldRval)).replaceWith(this.getRangBylval(newRval));
            }
            oldTab.find('.td_' + this.lkey(oldLval)).replaceWith(this.getRangBylval(newLval));
        }

        //左侧长度变化
        if (oldLval != null && oldRval == null) {
            var oldPreLval = this.getRangByrval(oldLval, oldRang)[0];
            if (newLval == this.min || newRang.sr.indexOf(newLval) != -1) {
                oldTab.find('.td_' + this.lkey(oldPreLval)).remove();
            } else {
                var newPreLval = this.getRangByrval(newLval)[0];
                oldTab.find('.td_' + this.lkey(oldPreLval)).replaceWith(this.getRangBylval(newPreLval));
            }
            oldTab.find('.td_' + this.lkey(oldLval)).replaceWith(this.getRangBylval(newLval));
        }

        $('#price_wrap').replaceWith(oldTab);
    },

    /*处理时间段新增*/
    addCallback: function(barRang) {
        var oldRang = {}
        $.extend(true, oldRang, this.rang);
        var newRang = this.setRang(barRang);

        //获取新增时间段
        var addRang = [];
        for (var k in newRang.sl) {
            if (newRang.sl[k] != oldRang.sl[k]) {
                addRang = this.getRangBylval(newRang.sl[k]);
                break;
            }
        }

        //生成时间段的左右时间段
        var addRangIndex = newRang.a.indexOf(addRang[0]);
        var lRang = addRangIndex == 0 ? [] : this.getRangBylval(newRang.a[addRangIndex - 1]);
        var rRang = (addRangIndex + 1) == newRang.a.length ? [] : this.getRangBylval(newRang.a[addRangIndex + 1]);

        var oldEmpty = [];
        var tdRang = [];
        if (lRang != [] && lRang[2] == false) {
            tdRang.push(lRang)
            oldEmpty[0] = lRang[0];
        } else oldEmpty[0] = addRang[0];

        tdRang.push(addRang)

        if (rRang != [] && rRang[2] == false) {
            tdRang.push(rRang)
        }

        var newTd = '';
        for (var k in tdRang) {
            newTd += this.createTd(tdRang[k]);
        }
        var oldTab = $('#price_wrap').clone();
        oldTab.find('.td_' + this.lkey(oldEmpty[0])).replaceWith(newTd);
        $('#price_wrap').replaceWith(oldTab);
    },

    /*索引的val*/
    val: function(key) {
        key = key.substr(1);
        if (parseInt(key) != key) return false;
        return key / 10;
    },

    /*左值索引的key*/
    lkey: function(val) {
        return 'l' + this.key(val);
    },

    /*右值索引的key*/
    rkey: function(val) {
        return 'r' + this.key(val);
    },

    /*table状态中添加时间段 */
    addRang: function(rang) {
        this.rang.l[this.lkey(rang[0])] = rang;
        this.rang.r[this.rkey(rang[1])] = rang;
        if (this.rang.a.indexOf(rang[0]) == -1) {
            this.rang.a.push(rang[0]);
        }

        if (rang[2] == false) return true;

        if (this.rang.sl.indexOf(rang[0]) == -1) {
            this.rang.sl.push(rang[0]);
        }

        if (this.rang.sr.indexOf(rang[1]) == -1) {
            this.rang.sr.push(rang[1]);
        }
    },

    /*根据左值获取时间段*/
    getRangBylval: function(lval, rang) {
        rang = rang || this.rang;
        return rang.l[this.lkey(lval)];
    },

    /*根据右值获取时间段*/
    getRangByrval: function(rval, rang) {
        rang = rang || this.rang;
        return rang.r[this.rkey(rval)];
    },

    clearRang: function() {
        this.rang = {
            /*以时间段左值为索引的时间段信息*/
            l: {},
            /*以时间段右值为索引的时间段信息*/
            r: {},
            /*以时间段左值存数组以方便排序*/
            a: [],
            /*以已选定时间段左值存数组以方便排序*/
            sl: [],
            /*以已选定时间段右值存数组以方便排序*/
            sr: [],
        };
    },

    /*根据bar值生成rang*/
    setRang: function(range) {
        this.clearRang();

        if (!range) {
            range = []; 
            for (v0 = this.min; v0 <= this.max; v0 += this.barOpt.minSize) {
                v1 = v0 + this.barOpt.minSize;
                if (v1 > this.max) break;
                range.push([this.barOpt.valueFormat(v0), this.barOpt.valueFormat(v1)]);
            }
        }
        var isEmptyBar = this.barOpt.values = [];

        //已选定时间段
        for (var k in range) {
            var r = range[k];
            var lval = this.barOpt.valueParse(r[0]);
            var rval = this.barOpt.valueParse(r[1]);
            if (typeof(r[2]) != 'undefined') {
                var price = parseFloat(r[2]).toFixed(2);
            } else var price = true;
            this.addRang([lval, rval, price, r[3]||'']);
            if (isEmptyBar) this.barOpt.values.push([r[0], r[1]])
        }
        if (isEmptyBar) this.createBar();

        //查找未选定时间段
        var notSelectTimeRang = [];
        if (this.rang.a[0] != this.min) {
            notSelectTimeRang.push([this.min, this.rang.a[0], false]);
        }
        for (var k in this.rang.a) {
            var lval = this.rang.a[k];
            var rval = this.getRangBylval(lval)[1];

            if (rval == this.max) break;

            //检查紧邻的L是否存在，不存在则新增一个空
            var slideL = this.getRangBylval(rval);
            if (slideL) {
                //若确定存在的下一个已到顶值
                if (slideL[1] == this.max) break;
                continue;
            }
            var nextLval = this.rang.a[parseInt(k) + 1];
            if (nextLval) notSelectTimeRang.push([rval, nextLval, false]);
            else {
                notSelectTimeRang.push([rval, this.max, false]);
                break;
            }
        }
        if (notSelectTimeRang.length != 0) {
            for (var k in notSelectTimeRang) {
                this.addRang(notSelectTimeRang[k]);
            }
        }

        this.rang.a.sort(function(a, b) {
            return a > b ? 1 : -1;
        });
        this.rang.sl.sort(function(a, b) {
            return a > b ? 1 : -1;
        });
        this.rang.sr.sort(function(a, b) {
            return a > b ? 1 : -1;
        });
        return this.rang;
    },

    /*生成td*/
    createTd: function(rang) {
        var lkey = this.lkey(rang[0]);
        var w = 100 * (rang[1] - rang[0]) / (this.max - this.min);
        var str = '';
        str += '<td class="td_' + lkey + '" style="width:' + w + '%">';
        if (rang[2] != false) {
            var cls = ' class="price_input input_' + lkey + '" ';
            var pls = ' placeholder="场地价格" ';
            var lval = ' lval="' + rang[0] + '" ';
            var rval = ' rval="' + rang[1] + '" ';
            var did  = ' did="' + rang[3] + '" ';
            var val = ' value="' + (rang[2] === true ? this.price : rang[2]) + '" ';
            str += '<input price type="text" ' + did + lval + rval + val + pls + cls + '/>';
        } else str += ' ';
        str += '</td>';
        return str;
    },

    /**
     * 生成TR
     * @param 场地名
     * @param 是否禁用掉input[type=text]的输入功能
     */
    createTr: function(siteId) {
        var str = '<tr>';
        for (var k in this.rang.a) {
            str += this.createTd(this.getRangBylval(this.rang.a[k]));
        }
        str += '</tr>';
        str = this.priceStr.replace('__STR__', str);
        str = $(str);
        str.find('.tooltip').tooltipster({
            contentAsHTML: true
        });
        str.find('.site_name').html(this.siteNameOpt(siteId||0)).attr('osid', siteId || '');
        return str;
    },

    //初始化bar
    createBar: function() {
        if (self.barOpt.values == []) {
            for (v0 = this.min; v0 <= this.max; v0 += this.barOpt.minSize) {
                v1 = v0 + this.barOpt.minSize;
                if (v1 > this.max) break;
                this.barOpt.values[this.barOpt.values.length] = [
                    this.barOpt.valueFormat(v0),
                    this.barOpt.valueFormat(v1),
                ];
            }
        }
        var r = new RangeBar(self.barOpt);
        //时间段选择器事件回调函数
        r.on('delete', function(v, r) {
            //删除时间段
            self.delCallback(r);
        }).on('change', function(v, r) {
            //改变时间段
            self.changeCallback(r);
        }).on('add', function(v, r) {
            //添加时间段
            self.addCallback(r);
        });
        $('[role=main]').html(r.$el);
    },

    init: function(opentime,closetime,duration,price) {
        if (opentime) {
            this.min = this.barOpt.valueParse(opentime);
            this.barOpt.min = opentime;
        }
        if (closetime) {
            this.max = this.barOpt.valueParse(closetime);
            this.barOpt.max = closetime;
        }

        if (price)
            this.price = parseFloat(price).toFixed(2);

        if (duration)
            this.barOpt.minSize = parseFloat(duration);

        self.barOpt.values = [];
        self.un = [];
    },

    /*获取指定某一天的场地信息*/
    getSiteInfo: function() {
        if (this.gid == "0" || this.pid == "0") return;

        //一些初始化工作
        self.isEdit  = false;
        self.isSaled = false;

        var data = {
            gid: this.gid,
            pid: this.pid,
            date: self.date
        };

        LC.loadData('Site/siteInfo', data, function(data) {
            if (!data || !data.status || data.status != 1) {
                LCPAGE.tips(data.info || '数据加载失败！');
            } else {
                var d = data.data;

                //日期及周的文字处理
                $('.price_date').html(self.date);
                $('.price_week').html($('.tab-tit .on span.week').html());
                $('.edit_mode_label').show();

                $('#price_form .edit_mode' + (d.mode==0?1:d.mode)).click();

                //显示表单及一个星期的日期
                $('#site_wrap,.tab-tit').show();

                if (self.isSaled) $('#create_time_bar').hide();
                else $('#create_time_bar').show();

                //若是为空
                if (!d.site || d.site.length == 0) {
                    $('#create_time_bar').click();
                    self.isEdit = true;
                    return;
                }

                self.isEdit = true;
                self.isSaled= d.is_saled;
                self.barOpt.allowDelete = !self.isSaled;
                self.init(d.ot, d.ct);
                var tr = null;
                for (var i in d.site) self.un.push(i);
                for (var i in d.site) {
                    self.setRang(d.site[i]);
                    if (tr == null) tr = self.createTr(i);
                    else tr = tr.add(self.createTr(i));
                }
                //数据为空，则不做任何操作
                if (!tr) return;

                //若数据仅一行，则隐藏删除按钮
                if (tr.size() < 2) tr.find('.fa-times').hide();

                //将价格表扩充到表格中
                $('#price_wrap').html('');
                $('#price_wrap').append(tr);
            }
        }, {
            dataType: 'json'
        })
    },

    //生成场地名下拉菜单选项
    siteNameOpt: function(selectedId) {
        var optStr = '<option value="0">选择场地</option>';
        for( var i in this.sn) {
            if (i != selectedId && this.un.indexOf(i) != -1) continue;
            if (selectedId && i == selectedId) var s = 'selected';
            else var s = '';
            optStr += '<option ' + s + ' value="' + i +'">' + this.sn[i] + '</option>'
        }
        optStr += '<option value="-1">新增场地</option>';
        return optStr;
    },

    //生成场地名下拉菜单选项
    updateSnOpt: function() {
        var self = this;
        $('.site_name:visible').each(function(i){
            $(this).html(self.siteNameOpt($(this).val()))
        });
    },

    //事件绑定
    bind: function() {
        self = this;
        //创建时间段选择器
        $('#create_time_bar').click(function() {
            if (!$('#lcsubmit2').valid()) return false;

            if (self.isEdit) { 
                if(!confirm('执行此操作将重置场地价格信息表，确定执行？'))
                    return false;
                self.isReset = true;
            } else self.isReset = false;

            var ot = $.trim($('#opentime').val());
            var ct = $.trim($('#closetime').val());
            var tr = $.trim($('#timeRange').val());
            var pr = $.trim($('#price').val());

            //场地信息及时间段选择器的初始化
            self.init(ot, ct, tr, pr);
            if (self.min >= self.max) return false;

            //生成并显示价格表
            self.setRang();
            $('#price_wrap').html('').append(self.createTr());
            $('#site_wrap').show();
            $('#price_form .edit_mode1').click();

            //隐藏删除按钮
            $('#price_wrap .fa-times').hide();
        })

        //触发时间选择器
        $('.timepicker').timepicker({
            minTime: '6:00am',
            maxTime: '12:00am',
            timeFormat: 'H:i:s',
            show2400: true
        });

        //场馆和项目变动时，获取场馆项目信息
        $('.gym_pro').change(function() {
            //一些初始化工作
            $('#site_wrap,#gym_pro_wrap,.tab-tit,.edit_mode_label').hide();
            $('.price_date').html('');
            self.min = null;
            self.max = null;
            self.isEdit = false;
            self.isReset= false;
            self.isSaled= false;
            self.gid = $('#gym_id').val();
            self.pid = $('#pro_id').val();

            if (self.gid == "0" || self.pid == "0") return;

            var param = '/gid/' + self.gid + '/pid/' + self.pid;
            $('#attr_mag').attr('infoUrl', 'Site/attrList' + param)
                .attr('saveUrl', 'Site/saveAttr' + param);

            var data = {
                gid: self.gid,
                pid: self.pid 
            };
            LC.loadData('Site/gymProInfo', data, function(data) {
                if (!data.status || data.status != 1) {
                    //出错
                    LCPAGE.tips(data.info || "数据加载失败！");
                    return;
                } else if (!data.data || !data.data.ot) {
                    //不存在场地信息
                    self.sn = data.data.sn;
                    $('#gym_pro_wrap,#create_time_bar').show();
                    condition_wrap();
                } else {
                    var d = data.data;
                    //已场地信息
                    $('#gym_pro_wrap').show();
                    $('#opentime').val(d.ot);
                    $('#closetime').val(d.ct);
                    $('#timeRange').val(d.du);
                    $('#price').val(d.price);

                    //生成bar 及价格表
                    self.init(null, null, d.du, d.price);
                    self.sn = d.sn;
                    self.getSiteInfo();
                }
            }, {
                dataType: 'json'
            });

            //时间段生成器的显示
            function condition_wrap() {
                $('#gym_pro_wrap input').not('#create_time_bar,#attr_mag').val('');
                $('#gym_pro_wrap select').val(0);
            }
        })

        if (window.pageParam && window.pageParam.gym_id) {
            $('#gym_id').val(window.pageParam.gym_id);
            window.pageParam.gym_id = null;
        }
        if (window.pageParam && window.pageParam.pro_id) {
            $('#pro_id').val(window.pageParam.pro_id).change();
            window.pageParam.pro_id = null;
        }

        //某一天场地信息编辑
        $('.tab-tit li.date_ss').click(function() {
            if ($(this).hasClass('on')) return false;
            self.date = $(this).attr('date'); 
            self.getSiteInfo();
            $('.on').removeClass('on');
            $(this).addClass('on');
            return false;
        })

        //自定义日期编辑
        $('.tab-tit li.date_picker').click(function() {
            var max = $(this).attr('max-date');
            WdatePicker({
                el:'max_date',
                minDate:'#F{$dp.$DV(\''+max+'\',{d:1})}',
                onpicked:function(){
                    var date = $dp.cal.getNewDateStr();
                    var week = $dp.cal.getNewP('D');
                    var label = $dp.cal.getNewDateStr('MM/dd') 
                        + ' <span class="week">' + $dp.cal.getNewP('D') + '</span>';
                    self.date = date;
                    self.getSiteInfo();
                    $('#d_p_d').html(label);
                    $('.on').removeClass('on');
                    $(this).parent().addClass('on').attr('date', date);
            }});
        })

        $('#price_form').delegate('.fa-files-o', 'click', function() {
            var clone = $(this).parent().parent().clone();

            //提示信息
            clone.find('.tooltip').removeClass("tooltipstered").each(function(){
                $(this).attr('title', $(this).attr('btitle'));
            }).tooltipster({
                contentAsHTML: true
            });

            //去掉site_id属性
            clone.find('.site_name').attr("osid", '')

            //场地名称下拉菜单调整
            var sid = clone.find('.site_name').val();
            if (sid != "0" && sid != "-1") {
                clone.find('.site_name option[value=' + sid + ']').remove();
            }

            clone.appendTo($(this).parents('#price_wrap'));

            //显示删除按钮
            $('#price_wrap .fa-times').show();

            return false;
        }).delegate('.fa-times', 'click', function() {
            var del_num = $('#price_wrap .fa-times').size();

            //只剩最后一个，禁止删除
            if (del_num == 1) {
                LCPAGE.tips('最后一个，还是留着吧！');
                return false;
            }

            var s = $(this).parent().parent().find('.site_name');
            var v = s.val();

            if (s.attr('osid') != '' && self.isSaled) {
                LCPAGE.tips('这一天已有售出，只能新增和更新！');
                return false;
            }

            $(this).parent().parent().hide();

            //是未入库的新增场地，直接删除
            //if (s.attr('osid') == '') $(this).parent().parent().remove();
            //else s.attr('is_del', 1);//删除标记
            $(this).parent().parent().remove();

            //删除的场地已选择了场地名，则更新其它下拉菜单的下拉选项
            if (v != "0" && s != "-1") {
                delete self.un[self.un.indexOf(v)];
                self.updateSnOpt();
            }

            //删除后只剩最后一个，隐藏删除
            if (del_num == 2)
                $('#price_wrap .fa-times').hide();

            return false;
        }).delegate('.site_name', 'change', function(){
            //场地名变更
            if (this.value != "-1") {
                self.un = [];
                $('#price_form .site_name:visible').each(function(){
                    if (this.value == "0" || this.value == "-1") return;
                    self.un.push(this.value);
                });
                self.updateSnOpt();
                return false;
            }

            self2 = this;

            var param = '/gid/' + self.gid + '/pid/' + self.pid;
            $('#addSite').attr('saveUrl', 'Site/addSite' + param).click();
            return false;
        });

        //保存初始化的场地信息
        $('#save_site').click(function() {
            if (!$('#price_wrap input,#price_wrap select').valid()) return false; 
            if (self.gid == "0" || self.pid == "0") return false;

            var data = 'ot=' + self.barOpt.valueFormat(self.min);
            data += '&ct=' + self.barOpt.valueFormat(self.max);
            data += '&pr=' + (parseFloat($('#price').val()) || self.price).toFixed(2);
            data += '&gym_id=' + self.gid;
            data += '&pro_id=' + self.pid;
            data += '&du=' + self.barOpt.minSize;
            data += '&saled=' + (self.isSaled || 0);
            data += '&reset=' + (self.isReset || 0);
            data += '&date=' + self.date;
            data += '&mode=' + $('.edit_mode:checked').val();

            //收集价格信息，每场时间信息
            $('#price_wrap tr.ttt').each(function(i) {
                var site_name = $(this).find('.site_name');
                data += '&sid[' + i + ']=';
                data += site_name.val();
                data += '&osid[' + i + ']=';
                data += site_name.attr('osid') || '';
                data += '&del[' + i + ']=';
                data += site_name.attr('is_del') || '';
                $(this).find('.price_input').each(function(j) {
                    var key = '[' + i + '][' + j + ']=';
                    data += '&price' + key + $.trim(this.value);
                    data += '&id' + key + $(this).attr('did');
                    data += '&opentime' + key + self.barOpt.valueFormat($(this).attr('lval'));
                    data += '&closetime' + key + self.barOpt.valueFormat($(this).attr('rval'));
                });
            })

            //发送数据
            var url = self.isEdit?'Site/updateSite':'Site/saveInitSite';
            LC.loadData(url, data, function(data) {
                if (!data || !data.status || data.status != 1) {
                    LCPAGE.tips(data.info || "数据保存失败！");
                } else {
                    LCPAGE.dlgMsg("数据保存成功！");
                    self.getSiteInfo();
                }
            }, {
                dataType: 'json'
            })
        });
    },

    priceStr: '<tr class="ttt"><td><select class="site_name" min="1"></select></td><td><table>__STR__</table></td><td><i class="fa fa-files-o tooltip" btitle="复制" title="复制"></i> <i class="fa fa-times tooltip" btitle="删除" title="删除"></i></td></tr>',

    /*时间段选择器选项*/
    barOpt: {
        values:[],
        minSize:null,
        valueFormat: function(ts) {
            ts = parseFloat(ts).toFixed(1);
            return ts.replace('.0', ':00').replace('.5', ':30') + ':00';
        },
        valueParse: function(date) {
            date = date.replace(/:00$/, '').replace(':', '.').replace('.3', '.5');
            return parseFloat(date);
        },
        label: function(a) {
            return a[0].replace(/:00$/, '') + '-' + a[1].replace(/:00$/, '');
        },
        snap: 0.5,
        allowSwap: false,
        barClass: 'progress',
        allowDelete: true,
        deleteTimeout: 200,
        rangeClass: 'bar'
    },

    /*时间段最大值*/
    max: null,

    /*时间段最小值*/
    min: null,

    /*场均价格*/
    price: null,

    /*是否是编辑状态*/
    isEdit: false,

    /*是否是重置状态*/
    isReset: false,

    /*场地ID:名称*/
    cn: {},

    /*已被选中的场地ID*/
    un: [],

    /*日期*/
    date: $('.tab-tit .on').attr('date'),

    rang: {
        /*以时间段左值为索引的时间段信息*/
        l: {},
        /*以时间段右值为索引的时间段信息*/
        r: {},
        /*以时间段左值存数组以方便排序*/
        a: [],
        /*以已选定时间段左值存数组以方便排序*/
        sl: [],
        /*以已选定时间段右值存数组以方便排序*/
        sr: [],
    },
};
window.pageFunc.bind();
