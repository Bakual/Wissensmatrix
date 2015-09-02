AmCharts.AmFunnelChart = AmCharts.Class({
    inherits: AmCharts.AmSlicedChart, construct: function () {
        AmCharts.AmFunnelChart.base.construct.call(this);
        this.startX = this.startY = 0;
        this.baseWidth = "100%";
        this.neckHeight = this.neckWidth = 0;
        this.rotate = !1;
        this.valueRepresents = "height";
        this.pullDistance = 30;
        this.labelPosition = "center";
        this.labelText = "[[title]]: [[value]]";
        this.balloonText = "[[title]]: [[value]]\n[[description]]"
    }, drawChart: function () {
        AmCharts.AmFunnelChart.base.drawChart.call(this);
        var y = this.chartData;
        if (AmCharts.ifArray(y))if (0 < this.realWidth && 0 < this.realHeight) {
            var r = this.container, A = this.startDuration, k = this.rotate, u = this.updateWidth();
            this.realWidth = u;
            var f = this.updateHeight();
            this.realHeight = f;
            var n = AmCharts.toCoordinate, B = n(this.marginLeft, u), t = n(this.marginRight, u), a = n(this.marginTop, f) + this.getTitleHeight(), n = n(this.marginBottom, f), t = u - B - t, v = AmCharts.toCoordinate(this.baseWidth, t), p = AmCharts.toCoordinate(this.neckWidth, t), C = f - n - a, w = AmCharts.toCoordinate(this.neckHeight, C), s = a + C - w;
            k && (a =
                f - n, s = a - C + w);
            this.firstSliceY = a;
            AmCharts.VML && (this.startAlpha = 1);
            for (var g = t / 2 + B, D = (C - w) / ((v - p) / 2), x = v / 2, v = (C - w) * (v + p) / 2 + p * w, w = a, F = 0, E = 0; E < y.length; E++) {
                var c = y[E];
                if (!0 !== c.hidden) {
                    var l = [], h = [], b;
                    if ("height" == this.valueRepresents)b = C * c.percents / 100; else {
                        var m = -v * c.percents / 100 / 2, z = x, d = -1 / (2 * D);
                        b = Math.pow(z, 2) - 4 * d * m;
                        0 > b && (b = 0);
                        b = (Math.sqrt(b) - z) / (2 * d);
                        if (!k && a >= s || k && a <= s)b = 2 * -m / p; else if (!k && a + b > s || k && a - b < s)d = k ? Math.round(b + (a - b - s)) : Math.round(b - (a + b - s)), b = d / D, b = d + 2 * (-m - (z - b / 2) * d) / p
                    }
                    m = x - b / D;
                    z = !1;
                    !k && a + b > s || k && a - b < s ? (m = p / 2, l.push(g - x, g + x, g + m, g + m, g - m, g - m), k ? (d = b + (a - b - s), h.push(a, a, a - d, a - b, a - b, a - d, a)) : (d = b - (a + b - s), h.push(a, a, a + d, a + b, a + b, a + d, a)), z = !0) : (l.push(g - x, g + x, g + m, g - m), k ? h.push(a, a, a - b, a - b) : h.push(a, a, a + b, a + b));
                    r.set();
                    d = r.set();
                    l = AmCharts.polygon(r, l, h, c.color, c.alpha, this.outlineThickness, this.outlineColor, this.outlineAlpha);
                    d.push(l);
                    this.graphsSet.push(d);
                    c.wedge = d;
                    c.index = E;
                    if (h = this.gradientRatio) {
                        var q = [], e;
                        for (e = 0; e < h.length; e++)q.push(AmCharts.adjustLuminosity(c.color, h[e]));
                        0 < q.length && l.gradient("linearGradient", q);
                        c.pattern && l.pattern(c.pattern)
                    }
                    0 < A && (l = this.startAlpha, this.chartCreated && (l = c.alpha), d.setAttr("opacity", l));
                    this.addEventListeners(d, c);
                    this.labelsEnabled && this.labelText && c.percents >= this.hideLabelsPercent && (h = this.formatString(this.labelText, c), q = c.labelColor, q || (q = this.color), l = this.labelPosition, e = "left", "center" == l && (e = "middle"), "left" == l && (e = "right"), h = AmCharts.text(r, h, q, this.fontFamily, this.fontSize, e), d.push(h), q = g, k ? (e = a - b / 2, c.ty0 = e) : (e = a + b /
                        2, c.ty0 = e, e < w + F + 5 && (e = w + F + 5), e > f - n && (e = f - n)), "right" == l && (q = t + 10 + B, c.tx0 = g + (x - b / 2 / D), z && (c.tx0 = g + m)), "left" == l && (c.tx0 = g - (x - b / 2 / D), z && (c.tx0 = g - m), q = B), c.label = h, c.labelX = q, c.labelY = e, c.labelHeight = h.getBBox().height, h.translate(q, e), (0 === c.alpha || 0 < A && !this.chartCreated) && d.hide(), a = k ? a - b : a + b, x = m, F = h.getBBox().height, w = e);
                    c.startX = AmCharts.toCoordinate(this.startX, u);
                    c.startY = AmCharts.toCoordinate(this.startY, f);
                    c.pullX = AmCharts.toCoordinate(this.pullDistance, u);
                    c.pullY = 0;
                    c.balloonX = g;
                    c.balloonY =
                        c.ty0
                }
            }
            this.arrangeLabels();
            this.initialStart();
            (y = this.legend) && y.invalidateSize()
        } else this.cleanChart();
        this.dispDUpd();
        this.chartCreated = !0
    }, arrangeLabels: function () {
        var y = this.rotate, r;
        r = y ? 0 : this.realHeight;
        for (var A = 0, k = this.chartData, u = k.length, f, n = 0; n < u; n++) {
            f = k[u - n - 1];
            var B = f.label, t = f.labelY, a = f.labelX, v = f.labelHeight, p = t;
            y ? r + A + 5 > t && (p = r + A + 5) : t + v + 5 > r && (p = r - 5 - v);
            r = p;
            A = v;
            B.translate(a, p);
            f.labelY = p;
            f.tx = a;
            f.ty = p;
            f.tx2 = a
        }
        "center" != this.labelPosition && this.drawTicks()
    }
});