!function (definition) {
    return typeof exports === 'object' ? module.exports = definition(require('jquery')) : typeof define === 'function' && define.amd ? define(['jquery'], definition) : window.RangeBar = definition(window.jQuery);
}(function ($) {
    return function e(t, n, r) {
        function s(o, u) {
            if (!n[o]) {
                if (!t[o]) {
                    var a = typeof require == 'function' && require;
                    if (!u && a)
                        return a(o, !0);
                    if (i)
                        return i(o, !0);
                    throw new Error('Cannot find module \'' + o + '\'');
                }
                var f = n[o] = { exports: {} };
                t[o][0].call(f.exports, function (e) {
                    var n = t[o][1][e];
                    return s(n ? n : e);
                }, f, f.exports, e, t, n, r);
            }
            return n[o].exports;
        }
        var i = typeof require == 'function' && require;
        for (var o = 0; o < r.length; o++)
            s(r[o]);
        return s;
    }({
        1: [
            function (_dereq_, module, exports) {
                var Base = _dereq_('estira');
                var requestAnimationFrame = _dereq_('./raf');
                _dereq_('es5-shim');
                var has = Object.prototype.hasOwnProperty;
                var Element = Base.extend({
                        initialize: function (html) {
                            this.$el = $(html);
                            this.$data = {};
                            this.$el.data('element', this);
                        },
                        draw: function (css) {
                            var self = this;
                            if (this.drawing)
                                return this.$el;
                            requestAnimationFrame(function () {
                                self.drawing = false;
                                self.$el.css(css);
                            });
                            this.drawing = true;
                            return this.$el;
                        },
                        on: function () {
                            this.$el.on.apply(this.$el, arguments);
                            return this;
                        },
                        one: function () {
                            this.$el.one.apply(this.$el, arguments);
                            return this;
                        },
                        off: function () {
                            this.$el.off.apply(this.$el, arguments);
                            return this;
                        },
                        trigger: function () {
                            this.$el.trigger.apply(this.$el, arguments);
                            return this;
                        },
                        remove: function () {
                            this.$el.remove();
                        },
                        data: function (key, value) {
                            var obj = key;
                            if (typeof key === 'string') {
                                if (typeof value === 'undefined') {
                                    return this.$data[key];
                                }
                                obj = {};
                                obj[key] = value;
                            }
                            $.extend(this.$data, obj);
                            return this;
                        }
                    });
                module.exports = Element;
            },
            {
                './raf': 5,
                'es5-shim': 9,
                'estira': 10
            }
        ],
        2: [
            function (_dereq_, module, exports) {
                var has = Object.prototype.hasOwnProperty;
                module.exports = function getEventProperty(prop, event) {
                    return has.call(event, prop) ? event[prop] : event.originalEvent && has.call(event.originalEvent, 'touches') ? event.originalEvent.touches[0][prop] : 0;
                };
            },
            {}
        ],
        3: [
            function (_dereq_, module, exports) {
                var Element = _dereq_('./element');
                var vertical = _dereq_('./vertical');
                var Indicator = Element.extend(vertical).extend({
                        initialize: function initialize(options) {
                            initialize.super$.call(this, '<div class="elessar-indicator">');
                            if (options.indicatorClass)
                                this.$el.addClass(options.indicatorClass);
                            if (options.value)
                                this.val(options.value);
                            this.options = options;
                        },
                        val: function (pos) {
                            if (pos) {
                                if (this.isVertical()) {
                                    this.draw({ top: 100 * pos + '%' });
                                } else {
                                    this.draw({ left: 100 * pos + '%' });
                                }
                                this.value = pos;
                            }
                            return this.value;
                        }
                    });
                module.exports = Indicator;
            },
            {
                './element': 1,
                './vertical': 8
            }
        ],
        4: [
            function (_dereq_, module, exports) {
                var Range = _dereq_('./range');
                var requestAnimationFrame = _dereq_('./raf');
                var Phantom = Range.extend({
                        initialize: function initialize(options) {
                            initialize.super$.call(this, $.extend({
                                readonly: true,
                                label: '+'
                            }, options));
                            this.$el.addClass('elessar-phantom');
                            this.on('mousedown.elessar touchstart.elessar', $.proxy(this.mousedown, this));
                        },
                        mousedown: function (ev) {
                            if (ev.which === 1) {
                                var startX = ev.pageX;
                                var newRange = this.options.parent.addRange(this.val());
                                this.remove();
                                this.options.parent.trigger('addrange', [
                                    newRange.val(),
                                    newRange
                                ]);
                                requestAnimationFrame(function () {
                                    newRange.$el.find('.elessar-handle:first-child').trigger(ev.type);
                                });
                            }
                        },
                        removePhantom: function () {
                        }
                    });
                module.exports = Phantom;
            },
            {
                './raf': 5,
                './range': 6
            }
        ],
        5: [
            function (_dereq_, module, exports) {
                var lastTime = 0;
                var vendors = [
                        'webkit',
                        'moz'
                    ], requestAnimationFrame = window.requestAnimationFrame;
                for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
                    requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
                }
                if (!requestAnimationFrame) {
                    requestAnimationFrame = function (callback, element) {
                        var currTime = new Date().getTime();
                        var timeToCall = Math.max(0, 16 - (currTime - lastTime));
                        var id = window.setTimeout(function () {
                                callback(currTime + timeToCall);
                            }, timeToCall);
                        lastTime = currTime + timeToCall;
                        return id;
                    };
                }
                module.exports = requestAnimationFrame;
            },
            {}
        ],
        6: [
            function (_dereq_, module, exports) {
                var Element = _dereq_('./element');
                var getEventProperty = _dereq_('./eventprop');
                var vertical = _dereq_('./vertical');
                _dereq_('es5-shim');
                var Range = Element.extend(vertical).extend({
                        initialize: function initialize(options) {
                            var self = this;
                            initialize.super$.call(this, '<div class="elessar-range"><span class="elessar-barlabel">');
                            this.options = options;
                            this.parent = options.parent;
                            if (this.options.rangeClass)
                                this.$el.addClass(this.options.rangeClass);
                            if (!this.readonly()) {
                                this.$el.prepend('<div class="elessar-handle">').append('<div class="elessar-handle">');
                                this.on('mouseenter.elessar touchstart.elessar', $.proxy(this.removePhantom, this));
                                this.on('mousedown.elessar touchstart.elessar', $.proxy(this.mousedown, this));
                                this.on('click', $.proxy(this.click, this));
                            } else {
                                this.$el.addClass('elessar-readonly');
                            }
                            if (typeof this.options.label === 'function') {
                                this.on('changing', function (ev, range) {
                                    self.$el.find('.elessar-barlabel').text(self.options.label.call(self, range.map($.proxy(self.parent.normalise, self.parent))));
                                });
                                if (this.parent.options.allowDelete)
                                    this.$el.attr('title', '双击删除').tooltipster();
                            } else {
                                this.$el.find('.elessar-barlabel').text(this.options.label);
                            }
                            this.range = [];
                            this.hasChanged = false;
                            this.changeEventBind = false;
                            if (this.options.value)
                                this.val(this.options.value);
                        },
                        isVertical: function () {
                            return this.parent.options.vertical;
                        },
                        removePhantom: function () {
                            this.parent.removePhantom();
                        },
                        readonly: function () {
                            if (typeof this.options.readonly === 'function') {
                                return this.options.readonly.call(this.parent, this);
                            }
                            return this.options.readonly;
                        },
                        val: function (range, valOpts) {
                            if (typeof range === 'undefined') {
                                return this.range;
                            }
                            valOpts = $.extend({}, {
                                dontApplyDelta: false,
                                trigger: true
                            }, valOpts || {});
                            var next = this.parent.nextRange(this.$el), prev = this.parent.prevRange(this.$el), delta = range[1] - range[0], self = this;
                            if (this.options.snap) {
                                range = range.map(snap);
                                delta = snap(delta);
                            }
                            if (next && next.val()[0] <= range[1] && prev && prev.val()[1] >= range[0]) {
                                range[1] = next.val()[0];
                                range[0] = prev.val()[1];
                            }
                            if (next && next.val()[0] < range[1]) {
                                if (next.val()[1] >= range[0]) {
                                    range[1] = next.val()[0];
                                    if (!valOpts.dontApplyDelta)
                                        range[0] = range[1] - delta;
                                } else {
                                    this.parent.repositionRange(this, range);
                                }
                            }
                            if (prev && prev.val()[1] > range[0]) {
                                if (prev.val()[0] <= range[1]) {
                                    range[0] = prev.val()[1];
                                    if (!valOpts.dontApplyDelta)
                                        range[1] = range[0] + delta;
                                } else {
                                    this.parent.repositionRange(this, range);
                                }
                            }
                            if (range[1] >= 1) {
                                range[1] = 1;
                                if (!valOpts.dontApplyDelta)
                                    range[0] = 1 - delta;
                            }
                            if (range[0] <= 0) {
                                range[0] = 0;
                                if (!valOpts.dontApplyDelta)
                                    range[1] = delta;
                            }
                            if (this.parent.options.bound) {
                                var bound = this.parent.options.bound(this);
                                if (bound) {
                                    if (bound.upper && range[1] > this.parent.abnormalise(bound.upper)) {
                                        range[1] = this.parent.abnormalise(bound.upper);
                                        if (!valOpts.dontApplyDelta)
                                            range[0] = range[1] - delta;
                                    }
                                    if (bound.lower && range[0] < this.parent.abnormalise(bound.lower)) {
                                        range[0] = this.parent.abnormalise(bound.lower);
                                        if (!valOpts.dontApplyDelta)
                                            range[1] = range[0] + delta;
                                    }
                                }
                            }
                            if (this.options.minSize && range[1] - range[0] < this.options.minSize) {
                                range[1] = range[0] + this.options.minSize;
                            }
                            if (this.range[0] === range[0] && this.range[1] === range[1])
                                return this.$el;
                            this.range = range;
                            if (valOpts.trigger) {
                                this.$el.triggerHandler('changing', [
                                    range,
                                    this.$el
                                ]);
                                this.hasChanged = true;
                            }
                            var start = 100 * range[0] + '%', size = 100 * (range[1] - range[0]) + '%';
                            this.draw(this.parent.options.vertical ? {
                                top: start,
                                minHeight: size
                            } : {
                                left: start,
                                minWidth: size
                            });
                            return this;
                            function snap(val) {
                                return Math.round(val / self.options.snap) * self.options.snap;
                            }
                            function sign(x) {
                                return x ? x < 0 ? -1 : 1 : 0;
                            }
                        },
                        click: function (ev) {
                            ev.stopPropagation();
                            ev.preventDefault();
                            var self = this;
                            if (!this.parent.options.allowDelete)
                                return;
                            if (this.deleteConfirm) {
                                this.parent.removeRange(this);
                                clearTimeout(this.deleteTimeout);
                            } else {
                                this.$el.addClass('elessar-delete-confirm');
                                this.deleteConfirm = true;
                                this.deleteTimeout = setTimeout(function () {
                                    self.$el.removeClass('elessar-delete-confirm');
                                    self.deleteConfirm = false;
                                }, this.parent.options.deleteTimeout);
                            }
                        },
                        mousedown: function (ev) {
                            ev.stopPropagation();
                            ev.preventDefault();
                            this.hasChanged = false;
                            if (ev.which > 1)
                                return;
                            if ($(ev.target).is('.elessar-handle:first-child')) {
                                $('body').addClass('elessar-resizing');
                                $(document).on('mousemove.elessar touchmove.elessar', this.resizeStart(ev));
                            } else if ($(ev.target).is('.elessar-handle:last-child')) {
                                $('body').addClass('elessar-resizing');
                                $(document).on('mousemove.elessar touchmove.elessar', this.resizeEnd(ev));
                            } else {
                                $('body').addClass('elessar-dragging');
                                $(document).on('mousemove.elessar touchmove.elessar', this.drag(ev));
                            }
                            var self = this;
                            if (this.changeEventBind) return;
                            $(document).on('mouseup touchend', function (ev) {
                                ev.stopPropagation();
                                ev.preventDefault();
                                if (self.hasChanged)
                                    self.trigger('change', [
                                        self.range,
                                        self.$el
                                    ]);
                                self.hasChanged = false;
                                $(document).off('mouseup.elessar mousemove.elessar touchend.elessar touchmove.elessar');
                                $('body').removeClass('elessar-resizing elessar-dragging');
                            });
                            this.changeEventBind = true;
                        },
                        drag: function (origEv) {
                            return;
                            var self = this, beginStart = this.startProp('offset'), beginPosStart = this.startProp('position'), mousePos = getEventProperty(this.ifVertical('clientY', 'clientX'), origEv);
                            mouseOffset = mousePos ? mousePos - beginStart : 0, beginSize = this.totalSize(), parent = this.options.parent, parentStart = parent.startProp('offset'), parentSize = parent.totalSize();
                            return function (ev) {
                                ev.stopPropagation();
                                ev.preventDefault();
                                var mousePos = getEventProperty(self.ifVertical('clientY', 'clientX'), ev);
                                if (mousePos) {
                                    var start = mousePos - parentStart - mouseOffset;
                                    if (start >= 0 && start <= parentSize - beginSize) {
                                        var rangeOffset = start / parentSize - self.range[0];
                                        self.val([
                                            start / parentSize,
                                            self.range[1] + rangeOffset
                                        ]);
                                    } else {
                                        mouseOffset = mousePos - self.startProp('offset');
                                    }
                                }
                            };
                        },
                        resizeEnd: function (origEv) {
                            var self = this, beginStart = this.startProp('offset'), beginPosStart = this.startProp('position'), mousePos = getEventProperty(this.ifVertical('clientY', 'clientX'), origEv), mouseOffset = mousePos ? mousePos - beginStart : 0, beginSize = this.totalSize(), parent = this.options.parent, parentStart = parent.startProp('offset'), parentSize = parent.totalSize(), minSize = this.options.minSize * parentSize;
                            return function (ev) {
                                var opposite = ev.type === 'touchmove' ? 'touchend' : 'mouseup', subsequent = ev.type === 'touchmove' ? 'touchstart' : 'mousedown';
                                ev.stopPropagation();
                                ev.preventDefault();
                                var mousePos = getEventProperty(self.ifVertical('clientY', 'clientX'), ev);
                                var size = mousePos - beginStart;
                                if (mousePos) {
                                    if (size > parentSize - beginPosStart)
                                        size = parentSize - beginPosStart;
                                    if (size >= minSize) {
                                        self.val([
                                            self.range[0],
                                            self.range[0] + size / parentSize
                                        ], { dontApplyDelta: true });
                                    } else if (size <= 10) {
                                        $(document).trigger(opposite + '.elessar');
                                        self.$el.find('.elessar-handle:first-child').trigger(subsequent + '.elessar');
                                    }
                                }
                            };
                        },
                        resizeStart: function (origEv) {
                            var self = this, beginStart = this.startProp('offset'), beginPosStart = this.startProp('position'), mousePos = getEventProperty(this.ifVertical('clientY', 'clientX'), origEv), mouseOffset = mousePos ? mousePos - beginStart : 0, beginSize = this.totalSize(), parent = this.options.parent, parentStart = parent.startProp('offset'), parentSize = parent.totalSize(), minSize = this.options.minSize * parentSize;
                            return function (ev) {
                                var opposite = ev.type === 'touchmove' ? 'touchend' : 'mouseup', subsequent = ev.type === 'touchmove' ? 'touchstart' : 'mousedown';
                                ev.stopPropagation();
                                ev.preventDefault();
                                var mousePos = getEventProperty(self.ifVertical('clientY', 'clientX'), ev);
                                var start = mousePos - parentStart - mouseOffset;
                                var size = beginPosStart + beginSize - start;
                                if (mousePos) {
                                    if (start < 0) {
                                        start = 0;
                                        size = beginPosStart + beginSize;
                                    }
                                    if (size >= minSize) {
                                        self.val([
                                            start / parentSize,
                                            self.range[1]
                                        ], { dontApplyDelta: true });
                                    } else if (size <= 10) {
                                        $(document).trigger(opposite);
                                        self.$el.find('.elessar-handle:last-child').trigger(subsequent);
                                    }
                                }
                            };
                        }
                    });
                module.exports = Range;
            },
            {
                './element': 1,
                './eventprop': 2,
                './vertical': 8,
                'es5-shim': 9
            }
        ],
        7: [
            function (_dereq_, module, exports) {
                var Element = _dereq_('./element');
                var Range = _dereq_('./range');
                var Phantom = _dereq_('./phantom');
                var Indicator = _dereq_('./indicator');
                var getEventProperty = _dereq_('./eventprop');
                var vertical = _dereq_('./vertical');
                _dereq_('es5-shim');
                var RangeBar = Element.extend(vertical).extend({
                        initialize: function initialize(options) {
                            options = options || {};
                            initialize.super$.call(this, '<div class="elessar-rangebar">');
                            this.options = $.extend({}, RangeBar.defaults, options);
                            this.options.min = this.options.valueParse(this.options.min);
                            this.options.max = this.options.valueParse(this.options.max);
                            if (this.options.barClass)
                                this.$el.addClass(this.options.barClass);
                            if (this.options.vertical)
                                this.$el.addClass('elessar-vertical');
                            this.ranges = [];
                            this.on('mousemove.elessar touchmove.elessar', $.proxy(this.mousemove, this));
                            this.on('mouseleave.elessar touchleave.elessar', $.proxy(this.removePhantom, this));
                            if (options.values)
                                this.setVal(options.values);
                            for (var i = 0; i < options.bgLabels; ++i) {
                                this.addLabel(i / options.bgLabels);
                            }
                            var self = this;
                            if (options.indicator) {
                                var indicator = this.indicator = new Indicator({
                                        parent: this,
                                        vertical: this.options.vertical,
                                        indicatorClass: options.indicatorClass
                                    });
                                indicator.val(this.abnormalise(options.indicator(this, indicator, function () {
                                    indicator.val(self.abnormalise(options.indicator(self, indicator)));
                                })));
                                this.$el.append(indicator.$el);
                            }
                        },
                        normaliseRaw: function (value) {
                            return this.options.min + value * (this.options.max - this.options.min);
                        },
                        normalise: function (value) {
                            return this.options.valueFormat(this.normaliseRaw(value));
                        },
                        abnormaliseRaw: function (value) {
                            return (value - this.options.min) / (this.options.max - this.options.min);
                        },
                        abnormalise: function (value) {
                            return this.abnormaliseRaw(this.options.valueParse(value));
                        },
                        findGap: function (range) {
                            var newIndex = 0;
                            this.ranges.forEach(function ($r, i) {
                                if ($r.val()[0] < range[0] && $r.val()[1] < range[1])
                                    newIndex = i + 1;
                            });
                            return newIndex;
                        },
                        insertRangeIndex: function (range, index, avoidList) {
                            if (!avoidList)
                                this.ranges.splice(index, 0, range);
                            if (this.ranges[index - 1]) {
                                this.ranges[index - 1].$el.after(range.$el);
                            } else {
                                this.$el.prepend(range.$el);
                            }
                        },
                        addRange: function (range, data) {
                            var $range = Range({
                                    parent: this,
                                    snap: this.options.snap ? this.abnormaliseRaw(this.options.snap + this.options.min) : null,
                                    label: this.options.label,
                                    rangeClass: this.options.rangeClass,
                                    minSize: this.options.minSize ? this.abnormaliseRaw(this.options.minSize + this.options.min) : null,
                                    readonly: this.options.readonly
                                });
                            if (this.options.data) {
                                $range.data(this.options.data.call($range, this));
                            }
                            if (data) {
                                $range.data(data);
                            }
                            this.insertRangeIndex($range, this.findGap(range));
                            $range.val(range);
                            var self = this;
                            $range.on('changing', function (ev, nrange, changed) {
                                ev.stopPropagation();
                                self.trigger('changing', [
                                    self.val(),
                                    changed
                                ]);
                            }).on('change', function (ev, nrange, changed) {
                                ev.stopPropagation();
                                self.trigger('change', [
                                    self.val(),
                                    changed
                                ]);
                            });
                            this.trigger('add', [this.val()]);
                            return $range;
                        },
                        prevRange: function (range) {
                            var idx = range.index();
                            if (idx >= 0)
                                return this.ranges[idx - 1];
                        },
                        nextRange: function (range) {
                            var idx = range.index();
                            if (idx >= 0)
                                return this.ranges[range instanceof Phantom ? idx : idx + 1];
                        },
                        setVal: function (ranges) {
                            if (this.ranges.length > ranges.length) {
                                for (var i = ranges.length - 1, l = this.ranges.length - 1; i < l; --l) {
                                    this.removeRange(l);
                                }
                                this.ranges.length = ranges.length;
                            }
                            var self = this;
                            ranges.forEach(function (range, i) {
                                if (self.ranges[i]) {
                                    self.ranges[i].val(range.map($.proxy(self.abnormalise, self)));
                                } else {
                                    self.addRange(range.map($.proxy(self.abnormalise, self)));
                                }
                            });
                            return this;
                        },
                        val: function (ranges) {
                            var self = this;
                            if (typeof ranges === 'undefined') {
                                return this.ranges.map(function (range) {
                                    return range.val().map($.proxy(self.normalise, self));
                                });
                            }
                            if (!this.readonly())
                                this.setVal(ranges);
                            return this;
                        },
                        removePhantom: function () {
                            if (this.phantom) {
                                this.phantom.remove();
                                this.phantom = null;
                            }
                        },
                        removeRange: function (i, noTrigger) {
                            if (i instanceof Range) {
                                i = this.ranges.indexOf(i);
                            }
                            this.ranges.splice(i, 1)[0].remove();
                            if (!noTrigger) {
                                this.trigger('delete', [this.val()]);
                            }
                        },
                        repositionRange: function (range, val) {
                            this.removeRange(range, true);
                            this.insertRangeIndex(range, this.findGap(val));
                        },
                        calcGap: function (index) {
                            var start = this.ranges[index - 1] ? this.ranges[index - 1].val()[1] : 0;
                            var end = this.ranges[index] ? this.ranges[index].val()[0] : 1;
                            return this.normaliseRaw(end) - this.normaliseRaw(start);
                        },
                        addLabel: function (pos) {
                            var cent = pos * 100, val = this.normalise(pos);
                            var $el = $('<span class="elessar-label">').css(this.startEdge(), cent + '%').text(val);
                            if (1 - pos < 0.05) {
                                $el.css({
                                    left: '',
                                    right: 0
                                });
                            }
                            return $el.appendTo(this.$el);
                        },
                        readonly: function () {
                            if (typeof this.options.readonly === 'function') {
                                return this.options.readonly.call(this);
                            }
                            return this.options.readonly;
                        },
                        mousemove: function (ev) {
                            var w = this.options.minSize ? this.abnormaliseRaw(this.options.minSize + this.options.min) : 0.05;
                            var pageStart = getEventProperty(this.ifVertical('pageY', 'pageX'), ev);
                            var val = (pageStart - this.startProp('offset')) / this.totalSize() - w / 2;
                            if (ev.target === ev.currentTarget && this.ranges.length < this.options.maxRanges && !$('body').is('.elessar-dragging, .elessar-resizing') && !this.readonly()) {
                                if (!this.phantom)
                                    this.phantom = Phantom({
                                        parent: this,
                                        snap: this.options.snap ? this.abnormaliseRaw(this.options.snap + this.options.min) : null,
                                        label: '+',
                                        minSize: this.options.minSize ? this.abnormaliseRaw(this.options.minSize + this.options.min) : null,
                                        rangeClass: this.options.rangeClass
                                    });
                                var idx = this.findGap([
                                        val,
                                        val + w
                                    ]);
                                if (!this.options.minSize || this.calcGap(idx).toFixed(8) >= this.options.minSize) {
                                    this.insertRangeIndex(this.phantom, idx, true);
                                    this.phantom.val([
                                        val,
                                        val + w
                                    ], { trigger: false });
                                }
                            }
                        }
                    });
                RangeBar.defaults = {
                    min: 0,
                    max: 100,
                    valueFormat: function (a) {
                        return a;
                    },
                    valueParse: function (a) {
                        return a;
                    },
                    maxRanges: Infinity,
                    readonly: false,
                    bgLabels: 0,
                    deleteTimeout: 5000,
                    allowDelete: false,
                    vertical: false
                };
                module.exports = RangeBar;
            },
            {
                './element': 1,
                './eventprop': 2,
                './indicator': 3,
                './phantom': 4,
                './range': 6,
                './vertical': 8,
                'es5-shim': 9
            }
        ],
        8: [
            function (_dereq_, module, exports) {
                module.exports = {
                    isVertical: function () {
                        return this.options.vertical;
                    },
                    ifVertical: function (v, h) {
                        return this.isVertical() ? v : h;
                    },
                    edge: function (which) {
                        if (which === 'start') {
                            return this.ifVertical('top', 'left');
                        } else if (which === 'end') {
                            return this.ifVertical('bottom', 'right');
                        }
                        throw new TypeError('What kind of an edge is ' + which);
                    },
                    totalSize: function () {
                        return this.$el[this.ifVertical('height', 'width')]();
                    },
                    edgeProp: function (edge, prop) {
                        var o = this.$el[prop]();
                        return o[this.edge(edge)];
                    },
                    startProp: function (prop) {
                        return this.edgeProp('start', prop);
                    },
                    endProp: function (prop) {
                        return this.edgeProp('end', prop);
                    }
                };
            },
            {}
        ],
        9: [
            function (_dereq_, module, exports) {
                ;
                (function (root, factory) {
                    if (typeof define === 'function' && define.amd) {
                        define(factory);
                    } else if (typeof exports === 'object') {
                        module.exports = factory();
                    } else {
                        root.returnExports = factory();
                    }
                }(this, function () {
                    var call = Function.prototype.call;
                    var prototypeOfArray = Array.prototype;
                    var prototypeOfObject = Object.prototype;
                    var _Array_slice_ = prototypeOfArray.slice;
                    var array_splice = Array.prototype.splice;
                    var array_push = Array.prototype.push;
                    var array_unshift = Array.prototype.unshift;
                    var _toString = prototypeOfObject.toString;
                    var isFunction = function (val) {
                        return prototypeOfObject.toString.call(val) === '[object Function]';
                    };
                    var isRegex = function (val) {
                        return prototypeOfObject.toString.call(val) === '[object RegExp]';
                    };
                    var isArray = function isArray(obj) {
                        return _toString.call(obj) === '[object Array]';
                    };
                    var isArguments = function isArguments(value) {
                        var str = _toString.call(value);
                        var isArgs = str === '[object Arguments]';
                        if (!isArgs) {
                            isArgs = !isArray(str) && value !== null && typeof value === 'object' && typeof value.length === 'number' && value.length >= 0 && isFunction(value.callee);
                        }
                        return isArgs;
                    };
                    function Empty() {
                    }
                    if (!Function.prototype.bind) {
                        Function.prototype.bind = function bind(that) {
                            var target = this;
                            if (!isFunction(target)) {
                                throw new TypeError('Function.prototype.bind called on incompatible ' + target);
                            }
                            var args = _Array_slice_.call(arguments, 1);
                            var binder = function () {
                                if (this instanceof bound) {
                                    var result = target.apply(this, args.concat(_Array_slice_.call(arguments)));
                                    if (Object(result) === result) {
                                        return result;
                                    }
                                    return this;
                                } else {
                                    return target.apply(that, args.concat(_Array_slice_.call(arguments)));
                                }
                            };
                            var boundLength = Math.max(0, target.length - args.length);
                            var boundArgs = [];
                            for (var i = 0; i < boundLength; i++) {
                                boundArgs.push('$' + i);
                            }
                            var bound = Function('binder', 'return function (' + boundArgs.join(',') + '){return binder.apply(this,arguments)}')(binder);
                            if (target.prototype) {
                                Empty.prototype = target.prototype;
                                bound.prototype = new Empty();
                                Empty.prototype = null;
                            }
                            return bound;
                        };
                    }
                    var owns = call.bind(prototypeOfObject.hasOwnProperty);
                    var defineGetter;
                    var defineSetter;
                    var lookupGetter;
                    var lookupSetter;
                    var supportsAccessors;
                    if (supportsAccessors = owns(prototypeOfObject, '__defineGetter__')) {
                        defineGetter = call.bind(prototypeOfObject.__defineGetter__);
                        defineSetter = call.bind(prototypeOfObject.__defineSetter__);
                        lookupGetter = call.bind(prototypeOfObject.__lookupGetter__);
                        lookupSetter = call.bind(prototypeOfObject.__lookupSetter__);
                    }
                    var spliceWorksWithEmptyObject = function () {
                            var obj = {};
                            Array.prototype.splice.call(obj, 0, 0, 1);
                            return obj.length === 1;
                        }();
                    var omittingSecondSpliceArgIsNoop = [1].splice(0).length === 0;
                    var spliceNoopReturnsEmptyArray = function () {
                            var a = [
                                    1,
                                    2
                                ];
                            var result = a.splice();
                            return a.length === 2 && isArray(result) && result.length === 0;
                        }();
                    if (spliceNoopReturnsEmptyArray) {
                        Array.prototype.splice = function splice(start, deleteCount) {
                            if (arguments.length === 0) {
                                return [];
                            } else {
                                return array_splice.apply(this, arguments);
                            }
                        };
                    }
                    if (!omittingSecondSpliceArgIsNoop || !spliceWorksWithEmptyObject) {
                        Array.prototype.splice = function splice(start, deleteCount) {
                            if (arguments.length === 0) {
                                return [];
                            }
                            var args = arguments;
                            this.length = Math.max(toInteger(this.length), 0);
                            if (arguments.length > 0 && typeof deleteCount !== 'number') {
                                args = _Array_slice_.call(arguments);
                                if (args.length < 2) {
                                    args.push(toInteger(deleteCount));
                                } else {
                                    args[1] = toInteger(deleteCount);
                                }
                            }
                            return array_splice.apply(this, args);
                        };
                    }
                    if ([].unshift(0) !== 1) {
                        Array.prototype.unshift = function () {
                            array_unshift.apply(this, arguments);
                            return this.length;
                        };
                    }
                    if (!Array.isArray) {
                        Array.isArray = isArray;
                    }
                    var boxedString = Object('a');
                    var splitString = boxedString[0] !== 'a' || !(0 in boxedString);
                    var properlyBoxesContext = function properlyBoxed(method) {
                        var properlyBoxesNonStrict = true;
                        var properlyBoxesStrict = true;
                        if (method) {
                            method.call('foo', function (_, __, context) {
                                if (typeof context !== 'object') {
                                    properlyBoxesNonStrict = false;
                                }
                            });
                            method.call([1], function () {
                                'use strict';
                                properlyBoxesStrict = typeof this === 'string';
                            }, 'x');
                        }
                        return !!method && properlyBoxesNonStrict && properlyBoxesStrict;
                    };
                    if (!Array.prototype.forEach || !properlyBoxesContext(Array.prototype.forEach)) {
                        Array.prototype.forEach = function forEach(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, thisp = arguments[1], i = -1, length = self.length >>> 0;
                            if (!isFunction(fun)) {
                                throw new TypeError();
                            }
                            while (++i < length) {
                                if (i in self) {
                                    fun.call(thisp, self[i], i, object);
                                }
                            }
                        };
                    }
                    if (!Array.prototype.map || !properlyBoxesContext(Array.prototype.map)) {
                        Array.prototype.map = function map(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0, result = Array(length), thisp = arguments[1];
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            for (var i = 0; i < length; i++) {
                                if (i in self) {
                                    result[i] = fun.call(thisp, self[i], i, object);
                                }
                            }
                            return result;
                        };
                    }
                    if (!Array.prototype.filter || !properlyBoxesContext(Array.prototype.filter)) {
                        Array.prototype.filter = function filter(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0, result = [], value, thisp = arguments[1];
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            for (var i = 0; i < length; i++) {
                                if (i in self) {
                                    value = self[i];
                                    if (fun.call(thisp, value, i, object)) {
                                        result.push(value);
                                    }
                                }
                            }
                            return result;
                        };
                    }
                    if (!Array.prototype.every || !properlyBoxesContext(Array.prototype.every)) {
                        Array.prototype.every = function every(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0, thisp = arguments[1];
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            for (var i = 0; i < length; i++) {
                                if (i in self && !fun.call(thisp, self[i], i, object)) {
                                    return false;
                                }
                            }
                            return true;
                        };
                    }
                    if (!Array.prototype.some || !properlyBoxesContext(Array.prototype.some)) {
                        Array.prototype.some = function some(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0, thisp = arguments[1];
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            for (var i = 0; i < length; i++) {
                                if (i in self && fun.call(thisp, self[i], i, object)) {
                                    return true;
                                }
                            }
                            return false;
                        };
                    }
                    var reduceCoercesToObject = false;
                    if (Array.prototype.reduce) {
                        reduceCoercesToObject = typeof Array.prototype.reduce.call('es5', function (_, __, ___, list) {
                            return list;
                        }) === 'object';
                    }
                    if (!Array.prototype.reduce || !reduceCoercesToObject) {
                        Array.prototype.reduce = function reduce(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0;
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            if (!length && arguments.length === 1) {
                                throw new TypeError('reduce of empty array with no initial value');
                            }
                            var i = 0;
                            var result;
                            if (arguments.length >= 2) {
                                result = arguments[1];
                            } else {
                                do {
                                    if (i in self) {
                                        result = self[i++];
                                        break;
                                    }
                                    if (++i >= length) {
                                        throw new TypeError('reduce of empty array with no initial value');
                                    }
                                } while (true);
                            }
                            for (; i < length; i++) {
                                if (i in self) {
                                    result = fun.call(void 0, result, self[i], i, object);
                                }
                            }
                            return result;
                        };
                    }
                    var reduceRightCoercesToObject = false;
                    if (Array.prototype.reduceRight) {
                        reduceRightCoercesToObject = typeof Array.prototype.reduceRight.call('es5', function (_, __, ___, list) {
                            return list;
                        }) === 'object';
                    }
                    if (!Array.prototype.reduceRight || !reduceRightCoercesToObject) {
                        Array.prototype.reduceRight = function reduceRight(fun) {
                            var object = toObject(this), self = splitString && _toString.call(this) === '[object String]' ? this.split('') : object, length = self.length >>> 0;
                            if (!isFunction(fun)) {
                                throw new TypeError(fun + ' is not a function');
                            }
                            if (!length && arguments.length === 1) {
                                throw new TypeError('reduceRight of empty array with no initial value');
                            }
                            var result, i = length - 1;
                            if (arguments.length >= 2) {
                                result = arguments[1];
                            } else {
                                do {
                                    if (i in self) {
                                        result = self[i--];
                                        break;
                                    }
                                    if (--i < 0) {
                                        throw new TypeError('reduceRight of empty array with no initial value');
                                    }
                                } while (true);
                            }
                            if (i < 0) {
                                return result;
                            }
                            do {
                                if (i in self) {
                                    result = fun.call(void 0, result, self[i], i, object);
                                }
                            } while (i--);
                            return result;
                        };
                    }
                    if (!Array.prototype.indexOf || [
                            0,
                            1
                        ].indexOf(1, 2) !== -1) {
                        Array.prototype.indexOf = function indexOf(sought) {
                            var self = splitString && _toString.call(this) === '[object String]' ? this.split('') : toObject(this), length = self.length >>> 0;
                            if (!length) {
                                return -1;
                            }
                            var i = 0;
                            if (arguments.length > 1) {
                                i = toInteger(arguments[1]);
                            }
                            i = i >= 0 ? i : Math.max(0, length + i);
                            for (; i < length; i++) {
                                if (i in self && self[i] === sought) {
                                    return i;
                                }
                            }
                            return -1;
                        };
                    }
                    if (!Array.prototype.lastIndexOf || [
                            0,
                            1
                        ].lastIndexOf(0, -3) !== -1) {
                        Array.prototype.lastIndexOf = function lastIndexOf(sought) {
                            var self = splitString && _toString.call(this) === '[object String]' ? this.split('') : toObject(this), length = self.length >>> 0;
                            if (!length) {
                                return -1;
                            }
                            var i = length - 1;
                            if (arguments.length > 1) {
                                i = Math.min(i, toInteger(arguments[1]));
                            }
                            i = i >= 0 ? i : length - Math.abs(i);
                            for (; i >= 0; i--) {
                                if (i in self && sought === self[i]) {
                                    return i;
                                }
                            }
                            return -1;
                        };
                    }
                    var keysWorksWithArguments = Object.keys && function () {
                            return Object.keys(arguments).length === 2;
                        }(1, 2);
                    if (!Object.keys) {
                        var hasDontEnumBug = !{ 'toString': null }.propertyIsEnumerable('toString'), hasProtoEnumBug = function () {
                            }.propertyIsEnumerable('prototype'), dontEnums = [
                                'toString',
                                'toLocaleString',
                                'valueOf',
                                'hasOwnProperty',
                                'isPrototypeOf',
                                'propertyIsEnumerable',
                                'constructor'
                            ], dontEnumsLength = dontEnums.length;
                        Object.keys = function keys(object) {
                            var isFn = isFunction(object), isArgs = isArguments(object), isObject = object !== null && typeof object === 'object', isString = isObject && _toString.call(object) === '[object String]';
                            if (!isObject && !isFn && !isArgs) {
                                throw new TypeError('Object.keys called on a non-object');
                            }
                            var theKeys = [];
                            var skipProto = hasProtoEnumBug && isFn;
                            if (isString || isArgs) {
                                for (var i = 0; i < object.length; ++i) {
                                    theKeys.push(String(i));
                                }
                            } else {
                                for (var name in object) {
                                    if (!(skipProto && name === 'prototype') && owns(object, name)) {
                                        theKeys.push(String(name));
                                    }
                                }
                            }
                            if (hasDontEnumBug) {
                                var ctor = object.constructor, skipConstructor = ctor && ctor.prototype === object;
                                for (var j = 0; j < dontEnumsLength; j++) {
                                    var dontEnum = dontEnums[j];
                                    if (!(skipConstructor && dontEnum === 'constructor') && owns(object, dontEnum)) {
                                        theKeys.push(dontEnum);
                                    }
                                }
                            }
                            return theKeys;
                        };
                    } else if (!keysWorksWithArguments) {
                        var originalKeys = Object.keys;
                        Object.keys = function keys(object) {
                            if (isArguments(object)) {
                                return originalKeys(Array.prototype.slice.call(object));
                            } else {
                                return originalKeys(object);
                            }
                        };
                    }
                    var negativeDate = -62198755200000, negativeYearString = '-000001';
                    if (!Date.prototype.toISOString || new Date(negativeDate).toISOString().indexOf(negativeYearString) === -1) {
                        Date.prototype.toISOString = function toISOString() {
                            var result, length, value, year, month;
                            if (!isFinite(this)) {
                                throw new RangeError('Date.prototype.toISOString called on non-finite value.');
                            }
                            year = this.getUTCFullYear();
                            month = this.getUTCMonth();
                            year += Math.floor(month / 12);
                            month = (month % 12 + 12) % 12;
                            result = [
                                month + 1,
                                this.getUTCDate(),
                                this.getUTCHours(),
                                this.getUTCMinutes(),
                                this.getUTCSeconds()
                            ];
                            year = (year < 0 ? '-' : year > 9999 ? '+' : '') + ('00000' + Math.abs(year)).slice(0 <= year && year <= 9999 ? -4 : -6);
                            length = result.length;
                            while (length--) {
                                value = result[length];
                                if (value < 10) {
                                    result[length] = '0' + value;
                                }
                            }
                            return year + '-' + result.slice(0, 2).join('-') + 'T' + result.slice(2).join(':') + '.' + ('000' + this.getUTCMilliseconds()).slice(-3) + 'Z';
                        };
                    }
                    var dateToJSONIsSupported = false;
                    try {
                        dateToJSONIsSupported = Date.prototype.toJSON && new Date(NaN).toJSON() === null && new Date(negativeDate).toJSON().indexOf(negativeYearString) !== -1 && Date.prototype.toJSON.call({
                            toISOString: function () {
                                return true;
                            }
                        });
                    } catch (e) {
                    }
                    if (!dateToJSONIsSupported) {
                        Date.prototype.toJSON = function toJSON(key) {
                            var o = Object(this), tv = toPrimitive(o), toISO;
                            if (typeof tv === 'number' && !isFinite(tv)) {
                                return null;
                            }
                            toISO = o.toISOString;
                            if (typeof toISO !== 'function') {
                                throw new TypeError('toISOString property is not callable');
                            }
                            return toISO.call(o);
                        };
                    }
                    var supportsExtendedYears = Date.parse('+033658-09-27T01:46:40.000Z') === 1000000000000000;
                    var acceptsInvalidDates = !isNaN(Date.parse('2012-04-04T24:00:00.500Z')) || !isNaN(Date.parse('2012-11-31T23:59:59.000Z'));
                    var doesNotParseY2KNewYear = isNaN(Date.parse('2000-01-01T00:00:00.000Z'));
                    if (!Date.parse || doesNotParseY2KNewYear || acceptsInvalidDates || !supportsExtendedYears) {
                        Date = function (NativeDate) {
                            function Date(Y, M, D, h, m, s, ms) {
                                var length = arguments.length;
                                if (this instanceof NativeDate) {
                                    var date = length === 1 && String(Y) === Y ? new NativeDate(Date.parse(Y)) : length >= 7 ? new NativeDate(Y, M, D, h, m, s, ms) : length >= 6 ? new NativeDate(Y, M, D, h, m, s) : length >= 5 ? new NativeDate(Y, M, D, h, m) : length >= 4 ? new NativeDate(Y, M, D, h) : length >= 3 ? new NativeDate(Y, M, D) : length >= 2 ? new NativeDate(Y, M) : length >= 1 ? new NativeDate(Y) : new NativeDate();
                                    date.constructor = Date;
                                    return date;
                                }
                                return NativeDate.apply(this, arguments);
                            }
                            var isoDateExpression = new RegExp('^' + '(\\d{4}|[+-]\\d{6})' + '(?:-(\\d{2})' + '(?:-(\\d{2})' + '(?:' + 'T(\\d{2})' + ':(\\d{2})' + '(?:' + ':(\\d{2})' + '(?:(\\.\\d{1,}))?' + ')?' + '(' + 'Z|' + '(?:' + '([-+])' + '(\\d{2})' + ':(\\d{2})' + ')' + ')?)?)?)?' + '$');
                            var months = [
                                    0,
                                    31,
                                    59,
                                    90,
                                    120,
                                    151,
                                    181,
                                    212,
                                    243,
                                    273,
                                    304,
                                    334,
                                    365
                                ];
                            function dayFromMonth(year, month) {
                                var t = month > 1 ? 1 : 0;
                                return months[month] + Math.floor((year - 1969 + t) / 4) - Math.floor((year - 1901 + t) / 100) + Math.floor((year - 1601 + t) / 400) + 365 * (year - 1970);
                            }
                            function toUTC(t) {
                                return Number(new NativeDate(1970, 0, 1, 0, 0, 0, t));
                            }
                            for (var key in NativeDate) {
                                Date[key] = NativeDate[key];
                            }
                            Date.now = NativeDate.now;
                            Date.UTC = NativeDate.UTC;
                            Date.prototype = NativeDate.prototype;
                            Date.prototype.constructor = Date;
                            Date.parse = function parse(string) {
                                var match = isoDateExpression.exec(string);
                                if (match) {
                                    var year = Number(match[1]), month = Number(match[2] || 1) - 1, day = Number(match[3] || 1) - 1, hour = Number(match[4] || 0), minute = Number(match[5] || 0), second = Number(match[6] || 0), millisecond = Math.floor(Number(match[7] || 0) * 1000), isLocalTime = Boolean(match[4] && !match[8]), signOffset = match[9] === '-' ? 1 : -1, hourOffset = Number(match[10] || 0), minuteOffset = Number(match[11] || 0), result;
                                    if (hour < (minute > 0 || second > 0 || millisecond > 0 ? 24 : 25) && minute < 60 && second < 60 && millisecond < 1000 && month > -1 && month < 12 && hourOffset < 24 && minuteOffset < 60 && day > -1 && day < dayFromMonth(year, month + 1) - dayFromMonth(year, month)) {
                                        result = ((dayFromMonth(year, month) + day) * 24 + hour + hourOffset * signOffset) * 60;
                                        result = ((result + minute + minuteOffset * signOffset) * 60 + second) * 1000 + millisecond;
                                        if (isLocalTime) {
                                            result = toUTC(result);
                                        }
                                        if (-8640000000000000 <= result && result <= 8640000000000000) {
                                            return result;
                                        }
                                    }
                                    return NaN;
                                }
                                return NativeDate.parse.apply(this, arguments);
                            };
                            return Date;
                        }(Date);
                    }
                    if (!Date.now) {
                        Date.now = function now() {
                            return new Date().getTime();
                        };
                    }
                    if (!Number.prototype.toFixed || 0.00008.toFixed(3) !== '0.000' || 0.9.toFixed(0) === '0' || 1.255.toFixed(2) !== '1.25' || 1000000000000000100..toFixed(0) !== '1000000000000000128') {
                        (function () {
                            var base, size, data, i;
                            base = 10000000;
                            size = 6;
                            data = [
                                0,
                                0,
                                0,
                                0,
                                0,
                                0
                            ];
                            function multiply(n, c) {
                                var i = -1;
                                while (++i < size) {
                                    c += n * data[i];
                                    data[i] = c % base;
                                    c = Math.floor(c / base);
                                }
                            }
                            function divide(n) {
                                var i = size, c = 0;
                                while (--i >= 0) {
                                    c += data[i];
                                    data[i] = Math.floor(c / n);
                                    c = c % n * base;
                                }
                            }
                            function numToString() {
                                var i = size;
                                var s = '';
                                while (--i >= 0) {
                                    if (s !== '' || i === 0 || data[i] !== 0) {
                                        var t = String(data[i]);
                                        if (s === '') {
                                            s = t;
                                        } else {
                                            s += '0000000'.slice(0, 7 - t.length) + t;
                                        }
                                    }
                                }
                                return s;
                            }
                            function pow(x, n, acc) {
                                return n === 0 ? acc : n % 2 === 1 ? pow(x, n - 1, acc * x) : pow(x * x, n / 2, acc);
                            }
                            function log(x) {
                                var n = 0;
                                while (x >= 4096) {
                                    n += 12;
                                    x /= 4096;
                                }
                                while (x >= 2) {
                                    n += 1;
                                    x /= 2;
                                }
                                return n;
                            }
                            Number.prototype.toFixed = function toFixed(fractionDigits) {
                                var f, x, s, m, e, z, j, k;
                                f = Number(fractionDigits);
                                f = f !== f ? 0 : Math.floor(f);
                                if (f < 0 || f > 20) {
                                    throw new RangeError('Number.toFixed called with invalid number of decimals');
                                }
                                x = Number(this);
                                if (x !== x) {
                                    return 'NaN';
                                }
                                if (x <= -1e+21 || x >= 1e+21) {
                                    return String(x);
                                }
                                s = '';
                                if (x < 0) {
                                    s = '-';
                                    x = -x;
                                }
                                m = '0';
                                if (x > 1e-21) {
                                    e = log(x * pow(2, 69, 1)) - 69;
                                    z = e < 0 ? x * pow(2, -e, 1) : x / pow(2, e, 1);
                                    z *= 4503599627370496;
                                    e = 52 - e;
                                    if (e > 0) {
                                        multiply(0, z);
                                        j = f;
                                        while (j >= 7) {
                                            multiply(10000000, 0);
                                            j -= 7;
                                        }
                                        multiply(pow(10, j, 1), 0);
                                        j = e - 1;
                                        while (j >= 23) {
                                            divide(1 << 23);
                                            j -= 23;
                                        }
                                        divide(1 << j);
                                        multiply(1, 1);
                                        divide(2);
                                        m = numToString();
                                    } else {
                                        multiply(0, z);
                                        multiply(1 << -e, 0);
                                        m = numToString() + '0.00000000000000000000'.slice(2, 2 + f);
                                    }
                                }
                                if (f > 0) {
                                    k = m.length;
                                    if (k <= f) {
                                        m = s + '0.0000000000000000000'.slice(0, f - k + 2) + m;
                                    } else {
                                        m = s + m.slice(0, k - f) + '.' + m.slice(k - f);
                                    }
                                } else {
                                    m = s + m;
                                }
                                return m;
                            };
                        }());
                    }
                    var string_split = String.prototype.split;
                    if ('ab'.split(/(?:ab)*/).length !== 2 || '.'.split(/(.?)(.?)/).length !== 4 || 'tesst'.split(/(s)*/)[1] === 't' || 'test'.split(/(?:)/, -1).length !== 4 || ''.split(/.?/).length || '.'.split(/()()/).length > 1) {
                        (function () {
                            var compliantExecNpcg = /()??/.exec('')[1] === void 0;
                            String.prototype.split = function (separator, limit) {
                                var string = this;
                                if (separator === void 0 && limit === 0) {
                                    return [];
                                }
                                if (_toString.call(separator) !== '[object RegExp]') {
                                    return string_split.call(this, separator, limit);
                                }
                                var output = [], flags = (separator.ignoreCase ? 'i' : '') + (separator.multiline ? 'm' : '') + (separator.extended ? 'x' : '') + (separator.sticky ? 'y' : ''), lastLastIndex = 0, separator2, match, lastIndex, lastLength;
                                separator = new RegExp(separator.source, flags + 'g');
                                string += '';
                                if (!compliantExecNpcg) {
                                    separator2 = new RegExp('^' + separator.source + '$(?!\\s)', flags);
                                }
                                limit = limit === void 0 ? -1 >>> 0 : ToUint32(limit);
                                while (match = separator.exec(string)) {
                                    lastIndex = match.index + match[0].length;
                                    if (lastIndex > lastLastIndex) {
                                        output.push(string.slice(lastLastIndex, match.index));
                                        if (!compliantExecNpcg && match.length > 1) {
                                            match[0].replace(separator2, function () {
                                                for (var i = 1; i < arguments.length - 2; i++) {
                                                    if (arguments[i] === void 0) {
                                                        match[i] = void 0;
                                                    }
                                                }
                                            });
                                        }
                                        if (match.length > 1 && match.index < string.length) {
                                            Array.prototype.push.apply(output, match.slice(1));
                                        }
                                        lastLength = match[0].length;
                                        lastLastIndex = lastIndex;
                                        if (output.length >= limit) {
                                            break;
                                        }
                                    }
                                    if (separator.lastIndex === match.index) {
                                        separator.lastIndex++;
                                    }
                                }
                                if (lastLastIndex === string.length) {
                                    if (lastLength || !separator.test('')) {
                                        output.push('');
                                    }
                                } else {
                                    output.push(string.slice(lastLastIndex));
                                }
                                return output.length > limit ? output.slice(0, limit) : output;
                            };
                        }());
                    } else if ('0'.split(void 0, 0).length) {
                        String.prototype.split = function split(separator, limit) {
                            if (separator === void 0 && limit === 0) {
                                return [];
                            }
                            return string_split.call(this, separator, limit);
                        };
                    }
                    var str_replace = String.prototype.replace;
                    var replaceReportsGroupsCorrectly = function () {
                            var groups = [];
                            'x'.replace(/x(.)?/g, function (match, group) {
                                groups.push(group);
                            });
                            return groups.length === 1 && typeof groups[0] === 'undefined';
                        }();
                    if (!replaceReportsGroupsCorrectly) {
                        String.prototype.replace = function replace(searchValue, replaceValue) {
                            var isFn = isFunction(replaceValue);
                            var hasCapturingGroups = isRegex(searchValue) && /\)[*?]/.test(searchValue.source);
                            if (!isFn || !hasCapturingGroups) {
                                return str_replace.call(this, searchValue, replaceValue);
                            } else {
                                var wrappedReplaceValue = function (match) {
                                    var length = arguments.length;
                                    var originalLastIndex = searchValue.lastIndex;
                                    searchValue.lastIndex = 0;
                                    var args = searchValue.exec(match);
                                    searchValue.lastIndex = originalLastIndex;
                                    args.push(arguments[length - 2], arguments[length - 1]);
                                    return replaceValue.apply(this, args);
                                };
                                return str_replace.call(this, searchValue, wrappedReplaceValue);
                            }
                        };
                    }
                    if (''.substr && '0b'.substr(-1) !== 'b') {
                        var string_substr = String.prototype.substr;
                        String.prototype.substr = function substr(start, length) {
                            return string_substr.call(this, start < 0 ? (start = this.length + start) < 0 ? 0 : start : start, length);
                        };
                    }
                    var ws = '\t\n\x0B\f\r \xA0\u1680\u180E\u2000\u2001\u2002\u2003' + '\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028' + '\u2029\uFEFF';
                    var zeroWidth = '\u200B';
                    if (!String.prototype.trim || ws.trim() || !zeroWidth.trim()) {
                        ws = '[' + ws + ']';
                        var trimBeginRegexp = new RegExp('^' + ws + ws + '*'), trimEndRegexp = new RegExp(ws + ws + '*$');
                        String.prototype.trim = function trim() {
                            if (this === void 0 || this === null) {
                                throw new TypeError('can\'t convert ' + this + ' to object');
                            }
                            return String(this).replace(trimBeginRegexp, '').replace(trimEndRegexp, '');
                        };
                    }
                    if (parseInt(ws + '08') !== 8 || parseInt(ws + '0x16') !== 22) {
                        parseInt = function (origParseInt) {
                            var hexRegex = /^0[xX]/;
                            return function parseIntES5(str, radix) {
                                str = String(str).trim();
                                if (!Number(radix)) {
                                    radix = hexRegex.test(str) ? 16 : 10;
                                }
                                return origParseInt(str, radix);
                            };
                        }(parseInt);
                    }
                    function toInteger(n) {
                        n = +n;
                        if (n !== n) {
                            n = 0;
                        } else if (n !== 0 && n !== 1 / 0 && n !== -(1 / 0)) {
                            n = (n > 0 || -1) * Math.floor(Math.abs(n));
                        }
                        return n;
                    }
                    function isPrimitive(input) {
                        var type = typeof input;
                        return input === null || type === 'undefined' || type === 'boolean' || type === 'number' || type === 'string';
                    }
                    function toPrimitive(input) {
                        var val, valueOf, toStr;
                        if (isPrimitive(input)) {
                            return input;
                        }
                        valueOf = input.valueOf;
                        if (isFunction(valueOf)) {
                            val = valueOf.call(input);
                            if (isPrimitive(val)) {
                                return val;
                            }
                        }
                        toStr = input.toString;
                        if (isFunction(toStr)) {
                            val = toStr.call(input);
                            if (isPrimitive(val)) {
                                return val;
                            }
                        }
                        throw new TypeError();
                    }
                    var toObject = function (o) {
                        if (o == null) {
                            throw new TypeError('can\'t convert ' + o + ' to object');
                        }
                        return Object(o);
                    };
                    var ToUint32 = function ToUint32(x) {
                        return x >>> 0;
                    };
                }));
            },
            {}
        ],
        10: [
            function (_dereq_, module, exports) {
                (function () {
                    (function (definition) {
                        switch (false) {
                        case !(typeof define === 'function' && define.amd != null):
                            return define([], definition);
                        case typeof exports !== 'object':
                            return module.exports = definition();
                        default:
                            return this.Base = definition();
                        }
                    }(function () {
                        var Base;
                        return Base = function () {
                            Base.displayName = 'Base';
                            var attach, prototype = Base.prototype, constructor = Base;
                            attach = function (obj, name, prop, super$, superclass$) {
                                return obj[name] = typeof prop === 'function' ? function () {
                                    var this$ = this;
                                    prop.superclass$ = superclass$;
                                    prop.super$ = function () {
                                        return super$.apply(this$, arguments);
                                    };
                                    return prop.apply(this, arguments);
                                } : prop;
                            };
                            Base.extend = function (displayName, proto) {
                                proto == null && (proto = displayName);
                                return function (superclass) {
                                    var name, ref$, prop, prototype = extend$(import$(constructor, superclass), superclass).prototype;
                                    import$(constructor, Base);
                                    if (typeof displayName === 'string') {
                                        constructor.displayName = displayName;
                                    }
                                    function constructor() {
                                        var this$ = this instanceof ctor$ ? this : new ctor$();
                                        this$.initialize.apply(this$, arguments);
                                        return this$;
                                    }
                                    function ctor$() {
                                    }
                                    ctor$.prototype = prototype;
                                    prototype.initialize = function () {
                                        if (superclass.prototype.initialize != null) {
                                            return superclass.prototype.initialize.apply(this, arguments);
                                        } else {
                                            return superclass.apply(this, arguments);
                                        }
                                    };
                                    for (name in ref$ = proto) {
                                        prop = ref$[name];
                                        attach(prototype, name, prop, prototype[name], superclass);
                                    }
                                    return constructor;
                                }(this);
                            };
                            Base.meta = function (meta) {
                                var name, prop;
                                for (name in meta) {
                                    prop = meta[name];
                                    attach(this, name, prop, this[name], this);
                                }
                                return this;
                            };
                            prototype.initialize = function () {
                            };
                            function Base() {
                            }
                            return Base;
                        }();
                    }));
                    function extend$(sub, sup) {
                        function fun() {
                        }
                        fun.prototype = (sub.superclass = sup).prototype;
                        (sub.prototype = new fun()).constructor = sub;
                        if (typeof sup.extended == 'function')
                            sup.extended(sub);
                        return sub;
                    }
                    function import$(obj, src) {
                        var own = {}.hasOwnProperty;
                        for (var key in src)
                            if (own.call(src, key))
                                obj[key] = src[key];
                        return obj;
                    }
                }.call(this));
            },
            {}
        ]
    }, {}, [7])(7);
})
