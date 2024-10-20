!function (e, t) {
    "object" == typeof exports && "undefined" != typeof module ? t(require("jquery")) : "function" == typeof define && define.amd ? define(["jquery"], t) : t(e.jQuery)
}(this, function (e) {
    "use strict";

    function t(e, t) {
        return function (n, i) {
            var s, o = [];
            return t(i) && (s = k.splitTokens(k.split(n, e)), y.each(i, function (t, i) {
                var r = t.value;
                if (k.stringEncloses(n, r)) return !1;
                var a = k.splitTokens(k.split(r, e));
                0 === y.minus(s, a).length && o.push(i)
            })), 1 === o.length ? o[0] : -1
        }
    }

    function n(e, t) {
        var n = e.data && e.data[t];
        return n && new RegExp("^" + k.escapeRegExChars(n) + "([" + w + "]|$)", "i").test(e.value)
    }

    function i(e, t) {
        var n = /<strong>/;
        return n.test(t) && !n.test(e) ? t : e
    }

    function s(e, t, n, s, o) {
        var r = this;
        return i(r.highlightMatches(e, n, s, o), r.highlightMatches(t, n, s, o))
    }

    function o(e) {
        this.urlSuffix = e.toLowerCase(), this.noSuggestionsHint = "Неизвестное значение", this.matchers = [F.matchByNormalizedQuery(), F.matchByWords()]
    }

    function r(t, n) {
        var i = this;
        i.element = t, i.el = e(t), i.suggestions = [], i.badQueries = [], i.selectedIndex = -1, i.currentValue = i.element.value, i.intervalId = 0, i.cachedResponse = {}, i.enrichmentCache = {}, i.currentRequest = null, i.inputPhase = e.Deferred(), i.fetchPhase = e.Deferred(), i.enrichPhase = e.Deferred(), i.onChangeTimeout = null, i.triggering = {}, i.$wrapper = null, i.options = e.extend({}, V, n), i.classes = x, i.disabled = !1, i.selection = null, i.$viewport = e(window), i.$body = e(document.body), i.type = null, i.status = {}, i.setupElement(), i.initializer = e.Deferred(), i.el.is(":visible") ? i.initializer.resolve() : i.deferInitialization(), i.initializer.done(e.proxy(i.initialize, i))
    }

    function a() {
        L.each(G, function (e) {
            e.abort()
        }), G = {}
    }

    function u() {
        X = null, V.geoLocation = Y
    }

    function l(t) {
        return e.map(t, function (e) {
            var t = L.escapeHtml(e.text);
            return t && e.matched && (t = "<strong>" + t + "</strong>"), t
        }).join("")
    }

    function c(t, n) {
        var i = t.split(", ");
        return 1 === i.length ? t : e.map(i, function (e) {
            return '<span class="' + n + '">' + e + "</span>"
        }).join(", ")
    }

    function d(t, n) {
        var i = !1;
        return e.each(t, function (e, t) {
            if (i = t.value == n.value && t != n) return !1
        }), i
    }

    function f(e, t) {
        var n = t.selection, i = n && n.data && t.bounds;
        return i && y.each(t.bounds.all, function (t, s) {
            return i = n.data[t] === e.data[t]
        }), i
    }

    function p(e) {
        var t = e.replace(/^(\d{2})(\d*?)(0+)$/g, "$1$2"), n = t.length, i = -1;
        return n <= 2 ? i = 2 : n > 2 && n <= 5 ? i = 5 : n > 5 && n <= 8 ? i = 8 : n > 8 && n <= 11 ? i = 11 : n > 11 && n <= 15 ? i = 15 : n > 15 && (i = 19), k.padEnd(t, i, "0")
    }

    function g(e) {
        this.plan = e.status.plan;
        var t = e.getContainer();
        this.element = de.selectByClass(x.promo, t)
    }

    function h() {
        new g(this).show()
    }

    e = e && e.hasOwnProperty("default") ? e.default : e;
    var m = {
            isArray: function (e) {
                return Array.isArray(e)
            }, isFunction: function (e) {
                return "[object Function]" === Object.prototype.toString.call(e)
            }, isEmptyObject: function (e) {
                return 0 === Object.keys(e).length && e.constructor === Object
            }, isPlainObject: function (e) {
                return void 0 !== e && "object" == typeof e && null !== e && !e.nodeType && e !== e.window && !(e.constructor && !Object.prototype.hasOwnProperty.call(e.constructor.prototype, "isPrototypeOf"))
            }
        }, y = {
            compact: function (e) {
                return e.filter(function (e) {
                    return !!e
                })
            }, each: function (e, t) {
                if (Array.isArray(e)) return void e.some(function (e, n) {
                    return !1 === t(e, n)
                });
                Object.keys(e).some(function (n) {
                    var i = e[n];
                    return !1 === t(i, n)
                })
            }, intersect: function (e, t) {
                var n = [];
                return Array.isArray(e) && Array.isArray(t) ? e.filter(function (e) {
                    return -1 !== t.indexOf(e)
                }) : n
            }, minus: function (e, t) {
                return t && 0 !== t.length ? e.filter(function (e) {
                    return -1 === t.indexOf(e)
                }) : e
            }, makeArray: function (e) {
                return m.isArray(e) ? Array.prototype.slice.call(e) : [e]
            }, minusWithPartialMatching: function (e, t) {
                return t && 0 !== t.length ? e.filter(function (e) {
                    return !t.some(function (t) {
                        return 0 === t.indexOf(e)
                    })
                }) : e
            }, slice: function (e, t) {
                return Array.prototype.slice.call(e, t)
            }
        }, _ = {
            delay: function (e, t) {
                return setTimeout(e, t || 0)
            }
        }, v = {
            areSame: function e(t, n) {
                var i = !0;
                return typeof t == typeof n && ("object" == typeof t && null != t && null != n ? (y.each(t, function (t, s) {
                    return i = e(t, n[s])
                }), i) : t === n)
            }, assign: function (e, t) {
                if ("function" == typeof Object.assign) return Object.assign.apply(null, arguments);
                if (null == e) throw new TypeError("Cannot convert undefined or null to object");
                for (var n = Object(e), i = 1; i < arguments.length; i++) {
                    var s = arguments[i];
                    if (null != s) for (var o in s) Object.prototype.hasOwnProperty.call(s, o) && (n[o] = s[o])
                }
                return n
            }, clone: function (e) {
                return JSON.parse(JSON.stringify(e))
            }, compact: function (e) {
                var t = v.clone(e);
                return y.each(t, function (e, n) {
                    null !== e && void 0 !== e && "" !== e || delete t[n]
                }), t
            }, fieldsAreNotEmpty: function (e, t) {
                if (!m.isPlainObject(e)) return !1;
                var n = !0;
                return y.each(t, function (t, i) {
                    return n = !!e[t]
                }), n
            }, getDeepValue: function e(t, n) {
                var i = n.split("."), s = i.shift();
                return t && (i.length ? e(t[s], i.join(".")) : t[s])
            }, indexObjectsById: function (e, t, n) {
                var i = {};
                return y.each(e, function (e, s) {
                    var o = e[t], r = {};
                    n && (r[n] = s), i[o] = v.assign(r, e)
                }), i
            }
        }, b = {ENTER: 13, ESC: 27, TAB: 9, SPACE: 32, UP: 38, DOWN: 40}, x = {
            hint: "suggestions-hint",
            mobile: "suggestions-mobile",
            nowrap: "suggestions-nowrap",
            promo: "suggestions-promo",
            selected: "suggestions-selected",
            suggestion: "suggestions-suggestion",
            subtext: "suggestions-subtext",
            subtext_inline: "suggestions-subtext suggestions-subtext_inline",
            subtext_delimiter: "suggestions-subtext-delimiter",
            subtext_label: "suggestions-subtext suggestions-subtext_label",
            removeConstraint: "suggestions-remove",
            value: "suggestions-value"
        }, S = ".suggestions", w = "\\s\"'~\\*\\.,:\\|\\[\\]\\(\\)\\{\\}<>№", C = new RegExp("[" + w + "]+", "g"),
        E = new RegExp("[\\-\\+\\\\\\?!@#$%^&]+", "g"), k = {
            escapeHtml: function (e) {
                var t = {"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#x27;", "/": "&#x2F;"};
                return e && y.each(t, function (t, n) {
                    e = e.replace(new RegExp(n, "g"), t)
                }), e
            }, escapeRegExChars: function (e) {
                return e.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
            }, formatToken: function (e) {
                return e && e.toLowerCase().replace(/[ёЁ]/g, "е")
            }, getWordExtractorRegExp: function () {
                return new RegExp("([^" + w + "]*)([" + w + "]*)", "g")
            }, normalize: function (e, t) {
                return k.split(e, t).join(" ")
            }, padEnd: function (e, t, n) {
                return String.prototype.padEnd ? e.padEnd(t, n) : (t >>= 0, n = String(void 0 !== n ? n : " "), e.length > t ? String(e) : (t -= e.length, t > n.length && (n += n.repeat(t / n.length)), String(e) + n.slice(0, t)))
            }, split: function (e, t) {
                var n = e.toLowerCase().replace("ё", "е").replace(/(\d+)([а-я]{2,})/g, "$1 $2").replace(/([а-я]+)(\d+)/g, "$1 $2"),
                    i = y.compact(n.split(C));
                if (!i.length) return [];
                var s = i.pop(), o = y.minus(i, t);
                return o.push(s), o
            }, splitTokens: function (e) {
                var t = [];
                return y.each(e, function (e, n) {
                    var i = e.split(E);
                    t = t.concat(y.compact(i))
                }), t
            }, stringEncloses: function (e, t) {
                return e.length > t.length && -1 !== e.toLowerCase().indexOf(t.toLowerCase())
            }, tokenize: function (e, t) {
                var n = y.compact(k.formatToken(e).split(C)), i = y.minus(n, t), s = y.minus(n, i);
                return n = k.withSubTokens(i.concat(s))
            }, withSubTokens: function (e) {
                var t = [];
                return y.each(e, function (e, n) {
                    var i = e.split(E);
                    t.push(e), i.length > 1 && (t = t.concat(y.compact(i)))
                }), t
            }
        }, B = {
            Deferred: function () {
                return e.Deferred()
            }, ajax: function (t) {
                return e.ajax(t)
            }, extend: function () {
                return e.extend.apply(null, arguments)
            }, isJqObject: function (t) {
                return t instanceof e
            }, param: function (t) {
                return e.param(t)
            }, proxy: function (t, n) {
                return e.proxy(t, n)
            }, select: function (t) {
                return e(t)
            }, supportsCors: function () {
                return e.support.cors
            }
        }, T = {
            getDefaultType: function () {
                return B.supportsCors() ? "POST" : "GET"
            }, getDefaultContentType: function () {
                return B.supportsCors() ? "application/json" : "application/x-www-form-urlencoded"
            }, fixURLProtocol: function (e) {
                return B.supportsCors() ? e : e.replace(/^https?:/, location.protocol)
            }, addUrlParams: function (e, t) {
                return e + (/\?/.test(e) ? "&" : "?") + B.param(t)
            }, serialize: function (e) {
                return B.supportsCors() ? JSON.stringify(e, function (e, t) {
                    return null === t ? void 0 : t
                }) : (e = v.compact(e), B.param(e, !0))
            }
        }, j = function () {
            var e = 0;
            return function (t) {
                return (t || "") + ++e
            }
        }(), L = {
            escapeRegExChars: k.escapeRegExChars,
            escapeHtml: k.escapeHtml,
            formatToken: k.formatToken,
            normalize: k.normalize,
            reWordExtractor: k.getWordExtractorRegExp,
            stringEncloses: k.stringEncloses,
            addUrlParams: T.addUrlParams,
            getDefaultContentType: T.getDefaultContentType,
            getDefaultType: T.getDefaultType,
            fixURLProtocol: T.fixURLProtocol,
            serialize: T.serialize,
            arrayMinus: y.minus,
            arrayMinusWithPartialMatching: y.minusWithPartialMatching,
            arraysIntersection: y.intersect,
            compact: y.compact,
            each: y.each,
            makeArray: y.makeArray,
            slice: y.slice,
            delay: _.delay,
            areSame: v.areSame,
            compactObject: v.compact,
            getDeepValue: v.getDeepValue,
            fieldsNotEmpty: v.fieldsAreNotEmpty,
            indexBy: v.indexObjectsById,
            isArray: m.isArray,
            isEmptyObject: m.isEmptyObject,
            isFunction: m.isFunction,
            isPlainObject: m.isPlainObject,
            uniqueId: j
        }, V = {
            $helpers: null,
            autoSelectFirst: !1,
            containerClass: "suggestions-suggestions",
            count: 5,
            deferRequestBy: 100,
            enrichmentEnabled: !0,
            formatResult: null,
            formatSelected: null,
            headers: null,
            hint: "Выберите вариант или продолжите ввод",
            initializeInterval: 100,
            language: null,
            minChars: 1,
            mobileWidth: 600,
            noCache: !1,
            noSuggestionsHint: null,
            onInvalidateSelection: null,
            onSearchComplete: e.noop,
            onSearchError: e.noop,
            onSearchStart: e.noop,
            onSelect: null,
            onSelectNothing: null,
            onSuggestionsFetch: null,
            paramName: "query",
            params: {},
            preventBadQueries: !1,
            requestMode: "suggest",
            scrollOnFocus: !1,
            serviceUrl: "https://suggestions.dadata.ru/suggestions/api/4_1/rs",
            tabDisabled: !1,
            timeout: 3e3,
            triggerSelectOnBlur: !0,
            triggerSelectOnEnter: !0,
            triggerSelectOnSpace: !1,
            type: null,
            url: null
        }, O = function (e) {
            return function (t) {
                if (0 === t.length) return !1;
                if (1 === t.length) return !0;
                var n = e(t[0].value);
                return 0 === t.filter(function (t) {
                    return 0 !== e(t.value).indexOf(n)
                }).length
            }
        }(function (e) {
            return e
        }), F = {
            matchByNormalizedQuery: function (e) {
                return function (t, n) {
                    var i = k.normalize(t, e), s = [];
                    return y.each(n, function (n, o) {
                        var r = n.value.toLowerCase();
                        return !k.stringEncloses(t, r) && (!(r.indexOf(i) > 0) && void (i === k.normalize(r, e) && s.push(o)))
                    }), 1 === s.length ? s[0] : -1
                }
            }, matchByWords: function (e) {
                return t(e, O)
            }, matchByWordsAddress: function (e) {
                return t(e, O)
            }, matchByFields: function (e) {
                return function (t, n) {
                    var i = k.splitTokens(k.split(t)), s = [];
                    return 1 === n.length && (e && y.each(e, function (e, t) {
                        var i = v.getDeepValue(n[0], t), o = i && k.splitTokens(k.split(i, e));
                        o && o.length && (s = s.concat(o))
                    }), 0 === y.minusWithPartialMatching(i, s).length) ? 0 : -1
                }
            }
        },
        P = ["ао", "аобл", "дом", "респ", "а/я", "аал", "автодорога", "аллея", "арбан", "аул", "б-р", "берег", "бугор", "вал", "вл", "волость", "въезд", "высел", "г", "городок", "гск", "д", "двлд", "днп", "дор", "дп", "ж/д_будка", "ж/д_казарм", "ж/д_оп", "ж/д_платф", "ж/д_пост", "ж/д_рзд", "ж/д_ст", "жилзона", "жилрайон", "жт", "заезд", "заимка", "зона", "к", "казарма", "канал", "кв", "кв-л", "км", "кольцо", "комн", "кордон", "коса", "кп", "край", "линия", "лпх", "м", "массив", "местность", "мкр", "мост", "н/п", "наб", "нп", "обл", "округ", "остров", "оф", "п", "п/о", "п/р", "п/ст", "парк", "пгт", "пер", "переезд", "пл", "пл-ка", "платф", "погост", "полустанок", "починок", "пр-кт", "проезд", "промзона", "просек", "просека", "проселок", "проток", "протока", "проулок", "р-н", "рзд", "россия", "рп", "ряды", "с", "с/а", "с/мо", "с/о", "с/п", "с/с", "сад", "сквер", "сл", "снт", "спуск", "ст", "ст-ца", "стр", "тер", "тракт", "туп", "у", "ул", "уч-к", "ф/х", "ферма", "х", "ш", "бульвар", "владение", "выселки", "гаражно-строительный", "город", "деревня", "домовладение", "дорога", "квартал", "километр", "комната", "корпус", "литер", "леспромхоз", "местечко", "микрорайон", "набережная", "область", "переулок", "платформа", "площадка", "площадь", "поселение", "поселок", "проспект", "разъезд", "район", "республика", "село", "сельсовет", "слобода", "сооружение", "станица", "станция", "строение", "территория", "тупик", "улица", "улус", "участок", "хутор", "шоссе"],
        D = [{id: "kladr_id", fields: ["kladr_id"], forBounds: !1, forLocations: !0}, {
            id: "postal_code",
            fields: ["postal_code"],
            forBounds: !1,
            forLocations: !0
        }, {id: "country_iso_code", fields: ["country_iso_code"], forBounds: !1, forLocations: !0}, {
            id: "country",
            fields: ["country"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 0, zeros: 13},
            fiasType: "country_iso_code"
        }, {id: "region_iso_code", fields: ["region_iso_code"], forBounds: !1, forLocations: !0}, {
            id: "region_fias_id",
            fields: ["region_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "region_type_full",
            fields: ["region_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 2, zeros: 11},
            fiasType: "region_fias_id"
        }, {
            id: "region",
            fields: ["region", "region_type", "region_type_full", "region_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 2, zeros: 11},
            fiasType: "region_fias_id"
        }, {id: "area_fias_id", fields: ["area_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "area_type_full",
            fields: ["area_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 5, zeros: 8},
            fiasType: "area_fias_id"
        }, {
            id: "area",
            fields: ["area", "area_type", "area_type_full", "area_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 5, zeros: 8},
            fiasType: "area_fias_id"
        }, {id: "city_fias_id", fields: ["city_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "city_type_full",
            fields: ["city_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 8, zeros: 5},
            fiasType: "city_fias_id"
        }, {
            id: "city",
            fields: ["city", "city_type", "city_type_full", "city_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 8, zeros: 5},
            fiasType: "city_fias_id"
        }, {
            id: "city_district_fias_id",
            fields: ["city_district_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "city_district_type_full",
            fields: ["city_district_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "city_district_fias_id"
        }, {
            id: "city_district",
            fields: ["city_district", "city_district_type", "city_district_type_full", "city_district_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "city_district_fias_id"
        }, {
            id: "settlement_fias_id",
            fields: ["settlement_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "settlement_type_full",
            fields: ["settlement_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "settlement_fias_id"
        }, {
            id: "settlement",
            fields: ["settlement", "settlement_type", "settlement_type_full", "settlement_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "settlement_fias_id"
        }, {id: "street_fias_id", fields: ["street_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "street_type_full",
            fields: ["street_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "street_fias_id"
        }, {
            id: "street",
            fields: ["street", "street_type", "street_type_full", "street_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "street_fias_id"
        }, {
            id: "house",
            fields: ["house", "house_type", "house_type_full", "block", "block_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 19},
            fiasType: "house_fias_id"
        }, {
            id: "flat",
            fields: ["flat", "flat_type", "flat_type_full"],
            forBounds: !0,
            forLocations: !1,
            kladrFormat: {digits: 19},
            fiasType: "flat_fias_id"
        }], z = {
            urlSuffix: "address",
            noSuggestionsHint: "Неизвестный адрес",
            matchers: [F.matchByNormalizedQuery(P), F.matchByWordsAddress(P)],
            dataComponents: D,
            dataComponentsById: v.indexObjectsById(D, "id", "index"),
            unformattableTokens: P,
            enrichmentEnabled: !0,
            enrichmentMethod: "suggest",
            enrichmentParams: {count: 1, locations: null, locations_boost: null, from_bound: null, to_bound: null},
            getEnrichmentQuery: function (e) {
                return e.unrestricted_value
            },
            geoEnabled: !0,
            isDataComplete: function (e) {
                var t = [this.bounds.to || "flat"], n = e.data;
                return !m.isPlainObject(n) || v.fieldsAreNotEmpty(n, t)
            },
            composeValue: function (e, t) {
                var n = e.country,
                    i = e.region_with_type || y.compact([e.region, e.region_type]).join(" ") || e.region_type_full,
                    s = e.area_with_type || y.compact([e.area_type, e.area]).join(" ") || e.area_type_full,
                    o = e.city_with_type || y.compact([e.city_type, e.city]).join(" ") || e.city_type_full,
                    r = e.settlement_with_type || y.compact([e.settlement_type, e.settlement]).join(" ") || e.settlement_type_full,
                    a = e.city_district_with_type || y.compact([e.city_district_type, e.city_district]).join(" ") || e.city_district_type_full,
                    u = e.street_with_type || y.compact([e.street_type, e.street]).join(" ") || e.street_type_full,
                    l = y.compact([e.house_type, e.house, e.block_type, e.block]).join(" "),
                    c = y.compact([e.flat_type, e.flat]).join(" "), d = e.postal_box && "а/я " + e.postal_box;
                return i === o && (i = ""), t && t.saveCityDistrict || (t && t.excludeCityDistrict ? a = "" : a && !e.city_district_fias_id && (a = "")), y.compact([n, i, s, o, a, r, u, l, c, d]).join(", ")
            },
            formatResult: function () {
                var e = [], t = !1;
                return D.forEach(function (n) {
                    t && e.push(n.id), "city_district" === n.id && (t = !0)
                }), function (t, n, i, s) {
                    var o, r, a, u = this, l = i.data && i.data.city_district_with_type, c = s && s.unformattableTokens,
                        d = i.data && i.data.history_values;
                    return d && d.length > 0 && (o = k.tokenize(n, c), r = this.type.findUnusedTokens(o, t), (a = this.type.getFormattedHistoryValues(r, d)) && (t += a)), t = u.highlightMatches(t, n, i, s), t = u.wrapFormattedValue(t, i), l && (!u.bounds.own.length || u.bounds.own.indexOf("street") >= 0) && !m.isEmptyObject(u.copyDataComponents(i.data, e)) && (t += '<div class="' + u.classes.subtext + '">' + u.highlightMatches(l, n, i) + "</div>"), t
                }
            }(),
            findUnusedTokens: function (e, t) {
                return e.filter(function (e) {
                    return -1 === t.indexOf(e)
                })
            },
            getFormattedHistoryValues: function (e, t) {
                var n = [], i = "";
                return t.forEach(function (t) {
                    y.each(e, function (e) {
                        if (t.toLowerCase().indexOf(e) >= 0) return n.push(t), !1
                    })
                }), n.length > 0 && (i = " (бывш. " + n.join(", ") + ")"), i
            },
            getSuggestionValue: function (e, t) {
                var n = null;
                return t.hasSameValues ? n = e.options.restrict_value ? this.getValueWithinConstraints(e, t.suggestion) : e.bounds.own.length ? this.getValueWithinBounds(e, t.suggestion) : t.suggestion.unrestricted_value : t.hasBeenEnriched && e.options.restrict_value && (n = this.getValueWithinConstraints(e, t.suggestion, {excludeCityDistrict: !0})), n
            },
            getValueWithinConstraints: function (e, t, n) {
                return this.composeValue(e.getUnrestrictedData(t.data), n)
            },
            getValueWithinBounds: function (e, t, n) {
                var i = e.copyDataComponents(t.data, e.bounds.own.concat(["city_district_fias_id"]));
                return this.composeValue(i, n)
            }
        }, I = [{id: "kladr_id", fields: ["kladr_id"], forBounds: !1, forLocations: !0}, {
            id: "region_fias_id",
            fields: ["region_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "region_type_full",
            fields: ["region_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 2, zeros: 11},
            fiasType: "region_fias_id"
        }, {
            id: "region",
            fields: ["region", "region_type", "region_type_full", "region_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 2, zeros: 11},
            fiasType: "region_fias_id"
        }, {id: "area_fias_id", fields: ["area_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "area_type_full",
            fields: ["area_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 5, zeros: 8},
            fiasType: "area_fias_id"
        }, {
            id: "area",
            fields: ["area", "area_type", "area_type_full", "area_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 5, zeros: 8},
            fiasType: "area_fias_id"
        }, {id: "city_fias_id", fields: ["city_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "city_type_full",
            fields: ["city_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 8, zeros: 5},
            fiasType: "city_fias_id"
        }, {
            id: "city",
            fields: ["city", "city_type", "city_type_full", "city_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 8, zeros: 5},
            fiasType: "city_fias_id"
        }, {
            id: "city_district_fias_id",
            fields: ["city_district_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "city_district_type_full",
            fields: ["city_district_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "city_district_fias_id"
        }, {
            id: "city_district",
            fields: ["city_district", "city_district_type", "city_district_type_full", "city_district_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "city_district_fias_id"
        }, {
            id: "settlement_fias_id",
            fields: ["settlement_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "settlement_type_full",
            fields: ["settlement_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "settlement_fias_id"
        }, {
            id: "settlement",
            fields: ["settlement", "settlement_type", "settlement_type_full", "settlement_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 11, zeros: 2},
            fiasType: "settlement_fias_id"
        }, {
            id: "planning_structure_fias_id",
            fields: ["planning_structure_fias_id"],
            forBounds: !1,
            forLocations: !0
        }, {
            id: "planning_structure_type_full",
            fields: ["planning_structure_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "planning_structure_fias_id"
        }, {
            id: "planning_structure",
            fields: ["planning_structure", "planning_structure_type", "planning_structure_type_full", "planning_structure_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "planning_structure_fias_id"
        }, {id: "street_fias_id", fields: ["street_fias_id"], forBounds: !1, forLocations: !0}, {
            id: "street_type_full",
            fields: ["street_type_full"],
            forBounds: !1,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "street_fias_id"
        }, {
            id: "street",
            fields: ["street", "street_type", "street_type_full", "street_with_type"],
            forBounds: !0,
            forLocations: !0,
            kladrFormat: {digits: 15, zeros: 2},
            fiasType: "street_fias_id"
        }, {
            id: "house",
            fields: ["house", "house_type", "block", "building_type", "building"],
            forBounds: !0,
            forLocations: !1,
            kladrFormat: {digits: 19}
        }], R = {
            urlSuffix: "fias",
            noSuggestionsHint: "Неизвестный адрес",
            matchers: [F.matchByNormalizedQuery(P), F.matchByWordsAddress(P)],
            dataComponents: I,
            dataComponentsById: v.indexObjectsById(I, "id", "index"),
            unformattableTokens: P,
            isDataComplete: function (e) {
                var t = [this.bounds.to || "house"], n = e.data;
                return !m.isPlainObject(n) || v.fieldsAreNotEmpty(n, t)
            },
            composeValue: function (e, t) {
                var n = e.country,
                    i = e.region_with_type || y.compact([e.region, e.region_type]).join(" ") || e.region_type_full,
                    s = e.area_with_type || y.compact([e.area_type, e.area]).join(" ") || e.area_type_full,
                    o = e.city_with_type || y.compact([e.city_type, e.city]).join(" ") || e.city_type_full,
                    r = e.settlement_with_type || y.compact([e.settlement_type, e.settlement]).join(" ") || e.settlement_type_full,
                    a = e.city_district_with_type || y.compact([e.city_district_type, e.city_district]).join(" ") || e.city_district_type_full,
                    u = e.planning_structure_with_type || y.compact([e.planning_structure_type, e.planning_structure]).join(" ") || e.planning_structure_type_full,
                    l = e.street_with_type || y.compact([e.street_type, e.street]).join(" ") || e.street_type_full,
                    c = y.compact([e.house_type, e.house, e.block_type, e.block]).join(" "),
                    d = y.compact([e.flat_type, e.flat]).join(" "), f = e.postal_box && "а/я " + e.postal_box;
                return i === o && (i = ""), t && t.saveCityDistrict || (t && t.excludeCityDistrict ? a = "" : a && !e.city_district_fias_id && (a = "")), y.compact([n, i, s, o, a, r, u, l, c, d, f]).join(", ")
            },
            formatResult: function () {
                return function (e, t, n, i) {
                    var s = this;
                    return e = s.highlightMatches(e, t, n, i), e = s.wrapFormattedValue(e, n)
                }
            }(),
            getSuggestionValue: z.getSuggestionValue,
            getValueWithinConstraints: z.getValueWithinConstraints,
            getValueWithinBounds: z.getValueWithinBounds
        }, q = {
            urlSuffix: "fio",
            noSuggestionsHint: !1,
            matchers: [F.matchByNormalizedQuery(), F.matchByWords()],
            fieldNames: {surname: "фамилия", name: "имя", patronymic: "отчество"},
            isDataComplete: function (e) {
                var t, i = this, s = i.options.params, o = e.data;
                return m.isFunction(s) && (s = s.call(i.element, e.value)), s && s.parts ? t = s.parts.map(function (e) {
                    return e.toLowerCase()
                }) : (t = ["surname", "name"], n(e, "surname") && t.push("patronymic")), v.fieldsAreNotEmpty(o, t)
            },
            composeValue: function (e) {
                return y.compact([e.surname, e.name, e.patronymic]).join(" ")
            }
        }, A = {LEGAL: [2, 2, 5, 1], INDIVIDUAL: [2, 2, 6, 2]}, $ = {
            urlSuffix: "party",
            noSuggestionsHint: "Неизвестная организация",
            matchers: [F.matchByFields({value: null, "data.address.value": P, "data.inn": null, "data.ogrn": null})],
            dataComponents: D,
            enrichmentEnabled: !0,
            enrichmentMethod: "findById",
            enrichmentParams: {count: 1, locations_boost: null},
            getEnrichmentQuery: function (e) {
                return e.data.hid
            },
            geoEnabled: !0,
            formatResult: function (e, t, n, o) {
                var r = this, a = r.type.formatResultInn.call(r, n, t),
                    u = r.highlightMatches(v.getDeepValue(n.data, "ogrn"), t, n), l = i(a, u),
                    c = r.highlightMatches(v.getDeepValue(n.data, "management.name"), t, n),
                    d = v.getDeepValue(n.data, "address.value") || "";
                return r.isMobile && ((o || (o = {})).maxLength = 50), e = s.call(r, e, v.getDeepValue(n.data, "name.latin"), t, n, o), e = r.wrapFormattedValue(e, n), d && (d = d.replace(/^(\d{6}|Россия),\s+/i, ""), d = r.isMobile ? d.replace(new RegExp("^([^" + w + "]+[" + w + "]+[^" + w + "]+).*"), "$1") : r.highlightMatches(d, t, n, {unformattableTokens: P})), (l || d || c) && (e += '<div class="' + r.classes.subtext + '"><span class="' + r.classes.subtext_inline + '">' + (l || "") + "</span>" + (i(d, c) || "") + "</div>"), e
            },
            formatResultInn: function (e, t) {
                var n, i, s = this, o = e.data && e.data.inn, r = A[e.data && e.data.type], a = /\d/;
                if (o) return i = s.highlightMatches(o, t, e), r && (i = i.split(""), n = r.map(function (e) {
                    for (var t, n = ""; e && (t = i.shift());) n += t, a.test(t) && e--;
                    return n
                }), i = n.join('<span class="' + s.classes.subtext_delimiter + '"></span>') + i.join("")), i
            }
        }, N = {
            urlSuffix: "email",
            noSuggestionsHint: !1,
            matchers: [F.matchByNormalizedQuery()],
            isQueryRequestable: function (e) {
                return this.options.suggest_local || e.indexOf("@") >= 0
            }
        }, M = {
            urlSuffix: "bank",
            noSuggestionsHint: "Неизвестный банк",
            matchers: [F.matchByFields({value: null, "data.bic": null, "data.swift": null})],
            dataComponents: D,
            enrichmentEnabled: !0,
            enrichmentMethod: "findById",
            enrichmentParams: {count: 1},
            getEnrichmentQuery: function (e) {
                return e.data.bic
            },
            geoEnabled: !0,
            formatResult: function (e, t, n, i) {
                var s = this, o = s.highlightMatches(v.getDeepValue(n.data, "bic"), t, n),
                    r = v.getDeepValue(n.data, "address.value") || "";
                return e = s.highlightMatches(e, t, n, i), e = s.wrapFormattedValue(e, n), r && (r = r.replace(/^\d{6}( РОССИЯ)?, /i, ""), r = s.isMobile ? r.replace(new RegExp("^([^" + w + "]+[" + w + "]+[^" + w + "]+).*"), "$1") : s.highlightMatches(r, t, n, {unformattableTokens: P})), (o || r) && (e += '<div class="' + s.classes.subtext + '"><span class="' + s.classes.subtext_inline + '">' + o + "</span>" + r + "</div>"), e
            },
            formatSelected: function (e) {
                return v.getDeepValue(e, "data.name.payment") || null
            }
        }, W = {NAME: q, ADDRESS: z, FIAS: R, PARTY: $, EMAIL: N, BANK: M};
    W.get = function (e) {
        return W.hasOwnProperty(e) ? W[e] : new o(e)
    }, B.extend(V, {suggest_local: !0});
    var U = {
        chains: {}, on: function (e, t) {
            return this.get(e).push(t), this
        }, get: function (e) {
            var t = this.chains;
            return t[e] || (t[e] = [])
        }
    }, H = {
        suggest: {
            defaultParams: {
                type: L.getDefaultType(),
                dataType: "json",
                contentType: L.getDefaultContentType()
            }, addTypeInUrl: !0
        },
        "iplocate/address": {defaultParams: {type: "GET", dataType: "json"}, addTypeInUrl: !1},
        status: {defaultParams: {type: "GET", dataType: "json"}, addTypeInUrl: !0},
        findById: {
            defaultParams: {type: L.getDefaultType(), dataType: "json", contentType: L.getDefaultContentType()},
            addTypeInUrl: !0
        }
    }, Q = {
        suggest: {method: "suggest", userSelect: !0, updateValue: !0, enrichmentEnabled: !0},
        findById: {method: "findById", userSelect: !1, updateValue: !1, enrichmentEnabled: !1}
    };
    r.prototype = {
        initialize: function () {
            var e = this;
            e.uniqueId = L.uniqueId("i"), e.createWrapper(), e.notify("initialize"), e.bindWindowEvents(), e.setOptions(), e.inferIsMobile(), e.notify("ready")
        }, deferInitialization: function () {
            var e, t = this, n = "mouseover focus keydown", i = function () {
                t.initializer.resolve(), t.enable()
            };
            t.initializer.always(function () {
                t.el.off(n, i), clearInterval(e)
            }), t.disabled = !0, t.el.on(n, i), e = setInterval(function () {
                t.el.is(":visible") && i()
            }, t.options.initializeInterval)
        }, isInitialized: function () {
            return "resolved" === this.initializer.state()
        }, dispose: function () {
            var e = this;
            e.initializer.reject(), e.notify("dispose"), e.el.removeData("suggestions").removeClass("suggestions-input"), e.unbindWindowEvents(), e.removeWrapper(), e.el.trigger("suggestions-dispose")
        }, notify: function (t) {
            var n = this, i = L.slice(arguments, 1);
            return e.map(U.get(t), function (e) {
                return e.apply(n, i)
            })
        }, createWrapper: function () {
            var t = this;
            t.$wrapper = e('<div class="suggestions-wrapper"/>'), t.el.after(t.$wrapper), t.$wrapper.on("mousedown" + S, e.proxy(t.onMousedown, t))
        }, removeWrapper: function () {
            var t = this;
            t.$wrapper && t.$wrapper.remove(), e(t.options.$helpers).off(S)
        }, onMousedown: function (t) {
            var n = this;
            t.preventDefault(), n.cancelBlur = !0, L.delay(function () {
                delete n.cancelBlur
            }), 0 == e(t.target).closest(".ui-menu-item").length && L.delay(function () {
                e(document).one("mousedown", function (t) {
                    var i = n.el.add(n.$wrapper).add(n.options.$helpers);
                    n.options.floating && (i = i.add(n.$container)), i = i.filter(function () {
                        return this === t.target || e.contains(this, t.target)
                    }), i.length || n.hide()
                })
            })
        }, bindWindowEvents: function () {
            var t = e.proxy(this.inferIsMobile, this);
            this.$viewport.on("resize" + S + this.uniqueId, t)
        }, unbindWindowEvents: function () {
            this.$viewport.off("resize" + S + this.uniqueId)
        }, scrollToTop: function () {
            var t = this, n = t.options.scrollOnFocus;
            !0 === n && (n = t.el), n instanceof e && n.length > 0 && e("body,html").animate({scrollTop: n.offset().top}, "fast")
        }, setOptions: function (t) {
            var n = this;
            e.extend(n.options, t), n.type = W.get(n.options.type), e.each({requestMode: Q}, function (t, i) {
                if (n[t] = i[n.options[t]], !n[t]) throw n.disable(), "`" + t + "` option is incorrect! Must be one of: " + e.map(i, function (e, t) {
                    return '"' + t + '"'
                }).join(", ")
            }), e(n.options.$helpers).off(S).on("mousedown" + S, e.proxy(n.onMousedown, n)), n.isInitialized() && n.notify("setOptions")
        }, inferIsMobile: function () {
            this.isMobile = this.$viewport.width() <= this.options.mobileWidth
        }, clearCache: function () {
            this.cachedResponse = {}, this.enrichmentCache = {}, this.badQueries = []
        }, clear: function () {
            var e = this, t = e.selection;
            e.isInitialized() && (e.clearCache(), e.currentValue = "", e.selection = null, e.hide(), e.suggestions = [], e.el.val(""), e.el.trigger("suggestions-clear"), e.notify("clear"), e.trigger("InvalidateSelection", t))
        }, disable: function () {
            var e = this;
            e.disabled = !0, e.abortRequest(), e.visible && e.hide()
        }, enable: function () {
            this.disabled = !1
        }, isUnavailable: function () {
            return this.disabled
        }, update: function () {
            var e = this, t = e.el.val();
            e.isInitialized() && (e.currentValue = t, e.isQueryRequestable(t) ? e.updateSuggestions(t) : e.hide())
        }, setSuggestion: function (t) {
            var n, i, s = this;
            e.isPlainObject(t) && e.isPlainObject(t.data) && (t = e.extend(!0, {}, t), s.isUnavailable() && s.initializer && "pending" === s.initializer.state() && (s.initializer.resolve(), s.enable()), s.bounds.own.length && (s.checkValueBounds(t), n = s.copyDataComponents(t.data, s.bounds.all), t.data.kladr_id && (n.kladr_id = s.getBoundedKladrId(t.data.kladr_id, s.bounds.all)), t.data = n), s.selection = t, s.suggestions = [t], i = s.getSuggestionValue(t) || "", s.currentValue = i, s.el.val(i), s.abortRequest(), s.el.trigger("suggestions-set"))
        }, fixData: function () {
            var t = this, n = t.extendedCurrentValue(), i = t.el.val(), s = e.Deferred();
            s.done(function (e) {
                t.selectSuggestion(e, 0, i, {hasBeenEnriched: !0}), t.el.trigger("suggestions-fixdata", e)
            }).fail(function () {
                t.selection = null, t.el.trigger("suggestions-fixdata")
            }), t.isQueryRequestable(n) ? (t.currentValue = n, t.getSuggestions(n, {
                count: 1,
                from_bound: null,
                to_bound: null
            }).done(function (e) {
                var t = e[0];
                t ? s.resolve(t) : s.reject()
            }).fail(function () {
                s.reject()
            })) : s.reject()
        }, extendedCurrentValue: function () {
            var t = this, n = t.getParentInstance(), i = n && n.extendedCurrentValue(), s = e.trim(t.el.val());
            return L.compact([i, s]).join(" ")
        }, getAjaxParams: function (t, n) {
            var i = this, s = e.trim(i.options.token), o = e.trim(i.options.partner), a = i.options.serviceUrl,
                u = i.options.url, l = H[t], c = e.extend({timeout: i.options.timeout}, l.defaultParams), d = {};
            return u ? a = u : (/\/$/.test(a) || (a += "/"), a += t, l.addTypeInUrl && (a += "/" + i.type.urlSuffix)), a = L.fixURLProtocol(a), e.support.cors ? (s && (d.Authorization = "Token " + s), o && (d["X-Partner"] = o), d["X-Version"] = r.version, c.headers || (c.headers = {}), c.xhrFields || (c.xhrFields = {}), e.extend(c.headers, i.options.headers, d), c.xhrFields.withCredentials = !1) : (s && (d.token = s), o && (d.partner = o), d.version = r.version, a = L.addUrlParams(a, d)), c.url = a, e.extend(c, n)
        }, isQueryRequestable: function (e) {
            var t, n = this;
            return t = e.length >= n.options.minChars, t && n.type.isQueryRequestable && (t = n.type.isQueryRequestable.call(n, e)), t
        }, constructRequestParams: function (t, n) {
            var i = this, s = i.options,
                o = e.isFunction(s.params) ? s.params.call(i.element, t) : e.extend({}, s.params);
            return i.type.constructRequestParams && e.extend(o, i.type.constructRequestParams.call(i)), e.each(i.notify("requestParams"), function (t, n) {
                e.extend(o, n)
            }), o[s.paramName] = t, e.isNumeric(s.count) && s.count > 0 && (o.count = s.count), s.language && (o.language = s.language), e.extend(o, n)
        }, updateSuggestions: function (e) {
            var t = this;
            t.fetchPhase = t.getSuggestions(e).done(function (n) {
                t.assignSuggestions(n, e)
            })
        }, getSuggestions: function (t, n, i) {
            var s, o = this, r = o.options, a = i && i.noCallbacks, u = i && i.useEnrichmentCache,
                l = i && i.method || o.requestMode.method, c = o.constructRequestParams(t, n), d = e.param(c || {}),
                f = e.Deferred();
            return s = o.cachedResponse[d], s && e.isArray(s.suggestions) ? f.resolve(s.suggestions) : o.isBadQuery(t) ? f.reject() : a || !1 !== r.onSearchStart.call(o.element, c) ? o.doGetSuggestions(c, l).done(function (e) {
                o.processResponse(e) && t == o.currentValue ? (r.noCache || (u ? o.enrichmentCache[t] = e.suggestions[0] : (o.enrichResponse(e, t), o.cachedResponse[d] = e, r.preventBadQueries && 0 === e.suggestions.length && o.badQueries.push(t))), f.resolve(e.suggestions)) : f.reject(),
                a || r.onSearchComplete.call(o.element, t, e.suggestions)
            }).fail(function (e, n, i) {
                f.reject(), a || "abort" === n || r.onSearchError.call(o.element, t, e, n, i)
            }) : f.reject(), f
        }, doGetSuggestions: function (t, n) {
            var i = this, s = e.ajax(i.getAjaxParams(n, {data: L.serialize(t)}));
            return i.abortRequest(), i.currentRequest = s, i.notify("request"), s.always(function () {
                i.currentRequest = null, i.notify("request")
            }), s
        }, isBadQuery: function (t) {
            if (!this.options.preventBadQueries) return !1;
            var n = !1;
            return e.each(this.badQueries, function (e, i) {
                return !(n = 0 === t.indexOf(i))
            }), n
        }, abortRequest: function () {
            var e = this;
            e.currentRequest && e.currentRequest.abort()
        }, processResponse: function (t) {
            var n, i = this;
            return !(!t || !e.isArray(t.suggestions)) && (i.verifySuggestionsFormat(t.suggestions), i.setUnrestrictedValues(t.suggestions), e.isFunction(i.options.onSuggestionsFetch) && (n = i.options.onSuggestionsFetch.call(i.element, t.suggestions), e.isArray(n) && (t.suggestions = n)), !0)
        }, verifySuggestionsFormat: function (t) {
            "string" == typeof t[0] && e.each(t, function (e, n) {
                t[e] = {value: n, data: null}
            })
        }, getSuggestionValue: function (t, n) {
            var i, s = this, o = s.options.formatSelected || s.type.formatSelected, r = n && n.hasSameValues,
                a = n && n.hasBeenEnriched, u = null;
            return e.isFunction(o) && (i = o.call(s, t)), "string" != typeof i && (i = t.value, s.type.getSuggestionValue && null !== (u = s.type.getSuggestionValue(s, {
                suggestion: t,
                hasSameValues: r,
                hasBeenEnriched: a
            })) && (i = u)), i
        }, hasSameValues: function (t) {
            var n = !1;
            return e.each(this.suggestions, function (e, i) {
                if (i.value === t.value && i !== t) return n = !0, !1
            }), n
        }, assignSuggestions: function (e, t) {
            var n = this;
            n.suggestions = e, n.notify("assignSuggestions", t)
        }, shouldRestrictValues: function () {
            var e = this;
            return e.options.restrict_value && e.constraints && 1 == Object.keys(e.constraints).length
        }, setUnrestrictedValues: function (t) {
            var n = this, i = n.shouldRestrictValues(), s = n.getFirstConstraintLabel();
            e.each(t, function (e, t) {
                t.unrestricted_value || (t.unrestricted_value = i ? s + ", " + t.value : t.value)
            })
        }, areSuggestionsSame: function (e, t) {
            return e && t && e.value === t.value && L.areSame(e.data, t.data)
        }, getNoSuggestionsHint: function () {
            var e = this;
            return !1 !== e.options.noSuggestionsHint && (e.options.noSuggestionsHint || e.type.noSuggestionsHint)
        }
    };
    var K = {
        setupElement: function () {
            this.el.attr("autocomplete", "new-password").attr("autocorrect", "off").attr("autocapitalize", "off").attr("spellcheck", "false").addClass("suggestions-input").css("box-sizing", "border-box")
        }, bindElementEvents: function () {
            var t = this;
            t.el.on("keydown" + S, e.proxy(t.onElementKeyDown, t)), t.el.on(["keyup" + S, "cut" + S, "paste" + S, "input" + S].join(" "), e.proxy(t.onElementKeyUp, t)), t.el.on("blur" + S, e.proxy(t.onElementBlur, t)), t.el.on("focus" + S, e.proxy(t.onElementFocus, t))
        }, unbindElementEvents: function () {
            this.el.off(S)
        }, onElementBlur: function () {
            var e = this;
            if (e.cancelBlur) return void (e.cancelBlur = !1);
            e.options.triggerSelectOnBlur ? e.isUnavailable() || e.selectCurrentValue({noSpace: !0}).always(function () {
                e.hide()
            }) : e.hide(), e.fetchPhase.abort && e.fetchPhase.abort()
        }, onElementFocus: function () {
            var t = this;
            t.cancelFocus || L.delay(e.proxy(t.completeOnFocus, t)), t.cancelFocus = !1
        }, onElementKeyDown: function (e) {
            var t = this;
            if (!t.isUnavailable()) if (t.visible) {
                switch (e.which) {
                    case b.ESC:
                        t.el.val(t.currentValue), t.hide(), t.abortRequest();
                        break;
                    case b.TAB:
                        if (!1 === t.options.tabDisabled) return;
                        break;
                    case b.ENTER:
                        t.options.triggerSelectOnEnter && t.selectCurrentValue();
                        break;
                    case b.SPACE:
                        return void (t.options.triggerSelectOnSpace && t.isCursorAtEnd() && (e.preventDefault(), t.selectCurrentValue({
                            continueSelecting: !0,
                            dontEnrich: !0
                        }).fail(function () {
                            t.currentValue += " ", t.el.val(t.currentValue), t.proceedChangedValue()
                        })));
                    case b.UP:
                        t.moveUp();
                        break;
                    case b.DOWN:
                        t.moveDown();
                        break;
                    default:
                        return
                }
                e.stopImmediatePropagation(), e.preventDefault()
            } else switch (e.which) {
                case b.DOWN:
                    t.suggest();
                    break;
                case b.ENTER:
                    t.options.triggerSelectOnEnter && t.triggerOnSelectNothing()
            }
        }, onElementKeyUp: function (e) {
            var t = this;
            if (!t.isUnavailable()) {
                switch (e.which) {
                    case b.UP:
                    case b.DOWN:
                    case b.ENTER:
                        return
                }
                clearTimeout(t.onChangeTimeout), t.inputPhase.reject(), t.currentValue !== t.el.val() && t.proceedChangedValue()
            }
        }, proceedChangedValue: function () {
            var t = this;
            t.abortRequest(), t.inputPhase = e.Deferred().done(e.proxy(t.onValueChange, t)), t.options.deferRequestBy > 0 ? t.onChangeTimeout = L.delay(function () {
                t.inputPhase.resolve()
            }, t.options.deferRequestBy) : t.inputPhase.resolve()
        }, onValueChange: function () {
            var e, t = this;
            t.selection && (e = t.selection, t.selection = null, t.trigger("InvalidateSelection", e)), t.selectedIndex = -1, t.update(), t.notify("valueChange")
        }, completeOnFocus: function () {
            var e = this;
            e.isUnavailable() || e.isElementFocused() && (e.update(), e.isMobile && (e.setCursorAtEnd(), e.scrollToTop()))
        }, isElementFocused: function () {
            return document.activeElement === this.element
        }, isElementDisabled: function () {
            return Boolean(this.element.getAttribute("disabled") || this.element.getAttribute("readonly"))
        }, isCursorAtEnd: function () {
            var e, t, n = this, i = n.el.val().length;
            try {
                if ("number" == typeof (e = n.element.selectionStart)) return e === i
            } catch (e) {
            }
            return !document.selection || (t = document.selection.createRange(), t.moveStart("character", -i), i === t.text.length)
        }, setCursorAtEnd: function () {
            var e = this.element;
            try {
                e.selectionEnd = e.selectionStart = e.value.length, e.scrollLeft = e.scrollWidth
            } catch (t) {
                e.value = e.value
            }
        }
    };
    e.extend(r.prototype, K), U.on("initialize", K.bindElementEvents).on("dispose", K.unbindElementEvents);
    var G = {};
    a();
    var J = {
        checkStatus: function () {
            function e(e) {
                L.isFunction(t.options.onSearchError) && t.options.onSearchError.call(t.element, null, s, "error", e)
            }

            var t = this, n = t.options.token && t.options.token.trim() || "", i = t.options.type + n, s = G[i];
            s || (s = G[i] = B.ajax(t.getAjaxParams("status"))), s.done(function (n, i, s) {
                if (n.search) {
                    var o = s.getResponseHeader("X-Plan");
                    n.plan = o, B.extend(t.status, n)
                } else e("Service Unavailable")
            }).fail(function () {
                e(s.statusText)
            })
        }
    };
    r.resetTokens = a, B.extend(r.prototype, J), U.on("setOptions", J.checkStatus);
    var X, Y = !0, Z = {
        checkLocation: function () {
            var t = this, n = t.options.geoLocation;
            t.type.geoEnabled && n && (t.geoLocation = e.Deferred(), e.isPlainObject(n) || e.isArray(n) ? t.geoLocation.resolve(n) : (X || (X = e.ajax(t.getAjaxParams("iplocate/address"))), X.done(function (e) {
                var n = e && e.location && e.location.data;
                n && n.kladr_id ? t.geoLocation.resolve({kladr_id: n.kladr_id}) : t.geoLocation.reject()
            }).fail(function () {
                t.geoLocation.reject()
            })))
        }, getGeoLocation: function () {
            return this.geoLocation
        }, constructParams: function () {
            var t = this, n = {};
            return t.geoLocation && e.isFunction(t.geoLocation.promise) && "resolved" == t.geoLocation.state() && t.geoLocation.done(function (t) {
                n.locations_boost = e.makeArray(t)
            }), n
        }
    };
    "GET" != L.getDefaultType() && (e.extend(V, {geoLocation: Y}), e.extend(r, {resetLocation: u}), e.extend(r.prototype, {getGeoLocation: Z.getGeoLocation}), U.on("setOptions", Z.checkLocation).on("requestParams", Z.constructParams));
    var ee = {
        enrichSuggestion: function (t, n) {
            var i = this, s = e.Deferred();
            if (!i.options.enrichmentEnabled || !i.type.enrichmentEnabled || !i.requestMode.enrichmentEnabled || n && n.dontEnrich) return s.resolve(t);
            if (t.data && null != t.data.qc) return s.resolve(t);
            i.disableDropdown();
            var o = i.type.getEnrichmentQuery(t), r = i.type.enrichmentParams,
                a = {noCallbacks: !0, useEnrichmentCache: !0, method: i.type.enrichmentMethod};
            return i.currentValue = o, i.enrichPhase = i.getSuggestions(o, r, a).always(function () {
                i.enableDropdown()
            }).done(function (e) {
                var n = e && e[0];
                s.resolve(n || t, !!n)
            }).fail(function () {
                s.resolve(t)
            }), s
        }, enrichResponse: function (t, n) {
            var i = this, s = i.enrichmentCache[n];
            s && e.each(t.suggestions, function (e, i) {
                if (i.value === n) return t.suggestions[e] = s, !1
            })
        }
    };
    e.extend(r.prototype, ee);
    var te = {width: "auto", floating: !1}, ne = {
        createContainer: function () {
            var t = this, n = "." + t.classes.suggestion, i = t.options,
                s = e("<div/>").addClass(i.containerClass).css({display: "none"});
            t.$container = s, s.on("click" + S, n, e.proxy(t.onSuggestionClick, t))
        }, showContainer: function () {
            this.$container.appendTo(this.options.floating ? this.$body : this.$wrapper)
        }, getContainer: function () {
            return this.$container.get(0)
        }, removeContainer: function () {
            var e = this;
            e.options.floating && e.$container.remove()
        }, setContainerOptions: function () {
            var t = this;
            t.$container.off("mousedown.suggestions"), t.options.floating && t.$container.on("mousedown.suggestions", e.proxy(t.onMousedown, t))
        }, onSuggestionClick: function (t) {
            var n, i = this, s = e(t.target);
            if (!i.dropdownDisabled) {
                for (i.cancelFocus = !0, i.el.focus(); s.length && !(n = s.attr("data-index"));) s = s.closest("." + i.classes.suggestion);
                n && !isNaN(n) && i.select(+n)
            }
        }, getSuggestionsItems: function () {
            return this.$container.children("." + this.classes.suggestion)
        }, toggleDropdownEnabling: function (e) {
            this.dropdownDisabled = !e, this.$container.attr("disabled", !e)
        }, disableDropdown: function () {
            this.toggleDropdownEnabling(!1)
        }, enableDropdown: function () {
            this.toggleDropdownEnabling(!0)
        }, hasSuggestionsToChoose: function () {
            var t = this;
            return t.suggestions.length > 1 || 1 === t.suggestions.length && (!t.selection || e.trim(t.suggestions[0].value) !== e.trim(t.selection.value))
        }, suggest: function () {
            var t = this, n = t.options, i = [];
            if (t.requestMode.userSelect) {
                if (t.hasSuggestionsToChoose()) n.hint && t.suggestions.length && i.push('<div class="' + t.classes.hint + '">' + n.hint + "</div>"), t.selectedIndex = -1, t.suggestions.forEach(function (e, n) {
                    e == t.selection && (t.selectedIndex = n), t.buildSuggestionHtml(e, n, i)
                }); else {
                    if (t.suggestions.length) return void t.hide();
                    var s = t.getNoSuggestionsHint();
                    if (!s) return void t.hide();
                    i.push('<div class="' + t.classes.hint + '">' + s + "</div>")
                }
                i.push('<div class="' + x.promo + '"></div>'), i.push("</div>"), t.$container.html(i.join("")), n.autoSelectFirst && -1 === t.selectedIndex && (t.selectedIndex = 0), -1 !== t.selectedIndex && t.getSuggestionsItems().eq(t.selectedIndex).addClass(t.classes.selected), e.isFunction(n.beforeRender) && n.beforeRender.call(t.element, t.$container), t.$container.show(), t.visible = !0
            }
        }, buildSuggestionHtml: function (e, t, n) {
            n.push('<div class="' + this.classes.suggestion + '" data-index="' + t + '">');
            var i = this.options.formatResult || this.type.formatResult || this.formatResult;
            n.push(i.call(this, e.value, this.currentValue, e, {unformattableTokens: this.type.unformattableTokens}));
            var s = this.makeSuggestionLabel(this.suggestions, e);
            s && n.push('<span class="' + this.classes.subtext_label + '">' + L.escapeHtml(s) + "</span>"), n.push("</div>")
        }, wrapFormattedValue: function (e, t) {
            var n = this, i = L.getDeepValue(t.data, "state.status");
            return '<span class="' + n.classes.value + '"' + (i ? ' data-suggestion-status="' + i + '"' : "") + ">" + e + "</span>"
        }, formatResult: function (e, t, n, i) {
            var s = this;
            return e = s.highlightMatches(e, t, n, i), s.wrapFormattedValue(e, n)
        }, highlightMatches: function (t, n, i, s) {
            var o, r, a, u, d, f, p, g = this, h = [], m = s && s.unformattableTokens, y = s && s.maxLength,
                _ = L.reWordExtractor();
            if (!t) return "";
            for (o = k.tokenize(n, m), r = e.map(o, function (e) {
                return new RegExp("^((.*)([\\-\\+\\\\\\?!@#$%^&]+))?(" + L.escapeRegExChars(e) + ")([^\\-\\+\\\\\\?!@#$%^&]*[\\-\\+\\\\\\?!@#$%^&]*)", "i")
            }); (a = _.exec(t)) && a[0];) u = a[1], h.push({
                text: u,
                hasUpperCase: u.toLowerCase() !== u,
                formatted: L.formatToken(u),
                matchable: !0
            }), a[2] && h.push({text: a[2]});
            for (d = 0; d < h.length; d++) f = h[d], !f.matchable || f.matched || -1 !== e.inArray(f.formatted, m) && !f.hasUpperCase || e.each(r, function (e, t) {
                var n, i = t.exec(f.formatted), s = d + 1;
                if (i) return i = {
                    before: i[1] || "",
                    beforeText: i[2] || "",
                    beforeDelimiter: i[3] || "",
                    text: i[4] || "",
                    after: i[5] || ""
                }, i.before && (h.splice(d, 0, {
                    text: f.text.substr(0, i.beforeText.length),
                    formatted: i.beforeText,
                    matchable: !0
                }, {text: i.beforeDelimiter}), s += 2, n = i.before.length, f.text = f.text.substr(n), f.formatted = f.formatted.substr(n), d--), n = i.text.length + i.after.length, f.formatted.length > n && (h.splice(s, 0, {
                    text: f.text.substr(n),
                    formatted: f.formatted.substr(n),
                    matchable: !0
                }), f.text = f.text.substr(0, n), f.formatted = f.formatted.substr(0, n)), i.after && (n = i.text.length, h.splice(s, 0, {
                    text: f.text.substr(n),
                    formatted: f.formatted.substr(n)
                }), f.text = f.text.substr(0, n), f.formatted = f.formatted.substr(0, n)), f.matched = !0, !1
            });
            if (y) {
                for (d = 0; d < h.length && y >= 0; d++) f = h[d], (y -= f.text.length) < 0 && (f.text = f.text.substr(0, f.text.length + y) + "...");
                h.length = d
            }
            return p = l(h), c(p, g.classes.nowrap)
        }, makeSuggestionLabel: function (t, n) {
            var i, s, o = this, r = o.type.fieldNames, a = {}, u = L.reWordExtractor(), l = [];
            if (r && d(t, n) && n.data && (e.each(r, function (e) {
                var t = n.data[e];
                t && (a[e] = L.formatToken(t))
            }), !e.isEmptyObject(a))) {
                for (; (i = u.exec(L.formatToken(n.value))) && (s = i[1]);) e.each(a, function (e, t) {
                    if (t == s) return l.push(r[e]), delete a[e], !1
                });
                if (l.length) return l.join(", ")
            }
        }, hide: function () {
            var e = this;
            e.visible = !1, e.selectedIndex = -1, e.$container.hide().empty()
        }, activate: function (e) {
            var t, n, i = this, s = i.classes.selected;
            return !i.dropdownDisabled && (n = i.getSuggestionsItems(), n.removeClass(s), i.selectedIndex = e, -1 !== i.selectedIndex && n.length > i.selectedIndex) ? (t = n.eq(i.selectedIndex), t.addClass(s), t) : null
        }, deactivate: function (e) {
            var t = this;
            t.dropdownDisabled || (t.selectedIndex = -1, t.getSuggestionsItems().removeClass(t.classes.selected), e && t.el.val(t.currentValue))
        }, moveUp: function () {
            var e = this;
            if (!e.dropdownDisabled) return -1 === e.selectedIndex ? void (e.suggestions.length && e.adjustScroll(e.suggestions.length - 1)) : 0 === e.selectedIndex ? void e.deactivate(!0) : void e.adjustScroll(e.selectedIndex - 1)
        }, moveDown: function () {
            var e = this;
            if (!e.dropdownDisabled) return e.selectedIndex === e.suggestions.length - 1 ? void e.deactivate(!0) : void e.adjustScroll(e.selectedIndex + 1)
        }, adjustScroll: function (e) {
            var t, n, i, s = this, o = s.activate(e), r = s.$container.scrollTop();
            o && o.length && (t = o.position().top, t < 0 ? s.$container.scrollTop(r + t) : (n = t + o.outerHeight(), i = s.$container.innerHeight(), n > i && s.$container.scrollTop(r - i + n)), s.el.val(s.suggestions[e].value))
        }
    };
    e.extend(V, te), e.extend(r.prototype, ne), U.on("initialize", ne.createContainer).on("dispose", ne.removeContainer).on("setOptions", ne.setContainerOptions).on("ready", ne.showContainer).on("assignSuggestions", ne.suggest);
    var ie = {constraints: null, restrict_value: !1},
        se = ["country_iso_code", "region_iso_code", "region_fias_id", "area_fias_id", "city_fias_id", "city_district_fias_id", "settlement_fias_id", "planning_structure_fias_id", "street_fias_id"],
        oe = function (e, t) {
            var n, i, s = this, o = {};
            s.instance = t, s.fields = {}, s.specificity = -1, m.isPlainObject(e) && t.type.dataComponents && y.each(t.type.dataComponents, function (t, n) {
                var i = t.id;
                t.forLocations && e[i] && (s.fields[i] = e[i], s.specificity = n)
            }), n = Object.keys(s.fields), i = y.intersect(n, se), i.length ? (y.each(i, function (e, t) {
                o[e] = s.fields[e]
            }), s.fields = o, s.specificity = s.getFiasSpecificity(i)) : s.fields.kladr_id && (s.fields = {kladr_id: s.fields.kladr_id}, s.significantKladr = p(s.fields.kladr_id), s.specificity = s.getKladrSpecificity(s.significantKladr))
        };
    B.extend(oe.prototype, {
        getLabel: function () {
            return this.instance.type.composeValue(this.fields, {saveCityDistrict: !0})
        }, getFields: function () {
            return this.fields
        }, isValid: function () {
            return !m.isEmptyObject(this.fields)
        }, getKladrSpecificity: function (e) {
            var t = -1, n = e.length;
            return y.each(this.instance.type.dataComponents, function (e, i) {
                e.kladrFormat && n === e.kladrFormat.digits && (t = i)
            }), t
        }, getFiasSpecificity: function (e) {
            var t = -1;
            return y.each(this.instance.type.dataComponents, function (n, i) {
                n.fiasType && e.indexOf(n.fiasType) > -1 && t < i && (t = i)
            }), t
        }, containsData: function (e) {
            var t = !0;
            return this.fields.kladr_id ? !!e.kladr_id && 0 === e.kladr_id.indexOf(this.significantKladr) : (y.each(this.fields, function (n, i) {
                return t = !!e[i] && e[i].toLowerCase() === n.toLowerCase()
            }), t)
        }
    }), r.ConstraintLocation = oe;
    var re = function (e, t) {
        this.id = j("c"), this.deletable = !!e.deletable, this.instance = t;
        var n = y.makeArray(e && (e.locations || e.restrictions));
        this.locations = n.map(function (e) {
            return new oe(e, t)
        }), this.locations = this.locations.filter(function (e) {
            return e.isValid()
        }), this.label = e.label, null == this.label && t.type.composeValue && (this.label = this.locations.map(function (e) {
            return e.getLabel()
        }).join(", "))
    };
    B.extend(re.prototype, {
        isValid: function () {
            return this.locations.length > 0
        }, getFields: function () {
            return this.locations.map(function (e) {
                return e.getFields()
            })
        }
    });
    var ae = {
        createConstraints: function () {
            this.constraints = {}
        }, setupConstraints: function () {
            var e, t = this, n = t.options.constraints;
            if (!n) return void t.unbindFromParent();
            B.isJqObject(n) || "string" == typeof n || "number" == typeof n.nodeType ? (e = B.select(n), e.is(t.constraints) || (t.unbindFromParent(), e.is(t.el) || (t.constraints = e, t.bindToParent()))) : (y.each(t.constraints, function (e, n) {
                t.removeConstraint(n)
            }), y.each(y.makeArray(n), function (e, n) {
                t.addConstraint(e)
            }))
        }, filteredLocation: function (e) {
            var t = [], n = {};
            if (y.each(this.type.dataComponents, function () {
                this.forLocations && t.push(this.id)
            }), m.isPlainObject(e) && y.each(e, function (e, i) {
                e && t.indexOf(i) >= 0 && (n[i] = e)
            }), !m.isEmptyObject(n)) return n.kladr_id ? {kladr_id: n.kladr_id} : n
        }, addConstraint: function (e) {
            var t = this;
            e = new re(e, t), e.isValid() && (t.constraints[e.id] = e)
        }, removeConstraint: function (e) {
            delete this.constraints[e]
        }, constructConstraintsParams: function () {
            for (var e, t, n = this, i = [], s = n.constraints, o = {}; B.isJqObject(s) && (e = s.suggestions()) && !(t = v.getDeepValue(e, "selection.data"));) s = e.constraints;
            return B.isJqObject(s) ? (t = new oe(t, e).getFields()) && (n.bounds.own.indexOf("city") > -1 && delete t.city_fias_id, o.locations = [t], o.restrict_value = !0) : s && (y.each(s, function (e, t) {
                i = i.concat(e.getFields())
            }), i.length && (o.locations = i, o.restrict_value = n.options.restrict_value)), o
        }, getFirstConstraintLabel: function () {
            var e = this, t = m.isPlainObject(e.constraints) && Object.keys(e.constraints)[0];
            return t ? e.constraints[t].label : ""
        }, bindToParent: function () {
            var e = this;
            e.constraints.on(["suggestions-select." + e.uniqueId, "suggestions-invalidateselection." + e.uniqueId, "suggestions-clear." + e.uniqueId].join(" "), B.proxy(e.onParentSelectionChanged, e)).on("suggestions-dispose." + e.uniqueId, B.proxy(e.onParentDispose, e))
        }, unbindFromParent: function () {
            var e = this, t = e.constraints;
            B.isJqObject(t) && t.off("." + e.uniqueId)
        }, onParentSelectionChanged: function (e, t, n) {
            ("suggestions-select" !== e.type || n) && this.clear()
        }, onParentDispose: function (e) {
            this.unbindFromParent()
        }, getParentInstance: function () {
            return B.isJqObject(this.constraints) && this.constraints.suggestions()
        }, shareWithParent: function (e) {
            var t = this.getParentInstance();
            t && t.type === this.type && !f(e, t) && (t.shareWithParent(e), t.setSuggestion(e))
        }, getUnrestrictedData: function (e) {
            var t = this, n = [], i = {}, s = -1;
            return y.each(t.constraints, function (t, n) {
                y.each(t.locations, function (t, n) {
                    t.containsData(e) && t.specificity > s && (s = t.specificity)
                })
            }), s >= 0 ? (e.region_kladr_id && e.region_kladr_id === e.city_kladr_id && n.push.apply(n, t.type.dataComponentsById.city.fields), y.each(t.type.dataComponents.slice(0, s + 1), function (e, t) {
                n.push.apply(n, e.fields)
            }), y.each(e, function (e, t) {
                -1 === n.indexOf(t) && (i[t] = e)
            })) : i = e, i
        }
    };
    B.extend(V, ie), B.extend(r.prototype, ae), "GET" != T.getDefaultType() && U.on("initialize", ae.createConstraints).on("setOptions", ae.setupConstraints).on("requestParams", ae.constructConstraintsParams).on("dispose", ae.unbindFromParent);
    var ue = {
        proceedQuery: function (e) {
            var t = this;
            e.length >= t.options.minChars ? t.updateSuggestions(e) : t.hide()
        }, selectCurrentValue: function (e) {
            var t = this, n = B.Deferred();
            return t.inputPhase.resolve(), t.fetchPhase.done(function () {
                var i;
                t.selection && !t.visible ? n.reject() : (i = t.findSuggestionIndex(), t.select(i, e), -1 === i ? n.reject() : n.resolve(i))
            }).fail(function () {
                n.reject()
            }), n
        }, selectFoundSuggestion: function () {
            var e = this;
            e.requestMode.userSelect || e.select(0)
        }, findSuggestionIndex: function () {
            var e, t = this, n = t.selectedIndex;
            return -1 === n && (e = t.el.val().trim()) && t.type.matchers.some(function (i) {
                return -1 !== (n = i(e, t.suggestions))
            }), n
        }, select: function (e, t) {
            var n, i = this, s = i.suggestions[e], o = t && t.continueSelecting, r = i.currentValue;
            if (!i.triggering.Select) {
                if (!s) return o || i.selection || i.triggerOnSelectNothing(), void i.onSelectComplete(o);
                n = i.hasSameValues(s), i.enrichSuggestion(s, t).done(function (s, o) {
                    var a = B.extend({hasBeenEnriched: o, hasSameValues: n}, t);
                    i.selectSuggestion(s, e, r, a)
                })
            }
        }, selectSuggestion: function (e, t, n, i) {
            var s = this, o = i.continueSelecting, r = !s.type.isDataComplete || s.type.isDataComplete.call(s, e),
                a = s.selection;
            s.triggering.Select || (s.type.alwaysContinueSelecting && (o = !0), r && (o = !1), i.hasBeenEnriched && s.suggestions[t] && (s.suggestions[t].data = e.data), s.requestMode.updateValue && (s.checkValueBounds(e), s.currentValue = s.getSuggestionValue(e, i), !s.currentValue || i.noSpace || r || (s.currentValue += " "), s.el.val(s.currentValue)), s.currentValue ? (s.selection = e, s.areSuggestionsSame(e, a) || s.trigger("Select", e, s.currentValue != n), s.requestMode.userSelect && s.onSelectComplete(o)) : (s.selection = null, s.triggerOnSelectNothing()), s.shareWithParent(e))
        }, onSelectComplete: function (e) {
            var t = this;
            e ? (t.selectedIndex = -1, t.updateSuggestions(t.currentValue)) : t.hide()
        }, triggerOnSelectNothing: function () {
            var e = this;
            e.triggering.SelectNothing || e.trigger("SelectNothing", e.currentValue)
        }, trigger: function (e) {
            var t = this, n = L.slice(arguments, 1), i = t.options["on" + e];
            t.triggering[e] = !0, L.isFunction(i) && i.apply(t.element, n), t.el.trigger.call(t.el, "suggestions-" + e.toLowerCase(), n), t.triggering[e] = !1
        }
    };
    B.extend(r.prototype, ue), U.on("assignSuggestions", ue.selectFoundSuggestion);
    var le = {bounds: null}, ce = {
        setupBounds: function () {
            this.bounds = {from: null, to: null}
        }, setBoundsOptions: function () {
            var t, n = this, i = [], s = e.trim(n.options.bounds).split("-"), o = s[0], r = s[s.length - 1], a = [],
                u = [];
            n.type.dataComponents && e.each(n.type.dataComponents, function () {
                this.forBounds && i.push(this.id)
            }), -1 === i.indexOf(o) && (o = null), -1 === i.indexOf(r) && (r = null), (o || r) && (t = !o, e.each(i, function (e, n) {
                if (n == o && (t = !0), u.push(n), t && a.push(n), n == r) return !1
            })), n.bounds.from = o, n.bounds.to = r, n.bounds.all = u, n.bounds.own = a
        }, constructBoundsParams: function () {
            var e = this, t = {};
            return e.bounds.from && (t.from_bound = {value: e.bounds.from}), e.bounds.to && (t.to_bound = {value: e.bounds.to}), t
        }, checkValueBounds: function (e) {
            var t, n = this;
            if (n.bounds.own.length && n.type.composeValue) {
                var i = n.bounds.own.slice(0);
                1 === i.length && "city_district" === i[0] && i.push("city_district_fias_id"), t = n.copyDataComponents(e.data, i), e.value = n.type.composeValue(t)
            }
        }, copyDataComponents: function (t, n) {
            var i = {}, s = this.type.dataComponentsById;
            return s && e.each(n, function (n, o) {
                e.each(s[o].fields, function (e, n) {
                    null != t[n] && (i[n] = t[n])
                })
            }), i
        }, getBoundedKladrId: function (t, n) {
            var i, s = n[n.length - 1];
            return e.each(this.type.dataComponents, function (e, t) {
                if (t.id === s) return i = t.kladrFormat, !1
            }), t.substr(0, i.digits) + new Array((i.zeros || 0) + 1).join("0")
        }
    };
    e.extend(V, le), e.extend(r.prototype, ce), U.on("initialize", ce.setupBounds).on("setOptions", ce.setBoundsOptions).on("requestParams", ce.constructBoundsParams);
    var de = {
        selectByClass: function (e, t) {
            var n = "." + e;
            return t ? t.querySelector(n) : document.querySelector(n)
        }, addClass: function (e, t) {
            var n = e.className.split(" ");
            -1 === n.indexOf(t) && n.push(t), e.className = n.join(" ")
        }, setStyle: function (e, t, n) {
            e.style[t] = n
        }, listenTo: function (e, t, n, i) {
            e.addEventListener(t, i, !1), n && (eventsByNamespace[n] || (eventsByNamespace[n] = []), eventsByNamespace[n].push({
                eventName: t,
                element: e,
                callback: i
            }))
        }, stopListeningNamespace: function (e) {
            var t = eventsByNamespace[e];
            t && t.forEach(function (e) {
                e.element.removeEventListener(e.eventName, e.callback, !1)
            })
        }
    };
    g.prototype.show = function () {
        "FREE" === this.plan && this.element && (this.setStyles(), this.setHtml())
    }, g.prototype.setStyles = function () {
        this.element.style.display = "block"
    }, g.prototype.setHtml = function () {
        this.element.innerHTML = '<a target="_blank" tabindex="-1" href="https://dadata.ru/suggestions/?utm_source=dadata&utm_medium=module&utm_campaign=suggestions-jquery"><svg version="1.1" viewBox="0 0 128 38" xmlns="http://www.w3.org/2000/svg"><path d="m128 19v16.077c0 1.614-1.302 2.923-2.909 2.923h-122.18c-1.607 0-2.909-1.309-2.909-2.923v-32.154c-0-1.614 1.302-2.923 2.909-2.923h122.18c1.607 0 2.909 1.309 2.909 2.923z" fill="#ef4741"/><path d="m59.52 7.912h-8.902v22.098h9.92c3.724 0 9.872-0.341 9.872-6.703v-8.682c-0.01-6.372-7.166-6.713-10.89-6.713zm5.595 14.81c0 3.186-2.308 3.508-4.936 3.508h-4.276v-14.538h3.287c2.628 0 5.954 0.322 5.954 3.508zm-46.545-14.81h-8.834v22.098h9.871c3.724 0 9.872-0.341 9.872-6.703v-8.682c0-6.372-7.137-6.713-10.88-6.713zm5.595 14.81c0 3.186-2.308 3.508-4.936 3.508h-4.247v-14.538h3.258c2.628 0 5.954 0.322 5.954 3.508zm71.757-13.953h-4.945v16.301c-0.018 0.785 0.113 1.565 0.388 2.3 0.203 0.569 0.535 1.082 0.97 1.5 0.446 0.385 0.962 0.677 1.522 0.858 0.58 0.205 1.182 0.343 1.794 0.409 0.575 0.052 1.248 0.081 2.017 0.088 0.917-1e-3 1.834-0.057 2.744-0.166v-2.796h-1.765c-0.393 0.055-0.795 0.032-1.18-0.071-0.385-0.102-0.745-0.28-1.06-0.524-0.413-0.691-0.59-1.498-0.504-2.299v-8.068h4.509v-3.06h-4.509zm20.364 5.535c-1.176-0.741-3.278-1.108-6.303-1.101h-5.741v0.243l0.708 2.826h5.033c0.837-0.051 1.672 0.117 2.424 0.487 0.259 0.248 0.458 0.553 0.579 0.891 0.121 0.339 0.162 0.701 0.119 1.058v1.12h-5.527c-1.939 0-3.271 0.38-3.995 1.14-0.725 0.76-1.099 2.127-1.125 4.102 0 2.154 0.359 3.06 1.086 3.742 0.728 0.682 2.134 1.188 4.344 1.188h6.847c1.706 0 3.345-0.808 3.345-2.747v-8.584c0-2.176-0.589-3.635-1.765-4.375zm-3.19 12.959h-3.249c-0.735 0.081-1.478-0.036-2.152-0.342-0.285-0.227-0.427-0.876-0.427-1.948s0.136-1.741 0.407-2.007c0.625-0.331 1.336-0.46 2.037-0.371h3.384zm-26.667-12.959c-1.176-0.741-3.277-1.108-6.303-1.101h-5.741v0.243l0.708 2.826h5.033c0.836-0.051 1.672 0.117 2.424 0.487 0.259 0.248 0.457 0.553 0.578 0.891 0.121 0.339 0.162 0.701 0.12 1.058v1.12h-5.556c-1.939 0-3.271 0.38-3.995 1.14s-1.086 2.127-1.086 4.102c0 2.154 0.359 3.06 1.086 3.742s2.133 1.188 4.344 1.188h6.846c1.717 0 3.346-0.808 3.346-2.747v-8.584c-7e-3 -2.176-0.595-3.635-1.765-4.375zm-3.181 12.959h-3.248c-0.735 0.081-1.478-0.037-2.153-0.342-0.284-0.227-0.426-0.876-0.426-1.948s0.135-1.741 0.407-2.007c0.624-0.331 1.336-0.46 2.036-0.371h3.384zm-37.74-12.959c-1.176-0.741-3.278-1.108-6.303-1.101h-5.712v0.243l0.708 2.826h5.033c0.837-0.051 1.672 0.117 2.424 0.487 0.259 0.248 0.457 0.553 0.578 0.891 0.121 0.339 0.162 0.701 0.12 1.058v1.12h-5.556c-1.939 0-3.271 0.38-3.995 1.14s-1.099 2.127-1.125 4.102c0 2.154 0.359 3.06 1.086 3.742s2.133 1.188 4.344 1.188h6.846c1.717 0 3.346-0.808 3.346-2.747v-8.584c0-2.176-0.589-3.635-1.765-4.375zm-3.181 12.959h-3.219c-0.735 0.081-1.478-0.037-2.153-0.342-0.284-0.227-0.427-0.876-0.427-1.948s0.136-1.741 0.408-2.007c0.624-0.331 1.336-0.46 2.036-0.371h3.384z" fill="#fff"/></svg></a>'
    }, U.on("assignSuggestions", h), r.defaultOptions = V, r.version = "21.12.0", e.Suggestions = r, e.fn.suggestions = function (t, n) {
        return 0 === arguments.length ? this.first().data("suggestions") : this.each(function () {
            var i = e(this), s = i.data("suggestions");
            "string" == typeof t ? s && "function" == typeof s[t] && s[t](n) : (s && s.dispose && s.dispose(), s = new r(this, t), i.data("suggestions", s))
        })
    }
});