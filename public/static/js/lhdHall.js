! function(e, t) {
    function n(e) { var t = de[e] = {}; return Q.each(e.split(ee), function(e, n) { t[n] = !0 }), t }

    function r(e, n, r) { if (r === t && 1 === e.nodeType) { var i = "data-" + n.replace(ge, "-$1").toLowerCase(); if ("string" == typeof(r = e.getAttribute(i))) { try { r = "true" === r || "false" !== r && ("null" === r ? null : +r + "" === r ? +r : he.test(r) ? Q.parseJSON(r) : r) } catch (e) {} Q.data(e, n, r) } else r = t } return r }

    function i(e) { var t; for (t in e)
            if (("data" !== t || !Q.isEmptyObject(e[t])) && "toJSON" !== t) return !1; return !0 }

    function o() { return !1 }

    function a() { return !0 }

    function s(e) { return !e || !e.parentNode || 11 === e.parentNode.nodeType }

    function l(e, t) { do { e = e[t] } while (e && 1 !== e.nodeType); return e }

    function u(e, t, n) { if (t = t || 0, Q.isFunction(t)) return Q.grep(e, function(e, r) { return !!t.call(e, r, e) === n }); if (t.nodeType) return Q.grep(e, function(e, r) { return e === t === n }); if ("string" == typeof t) { var r = Q.grep(e, function(e) { return 1 === e.nodeType }); if (Oe.test(t)) return Q.filter(t, r, !n);
            t = Q.filter(t, r) } return Q.grep(e, function(e, r) { return Q.inArray(e, t) >= 0 === n }) }

    function c(e) { var t = Be.split("|"),
            n = e.createDocumentFragment(); if (n.createElement)
            for (; t.length;) n.createElement(t.pop()); return n }

    function f(e, t) { return e.getElementsByTagName(t)[0] || e.appendChild(e.ownerDocument.createElement(t)) }

    function p(e, t) { if (1 === t.nodeType && Q.hasData(e)) { var n, r, i, o = Q._data(e),
                a = Q._data(t, o),
                s = o.events; if (s) { delete a.handle, a.events = {}; for (n in s)
                    for (r = 0, i = s[n].length; r < i; r++) Q.event.add(t, n, s[n][r]) } a.data && (a.data = Q.extend({}, a.data)) } }

    function d(e, t) { var n;
        1 === t.nodeType && (t.clearAttributes && t.clearAttributes(), t.mergeAttributes && t.mergeAttributes(e), "object" === (n = t.nodeName.toLowerCase()) ? (t.parentNode && (t.outerHTML = e.outerHTML), Q.support.html5Clone && e.innerHTML && !Q.trim(t.innerHTML) && (t.innerHTML = e.innerHTML)) : "input" === n && Ve.test(e.type) ? (t.defaultChecked = t.checked = e.checked, t.value !== e.value && (t.value = e.value)) : "option" === n ? t.selected = e.defaultSelected : "input" === n || "textarea" === n ? t.defaultValue = e.defaultValue : "script" === n && t.text !== e.text && (t.text = e.text), t.removeAttribute(Q.expando)) }

    function h(e) { return void 0 !== e.getElementsByTagName ? e.getElementsByTagName("*") : void 0 !== e.querySelectorAll ? e.querySelectorAll("*") : [] }

    function g(e) { Ve.test(e.type) && (e.defaultChecked = e.checked) }

    function m(e, t) { if (t in e) return t; for (var n = t.charAt(0).toUpperCase() + t.slice(1), r = t, i = mt.length; i--;)
            if ((t = mt[i] + n) in e) return t; return r }

    function y(e, t) { return e = t || e, "none" === Q.css(e, "display") || !Q.contains(e.ownerDocument, e) }

    function v(e, t) { for (var n, r, i = [], o = 0, a = e.length; o < a; o++)(n = e[o]).style && (i[o] = Q._data(n, "olddisplay"), t ? (!i[o] && "none" === n.style.display && (n.style.display = ""), "" === n.style.display && y(n) && (i[o] = Q._data(n, "olddisplay", T(n.nodeName)))) : (r = tt(n, "display"), !i[o] && "none" !== r && Q._data(n, "olddisplay", r))); for (o = 0; o < a; o++)(n = e[o]).style && (t && "none" !== n.style.display && "" !== n.style.display || (n.style.display = t ? i[o] || "" : "none")); return e }

    function b(e, t, n) { var r = ut.exec(t); return r ? Math.max(0, r[1] - (n || 0)) + (r[2] || "px") : t }

    function x(e, t, n, r) { for (var i = n === (r ? "border" : "content") ? 4 : "width" === t ? 1 : 0, o = 0; i < 4; i += 2) "margin" === n && (o += Q.css(e, n + gt[i], !0)), r ? ("content" === n && (o -= parseFloat(tt(e, "padding" + gt[i])) || 0), "margin" !== n && (o -= parseFloat(tt(e, "border" + gt[i] + "Width")) || 0)) : (o += parseFloat(tt(e, "padding" + gt[i])) || 0, "padding" !== n && (o += parseFloat(tt(e, "border" + gt[i] + "Width")) || 0)); return o }

    function w(e, t, n) { var r = "width" === t ? e.offsetWidth : e.offsetHeight,
            i = !0,
            o = Q.support.boxSizing && "border-box" === Q.css(e, "boxSizing"); if (r <= 0 || null == r) { if (((r = tt(e, t)) < 0 || null == r) && (r = e.style[t]), ct.test(r)) return r;
            i = o && (Q.support.boxSizingReliable || r === e.style[t]), r = parseFloat(r) || 0 } return r + x(e, t, n || (o ? "border" : "content"), i) + "px" }

    function T(e) { if (pt[e]) return pt[e]; var t = Q("<" + e + ">").appendTo(P.body),
            n = t.css("display"); return t.remove(), "none" !== n && "" !== n || (nt = P.body.appendChild(nt || Q.extend(P.createElement("iframe"), { frameBorder: 0, width: 0, height: 0 })), rt && nt.createElement || ((rt = (nt.contentWindow || nt.contentDocument).document).write("<!doctype html><html><body>"), rt.close()), t = rt.body.appendChild(rt.createElement(e)), n = tt(t, "display"), P.body.removeChild(nt)), pt[e] = n, n }

    function N(e, t, n, r) { var i; if (Q.isArray(t)) Q.each(t, function(t, i) { n || bt.test(e) ? r(e, i) : N(e + "[" + ("object" == typeof i ? t : "") + "]", i, n, r) });
        else if (n || "object" !== Q.type(t)) r(e, t);
        else
            for (i in t) N(e + "[" + i + "]", t[i], n, r) }

    function C(e) { return function(t, n) { "string" != typeof t && (n = t, t = "*"); var r, i, o = t.toLowerCase().split(ee),
                a = 0,
                s = o.length; if (Q.isFunction(n))
                for (; a < s; a++) r = o[a], (i = /^\+/.test(r)) && (r = r.substr(1) || "*"), (e[r] = e[r] || [])[i ? "unshift" : "push"](n) } }

    function k(e, n, r, i, o, a) { o = o || n.dataTypes[0], (a = a || {})[o] = !0; for (var s, l = e[o], u = 0, c = l ? l.length : 0, f = e === Ot; u < c && (f || !s); u++) "string" == typeof(s = l[u](n, r, i)) && (!f || a[s] ? s = t : (n.dataTypes.unshift(s), s = k(e, n, r, i, s, a))); return (f || !s) && !a["*"] && (s = k(e, n, r, i, "*", a)), s }

    function E(e, n) { var r, i, o = Q.ajaxSettings.flatOptions || {}; for (r in n) n[r] !== t && ((o[r] ? e : i || (i = {}))[r] = n[r]);
        i && Q.extend(!0, e, i) }

    function S(e, n, r) { var i, o, a, s, l = e.contents,
            u = e.dataTypes,
            c = e.responseFields; for (o in c) o in r && (n[c[o]] = r[o]); for (;
            "*" === u[0];) u.shift(), i === t && (i = e.mimeType || n.getResponseHeader("content-type")); if (i)
            for (o in l)
                if (l[o] && l[o].test(i)) { u.unshift(o); break }
        if (u[0] in r) a = u[0];
        else { for (o in r) { if (!u[0] || e.converters[o + " " + u[0]]) { a = o; break } s || (s = o) } a = a || s } if (a) return a !== u[0] && u.unshift(a), r[a] }

    function A(e, t) { var n, r, i, o, a = e.dataTypes.slice(),
            s = a[0],
            l = {},
            u = 0; if (e.dataFilter && (t = e.dataFilter(t, e.dataType)), a[1])
            for (n in e.converters) l[n.toLowerCase()] = e.converters[n]; for (; i = a[++u];)
            if ("*" !== i) { if ("*" !== s && s !== i) { if (!(n = l[s + " " + i] || l["* " + i]))
                        for (r in l)
                            if ((o = r.split(" "))[1] === i && (n = l[s + " " + o[0]] || l["* " + o[0]])) {!0 === n ? n = l[r] : !0 !== l[r] && (i = o[0], a.splice(u--, 0, i)); break }
                    if (!0 !== n)
                        if (n && e.throws) t = n(t);
                        else try { t = n(t) } catch (e) { return { state: "parsererror", error: n ? e : "No conversion from " + s + " to " + i } } } s = i }
        return { state: "success", data: t } }

    function j() { try { return new e.XMLHttpRequest } catch (e) {} }

    function D() { try { return new e.ActiveXObject("Microsoft.XMLHTTP") } catch (e) {} }

    function L() { return setTimeout(function() { Xt = t }, 0), Xt = Q.now() }

    function H(e, t) { Q.each(t, function(t, n) { for (var r = (Qt[t] || []).concat(Qt["*"]), i = 0, o = r.length; i < o; i++)
                if (r[i].call(e, t, n)) return }) }

    function F(e, t, n) { var r, i = 0,
            o = Gt.length,
            a = Q.Deferred().always(function() { delete s.elem }),
            s = function() { for (var t = Xt || L(), n = Math.max(0, l.startTime + l.duration - t), r = 1 - (n / l.duration || 0), i = 0, o = l.tweens.length; i < o; i++) l.tweens[i].run(r); return a.notifyWith(e, [l, r, n]), r < 1 && o ? n : (a.resolveWith(e, [l]), !1) },
            l = a.promise({ elem: e, props: Q.extend({}, t), opts: Q.extend(!0, { specialEasing: {} }, n), originalProperties: t, originalOptions: n, startTime: Xt || L(), duration: n.duration, tweens: [], createTween: function(t, n, r) { var i = Q.Tween(e, l.opts, t, n, l.opts.specialEasing[t] || l.opts.easing); return l.tweens.push(i), i }, stop: function(t) { for (var n = 0, r = t ? l.tweens.length : 0; n < r; n++) l.tweens[n].run(1); return t ? a.resolveWith(e, [l, t]) : a.rejectWith(e, [l, t]), this } }),
            u = l.props; for (M(u, l.opts.specialEasing); i < o; i++)
            if (r = Gt[i].call(l, e, u, l.opts)) return r; return H(l, u), Q.isFunction(l.opts.start) && l.opts.start.call(e, l), Q.fx.timer(Q.extend(s, { anim: l, queue: l.opts.queue, elem: e })), l.progress(l.opts.progress).done(l.opts.done, l.opts.complete).fail(l.opts.fail).always(l.opts.always) }

    function M(e, t) { var n, r, i, o, a; for (n in e)
            if (r = Q.camelCase(n), i = t[r], o = e[n], Q.isArray(o) && (i = o[1], o = e[n] = o[0]), n !== r && (e[r] = o, delete e[n]), (a = Q.cssHooks[r]) && "expand" in a) { o = a.expand(o), delete e[r]; for (n in o) n in e || (e[n] = o[n], t[n] = i) } else t[r] = i }

    function O(e, t, n, r, i) { return new O.prototype.init(e, t, n, r, i) }

    function _(e, t) { var n, r = { height: e },
            i = 0; for (t = t ? 1 : 0; i < 4; i += 2 - t) n = gt[i], r["margin" + n] = r["padding" + n] = e; return t && (r.opacity = r.width = e), r }

    function q(e) { return Q.isWindow(e) ? e : 9 === e.nodeType && (e.defaultView || e.parentWindow) } var B, W, P = e.document,
        R = e.location,
        $ = e.navigator,
        I = e.jQuery,
        z = e.$,
        X = Array.prototype.push,
        U = Array.prototype.slice,
        Y = Array.prototype.indexOf,
        V = Object.prototype.toString,
        J = Object.prototype.hasOwnProperty,
        G = String.prototype.trim,
        Q = function(e, t) { return new Q.fn.init(e, t, B) },
        K = /[\-+]?(?:\d*\.|)\d+(?:[eE][\-+]?\d+|)/.source,
        Z = /\S/,
        ee = /\s+/,
        te = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,
        ne = /^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,
        re = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
        ie = /^[\],:{}\s]*$/,
        oe = /(?:^|:|,)(?:\s*\[)+/g,
        ae = /\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,
        se = /"[^"\\\r\n]*"|true|false|null|-?(?:\d\d*\.|)\d+(?:[eE][\-+]?\d+|)/g,
        le = /^-ms-/,
        ue = /-([\da-z])/gi,
        ce = function(e, t) { return (t + "").toUpperCase() },
        fe = function() { P.addEventListener ? (P.removeEventListener("DOMContentLoaded", fe, !1), Q.ready()) : "complete" === P.readyState && (P.detachEvent("onreadystatechange", fe), Q.ready()) },
        pe = {};
    Q.fn = Q.prototype = { constructor: Q, init: function(e, n, r) { var i, o, a; if (!e) return this; if (e.nodeType) return this.context = this[0] = e, this.length = 1, this; if ("string" == typeof e) { if ((i = "<" === e.charAt(0) && ">" === e.charAt(e.length - 1) && e.length >= 3 ? [null, e, null] : ne.exec(e)) && (i[1] || !n)) { if (i[1]) return n = n instanceof Q ? n[0] : n, a = n && n.nodeType ? n.ownerDocument || n : P, e = Q.parseHTML(i[1], a, !0), re.test(i[1]) && Q.isPlainObject(n) && this.attr.call(e, n, !0), Q.merge(this, e); if ((o = P.getElementById(i[2])) && o.parentNode) { if (o.id !== i[2]) return r.find(e);
                        this.length = 1, this[0] = o } return this.context = P, this.selector = e, this } return !n || n.jquery ? (n || r).find(e) : this.constructor(n).find(e) } return Q.isFunction(e) ? r.ready(e) : (e.selector !== t && (this.selector = e.selector, this.context = e.context), Q.makeArray(e, this)) }, selector: "", jquery: "1.8.3", length: 0, size: function() { return this.length }, toArray: function() { return U.call(this) }, get: function(e) { return null == e ? this.toArray() : e < 0 ? this[this.length + e] : this[e] }, pushStack: function(e, t, n) { var r = Q.merge(this.constructor(), e); return r.prevObject = this, r.context = this.context, "find" === t ? r.selector = this.selector + (this.selector ? " " : "") + n : t && (r.selector = this.selector + "." + t + "(" + n + ")"), r }, each: function(e, t) { return Q.each(this, e, t) }, ready: function(e) { return Q.ready.promise().done(e), this }, eq: function(e) { return -1 == (e = +e) ? this.slice(e) : this.slice(e, e + 1) }, first: function() { return this.eq(0) }, last: function() { return this.eq(-1) }, slice: function() { return this.pushStack(U.apply(this, arguments), "slice", U.call(arguments).join(",")) }, map: function(e) { return this.pushStack(Q.map(this, function(t, n) { return e.call(t, n, t) })) }, end: function() { return this.prevObject || this.constructor(null) }, push: X, sort: [].sort, splice: [].splice }, Q.fn.init.prototype = Q.fn, Q.extend = Q.fn.extend = function() { var e, n, r, i, o, a, s = arguments[0] || {},
            l = 1,
            u = arguments.length,
            c = !1; for ("boolean" == typeof s && (c = s, s = arguments[1] || {}, l = 2), "object" != typeof s && !Q.isFunction(s) && (s = {}), u === l && (s = this, --l); l < u; l++)
            if (null != (e = arguments[l]))
                for (n in e) r = s[n], s !== (i = e[n]) && (c && i && (Q.isPlainObject(i) || (o = Q.isArray(i))) ? (o ? (o = !1, a = r && Q.isArray(r) ? r : []) : a = r && Q.isPlainObject(r) ? r : {}, s[n] = Q.extend(c, a, i)) : i !== t && (s[n] = i)); return s }, Q.extend({ noConflict: function(t) { return e.$ === Q && (e.$ = z), t && e.jQuery === Q && (e.jQuery = I), Q }, isReady: !1, readyWait: 1, holdReady: function(e) { e ? Q.readyWait++ : Q.ready(!0) }, ready: function(e) { if (!0 === e ? !--Q.readyWait : !Q.isReady) { if (!P.body) return setTimeout(Q.ready, 1);
                Q.isReady = !0, !0 !== e && --Q.readyWait > 0 || (W.resolveWith(P, [Q]), Q.fn.trigger && Q(P).trigger("ready").off("ready")) } }, isFunction: function(e) { return "function" === Q.type(e) }, isArray: Array.isArray || function(e) { return "array" === Q.type(e) }, isWindow: function(e) { return null != e && e == e.window }, isNumeric: function(e) { return !isNaN(parseFloat(e)) && isFinite(e) }, type: function(e) { return null == e ? String(e) : pe[V.call(e)] || "object" }, isPlainObject: function(e) { if (!e || "object" !== Q.type(e) || e.nodeType || Q.isWindow(e)) return !1; try { if (e.constructor && !J.call(e, "constructor") && !J.call(e.constructor.prototype, "isPrototypeOf")) return !1 } catch (e) { return !1 } var n; for (n in e); return n === t || J.call(e, n) }, isEmptyObject: function(e) { var t; for (t in e) return !1; return !0 }, error: function(e) { throw new Error(e) }, parseHTML: function(e, t, n) { var r; return e && "string" == typeof e ? ("boolean" == typeof t && (n = t, t = 0), t = t || P, (r = re.exec(e)) ? [t.createElement(r[1])] : (r = Q.buildFragment([e], t, n ? null : []), Q.merge([], (r.cacheable ? Q.clone(r.fragment) : r.fragment).childNodes))) : null }, parseJSON: function(t) { return t && "string" == typeof t ? (t = Q.trim(t), e.JSON && e.JSON.parse ? e.JSON.parse(t) : ie.test(t.replace(ae, "@").replace(se, "]").replace(oe, "")) ? new Function("return " + t)() : void Q.error("Invalid JSON: " + t)) : null }, parseXML: function(n) { var r, i; if (!n || "string" != typeof n) return null; try { e.DOMParser ? (i = new DOMParser, r = i.parseFromString(n, "text/xml")) : (r = new ActiveXObject("Microsoft.XMLDOM"), r.async = "false", r.loadXML(n)) } catch (e) { r = t } return (!r || !r.documentElement || r.getElementsByTagName("parsererror").length) && Q.error("Invalid XML: " + n), r }, noop: function() {}, globalEval: function(t) { t && Z.test(t) && (e.execScript || function(t) { e.eval.call(e, t) })(t) }, camelCase: function(e) { return e.replace(le, "ms-").replace(ue, ce) }, nodeName: function(e, t) { return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase() }, each: function(e, n, r) { var i, o = 0,
                a = e.length,
                s = a === t || Q.isFunction(e); if (r)
                if (s) { for (i in e)
                        if (!1 === n.apply(e[i], r)) break } else
                    for (; o < a && !1 !== n.apply(e[o++], r););
            else if (s) { for (i in e)
                    if (!1 === n.call(e[i], i, e[i])) break } else
                for (; o < a && !1 !== n.call(e[o], o, e[o++]);); return e }, trim: G && !G.call("\ufeff ") ? function(e) { return null == e ? "" : G.call(e) } : function(e) { return null == e ? "" : (e + "").replace(te, "") }, makeArray: function(e, t) { var n, r = t || []; return null != e && (n = Q.type(e), null == e.length || "string" === n || "function" === n || "regexp" === n || Q.isWindow(e) ? X.call(r, e) : Q.merge(r, e)), r }, inArray: function(e, t, n) { var r; if (t) { if (Y) return Y.call(t, e, n); for (r = t.length, n = n ? n < 0 ? Math.max(0, r + n) : n : 0; n < r; n++)
                    if (n in t && t[n] === e) return n } return -1 }, merge: function(e, n) { var r = n.length,
                i = e.length,
                o = 0; if ("number" == typeof r)
                for (; o < r; o++) e[i++] = n[o];
            else
                for (; n[o] !== t;) e[i++] = n[o++]; return e.length = i, e }, grep: function(e, t, n) { var r, i = [],
                o = 0,
                a = e.length; for (n = !!n; o < a; o++) r = !!t(e[o], o), n !== r && i.push(e[o]); return i }, map: function(e, n, r) { var i, o, a = [],
                s = 0,
                l = e.length; if (e instanceof Q || l !== t && "number" == typeof l && (l > 0 && e[0] && e[l - 1] || 0 === l || Q.isArray(e)))
                for (; s < l; s++) null != (i = n(e[s], s, r)) && (a[a.length] = i);
            else
                for (o in e) null != (i = n(e[o], o, r)) && (a[a.length] = i); return a.concat.apply([], a) }, guid: 1, proxy: function(e, n) { var r, i, o; return "string" == typeof n && (r = e[n], n = e, e = r), Q.isFunction(e) ? (i = U.call(arguments, 2), o = function() { return e.apply(n, i.concat(U.call(arguments))) }, o.guid = e.guid = e.guid || Q.guid++, o) : t }, access: function(e, n, r, i, o, a, s) { var l, u = null == r,
                c = 0,
                f = e.length; if (r && "object" == typeof r) { for (c in r) Q.access(e, n, c, r[c], 1, a, i);
                o = 1 } else if (i !== t) { if (l = s === t && Q.isFunction(i), u && (l ? (l = n, n = function(e, t, n) { return l.call(Q(e), n) }) : (n.call(e, i), n = null)), n)
                    for (; c < f; c++) n(e[c], r, l ? i.call(e[c], c, n(e[c], r)) : i, s);
                o = 1 } return o ? e : u ? n.call(e) : f ? n(e[0], r) : a }, now: function() { return (new Date).getTime() } }), Q.ready.promise = function(t) { if (!W)
            if (W = Q.Deferred(), "complete" === P.readyState) setTimeout(Q.ready, 1);
            else if (P.addEventListener) P.addEventListener("DOMContentLoaded", fe, !1), e.addEventListener("load", Q.ready, !1);
        else { P.attachEvent("onreadystatechange", fe), e.attachEvent("onload", Q.ready); var n = !1; try { n = null == e.frameElement && P.documentElement } catch (e) {} n && n.doScroll && function e() { if (!Q.isReady) { try { n.doScroll("left") } catch (t) { return setTimeout(e, 50) } Q.ready() } }() } return W.promise(t) }, Q.each("Boolean Number String Function Array Date RegExp Object".split(" "), function(e, t) { pe["[object " + t + "]"] = t.toLowerCase() }), B = Q(P); var de = {};
    Q.Callbacks = function(e) { var r, i, o, a, s, l, u = [],
            c = !(e = "string" == typeof e ? de[e] || n(e) : Q.extend({}, e)).once && [],
            f = function(t) { for (r = e.memory && t, i = !0, l = a || 0, a = 0, s = u.length, o = !0; u && l < s; l++)
                    if (!1 === u[l].apply(t[0], t[1]) && e.stopOnFalse) { r = !1; break }
                o = !1, u && (c ? c.length && f(c.shift()) : r ? u = [] : p.disable()) },
            p = { add: function() { if (u) { var t = u.length;
                        (function t(n) { Q.each(n, function(n, r) { var i = Q.type(r); "function" === i ? (!e.unique || !p.has(r)) && u.push(r) : r && r.length && "string" !== i && t(r) }) })(arguments), o ? s = u.length : r && (a = t, f(r)) } return this }, remove: function() { return u && Q.each(arguments, function(e, t) { for (var n;
                            (n = Q.inArray(t, u, n)) > -1;) u.splice(n, 1), o && (n <= s && s--, n <= l && l--) }), this }, has: function(e) { return Q.inArray(e, u) > -1 }, empty: function() { return u = [], this }, disable: function() { return u = c = r = t, this }, disabled: function() { return !u }, lock: function() { return c = t, r || p.disable(), this }, locked: function() { return !c }, fireWith: function(e, t) { return t = t || [], t = [e, t.slice ? t.slice() : t], u && (!i || c) && (o ? c.push(t) : f(t)), this }, fire: function() { return p.fireWith(this, arguments), this }, fired: function() { return !!i } }; return p }, Q.extend({ Deferred: function(e) { var t = [
                    ["resolve", "done", Q.Callbacks("once memory"), "resolved"],
                    ["reject", "fail", Q.Callbacks("once memory"), "rejected"],
                    ["notify", "progress", Q.Callbacks("memory")]
                ],
                n = "pending",
                r = { state: function() { return n }, always: function() { return i.done(arguments).fail(arguments), this }, then: function() { var e = arguments; return Q.Deferred(function(n) { Q.each(t, function(t, r) { var o = r[0],
                                    a = e[t];
                                i[r[1]](Q.isFunction(a) ? function() { var e = a.apply(this, arguments);
                                    e && Q.isFunction(e.promise) ? e.promise().done(n.resolve).fail(n.reject).progress(n.notify) : n[o + "With"](this === i ? n : this, [e]) } : n[o]) }), e = null }).promise() }, promise: function(e) { return null != e ? Q.extend(e, r) : r } },
                i = {}; return r.pipe = r.then, Q.each(t, function(e, o) { var a = o[2],
                    s = o[3];
                r[o[1]] = a.add, s && a.add(function() { n = s }, t[1 ^ e][2].disable, t[2][2].lock), i[o[0]] = a.fire, i[o[0] + "With"] = a.fireWith }), r.promise(i), e && e.call(i, i), i }, when: function(e) { var t, n, r, i = 0,
                o = U.call(arguments),
                a = o.length,
                s = 1 !== a || e && Q.isFunction(e.promise) ? a : 0,
                l = 1 === s ? e : Q.Deferred(),
                u = function(e, n, r) { return function(i) { n[e] = this, r[e] = arguments.length > 1 ? U.call(arguments) : i, r === t ? l.notifyWith(n, r) : --s || l.resolveWith(n, r) } }; if (a > 1)
                for (t = new Array(a), n = new Array(a), r = new Array(a); i < a; i++) o[i] && Q.isFunction(o[i].promise) ? o[i].promise().done(u(i, r, o)).fail(l.reject).progress(u(i, n, t)) : --s; return s || l.resolveWith(r, o), l.promise() } }), Q.support = function() { var t, n, r, i, o, a, s, l, u, c, f, p = P.createElement("div"); if (p.setAttribute("className", "t"), p.innerHTML = "  <link/><table></table><a data-ajax=false href='/a'>a</a><input type='checkbox'/>", n = p.getElementsByTagName("*"), r = p.getElementsByTagName("a")[0], !n || !r || !n.length) return {};
        o = (i = P.createElement("select")).appendChild(P.createElement("option")), a = p.getElementsByTagName("input")[0], r.style.cssText = "top:1px;float:left;opacity:.5", t = { leadingWhitespace: 3 === p.firstChild.nodeType, tbody: !p.getElementsByTagName("tbody").length, htmlSerialize: !!p.getElementsByTagName("link").length, style: /top/.test(r.getAttribute("style")), hrefNormalized: "/a" === r.getAttribute("href"), opacity: /^0.5/.test(r.style.opacity), cssFloat: !!r.style.cssFloat, checkOn: "on" === a.value, optSelected: o.selected, getSetAttribute: "t" !== p.className, enctype: !!P.createElement("form").enctype, html5Clone: "<:nav></:nav>" !== P.createElement("nav").cloneNode(!0).outerHTML, boxModel: "CSS1Compat" === P.compatMode, submitBubbles: !0, changeBubbles: !0, focusinBubbles: !1, deleteExpando: !0, noCloneEvent: !0, inlineBlockNeedsLayout: !1, shrinkWrapBlocks: !1, reliableMarginRight: !0, boxSizingReliable: !0, pixelPosition: !1 }, a.checked = !0, t.noCloneChecked = a.cloneNode(!0).checked, i.disabled = !0, t.optDisabled = !o.disabled; try { delete p.test } catch (e) { t.deleteExpando = !1 } if (!p.addEventListener && p.attachEvent && p.fireEvent && (p.attachEvent("onclick", f = function() { t.noCloneEvent = !1 }), p.cloneNode(!0).fireEvent("onclick"), p.detachEvent("onclick", f)), a = P.createElement("input"), a.value = "t", a.setAttribute("type", "radio"), t.radioValue = "t" === a.value, a.setAttribute("checked", "checked"), a.setAttribute("name", "t"), p.appendChild(a), (s = P.createDocumentFragment()).appendChild(p.lastChild), t.checkClone = s.cloneNode(!0).cloneNode(!0).lastChild.checked, t.appendChecked = a.checked, s.removeChild(a), s.appendChild(p), p.attachEvent)
            for (u in { submit: !0, change: !0, focusin: !0 }) l = "on" + u, (c = l in p) || (p.setAttribute(l, "return;"), c = "function" == typeof p[l]), t[u + "Bubbles"] = c; return Q(function() { var n, r, i, o, a = "padding:0;margin:0;border:0;display:block;overflow:hidden;",
                s = P.getElementsByTagName("body")[0];
            s && ((n = P.createElement("div")).style.cssText = "visibility:hidden;border:0;width:0;height:0;position:static;top:0;margin-top:1px", s.insertBefore(n, s.firstChild), r = P.createElement("div"), n.appendChild(r), r.innerHTML = "<table><tr><td></td><td>t</td></tr></table>", (i = r.getElementsByTagName("td"))[0].style.cssText = "padding:0;margin:0;border:0;display:none", c = 0 === i[0].offsetHeight, i[0].style.display = "", i[1].style.display = "none", t.reliableHiddenOffsets = c && 0 === i[0].offsetHeight, r.innerHTML = "", r.style.cssText = "box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;", t.boxSizing = 4 === r.offsetWidth, t.doesNotIncludeMarginInBodyOffset = 1 !== s.offsetTop, e.getComputedStyle && (t.pixelPosition = "1%" !== (e.getComputedStyle(r, null) || {}).top, t.boxSizingReliable = "4px" === (e.getComputedStyle(r, null) || { width: "4px" }).width, o = P.createElement("div"), o.style.cssText = r.style.cssText = a, o.style.marginRight = o.style.width = "0", r.style.width = "1px", r.appendChild(o), t.reliableMarginRight = !parseFloat((e.getComputedStyle(o, null) || {}).marginRight)), void 0 !== r.style.zoom && (r.innerHTML = "", r.style.cssText = a + "width:1px;padding:1px;display:inline;zoom:1", t.inlineBlockNeedsLayout = 3 === r.offsetWidth, r.style.display = "block", r.style.overflow = "visible", r.innerHTML = "<div></div>", r.firstChild.style.width = "5px", t.shrinkWrapBlocks = 3 !== r.offsetWidth, n.style.zoom = 1), s.removeChild(n), n = r = i = o = null) }), s.removeChild(p), n = r = i = o = a = s = p = null, t }(); var he = /(?:\{[\s\S]*\}|\[[\s\S]*\])$/,
        ge = /([A-Z])/g;
    Q.extend({ cache: {}, deletedIds: [], uuid: 0, expando: "jQuery" + (Q.fn.jquery + Math.random()).replace(/\D/g, ""), noData: { embed: !0, object: "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000", applet: !0 }, hasData: function(e) { return !!(e = e.nodeType ? Q.cache[e[Q.expando]] : e[Q.expando]) && !i(e) }, data: function(e, n, r, i) { if (Q.acceptData(e)) { var o, a, s = Q.expando,
                    l = "string" == typeof n,
                    u = e.nodeType,
                    c = u ? Q.cache : e,
                    f = u ? e[s] : e[s] && s; if (f && c[f] && (i || c[f].data) || !l || r !== t) return f || (u ? e[s] = f = Q.deletedIds.pop() || Q.guid++ : f = s), c[f] || (c[f] = {}, u || (c[f].toJSON = Q.noop)), "object" != typeof n && "function" != typeof n || (i ? c[f] = Q.extend(c[f], n) : c[f].data = Q.extend(c[f].data, n)), o = c[f], i || (o.data || (o.data = {}), o = o.data), r !== t && (o[Q.camelCase(n)] = r), l ? null == (a = o[n]) && (a = o[Q.camelCase(n)]) : a = o, a } }, removeData: function(e, t, n) { if (Q.acceptData(e)) { var r, o, a, s = e.nodeType,
                    l = s ? Q.cache : e,
                    u = s ? e[Q.expando] : Q.expando; if (l[u]) { if (t && (r = n ? l[u] : l[u].data)) { Q.isArray(t) || (t in r ? t = [t] : (t = Q.camelCase(t), t = t in r ? [t] : t.split(" "))); for (o = 0, a = t.length; o < a; o++) delete r[t[o]]; if (!(n ? i : Q.isEmptyObject)(r)) return }(n || (delete l[u].data, i(l[u]))) && (s ? Q.cleanData([e], !0) : Q.support.deleteExpando || l != l.window ? delete l[u] : l[u] = null) } } }, _data: function(e, t, n) { return Q.data(e, t, n, !0) }, acceptData: function(e) { var t = e.nodeName && Q.noData[e.nodeName.toLowerCase()]; return !t || !0 !== t && e.getAttribute("classid") === t } }), Q.fn.extend({ data: function(e, n) { var i, o, a, s, l, u = this[0],
                c = 0,
                f = null; if (e === t) { if (this.length && (f = Q.data(u), 1 === u.nodeType && !Q._data(u, "parsedAttrs"))) { for (l = (a = u.attributes).length; c < l; c++)(s = a[c].name).indexOf("data-") || (s = Q.camelCase(s.substring(5)), r(u, s, f[s]));
                    Q._data(u, "parsedAttrs", !0) } return f } return "object" == typeof e ? this.each(function() { Q.data(this, e) }) : (i = e.split(".", 2), i[1] = i[1] ? "." + i[1] : "", o = i[1] + "!", Q.access(this, function(n) { if (n === t) return (f = this.triggerHandler("getData" + o, [i[0]])) === t && u && (f = Q.data(u, e), f = r(u, e, f)), f === t && i[1] ? this.data(i[0]) : f;
                i[1] = n, this.each(function() { var t = Q(this);
                    t.triggerHandler("setData" + o, i), Q.data(this, e, n), t.triggerHandler("changeData" + o, i) }) }, null, n, arguments.length > 1, null, !1)) }, removeData: function(e) { return this.each(function() { Q.removeData(this, e) }) } }), Q.extend({ queue: function(e, t, n) { var r; if (e) return t = (t || "fx") + "queue", r = Q._data(e, t), n && (!r || Q.isArray(n) ? r = Q._data(e, t, Q.makeArray(n)) : r.push(n)), r || [] }, dequeue: function(e, t) { t = t || "fx"; var n = Q.queue(e, t),
                r = n.length,
                i = n.shift(),
                o = Q._queueHooks(e, t); "inprogress" === i && (i = n.shift(), r--), i && ("fx" === t && n.unshift("inprogress"), delete o.stop, i.call(e, function() { Q.dequeue(e, t) }, o)), !r && o && o.empty.fire() }, _queueHooks: function(e, t) { var n = t + "queueHooks"; return Q._data(e, n) || Q._data(e, n, { empty: Q.Callbacks("once memory").add(function() { Q.removeData(e, t + "queue", !0), Q.removeData(e, n, !0) }) }) } }), Q.fn.extend({ queue: function(e, n) { var r = 2; return "string" != typeof e && (n = e, e = "fx", r--), arguments.length < r ? Q.queue(this[0], e) : n === t ? this : this.each(function() { var t = Q.queue(this, e, n);
                Q._queueHooks(this, e), "fx" === e && "inprogress" !== t[0] && Q.dequeue(this, e) }) }, dequeue: function(e) { return this.each(function() { Q.dequeue(this, e) }) }, delay: function(e, t) { return e = Q.fx ? Q.fx.speeds[e] || e : e, t = t || "fx", this.queue(t, function(t, n) { var r = setTimeout(t, e);
                n.stop = function() { clearTimeout(r) } }) }, clearQueue: function(e) { return this.queue(e || "fx", []) }, promise: function(e, n) { var r, i = 1,
                o = Q.Deferred(),
                a = this,
                s = this.length,
                l = function() {--i || o.resolveWith(a, [a]) }; for ("string" != typeof e && (n = e, e = t), e = e || "fx"; s--;)(r = Q._data(a[s], e + "queueHooks")) && r.empty && (i++, r.empty.add(l)); return l(), o.promise(n) } }); var me, ye, ve, be = /[\t\r\n]/g,
        xe = /\r/g,
        we = /^(?:button|input)$/i,
        Te = /^(?:button|input|object|select|textarea)$/i,
        Ne = /^a(?:rea|)$/i,
        Ce = /^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,
        ke = Q.support.getSetAttribute;
    Q.fn.extend({ attr: function(e, t) { return Q.access(this, Q.attr, e, t, arguments.length > 1) }, removeAttr: function(e) { return this.each(function() { Q.removeAttr(this, e) }) }, prop: function(e, t) { return Q.access(this, Q.prop, e, t, arguments.length > 1) }, removeProp: function(e) { return e = Q.propFix[e] || e, this.each(function() { try { this[e] = t, delete this[e] } catch (e) {} }) }, addClass: function(e) { var t, n, r, i, o, a, s; if (Q.isFunction(e)) return this.each(function(t) { Q(this).addClass(e.call(this, t, this.className)) }); if (e && "string" == typeof e)
                for (t = e.split(ee), n = 0, r = this.length; n < r; n++)
                    if (1 === (i = this[n]).nodeType)
                        if (i.className || 1 !== t.length) { for (o = " " + i.className + " ", a = 0, s = t.length; a < s; a++) o.indexOf(" " + t[a] + " ") < 0 && (o += t[a] + " ");
                            i.className = Q.trim(o) } else i.className = e; return this }, removeClass: function(e) { var n, r, i, o, a, s, l; if (Q.isFunction(e)) return this.each(function(t) { Q(this).removeClass(e.call(this, t, this.className)) }); if (e && "string" == typeof e || e === t)
                for (n = (e || "").split(ee), s = 0, l = this.length; s < l; s++)
                    if (1 === (i = this[s]).nodeType && i.className) { for (r = (" " + i.className + " ").replace(be, " "), o = 0, a = n.length; o < a; o++)
                            for (; r.indexOf(" " + n[o] + " ") >= 0;) r = r.replace(" " + n[o] + " ", " ");
                        i.className = e ? Q.trim(r) : "" }
            return this }, toggleClass: function(e, t) { var n = typeof e,
                r = "boolean" == typeof t; return Q.isFunction(e) ? this.each(function(n) { Q(this).toggleClass(e.call(this, n, this.className, t), t) }) : this.each(function() { if ("string" === n)
                    for (var i, o = 0, a = Q(this), s = t, l = e.split(ee); i = l[o++];) s = r ? s : !a.hasClass(i), a[s ? "addClass" : "removeClass"](i);
                else "undefined" !== n && "boolean" !== n || (this.className && Q._data(this, "__className__", this.className), this.className = this.className || !1 === e ? "" : Q._data(this, "__className__") || "") }) }, hasClass: function(e) { for (var t = " " + e + " ", n = 0, r = this.length; n < r; n++)
                if (1 === this[n].nodeType && (" " + this[n].className + " ").replace(be, " ").indexOf(t) >= 0) return !0; return !1 }, val: function(e) { var n, r, i, o = this[0]; { if (arguments.length) return i = Q.isFunction(e), this.each(function(r) { var o, a = Q(this);
                    1 === this.nodeType && (null == (o = i ? e.call(this, r, a.val()) : e) ? o = "" : "number" == typeof o ? o += "" : Q.isArray(o) && (o = Q.map(o, function(e) { return null == e ? "" : e + "" })), (n = Q.valHooks[this.type] || Q.valHooks[this.nodeName.toLowerCase()]) && "set" in n && n.set(this, o, "value") !== t || (this.value = o)) }); if (o) return (n = Q.valHooks[o.type] || Q.valHooks[o.nodeName.toLowerCase()]) && "get" in n && (r = n.get(o, "value")) !== t ? r : "string" == typeof(r = o.value) ? r.replace(xe, "") : null == r ? "" : r } } }), Q.extend({ valHooks: { option: { get: function(e) { var t = e.attributes.value; return !t || t.specified ? e.value : e.text } }, select: { get: function(e) { for (var t, n, r = e.options, i = e.selectedIndex, o = "select-one" === e.type || i < 0, a = o ? null : [], s = o ? i + 1 : r.length, l = i < 0 ? s : o ? i : 0; l < s; l++)
                        if (((n = r[l]).selected || l === i) && (Q.support.optDisabled ? !n.disabled : null === n.getAttribute("disabled")) && (!n.parentNode.disabled || !Q.nodeName(n.parentNode, "optgroup"))) { if (t = Q(n).val(), o) return t;
                            a.push(t) }
                    return a }, set: function(e, t) { var n = Q.makeArray(t); return Q(e).find("option").each(function() { this.selected = Q.inArray(Q(this).val(), n) >= 0 }), n.length || (e.selectedIndex = -1), n } } }, attrFn: {}, attr: function(e, n, r, i) { var o, a, s, l = e.nodeType; if (e && 3 !== l && 8 !== l && 2 !== l) return i && Q.isFunction(Q.fn[n]) ? Q(e)[n](r) : void 0 === e.getAttribute ? Q.prop(e, n, r) : ((s = 1 !== l || !Q.isXMLDoc(e)) && (n = n.toLowerCase(), a = Q.attrHooks[n] || (Ce.test(n) ? ye : me)), r !== t ? null === r ? void Q.removeAttr(e, n) : a && "set" in a && s && (o = a.set(e, r, n)) !== t ? o : (e.setAttribute(n, r + ""), r) : a && "get" in a && s && null !== (o = a.get(e, n)) ? o : null === (o = e.getAttribute(n)) ? t : o) }, removeAttr: function(e, t) { var n, r, i, o, a = 0; if (t && 1 === e.nodeType)
                for (r = t.split(ee); a < r.length; a++)(i = r[a]) && (n = Q.propFix[i] || i, (o = Ce.test(i)) || Q.attr(e, i, ""), e.removeAttribute(ke ? i : n), o && n in e && (e[n] = !1)) }, attrHooks: { type: { set: function(e, t) { if (we.test(e.nodeName) && e.parentNode) Q.error("type property can't be changed");
                    else if (!Q.support.radioValue && "radio" === t && Q.nodeName(e, "input")) { var n = e.value; return e.setAttribute("type", t), n && (e.value = n), t } } }, value: { get: function(e, t) { return me && Q.nodeName(e, "button") ? me.get(e, t) : t in e ? e.value : null }, set: function(e, t, n) { if (me && Q.nodeName(e, "button")) return me.set(e, t, n);
                    e.value = t } } }, propFix: { tabindex: "tabIndex", readonly: "readOnly", for: "htmlFor", class: "className", maxlength: "maxLength", cellspacing: "cellSpacing", cellpadding: "cellPadding", rowspan: "rowSpan", colspan: "colSpan", usemap: "useMap", frameborder: "frameBorder", contenteditable: "contentEditable" }, prop: function(e, n, r) { var i, o, a = e.nodeType; if (e && 3 !== a && 8 !== a && 2 !== a) return (1 !== a || !Q.isXMLDoc(e)) && (n = Q.propFix[n] || n, o = Q.propHooks[n]), r !== t ? o && "set" in o && (i = o.set(e, r, n)) !== t ? i : e[n] = r : o && "get" in o && null !== (i = o.get(e, n)) ? i : e[n] }, propHooks: { tabIndex: { get: function(e) { var n = e.getAttributeNode("tabindex"); return n && n.specified ? parseInt(n.value, 10) : Te.test(e.nodeName) || Ne.test(e.nodeName) && e.href ? 0 : t } } } }), ye = { get: function(e, n) { var r, i = Q.prop(e, n); return !0 === i || "boolean" != typeof i && (r = e.getAttributeNode(n)) && !1 !== r.nodeValue ? n.toLowerCase() : t }, set: function(e, t, n) { var r; return !1 === t ? Q.removeAttr(e, n) : ((r = Q.propFix[n] || n) in e && (e[r] = !0), e.setAttribute(n, n.toLowerCase())), n } }, ke || (ve = { name: !0, id: !0, coords: !0 }, me = Q.valHooks.button = { get: function(e, n) { var r; return (r = e.getAttributeNode(n)) && (ve[n] ? "" !== r.value : r.specified) ? r.value : t }, set: function(e, t, n) { var r = e.getAttributeNode(n); return r || (r = P.createAttribute(n), e.setAttributeNode(r)), r.value = t + "" } }, Q.each(["width", "height"], function(e, t) { Q.attrHooks[t] = Q.extend(Q.attrHooks[t], { set: function(e, n) { if ("" === n) return e.setAttribute(t, "auto"), n } }) }), Q.attrHooks.contenteditable = { get: me.get, set: function(e, t, n) { "" === t && (t = "false"), me.set(e, t, n) } }), Q.support.hrefNormalized || Q.each(["href", "src", "width", "height"], function(e, n) { Q.attrHooks[n] = Q.extend(Q.attrHooks[n], { get: function(e) { var r = e.getAttribute(n, 2); return null === r ? t : r } }) }), Q.support.style || (Q.attrHooks.style = { get: function(e) { return e.style.cssText.toLowerCase() || t }, set: function(e, t) { return e.style.cssText = t + "" } }), Q.support.optSelected || (Q.propHooks.selected = Q.extend(Q.propHooks.selected, { get: function(e) { var t = e.parentNode; return t && (t.selectedIndex, t.parentNode && t.parentNode.selectedIndex), null } })), Q.support.enctype || (Q.propFix.enctype = "encoding"), Q.support.checkOn || Q.each(["radio", "checkbox"], function() { Q.valHooks[this] = { get: function(e) { return null === e.getAttribute("value") ? "on" : e.value } } }), Q.each(["radio", "checkbox"], function() { Q.valHooks[this] = Q.extend(Q.valHooks[this], { set: function(e, t) { if (Q.isArray(t)) return e.checked = Q.inArray(Q(e).val(), t) >= 0 } }) }); var Ee = /^(?:textarea|input|select)$/i,
        Se = /^([^\.]*|)(?:\.(.+)|)$/,
        Ae = /(?:^|\s)hover(\.\S+|)\b/,
        je = /^key/,
        De = /^(?:mouse|contextmenu)|click/,
        Le = /^(?:focusinfocus|focusoutblur)$/,
        He = function(e) { return Q.event.special.hover ? e : e.replace(Ae, "mouseenter$1 mouseleave$1") };
    Q.event = { add: function(e, n, r, i, o) { var a, s, l, u, c, f, p, d, h, g, m; if (3 !== e.nodeType && 8 !== e.nodeType && n && r && (a = Q._data(e))) { for (r.handler && (h = r, r = h.handler, o = h.selector), r.guid || (r.guid = Q.guid++), (l = a.events) || (a.events = l = {}), (s = a.handle) || (a.handle = s = function(e) { return void 0 === Q || e && Q.event.triggered === e.type ? t : Q.event.dispatch.apply(s.elem, arguments) }, s.elem = e), n = Q.trim(He(n)).split(" "), u = 0; u < n.length; u++) f = (c = Se.exec(n[u]) || [])[1], p = (c[2] || "").split(".").sort(), m = Q.event.special[f] || {}, f = (o ? m.delegateType : m.bindType) || f, m = Q.event.special[f] || {}, d = Q.extend({ type: f, origType: c[1], data: i, handler: r, guid: r.guid, selector: o, needsContext: o && Q.expr.match.needsContext.test(o), namespace: p.join(".") }, h), (g = l[f]) || ((g = l[f] = []).delegateCount = 0, m.setup && !1 !== m.setup.call(e, i, p, s) || (e.addEventListener ? e.addEventListener(f, s, !1) : e.attachEvent && e.attachEvent("on" + f, s))), m.add && (m.add.call(e, d), d.handler.guid || (d.handler.guid = r.guid)), o ? g.splice(g.delegateCount++, 0, d) : g.push(d), Q.event.global[f] = !0;
                    e = null } }, global: {}, remove: function(e, t, n, r, i) { var o, a, s, l, u, c, f, p, d, h, g, m = Q.hasData(e) && Q._data(e); if (m && (p = m.events)) { for (t = Q.trim(He(t || "")).split(" "), o = 0; o < t.length; o++)
                        if (a = Se.exec(t[o]) || [], s = l = a[1], u = a[2], s) { for (d = Q.event.special[s] || {}, c = (h = p[s = (r ? d.delegateType : d.bindType) || s] || []).length, u = u ? new RegExp("(^|\\.)" + u.split(".").sort().join("\\.(?:.*\\.|)") + "(\\.|$)") : null, f = 0; f < h.length; f++) g = h[f], (i || l === g.origType) && (!n || n.guid === g.guid) && (!u || u.test(g.namespace)) && (!r || r === g.selector || "**" === r && g.selector) && (h.splice(f--, 1), g.selector && h.delegateCount--, d.remove && d.remove.call(e, g));
                            0 === h.length && c !== h.length && ((!d.teardown || !1 === d.teardown.call(e, u, m.handle)) && Q.removeEvent(e, s, m.handle), delete p[s]) } else
                            for (s in p) Q.event.remove(e, s + t[o], n, r, !0);
                    Q.isEmptyObject(p) && (delete m.handle, Q.removeData(e, "events", !0)) } }, customEvent: { getData: !0, setData: !0, changeData: !0 }, trigger: function(n, r, i, o) { if (!i || 3 !== i.nodeType && 8 !== i.nodeType) { var a, s, l, u, c, f, p, d, h, g, m = n.type || n,
                        y = []; if (Le.test(m + Q.event.triggered)) return; if (m.indexOf("!") >= 0 && (m = m.slice(0, -1), s = !0), m.indexOf(".") >= 0 && (y = m.split("."), m = y.shift(), y.sort()), (!i || Q.event.customEvent[m]) && !Q.event.global[m]) return; if (n = "object" == typeof n ? n[Q.expando] ? n : new Q.Event(m, n) : new Q.Event(m), n.type = m, n.isTrigger = !0, n.exclusive = s, n.namespace = y.join("."), n.namespace_re = n.namespace ? new RegExp("(^|\\.)" + y.join("\\.(?:.*\\.|)") + "(\\.|$)") : null, f = m.indexOf(":") < 0 ? "on" + m : "", !i) { a = Q.cache; for (l in a) a[l].events && a[l].events[m] && Q.event.trigger(n, r, a[l].handle.elem, !0); return } if (n.result = t, n.target || (n.target = i), (r = null != r ? Q.makeArray(r) : []).unshift(n), (p = Q.event.special[m] || {}).trigger && !1 === p.trigger.apply(i, r)) return; if (h = [
                            [i, p.bindType || m]
                        ], !o && !p.noBubble && !Q.isWindow(i)) { for (g = p.delegateType || m, u = Le.test(g + m) ? i : i.parentNode, c = i; u; u = u.parentNode) h.push([u, g]), c = u;
                        c === (i.ownerDocument || P) && h.push([c.defaultView || c.parentWindow || e, g]) } for (l = 0; l < h.length && !n.isPropagationStopped(); l++) u = h[l][0], n.type = h[l][1], (d = (Q._data(u, "events") || {})[n.type] && Q._data(u, "handle")) && d.apply(u, r), (d = f && u[f]) && Q.acceptData(u) && d.apply && !1 === d.apply(u, r) && n.preventDefault(); return n.type = m, !o && !n.isDefaultPrevented() && (!p._default || !1 === p._default.apply(i.ownerDocument, r)) && ("click" !== m || !Q.nodeName(i, "a")) && Q.acceptData(i) && f && i[m] && ("focus" !== m && "blur" !== m || 0 !== n.target.offsetWidth) && !Q.isWindow(i) && ((c = i[f]) && (i[f] = null), Q.event.triggered = m, i[m](), Q.event.triggered = t, c && (i[f] = c)), n.result } }, dispatch: function(n) { n = Q.event.fix(n || e.event); var r, i, o, a, s, l, u, c, f, p = (Q._data(this, "events") || {})[n.type] || [],
                    d = p.delegateCount,
                    h = U.call(arguments),
                    g = !n.exclusive && !n.namespace,
                    m = Q.event.special[n.type] || {},
                    y = []; if (h[0] = n, n.delegateTarget = this, !m.preDispatch || !1 !== m.preDispatch.call(this, n)) { if (d && (!n.button || "click" !== n.type))
                        for (o = n.target; o != this; o = o.parentNode || this)
                            if (!0 !== o.disabled || "click" !== n.type) { for (s = {}, u = [], r = 0; r < d; r++) c = p[r], f = c.selector, s[f] === t && (s[f] = c.needsContext ? Q(f, this).index(o) >= 0 : Q.find(f, this, null, [o]).length), s[f] && u.push(c);
                                u.length && y.push({ elem: o, matches: u }) }
                    for (p.length > d && y.push({ elem: this, matches: p.slice(d) }), r = 0; r < y.length && !n.isPropagationStopped(); r++)
                        for (l = y[r], n.currentTarget = l.elem, i = 0; i < l.matches.length && !n.isImmediatePropagationStopped(); i++) c = l.matches[i], (g || !n.namespace && !c.namespace || n.namespace_re && n.namespace_re.test(c.namespace)) && (n.data = c.data, n.handleObj = c, (a = ((Q.event.special[c.origType] || {}).handle || c.handler).apply(l.elem, h)) !== t && (n.result = a, !1 === a && (n.preventDefault(), n.stopPropagation()))); return m.postDispatch && m.postDispatch.call(this, n), n.result } }, props: "attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "), fixHooks: {}, keyHooks: { props: "char charCode key keyCode".split(" "), filter: function(e, t) { return null == e.which && (e.which = null != t.charCode ? t.charCode : t.keyCode), e } }, mouseHooks: { props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "), filter: function(e, n) { var r, i, o, a = n.button,
                        s = n.fromElement; return null == e.pageX && null != n.clientX && (r = e.target.ownerDocument || P, i = r.documentElement, o = r.body, e.pageX = n.clientX + (i && i.scrollLeft || o && o.scrollLeft || 0) - (i && i.clientLeft || o && o.clientLeft || 0), e.pageY = n.clientY + (i && i.scrollTop || o && o.scrollTop || 0) - (i && i.clientTop || o && o.clientTop || 0)), !e.relatedTarget && s && (e.relatedTarget = s === e.target ? n.toElement : s), !e.which && a !== t && (e.which = 1 & a ? 1 : 2 & a ? 3 : 4 & a ? 2 : 0), e } }, fix: function(e) { if (e[Q.expando]) return e; var t, n, r = e,
                    i = Q.event.fixHooks[e.type] || {},
                    o = i.props ? this.props.concat(i.props) : this.props; for (e = Q.Event(r), t = o.length; t;) n = o[--t], e[n] = r[n]; return e.target || (e.target = r.srcElement || P), 3 === e.target.nodeType && (e.target = e.target.parentNode), e.metaKey = !!e.metaKey, i.filter ? i.filter(e, r) : e }, special: { load: { noBubble: !0 }, focus: { delegateType: "focusin" }, blur: { delegateType: "focusout" }, beforeunload: { setup: function(e, t, n) { Q.isWindow(this) && (this.onbeforeunload = n) }, teardown: function(e, t) { this.onbeforeunload === t && (this.onbeforeunload = null) } } }, simulate: function(e, t, n, r) { var i = Q.extend(new Q.Event, n, { type: e, isSimulated: !0, originalEvent: {} });
                r ? Q.event.trigger(i, null, t) : Q.event.dispatch.call(t, i), i.isDefaultPrevented() && n.preventDefault() } }, Q.event.handle = Q.event.dispatch, Q.removeEvent = P.removeEventListener ? function(e, t, n) { e.removeEventListener && e.removeEventListener(t, n, !1) } : function(e, t, n) { var r = "on" + t;
            e.detachEvent && (void 0 === e[r] && (e[r] = null), e.detachEvent(r, n)) }, Q.Event = function(e, t) { if (!(this instanceof Q.Event)) return new Q.Event(e, t);
            e && e.type ? (this.originalEvent = e, this.type = e.type, this.isDefaultPrevented = e.defaultPrevented || !1 === e.returnValue || e.getPreventDefault && e.getPreventDefault() ? a : o) : this.type = e, t && Q.extend(this, t), this.timeStamp = e && e.timeStamp || Q.now(), this[Q.expando] = !0 }, Q.Event.prototype = { preventDefault: function() { this.isDefaultPrevented = a; var e = this.originalEvent;
                e && (e.preventDefault ? e.preventDefault() : e.returnValue = !1) }, stopPropagation: function() { this.isPropagationStopped = a; var e = this.originalEvent;
                e && (e.stopPropagation && e.stopPropagation(), e.cancelBubble = !0) }, stopImmediatePropagation: function() { this.isImmediatePropagationStopped = a, this.stopPropagation() }, isDefaultPrevented: o, isPropagationStopped: o, isImmediatePropagationStopped: o }, Q.each({ mouseenter: "mouseover", mouseleave: "mouseout" }, function(e, t) { Q.event.special[e] = { delegateType: t, bindType: t, handle: function(e) { var n, r = this,
                        i = e.relatedTarget,
                        o = e.handleObj;
                    o.selector; return i && (i === r || Q.contains(r, i)) || (e.type = o.origType, n = o.handler.apply(this, arguments), e.type = t), n } } }), Q.support.submitBubbles || (Q.event.special.submit = { setup: function() { if (Q.nodeName(this, "form")) return !1;
                Q.event.add(this, "click._submit keypress._submit", function(e) { var n = e.target,
                        r = Q.nodeName(n, "input") || Q.nodeName(n, "button") ? n.form : t;
                    r && !Q._data(r, "_submit_attached") && (Q.event.add(r, "submit._submit", function(e) { e._submit_bubble = !0 }), Q._data(r, "_submit_attached", !0)) }) }, postDispatch: function(e) { e._submit_bubble && (delete e._submit_bubble, this.parentNode && !e.isTrigger && Q.event.simulate("submit", this.parentNode, e, !0)) }, teardown: function() { if (Q.nodeName(this, "form")) return !1;
                Q.event.remove(this, "._submit") } }), Q.support.changeBubbles || (Q.event.special.change = { setup: function() { if (Ee.test(this.nodeName)) return "checkbox" !== this.type && "radio" !== this.type || (Q.event.add(this, "propertychange._change", function(e) { "checked" === e.originalEvent.propertyName && (this._just_changed = !0) }), Q.event.add(this, "click._change", function(e) { this._just_changed && !e.isTrigger && (this._just_changed = !1), Q.event.simulate("change", this, e, !0) })), !1;
                Q.event.add(this, "beforeactivate._change", function(e) { var t = e.target;
                    Ee.test(t.nodeName) && !Q._data(t, "_change_attached") && (Q.event.add(t, "change._change", function(e) { this.parentNode && !e.isSimulated && !e.isTrigger && Q.event.simulate("change", this.parentNode, e, !0) }), Q._data(t, "_change_attached", !0)) }) }, handle: function(e) { var t = e.target; if (this !== t || e.isSimulated || e.isTrigger || "radio" !== t.type && "checkbox" !== t.type) return e.handleObj.handler.apply(this, arguments) }, teardown: function() { return Q.event.remove(this, "._change"), !Ee.test(this.nodeName) } }), Q.support.focusinBubbles || Q.each({ focus: "focusin", blur: "focusout" }, function(e, t) { var n = 0,
                r = function(e) { Q.event.simulate(t, e.target, Q.event.fix(e), !0) };
            Q.event.special[t] = { setup: function() { 0 == n++ && P.addEventListener(e, r, !0) }, teardown: function() { 0 == --n && P.removeEventListener(e, r, !0) } } }), Q.fn.extend({ on: function(e, n, r, i, a) { var s, l; if ("object" == typeof e) { "string" != typeof n && (r = r || n, n = t); for (l in e) this.on(l, n, r, e[l], a); return this } if (null == r && null == i ? (i = n, r = n = t) : null == i && ("string" == typeof n ? (i = r, r = t) : (i = r, r = n, n = t)), !1 === i) i = o;
                else if (!i) return this; return 1 === a && (s = i, i = function(e) { return Q().off(e), s.apply(this, arguments) }, i.guid = s.guid || (s.guid = Q.guid++)), this.each(function() { Q.event.add(this, e, i, r, n) }) }, one: function(e, t, n, r) { return this.on(e, t, n, r, 1) }, off: function(e, n, r) { var i, a; if (e && e.preventDefault && e.handleObj) return i = e.handleObj, Q(e.delegateTarget).off(i.namespace ? i.origType + "." + i.namespace : i.origType, i.selector, i.handler), this; if ("object" == typeof e) { for (a in e) this.off(a, n, e[a]); return this } return !1 !== n && "function" != typeof n || (r = n, n = t), !1 === r && (r = o), this.each(function() { Q.event.remove(this, e, r, n) }) }, bind: function(e, t, n) { return this.on(e, null, t, n) }, unbind: function(e, t) { return this.off(e, null, t) }, live: function(e, t, n) { return Q(this.context).on(e, this.selector, t, n), this }, die: function(e, t) { return Q(this.context).off(e, this.selector || "**", t), this }, delegate: function(e, t, n, r) { return this.on(t, e, n, r) }, undelegate: function(e, t, n) { return 1 === arguments.length ? this.off(e, "**") : this.off(t, e || "**", n) }, trigger: function(e, t) { return this.each(function() { Q.event.trigger(e, t, this) }) }, triggerHandler: function(e, t) { if (this[0]) return Q.event.trigger(e, t, this[0], !0) }, toggle: function(e) { var t = arguments,
                    n = e.guid || Q.guid++,
                    r = 0,
                    i = function(n) { var i = (Q._data(this, "lastToggle" + e.guid) || 0) % r; return Q._data(this, "lastToggle" + e.guid, i + 1), n.preventDefault(), t[i].apply(this, arguments) || !1 }; for (i.guid = n; r < t.length;) t[r++].guid = n; return this.click(i) }, hover: function(e, t) { return this.mouseenter(e).mouseleave(t || e) } }), Q.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "), function(e, t) { Q.fn[t] = function(e, n) { return null == n && (n = e, e = null), arguments.length > 0 ? this.on(t, null, e, n) : this.trigger(t) }, je.test(t) && (Q.event.fixHooks[t] = Q.event.keyHooks), De.test(t) && (Q.event.fixHooks[t] = Q.event.mouseHooks) }),
        function(e, t) {
            function n(e, t, n, r) { n = n || []; var i, o, a, s, l = (t = t || L).nodeType; if (!e || "string" != typeof e) return n; if (1 !== l && 9 !== l) return []; if (!(a = w(t)) && !r && (i = te.exec(e)))
                    if (s = i[1]) { if (9 === l) { if (!(o = t.getElementById(s)) || !o.parentNode) return n; if (o.id === s) return n.push(o), n } else if (t.ownerDocument && (o = t.ownerDocument.getElementById(s)) && T(t, o) && o.id === s) return n.push(o), n } else { if (i[2]) return _.apply(n, q.call(t.getElementsByTagName(e), 0)), n; if ((s = i[3]) && fe && t.getElementsByClassName) return _.apply(n, q.call(t.getElementsByClassName(s), 0)), n }
                return g(e.replace(G, "$1"), t, n, r, a) }

            function r(e) { return function(t) { return "input" === t.nodeName.toLowerCase() && t.type === e } }

            function i(e) { return function(t) { var n = t.nodeName.toLowerCase(); return ("input" === n || "button" === n) && t.type === e } }

            function o(e) { return W(function(t) { return t = +t, W(function(n, r) { for (var i, o = e([], n.length, t), a = o.length; a--;) n[i = o[a]] && (n[i] = !(r[i] = n[i])) }) }) }

            function a(e, t, n) { if (e === t) return n; for (var r = e.nextSibling; r;) { if (r === t) return -1;
                    r = r.nextSibling } return 1 }

            function s(e, t) { var r, i, o, a, s, l, u, c = $[j][e + " "]; if (c) return t ? 0 : c.slice(0); for (s = e, l = [], u = b.preFilter; s;) { r && !(i = K.exec(s)) || (i && (s = s.slice(i[0].length) || s), l.push(o = [])), r = !1, (i = Z.exec(s)) && (o.push(r = new D(i.shift())), s = s.slice(r.length), r.type = i[0].replace(G, " ")); for (a in b.filter)(i = ae[a].exec(s)) && (!u[a] || (i = u[a](i))) && (o.push(r = new D(i.shift())), s = s.slice(r.length), r.type = a, r.matches = i); if (!r) break } return t ? s.length : s ? n.error(e) : $(e, l).slice(0) }

            function l(e, t, n) { var r = t.dir,
                    i = n && "parentNode" === t.dir,
                    o = M++; return t.first ? function(t, n, o) { for (; t = t[r];)
                        if (i || 1 === t.nodeType) return e(t, n, o) } : function(t, n, a) { if (a) { for (; t = t[r];)
                            if ((i || 1 === t.nodeType) && e(t, n, a)) return t } else
                        for (var s, l = F + " " + o + " ", u = l + y; t = t[r];)
                            if (i || 1 === t.nodeType) { if ((s = t[j]) === u) return t.sizset; if ("string" == typeof s && 0 === s.indexOf(l)) { if (t.sizset) return t } else { if (t[j] = u, e(t, n, a)) return t.sizset = !0, t;
                                    t.sizset = !1 } } } }

            function u(e) { return e.length > 1 ? function(t, n, r) { for (var i = e.length; i--;)
                        if (!e[i](t, n, r)) return !1; return !0 } : e[0] }

            function c(e, t, n, r, i) { for (var o, a = [], s = 0, l = e.length, u = null != t; s < l; s++)(o = e[s]) && (n && !n(o, r, i) || (a.push(o), u && t.push(s))); return a }

            function f(e, t, n, r, i, o) { return r && !r[j] && (r = f(r)), i && !i[j] && (i = f(i, o)), W(function(o, a, s, l) { var u, f, p, d = [],
                        g = [],
                        m = a.length,
                        y = o || h(t || "*", s.nodeType ? [s] : s, []),
                        v = !e || !o && t ? y : c(y, d, e, s, l),
                        b = n ? i || (o ? e : m || r) ? [] : a : v; if (n && n(v, b, s, l), r)
                        for (u = c(b, g), r(u, [], s, l), f = u.length; f--;)(p = u[f]) && (b[g[f]] = !(v[g[f]] = p)); if (o) { if (i || e) { if (i) { for (u = [], f = b.length; f--;)(p = b[f]) && u.push(v[f] = p);
                                i(null, b = [], u, l) } for (f = b.length; f--;)(p = b[f]) && (u = i ? B.call(o, p) : d[f]) > -1 && (o[u] = !(a[u] = p)) } } else b = c(b === a ? b.splice(m, b.length) : b), i ? i(null, a, b, l) : _.apply(a, b) }) }

            function p(e) { for (var t, n, r, i = e.length, o = b.relative[e[0].type], a = o || b.relative[" "], s = o ? 1 : 0, c = l(function(e) { return e === t }, a, !0), d = l(function(e) { return B.call(t, e) > -1 }, a, !0), h = [function(e, n, r) { return !o && (r || n !== E) || ((t = n).nodeType ? c(e, n, r) : d(e, n, r)) }]; s < i; s++)
                    if (n = b.relative[e[s].type]) h = [l(u(h), n)];
                    else { if ((n = b.filter[e[s].type].apply(null, e[s].matches))[j]) { for (r = ++s; r < i && !b.relative[e[r].type]; r++); return f(s > 1 && u(h), s > 1 && e.slice(0, s - 1).join("").replace(G, "$1"), n, s < r && p(e.slice(s, r)), r < i && p(e = e.slice(r)), r < i && e.join("")) } h.push(n) }
                return u(h) }

            function d(e, t) { var r = t.length > 0,
                    i = e.length > 0,
                    o = function(a, s, l, u, f) { var p, d, h, g = [],
                            m = 0,
                            v = "0",
                            x = a && [],
                            w = null != f,
                            T = E,
                            N = a || i && b.find.TAG("*", f && s.parentNode || s),
                            C = F += null == T ? 1 : Math.E; for (w && (E = s !== L && s, y = o.el); null != (p = N[v]); v++) { if (i && p) { for (d = 0; h = e[d]; d++)
                                    if (h(p, s, l)) { u.push(p); break }
                                w && (F = C, y = ++o.el) } r && ((p = !h && p) && m--, a && x.push(p)) } if (m += v, r && v !== m) { for (d = 0; h = t[d]; d++) h(x, g, s, l); if (a) { if (m > 0)
                                    for (; v--;) !x[v] && !g[v] && (g[v] = O.call(u));
                                g = c(g) } _.apply(u, g), w && !a && g.length > 0 && m + t.length > 1 && n.uniqueSort(u) } return w && (F = C, E = T), x }; return o.el = 0, r ? W(o) : o }

            function h(e, t, r) { for (var i = 0, o = t.length; i < o; i++) n(e, t[i], r); return r }

            function g(e, t, n, r, i) { var o, a, l, u, c, f = s(e);
                f.length; if (!r && 1 === f.length) { if ((a = f[0] = f[0].slice(0)).length > 2 && "ID" === (l = a[0]).type && 9 === t.nodeType && !i && b.relative[a[1].type]) { if (!(t = b.find.ID(l.matches[0].replace(oe, ""), t, i)[0])) return n;
                        e = e.slice(a.shift().length) } for (o = ae.POS.test(e) ? -1 : a.length - 1; o >= 0 && (l = a[o], !b.relative[u = l.type]); o--)
                        if ((c = b.find[u]) && (r = c(l.matches[0].replace(oe, ""), ne.test(a[0].type) && t.parentNode || t, i))) { if (a.splice(o, 1), !(e = r.length && a.join(""))) return _.apply(n, q.call(r, 0)), n; break } } return N(e, f)(r, t, i, n, ne.test(e)), n }

            function m() {} var y, v, b, x, w, T, N, C, k, E, S = !0,
                A = "undefined",
                j = ("sizcache" + Math.random()).replace(".", ""),
                D = String,
                L = e.document,
                H = L.documentElement,
                F = 0,
                M = 0,
                O = [].pop,
                _ = [].push,
                q = [].slice,
                B = [].indexOf || function(e) { for (var t = 0, n = this.length; t < n; t++)
                        if (this[t] === e) return t; return -1 },
                W = function(e, t) { return e[j] = null == t || t, e },
                P = function() { var e = {},
                        t = []; return W(function(n, r) { return t.push(n) > b.cacheLength && delete e[t.shift()], e[n + " "] = r }, e) },
                R = P(),
                $ = P(),
                I = P(),
                z = "[\\x20\\t\\r\\n\\f]",
                X = "(?:\\\\.|[-\\w]|[^\\x00-\\xa0])+",
                U = X.replace("w", "w#"),
                Y = "\\[" + z + "*(" + X + ")" + z + "*(?:([*^$|!~]?=)" + z + "*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|(" + U + ")|)|)" + z + "*\\]",
                V = ":(" + X + ")(?:\\((?:(['\"])((?:\\\\.|[^\\\\])*?)\\2|([^()[\\]]*|(?:(?:" + Y + ")|[^:]|\\\\.)*|.*))\\)|)",
                J = ":(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + z + "*((?:-\\d)?\\d*)" + z + "*\\)|)(?=[^-]|$)",
                G = new RegExp("^" + z + "+|((?:^|[^\\\\])(?:\\\\.)*)" + z + "+$", "g"),
                K = new RegExp("^" + z + "*," + z + "*"),
                Z = new RegExp("^" + z + "*([\\x20\\t\\r\\n\\f>+~])" + z + "*"),
                ee = new RegExp(V),
                te = /^(?:#([\w\-]+)|(\w+)|\.([\w\-]+))$/,
                ne = /[\x20\t\r\n\f]*[+~]/,
                re = /h\d/i,
                ie = /input|select|textarea|button/i,
                oe = /\\(?!\\)/g,
                ae = { ID: new RegExp("^#(" + X + ")"), CLASS: new RegExp("^\\.(" + X + ")"), NAME: new RegExp("^\\[name=['\"]?(" + X + ")['\"]?\\]"), TAG: new RegExp("^(" + X.replace("w", "w*") + ")"), ATTR: new RegExp("^" + Y), PSEUDO: new RegExp("^" + V), POS: new RegExp(J, "i"), CHILD: new RegExp("^:(only|nth|first|last)-child(?:\\(" + z + "*(even|odd|(([+-]|)(\\d*)n|)" + z + "*(?:([+-]|)" + z + "*(\\d+)|))" + z + "*\\)|)", "i"), needsContext: new RegExp("^" + z + "*[>+~]|" + J, "i") },
                se = function(e) { var t = L.createElement("div"); try { return e(t) } catch (e) { return !1 } finally { t = null } },
                le = se(function(e) { return e.appendChild(L.createComment("")), !e.getElementsByTagName("*").length }),
                ue = se(function(e) { return e.innerHTML = "<a data-ajax=false href='#'></a>", e.firstChild && typeof e.firstChild.getAttribute !== A && "#" === e.firstChild.getAttribute("href") }),
                ce = se(function(e) { e.innerHTML = "<select></select>"; var t = typeof e.lastChild.getAttribute("multiple"); return "boolean" !== t && "string" !== t }),
                fe = se(function(e) { return e.innerHTML = "<div class='hidden e'></div><div class='hidden'></div>", !(!e.getElementsByClassName || !e.getElementsByClassName("e").length) && (e.lastChild.className = "e", 2 === e.getElementsByClassName("e").length) }),
                pe = se(function(e) { e.id = j + 0, e.innerHTML = "<a data-ajax=falsename='" + j + "'></a><div name='" + j + "'></div>", H.insertBefore(e, H.firstChild); var t = L.getElementsByName && L.getElementsByName(j).length === 2 + L.getElementsByName(j + 0).length; return v = !L.getElementById(j), H.removeChild(e), t }); try { q.call(H.childNodes, 0)[0].nodeType } catch (e) { q = function(e) { for (var t, n = []; t = this[e]; e++) n.push(t); return n } } n.matches = function(e, t) { return n(e, null, null, t) }, n.matchesSelector = function(e, t) { return n(t, null, null, [e]).length > 0 }, x = n.getText = function(e) { var t, n = "",
                    r = 0,
                    i = e.nodeType; if (i) { if (1 === i || 9 === i || 11 === i) { if ("string" == typeof e.textContent) return e.textContent; for (e = e.firstChild; e; e = e.nextSibling) n += x(e) } else if (3 === i || 4 === i) return e.nodeValue } else
                    for (; t = e[r]; r++) n += x(t); return n }, w = n.isXML = function(e) { var t = e && (e.ownerDocument || e).documentElement; return !!t && "HTML" !== t.nodeName }, T = n.contains = H.contains ? function(e, t) { var n = 9 === e.nodeType ? e.documentElement : e,
                    r = t && t.parentNode; return e === r || !!(r && 1 === r.nodeType && n.contains && n.contains(r)) } : H.compareDocumentPosition ? function(e, t) { return t && !!(16 & e.compareDocumentPosition(t)) } : function(e, t) { for (; t = t.parentNode;)
                    if (t === e) return !0; return !1 }, n.attr = function(e, t) { var n, r = w(e); return r || (t = t.toLowerCase()), (n = b.attrHandle[t]) ? n(e) : r || ce ? e.getAttribute(t) : (n = e.getAttributeNode(t)) ? "boolean" == typeof e[t] ? e[t] ? t : null : n.specified ? n.value : null : null }, b = n.selectors = { cacheLength: 50, createPseudo: W, match: ae, attrHandle: ue ? {} : { href: function(e) { return e.getAttribute("href", 2) }, type: function(e) { return e.getAttribute("type") } }, find: { ID: v ? function(e, t, n) { if (typeof t.getElementById !== A && !n) { var r = t.getElementById(e); return r && r.parentNode ? [r] : [] } } : function(e, t, n) { if (typeof t.getElementById !== A && !n) { var r = t.getElementById(e); return r ? r.id === e || typeof r.getAttributeNode !== A && r.getAttributeNode("id").value === e ? [r] : void 0 : [] } }, TAG: le ? function(e, t) { if (typeof t.getElementsByTagName !== A) return t.getElementsByTagName(e) } : function(e, t) { var n = t.getElementsByTagName(e); if ("*" === e) { for (var r, i = [], o = 0; r = n[o]; o++) 1 === r.nodeType && i.push(r); return i } return n }, NAME: pe && function(e, t) { if (typeof t.getElementsByName !== A) return t.getElementsByName(name) }, CLASS: fe && function(e, t, n) { if (typeof t.getElementsByClassName !== A && !n) return t.getElementsByClassName(e) } }, relative: { ">": { dir: "parentNode", first: !0 }, " ": { dir: "parentNode" }, "+": { dir: "previousSibling", first: !0 }, "~": { dir: "previousSibling" } }, preFilter: { ATTR: function(e) { return e[1] = e[1].replace(oe, ""), e[3] = (e[4] || e[5] || "").replace(oe, ""), "~=" === e[2] && (e[3] = " " + e[3] + " "), e.slice(0, 4) }, CHILD: function(e) { return e[1] = e[1].toLowerCase(), "nth" === e[1] ? (e[2] || n.error(e[0]), e[3] = +(e[3] ? e[4] + (e[5] || 1) : 2 * ("even" === e[2] || "odd" === e[2])), e[4] = +(e[6] + e[7] || "odd" === e[2])) : e[2] && n.error(e[0]), e }, PSEUDO: function(e) { var t, n; return ae.CHILD.test(e[0]) ? null : (e[3] ? e[2] = e[3] : (t = e[4]) && (ee.test(t) && (n = s(t, !0)) && (n = t.indexOf(")", t.length - n) - t.length) && (t = t.slice(0, n), e[0] = e[0].slice(0, n)), e[2] = t), e.slice(0, 3)) } }, filter: { ID: v ? function(e) { return e = e.replace(oe, ""),
                            function(t) { return t.getAttribute("id") === e } } : function(e) { return e = e.replace(oe, ""),
                            function(t) { var n = typeof t.getAttributeNode !== A && t.getAttributeNode("id"); return n && n.value === e } }, TAG: function(e) { return "*" === e ? function() { return !0 } : (e = e.replace(oe, "").toLowerCase(), function(t) { return t.nodeName && t.nodeName.toLowerCase() === e }) }, CLASS: function(e) { var t = R[j][e + " "]; return t || (t = new RegExp("(^|" + z + ")" + e + "(" + z + "|$)")) && R(e, function(e) { return t.test(e.className || typeof e.getAttribute !== A && e.getAttribute("class") || "") }) }, ATTR: function(e, t, r) { return function(i, o) { var a = n.attr(i, e); return null == a ? "!=" === t : !t || (a += "", "=" === t ? a === r : "!=" === t ? a !== r : "^=" === t ? r && 0 === a.indexOf(r) : "*=" === t ? r && a.indexOf(r) > -1 : "$=" === t ? r && a.substr(a.length - r.length) === r : "~=" === t ? (" " + a + " ").indexOf(r) > -1 : "|=" === t && (a === r || a.substr(0, r.length + 1) === r + "-")) } }, CHILD: function(e, t, n, r) { return "nth" === e ? function(e) { var t, i, o = e.parentNode; if (1 === n && 0 === r) return !0; if (o)
                                for (i = 0, t = o.firstChild; t && (1 !== t.nodeType || (i++, e !== t)); t = t.nextSibling); return (i -= r) === n || i % n == 0 && i / n >= 0 } : function(t) { var n = t; switch (e) {
                                case "only":
                                case "first":
                                    for (; n = n.previousSibling;)
                                        if (1 === n.nodeType) return !1; if ("first" === e) return !0;
                                    n = t;
                                case "last":
                                    for (; n = n.nextSibling;)
                                        if (1 === n.nodeType) return !1; return !0 } } }, PSEUDO: function(e, t) { var r, i = b.pseudos[e] || b.setFilters[e.toLowerCase()] || n.error("unsupported pseudo: " + e); return i[j] ? i(t) : i.length > 1 ? (r = [e, e, "", t], b.setFilters.hasOwnProperty(e.toLowerCase()) ? W(function(e, n) { for (var r, o = i(e, t), a = o.length; a--;) r = B.call(e, o[a]), e[r] = !(n[r] = o[a]) }) : function(e) { return i(e, 0, r) }) : i } }, pseudos: { not: W(function(e) { var t = [],
                            n = [],
                            r = N(e.replace(G, "$1")); return r[j] ? W(function(e, t, n, i) { for (var o, a = r(e, null, i, []), s = e.length; s--;)(o = a[s]) && (e[s] = !(t[s] = o)) }) : function(e, i, o) { return t[0] = e, r(t, null, o, n), !n.pop() } }), has: W(function(e) { return function(t) { return n(e, t).length > 0 } }), contains: W(function(e) { return function(t) { return (t.textContent || t.innerText || x(t)).indexOf(e) > -1 } }), enabled: function(e) { return !1 === e.disabled }, disabled: function(e) { return !0 === e.disabled }, checked: function(e) { var t = e.nodeName.toLowerCase(); return "input" === t && !!e.checked || "option" === t && !!e.selected }, selected: function(e) { return e.parentNode && e.parentNode.selectedIndex, !0 === e.selected }, parent: function(e) { return !b.pseudos.empty(e) }, empty: function(e) { var t; for (e = e.firstChild; e;) { if (e.nodeName > "@" || 3 === (t = e.nodeType) || 4 === t) return !1;
                            e = e.nextSibling } return !0 }, header: function(e) { return re.test(e.nodeName) }, text: function(e) { var t, n; return "input" === e.nodeName.toLowerCase() && "text" === (t = e.type) && (null == (n = e.getAttribute("type")) || n.toLowerCase() === t) }, radio: r("radio"), checkbox: r("checkbox"), file: r("file"), password: r("password"), image: r("image"), submit: i("submit"), reset: i("reset"), button: function(e) { var t = e.nodeName.toLowerCase(); return "input" === t && "button" === e.type || "button" === t }, input: function(e) { return ie.test(e.nodeName) }, focus: function(e) { var t = e.ownerDocument; return e === t.activeElement && (!t.hasFocus || t.hasFocus()) && !!(e.type || e.href || ~e.tabIndex) }, active: function(e) { return e === e.ownerDocument.activeElement }, first: o(function() { return [0] }), last: o(function(e, t) { return [t - 1] }), eq: o(function(e, t, n) { return [n < 0 ? n + t : n] }), even: o(function(e, t) { for (var n = 0; n < t; n += 2) e.push(n); return e }), odd: o(function(e, t) { for (var n = 1; n < t; n += 2) e.push(n); return e }), lt: o(function(e, t, n) { for (var r = n < 0 ? n + t : n; --r >= 0;) e.push(r); return e }), gt: o(function(e, t, n) { for (var r = n < 0 ? n + t : n; ++r < t;) e.push(r); return e }) } }, C = H.compareDocumentPosition ? function(e, t) { return e === t ? (k = !0, 0) : (e.compareDocumentPosition && t.compareDocumentPosition ? 4 & e.compareDocumentPosition(t) : e.compareDocumentPosition) ? -1 : 1 } : function(e, t) { if (e === t) return k = !0, 0; if (e.sourceIndex && t.sourceIndex) return e.sourceIndex - t.sourceIndex; var n, r, i = [],
                    o = [],
                    s = e.parentNode,
                    l = t.parentNode,
                    u = s; if (s === l) return a(e, t); if (!s) return -1; if (!l) return 1; for (; u;) i.unshift(u), u = u.parentNode; for (u = l; u;) o.unshift(u), u = u.parentNode;
                n = i.length, r = o.length; for (var c = 0; c < n && c < r; c++)
                    if (i[c] !== o[c]) return a(i[c], o[c]); return c === n ? a(e, o[c], -1) : a(i[c], t, 1) }, [0, 0].sort(C), S = !k, n.uniqueSort = function(e) { var t, n = [],
                    r = 1,
                    i = 0; if (k = S, e.sort(C), k) { for (; t = e[r]; r++) t === e[r - 1] && (i = n.push(r)); for (; i--;) e.splice(n[i], 1) } return e }, n.error = function(e) { throw new Error("Syntax error, unrecognized expression: " + e) }, N = n.compile = function(e, t) { var n, r = [],
                    i = [],
                    o = I[j][e + " "]; if (!o) { for (t || (t = s(e)), n = t.length; n--;)(o = p(t[n]))[j] ? r.push(o) : i.push(o);
                    o = I(e, d(i, r)) } return o }, L.querySelectorAll && function() { var e, t = g,
                    r = /'|\\/g,
                    i = /\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,
                    o = [":focus"],
                    a = [":active"],
                    l = H.matchesSelector || H.mozMatchesSelector || H.webkitMatchesSelector || H.oMatchesSelector || H.msMatchesSelector;
                se(function(e) { e.innerHTML = "<select><option selected=''></option></select>", e.querySelectorAll("[selected]").length || o.push("\\[" + z + "*(?:checked|disabled|ismap|multiple|readonly|selected|value)"), e.querySelectorAll(":checked").length || o.push(":checked") }), se(function(e) { e.innerHTML = "<p test=''></p>", e.querySelectorAll("[test^='']").length && o.push("[*^$]=" + z + "*(?:\"\"|'')"), e.innerHTML = "<input type='hidden'/>", e.querySelectorAll(":enabled").length || o.push(":enabled", ":disabled") }), o = new RegExp(o.join("|")), g = function(e, n, i, a, l) { if (!a && !l && !o.test(e)) { var u, c, f = !0,
                            p = j,
                            d = n,
                            h = 9 === n.nodeType && e; if (1 === n.nodeType && "object" !== n.nodeName.toLowerCase()) { for (u = s(e), (f = n.getAttribute("id")) ? p = f.replace(r, "\\$&") : n.setAttribute("id", p), p = "[id='" + p + "'] ", c = u.length; c--;) u[c] = p + u[c].join("");
                            d = ne.test(e) && n.parentNode || n, h = u.join(",") } if (h) try { return _.apply(i, q.call(d.querySelectorAll(h), 0)), i } catch (e) {} finally { f || n.removeAttribute("id") } } return t(e, n, i, a, l) }, l && (se(function(t) { e = l.call(t, "div"); try { l.call(t, "[test!='']:sizzle"), a.push("!=", V) } catch (e) {} }), a = new RegExp(a.join("|")), n.matchesSelector = function(t, r) { if (r = r.replace(i, "='$1']"), !w(t) && !a.test(r) && !o.test(r)) try { var s = l.call(t, r); if (s || e || t.document && 11 !== t.document.nodeType) return s } catch (e) {}
                    return n(r, null, null, [t]).length > 0 }) }(), b.pseudos.nth = b.pseudos.eq, b.filters = m.prototype = b.pseudos, b.setFilters = new m, n.attr = Q.attr, Q.find = n, Q.expr = n.selectors, Q.expr[":"] = Q.expr.pseudos, Q.unique = n.uniqueSort, Q.text = n.getText, Q.isXMLDoc = n.isXML, Q.contains = n.contains }(e); var Fe = /Until$/,
        Me = /^(?:parents|prev(?:Until|All))/,
        Oe = /^.[^:#\[\.,]*$/,
        _e = Q.expr.match.needsContext,
        qe = { children: !0, contents: !0, next: !0, prev: !0 };
    Q.fn.extend({ find: function(e) { var t, n, r, i, o, a, s = this; if ("string" != typeof e) return Q(e).filter(function() { for (t = 0, n = s.length; t < n; t++)
                    if (Q.contains(s[t], this)) return !0 }); for (a = this.pushStack("", "find", e), t = 0, n = this.length; t < n; t++)
                if (r = a.length, Q.find(e, this[t], a), t > 0)
                    for (i = r; i < a.length; i++)
                        for (o = 0; o < r; o++)
                            if (a[o] === a[i]) { a.splice(i--, 1); break }
            return a }, has: function(e) { var t, n = Q(e, this),
                r = n.length; return this.filter(function() { for (t = 0; t < r; t++)
                    if (Q.contains(this, n[t])) return !0 }) }, not: function(e) { return this.pushStack(u(this, e, !1), "not", e) }, filter: function(e) { return this.pushStack(u(this, e, !0), "filter", e) }, is: function(e) { return !!e && ("string" == typeof e ? _e.test(e) ? Q(e, this.context).index(this[0]) >= 0 : Q.filter(e, this).length > 0 : this.filter(e).length > 0) }, closest: function(e, t) { for (var n, r = 0, i = this.length, o = [], a = _e.test(e) || "string" != typeof e ? Q(e, t || this.context) : 0; r < i; r++)
                for (n = this[r]; n && n.ownerDocument && n !== t && 11 !== n.nodeType;) { if (a ? a.index(n) > -1 : Q.find.matchesSelector(n, e)) { o.push(n); break } n = n.parentNode }
            return o = o.length > 1 ? Q.unique(o) : o, this.pushStack(o, "closest", e) }, index: function(e) { return e ? "string" == typeof e ? Q.inArray(this[0], Q(e)) : Q.inArray(e.jquery ? e[0] : e, this) : this[0] && this[0].parentNode ? this.prevAll().length : -1 }, add: function(e, t) { var n = "string" == typeof e ? Q(e, t) : Q.makeArray(e && e.nodeType ? [e] : e),
                r = Q.merge(this.get(), n); return this.pushStack(s(n[0]) || s(r[0]) ? r : Q.unique(r)) }, addBack: function(e) { return this.add(null == e ? this.prevObject : this.prevObject.filter(e)) } }), Q.fn.andSelf = Q.fn.addBack, Q.each({ parent: function(e) { var t = e.parentNode; return t && 11 !== t.nodeType ? t : null }, parents: function(e) { return Q.dir(e, "parentNode") }, parentsUntil: function(e, t, n) { return Q.dir(e, "parentNode", n) }, next: function(e) { return l(e, "nextSibling") }, prev: function(e) { return l(e, "previousSibling") }, nextAll: function(e) { return Q.dir(e, "nextSibling") }, prevAll: function(e) { return Q.dir(e, "previousSibling") }, nextUntil: function(e, t, n) { return Q.dir(e, "nextSibling", n) }, prevUntil: function(e, t, n) { return Q.dir(e, "previousSibling", n) }, siblings: function(e) { return Q.sibling((e.parentNode || {}).firstChild, e) }, children: function(e) { return Q.sibling(e.firstChild) }, contents: function(e) { return Q.nodeName(e, "iframe") ? e.contentDocument || e.contentWindow.document : Q.merge([], e.childNodes) } }, function(e, t) { Q.fn[e] = function(n, r) { var i = Q.map(this, t, n); return Fe.test(e) || (r = n), r && "string" == typeof r && (i = Q.filter(r, i)), i = this.length > 1 && !qe[e] ? Q.unique(i) : i, this.length > 1 && Me.test(e) && (i = i.reverse()), this.pushStack(i, e, U.call(arguments).join(",")) } }), Q.extend({ filter: function(e, t, n) { return n && (e = ":not(" + e + ")"), 1 === t.length ? Q.find.matchesSelector(t[0], e) ? [t[0]] : [] : Q.find.matches(e, t) }, dir: function(e, n, r) { for (var i = [], o = e[n]; o && 9 !== o.nodeType && (r === t || 1 !== o.nodeType || !Q(o).is(r));) 1 === o.nodeType && i.push(o), o = o[n]; return i }, sibling: function(e, t) { for (var n = []; e; e = e.nextSibling) 1 === e.nodeType && e !== t && n.push(e); return n } }); var Be = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",
        We = / jQuery\d+="(?:null|\d+)"/g,
        Pe = /^\s+/,
        Re = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
        $e = /<([\w:]+)/,
        Ie = /<tbody/i,
        ze = /<|&#?\w+;/,
        Xe = /<(?:script|style|link)/i,
        Ue = /<(?:script|object|embed|option|style)/i,
        Ye = new RegExp("<(?:" + Be + ")[\\s/>]", "i"),
        Ve = /^(?:checkbox|radio)$/,
        Je = /checked\s*(?:[^=]|=\s*.checked.)/i,
        Ge = /\/(java|ecma)script/i,
        Qe = /^\s*<!(?:\[CDATA\[|\-\-)|[\]\-]{2}>\s*$/g,
        Ke = { option: [1, "<select multiple='multiple'>", "</select>"], legend: [1, "<fieldset>", "</fieldset>"], thead: [1, "<table>", "</table>"], tr: [2, "<table><tbody>", "</tbody></table>"], td: [3, "<table><tbody><tr>", "</tr></tbody></table>"], col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"], area: [1, "<map>", "</map>"], _default: [0, "", ""] },
        Ze = c(P),
        et = Ze.appendChild(P.createElement("div"));
    Ke.optgroup = Ke.option, Ke.tbody = Ke.tfoot = Ke.colgroup = Ke.caption = Ke.thead, Ke.th = Ke.td, Q.support.htmlSerialize || (Ke._default = [1, "X<div>", "</div>"]), Q.fn.extend({ text: function(e) { return Q.access(this, function(e) { return e === t ? Q.text(this) : this.empty().append((this[0] && this[0].ownerDocument || P).createTextNode(e)) }, null, e, arguments.length) }, wrapAll: function(e) { if (Q.isFunction(e)) return this.each(function(t) { Q(this).wrapAll(e.call(this, t)) }); if (this[0]) { var t = Q(e, this[0].ownerDocument).eq(0).clone(!0);
                    this[0].parentNode && t.insertBefore(this[0]), t.map(function() { for (var e = this; e.firstChild && 1 === e.firstChild.nodeType;) e = e.firstChild; return e }).append(this) } return this }, wrapInner: function(e) { return Q.isFunction(e) ? this.each(function(t) { Q(this).wrapInner(e.call(this, t)) }) : this.each(function() { var t = Q(this),
                        n = t.contents();
                    n.length ? n.wrapAll(e) : t.append(e) }) }, wrap: function(e) { var t = Q.isFunction(e); return this.each(function(n) { Q(this).wrapAll(t ? e.call(this, n) : e) }) }, unwrap: function() { return this.parent().each(function() { Q.nodeName(this, "body") || Q(this).replaceWith(this.childNodes) }).end() }, append: function() { return this.domManip(arguments, !0, function(e) {
                    (1 === this.nodeType || 11 === this.nodeType) && this.appendChild(e) }) }, prepend: function() { return this.domManip(arguments, !0, function(e) {
                    (1 === this.nodeType || 11 === this.nodeType) && this.insertBefore(e, this.firstChild) }) }, before: function() { if (!s(this[0])) return this.domManip(arguments, !1, function(e) { this.parentNode.insertBefore(e, this) }); if (arguments.length) { var e = Q.clean(arguments); return this.pushStack(Q.merge(e, this), "before", this.selector) } }, after: function() { if (!s(this[0])) return this.domManip(arguments, !1, function(e) { this.parentNode.insertBefore(e, this.nextSibling) }); if (arguments.length) { var e = Q.clean(arguments); return this.pushStack(Q.merge(this, e), "after", this.selector) } }, remove: function(e, t) { for (var n, r = 0; null != (n = this[r]); r++) e && !Q.filter(e, [n]).length || (!t && 1 === n.nodeType && (Q.cleanData(n.getElementsByTagName("*")), Q.cleanData([n])), n.parentNode && n.parentNode.removeChild(n)); return this }, empty: function() { for (var e, t = 0; null != (e = this[t]); t++)
                    for (1 === e.nodeType && Q.cleanData(e.getElementsByTagName("*")); e.firstChild;) e.removeChild(e.firstChild); return this }, clone: function(e, t) { return e = null != e && e, t = null == t ? e : t, this.map(function() { return Q.clone(this, e, t) }) }, html: function(e) { return Q.access(this, function(e) { var n = this[0] || {},
                        r = 0,
                        i = this.length; if (e === t) return 1 === n.nodeType ? n.innerHTML.replace(We, "") : t; if ("string" == typeof e && !Xe.test(e) && (Q.support.htmlSerialize || !Ye.test(e)) && (Q.support.leadingWhitespace || !Pe.test(e)) && !Ke[($e.exec(e) || ["", ""])[1].toLowerCase()]) { e = e.replace(Re, "<$1></$2>"); try { for (; r < i; r++) 1 === (n = this[r] || {}).nodeType && (Q.cleanData(n.getElementsByTagName("*")), n.innerHTML = e);
                            n = 0 } catch (e) {} } n && this.empty().append(e) }, null, e, arguments.length) }, replaceWith: function(e) { return s(this[0]) ? this.length ? this.pushStack(Q(Q.isFunction(e) ? e() : e), "replaceWith", e) : this : Q.isFunction(e) ? this.each(function(t) { var n = Q(this),
                        r = n.html();
                    n.replaceWith(e.call(this, t, r)) }) : ("string" != typeof e && (e = Q(e).detach()), this.each(function() { var t = this.nextSibling,
                        n = this.parentNode;
                    Q(this).remove(), t ? Q(t).before(e) : Q(n).append(e) })) }, detach: function(e) { return this.remove(e, !0) }, domManip: function(e, n, r) { var i, o, a, s, l = 0,
                    u = (e = [].concat.apply([], e))[0],
                    c = [],
                    p = this.length; if (!Q.support.checkClone && p > 1 && "string" == typeof u && Je.test(u)) return this.each(function() { Q(this).domManip(e, n, r) }); if (Q.isFunction(u)) return this.each(function(i) { var o = Q(this);
                    e[0] = u.call(this, i, n ? o.html() : t), o.domManip(e, n, r) }); if (this[0]) { if (i = Q.buildFragment(e, this, c), a = i.fragment, o = a.firstChild, 1 === a.childNodes.length && (a = o), o)
                        for (n = n && Q.nodeName(o, "tr"), s = i.cacheable || p - 1; l < p; l++) r.call(n && Q.nodeName(this[l], "table") ? f(this[l], "tbody") : this[l], l === s ? a : Q.clone(a, !0, !0));
                    a = o = null, c.length && Q.each(c, function(e, t) { t.src ? Q.ajax ? Q.ajax({ url: t.src, type: "GET", dataType: "script", async: !1, global: !1, throws: !0 }) : Q.error("no ajax") : Q.globalEval((t.text || t.textContent || t.innerHTML || "").replace(Qe, "")), t.parentNode && t.parentNode.removeChild(t) }) } return this } }), Q.buildFragment = function(e, n, r) { var i, o, a, s = e[0]; return n = n || P, n = !n.nodeType && n[0] || n, n = n.ownerDocument || n, 1 === e.length && "string" == typeof s && s.length < 512 && n === P && "<" === s.charAt(0) && !Ue.test(s) && (Q.support.checkClone || !Je.test(s)) && (Q.support.html5Clone || !Ye.test(s)) && (o = !0, i = Q.fragments[s], a = i !== t), i || (i = n.createDocumentFragment(), Q.clean(e, n, i, r), o && (Q.fragments[s] = a && i)), { fragment: i, cacheable: o } }, Q.fragments = {}, Q.each({ appendTo: "append", prependTo: "prepend", insertBefore: "before", insertAfter: "after", replaceAll: "replaceWith" }, function(e, t) { Q.fn[e] = function(n) { var r, i = 0,
                    o = [],
                    a = Q(n),
                    s = a.length,
                    l = 1 === this.length && this[0].parentNode; if ((null == l || l && 11 === l.nodeType && 1 === l.childNodes.length) && 1 === s) return a[t](this[0]), this; for (; i < s; i++) r = (i > 0 ? this.clone(!0) : this).get(), Q(a[i])[t](r), o = o.concat(r); return this.pushStack(o, e, a.selector) } }), Q.extend({ clone: function(e, t, n) { var r, i, o, a; if (Q.support.html5Clone || Q.isXMLDoc(e) || !Ye.test("<" + e.nodeName + ">") ? a = e.cloneNode(!0) : (et.innerHTML = e.outerHTML, et.removeChild(a = et.firstChild)), !(Q.support.noCloneEvent && Q.support.noCloneChecked || 1 !== e.nodeType && 11 !== e.nodeType || Q.isXMLDoc(e)))
                    for (d(e, a), r = h(e), i = h(a), o = 0; r[o]; ++o) i[o] && d(r[o], i[o]); if (t && (p(e, a), n))
                    for (r = h(e), i = h(a), o = 0; r[o]; ++o) p(r[o], i[o]); return r = i = null, a }, clean: function(e, t, n, r) { var i, o, a, s, l, u, f, p, d, h, m, y = t === P && Ze,
                    v = []; for (t && void 0 !== t.createDocumentFragment || (t = P), i = 0; null != (a = e[i]); i++)
                    if ("number" == typeof a && (a += ""), a) { if ("string" == typeof a)
                            if (ze.test(a)) { for (y = y || c(t), f = t.createElement("div"), y.appendChild(f), a = a.replace(Re, "<$1></$2>"), s = ($e.exec(a) || ["", ""])[1].toLowerCase(), u = (l = Ke[s] || Ke._default)[0], f.innerHTML = l[1] + a + l[2]; u--;) f = f.lastChild; if (!Q.support.tbody)
                                    for (p = Ie.test(a), o = (d = "table" !== s || p ? "<table>" !== l[1] || p ? [] : f.childNodes : f.firstChild && f.firstChild.childNodes).length - 1; o >= 0; --o) Q.nodeName(d[o], "tbody") && !d[o].childNodes.length && d[o].parentNode.removeChild(d[o]);!Q.support.leadingWhitespace && Pe.test(a) && f.insertBefore(t.createTextNode(Pe.exec(a)[0]), f.firstChild), a = f.childNodes, f.parentNode.removeChild(f) } else a = t.createTextNode(a);
                        a.nodeType ? v.push(a) : Q.merge(v, a) }
                if (f && (a = f = y = null), !Q.support.appendChecked)
                    for (i = 0; null != (a = v[i]); i++) Q.nodeName(a, "input") ? g(a) : void 0 !== a.getElementsByTagName && Q.grep(a.getElementsByTagName("input"), g); if (n)
                    for (h = function(e) { if (!e.type || Ge.test(e.type)) return r ? r.push(e.parentNode ? e.parentNode.removeChild(e) : e) : n.appendChild(e) }, i = 0; null != (a = v[i]); i++) Q.nodeName(a, "script") && h(a) || (n.appendChild(a), void 0 !== a.getElementsByTagName && (m = Q.grep(Q.merge([], a.getElementsByTagName("script")), h), v.splice.apply(v, [i + 1, 0].concat(m)), i += m.length)); return v }, cleanData: function(e, t) { for (var n, r, i, o, a = 0, s = Q.expando, l = Q.cache, u = Q.support.deleteExpando, c = Q.event.special; null != (i = e[a]); a++)
                    if ((t || Q.acceptData(i)) && (r = i[s], n = r && l[r])) { if (n.events)
                            for (o in n.events) c[o] ? Q.event.remove(i, o) : Q.removeEvent(i, o, n.handle);
                        l[r] && (delete l[r], u ? delete i[s] : i.removeAttribute ? i.removeAttribute(s) : i[s] = null, Q.deletedIds.push(r)) } } }),
        function() { var e, t;
            Q.uaMatch = function(e) { e = e.toLowerCase(); var t = /(chrome)[ \/]([\w.]+)/.exec(e) || /(webkit)[ \/]([\w.]+)/.exec(e) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(e) || /(msie) ([\w.]+)/.exec(e) || e.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(e) || []; return { browser: t[1] || "", version: t[2] || "0" } }, t = {}, (e = Q.uaMatch($.userAgent)).browser && (t[e.browser] = !0, t.version = e.version), t.chrome ? t.webkit = !0 : t.webkit && (t.safari = !0), Q.browser = t, Q.sub = function() {
                function e(t, n) { return new e.fn.init(t, n) } Q.extend(!0, e, this), e.superclass = this, e.fn = e.prototype = this(), e.fn.constructor = e, e.sub = this.sub, e.fn.init = function(n, r) { return r && r instanceof Q && !(r instanceof e) && (r = e(r)), Q.fn.init.call(this, n, r, t) }, e.fn.init.prototype = e.fn; var t = e(P); return e } }(); var tt, nt, rt, it = /alpha\([^)]*\)/i,
        ot = /opacity=([^)]*)/,
        at = /^(top|right|bottom|left)$/,
        st = /^(none|table(?!-c[ea]).+)/,
        lt = /^margin/,
        ut = new RegExp("^(" + K + ")(.*)$", "i"),
        ct = new RegExp("^(" + K + ")(?!px)[a-z%]+$", "i"),
        ft = new RegExp("^([-+])=(" + K + ")", "i"),
        pt = { BODY: "block" },
        dt = { position: "absolute", visibility: "hidden", display: "block" },
        ht = { letterSpacing: 0, fontWeight: 400 },
        gt = ["Top", "Right", "Bottom", "Left"],
        mt = ["Webkit", "O", "Moz", "ms"],
        yt = Q.fn.toggle;
    Q.fn.extend({ css: function(e, n) { return Q.access(this, function(e, n, r) { return r !== t ? Q.style(e, n, r) : Q.css(e, n) }, e, n, arguments.length > 1) }, show: function() { return v(this, !0) }, hide: function() { return v(this) }, toggle: function(e, t) { var n = "boolean" == typeof e; return Q.isFunction(e) && Q.isFunction(t) ? yt.apply(this, arguments) : this.each(function() {
                (n ? e : y(this)) ? Q(this).show(): Q(this).hide() }) } }), Q.extend({ cssHooks: { opacity: { get: function(e, t) { if (t) { var n = tt(e, "opacity"); return "" === n ? "1" : n } } } }, cssNumber: { fillOpacity: !0, fontWeight: !0, lineHeight: !0, opacity: !0, orphans: !0, widows: !0, zIndex: !0, zoom: !0 }, cssProps: { float: Q.support.cssFloat ? "cssFloat" : "styleFloat" }, style: function(e, n, r, i) { if (e && 3 !== e.nodeType && 8 !== e.nodeType && e.style) { var o, a, s, l = Q.camelCase(n),
                    u = e.style; if (n = Q.cssProps[l] || (Q.cssProps[l] = m(u, l)), s = Q.cssHooks[n] || Q.cssHooks[l], r === t) return s && "get" in s && (o = s.get(e, !1, i)) !== t ? o : u[n]; if ("string" == (a = typeof r) && (o = ft.exec(r)) && (r = (o[1] + 1) * o[2] + parseFloat(Q.css(e, n)), a = "number"), !(null == r || "number" === a && isNaN(r) || ("number" === a && !Q.cssNumber[l] && (r += "px"), s && "set" in s && (r = s.set(e, r, i)) === t))) try { u[n] = r } catch (e) {} } }, css: function(e, n, r, i) { var o, a, s, l = Q.camelCase(n); return n = Q.cssProps[l] || (Q.cssProps[l] = m(e.style, l)), (s = Q.cssHooks[n] || Q.cssHooks[l]) && "get" in s && (o = s.get(e, !0, i)), o === t && (o = tt(e, n)), "normal" === o && n in ht && (o = ht[n]), r || i !== t ? (a = parseFloat(o), r || Q.isNumeric(a) ? a || 0 : o) : o }, swap: function(e, t, n) { var r, i, o = {}; for (i in t) o[i] = e.style[i], e.style[i] = t[i];
            r = n.call(e); for (i in t) e.style[i] = o[i]; return r } }), e.getComputedStyle ? tt = function(t, n) { var r, i, o, a, s = e.getComputedStyle(t, null),
            l = t.style; return s && ("" === (r = s.getPropertyValue(n) || s[n]) && !Q.contains(t.ownerDocument, t) && (r = Q.style(t, n)), ct.test(r) && lt.test(n) && (i = l.width, o = l.minWidth, a = l.maxWidth, l.minWidth = l.maxWidth = l.width = r, r = s.width, l.width = i, l.minWidth = o, l.maxWidth = a)), r } : P.documentElement.currentStyle && (tt = function(e, t) { var n, r, i = e.currentStyle && e.currentStyle[t],
            o = e.style; return null == i && o && o[t] && (i = o[t]), ct.test(i) && !at.test(t) && (n = o.left, (r = e.runtimeStyle && e.runtimeStyle.left) && (e.runtimeStyle.left = e.currentStyle.left), o.left = "fontSize" === t ? "1em" : i, i = o.pixelLeft + "px", o.left = n, r && (e.runtimeStyle.left = r)), "" === i ? "auto" : i }), Q.each(["height", "width"], function(e, t) { Q.cssHooks[t] = { get: function(e, n, r) { if (n) return 0 === e.offsetWidth && st.test(tt(e, "display")) ? Q.swap(e, dt, function() { return w(e, t, r) }) : w(e, t, r) }, set: function(e, n, r) { return b(0, n, r ? x(e, t, r, Q.support.boxSizing && "border-box" === Q.css(e, "boxSizing")) : 0) } } }), Q.support.opacity || (Q.cssHooks.opacity = { get: function(e, t) { return ot.test((t && e.currentStyle ? e.currentStyle.filter : e.style.filter) || "") ? .01 * parseFloat(RegExp.$1) + "" : t ? "1" : "" }, set: function(e, t) { var n = e.style,
                r = e.currentStyle,
                i = Q.isNumeric(t) ? "alpha(opacity=" + 100 * t + ")" : "",
                o = r && r.filter || n.filter || "";
            n.zoom = 1, t >= 1 && "" === Q.trim(o.replace(it, "")) && n.removeAttribute && (n.removeAttribute("filter"), r && !r.filter) || (n.filter = it.test(o) ? o.replace(it, i) : o + " " + i) } }), Q(function() { Q.support.reliableMarginRight || (Q.cssHooks.marginRight = { get: function(e, t) { return Q.swap(e, { display: "inline-block" }, function() { if (t) return tt(e, "marginRight") }) } }), !Q.support.pixelPosition && Q.fn.position && Q.each(["top", "left"], function(e, t) { Q.cssHooks[t] = { get: function(e, n) { if (n) { var r = tt(e, t); return ct.test(r) ? Q(e).position()[t] + "px" : r } } } }) }), Q.expr && Q.expr.filters && (Q.expr.filters.hidden = function(e) { return 0 === e.offsetWidth && 0 === e.offsetHeight || !Q.support.reliableHiddenOffsets && "none" === (e.style && e.style.display || tt(e, "display")) }, Q.expr.filters.visible = function(e) { return !Q.expr.filters.hidden(e) }), Q.each({ margin: "", padding: "", border: "Width" }, function(e, t) { Q.cssHooks[e + t] = { expand: function(n) { var r, i = "string" == typeof n ? n.split(" ") : [n],
                    o = {}; for (r = 0; r < 4; r++) o[e + gt[r] + t] = i[r] || i[r - 2] || i[0]; return o } }, lt.test(e) || (Q.cssHooks[e + t].set = b) }); var vt = /%20/g,
        bt = /\[\]$/,
        xt = /\r?\n/g,
        wt = /^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,
        Tt = /^(?:select|textarea)/i;
    Q.fn.extend({ serialize: function() { return Q.param(this.serializeArray()) }, serializeArray: function() { return this.map(function() { return this.elements ? Q.makeArray(this.elements) : this }).filter(function() { return this.name && !this.disabled && (this.checked || Tt.test(this.nodeName) || wt.test(this.type)) }).map(function(e, t) { var n = Q(this).val(); return null == n ? null : Q.isArray(n) ? Q.map(n, function(e, n) { return { name: t.name, value: e.replace(xt, "\r\n") } }) : { name: t.name, value: n.replace(xt, "\r\n") } }).get() } }), Q.param = function(e, n) { var r, i = [],
            o = function(e, t) { t = Q.isFunction(t) ? t() : null == t ? "" : t, i[i.length] = encodeURIComponent(e) + "=" + encodeURIComponent(t) }; if (n === t && (n = Q.ajaxSettings && Q.ajaxSettings.traditional), Q.isArray(e) || e.jquery && !Q.isPlainObject(e)) Q.each(e, function() { o(this.name, this.value) });
        else
            for (r in e) N(r, e[r], n, o); return i.join("&").replace(vt, "+") }; var Nt, Ct, kt = /#.*$/,
        Et = /^(.*?):[ \t]*([^\r\n]*)\r?$/gm,
        St = /^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,
        At = /^(?:GET|HEAD)$/,
        jt = /^\/\//,
        Dt = /\?/,
        Lt = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
        Ht = /([?&])_=[^&]*/,
        Ft = /^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,
        Mt = Q.fn.load,
        Ot = {},
        _t = {},
        qt = ["*/"] + ["*"]; try { Ct = R.href } catch (e) {
        (Ct = P.createElement("a")).href = "", Ct = Ct.href } Nt = Ft.exec(Ct.toLowerCase()) || [], Q.fn.load = function(e, n, r) { if ("string" != typeof e && Mt) return Mt.apply(this, arguments); if (!this.length) return this; var i, o, a, s = this,
            l = e.indexOf(" "); return l >= 0 && (i = e.slice(l, e.length), e = e.slice(0, l)), Q.isFunction(n) ? (r = n, n = t) : n && "object" == typeof n && (o = "POST"), Q.ajax({ url: e, type: o, dataType: "html", data: n, complete: function(e, t) { r && s.each(r, a || [e.responseText, t, e]) } }).done(function(e) { a = arguments, s.html(i ? Q("<div>").append(e.replace(Lt, "")).find(i) : e) }), this }, Q.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "), function(e, t) { Q.fn[t] = function(e) { return this.on(t, e) } }), Q.each(["get", "post"], function(e, n) { Q[n] = function(e, r, i, o) { return Q.isFunction(r) && (o = o || i, i = r, r = t), Q.ajax({ type: n, url: e, data: r, success: i, dataType: o }) } }), Q.extend({ getScript: function(e, n) { return Q.get(e, t, n, "script") }, getJSON: function(e, t, n) { return Q.get(e, t, n, "json") }, ajaxSetup: function(e, t) { return t ? E(e, Q.ajaxSettings) : (t = e, e = Q.ajaxSettings), E(e, t), e }, ajaxSettings: { url: Ct, isLocal: St.test(Nt[1]), global: !0, type: "GET", contentType: "application/x-www-form-urlencoded; charset=UTF-8", processData: !0, async: !0, accepts: { xml: "application/xml, text/xml", html: "text/html", text: "text/plain", json: "application/json, text/javascript", "*": qt }, contents: { xml: /xml/, html: /html/, json: /json/ }, responseFields: { xml: "responseXML", text: "responseText" }, converters: { "* text": e.String, "text html": !0, "text json": Q.parseJSON, "text xml": Q.parseXML }, flatOptions: { context: !0, url: !0 } }, ajaxPrefilter: C(Ot), ajaxTransport: C(_t), ajax: function(e, n) {
            function r(e, n, r, a) { var u, f, v, b, w, N = n;
                2 !== x && (x = 2, l && clearTimeout(l), s = t, o = a || "", T.readyState = e > 0 ? 4 : 0, r && (b = S(p, T, r)), e >= 200 && e < 300 || 304 === e ? (p.ifModified && ((w = T.getResponseHeader("Last-Modified")) && (Q.lastModified[i] = w), (w = T.getResponseHeader("Etag")) && (Q.etag[i] = w)), 304 === e ? (N = "notmodified", u = !0) : (u = A(p, b), N = u.state, f = u.data, v = u.error, u = !v)) : (v = N, N && !e || (N = "error", e < 0 && (e = 0))), T.status = e, T.statusText = (n || N) + "", u ? g.resolveWith(d, [f, N, T]) : g.rejectWith(d, [T, N, v]), T.statusCode(y), y = t, c && h.trigger("ajax" + (u ? "Success" : "Error"), [T, p, u ? f : v]), m.fireWith(d, [T, N]), c && (h.trigger("ajaxComplete", [T, p]), --Q.active || Q.event.trigger("ajaxStop"))) } "object" == typeof e && (n = e, e = t), n = n || {}; var i, o, a, s, l, u, c, f, p = Q.ajaxSetup({}, n),
                d = p.context || p,
                h = d !== p && (d.nodeType || d instanceof Q) ? Q(d) : Q.event,
                g = Q.Deferred(),
                m = Q.Callbacks("once memory"),
                y = p.statusCode || {},
                v = {},
                b = {},
                x = 0,
                w = "canceled",
                T = { readyState: 0, setRequestHeader: function(e, t) { if (!x) { var n = e.toLowerCase();
                            e = b[n] = b[n] || e, v[e] = t } return this }, getAllResponseHeaders: function() { return 2 === x ? o : null }, getResponseHeader: function(e) { var n; if (2 === x) { if (!a)
                                for (a = {}; n = Et.exec(o);) a[n[1].toLowerCase()] = n[2];
                            n = a[e.toLowerCase()] } return n === t ? null : n }, overrideMimeType: function(e) { return x || (p.mimeType = e), this }, abort: function(e) { return e = e || w, s && s.abort(e), r(0, e), this } }; if (g.promise(T), T.success = T.done, T.error = T.fail, T.complete = m.add, T.statusCode = function(e) { if (e) { var t; if (x < 2)
                            for (t in e) y[t] = [y[t], e[t]];
                        else t = e[T.status], T.always(t) } return this }, p.url = ((e || p.url) + "").replace(kt, "").replace(jt, Nt[1] + "//"), p.dataTypes = Q.trim(p.dataType || "*").toLowerCase().split(ee), null == p.crossDomain && (u = Ft.exec(p.url.toLowerCase()), p.crossDomain = !(!u || u[1] === Nt[1] && u[2] === Nt[2] && (u[3] || ("http:" === u[1] ? 80 : 443)) == (Nt[3] || ("http:" === Nt[1] ? 80 : 443)))), p.data && p.processData && "string" != typeof p.data && (p.data = Q.param(p.data, p.traditional)), k(Ot, p, n, T), 2 === x) return T; if (c = p.global, p.type = p.type.toUpperCase(), p.hasContent = !At.test(p.type), c && 0 == Q.active++ && Q.event.trigger("ajaxStart"), !p.hasContent && (p.data && (p.url += (Dt.test(p.url) ? "&" : "?") + p.data, delete p.data), i = p.url, !1 === p.cache)) { var N = Q.now(),
                    C = p.url.replace(Ht, "$1_=" + N);
                p.url = C + (C === p.url ? (Dt.test(p.url) ? "&" : "?") + "_=" + N : "") }(p.data && p.hasContent && !1 !== p.contentType || n.contentType) && T.setRequestHeader("Content-Type", p.contentType), p.ifModified && (i = i || p.url, Q.lastModified[i] && T.setRequestHeader("If-Modified-Since", Q.lastModified[i]), Q.etag[i] && T.setRequestHeader("If-None-Match", Q.etag[i])), T.setRequestHeader("Accept", p.dataTypes[0] && p.accepts[p.dataTypes[0]] ? p.accepts[p.dataTypes[0]] + ("*" !== p.dataTypes[0] ? ", " + qt + "; q=0.01" : "") : p.accepts["*"]); for (f in p.headers) T.setRequestHeader(f, p.headers[f]); if (!p.beforeSend || !1 !== p.beforeSend.call(d, T, p) && 2 !== x) { w = "abort"; for (f in { success: 1, error: 1, complete: 1 }) T[f](p[f]); if (s = k(_t, p, n, T)) { T.readyState = 1, c && h.trigger("ajaxSend", [T, p]), p.async && p.timeout > 0 && (l = setTimeout(function() { T.abort("timeout") }, p.timeout)); try { x = 1, s.send(v, r) } catch (e) { if (!(x < 2)) throw e;
                        r(-1, e) } } else r(-1, "No Transport"); return T } return T.abort() }, active: 0, lastModified: {}, etag: {} }); var Bt = [],
        Wt = /\?/,
        Pt = /(=)\?(?=&|$)|\?\?/,
        Rt = Q.now();
    Q.ajaxSetup({ jsonp: "callback", jsonpCallback: function() { var e = Bt.pop() || Q.expando + "_" + Rt++; return this[e] = !0, e } }), Q.ajaxPrefilter("json jsonp", function(n, r, i) { var o, a, s, l = n.data,
            u = n.url,
            c = !1 !== n.jsonp,
            f = c && Pt.test(u),
            p = c && !f && "string" == typeof l && !(n.contentType || "").indexOf("application/x-www-form-urlencoded") && Pt.test(l); if ("jsonp" === n.dataTypes[0] || f || p) return o = n.jsonpCallback = Q.isFunction(n.jsonpCallback) ? n.jsonpCallback() : n.jsonpCallback, a = e[o], f ? n.url = u.replace(Pt, "$1" + o) : p ? n.data = l.replace(Pt, "$1" + o) : c && (n.url += (Wt.test(u) ? "&" : "?") + n.jsonp + "=" + o), n.converters["script json"] = function() { return s || Q.error(o + " was not called"), s[0] }, n.dataTypes[0] = "json", e[o] = function() { s = arguments }, i.always(function() { e[o] = a, n[o] && (n.jsonpCallback = r.jsonpCallback, Bt.push(o)), s && Q.isFunction(a) && a(s[0]), s = a = t }), "script" }), Q.ajaxSetup({ accepts: { script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript" }, contents: { script: /javascript|ecmascript/ }, converters: { "text script": function(e) { return Q.globalEval(e), e } } }), Q.ajaxPrefilter("script", function(e) { e.cache === t && (e.cache = !1), e.crossDomain && (e.type = "GET", e.global = !1) }), Q.ajaxTransport("script", function(e) { if (e.crossDomain) { var n, r = P.head || P.getElementsByTagName("head")[0] || P.documentElement; return { send: function(i, o) {
                    (n = P.createElement("script")).async = "async", e.scriptCharset && (n.charset = e.scriptCharset), n.src = e.url, n.onload = n.onreadystatechange = function(e, i) {
                        (i || !n.readyState || /loaded|complete/.test(n.readyState)) && (n.onload = n.onreadystatechange = null, r && n.parentNode && r.removeChild(n), n = t, i || o(200, "success")) }, r.insertBefore(n, r.firstChild) }, abort: function() { n && n.onload(0, 1) } } } }); var $t, It = !!e.ActiveXObject && function() { for (var e in $t) $t[e](0, 1) },
        zt = 0;
    Q.ajaxSettings.xhr = e.ActiveXObject ? function() { return !this.isLocal && j() || D() } : j,
        function(e) { Q.extend(Q.support, { ajax: !!e, cors: !!e && "withCredentials" in e }) }(Q.ajaxSettings.xhr()), Q.support.ajax && Q.ajaxTransport(function(n) { if (!n.crossDomain || Q.support.cors) { var r; return { send: function(i, o) { var a, s, l = n.xhr(); if (n.username ? l.open(n.type, n.url, n.async, n.username, n.password) : l.open(n.type, n.url, n.async), n.xhrFields)
                            for (s in n.xhrFields) l[s] = n.xhrFields[s];
                        n.mimeType && l.overrideMimeType && l.overrideMimeType(n.mimeType), !n.crossDomain && !i["X-Requested-With"] && (i["X-Requested-With"] = "XMLHttpRequest"); try { for (s in i) l.setRequestHeader(s, i[s]) } catch (e) {} l.send(n.hasContent && n.data || null), r = function(e, i) { var s, u, c, f, p; try { if (r && (i || 4 === l.readyState))
                                    if (r = t, a && (l.onreadystatechange = Q.noop, It && delete $t[a]), i) 4 !== l.readyState && l.abort();
                                    else { s = l.status, c = l.getAllResponseHeaders(), f = {}, (p = l.responseXML) && p.documentElement && (f.xml = p); try { f.text = l.responseText } catch (e) {} try { u = l.statusText } catch (e) { u = "" } s || !n.isLocal || n.crossDomain ? 1223 === s && (s = 204) : s = f.text ? 200 : 404 } } catch (e) { i || o(-1, e) } f && o(s, u, f, c) }, n.async ? 4 === l.readyState ? setTimeout(r, 0) : (a = ++zt, It && ($t || ($t = {}, Q(e).unload(It)), $t[a] = r), l.onreadystatechange = r) : r() }, abort: function() { r && r(0, 1) } } } }); var Xt, Ut, Yt = /^(?:toggle|show|hide)$/,
        Vt = new RegExp("^(?:([-+])=|)(" + K + ")([a-z%]*)$", "i"),
        Jt = /queueHooks$/,
        Gt = [function(e, t, n) { var r, i, o, a, s, l, u, c, f, p = this,
                d = e.style,
                h = {},
                g = [],
                m = e.nodeType && y(e);
            n.queue || (null == (c = Q._queueHooks(e, "fx")).unqueued && (c.unqueued = 0, f = c.empty.fire, c.empty.fire = function() { c.unqueued || f() }), c.unqueued++, p.always(function() { p.always(function() { c.unqueued--, Q.queue(e, "fx").length || c.empty.fire() }) })), 1 === e.nodeType && ("height" in t || "width" in t) && (n.overflow = [d.overflow, d.overflowX, d.overflowY], "inline" === Q.css(e, "display") && "none" === Q.css(e, "float") && (Q.support.inlineBlockNeedsLayout && "inline" !== T(e.nodeName) ? d.zoom = 1 : d.display = "inline-block")), n.overflow && (d.overflow = "hidden", Q.support.shrinkWrapBlocks || p.done(function() { d.overflow = n.overflow[0], d.overflowX = n.overflow[1], d.overflowY = n.overflow[2] })); for (r in t)
                if (o = t[r], Yt.exec(o)) { if (delete t[r], l = l || "toggle" === o, o === (m ? "hide" : "show")) continue;
                    g.push(r) }
            if (a = g.length) { "hidden" in (s = Q._data(e, "fxshow") || Q._data(e, "fxshow", {})) && (m = s.hidden), l && (s.hidden = !m), m ? Q(e).show() : p.done(function() { Q(e).hide() }), p.done(function() { var t;
                    Q.removeData(e, "fxshow", !0); for (t in h) Q.style(e, t, h[t]) }); for (r = 0; r < a; r++) i = g[r], u = p.createTween(i, m ? s[i] : 0), h[i] = s[i] || Q.style(e, i), i in s || (s[i] = u.start, m && (u.end = u.start, u.start = "width" === i || "height" === i ? 1 : 0)) } }],
        Qt = { "*": [function(e, t) { var n, r, i = this.createTween(e, t),
                    o = Vt.exec(t),
                    a = i.cur(),
                    s = +a || 0,
                    l = 1,
                    u = 20; if (o) { if (n = +o[2], "px" !== (r = o[3] || (Q.cssNumber[e] ? "" : "px")) && s) { s = Q.css(i.elem, e, !0) || n || 1;
                        do { l = l || ".5", s /= l, Q.style(i.elem, e, s + r) } while (l !== (l = i.cur() / a) && 1 !== l && --u) } i.unit = r, i.start = s, i.end = o[1] ? s + (o[1] + 1) * n : n } return i }] };
    Q.Animation = Q.extend(F, { tweener: function(e, t) { Q.isFunction(e) ? (t = e, e = ["*"]) : e = e.split(" "); for (var n, r = 0, i = e.length; r < i; r++) n = e[r], Qt[n] = Qt[n] || [], Qt[n].unshift(t) }, prefilter: function(e, t) { t ? Gt.unshift(e) : Gt.push(e) } }), Q.Tween = O, O.prototype = { constructor: O, init: function(e, t, n, r, i, o) { this.elem = e, this.prop = n, this.easing = i || "swing", this.options = t, this.start = this.now = this.cur(), this.end = r, this.unit = o || (Q.cssNumber[n] ? "" : "px") }, cur: function() { var e = O.propHooks[this.prop]; return e && e.get ? e.get(this) : O.propHooks._default.get(this) }, run: function(e) { var t, n = O.propHooks[this.prop]; return this.options.duration ? this.pos = t = Q.easing[this.easing](e, this.options.duration * e, 0, 1, this.options.duration) : this.pos = t = e, this.now = (this.end - this.start) * t + this.start, this.options.step && this.options.step.call(this.elem, this.now, this), n && n.set ? n.set(this) : O.propHooks._default.set(this), this } }, O.prototype.init.prototype = O.prototype, O.propHooks = { _default: { get: function(e) { var t; return null == e.elem[e.prop] || e.elem.style && null != e.elem.style[e.prop] ? (t = Q.css(e.elem, e.prop, !1, "")) && "auto" !== t ? t : 0 : e.elem[e.prop] }, set: function(e) { Q.fx.step[e.prop] ? Q.fx.step[e.prop](e) : e.elem.style && (null != e.elem.style[Q.cssProps[e.prop]] || Q.cssHooks[e.prop]) ? Q.style(e.elem, e.prop, e.now + e.unit) : e.elem[e.prop] = e.now } } }, O.propHooks.scrollTop = O.propHooks.scrollLeft = { set: function(e) { e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now) } }, Q.each(["toggle", "show", "hide"], function(e, t) { var n = Q.fn[t];
        Q.fn[t] = function(r, i, o) { return null == r || "boolean" == typeof r || !e && Q.isFunction(r) && Q.isFunction(i) ? n.apply(this, arguments) : this.animate(_(t, !0), r, i, o) } }), Q.fn.extend({ fadeTo: function(e, t, n, r) { return this.filter(y).css("opacity", 0).show().end().animate({ opacity: t }, e, n, r) }, animate: function(e, t, n, r) { var i = Q.isEmptyObject(e),
                o = Q.speed(t, n, r),
                a = function() { var t = F(this, Q.extend({}, e), o);
                    i && t.stop(!0) }; return i || !1 === o.queue ? this.each(a) : this.queue(o.queue, a) }, stop: function(e, n, r) { var i = function(e) { var t = e.stop;
                delete e.stop, t(r) }; return "string" != typeof e && (r = n, n = e, e = t), n && !1 !== e && this.queue(e || "fx", []), this.each(function() { var t = !0,
                    n = null != e && e + "queueHooks",
                    o = Q.timers,
                    a = Q._data(this); if (n) a[n] && a[n].stop && i(a[n]);
                else
                    for (n in a) a[n] && a[n].stop && Jt.test(n) && i(a[n]); for (n = o.length; n--;) o[n].elem === this && (null == e || o[n].queue === e) && (o[n].anim.stop(r), t = !1, o.splice(n, 1));
                (t || !r) && Q.dequeue(this, e) }) } }), Q.each({ slideDown: _("show"), slideUp: _("hide"), slideToggle: _("toggle"), fadeIn: { opacity: "show" }, fadeOut: { opacity: "hide" }, fadeToggle: { opacity: "toggle" } }, function(e, t) { Q.fn[e] = function(e, n, r) { return this.animate(t, e, n, r) } }), Q.speed = function(e, t, n) { var r = e && "object" == typeof e ? Q.extend({}, e) : { complete: n || !n && t || Q.isFunction(e) && e, duration: e, easing: n && t || t && !Q.isFunction(t) && t }; return r.duration = Q.fx.off ? 0 : "number" == typeof r.duration ? r.duration : r.duration in Q.fx.speeds ? Q.fx.speeds[r.duration] : Q.fx.speeds._default, null != r.queue && !0 !== r.queue || (r.queue = "fx"), r.old = r.complete, r.complete = function() { Q.isFunction(r.old) && r.old.call(this), r.queue && Q.dequeue(this, r.queue) }, r }, Q.easing = { linear: function(e) { return e }, swing: function(e) { return .5 - Math.cos(e * Math.PI) / 2 } }, Q.timers = [], Q.fx = O.prototype.init, Q.fx.tick = function() { var e, n = Q.timers,
            r = 0; for (Xt = Q.now(); r < n.length; r++) !(e = n[r])() && n[r] === e && n.splice(r--, 1);
        n.length || Q.fx.stop(), Xt = t }, Q.fx.timer = function(e) { e() && Q.timers.push(e) && !Ut && (Ut = setInterval(Q.fx.tick, Q.fx.interval)) }, Q.fx.interval = 13, Q.fx.stop = function() { clearInterval(Ut), Ut = null }, Q.fx.speeds = { slow: 600, fast: 200, _default: 400 }, Q.fx.step = {}, Q.expr && Q.expr.filters && (Q.expr.filters.animated = function(e) { return Q.grep(Q.timers, function(t) { return e === t.elem }).length }); var Kt = /^(?:body|html)$/i;
    Q.fn.offset = function(e) { if (arguments.length) return e === t ? this : this.each(function(t) { Q.offset.setOffset(this, e, t) }); var n, r, i, o, a, s, l, u = { top: 0, left: 0 },
            c = this[0],
            f = c && c.ownerDocument; if (f) return (r = f.body) === c ? Q.offset.bodyOffset(c) : (n = f.documentElement, Q.contains(n, c) ? (void 0 !== c.getBoundingClientRect && (u = c.getBoundingClientRect()), i = q(f), o = n.clientTop || r.clientTop || 0, a = n.clientLeft || r.clientLeft || 0, s = i.pageYOffset || n.scrollTop, l = i.pageXOffset || n.scrollLeft, { top: u.top + s - o, left: u.left + l - a }) : u) }, Q.offset = { bodyOffset: function(e) { var t = e.offsetTop,
                n = e.offsetLeft; return Q.support.doesNotIncludeMarginInBodyOffset && (t += parseFloat(Q.css(e, "marginTop")) || 0, n += parseFloat(Q.css(e, "marginLeft")) || 0), { top: t, left: n } }, setOffset: function(e, t, n) { var r = Q.css(e, "position"); "static" === r && (e.style.position = "relative"); var i, o, a = Q(e),
                s = a.offset(),
                l = Q.css(e, "top"),
                u = Q.css(e, "left"),
                c = {},
                f = {};
            ("absolute" === r || "fixed" === r) && Q.inArray("auto", [l, u]) > -1 ? (f = a.position(), i = f.top, o = f.left) : (i = parseFloat(l) || 0, o = parseFloat(u) || 0), Q.isFunction(t) && (t = t.call(e, n, s)), null != t.top && (c.top = t.top - s.top + i), null != t.left && (c.left = t.left - s.left + o), "using" in t ? t.using.call(e, c) : a.css(c) } }, Q.fn.extend({ position: function() { if (this[0]) { var e = this[0],
                    t = this.offsetParent(),
                    n = this.offset(),
                    r = Kt.test(t[0].nodeName) ? { top: 0, left: 0 } : t.offset(); return n.top -= parseFloat(Q.css(e, "marginTop")) || 0, n.left -= parseFloat(Q.css(e, "marginLeft")) || 0, r.top += parseFloat(Q.css(t[0], "borderTopWidth")) || 0, r.left += parseFloat(Q.css(t[0], "borderLeftWidth")) || 0, { top: n.top - r.top, left: n.left - r.left } } }, offsetParent: function() { return this.map(function() { for (var e = this.offsetParent || P.body; e && !Kt.test(e.nodeName) && "static" === Q.css(e, "position");) e = e.offsetParent; return e || P.body }) } }), Q.each({ scrollLeft: "pageXOffset", scrollTop: "pageYOffset" }, function(e, n) { var r = /Y/.test(n);
        Q.fn[e] = function(i) { return Q.access(this, function(e, i, o) { var a = q(e); if (o === t) return a ? n in a ? a[n] : a.document.documentElement[i] : e[i];
                a ? a.scrollTo(r ? Q(a).scrollLeft() : o, r ? o : Q(a).scrollTop()) : e[i] = o }, e, i, arguments.length, null) } }), Q.each({ Height: "height", Width: "width" }, function(e, n) { Q.each({ padding: "inner" + e, content: n, "": "outer" + e }, function(r, i) { Q.fn[i] = function(i, o) { var a = arguments.length && (r || "boolean" != typeof i),
                    s = r || (!0 === i || !0 === o ? "margin" : "border"); return Q.access(this, function(n, r, i) { var o; return Q.isWindow(n) ? n.document.documentElement["client" + e] : 9 === n.nodeType ? (o = n.documentElement, Math.max(n.body["scroll" + e], o["scroll" + e], n.body["offset" + e], o["offset" + e], o["client" + e])) : i === t ? Q.css(n, r, i, s) : Q.style(n, r, i, s) }, n, a ? i : t, a, null) } }) }), e.jQuery = e.$ = Q, "function" == typeof define && define.amd && define.amd.jQuery && define("jquery", [], function() { return Q }) }(window);
