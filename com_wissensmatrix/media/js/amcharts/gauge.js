AmCharts.GaugeAxis = AmCharts.Class({
    construct: function () {
        this.radius = "95%";
        this.startAngle = -120;
        this.endAngle = 120;
        this.startValue = 0;
        this.endValue = 200;
        this.valueInterval = 20;
        this.minorTickInterval = 5;
        this.tickLength = 10;
        this.minorTickLength = 5;
        this.tickColor = "#555555";
        this.labelFrequency = this.tickThickness = this.tickAlpha = 1;
        this.inside = !0;
        this.labelOffset = 15;
        this.showLastLabel = this.showFirstLabel = !0;
        this.axisThickness = 1;
        this.axisColor = "#000000";
        this.axisAlpha = 1;
        this.gridInside = !0;
        this.topText = "";
        this.topTextYOffset =
            0;
        this.topTextBold = !0;
        this.bottomText = "";
        this.bottomTextYOffset = 0;
        this.bottomTextBold = !0;
        this.centerY = this.centerX = "0%";
        this.bandOutlineAlpha = this.bandOutlineThickness = 0;
        this.bandOutlineColor = "#000000";
        this.bandAlpha = 1
    }, value2angle: function (a) {
        return this.startAngle + this.singleValueAngle * a
    }, setTopText: function (a) {
        this.topText = a;
        var b = this.chart;
        if (this.axisCreated) {
            this.topTF && this.topTF.remove();
            var d = this.topTextFontSize;
            d || (d = b.fontSize);
            var c = this.topTextColor;
            c || (c = b.color);
            b = this.chart;
            a = AmCharts.text(b.container,
                a, c, b.fontFamily, d, void 0, this.topTextBold);
            a.translate(this.centerXReal, this.centerYReal - this.radiusReal / 2 + this.topTextYOffset);
            this.chart.graphsSet.push(a);
            this.topTF = a
        }
    }, setBottomText: function (a) {
        this.bottomText = a;
        var b = this.chart;
        if (this.axisCreated) {
            this.bottomTF && this.bottomTF.remove();
            var d = this.bottomTextFontSize;
            d || (d = b.fontSize);
            var c = this.bottomTextColor;
            c || (c = b.color);
            b = this.chart;
            a = AmCharts.text(b.container, a, c, b.fontFamily, d, void 0, this.bottomTextBold);
            a.translate(this.centerXReal, this.centerYReal +
                this.radiusReal / 2 + this.bottomTextYOffset);
            this.bottomTF = a;
            this.chart.graphsSet.push(a)
        }
    }, draw: function () {
        var a = this.chart, b = a.graphsSet, d = this.startValue, c = this.valueInterval, l = this.startAngle, g = this.endAngle, h = this.tickLength, k = (this.endValue - d) / c + 1, f = (g - l) / (k - 1), m = f / c;
        this.singleValueAngle = m;
        var e = a.container, p = this.tickColor, u = this.tickAlpha, B = this.tickThickness, C = c / this.minorTickInterval, D = f / C, E = this.minorTickLength, F = this.labelFrequency, q = this.radiusReal;
        this.inside || (q -= 15);
        var w = a.centerX +
            AmCharts.toCoordinate(this.centerX, a.realWidth), x = a.centerY + AmCharts.toCoordinate(this.centerY, a.realHeight);
        this.centerXReal = w;
        this.centerYReal = x;
        var s = {fill: this.axisColor, "fill-opacity": this.axisAlpha, "stroke-width": 0, "stroke-opacity": 0}, n, z;
        this.gridInside ? z = n = q : (n = q - h, z = n + E);
        var r = this.axisThickness / 2, g = AmCharts.wedge(e, w, x, l, g - l, n + r, n + r, n - r, 0, s);
        b.push(g);
        g = AmCharts.doNothing;
        AmCharts.isModern || (g = Math.round);
        for (s = 0; s < k; s++) {
            r = d + s * c;
            n = l + s * f;
            var v = g(w + q * Math.sin(n / 180 * Math.PI)), t = g(x - q * Math.cos(n /
                    180 * Math.PI)), A = g(w + (q - h) * Math.sin(n / 180 * Math.PI)), y = g(x - (q - h) * Math.cos(n / 180 * Math.PI)), v = AmCharts.line(e, [v, A], [t, y], p, u, B, 0, !1, !1, !0);
            b.push(v);
            t = this.labelOffset;
            this.inside || (t = -t - h);
            v = w + (q - h - t) * Math.sin(n / 180 * Math.PI);
            t = x - (q - h - t) * Math.cos(n / 180 * Math.PI);
            A = this.fontSize;
            isNaN(A) && (A = a.fontSize);
            0 < F && s / F == Math.round(s / F) && (this.showLastLabel || s != k - 1) && (this.showFirstLabel || 0 !== s) && (r = AmCharts.text(e, r, a.color, a.fontFamily, A), r.translate(v, t), b.push(r));
            if (s < k - 1)for (r = 1; r < C; r++)y = n + D * r, v = g(w + z *
                Math.sin(y / 180 * Math.PI)), t = g(x - z * Math.cos(y / 180 * Math.PI)), A = g(w + (z - E) * Math.sin(y / 180 * Math.PI)), y = g(x - (z - E) * Math.cos(y / 180 * Math.PI)), v = AmCharts.line(e, [v, A], [t, y], p, u, B, 0, !1, !1, !0), b.push(v)
        }
        if (b = this.bands)for (d = 0; d < b.length; d++)if (c = b[d])p = c.startValue, u = c.endValue, h = AmCharts.toCoordinate(c.radius, q), isNaN(h) && (h = z), k = AmCharts.toCoordinate(c.innerRadius, q), isNaN(k) && (k = h - E), f = l + m * p, u = m * (u - p), B = c.outlineColor, void 0 == B && (B = this.bandOutlineColor), C = c.outlineThickness, isNaN(C) && (C = this.bandOutlineThickness),
            D = c.outlineAlpha, isNaN(D) && (D = this.bandOutlineAlpha), p = c.alpha, isNaN(p) && (p = this.bandAlpha), c = AmCharts.wedge(e, w, x, f, u, h, h, k, 0, {
            fill: c.color,
            stroke: B,
            "stroke-width": C,
            "stroke-opacity": D
        }), c.setAttr("opacity", p), a.gridSet.push(c);
        this.axisCreated = !0;
        this.setTopText(this.topText);
        this.setBottomText(this.bottomText);
        a = a.graphsSet.getBBox();
        this.width = a.width;
        this.height = a.height
    }
});
AmCharts.GaugeArrow = AmCharts.Class({
    construct: function () {
        this.color = "#000000";
        this.nailAlpha = this.alpha = 1;
        this.startWidth = this.nailRadius = 8;
        this.borderAlpha = 1;
        this.radius = "90%";
        this.nailBorderAlpha = this.innerRadius = 0;
        this.nailBorderThickness = 1;
        this.frame = 0
    }, setValue: function (a) {
        var b = this.chart;
        b ? b.setValue(this, a) : this.previousValue = this.value = a
    }
});
AmCharts.GaugeBand = AmCharts.Class({
    construct: function () {
    }
});
AmCharts.AmAngularGauge = AmCharts.Class({
    inherits: AmCharts.AmChart, construct: function () {
        AmCharts.AmAngularGauge.base.construct.call(this);
        this.minRadius = this.marginRight = this.marginBottom = this.marginTop = this.marginLeft = 10;
        this.faceColor = "#FAFAFA";
        this.faceAlpha = 0;
        this.faceBorderWidth = 1;
        this.faceBorderColor = "#555555";
        this.faceBorderAlpha = 0;
        this.arrows = [];
        this.axes = [];
        this.startDuration = 1;
        this.startEffect = ">";
        this.adjustSize = !0;
        this.extraHeight = this.extraWidth = 0;
        this.clockWiseOnly = !1
    }, addAxis: function (a) {
        a.chart =
            this;
        this.axes.push(a)
    }, initChart: function () {
        AmCharts.AmAngularGauge.base.initChart.call(this);
        if (0 === this.axes.length) {
            var a = new AmCharts.GaugeAxis;
            this.addAxis(a)
        }
        for (var b, a = 0; a < this.arrows.length; a++)b = this.arrows[a], b.axis || (b.axis = this.axes[0]), isNaN(b.value) && b.setValue(b.axis.startValue), isNaN(b.previousValue) && (b.previousValue = b.axis.startValue);
        this.drawChart();
        this.totalFrames = 1E3 * this.startDuration / AmCharts.updateRate
    }, drawChart: function () {
        AmCharts.AmAngularGauge.base.drawChart.call(this);
        var a = this.container, b = this.updateWidth();
        this.realWidth = b;
        var d = this.updateHeight();
        this.realHeight = d;
        var c = AmCharts.toCoordinate, l = c(this.marginLeft, b), g = c(this.marginRight, b), h = c(this.marginTop, d) + this.getTitleHeight(), k = c(this.marginBottom, d), f = c(this.radius, b, d), c = b - l - g, m = d - h - k + this.extraHeight;
        f || (f = Math.min(c, m) / 2);
        f < this.minRadius && (f = this.minRadius);
        this.radiusReal = f;
        this.centerX = (b - l - g) / 2 + l;
        this.centerY = (d - h - k) / 2 + h + this.extraHeight / 2;
        isNaN(this.gaugeX) || (this.centerX = this.gaugeX);
        isNaN(this.gaugeY) ||
        (this.centerY = this.gaugeY);
        var b = this.faceAlpha, d = this.faceBorderAlpha, e;
        if (0 < b || 0 < d)e = AmCharts.circle(a, f, this.faceColor, b, this.faceBorderWidth, this.faceBorderColor, d, !1), e.translate(this.centerX, this.centerY), e.toBack();
        for (b = f = a = 0; b < this.axes.length; b++)d = this.axes[b], d.radiusReal = AmCharts.toCoordinate(d.radius, this.radiusReal), d.draw(), d.width > a && (a = d.width), d.height > f && (f = d.height);
        if (this.adjustSize && !this.chartCreated) {
            e && (e = e.getBBox(), e.width > a && (a = e.width), e.height > f && (f = e.height));
            e = 0;
            if (m > f || c > a)e = Math.min(m - f, c - a);
            0 < e && (this.extraHeight = m - f, this.chartCreated = !0, this.validateNow())
        }
        this.chartCreated = !0
    }, validateSize: function () {
        this.extraHeight = this.extraWidth = 0;
        this.chartCreated = !1;
        AmCharts.AmAngularGauge.base.validateSize.call(this)
    }, addArrow: function (a) {
        a.chart = this;
        this.arrows.push(a)
    }, removeArrow: function (a) {
        AmCharts.removeFromArray(this.arrows, a);
        this.validateNow()
    }, removeAxis: function (a) {
        AmCharts.removeFromArray(this.axes, a);
        this.validateNow()
    }, drawArrow: function (a, b) {
        a.set &&
        a.set.remove();
        var d = a.axis, c = d.radiusReal, l = this.container, g = d.centerXReal, h = d.centerYReal, k = a.startWidth, f = a.innerRadius, m = AmCharts.toCoordinate(a.radius, d.radiusReal);
        d.inside || (m -= 15);
        a.set = l.set();
        var e = a.nailColor;
        e || (e = a.color);
        var p = a.nailColor;
        p || (p = a.color);
        e = AmCharts.circle(l, a.nailRadius, e, a.nailAlpha, a.nailBorderThickness, e, a.nailBorderAlpha);
        a.set.push(e);
        e.translate(g, h);
        isNaN(m) && (m = c - d.tickLength);
        var d = Math.sin(b / 180 * Math.PI), c = Math.cos(b / 180 * Math.PI), e = Math.sin((b + 90) / 180 * Math.PI),
            u = Math.cos((b + 90) / 180 * Math.PI), l = AmCharts.polygon(l, [g - k / 2 * e + f * d, g + m * d, g + k / 2 * e + f * d], [h + k / 2 * u - f * c, h - m * c, h - k / 2 * u - f * c], a.color, a.alpha, 1, p, a.borderAlpha, void 0, !0);
        a.set.push(l);
        this.graphsSet.push(a.set)
    }, setValue: function (a, b) {
        a.axis && (a.axis.value2angle(b), a.frame = 0, a.previousValue = a.value);
        a.value = b
    }, updateAnimations: function () {
        AmCharts.AmAngularGauge.base.updateAnimations.call(this);
        for (var a = this.arrows.length, b, d = 0; d < a; d++) {
            b = this.arrows[d];
            var c;
            b.frame >= this.totalFrames ? c = b.value : (b.frame++,
            b.clockWiseOnly && b.value < b.previousValue && (c = b.axis, b.previousValue -= c.endValue - c.startValue), c = AmCharts.getEffect(this.startEffect), c = AmCharts[c](0, b.frame, b.previousValue, b.value - b.previousValue, this.totalFrames), isNaN(c) && (c = b.value));
            c = b.axis.value2angle(c);
            this.drawArrow(b, c)
        }
    }
});