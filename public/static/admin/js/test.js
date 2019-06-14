webpackJsonp([21], {
	262: function(e, s, t) {
		var r = t(0)(t(392), null, null, null);
		e.exports = r.exports
	},
	340: function(e, s, t) {
		"use strict";

		function r() {}
		var a = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
			o = function(e) {
				e = e.replace(/\r\n/g, "\n");
				for (var s = "", t = 0; t < e.length; t++) {
					var r = e.charCodeAt(t);
					r < 128 ? s += String.fromCharCode(r) : r > 127 && r < 2048 ? (s += String.fromCharCode(r >> 6 | 192), s += String.fromCharCode(63 & r | 128)) : (s += String.fromCharCode(r >> 12 | 224), s += String.fromCharCode(r >> 6 & 63 | 128), s += String.fromCharCode(63 & r | 128))
				}
				return s
			},
			i = function(e) {
				for (var s = "", t = 0, r = 0, a = 0; t < e.length;) r = e.charCodeAt(t), r < 128 ? (s += String.fromCharCode(r), t++) : r > 191 && r < 224 ? (a = e.charCodeAt(t + 1), s += String.fromCharCode((31 & r) << 6 | 63 & a), t += 2) : (a = e.charCodeAt(t + 1), c3 = e.charCodeAt(t + 2), s += String.fromCharCode((15 & r) << 12 | (63 & a) << 6 | 63 & c3), t += 3);
				return s
			};
		r.prototype = {
			constructor: r,
			encode: function(e) {
				var s, t, r, i, n, c, d, m = "",
					u = 0;
				for (e = o(e); u < e.length;) s = e.charCodeAt(u++), t = e.charCodeAt(u++), r = e.charCodeAt(u++), i = s >> 2, n = (3 & s) << 4 | t >> 4, c = (15 & t) << 2 | r >> 6, d = 63 & r, isNaN(t) ? c = d = 64 : isNaN(r) && (d = 64), m = m + a.charAt(i) + a.charAt(n) + a.charAt(c) + a.charAt(d);
				return m
			},
			decode: function() {
				var e, s, t, r, o, n, c, d = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
					m = "",
					u = 0;
				for (d = d.replace(/[^A-Za-z0-9+\/\=]/g, ""); u < d.length;) r = a.indexOf(d.charAt(u++)), o = a.indexOf(d.charAt(u++)), n = a.indexOf(d.charAt(u++)), c = a.indexOf(d.charAt(u++)), e = r << 2 | o >> 4, s = (15 & o) << 4 | n >> 2, t = (3 & n) << 6 | c, m += String.fromCharCode(e), 64 != n && (m += String.fromCharCode(s)), 64 != c && (m += String.fromCharCode(t));
				return m = i(m)
			},
			multipleEncode: function(e, s) {
				for (; s > 0;) e = this.encode(e), s--;
				return e
			},
			multipleDecode: function(e, s) {
				for (; s > 0;) e = this.decode(e), s--;
				return e
			}
		}, s.a = new r
	},
	392: function(e, s, t) {
		"use strict";
		Object.defineProperty(s, "__esModule", {
			value: !0
		});
		var r = t(505),
			a = t.n(r);
		s.default = {
			extends: a.a
		}
	},
	429: function(e, s, t) {
		"use strict";
		Object.defineProperty(s, "__esModule", {
			value: !0
		});
		var r = t(340),
			a = t(107),
			o = t.n(a),
			i = t(34);
		s.default = {
			name: "PageAccountLogin",
			data: function() {
				return {
					user: {
						user_name: "",
						password: "",
						type: ""
					},
					agreed: !1,
					rememberedUserName: !0,
					rememberedPassword: !1,
					toRouterName: "Account"
				}
			},
			computed: {
				loginLogo: function() {
					return this.$store.state.loginLogo
				},
				device: function() {
					return this.$store.state.device
				}
			},
			created: function() {
				this.$store.commit("setPage", {
					pageTitle: "登录",
					className: "page-account-login"
				}), this.getUserFromCookie(), t.i(i.d)(document, "keyup", this.handleEnterUp)
			},
			beforeDestroy: function() {
				t.i(i.c)(document, "keyup", this.handleEnterUp)
			},
			methods: {
				setUser: function(e) {
					return this.$store.commit("uSetUser", e)
				},
				fetchUserInfo: function() {
					return this.$store.dispatch("uFetchUserInfo", {
						vm: this
					})
				},
				fetchSetting: function() {
					var e = this;
					return this.$store.dispatch("uFetchSetting").catch(function(s) {
						console.log(s), e.$message({
							message: "获取账号个性化设置出错啦！",
							type: "error",
							force: !0
						})
					})
				},
				login: function(e) {
					return this.$store.dispatch("uLogin", e)
				},
				validators: function() {
					return "" === this.user.user_name ? (this.$message({
						message: "用户名不能为空!",
						type: "warning"
					}), !1) : /^[a-zA-Z0-9]+$/.test(this.user.user_name) ? this.user.user_name.length > 20 || this.user.user_name.length < 2 ? (this.$message({
						message: "用户名长度必须在2-20个字符，<br>手机号码必须是11位标准号码！",
						type: "warning"
					}), !1) : "" === this.user.password ? (this.$message({
						message: "密码不能为空!",
						type: "warning"
					}), !1) : /[\s\t\n\r]+/g.test(this.user.password) ? (this.$message({
						message: "密码不能包含空格！",
						type: "warning"
					}), !1) : !(this.user.password.length > 20 || this.user.password.length < 6) || (this.$message({
						message: "密码长度只能是6-20个字符！",
						type: "warning"
					}), !1) : (this.$message({
						message: "用户名只能是数字、字母组合，<br>或者是手机号码！",
						type: "warning"
					}), !1)
				},
				doLogin: function() {
					var e = this;
					!1 !== this.validators() && (this.cacheUserIntoCookie(), this.setUser({
						isManualLogout: !1
					}), /AKAPP/i.test(window.navigator.userAgent) && ("android" === this.device ? this.user.type = "android" : "ios" === this.device && (this.user.type = "ios")), this.login({
						data: this.user
					}).then(function(s) {
						var t = s.S;
						if ("115" === t) e.$message({
							message: "115: 用户名或密码无效！",
							type: "warning"
						});
						else if ("119" === t) e.$message({
							message: "119: 密码无效！",
							type: "warning"
						});
						else if ("444" === t) e.$message({
							message: "444: 您已登录，如要更换账号，请先退出登录！",
							type: "warning"
						});
						else if ("116" === t) e.$message({
							message: "116: 用户名无效！",
							type: "warning"
						});
						else if ("117" === t) e.$message({
							message: "117: 账户已停用",
							type: "warning"
						});
						else if ("118" === t) e.$message({
							message: "118: 登录密码无效！",
							type: "warning"
						});
						else if ("171" === t) e.$message({
							message: "171: 超出账户一天无效登录次数，账户已锁定!",
							type: "warning"
						});
						else if ("172" === t) e.$message({
							message: "172: 超出单IP尝试次数!",
							type: "warning"
						});
						else if ("173" === t) e.$message({
							message: "173: 超出单IP尝试账户数!",
							type: "warning"
						});
						else if ("120" === t) {
							var r = e;
							e.$message({
								message: "恭喜你，登录成功！",
								type: "success"
							}), e.fetchUserInfo().then(function() {
								r.$route.query.redirect ? r.$router.push({
									path: r.$route.query.redirect
								}) : r.$router.push({
									name: r.toRouterName
								})
							}), e.fetchSetting()
						} else e.$message({
							message: t + ": " + s.D,
							type: "error"
						})
					}).catch(function(s) {
						console.log(s), e.$message({
							message: "登录出错啦！",
							type: "error",
							force: !0
						})
					}))
				},
				goToRegister: function() {
					this.$router.push({
						name: "AccountRegister"
					})
				},
				toggleToRemember: function(e) {
					"userName" === e ? this.rememberedUserName = !this.rememberedUserName : "password" === e && (this.rememberedPassword = !this.rememberedPassword)
				},
				getUserFromCookie: function() {
					var e = o.a.get("cachedUser");
					void 0 !== e && (e = unescape(e.value).split("|"), this.user.user_name = r.a.multipleDecode(e[0], 5), this.user.password = r.a.multipleDecode(e[1], 5))
				},
				setUserCookie: function(e, s) {
					o.a.create("cachedUser", escape([this.rememberedUserName ? r.a.multipleEncode(e, 5) : "", this.rememberedPassword ? r.a.multipleEncode(s, 5) : ""].join("|")), {
						expires: 6048e5
					})
				},
				cacheUserIntoCookie: function() {
					!1 === this.rememberedUserName && !1 === this.rememberedPassword ? o.a.remove("cachedUser") : this.setUserCookie(this.user.user_name, this.user.password)
				},
				handleEnterUp: function(e) {
					13 === e.keyCode && this.doLogin()
				}
			}
		}
	},
	505: function(e, s, t) {
		var r = t(0)(t(429), t(604), null, null);
		e.exports = r.exports
	},
	604: function(e, s) {
		e.exports = {
			render: function() {
				var e = this,
					s = e.$createElement,
					t = e._self._c || s;
				return t("main", {
					staticClass: "kd-body"
				}, [t("div", {
					staticClass: "logo-wrapper kd-center"
				}, [t("span", {
					staticClass: "logo"
				}, [e.loginLogo ? t("img", {
					staticClass: "custom-logo__img",
					attrs: {
						src: e.loginLogo
					}
				}) : t("i", {
					staticClass: "icon icon-logo"
				})])]), e._v(" "), t("form", {
					staticClass: "kd-form kd-container",
					on: {
						submit: function(e) {
							e.preventDefault()
						}
					}
				}, [t("div", {
					staticClass: "kd-field"
				}, [t("label", {
					staticClass: "kd-field-title",
					attrs: {
						for: "username"
					}
				}, [e._v("账号")]), e._v(" "), t("div", {
					directives: [{
						name: "textinput",
						rawName: "v-textinput"
					}],
					staticClass: "kd-textinput"
				}, [t("input", {
					directives: [{
						name: "model",
						rawName: "v-model.trim",
						value: e.user.user_name,
						expression: "user.user_name",
						modifiers: {
							trim: !0
						}
					}, {
						name: "focus-fixed",
						rawName: "v-focus-fixed"
					}],
					attrs: {
						type: "text",
						id: "username",
						placeholder: "用户名 / 手机号 / 用户ID"
					},
					domProps: {
						value: e.user.user_name
					},
					on: {
						input: function(s) {
							s.target.composing || (e.user.user_name = s.target.value.trim())
						},
						blur: function(s) {
							e.$forceUpdate()
						}
					}
				})])]), e._v(" "), t("div", {
					staticClass: "kd-field"
				}, [t("label", {
					staticClass: "kd-field-title",
					attrs: {
						for: "password"
					}
				}, [e._v("密码")]), e._v(" "), t("div", {
					directives: [{
						name: "textinput",
						rawName: "v-textinput"
					}],
					staticClass: "kd-textinput"
				}, [t("input", {
					directives: [{
						name: "model",
						rawName: "v-model",
						value: e.user.password,
						expression: "user.password"
					}, {
						name: "focus-fixed",
						rawName: "v-focus-fixed"
					}],
					attrs: {
						type: "password",
						id: "password",
						placeholder: "输入6-20位密码"
					},
					domProps: {
						value: e.user.password
					},
					on: {
						input: function(s) {
							s.target.composing || (e.user.password = s.target.value)
						}
					}
				})])]), e._v(" "), t("div", {
					staticClass: "kd-field kd-row kd-row-remembered"
				}, [t("div", {
					staticClass: "kd-col remember-user-name"
				}, [t("kd-checkbox", {
					attrs: {
						id: "remember-user-name"
					},
					model: {
						value: e.rememberedUserName,
						callback: function(s) {
							e.rememberedUserName = s
						},
						expression: "rememberedUserName"
					}
				}), e._v(" "), t("label", {
					attrs: {
						for: "remember-user-name"
					},
					on: {
						click: function(s) {
							e.toggleToRemember("userName")
						}
					}
				}, [e._v("记住账号")])], 1), e._v(" "), t("div", {
					staticClass: "kd-col remember-password kd-align-center"
				}, [t("kd-checkbox", {
					attrs: {
						id: "remember-password"
					},
					model: {
						value: e.rememberedPassword,
						callback: function(s) {
							e.rememberedPassword = s
						},
						expression: "rememberedPassword"
					}
				}), e._v(" "), t("label", {
					attrs: {
						for: "remember-password"
					},
					on: {
						click: function(s) {
							e.toggleToRemember("password")
						}
					}
				}, [e._v("记住密码")])], 1), e._v(" "), t("div", {
					staticClass: "kd-col kd-align-right"
				}, [t("router-link", {
					attrs: {
						to: "/forgot-password"
					}
				}, [e._v("忘记密码？")])], 1)]), e._v(" "), t("div", {
					staticClass: "kd-buttons"
				}, [t("kd-button", {
					staticClass: "main-button",
					on: {
						click: e.doLogin
					}
				}, [t("span", [e._v("马上登录")])]), e._v(" "), t("kd-button", {
					staticClass: "hollow",
					on: {
						click: e.goToRegister
					}
				}, [e._v("免费注册")])], 1)])])
			},
			staticRenderFns: []
		}
	}
});