! function(e) { "use strict";

    function t(e) { var t = e.length,
            a = r.type(e); return "function" !== a && !r.isWindow(e) && (!(1 !== e.nodeType || !t) || ("array" === a || 0 === t || "number" == typeof t && t > 0 && t - 1 in e)) } if (!e.jQuery) { var r = function(e, t) { return new r.fn.init(e, t) };
        r.isWindow = function(e) { return e && e === e.window }, r.type = function(e) { return e ? "object" == typeof e || "function" == typeof e ? n[o.call(e)] || "object" : typeof e : e + "" }, r.isArray = Array.isArray || function(e) { return "array" === r.type(e) }, r.isPlainObject = function(e) { var t; if (!e || "object" !== r.type(e) || e.nodeType || r.isWindow(e)) return !1; try { if (e.constructor && !i.call(e, "constructor") && !i.call(e.constructor.prototype, "isPrototypeOf")) return !1 } catch (e) { return !1 } for (t in e); return void 0 === t || i.call(e, t) }, r.each = function(e, r, a) { var n = 0,
                i = e.length,
                o = t(e); if (a) { if (o)
                    for (; n < i && !1 !== r.apply(e[n], a); n++);
                else
                    for (n in e)
                        if (e.hasOwnProperty(n) && !1 === r.apply(e[n], a)) break } else if (o)
                for (; n < i && !1 !== r.call(e[n], n, e[n]); n++);
            else
                for (n in e)
                    if (e.hasOwnProperty(n) && !1 === r.call(e[n], n, e[n])) break; return e }, r.data = function(e, t, n) { if (void 0 === n) { var i = e[r.expando],
                    o = i && a[i]; if (void 0 === t) return o; if (o && t in o) return o[t] } else if (void 0 !== t) { var s = e[r.expando] || (e[r.expando] = ++r.uuid); return a[s] = a[s] || {}, a[s][t] = n, n } }, r.removeData = function(e, t) { var n = e[r.expando],
                i = n && a[n];
            i && (t ? r.each(t, function(e, t) { delete i[t] }) : delete a[n]) }, r.extend = function() { var e, t, a, n, i, o, s = arguments[0] || {},
                l = 1,
                u = arguments.length,
                c = !1; for ("boolean" == typeof s && (c = s, s = arguments[l] || {}, l++), "object" != typeof s && "function" !== r.type(s) && (s = {}), l === u && (s = this, l--); l < u; l++)
                if (i = arguments[l])
                    for (n in i) i.hasOwnProperty(n) && (e = s[n], s !== (a = i[n]) && (c && a && (r.isPlainObject(a) || (t = r.isArray(a))) ? (t ? (t = !1, o = e && r.isArray(e) ? e : []) : o = e && r.isPlainObject(e) ? e : {}, s[n] = r.extend(c, o, a)) : void 0 !== a && (s[n] = a))); return s }, r.queue = function(e, a, n) { if (e) { a = (a || "fx") + "queue"; var i = r.data(e, a); return n ? (!i || r.isArray(n) ? i = r.data(e, a, function(e, r) { var a = r || []; return e && (t(Object(e)) ? function(e, t) { for (var r = +t.length, a = 0, n = e.length; a < r;) e[n++] = t[a++]; if (r !== r)
                            for (; void 0 !== t[a];) e[n++] = t[a++];
                        e.length = n }(a, "string" == typeof e ? [e] : e) : [].push.call(a, e)), a }(n)) : i.push(n), i) : i || [] } }, r.dequeue = function(e, t) { r.each(e.nodeType ? [e] : e, function(e, a) { t = t || "fx"; var n = r.queue(a, t),
                    i = n.shift(); "inprogress" === i && (i = n.shift()), i && ("fx" === t && n.unshift("inprogress"), i.call(a, function() { r.dequeue(a, t) })) }) }, r.fn = r.prototype = { init: function(e) { if (e.nodeType) return this[0] = e, this; throw new Error("Not a DOM node.") }, offset: function() { var t = this[0].getBoundingClientRect ? this[0].getBoundingClientRect() : { top: 0, left: 0 }; return { top: t.top + (e.pageYOffset || document.scrollTop || 0) - (document.clientTop || 0), left: t.left + (e.pageXOffset || document.scrollLeft || 0) - (document.clientLeft || 0) } }, position: function() { var e = this[0],
                    t = function(e) { for (var t = e.offsetParent; t && "html" !== t.nodeName.toLowerCase() && t.style && "static" === t.style.position;) t = t.offsetParent; return t || document }(e),
                    a = this.offset(),
                    n = /^(?:body|html)$/i.test(t.nodeName) ? { top: 0, left: 0 } : r(t).offset(); return a.top -= parseFloat(e.style.marginTop) || 0, a.left -= parseFloat(e.style.marginLeft) || 0, t.style && (n.top += parseFloat(t.style.borderTopWidth) || 0, n.left += parseFloat(t.style.borderLeftWidth) || 0), { top: a.top - n.top, left: a.left - n.left } } }; var a = {};
        r.expando = "velocity" + (new Date).getTime(), r.uuid = 0; for (var n = {}, i = n.hasOwnProperty, o = n.toString, s = "Boolean Number String Function Array Date RegExp Object Error".split(" "), l = 0; l < s.length; l++) n["[object " + s[l] + "]"] = s[l].toLowerCase();
        r.fn.init.prototype = r.fn, e.Velocity = { Utilities: r } } }(window),
function(e) { "use strict"; "object" == typeof module && "object" == typeof module.exports ? module.exports = e() : "function" == typeof define && define.amd ? define(e) : e() }(function() { "use strict"; return function(e, t, r, a) {
        function n(e) { for (var t = -1, r = e ? e.length : 0, a = []; ++t < r;) { var n = e[t];
                n && a.push(n) } return a }

        function i(e) { return b.isWrapped(e) ? e = v.call(e) : b.isNode(e) && (e = [e]), e }

        function o(e) { var t = g.data(e, "velocity"); return null === t ? a : t }

        function s(e, t) { var r = o(e);
            r && r.delayTimer && !r.delayPaused && (r.delayRemaining = r.delay - t + r.delayBegin, r.delayPaused = !0, clearTimeout(r.delayTimer.setTimeout)) }

        function l(e, t) { var r = o(e);
            r && r.delayTimer && r.delayPaused && (r.delayPaused = !1, r.delayTimer.setTimeout = setTimeout(r.delayTimer.next, r.delayRemaining)) }

        function u(e) { return function(t) { return Math.round(t * e) * (1 / e) } }

        function c(e, r, a, n) {
            function i(e, t) { return 1 - 3 * t + 3 * e }

            function o(e, t) { return 3 * t - 6 * e }

            function s(e) { return 3 * e }

            function l(e, t, r) { return ((i(t, r) * e + o(t, r)) * e + s(t)) * e }

            function u(e, t, r) { return 3 * i(t, r) * e * e + 2 * o(t, r) * e + s(t) }

            function c(t, r) { for (var n = 0; n < m; ++n) { var i = u(r, e, a); if (0 === i) return r;
                    r -= (l(r, e, a) - t) / i } return r }

            function p() { for (var t = 0; t < b; ++t) P[t] = l(t * x, e, a) }

            function d(t, r, n) { var i, o, s = 0;
                do {
                    (i = l(o = r + (n - r) / 2, e, a) - t) > 0 ? n = o : r = o } while (Math.abs(i) > y && ++s < v); return o }

            function f(t) { for (var r = 0, n = 1, i = b - 1; n !== i && P[n] <= t; ++n) r += x; var o = r + (t - P[--n]) / (P[n + 1] - P[n]) * x,
                    s = u(o, e, a); return s >= h ? c(t, o) : 0 === s ? o : d(t, r, r + x) }

            function g() { V = !0, e === r && a === n || p() } var m = 4,
                h = .001,
                y = 1e-7,
                v = 10,
                b = 11,
                x = 1 / (b - 1),
                w = "Float32Array" in t; if (4 !== arguments.length) return !1; for (var S = 0; S < 4; ++S)
                if ("number" != typeof arguments[S] || isNaN(arguments[S]) || !isFinite(arguments[S])) return !1;
            e = Math.min(e, 1), a = Math.min(a, 1), e = Math.max(e, 0), a = Math.max(a, 0); var P = w ? new Float32Array(b) : new Array(b),
                V = !1,
                k = function(t) { return V || g(), e === r && a === n ? t : 0 === t ? 0 : 1 === t ? 1 : l(f(t), r, n) };
            k.getControlPoints = function() { return [{ x: e, y: r }, { x: a, y: n }] }; var T = "generateBezier(" + [e, r, a, n] + ")"; return k.toString = function() { return T }, k }

        function p(e, t) { var r = e; return b.isString(e) ? P.Easings[e] || (r = !1) : r = b.isArray(e) && 1 === e.length ? u.apply(null, e) : b.isArray(e) && 2 === e.length ? V.apply(null, e.concat([t])) : !(!b.isArray(e) || 4 !== e.length) && c.apply(null, e), !1 === r && (r = P.Easings[P.defaults.easing] ? P.defaults.easing : S), r }

        function d(e) { if (e) { var t = P.timestamp && !0 !== e ? e : y.now(),
                    r = P.State.calls.length;
                r > 1e4 && (P.State.calls = n(P.State.calls), r = P.State.calls.length); for (var i = 0; i < r; i++)
                    if (P.State.calls[i]) { var s = P.State.calls[i],
                            l = s[0],
                            u = s[2],
                            c = s[3],
                            p = !!c,
                            h = null,
                            v = s[5],
                            x = s[6]; if (c || (c = P.State.calls[i][3] = t - 16), v) { if (!0 !== v.resume) continue;
                            c = s[3] = Math.round(t - x - 16), s[5] = null } x = s[6] = t - c; for (var w = Math.min(x / u.duration, 1), S = 0, V = l.length; S < V; S++) { var T = l[S],
                                F = T.element; if (o(F)) { var A = !1; if (u.display !== a && null !== u.display && "none" !== u.display) { if ("flex" === u.display) { var E = ["-webkit-box", "-moz-box", "-ms-flexbox", "-webkit-flex"];
                                        g.each(E, function(e, t) { k.setPropertyValue(F, "display", t) }) } k.setPropertyValue(F, "display", u.display) } u.visibility !== a && "hidden" !== u.visibility && k.setPropertyValue(F, "visibility", u.visibility); for (var N in T)
                                    if (T.hasOwnProperty(N) && "element" !== N) { var H, O = T[N],
                                            j = b.isString(O.easing) ? P.Easings[O.easing] : O.easing; if (b.isString(O.pattern)) { var q = 1 === w ? function(e, t, r) { var a = O.endValue[t]; return r ? Math.round(a) : a } : function(e, t, r) { var a = O.startValue[t],
                                                    n = O.endValue[t] - a,
                                                    i = a + n * j(w, u, n); return r ? Math.round(i) : i };
                                            H = O.pattern.replace(/{(\d+)(!)?}/g, q) } else if (1 === w) H = O.endValue;
                                        else { var L = O.endValue - O.startValue;
                                            H = O.startValue + L * j(w, u, L) } if (!p && H === O.currentValue) continue; if (O.currentValue = H, "tween" === N) h = H;
                                        else { var z; if (k.Hooks.registered[N]) { z = k.Hooks.getRoot(N); var R = o(F).rootPropertyValueCache[z];
                                                R && (O.rootPropertyValue = R) } var M = k.setPropertyValue(F, N, O.currentValue + (m < 9 && 0 === parseFloat(H) ? "" : O.unitType), O.rootPropertyValue, O.scrollData);
                                            k.Hooks.registered[N] && (k.Normalizations.registered[z] ? o(F).rootPropertyValueCache[z] = k.Normalizations.registered[z]("extract", null, M[1]) : o(F).rootPropertyValueCache[z] = M[1]), "transform" === M[0] && (A = !0) } }
                                u.mobileHA && o(F).transformCache.translate3d === a && (o(F).transformCache.translate3d = "(0px, 0px, 0px)", A = !0), A && k.flushTransformCache(F) } } u.display !== a && "none" !== u.display && (P.State.calls[i][2].display = !1), u.visibility !== a && "hidden" !== u.visibility && (P.State.calls[i][2].visibility = !1), u.progress && u.progress.call(s[1], s[1], w, Math.max(0, c + u.duration - t), c, h), 1 === w && f(i) } } P.State.isTicking && C(d) }

        function f(e, t) { if (!P.State.calls[e]) return !1; for (var r = P.State.calls[e][0], n = P.State.calls[e][1], i = P.State.calls[e][2], s = P.State.calls[e][4], l = !1, u = 0, c = r.length; u < c; u++) { var p = r[u].element;
                t || i.loop || ("none" === i.display && k.setPropertyValue(p, "display", i.display), "hidden" === i.visibility && k.setPropertyValue(p, "visibility", i.visibility)); var d = o(p); if (!0 !== i.loop && (g.queue(p)[1] === a || !/\.velocityQueueEntryFlag/i.test(g.queue(p)[1])) && d) { d.isAnimating = !1, d.rootPropertyValueCache = {}; var f = !1;
                    g.each(k.Lists.transforms3D, function(e, t) { var r = /^scale/.test(t) ? 1 : 0,
                            n = d.transformCache[t];
                        d.transformCache[t] !== a && new RegExp("^\\(" + r + "[^.]").test(n) && (f = !0, delete d.transformCache[t]) }), i.mobileHA && (f = !0, delete d.transformCache.translate3d), f && k.flushTransformCache(p), k.Values.removeClass(p, "velocity-animating") } if (!t && i.complete && !i.loop && u === c - 1) try { i.complete.call(n, n) } catch (e) { setTimeout(function() { throw e }, 1) } s && !0 !== i.loop && s(n), d && !0 === i.loop && !t && (g.each(d.tweensContainer, function(e, t) { if (/^rotate/.test(e) && (parseFloat(t.startValue) - parseFloat(t.endValue)) % 360 == 0) { var r = t.startValue;
                        t.startValue = t.endValue, t.endValue = r } /^backgroundPosition/.test(e) && 100 === parseFloat(t.endValue) && "%" === t.unitType && (t.endValue = 0, t.startValue = 100) }), P(p, "reverse", { loop: !0, delay: i.delay })), !1 !== i.queue && g.dequeue(p, i.queue) } P.State.calls[e] = !1; for (var m = 0, h = P.State.calls.length; m < h; m++)
                if (!1 !== P.State.calls[m]) { l = !0; break }!1 === l && (P.State.isTicking = !1, delete P.State.calls, P.State.calls = []) } var g, m = function() { if (r.documentMode) return r.documentMode; for (var e = 7; e > 4; e--) { var t = r.createElement("div"); if (t.innerHTML = "\x3c!--[if IE " + e + "]><span></span><![endif]--\x3e", t.getElementsByTagName("span").length) return t = null, e } return a }(),
            h = function() { var e = 0; return t.webkitRequestAnimationFrame || t.mozRequestAnimationFrame || function(t) { var r, a = (new Date).getTime(); return r = Math.max(0, 16 - (a - e)), e = a + r, setTimeout(function() { t(a + r) }, r) } }(),
            y = function() { var e = t.performance || {}; if (!Object.prototype.hasOwnProperty.call(e, "now")) { var r = e.timing && e.timing.domComplete ? e.timing.domComplete : (new Date).getTime();
                    e.now = function() { return (new Date).getTime() - r } } return e }(),
            v = function() { var e = Array.prototype.slice; try { e.call(r.documentElement) } catch (t) { e = function() { for (var e = this.length, t = []; --e > 0;) t[e] = this[e]; return t } } return e }(),
            b = { isNumber: function(e) { return "number" == typeof e }, isString: function(e) { return "string" == typeof e }, isArray: Array.isArray || function(e) { return "[object Array]" === Object.prototype.toString.call(e) }, isFunction: function(e) { return "[object Function]" === Object.prototype.toString.call(e) }, isNode: function(e) { return e && e.nodeType }, isWrapped: function(e) { return e && b.isNumber(e.length) && !b.isString(e) && !b.isFunction(e) && !b.isNode(e) && (0 === e.length || b.isNode(e[0])) }, isSVG: function(e) { return t.SVGElement && e instanceof t.SVGElement }, isEmptyObject: function(e) { for (var t in e)
                        if (e.hasOwnProperty(t)) return !1; return !0 } },
            x = !1; if (e.fn && e.fn.jquery ? (g = e, x = !0) : g = t.Velocity.Utilities, m <= 8 && !x) throw new Error("Velocity: IE8 and below require jQuery to be loaded before Velocity."); if (!(m <= 7)) { var w = 400,
                S = "swing",
                P = { State: { isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent), isAndroid: /Android/i.test(navigator.userAgent), isGingerbread: /Android 2\.3\.[3-7]/i.test(navigator.userAgent), isChrome: t.chrome, isFirefox: /Firefox/i.test(navigator.userAgent), prefixElement: r.createElement("div"), prefixMatches: {}, scrollAnchor: null, scrollPropertyLeft: null, scrollPropertyTop: null, isTicking: !1, calls: [], delayedElements: { count: 0 } }, CSS: {}, Utilities: g, Redirects: {}, Easings: {}, Promise: t.Promise, defaults: { queue: "", duration: w, easing: S, begin: a, complete: a, progress: a, display: a, visibility: a, loop: !1, delay: !1, mobileHA: !0, _cacheValues: !0, promiseRejectEmpty: !0 }, init: function(e) { g.data(e, "velocity", { isSVG: b.isSVG(e), isAnimating: !1, computedStyle: null, tweensContainer: null, rootPropertyValueCache: {}, transformCache: {} }) }, hook: null, mock: !1, version: { major: 1, minor: 4, patch: 3 }, debug: !1, timestamp: !0, pauseAll: function(e) { var t = (new Date).getTime();
                        g.each(P.State.calls, function(t, r) { if (r) { if (e !== a && (r[2].queue !== e || !1 === r[2].queue)) return !0;
                                r[5] = { resume: !1 } } }), g.each(P.State.delayedElements, function(e, r) { r && s(r, t) }) }, resumeAll: function(e) {
                        (new Date).getTime();
                        g.each(P.State.calls, function(t, r) { if (r) { if (e !== a && (r[2].queue !== e || !1 === r[2].queue)) return !0;
                                r[5] && (r[5].resume = !0) } }), g.each(P.State.delayedElements, function(e, t) { t && l(t) }) } };
            t.pageYOffset !== a ? (P.State.scrollAnchor = t, P.State.scrollPropertyLeft = "pageXOffset", P.State.scrollPropertyTop = "pageYOffset") : (P.State.scrollAnchor = r.documentElement || r.body.parentNode || r.body, P.State.scrollPropertyLeft = "scrollLeft", P.State.scrollPropertyTop = "scrollTop"); var V = function() {
                function e(e) { return -e.tension * e.x - e.friction * e.v }

                function t(t, r, a) { var n = { x: t.x + a.dx * r, v: t.v + a.dv * r, tension: t.tension, friction: t.friction }; return { dx: n.v, dv: e(n) } }

                function r(r, a) { var n = { dx: r.v, dv: e(r) },
                        i = t(r, .5 * a, n),
                        o = t(r, .5 * a, i),
                        s = t(r, a, o),
                        l = 1 / 6 * (n.dx + 2 * (i.dx + o.dx) + s.dx),
                        u = 1 / 6 * (n.dv + 2 * (i.dv + o.dv) + s.dv); return r.x = r.x + l * a, r.v = r.v + u * a, r } return function e(t, a, n) { var i, o, s, l = { x: -1, v: 0, tension: null, friction: null },
                        u = [0],
                        c = 0; for (t = parseFloat(t) || 500, a = parseFloat(a) || 20, n = n || null, l.tension = t, l.friction = a, o = (i = null !== n) ? (c = e(t, a)) / n * .016 : .016;;)
                        if (s = r(s || l, o), u.push(1 + s.x), c += 16, !(Math.abs(s.x) > 1e-4 && Math.abs(s.v) > 1e-4)) break; return i ? function(e) { return u[e * (u.length - 1) | 0] } : c } }();
            P.Easings = { linear: function(e) { return e }, swing: function(e) { return .5 - Math.cos(e * Math.PI) / 2 }, spring: function(e) { return 1 - Math.cos(4.5 * e * Math.PI) * Math.exp(6 * -e) } }, g.each([
                ["ease", [.25, .1, .25, 1]],
                ["ease-in", [.42, 0, 1, 1]],
                ["ease-out", [0, 0, .58, 1]],
                ["ease-in-out", [.42, 0, .58, 1]],
                ["easeInSine", [.47, 0, .745, .715]],
                ["easeOutSine", [.39, .575, .565, 1]],
                ["easeInOutSine", [.445, .05, .55, .95]],
                ["easeInQuad", [.55, .085, .68, .53]],
                ["easeOutQuad", [.25, .46, .45, .94]],
                ["easeInOutQuad", [.455, .03, .515, .955]],
                ["easeInCubic", [.55, .055, .675, .19]],
                ["easeOutCubic", [.215, .61, .355, 1]],
                ["easeInOutCubic", [.645, .045, .355, 1]],
                ["easeInQuart", [.895, .03, .685, .22]],
                ["easeOutQuart", [.165, .84, .44, 1]],
                ["easeInOutQuart", [.77, 0, .175, 1]],
                ["easeInQuint", [.755, .05, .855, .06]],
                ["easeOutQuint", [.23, 1, .32, 1]],
                ["easeInOutQuint", [.86, 0, .07, 1]],
                ["easeInExpo", [.95, .05, .795, .035]],
                ["easeOutExpo", [.19, 1, .22, 1]],
                ["easeInOutExpo", [1, 0, 0, 1]],
                ["easeInCirc", [.6, .04, .98, .335]],
                ["easeOutCirc", [.075, .82, .165, 1]],
                ["easeInOutCirc", [.785, .135, .15, .86]]
            ], function(e, t) { P.Easings[t[0]] = c.apply(null, t[1]) }); var k = P.CSS = { RegEx: { isHex: /^#([A-f\d]{3}){1,2}$/i, valueUnwrap: /^[A-z]+\((.*)\)$/i, wrappedValueAlreadyExtracted: /[0-9.]+ [0-9.]+ [0-9.]+( [0-9.]+)?/, valueSplit: /([A-z]+\(.+\))|(([A-z0-9#-.]+?)(?=\s|$))/gi }, Lists: { colors: ["fill", "stroke", "stopColor", "color", "backgroundColor", "borderColor", "borderTopColor", "borderRightColor", "borderBottomColor", "borderLeftColor", "outlineColor"], transformsBase: ["translateX", "translateY", "scale", "scaleX", "scaleY", "skewX", "skewY", "rotateZ"], transforms3D: ["transformPerspective", "translateZ", "scaleZ", "rotateX", "rotateY"], units: ["%", "em", "ex", "ch", "rem", "vw", "vh", "vmin", "vmax", "cm", "mm", "Q", "in", "pc", "pt", "px", "deg", "grad", "rad", "turn", "s", "ms"], colorNames: { aliceblue: "240,248,255", antiquewhite: "250,235,215", aquamarine: "127,255,212", aqua: "0,255,255", azure: "240,255,255", beige: "245,245,220", bisque: "255,228,196", black: "0,0,0", blanchedalmond: "255,235,205", blueviolet: "138,43,226", blue: "0,0,255", brown: "165,42,42", burlywood: "222,184,135", cadetblue: "95,158,160", chartreuse: "127,255,0", chocolate: "210,105,30", coral: "255,127,80", cornflowerblue: "100,149,237", cornsilk: "255,248,220", crimson: "220,20,60", cyan: "0,255,255", darkblue: "0,0,139", darkcyan: "0,139,139", darkgoldenrod: "184,134,11", darkgray: "169,169,169", darkgrey: "169,169,169", darkgreen: "0,100,0", darkkhaki: "189,183,107", darkmagenta: "139,0,139", darkolivegreen: "85,107,47", darkorange: "255,140,0", darkorchid: "153,50,204", darkred: "139,0,0", darksalmon: "233,150,122", darkseagreen: "143,188,143", darkslateblue: "72,61,139", darkslategray: "47,79,79", darkturquoise: "0,206,209", darkviolet: "148,0,211", deeppink: "255,20,147", deepskyblue: "0,191,255", dimgray: "105,105,105", dimgrey: "105,105,105", dodgerblue: "30,144,255", firebrick: "178,34,34", floralwhite: "255,250,240", forestgreen: "34,139,34", fuchsia: "255,0,255", gainsboro: "220,220,220", ghostwhite: "248,248,255", gold: "255,215,0", goldenrod: "218,165,32", gray: "128,128,128", grey: "128,128,128", greenyellow: "173,255,47", green: "0,128,0", honeydew: "240,255,240", hotpink: "255,105,180", indianred: "205,92,92", indigo: "75,0,130", ivory: "255,255,240", khaki: "240,230,140", lavenderblush: "255,240,245", lavender: "230,230,250", lawngreen: "124,252,0", lemonchiffon: "255,250,205", lightblue: "173,216,230", lightcoral: "240,128,128", lightcyan: "224,255,255", lightgoldenrodyellow: "250,250,210", lightgray: "211,211,211", lightgrey: "211,211,211", lightgreen: "144,238,144", lightpink: "255,182,193", lightsalmon: "255,160,122", lightseagreen: "32,178,170", lightskyblue: "135,206,250", lightslategray: "119,136,153", lightsteelblue: "176,196,222", lightyellow: "255,255,224", limegreen: "50,205,50", lime: "0,255,0", linen: "250,240,230", magenta: "255,0,255", maroon: "128,0,0", mediumaquamarine: "102,205,170", mediumblue: "0,0,205", mediumorchid: "186,85,211", mediumpurple: "147,112,219", mediumseagreen: "60,179,113", mediumslateblue: "123,104,238", mediumspringgreen: "0,250,154", mediumturquoise: "72,209,204", mediumvioletred: "199,21,133", midnightblue: "25,25,112", mintcream: "245,255,250", mistyrose: "255,228,225", moccasin: "255,228,181", navajowhite: "255,222,173", navy: "0,0,128", oldlace: "253,245,230", olivedrab: "107,142,35", olive: "128,128,0", orangered: "255,69,0", orange: "255,165,0", orchid: "218,112,214", palegoldenrod: "238,232,170", palegreen: "152,251,152", paleturquoise: "175,238,238", palevioletred: "219,112,147", papayawhip: "255,239,213", peachpuff: "255,218,185", peru: "205,133,63", pink: "255,192,203", plum: "221,160,221", powderblue: "176,224,230", purple: "128,0,128", red: "255,0,0", rosybrown: "188,143,143", royalblue: "65,105,225", saddlebrown: "139,69,19", salmon: "250,128,114", sandybrown: "244,164,96", seagreen: "46,139,87", seashell: "255,245,238", sienna: "160,82,45", silver: "192,192,192", skyblue: "135,206,235", slateblue: "106,90,205", slategray: "112,128,144", snow: "255,250,250", springgreen: "0,255,127", steelblue: "70,130,180", tan: "210,180,140", teal: "0,128,128", thistle: "216,191,216", tomato: "255,99,71", turquoise: "64,224,208", violet: "238,130,238", wheat: "245,222,179", whitesmoke: "245,245,245", white: "255,255,255", yellowgreen: "154,205,50", yellow: "255,255,0" } }, Hooks: { templates: { textShadow: ["Color X Y Blur", "black 0px 0px 0px"], boxShadow: ["Color X Y Blur Spread", "black 0px 0px 0px 0px"], clip: ["Top Right Bottom Left", "0px 0px 0px 0px"], backgroundPosition: ["X Y", "0% 0%"], transformOrigin: ["X Y Z", "50% 50% 0px"], perspectiveOrigin: ["X Y", "50% 50%"] }, registered: {}, register: function() { for (var e = 0; e < k.Lists.colors.length; e++) { var t = "color" === k.Lists.colors[e] ? "0 0 0 1" : "255 255 255 1";
                            k.Hooks.templates[k.Lists.colors[e]] = ["Red Green Blue Alpha", t] } var r, a, n; if (m)
                            for (r in k.Hooks.templates)
                                if (k.Hooks.templates.hasOwnProperty(r)) { n = (a = k.Hooks.templates[r])[0].split(" "); var i = a[1].match(k.RegEx.valueSplit); "Color" === n[0] && (n.push(n.shift()), i.push(i.shift()), k.Hooks.templates[r] = [n.join(" "), i.join(" ")]) }
                        for (r in k.Hooks.templates)
                            if (k.Hooks.templates.hasOwnProperty(r)) { n = (a = k.Hooks.templates[r])[0].split(" "); for (var o in n)
                                    if (n.hasOwnProperty(o)) { var s = r + n[o],
                                            l = o;
                                        k.Hooks.registered[s] = [r, l] } } }, getRoot: function(e) { var t = k.Hooks.registered[e]; return t ? t[0] : e }, getUnit: function(e, t) { var r = (e.substr(t || 0, 5).match(/^[a-z%]+/) || [])[0] || ""; return r && k.Lists.units.indexOf(r) >= 0 ? r : "" }, fixColors: function(e) { return e.replace(/(rgba?\(\s*)?(\b[a-z]+\b)/g, function(e, t, r) { return k.Lists.colorNames.hasOwnProperty(r) ? (t || "rgba(") + k.Lists.colorNames[r] + (t ? "" : ",1)") : t + r }) }, cleanRootPropertyValue: function(e, t) { return k.RegEx.valueUnwrap.test(t) && (t = t.match(k.RegEx.valueUnwrap)[1]), k.Values.isCSSNullValue(t) && (t = k.Hooks.templates[e][1]), t }, extractValue: function(e, t) { var r = k.Hooks.registered[e]; if (r) { var a = r[0],
                                n = r[1]; return (t = k.Hooks.cleanRootPropertyValue(a, t)).toString().match(k.RegEx.valueSplit)[n] } return t }, injectValue: function(e, t, r) { var a = k.Hooks.registered[e]; if (a) { var n, i = a[0],
                                o = a[1]; return r = k.Hooks.cleanRootPropertyValue(i, r), n = r.toString().match(k.RegEx.valueSplit), n[o] = t, n.join(" ") } return r } }, Normalizations: { registered: { clip: function(e, t, r) { switch (e) {
                                case "name":
                                    return "clip";
                                case "extract":
                                    var a; return a = k.RegEx.wrappedValueAlreadyExtracted.test(r) ? r : (a = r.toString().match(k.RegEx.valueUnwrap)) ? a[1].replace(/,(\s+)?/g, " ") : r;
                                case "inject":
                                    return "rect(" + r + ")" } }, blur: function(e, t, r) { switch (e) {
                                case "name":
                                    return P.State.isFirefox ? "filter" : "-webkit-filter";
                                case "extract":
                                    var a = parseFloat(r); if (!a && 0 !== a) { var n = r.toString().match(/blur\(([0-9]+[A-z]+)\)/i);
                                        a = n ? n[1] : 0 } return a;
                                case "inject":
                                    return parseFloat(r) ? "blur(" + r + ")" : "none" } }, opacity: function(e, t, r) { if (m <= 8) switch (e) {
                                case "name":
                                    return "filter";
                                case "extract":
                                    var a = r.toString().match(/alpha\(opacity=(.*)\)/i); return r = a ? a[1] / 100 : 1;
                                case "inject":
                                    return t.style.zoom = 1, parseFloat(r) >= 1 ? "" : "alpha(opacity=" + parseInt(100 * parseFloat(r), 10) + ")" } else switch (e) {
                                case "name":
                                    return "opacity";
                                case "extract":
                                case "inject":
                                    return r } } }, register: function() {
                        function e(e, t, r) { if ("border-box" === k.getPropertyValue(t, "boxSizing").toString().toLowerCase() === (r || !1)) { var a, n, i = 0,
                                    o = "width" === e ? ["Left", "Right"] : ["Top", "Bottom"],
                                    s = ["padding" + o[0], "padding" + o[1], "border" + o[0] + "Width", "border" + o[1] + "Width"]; for (a = 0; a < s.length; a++) n = parseFloat(k.getPropertyValue(t, s[a])), isNaN(n) || (i += n); return r ? -i : i } return 0 }

                        function t(t, r) { return function(a, n, i) { switch (a) {
                                    case "name":
                                        return t;
                                    case "extract":
                                        return parseFloat(i) + e(t, n, r);
                                    case "inject":
                                        return parseFloat(i) - e(t, n, r) + "px" } } } m && !(m > 9) || P.State.isGingerbread || (k.Lists.transformsBase = k.Lists.transformsBase.concat(k.Lists.transforms3D)); for (var r = 0; r < k.Lists.transformsBase.length; r++) ! function() { var e = k.Lists.transformsBase[r];
                            k.Normalizations.registered[e] = function(t, r, n) { switch (t) {
                                    case "name":
                                        return "transform";
                                    case "extract":
                                        return o(r) === a || o(r).transformCache[e] === a ? /^scale/i.test(e) ? 1 : 0 : o(r).transformCache[e].replace(/[()]/g, "");
                                    case "inject":
                                        var i = !1; switch (e.substr(0, e.length - 1)) {
                                            case "translate":
                                                i = !/(%|px|em|rem|vw|vh|\d)$/i.test(n); break;
                                            case "scal":
                                            case "scale":
                                                P.State.isAndroid && o(r).transformCache[e], i = !/(\d)$/i.test(n); break;
                                            case "skew":
                                            case "rotate":
                                                i = !/(deg|\d)$/i.test(n) } return i || (o(r).transformCache[e] = "(" + n + ")"), o(r).transformCache[e] } } }(); for (var n = 0; n < k.Lists.colors.length; n++) ! function() { var e = k.Lists.colors[n];
                            k.Normalizations.registered[e] = function(t, r, n) { switch (t) {
                                    case "name":
                                        return e;
                                    case "extract":
                                        var i; if (k.RegEx.wrappedValueAlreadyExtracted.test(n)) i = n;
                                        else { var o, s = { black: "rgb(0, 0, 0)", blue: "rgb(0, 0, 255)", gray: "rgb(128, 128, 128)", green: "rgb(0, 128, 0)", red: "rgb(255, 0, 0)", white: "rgb(255, 255, 255)" }; /^[A-z]+$/i.test(n) ? o = s[n] !== a ? s[n] : s.black : k.RegEx.isHex.test(n) ? o = "rgb(" + k.Values.hexToRgb(n).join(" ") + ")" : /^rgba?\(/i.test(n) || (o = s.black), i = (o || n).toString().match(k.RegEx.valueUnwrap)[1].replace(/,(\s+)?/g, " ") } return (!m || m > 8) && 3 === i.split(" ").length && (i += " 1"), i;
                                    case "inject":
                                        return /^rgb/.test(n) ? n : (m <= 8 ? 4 === n.split(" ").length && (n = n.split(/\s+/).slice(0, 3).join(" ")) : 3 === n.split(" ").length && (n += " 1"), (m <= 8 ? "rgb" : "rgba") + "(" + n.replace(/\s+/g, ",").replace(/\.(\d)+(?=,)/g, "") + ")") } } }();
                        k.Normalizations.registered.innerWidth = t("width", !0), k.Normalizations.registered.innerHeight = t("height", !0), k.Normalizations.registered.outerWidth = t("width"), k.Normalizations.registered.outerHeight = t("height") } }, Names: { camelCase: function(e) { return e.replace(/-(\w)/g, function(e, t) { return t.toUpperCase() }) }, SVGAttribute: function(e) { var t = "width|height|x|y|cx|cy|r|rx|ry|x1|x2|y1|y2"; return (m || P.State.isAndroid && !P.State.isChrome) && (t += "|transform"), new RegExp("^(" + t + ")$", "i").test(e) }, prefixCheck: function(e) { if (P.State.prefixMatches[e]) return [P.State.prefixMatches[e], !0]; for (var t = ["", "Webkit", "Moz", "ms", "O"], r = 0, a = t.length; r < a; r++) { var n; if (n = 0 === r ? e : t[r] + e.replace(/^\w/, function(e) { return e.toUpperCase() }), b.isString(P.State.prefixElement.style[n])) return P.State.prefixMatches[e] = n, [n, !0] } return [e, !1] } }, Values: { hexToRgb: function(e) { var t, r = /^#?([a-f\d])([a-f\d])([a-f\d])$/i,
                            a = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i; return e = e.replace(r, function(e, t, r, a) { return t + t + r + r + a + a }), (t = a.exec(e)) ? [parseInt(t[1], 16), parseInt(t[2], 16), parseInt(t[3], 16)] : [0, 0, 0] }, isCSSNullValue: function(e) { return !e || /^(none|auto|transparent|(rgba\(0, ?0, ?0, ?0\)))$/i.test(e) }, getUnitType: function(e) { return /^(rotate|skew)/i.test(e) ? "deg" : /(^(scale|scaleX|scaleY|scaleZ|alpha|flexGrow|flexHeight|zIndex|fontWeight)$)|((opacity|red|green|blue|alpha)$)/i.test(e) ? "" : "px" }, getDisplayType: function(e) { var t = e && e.tagName.toString().toLowerCase(); return /^(b|big|i|small|tt|abbr|acronym|cite|code|dfn|em|kbd|strong|samp|var|a|bdo|br|img|map|object|q|script|span|sub|sup|button|input|label|select|textarea)$/i.test(t) ? "inline" : /^(li)$/i.test(t) ? "list-item" : /^(tr)$/i.test(t) ? "table-row" : /^(table)$/i.test(t) ? "table" : /^(tbody)$/i.test(t) ? "table-row-group" : "block" }, addClass: function(e, t) { if (e)
                            if (e.classList) e.classList.add(t);
                            else if (b.isString(e.className)) e.className += (e.className.length ? " " : "") + t;
                        else { var r = e.getAttribute(m <= 7 ? "className" : "class") || "";
                            e.setAttribute("class", r + (r ? " " : "") + t) } }, removeClass: function(e, t) { if (e)
                            if (e.classList) e.classList.remove(t);
                            else if (b.isString(e.className)) e.className = e.className.toString().replace(new RegExp("(^|\\s)" + t.split(" ").join("|") + "(\\s|$)", "gi"), " ");
                        else { var r = e.getAttribute(m <= 7 ? "className" : "class") || "";
                            e.setAttribute("class", r.replace(new RegExp("(^|s)" + t.split(" ").join("|") + "(s|$)", "gi"), " ")) } } }, getPropertyValue: function(e, r, n, i) {
                    function s(e, r) { var n = 0; if (m <= 8) n = g.css(e, r);
                        else { var l = !1; /^(width|height)$/.test(r) && 0 === k.getPropertyValue(e, "display") && (l = !0, k.setPropertyValue(e, "display", k.Values.getDisplayType(e))); var u = function() { l && k.setPropertyValue(e, "display", "none") }; if (!i) { if ("height" === r && "border-box" !== k.getPropertyValue(e, "boxSizing").toString().toLowerCase()) { var c = e.offsetHeight - (parseFloat(k.getPropertyValue(e, "borderTopWidth")) || 0) - (parseFloat(k.getPropertyValue(e, "borderBottomWidth")) || 0) - (parseFloat(k.getPropertyValue(e, "paddingTop")) || 0) - (parseFloat(k.getPropertyValue(e, "paddingBottom")) || 0); return u(), c } if ("width" === r && "border-box" !== k.getPropertyValue(e, "boxSizing").toString().toLowerCase()) { var p = e.offsetWidth - (parseFloat(k.getPropertyValue(e, "borderLeftWidth")) || 0) - (parseFloat(k.getPropertyValue(e, "borderRightWidth")) || 0) - (parseFloat(k.getPropertyValue(e, "paddingLeft")) || 0) - (parseFloat(k.getPropertyValue(e, "paddingRight")) || 0); return u(), p } } var d;
                            d = o(e) === a ? t.getComputedStyle(e, null) : o(e).computedStyle ? o(e).computedStyle : o(e).computedStyle = t.getComputedStyle(e, null), "borderColor" === r && (r = "borderTopColor"), "" !== (n = 9 === m && "filter" === r ? d.getPropertyValue(r) : d[r]) && null !== n || (n = e.style[r]), u() } if ("auto" === n && /^(top|right|bottom|left)$/i.test(r)) { var f = s(e, "position");
                            ("fixed" === f || "absolute" === f && /top|left/i.test(r)) && (n = g(e).position()[r] + "px") } return n } var l; if (k.Hooks.registered[r]) { var u = r,
                            c = k.Hooks.getRoot(u);
                        n === a && (n = k.getPropertyValue(e, k.Names.prefixCheck(c)[0])), k.Normalizations.registered[c] && (n = k.Normalizations.registered[c]("extract", e, n)), l = k.Hooks.extractValue(u, n) } else if (k.Normalizations.registered[r]) { var p, d; "transform" !== (p = k.Normalizations.registered[r]("name", e)) && (d = s(e, k.Names.prefixCheck(p)[0]), k.Values.isCSSNullValue(d) && k.Hooks.templates[r] && (d = k.Hooks.templates[r][1])), l = k.Normalizations.registered[r]("extract", e, d) } if (!/^[\d-]/.test(l)) { var f = o(e); if (f && f.isSVG && k.Names.SVGAttribute(r))
                            if (/^(height|width)$/i.test(r)) try { l = e.getBBox()[r] } catch (e) { l = 0 } else l = e.getAttribute(r);
                            else l = s(e, k.Names.prefixCheck(r)[0]) } return k.Values.isCSSNullValue(l) && (l = 0), P.debug >= 2 && console.log("Get " + r + ": " + l), l }, setPropertyValue: function(e, r, a, n, i) { var s = r; if ("scroll" === r) i.container ? i.container["scroll" + i.direction] = a : "Left" === i.direction ? t.scrollTo(a, i.alternateValue) : t.scrollTo(i.alternateValue, a);
                    else if (k.Normalizations.registered[r] && "transform" === k.Normalizations.registered[r]("name", e)) k.Normalizations.registered[r]("inject", e, a), s = "transform", a = o(e).transformCache[r];
                    else { if (k.Hooks.registered[r]) { var l = r,
                                u = k.Hooks.getRoot(r);
                            n = n || k.getPropertyValue(e, u), a = k.Hooks.injectValue(l, a, n), r = u } if (k.Normalizations.registered[r] && (a = k.Normalizations.registered[r]("inject", e, a), r = k.Normalizations.registered[r]("name", e)), s = k.Names.prefixCheck(r)[0], m <= 8) try { e.style[s] = a } catch (e) { P.debug && console.log("Browser does not support [" + a + "] for [" + s + "]") } else { var c = o(e);
                            c && c.isSVG && k.Names.SVGAttribute(r) ? e.setAttribute(r, a) : e.style[s] = a } P.debug >= 2 && console.log("Set " + r + " (" + s + "): " + a) } return [s, a] }, flushTransformCache: function(e) { var t = "",
                        r = o(e); if ((m || P.State.isAndroid && !P.State.isChrome) && r && r.isSVG) { var a = function(t) { return parseFloat(k.getPropertyValue(e, t)) },
                            n = { translate: [a("translateX"), a("translateY")], skewX: [a("skewX")], skewY: [a("skewY")], scale: 1 !== a("scale") ? [a("scale"), a("scale")] : [a("scaleX"), a("scaleY")], rotate: [a("rotateZ"), 0, 0] };
                        g.each(o(e).transformCache, function(e) { /^translate/i.test(e) ? e = "translate" : /^scale/i.test(e) ? e = "scale" : /^rotate/i.test(e) && (e = "rotate"), n[e] && (t += e + "(" + n[e].join(" ") + ") ", delete n[e]) }) } else { var i, s;
                        g.each(o(e).transformCache, function(r) { if (i = o(e).transformCache[r], "transformPerspective" === r) return s = i, !0;
                            9 === m && "rotateZ" === r && (r = "rotate"), t += r + i + " " }), s && (t = "perspective" + s + " " + t) } k.setPropertyValue(e, "transform", t) } };
            k.Hooks.register(), k.Normalizations.register(), P.hook = function(e, t, r) { var n; return e = i(e), g.each(e, function(e, i) { if (o(i) === a && P.init(i), r === a) n === a && (n = k.getPropertyValue(i, t));
                    else { var s = k.setPropertyValue(i, t, r); "transform" === s[0] && P.CSS.flushTransformCache(i), n = s } }), n }; var T = function() {
                function e() { return c ? V.promise || null : m }

                function n(e, n) {
                    function i(i) { var c, f; if (l.begin && 0 === F) try { l.begin.call(y, y) } catch (e) { setTimeout(function() { throw e }, 1) }
                        if ("scroll" === E) { var m, h, w, S = /^x$/i.test(l.axis) ? "Left" : "Top",
                                T = parseFloat(l.offset) || 0;
                            l.container ? b.isWrapped(l.container) || b.isNode(l.container) ? (l.container = l.container[0] || l.container, w = (m = l.container["scroll" + S]) + g(e).position()[S.toLowerCase()] + T) : l.container = null : (m = P.State.scrollAnchor[P.State["scrollProperty" + S]], h = P.State.scrollAnchor[P.State["scrollProperty" + ("Left" === S ? "Top" : "Left")]], w = g(e).offset()[S.toLowerCase()] + T), u = { scroll: { rootPropertyValue: !1, startValue: m, currentValue: m, endValue: w, unitType: "", easing: l.easing, scrollData: { container: l.container, direction: S, alternateValue: h } }, element: e }, P.debug && console.log("tweensContainer (scroll): ", u.scroll, e) } else if ("reverse" === E) { if (!(c = o(e))) return; if (!c.tweensContainer) return void g.dequeue(e, l.queue); "none" === c.opts.display && (c.opts.display = "auto"), "hidden" === c.opts.visibility && (c.opts.visibility = "visible"), c.opts.loop = !1, c.opts.begin = null, c.opts.complete = null, x.easing || delete l.easing, x.duration || delete l.duration, l = g.extend({}, c.opts, l), f = g.extend(!0, {}, c ? c.tweensContainer : null); for (var A in f)
                                if (f.hasOwnProperty(A) && "element" !== A) { var N = f[A].startValue;
                                    f[A].startValue = f[A].currentValue = f[A].endValue, f[A].endValue = N, b.isEmptyObject(x) || (f[A].easing = l.easing), P.debug && console.log("reverse tweensContainer (" + A + "): " + JSON.stringify(f[A]), e) }
                            u = f } else if ("start" === E) {
                            (c = o(e)) && c.tweensContainer && !0 === c.isAnimating && (f = c.tweensContainer); var H = function(n, i) { var o, p = k.Hooks.getRoot(n),
                                    d = !1,
                                    m = i[0],
                                    h = i[1],
                                    y = i[2]; if (c && c.isSVG || "tween" === p || !1 !== k.Names.prefixCheck(p)[1] || k.Normalizations.registered[p] !== a) {
                                    (l.display !== a && null !== l.display && "none" !== l.display || l.visibility !== a && "hidden" !== l.visibility) && /opacity|filter/.test(n) && !y && 0 !== m && (y = 0), l._cacheValues && f && f[n] ? (y === a && (y = f[n].endValue + f[n].unitType), d = c.rootPropertyValueCache[p]) : k.Hooks.registered[n] ? y === a ? (d = k.getPropertyValue(e, p), y = k.getPropertyValue(e, n, d)) : d = k.Hooks.templates[p][1] : y === a && (y = k.getPropertyValue(e, n)); var v, x, w, S = !1,
                                        V = function(e, t) { var r, a; return a = (t || "0").toString().toLowerCase().replace(/[%A-z]+$/, function(e) { return r = e, "" }), r || (r = k.Values.getUnitType(e)), [a, r] }; if (y !== m && b.isString(y) && b.isString(m)) { o = ""; var T = 0,
                                            C = 0,
                                            F = [],
                                            A = [],
                                            E = 0,
                                            N = 0,
                                            H = 0; for (y = k.Hooks.fixColors(y), m = k.Hooks.fixColors(m); T < y.length && C < m.length;) { var O = y[T],
                                                j = m[C]; if (/[\d\.-]/.test(O) && /[\d\.-]/.test(j)) { for (var q = O, z = j, R = ".", M = "."; ++T < y.length;) { if ((O = y[T]) === R) R = "..";
                                                    else if (!/\d/.test(O)) break;
                                                    q += O } for (; ++C < m.length;) { if ((j = m[C]) === M) M = "..";
                                                    else if (!/\d/.test(j)) break;
                                                    z += j } var $ = k.Hooks.getUnit(y, T),
                                                    B = k.Hooks.getUnit(m, C); if (T += $.length, C += B.length, $ === B) q === z ? o += q + $ : (o += "{" + F.length + (N ? "!" : "") + "}" + $, F.push(parseFloat(q)), A.push(parseFloat(z)));
                                                else { var W = parseFloat(q),
                                                        I = parseFloat(z);
                                                    o += (E < 5 ? "calc" : "") + "(" + (W ? "{" + F.length + (N ? "!" : "") + "}" : "0") + $ + " + " + (I ? "{" + (F.length + (W ? 1 : 0)) + (N ? "!" : "") + "}" : "0") + B + ")", W && (F.push(W), A.push(0)), I && (F.push(0), A.push(I)) } } else { if (O !== j) { E = 0; break } o += O, T++, C++, 0 === E && "c" === O || 1 === E && "a" === O || 2 === E && "l" === O || 3 === E && "c" === O || E >= 4 && "(" === O ? E++ : (E && E < 5 || E >= 4 && ")" === O && --E < 5) && (E = 0), 0 === N && "r" === O || 1 === N && "g" === O || 2 === N && "b" === O || 3 === N && "a" === O || N >= 3 && "(" === O ? (3 === N && "a" === O && (H = 1), N++) : H && "," === O ? ++H > 3 && (N = H = 0) : (H && N < (H ? 5 : 4) || N >= (H ? 4 : 3) && ")" === O && --N < (H ? 5 : 4)) && (N = H = 0) } } T === y.length && C === m.length || (P.debug && console.error('Trying to pattern match mis-matched strings ["' + m + '", "' + y + '"]'), o = a), o && (F.length ? (P.debug && console.log('Pattern found "' + o + '" -> ', F, A, "[" + y + "," + m + "]"), y = F, m = A, x = w = "") : o = a) } o || (y = (v = V(n, y))[0], w = v[1], m = (v = V(n, m))[0].replace(/^([+-\/*])=/, function(e, t) { return S = t, "" }), x = v[1], y = parseFloat(y) || 0, m = parseFloat(m) || 0, "%" === x && (/^(fontSize|lineHeight)$/.test(n) ? (m /= 100, x = "em") : /^scale/.test(n) ? (m /= 100, x = "") : /(Red|Green|Blue)$/i.test(n) && (m = m / 100 * 255, x = ""))); if (/[\/*]/.test(S)) x = w;
                                    else if (w !== x && 0 !== y)
                                        if (0 === m) x = w;
                                        else { s = s || function() { var a = { myParent: e.parentNode || r.body, position: k.getPropertyValue(e, "position"), fontSize: k.getPropertyValue(e, "fontSize") },
                                                    n = a.position === L.lastPosition && a.myParent === L.lastParent,
                                                    i = a.fontSize === L.lastFontSize;
                                                L.lastParent = a.myParent, L.lastPosition = a.position, L.lastFontSize = a.fontSize; var o = {}; if (i && n) o.emToPx = L.lastEmToPx, o.percentToPxWidth = L.lastPercentToPxWidth, o.percentToPxHeight = L.lastPercentToPxHeight;
                                                else { var s = c && c.isSVG ? r.createElementNS("http://www.w3.org/2000/svg", "rect") : r.createElement("div");
                                                    P.init(s), a.myParent.appendChild(s), g.each(["overflow", "overflowX", "overflowY"], function(e, t) { P.CSS.setPropertyValue(s, t, "hidden") }), P.CSS.setPropertyValue(s, "position", a.position), P.CSS.setPropertyValue(s, "fontSize", a.fontSize), P.CSS.setPropertyValue(s, "boxSizing", "content-box"), g.each(["minWidth", "maxWidth", "width", "minHeight", "maxHeight", "height"], function(e, t) { P.CSS.setPropertyValue(s, t, "100%") }), P.CSS.setPropertyValue(s, "paddingLeft", "100em"), o.percentToPxWidth = L.lastPercentToPxWidth = (parseFloat(k.getPropertyValue(s, "width", null, !0)) || 1) / 100, o.percentToPxHeight = L.lastPercentToPxHeight = (parseFloat(k.getPropertyValue(s, "height", null, !0)) || 1) / 100, o.emToPx = L.lastEmToPx = (parseFloat(k.getPropertyValue(s, "paddingLeft")) || 1) / 100, a.myParent.removeChild(s) } return null === L.remToPx && (L.remToPx = parseFloat(k.getPropertyValue(r.body, "fontSize")) || 16), null === L.vwToPx && (L.vwToPx = parseFloat(t.innerWidth) / 100, L.vhToPx = parseFloat(t.innerHeight) / 100), o.remToPx = L.remToPx, o.vwToPx = L.vwToPx, o.vhToPx = L.vhToPx, P.debug >= 1 && console.log("Unit ratios: " + JSON.stringify(o), e), o }(); var D = /margin|padding|left|right|width|text|word|letter/i.test(n) || /X$/.test(n) || "x" === n ? "x" : "y"; switch (w) {
                                                case "%":
                                                    y *= "x" === D ? s.percentToPxWidth : s.percentToPxHeight; break;
                                                case "px":
                                                    break;
                                                default:
                                                    y *= s[w + "ToPx"] } switch (x) {
                                                case "%":
                                                    y *= 1 / ("x" === D ? s.percentToPxWidth : s.percentToPxHeight); break;
                                                case "px":
                                                    break;
                                                default:
                                                    y *= 1 / s[x + "ToPx"] } }
                                    switch (S) {
                                        case "+":
                                            m = y + m; break;
                                        case "-":
                                            m = y - m; break;
                                        case "*":
                                            m *= y; break;
                                        case "/":
                                            m = y / m } u[n] = { rootPropertyValue: d, startValue: y, currentValue: y, endValue: m, unitType: x, easing: h }, o && (u[n].pattern = o), P.debug && console.log("tweensContainer (" + n + "): " + JSON.stringify(u[n]), e) } else P.debug && console.log("Skipping [" + p + "] due to a lack of browser support.") }; for (var O in v)
                                if (v.hasOwnProperty(O)) { var j = k.Names.camelCase(O),
                                        q = function(t, r) { var a, i, o; return b.isFunction(t) && (t = t.call(e, n, C)), b.isArray(t) ? (a = t[0], !b.isArray(t[1]) && /^[\d-]/.test(t[1]) || b.isFunction(t[1]) || k.RegEx.isHex.test(t[1]) ? o = t[1] : b.isString(t[1]) && !k.RegEx.isHex.test(t[1]) && P.Easings[t[1]] || b.isArray(t[1]) ? (i = r ? t[1] : p(t[1], l.duration), o = t[2]) : o = t[1] || t[2]) : a = t, r || (i = i || l.easing), b.isFunction(a) && (a = a.call(e, n, C)), b.isFunction(o) && (o = o.call(e, n, C)), [a || 0, i, o] }(v[O]); if (k.Lists.colors.indexOf(j) >= 0) { var R = q[0],
                                            M = q[1],
                                            $ = q[2]; if (k.RegEx.isHex.test(R)) { for (var B = ["Red", "Green", "Blue"], W = k.Values.hexToRgb(R), I = $ ? k.Values.hexToRgb($) : a, D = 0; D < B.length; D++) { var G = [W[D]];
                                                M && G.push(M), I !== a && G.push(I[D]), H(j + B[D], G) } continue } } H(j, q) }
                            u.element = e } u.element && (k.Values.addClass(e, "velocity-animating"), z.push(u), (c = o(e)) && ("" === l.queue && (c.tweensContainer = u, c.opts = l), c.isAnimating = !0), F === C - 1 ? (P.State.calls.push([z, y, l, null, V.resolver, null, 0]), !1 === P.State.isTicking && (P.State.isTicking = !0, d())) : F++) } var s, l = g.extend({}, P.defaults, x),
                        u = {}; switch (o(e) === a && P.init(e), parseFloat(l.delay) && !1 !== l.queue && g.queue(e, l.queue, function(t) { P.velocityQueueEntryFlag = !0; var r = P.State.delayedElements.count++;
                        P.State.delayedElements[r] = e; var a = function(e) { return function() { P.State.delayedElements[e] = !1, t() } }(r);
                        o(e).delayBegin = (new Date).getTime(), o(e).delay = parseFloat(l.delay), o(e).delayTimer = { setTimeout: setTimeout(t, parseFloat(l.delay)), next: a } }), l.duration.toString().toLowerCase()) {
                        case "fast":
                            l.duration = 200; break;
                        case "normal":
                            l.duration = w; break;
                        case "slow":
                            l.duration = 600; break;
                        default:
                            l.duration = parseFloat(l.duration) || 1 } if (!1 !== P.mock && (!0 === P.mock ? l.duration = l.delay = 1 : (l.duration *= parseFloat(P.mock) || 1, l.delay *= parseFloat(P.mock) || 1)), l.easing = p(l.easing, l.duration), l.begin && !b.isFunction(l.begin) && (l.begin = null), l.progress && !b.isFunction(l.progress) && (l.progress = null), l.complete && !b.isFunction(l.complete) && (l.complete = null), l.display !== a && null !== l.display && (l.display = l.display.toString().toLowerCase(), "auto" === l.display && (l.display = P.CSS.Values.getDisplayType(e))), l.visibility !== a && null !== l.visibility && (l.visibility = l.visibility.toString().toLowerCase()), l.mobileHA = l.mobileHA && P.State.isMobile && !P.State.isGingerbread, !1 === l.queue)
                        if (l.delay) { var c = P.State.delayedElements.count++;
                            P.State.delayedElements[c] = e; var f = function(e) { return function() { P.State.delayedElements[e] = !1, i() } }(c);
                            o(e).delayBegin = (new Date).getTime(), o(e).delay = parseFloat(l.delay), o(e).delayTimer = { setTimeout: setTimeout(i, parseFloat(l.delay)), next: f } } else i();
                    else g.queue(e, l.queue, function(e, t) { if (!0 === t) return V.promise && V.resolver(y), !0;
                        P.velocityQueueEntryFlag = !0, i() }); "" !== l.queue && "fx" !== l.queue || "inprogress" === g.queue(e)[0] || g.dequeue(e) } var u, c, m, h, y, v, x, S = arguments[0] && (arguments[0].p || g.isPlainObject(arguments[0].properties) && !arguments[0].properties.names || b.isString(arguments[0].properties));
                b.isWrapped(this) ? (c = !1, h = 0, y = this, m = this) : (c = !0, h = 1, y = S ? arguments[0].elements || arguments[0].e : arguments[0]); var V = { promise: null, resolver: null, rejecter: null }; if (c && P.Promise && (V.promise = new P.Promise(function(e, t) { V.resolver = e, V.rejecter = t })), S ? (v = arguments[0].properties || arguments[0].p, x = arguments[0].options || arguments[0].o) : (v = arguments[h], x = arguments[h + 1]), y = i(y)) { var C = y.length,
                        F = 0; if (!/^(stop|finish|finishAll|pause|resume)$/i.test(v) && !g.isPlainObject(x)) { x = {}; for (var A = h + 1; A < arguments.length; A++) b.isArray(arguments[A]) || !/^(fast|normal|slow)$/i.test(arguments[A]) && !/^\d/.test(arguments[A]) ? b.isString(arguments[A]) || b.isArray(arguments[A]) ? x.easing = arguments[A] : b.isFunction(arguments[A]) && (x.complete = arguments[A]) : x.duration = arguments[A] } var E; switch (v) {
                        case "scroll":
                            E = "scroll"; break;
                        case "reverse":
                            E = "reverse"; break;
                        case "pause":
                            var N = (new Date).getTime(); return g.each(y, function(e, t) { s(t, N) }), g.each(P.State.calls, function(e, t) { var r = !1;
                                t && g.each(t[1], function(e, n) { var i = x === a ? "" : x; return !0 !== i && t[2].queue !== i && (x !== a || !1 !== t[2].queue) || (g.each(y, function(e, a) { if (a === n) return t[5] = { resume: !1 }, r = !0, !1 }), !r && void 0) }) }), e();
                        case "resume":
                            return g.each(y, function(e, t) { l(t) }), g.each(P.State.calls, function(e, t) { var r = !1;
                                t && g.each(t[1], function(e, n) { var i = x === a ? "" : x; return !0 !== i && t[2].queue !== i && (x !== a || !1 !== t[2].queue) || (!t[5] || (g.each(y, function(e, a) { if (a === n) return t[5].resume = !0, r = !0, !1 }), !r && void 0)) }) }), e();
                        case "finish":
                        case "finishAll":
                        case "stop":
                            g.each(y, function(e, t) { o(t) && o(t).delayTimer && (clearTimeout(o(t).delayTimer.setTimeout), o(t).delayTimer.next && o(t).delayTimer.next(), delete o(t).delayTimer), "finishAll" !== v || !0 !== x && !b.isString(x) || (g.each(g.queue(t, b.isString(x) ? x : ""), function(e, t) { b.isFunction(t) && t() }), g.queue(t, b.isString(x) ? x : "", [])) }); var H = []; return g.each(P.State.calls, function(e, t) { t && g.each(t[1], function(r, n) { var i = x === a ? "" : x; if (!0 !== i && t[2].queue !== i && (x !== a || !1 !== t[2].queue)) return !0;
                                    g.each(y, function(r, a) { if (a === n)
                                            if ((!0 === x || b.isString(x)) && (g.each(g.queue(a, b.isString(x) ? x : ""), function(e, t) { b.isFunction(t) && t(null, !0) }), g.queue(a, b.isString(x) ? x : "", [])), "stop" === v) { var s = o(a);
                                                s && s.tweensContainer && !1 !== i && g.each(s.tweensContainer, function(e, t) { t.endValue = t.currentValue }), H.push(e) } else "finish" !== v && "finishAll" !== v || (t[2].duration = 1) }) }) }), "stop" === v && (g.each(H, function(e, t) { f(t, !0) }), V.promise && V.resolver(y)), e();
                        default:
                            if (!g.isPlainObject(v) || b.isEmptyObject(v)) { if (b.isString(v) && P.Redirects[v]) { var O = (u = g.extend({}, x)).duration,
                                        j = u.delay || 0; return !0 === u.backwards && (y = g.extend(!0, [], y).reverse()), g.each(y, function(e, t) { parseFloat(u.stagger) ? u.delay = j + parseFloat(u.stagger) * e : b.isFunction(u.stagger) && (u.delay = j + u.stagger.call(t, e, C)), u.drag && (u.duration = parseFloat(O) || (/^(callout|transition)/.test(v) ? 1e3 : w), u.duration = Math.max(u.duration * (u.backwards ? 1 - e / C : (e + 1) / C), .75 * u.duration, 200)), P.Redirects[v].call(t, t, u || {}, e, C, y, V.promise ? V : a) }), e() } var q = "Velocity: First argument (" + v + ") was not a property map, a known action, or a registered redirect. Aborting."; return V.promise ? V.rejecter(new Error(q)) : console.log(q), e() } E = "start" } var L = { lastParent: null, lastPosition: null, lastFontSize: null, lastPercentToPxWidth: null, lastPercentToPxHeight: null, lastEmToPx: null, remToPx: null, vwToPx: null, vhToPx: null },
                        z = [];
                    g.each(y, function(e, t) { b.isNode(t) && n(t, e) }), (u = g.extend({}, P.defaults, x)).loop = parseInt(u.loop, 10); var R = 2 * u.loop - 1; if (u.loop)
                        for (var M = 0; M < R; M++) { var $ = { delay: u.delay, progress: u.progress };
                            M === R - 1 && ($.display = u.display, $.visibility = u.visibility, $.complete = u.complete), T(y, "reverse", $) }
                    return e() } V.promise && (v && x && !1 === x.promiseRejectEmpty ? V.resolver() : V.rejecter()) };
            (P = g.extend(T, P)).animate = T; var C = t.requestAnimationFrame || h; if (!P.State.isMobile && r.hidden !== a) { var F = function() { r.hidden ? (C = function(e) { return setTimeout(function() { e(!0) }, 16) }, d()) : C = t.requestAnimationFrame || h };
                F(), r.addEventListener("visibilitychange", F) } return e.Velocity = P, e !== t && (e.fn.velocity = T, e.fn.velocity.defaults = P.defaults), g.each(["Down", "Up"], function(e, t) { P.Redirects["slide" + t] = function(e, r, n, i, o, s) { var l = g.extend({}, r),
                        u = l.begin,
                        c = l.complete,
                        p = {},
                        d = { height: "", marginTop: "", marginBottom: "", paddingTop: "", paddingBottom: "" };
                    l.display === a && (l.display = "Down" === t ? "inline" === P.CSS.Values.getDisplayType(e) ? "inline-block" : "block" : "none"), l.begin = function() { 0 === n && u && u.call(o, o); for (var r in d)
                            if (d.hasOwnProperty(r)) { p[r] = e.style[r]; var a = k.getPropertyValue(e, r);
                                d[r] = "Down" === t ? [a, 0] : [0, a] }
                        p.overflow = e.style.overflow, e.style.overflow = "hidden" }, l.complete = function() { for (var t in p) p.hasOwnProperty(t) && (e.style[t] = p[t]);
                        n === i - 1 && (c && c.call(o, o), s && s.resolver(o)) }, P(e, d, l) } }), g.each(["In", "Out"], function(e, t) { P.Redirects["fade" + t] = function(e, r, n, i, o, s) { var l = g.extend({}, r),
                        u = l.complete,
                        c = { opacity: "In" === t ? 1 : 0 };
                    0 !== n && (l.begin = null), l.complete = n !== i - 1 ? null : function() { u && u.call(o, o), s && s.resolver(o) }, l.display === a && (l.display = "In" === t ? "auto" : "none"), P(this, c, l) } }), P } jQuery.fn.velocity = jQuery.fn.animate }(window.jQuery || window.Zepto || window, window, window ? window.document : void 0) });
! function() { "use strict"; var e = function() { this.init() };
    e.prototype = { init: function() { var e = this || n; return e._codecs = {}, e._howls = [], e._muted = !1, e._volume = 1, e._canPlayEvent = "canplaythrough", e._navigator = "undefined" != typeof window && window.navigator ? window.navigator : null, e.masterGain = null, e.noAudio = !1, e.usingWebAudio = !0, e.autoSuspend = !0, e.ctx = null, e.mobileAutoEnable = !0, e._setup(), e }, volume: function(e) { var o = this || n; if (e = parseFloat(e), o.ctx || _(), void 0 !== e && e >= 0 && e <= 1) { if (o._volume = e, o._muted) return o;
                o.usingWebAudio && (o.masterGain.gain.value = e); for (var t = 0; t < o._howls.length; t++)
                    if (!o._howls[t]._webAudio)
                        for (var r = o._howls[t]._getSoundIds(), a = 0; a < r.length; a++) { var i = o._howls[t]._soundById(r[a]);
                            i && i._node && (i._node.volume = i._volume * e) }
                return o } return o._volume }, mute: function(e) { var o = this || n;
            o.ctx || _(), o._muted = e, o.usingWebAudio && (o.masterGain.gain.value = e ? 0 : o._volume); for (var t = 0; t < o._howls.length; t++)
                if (!o._howls[t]._webAudio)
                    for (var r = o._howls[t]._getSoundIds(), a = 0; a < r.length; a++) { var i = o._howls[t]._soundById(r[a]);
                        i && i._node && (i._node.muted = !!e || i._muted) }
            return o }, unload: function() { for (var e = this || n, o = e._howls.length - 1; o >= 0; o--) e._howls[o].unload(); return e.usingWebAudio && e.ctx && void 0 !== e.ctx.close && (e.ctx.close(), e.ctx = null, _()), e }, codecs: function(e) { return (this || n)._codecs[e.replace(/^x-/, "")] }, _setup: function() { var e = this || n; if (e.state = e.ctx ? e.ctx.state || "running" : "running", e._autoSuspend(), !e.usingWebAudio)
                if ("undefined" != typeof Audio) try { void 0 === (o = new Audio).oncanplaythrough && (e._canPlayEvent = "canplay") } catch (n) { e.noAudio = !0 } else e.noAudio = !0; try { var o = new Audio;
                o.muted && (e.noAudio = !0) } catch (e) {} return e.noAudio || e._setupCodecs(), e }, _setupCodecs: function() { var e = this || n,
                o = null; try { o = "undefined" != typeof Audio ? new Audio : null } catch (n) { return e } if (!o || "function" != typeof o.canPlayType) return e; var t = o.canPlayType("audio/mpeg;").replace(/^no$/, ""),
                r = e._navigator && e._navigator.userAgent.match(/OPR\/([0-6].)/g),
                a = r && parseInt(r[0].split("/")[1], 10) < 33; return e._codecs = { mp3: !(a || !t && !o.canPlayType("audio/mp3;").replace(/^no$/, "")), mpeg: !!t, opus: !!o.canPlayType('audio/ogg; codecs="opus"').replace(/^no$/, ""), ogg: !!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/, ""), oga: !!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/, ""), wav: !!o.canPlayType('audio/wav; codecs="1"').replace(/^no$/, ""), aac: !!o.canPlayType("audio/aac;").replace(/^no$/, ""), caf: !!o.canPlayType("audio/x-caf;").replace(/^no$/, ""), m4a: !!(o.canPlayType("audio/x-m4a;") || o.canPlayType("audio/m4a;") || o.canPlayType("audio/aac;")).replace(/^no$/, ""), mp4: !!(o.canPlayType("audio/x-mp4;") || o.canPlayType("audio/mp4;") || o.canPlayType("audio/aac;")).replace(/^no$/, ""), weba: !!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/, ""), webm: !!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/, ""), dolby: !!o.canPlayType('audio/mp4; codecs="ec-3"').replace(/^no$/, ""), flac: !!(o.canPlayType("audio/x-flac;") || o.canPlayType("audio/flac;")).replace(/^no$/, "") }, e }, _enableMobileAudio: function() { var e = this || n,
                o = /iPhone|iPad|iPod|Android|BlackBerry|BB10|Silk|Mobi/i.test(e._navigator && e._navigator.userAgent),
                t = !!("ontouchend" in window || e._navigator && e._navigator.maxTouchPoints > 0 || e._navigator && e._navigator.msMaxTouchPoints > 0); if (!e._mobileEnabled && e.ctx && (o || t)) { e._mobileEnabled = !1, e._mobileUnloaded || 44100 === e.ctx.sampleRate || (e._mobileUnloaded = !0, e.unload()), e._scratchBuffer = e.ctx.createBuffer(1, 1, 22050); var r = function() { var n = e.ctx.createBufferSource();
                    n.buffer = e._scratchBuffer, n.connect(e.ctx.destination), void 0 === n.start ? n.noteOn(0) : n.start(0), n.onended = function() { n.disconnect(0), e._mobileEnabled = !0, e.mobileAutoEnable = !1, document.removeEventListener("touchend", r, !0) } }; return document.addEventListener("touchend", r, !0), e } }, _autoSuspend: function() { var e = this; if (e.autoSuspend && e.ctx && void 0 !== e.ctx.suspend && n.usingWebAudio) { for (var o = 0; o < e._howls.length; o++)
                    if (e._howls[o]._webAudio)
                        for (var t = 0; t < e._howls[o]._sounds.length; t++)
                            if (!e._howls[o]._sounds[t]._paused) return e; return e._suspendTimer && clearTimeout(e._suspendTimer), e._suspendTimer = setTimeout(function() { e.autoSuspend && (e._suspendTimer = null, e.state = "suspending", e.ctx.suspend().then(function() { e.state = "suspended", e._resumeAfterSuspend && (delete e._resumeAfterSuspend, e._autoResume()) })) }, 3e4), e } }, _autoResume: function() { var e = this; if (e.ctx && void 0 !== e.ctx.resume && n.usingWebAudio) return "running" === e.state && e._suspendTimer ? (clearTimeout(e._suspendTimer), e._suspendTimer = null) : "suspended" === e.state ? (e.state = "resuming", e.ctx.resume().then(function() { e.state = "running"; for (var n = 0; n < e._howls.length; n++) e._howls[n]._emit("resume") }), e._suspendTimer && (clearTimeout(e._suspendTimer), e._suspendTimer = null)) : "suspending" === e.state && (e._resumeAfterSuspend = !0), e } }; var n = new e,
        o = function(e) { var n = this; return e.src && 0 !== e.src.length ? void n.init(e) : void console.error("An array of source files must be passed with any new Howl.") };
    o.prototype = { init: function(e) { var o = this; return n.ctx || _(), o._autoplay = e.autoplay || !1, o._format = "string" != typeof e.format ? e.format : [e.format], o._html5 = e.html5 || !1, o._muted = e.mute || !1, o._loop = e.loop || !1, o._pool = e.pool || 5, o._preload = "boolean" != typeof e.preload || e.preload, o._rate = e.rate || 1, o._sprite = e.sprite || {}, o._src = "string" != typeof e.src ? e.src : [e.src], o._volume = void 0 !== e.volume ? e.volume : 1, o._duration = 0, o._state = "unloaded", o._sounds = [], o._endTimers = {}, o._queue = [], o._onend = e.onend ? [{ fn: e.onend }] : [], o._onfade = e.onfade ? [{ fn: e.onfade }] : [], o._onload = e.onload ? [{ fn: e.onload }] : [], o._onloaderror = e.onloaderror ? [{ fn: e.onloaderror }] : [], o._onpause = e.onpause ? [{ fn: e.onpause }] : [], o._onplay = e.onplay ? [{ fn: e.onplay }] : [], o._onstop = e.onstop ? [{ fn: e.onstop }] : [], o._onmute = e.onmute ? [{ fn: e.onmute }] : [], o._onvolume = e.onvolume ? [{ fn: e.onvolume }] : [], o._onrate = e.onrate ? [{ fn: e.onrate }] : [], o._onseek = e.onseek ? [{ fn: e.onseek }] : [], o._onresume = [], o._webAudio = n.usingWebAudio && !o._html5, void 0 !== n.ctx && n.ctx && n.mobileAutoEnable && n._enableMobileAudio(), n._howls.push(o), o._autoplay && o._queue.push({ event: "play", action: function() { o.play() } }), o._preload && o.load(), o }, load: function() { var e = this,
                o = null; { if (!n.noAudio) { "string" == typeof e._src && (e._src = [e._src]); for (var r = 0; r < e._src.length; r++) { var i, u; if (e._format && e._format[r]) i = e._format[r];
                        else { if ("string" != typeof(u = e._src[r])) { e._emit("loaderror", null, "Non-string found in selected audio sources - ignoring."); continue }(i = /^data:audio\/([^;,]+);/i.exec(u)) || (i = /\.([^.]+)$/.exec(u.split("?", 1)[0])), i && (i = i[1].toLowerCase()) } if (n.codecs(i)) { o = e._src[r]; break } } return o ? (e._src = o, e._state = "loading", "https:" === window.location.protocol && "http:" === o.slice(0, 5) && (e._html5 = !0, e._webAudio = !1), new t(e), e._webAudio && a(e), e) : void e._emit("loaderror", null, "No codec support for selected audio sources.") } e._emit("loaderror", null, "No audio support.") } }, play: function(e, o) { var t = this,
                r = null; if ("number" == typeof e) r = e, e = null;
            else { if ("string" == typeof e && "loaded" === t._state && !t._sprite[e]) return null; if (void 0 === e) { e = "__default"; for (var a = 0, i = 0; i < t._sounds.length; i++) t._sounds[i]._paused && !t._sounds[i]._ended && (a++, r = t._sounds[i]._id);
                    1 === a ? e = null : r = null } } var u = r ? t._soundById(r) : t._inactiveSound(); if (!u) return null; if (r && !e && (e = u._sprite || "__default"), "loaded" !== t._state && !t._sprite[e]) return t._queue.push({ event: "play", action: function() { t.play(t._soundById(u._id) ? u._id : void 0) } }), u._id; if (r && !u._paused) return o || setTimeout(function() { t._emit("play", u._id) }, 0), u._id;
            t._webAudio && n._autoResume(); var d = Math.max(0, u._seek > 0 ? u._seek : t._sprite[e][0] / 1e3),
                _ = Math.max(0, (t._sprite[e][0] + t._sprite[e][1]) / 1e3 - d),
                s = 1e3 * _ / Math.abs(u._rate);
            u._paused = !1, u._ended = !1, u._sprite = e, u._seek = d, u._start = t._sprite[e][0] / 1e3, u._stop = (t._sprite[e][0] + t._sprite[e][1]) / 1e3, u._loop = !(!u._loop && !t._sprite[e][2]); var l = u._node; if (t._webAudio) { var c = function() { t._refreshBuffer(u); var e = u._muted || t._muted ? 0 : u._volume;
                        l.gain.setValueAtTime(e, n.ctx.currentTime), u._playStart = n.ctx.currentTime, void 0 === l.bufferSource.start ? u._loop ? l.bufferSource.noteGrainOn(0, d, 86400) : l.bufferSource.noteGrainOn(0, d, _) : u._loop ? l.bufferSource.start(0, d, 86400) : l.bufferSource.start(0, d, _), s !== 1 / 0 && (t._endTimers[u._id] = setTimeout(t._ended.bind(t, u), s)), o || setTimeout(function() { t._emit("play", u._id) }, 0) },
                    f = "running" === n.state; "loaded" === t._state && f ? c() : (t.once(f ? "load" : "resume", c, f ? u._id : null), t._clearTimer(u._id)) } else { var p = function() { l.currentTime = d, l.muted = u._muted || t._muted || n._muted || l.muted, l.volume = u._volume * n.volume(), l.playbackRate = u._rate, setTimeout(function() { l.play(), s !== 1 / 0 && (t._endTimers[u._id] = setTimeout(t._ended.bind(t, u), s)), o || t._emit("play", u._id) }, 0) },
                    v = "loaded" === t._state && (window && window.ejecta || !l.readyState && n._navigator.isCocoonJS); if (4 === l.readyState || v) p();
                else { var m = function() { p(), l.removeEventListener(n._canPlayEvent, m, !1) };
                    l.addEventListener(n._canPlayEvent, m, !1), t._clearTimer(u._id) } } return u._id }, pause: function(e) { var n = this; if ("loaded" !== n._state) return n._queue.push({ event: "pause", action: function() { n.pause(e) } }), n; for (var o = n._getSoundIds(e), t = 0; t < o.length; t++) { n._clearTimer(o[t]); var r = n._soundById(o[t]); if (r && !r._paused && (r._seek = n.seek(o[t]), r._rateSeek = 0, r._paused = !0, n._stopFade(o[t]), r._node))
                    if (n._webAudio) { if (!r._node.bufferSource) return n;
                        void 0 === r._node.bufferSource.stop ? r._node.bufferSource.noteOff(0) : r._node.bufferSource.stop(0), n._cleanBuffer(r._node) } else isNaN(r._node.duration) && r._node.duration !== 1 / 0 || r._node.pause();
                arguments[1] || n._emit("pause", r ? r._id : null) } return n }, stop: function(e, n) { var o = this; if ("loaded" !== o._state) return o._queue.push({ event: "stop", action: function() { o.stop(e) } }), o; for (var t = o._getSoundIds(e), r = 0; r < t.length; r++) { o._clearTimer(t[r]); var a = o._soundById(t[r]); if (a && (a._seek = a._start || 0, a._rateSeek = 0, a._paused = !0, a._ended = !0, o._stopFade(t[r]), a._node))
                    if (o._webAudio) { if (!a._node.bufferSource) return n || o._emit("stop", a._id), o;
                        void 0 === a._node.bufferSource.stop ? a._node.bufferSource.noteOff(0) : a._node.bufferSource.stop(0), o._cleanBuffer(a._node) } else isNaN(a._node.duration) && a._node.duration !== 1 / 0 || (a._node.currentTime = a._start || 0, a._node.pause());
                a && !n && o._emit("stop", a._id) } return o }, mute: function(e, o) { var t = this; if ("loaded" !== t._state) return t._queue.push({ event: "mute", action: function() { t.mute(e, o) } }), t; if (void 0 === o) { if ("boolean" != typeof e) return t._muted;
                t._muted = e } for (var r = t._getSoundIds(o), a = 0; a < r.length; a++) { var i = t._soundById(r[a]);
                i && (i._muted = e, t._webAudio && i._node ? i._node.gain.setValueAtTime(e ? 0 : i._volume, n.ctx.currentTime) : i._node && (i._node.muted = !!n._muted || e), t._emit("mute", i._id)) } return t }, volume: function() { var e, o, t = this,
                r = arguments; if (0 === r.length) return t._volume;
            1 === r.length || 2 === r.length && void 0 === r[1] ? t._getSoundIds().indexOf(r[0]) >= 0 ? o = parseInt(r[0], 10) : e = parseFloat(r[0]) : r.length >= 2 && (e = parseFloat(r[0]), o = parseInt(r[1], 10)); var a; if (!(void 0 !== e && e >= 0 && e <= 1)) return (a = o ? t._soundById(o) : t._sounds[0]) ? a._volume : 0; if ("loaded" !== t._state) return t._queue.push({ event: "volume", action: function() { t.volume.apply(t, r) } }), t;
            void 0 === o && (t._volume = e), o = t._getSoundIds(o); for (var i = 0; i < o.length; i++)(a = t._soundById(o[i])) && (a._volume = e, r[2] || t._stopFade(o[i]), t._webAudio && a._node && !a._muted ? a._node.gain.setValueAtTime(e, n.ctx.currentTime) : a._node && !a._muted && (a._node.volume = e * n.volume()), t._emit("volume", a._id)); return t }, fade: function(e, o, t, r) { var a = this,
                i = Math.abs(e - o),
                u = e > o ? "out" : "in",
                d = i / .01,
                _ = d > 0 ? t / d : t; if (_ < 4 && (d = Math.ceil(d / (4 / _)), _ = 4), "loaded" !== a._state) return a._queue.push({ event: "fade", action: function() { a.fade(e, o, t, r) } }), a;
            a.volume(e, r); for (var s = a._getSoundIds(r), l = 0; l < s.length; l++) { var c = a._soundById(s[l]); if (c) { if (r || a._stopFade(s[l]), a._webAudio && !c._muted) { var f = n.ctx.currentTime,
                            p = f + t / 1e3;
                        c._volume = e, c._node.gain.setValueAtTime(e, f), c._node.gain.linearRampToValueAtTime(o, p) } var v = e;
                    c._interval = setInterval(function(e, n) { d > 0 && (v += "in" === u ? .01 : -.01), v = Math.max(0, v), v = Math.min(1, v), v = Math.round(100 * v) / 100, a._webAudio ? (void 0 === r && (a._volume = v), n._volume = v) : a.volume(v, e, !0), v === o && (clearInterval(n._interval), n._interval = null, a.volume(v, e), a._emit("fade", e)) }.bind(a, s[l], c), _) } } return a }, _stopFade: function(e) { var o = this,
                t = o._soundById(e); return t && t._interval && (o._webAudio && t._node.gain.cancelScheduledValues(n.ctx.currentTime), clearInterval(t._interval), t._interval = null, o._emit("fade", e)), o }, loop: function() { var e, n, o, t = this,
                r = arguments; if (0 === r.length) return t._loop; if (1 === r.length) { if ("boolean" != typeof r[0]) return !!(o = t._soundById(parseInt(r[0], 10))) && o._loop;
                e = r[0], t._loop = e } else 2 === r.length && (e = r[0], n = parseInt(r[1], 10)); for (var a = t._getSoundIds(n), i = 0; i < a.length; i++)(o = t._soundById(a[i])) && (o._loop = e, t._webAudio && o._node && o._node.bufferSource && (o._node.bufferSource.loop = e, e && (o._node.bufferSource.loopStart = o._start || 0, o._node.bufferSource.loopEnd = o._stop))); return t }, rate: function() { var e, o, t = this,
                r = arguments;
            0 === r.length ? o = t._sounds[0]._id : 1 === r.length ? t._getSoundIds().indexOf(r[0]) >= 0 ? o = parseInt(r[0], 10) : e = parseFloat(r[0]) : 2 === r.length && (e = parseFloat(r[0]), o = parseInt(r[1], 10)); var a; if ("number" != typeof e) return (a = t._soundById(o)) ? a._rate : t._rate; if ("loaded" !== t._state) return t._queue.push({ event: "rate", action: function() { t.rate.apply(t, r) } }), t;
            void 0 === o && (t._rate = e), o = t._getSoundIds(o); for (var i = 0; i < o.length; i++)
                if (a = t._soundById(o[i])) { a._rateSeek = t.seek(o[i]), a._playStart = t._webAudio ? n.ctx.currentTime : a._playStart, a._rate = e, t._webAudio && a._node && a._node.bufferSource ? a._node.bufferSource.playbackRate.value = e : a._node && (a._node.playbackRate = e); var u = t.seek(o[i]),
                        d = 1e3 * ((t._sprite[a._sprite][0] + t._sprite[a._sprite][1]) / 1e3 - u) / Math.abs(a._rate);!t._endTimers[o[i]] && a._paused || (t._clearTimer(o[i]), t._endTimers[o[i]] = setTimeout(t._ended.bind(t, a), d)), t._emit("rate", a._id) }
            return t }, seek: function() { var e, o, t = this,
                r = arguments; if (0 === r.length ? o = t._sounds[0]._id : 1 === r.length ? t._getSoundIds().indexOf(r[0]) >= 0 ? o = parseInt(r[0], 10) : (o = t._sounds[0]._id, e = parseFloat(r[0])) : 2 === r.length && (e = parseFloat(r[0]), o = parseInt(r[1], 10)), void 0 === o) return t; if ("loaded" !== t._state) return t._queue.push({ event: "seek", action: function() { t.seek.apply(t, r) } }), t; var a = t._soundById(o); if (a) { if (!("number" == typeof e && e >= 0)) { if (t._webAudio) { var i = t.playing(o) ? n.ctx.currentTime - a._playStart : 0,
                            u = a._rateSeek ? a._rateSeek - a._seek : 0; return a._seek + (u + i * Math.abs(a._rate)) } return a._node.currentTime } var d = t.playing(o);
                d && t.pause(o, !0), a._seek = e, a._ended = !1, t._clearTimer(o), d && t.play(o, !0), !t._webAudio && a._node && (a._node.currentTime = e), t._emit("seek", o) } return t }, playing: function(e) { var n = this; if ("number" == typeof e) { var o = n._soundById(e); return !!o && !o._paused } for (var t = 0; t < n._sounds.length; t++)
                if (!n._sounds[t]._paused) return !0; return !1 }, duration: function(e) { var n = this,
                o = n._duration,
                t = n._soundById(e); return t && (o = n._sprite[t._sprite][1] / 1e3), o }, state: function() { return this._state }, unload: function() { for (var e = this, o = e._sounds, t = 0; t < o.length; t++) { o[t]._paused || (e.stop(o[t]._id), e._emit("end", o[t]._id)), e._webAudio || (o[t]._node.src = "data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQAAAAA=", o[t]._node.removeEventListener("error", o[t]._errorFn, !1), o[t]._node.removeEventListener(n._canPlayEvent, o[t]._loadFn, !1)), delete o[t]._node, e._clearTimer(o[t]._id); var a = n._howls.indexOf(e);
                a >= 0 && n._howls.splice(a, 1) } var i = !0; for (t = 0; t < n._howls.length; t++)
                if (n._howls[t]._src === e._src) { i = !1; break }
            return r && i && delete r[e._src], n.noAudio = !1, e._state = "unloaded", e._sounds = [], e = null, null }, on: function(e, n, o, t) { var r = this,
                a = r["_on" + e]; return "function" == typeof n && a.push(t ? { id: o, fn: n, once: t } : { id: o, fn: n }), r }, off: function(e, n, o) { var t = this,
                r = t["_on" + e],
                a = 0; if (n) { for (a = 0; a < r.length; a++)
                    if (n === r[a].fn && o === r[a].id) { r.splice(a, 1); break } } else if (e) t["_on" + e] = [];
            else { var i = Object.keys(t); for (a = 0; a < i.length; a++) 0 === i[a].indexOf("_on") && Array.isArray(t[i[a]]) && (t[i[a]] = []) } return t }, once: function(e, n, o) { var t = this; return t.on(e, n, o, 1), t }, _emit: function(e, n, o) { for (var t = this, r = t["_on" + e], a = r.length - 1; a >= 0; a--) r[a].id && r[a].id !== n && "load" !== e || (setTimeout(function(e) { e.call(this, n, o) }.bind(t, r[a].fn), 0), r[a].once && t.off(e, r[a].fn, r[a].id)); return t }, _loadQueue: function() { var e = this; if (e._queue.length > 0) { var n = e._queue[0];
                e.once(n.event, function() { e._queue.shift(), e._loadQueue() }), n.action() } return e }, _ended: function(e) { var o = this,
                t = e._sprite,
                r = !(!e._loop && !o._sprite[t][2]); if (o._emit("end", e._id), !o._webAudio && r && o.stop(e._id, !0).play(e._id), o._webAudio && r) { o._emit("play", e._id), e._seek = e._start || 0, e._rateSeek = 0, e._playStart = n.ctx.currentTime; var a = 1e3 * (e._stop - e._start) / Math.abs(e._rate);
                o._endTimers[e._id] = setTimeout(o._ended.bind(o, e), a) } return o._webAudio && !r && (e._paused = !0, e._ended = !0, e._seek = e._start || 0, e._rateSeek = 0, o._clearTimer(e._id), o._cleanBuffer(e._node), n._autoSuspend()), o._webAudio || r || o.stop(e._id), o }, _clearTimer: function(e) { var n = this; return n._endTimers[e] && (clearTimeout(n._endTimers[e]), delete n._endTimers[e]), n }, _soundById: function(e) { for (var n = this, o = 0; o < n._sounds.length; o++)
                if (e === n._sounds[o]._id) return n._sounds[o]; return null }, _inactiveSound: function() { var e = this;
            e._drain(); for (var n = 0; n < e._sounds.length; n++)
                if (e._sounds[n]._ended) return e._sounds[n].reset(); return new t(e) }, _drain: function() { var e = this,
                n = e._pool,
                o = 0,
                t = 0; if (!(e._sounds.length < n)) { for (t = 0; t < e._sounds.length; t++) e._sounds[t]._ended && o++; for (t = e._sounds.length - 1; t >= 0; t--) { if (o <= n) return;
                    e._sounds[t]._ended && (e._webAudio && e._sounds[t]._node && e._sounds[t]._node.disconnect(0), e._sounds.splice(t, 1), o--) } } }, _getSoundIds: function(e) { var n = this; if (void 0 === e) { for (var o = [], t = 0; t < n._sounds.length; t++) o.push(n._sounds[t]._id); return o } return [e] }, _refreshBuffer: function(e) { var o = this; return e._node.bufferSource = n.ctx.createBufferSource(), e._node.bufferSource.buffer = r[o._src], e._panner ? e._node.bufferSource.connect(e._panner) : e._node.bufferSource.connect(e._node), e._node.bufferSource.loop = e._loop, e._loop && (e._node.bufferSource.loopStart = e._start || 0, e._node.bufferSource.loopEnd = e._stop), e._node.bufferSource.playbackRate.value = e._rate, o }, _cleanBuffer: function(e) { var n = this; if (n._scratchBuffer) { e.bufferSource.onended = null, e.bufferSource.disconnect(0); try { e.bufferSource.buffer = n._scratchBuffer } catch (e) {} } return e.bufferSource = null, n } }; var t = function(e) { this._parent = e, this.init() };
    t.prototype = { init: function() { var e = this,
                n = e._parent; return e._muted = n._muted, e._loop = n._loop, e._volume = n._volume, e._muted = n._muted, e._rate = n._rate, e._seek = 0, e._paused = !0, e._ended = !0, e._sprite = "__default", e._id = Math.round(Date.now() * Math.random()), n._sounds.push(e), e.create(), e }, create: function() { var e = this,
                o = e._parent,
                t = n._muted || e._muted || e._parent._muted ? 0 : e._volume; return o._webAudio ? (e._node = void 0 === n.ctx.createGain ? n.ctx.createGainNode() : n.ctx.createGain(), e._node.gain.setValueAtTime(t, n.ctx.currentTime), e._node.paused = !0, e._node.connect(n.masterGain)) : (e._node = new Audio, e._errorFn = e._errorListener.bind(e), e._node.addEventListener("error", e._errorFn, !1), e._loadFn = e._loadListener.bind(e), e._node.addEventListener(n._canPlayEvent, e._loadFn, !1), e._node.src = o._src, e._node.preload = "auto", e._node.volume = t * n.volume(), e._node.load()), e }, reset: function() { var e = this,
                n = e._parent; return e._muted = n._muted, e._loop = n._loop, e._volume = n._volume, e._muted = n._muted, e._rate = n._rate, e._seek = 0, e._rateSeek = 0, e._paused = !0, e._ended = !0, e._sprite = "__default", e._id = Math.round(Date.now() * Math.random()), e }, _errorListener: function() { var e = this;
            e._parent._emit("loaderror", e._id, e._node.error ? e._node.error.code : 0), e._node.removeEventListener("error", e._errorListener, !1) }, _loadListener: function() { var e = this,
                o = e._parent;
            o._duration = Math.ceil(10 * e._node.duration) / 10, 0 === Object.keys(o._sprite).length && (o._sprite = { __default: [0, 1e3 * o._duration] }), "loaded" !== o._state && (o._state = "loaded", o._emit("load"), o._loadQueue()), e._node.removeEventListener(n._canPlayEvent, e._loadFn, !1) } }; var r = {},
        a = function(e) { var n = e._src; if (r[n]) return e._duration = r[n].duration, void d(e); if (/^data:[^;]+;base64,/.test(n)) { for (var o = atob(n.split(",")[1]), t = new Uint8Array(o.length), a = 0; a < o.length; ++a) t[a] = o.charCodeAt(a);
                u(t.buffer, e) } else { var _ = new XMLHttpRequest;
                _.open("GET", n, !0), _.responseType = "arraybuffer", _.onload = function() { var n = (_.status + "")[0]; return "0" !== n && "2" !== n && "3" !== n ? void e._emit("loaderror", null, "Failed loading audio file with status: " + _.status + ".") : void u(_.response, e) }, _.onerror = function() { e._webAudio && (e._html5 = !0, e._webAudio = !1, e._sounds = [], delete r[n], e.load()) }, i(_) } },
        i = function(e) { try { e.send() } catch (n) { e.onerror() } },
        u = function(e, o) { n.ctx.decodeAudioData(e, function(e) { e && o._sounds.length > 0 && (r[o._src] = e, d(o, e)) }, function() { o._emit("loaderror", null, "Decoding audio data failed.") }) },
        d = function(e, n) { n && !e._duration && (e._duration = n.duration), 0 === Object.keys(e._sprite).length && (e._sprite = { __default: [0, 1e3 * e._duration] }), "loaded" !== e._state && (e._state = "loaded", e._emit("load"), e._loadQueue()) },
        _ = function() { try { "undefined" != typeof AudioContext ? n.ctx = new AudioContext : "undefined" != typeof webkitAudioContext ? n.ctx = new webkitAudioContext : n.usingWebAudio = !1 } catch (e) { n.usingWebAudio = !1 } var e = /iP(hone|od|ad)/.test(n._navigator && n._navigator.platform),
                o = n._navigator && n._navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/),
                t = o ? parseInt(o[1], 10) : null; if (e && t && t < 9) { var r = /safari/.test(n._navigator && n._navigator.userAgent.toLowerCase());
                (n._navigator && n._navigator.standalone && !r || n._navigator && !n._navigator.standalone && !r) && (n.usingWebAudio = !1) } n.usingWebAudio && (n.masterGain = void 0 === n.ctx.createGain ? n.ctx.createGainNode() : n.ctx.createGain(), n.masterGain.gain.value = 1, n.masterGain.connect(n.ctx.destination)), n._setup() }; "function" == typeof define && define.amd && define([], function() { return { Howler: n, Howl: o } }), "undefined" != typeof exports && (exports.Howler = n, exports.Howl = o), "undefined" != typeof window ? (window.HowlerGlobal = e, window.Howler = n, window.Howl = o, window.Sound = t) : "undefined" != typeof global && (global.HowlerGlobal = e, global.Howler = n, global.Howl = o, global.Sound = t) }(),
function() { "use strict";
    HowlerGlobal.prototype._pos = [0, 0, 0], HowlerGlobal.prototype._orientation = [0, 0, -1, 0, 1, 0], HowlerGlobal.prototype.stereo = function(e) { var n = this; if (!n.ctx || !n.ctx.listener) return n; for (var o = n._howls.length - 1; o >= 0; o--) n._howls[o].stereo(e); return n }, HowlerGlobal.prototype.pos = function(e, n, o) { var t = this; return t.ctx && t.ctx.listener ? (n = "number" != typeof n ? t._pos[1] : n, o = "number" != typeof o ? t._pos[2] : o, "number" != typeof e ? t._pos : (t._pos = [e, n, o], t.ctx.listener.setPosition(t._pos[0], t._pos[1], t._pos[2]), t)) : t }, HowlerGlobal.prototype.orientation = function(e, n, o, t, r, a) { var i = this; if (!i.ctx || !i.ctx.listener) return i; var u = i._orientation; return n = "number" != typeof n ? u[1] : n, o = "number" != typeof o ? u[2] : o, t = "number" != typeof t ? u[3] : t, r = "number" != typeof r ? u[4] : r, a = "number" != typeof a ? u[5] : a, "number" != typeof e ? u : (i._orientation = [e, n, o, t, r, a], i.ctx.listener.setOrientation(e, n, o, t, r, a), i) }, Howl.prototype.init = function(e) { return function(n) { var o = this; return o._orientation = n.orientation || [1, 0, 0], o._stereo = n.stereo || null, o._pos = n.pos || null, o._pannerAttr = { coneInnerAngle: void 0 !== n.coneInnerAngle ? n.coneInnerAngle : 360, coneOuterAngle: void 0 !== n.coneOuterAngle ? n.coneOuterAngle : 360, coneOuterGain: void 0 !== n.coneOuterGain ? n.coneOuterGain : 0, distanceModel: void 0 !== n.distanceModel ? n.distanceModel : "inverse", maxDistance: void 0 !== n.maxDistance ? n.maxDistance : 1e4, panningModel: void 0 !== n.panningModel ? n.panningModel : "HRTF", refDistance: void 0 !== n.refDistance ? n.refDistance : 1, rolloffFactor: void 0 !== n.rolloffFactor ? n.rolloffFactor : 1 }, o._onstereo = n.onstereo ? [{ fn: n.onstereo }] : [], o._onpos = n.onpos ? [{ fn: n.onpos }] : [], o._onorientation = n.onorientation ? [{ fn: n.onorientation }] : [], e.call(this, n) } }(Howl.prototype.init), Howl.prototype.stereo = function(n, o) { var t = this; if (!t._webAudio) return t; if ("loaded" !== t._state) return t._queue.push({ event: "stereo", action: function() { t.stereo(n, o) } }), t; var r = void 0 === Howler.ctx.createStereoPanner ? "spatial" : "stereo"; if (void 0 === o) { if ("number" != typeof n) return t._stereo;
            t._stereo = n, t._pos = [n, 0, 0] } for (var a = t._getSoundIds(o), i = 0; i < a.length; i++) { var u = t._soundById(a[i]); if (u) { if ("number" != typeof n) return u._stereo;
                u._stereo = n, u._pos = [n, 0, 0], u._node && (u._pannerAttr.panningModel = "equalpower", u._panner && u._panner.pan || e(u, r), "spatial" === r ? u._panner.setPosition(n, 0, 0) : u._panner.pan.value = n), t._emit("stereo", u._id) } } return t }, Howl.prototype.pos = function(n, o, t, r) { var a = this; if (!a._webAudio) return a; if ("loaded" !== a._state) return a._queue.push({ event: "pos", action: function() { a.pos(n, o, t, r) } }), a; if (o = "number" != typeof o ? 0 : o, t = "number" != typeof t ? -.5 : t, void 0 === r) { if ("number" != typeof n) return a._pos;
            a._pos = [n, o, t] } for (var i = a._getSoundIds(r), u = 0; u < i.length; u++) { var d = a._soundById(i[u]); if (d) { if ("number" != typeof n) return d._pos;
                d._pos = [n, o, t], d._node && (d._panner && !d._panner.pan || e(d, "spatial"), d._panner.setPosition(n, o, t)), a._emit("pos", d._id) } } return a }, Howl.prototype.orientation = function(n, o, t, r) { var a = this; if (!a._webAudio) return a; if ("loaded" !== a._state) return a._queue.push({ event: "orientation", action: function() { a.orientation(n, o, t, r) } }), a; if (o = "number" != typeof o ? a._orientation[1] : o, t = "number" != typeof t ? a._orientation[2] : t, void 0 === r) { if ("number" != typeof n) return a._orientation;
            a._orientation = [n, o, t] } for (var i = a._getSoundIds(r), u = 0; u < i.length; u++) { var d = a._soundById(i[u]); if (d) { if ("number" != typeof n) return d._orientation;
                d._orientation = [n, o, t], d._node && (d._panner || (d._pos || (d._pos = a._pos || [0, 0, -.5]), e(d, "spatial")), d._panner.setOrientation(n, o, t)), a._emit("orientation", d._id) } } return a }, Howl.prototype.pannerAttr = function() { var n, o, t, r = this,
            a = arguments; if (!r._webAudio) return r; if (0 === a.length) return r._pannerAttr; if (1 === a.length) { if ("object" != typeof a[0]) return (t = r._soundById(parseInt(a[0], 10))) ? t._pannerAttr : r._pannerAttr;
            n = a[0], void 0 === o && (r._pannerAttr = { coneInnerAngle: void 0 !== n.coneInnerAngle ? n.coneInnerAngle : r._coneInnerAngle, coneOuterAngle: void 0 !== n.coneOuterAngle ? n.coneOuterAngle : r._coneOuterAngle, coneOuterGain: void 0 !== n.coneOuterGain ? n.coneOuterGain : r._coneOuterGain, distanceModel: void 0 !== n.distanceModel ? n.distanceModel : r._distanceModel, maxDistance: void 0 !== n.maxDistance ? n.maxDistance : r._maxDistance, panningModel: void 0 !== n.panningModel ? n.panningModel : r._panningModel, refDistance: void 0 !== n.refDistance ? n.refDistance : r._refDistance, rolloffFactor: void 0 !== n.rolloffFactor ? n.rolloffFactor : r._rolloffFactor }) } else 2 === a.length && (n = a[0], o = parseInt(a[1], 10)); for (var i = r._getSoundIds(o), u = 0; u < i.length; u++)
            if (t = r._soundById(i[u])) { var d = t._pannerAttr;
                d = { coneInnerAngle: void 0 !== n.coneInnerAngle ? n.coneInnerAngle : d.coneInnerAngle, coneOuterAngle: void 0 !== n.coneOuterAngle ? n.coneOuterAngle : d.coneOuterAngle, coneOuterGain: void 0 !== n.coneOuterGain ? n.coneOuterGain : d.coneOuterGain, distanceModel: void 0 !== n.distanceModel ? n.distanceModel : d.distanceModel, maxDistance: void 0 !== n.maxDistance ? n.maxDistance : d.maxDistance, panningModel: void 0 !== n.panningModel ? n.panningModel : d.panningModel, refDistance: void 0 !== n.refDistance ? n.refDistance : d.refDistance, rolloffFactor: void 0 !== n.rolloffFactor ? n.rolloffFactor : d.rolloffFactor }; var _ = t._panner;
                _ ? (_.coneInnerAngle = d.coneInnerAngle, _.coneOuterAngle = d.coneOuterAngle, _.coneOuterGain = d.coneOuterGain, _.distanceModel = d.distanceModel, _.maxDistance = d.maxDistance, _.panningModel = d.panningModel, _.refDistance = d.refDistance, _.rolloffFactor = d.rolloffFactor) : (t._pos || (t._pos = r._pos || [0, 0, -.5]), e(t, "spatial")) }
        return r }, Sound.prototype.init = function(e) { return function() { var n = this,
                o = n._parent;
            n._orientation = o._orientation, n._stereo = o._stereo, n._pos = o._pos, n._pannerAttr = o._pannerAttr, e.call(this), n._stereo ? o.stereo(n._stereo) : n._pos && o.pos(n._pos[0], n._pos[1], n._pos[2], n._id) } }(Sound.prototype.init), Sound.prototype.reset = function(e) { return function() { var n = this,
                o = n._parent; return n._orientation = o._orientation, n._pos = o._pos, n._pannerAttr = o._pannerAttr, e.call(this) } }(Sound.prototype.reset); var e = function(e, n) { "spatial" === (n = n || "spatial") ? (e._panner = Howler.ctx.createPanner(), e._panner.coneInnerAngle = e._pannerAttr.coneInnerAngle, e._panner.coneOuterAngle = e._pannerAttr.coneOuterAngle, e._panner.coneOuterGain = e._pannerAttr.coneOuterGain, e._panner.distanceModel = e._pannerAttr.distanceModel, e._panner.maxDistance = e._pannerAttr.maxDistance, e._panner.panningModel = e._pannerAttr.panningModel, e._panner.refDistance = e._pannerAttr.refDistance, e._panner.rolloffFactor = e._pannerAttr.rolloffFactor, e._panner.setPosition(e._pos[0], e._pos[1], e._pos[2]), e._panner.setOrientation(e._orientation[0], e._orientation[1], e._orientation[2])) : (e._panner = Howler.ctx.createStereoPanner(), e._panner.pan.value = e._stereo), e._panner.connect(e._node), e._paused || e._parent.pause(e._id, !0).play(e._id) } }();
! function(e, t) { "object" == typeof exports && "undefined" != typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : e.kdCookie = t() }(this, function() { "use strict";

    function e() { t.call(this) }

    function t() { var e, t, o = document.cookie ? document.cookie.split(";") : [],
            r = {}; for (e = 0; e < o.length; e++) void 0 === r[(t = o[e].replace(/^\s+|\s+$/g, "").split("="))[0]] && (r[t[0]] = { name: t[0], value: t[1] });
        n = o.length, i = r } var i = {},
        n = 0; return e.prototype = { constructor: e, create: function(e, i, n) { if (!1 == !!e) return this; var o, r = e + "=" + i + ";"; return (n = n || {}).expires && ((o = new Date).setMilliseconds(o.getMilliseconds() + n.expires), r += "expires=" + o.toGMTString() + ";"), !!n.path && (r += "path=" + n.path + ";"), !!n.domain && (r += "domain=" + n.domain + ";"), !!n.secure && (r += "secure=" + n.secure + ";"), document.cookie = r, t.call(this), this }, get: function(e) { return 0 === n ? void 0 : !1 == !!e ? i : i[e] }, remove: function(e, t) { if (!1 == !!e || !1 == !!i[e]) return this; var n = { expires: -1 }; return !!(t = t || {}).domain && (n.domain = t.domain), !!t.path && (n.path = t.path), this.create(e, "", n) } }, new e });
! function() { "use strict";

    function e(e) { var n = '<div class="mask alert ab0 flex center middle">\n   <div>\n       <div>' + e + "</div>\n       <div class=\"clearfix\"><div class='confirm'>确定</div></div>\n   </div>\n</div>\n";
        n = $(n), $("body").append(n), n.redraw(function() { $(this).addClass("show") }), n.find(".confirm").one("click", function() { n.removeClass("show").addClass("hidden"), setTimeout(function() { n.remove() }, 200) }) } $(function(n) {
        function i() { /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) ? n(document).one("touchend", function() { u.play() }) : u.play() }

        function t() { g && clearInterval(g), a() }
        					//这里请求数据
        function a() { n.ajax("/home/Lhd/lhdList", { type: "POST", dataType: "json", cache: !1, success: function(e) { e && (s(e), l()), "block" === x.css("display") && v(e), r(e ? 1e3 * (parseInt(e.NextTime) - parseInt(e.ServerTime)) : 1e3) } }) }

        function s(e) { var i = n(".room");
            i.removeClass("found"), n.each(e.Data, function() { var t, a = this;
                i.each(function() { var i = n(this); if (i.find(".desk").text() === a.Desk) { if (t = !0, i.addClass("found"), i.find("b").text(d(a.State)), i.find(".title").text(c(a.MinBetMoney) + " - " + c(a.MaxBetMoney)), i.find(".color").attr("class", "color lv" + a.Level), !1 === a.IsEnable) i.find("span").remove();
                        else { i.find("span").length || n(".board").children().append("<span></span>"); var s = parseInt(a.EndTime) - parseInt(e.ServerTime);
                            s >= 0 && i.find("span").text(s) } return !1 } }), t || x.append(o(e, a).addClass("found")) }), i.not(".found").remove() }

        function o(i, t) { var a, s, o, l = !1 === t.IsEnable,
                r = t.DisplayName,
                v = d(t.State, l),
                u = t.MinBetMoney,
                f = t.MaxBetMoney,
                p = t.Level,
                m = t.Desk; return l ? (a = 0, s = 0) : (a = i.ServerTime, s = t.EndTime), o = '<div class="room">\n   <div class="wrapper">\n       <div class="highlight"></div>\n       <div class="man"></div>\n       <div class="title flex middle">' + c(u) + " - " + c(f) + '</div>\n       <div class="plate">\n           <div class="light"></div>\n           <div class="color lv' + p + '">\n               <div class="ltd flex center middle">&nbsp;</div>\n               <div class="line"></div>\n               <div class="desk flex center middle">' + r + '</div>\n           </div>\n       </div>\n       <div class="board">\n           <div class="flex center middle">\n               <i></i>\n               <i></i>\n               <b>' + v + "</b>\n               <span>" + (parseInt(s) - parseInt(a)) + "</span>\n           </div>\n       </div>\n   </div>\n</div>\n", o = n(o), l ? (o.find("span").remove(), o.children().on("click", function() { e("该房间已关闭") })) : o.children().on("click", function() { location.href = "/home/lhd/room.html?Desk=" + m }), o }

        function c(e) { var n; return (e = parseInt(e)) / 1e3 < 1 ? e.toString() : (e / 1e3 >= 1 && (n = e / 1e3 + "千"), e / 1e4 >= 1 && (n = e / 1e4 + "万"), e / 1e6 >= 1 && (n = e / 1e6 + "百万"), e / 1e7 >= 1 && (n = e / 1e7 + "千万"), e / 1e8 >= 1 && (n = e / 1e8 + "亿"), n) }

        function d(e, n) { var i; if (n) i = "房间已关闭";
            else switch (e) {
                case "Close":
                    i = "开牌中"; break;
                case "Bet":
                    i = "下注中"; break;
                case "Shuffle":
                    i = "洗牌中"; break;
                default:
                    i = e }
            return i }

        function l() { var e = n(".loading"); "block" === e.css("display") && (e.fadeOut(), x.fadeIn()) }

        function r(e, n) { var i = setTimeout(function() { clearTimeout(i), t() }, e);
            n && console.error(n) }

        function v(e) { e && !e.Data.length || (g = setInterval(function() { n(".room").each(function() { var i = n(this),
                        t = i.find("span"),
                        a = parseInt(t.text());
                    0 === a ? e && n.each(e.Data, function() { if (i.find(".desk").text() === this.DisplayName) { i.find("b").text(d(this.NextState)); var e = parseInt(this.NextEndTime) - parseInt(this.NextStartTime); return e >= 0 && t.text(e), !1 } }) : --a >= 0 && t.text(a) }) }, 1e3)) } var u = function(e) { return new Howl({ src: e, loop: !0 }) }(/iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase()) ? "/static/lhdMusicIos/bg.mp3" : "/static/lhdMusicIos/bg.mp3"),
            f = !1,
            p = "lhd.music",
            m = n("#music-switch"),
            h = kdCookie.get(p),
            target_GK = '#';
            targetGK();
            function targetGK(){
              $.post('/home/lhd/target',null,function(res){
                 target_GK = 'http://'+res+'/#/?n=1';
              })
            }

        h && "off" === h.value && (f = !0, m.removeClass("opened")), n.ajax({ url: "/home/lhd/userGet", type: "POST", dataType: "json", cache: !1, success: function(e) { e.S || 0 !== e.Voice_Open && 0 !== e.Bgm_Open || (f = !0, m.remove()), f ? (u.mute(!0), m.removeClass("opened")) : (u.mute(!1), m.addClass("opened")), i() } }); var g, x = n(".hall");
        n(".explainBtn").on("click", function() { var e = n('<div class="explain ab0 flex center middle">\n   <div class="wrapper">\n       <div class="header">规则说明</div>\n       <div class="body" id=gamerule>\n       </div>\n       <div class="footer">我知道了</div>\n   </div>\n</div>\n');
            n.ajax("/home/lhd/gameRule", { type: "POST", data: "{g: 'longhu'}", contentType: "application/json", success: function(n) { e.find("#gamerule").html(n) } }), n(this).velocity({ scale: .9 }, 100, "ease-in-out", function() { n(this).velocity("reverse") }), n("body").append(e), e.velocity({ backgroundColor: ["#000", "transparent"], backgroundColorAlpha: [.5, 0] }, 200, "ease-in-out"), e.children().velocity({ scale: [1, 1.2], opacity: [1, .5] }, 200, "ease-in-out"), e.find(".footer").one("click", function() { e.velocity("reverse", function() { n(this).remove() }), e.children().velocity({ scale: .8, opacity: 0 }, 200, "ease-in-out") }) }), m.off("click.musicswitch").on("click.musicswitch", function() { f ? (u.mute(!1), f = !1, n(this).addClass("opened"), kdCookie.remove(p)) : (u.mute(!0), f = !0, n(this).removeClass("opened"), kdCookie.create(p, "off", { path: "/" })) }), n(".back").one("click", function() { location.href = history.go(-1) }), t(), n(window).on("beforeunload", function() { u.stop() }) }), $.fn.extend({ redraw: function(e) { return this.hide(0, function() { $(this).show(0, e) }) } }) }();
