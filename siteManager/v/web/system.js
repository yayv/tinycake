window.User = (function () {
	var user = HLT.cache.get('user') || {};
	var status = user.status || '';
	return {
		isUsing: status == '使用中',
		isStop: status == '已停用',
		isOther: status == '其他'
	}
})();

window.Pages = {};

Pages.dashboard = {
	template: HLT.getTpl('#dashboard'),
	data: function () {
		return {
			name: '',
			num: {
				D10Flow: 0,
				todayCommission: 0,
				todayFlow: 0,
				todayIncome: 0,
				totalCommission: 0,
				totalIncome: 0,
				totalScore: 0
			}
		};
	},
	mounted: function () {
		var Services = window.Services;
		var _this = this;

		Services.dashboard(function (data) {
			_this.num = data;
      _this.name = data.name;
      _this.$parent.name = data.name;
			_this.$parent.totalScore = data.totalScore;
		});
	}
}

Pages.device = {
	template: HLT.getTpl('#device'),
	data: function () {
		return {
			lines: [],
			isSub: false
			// store: {}
		}
	},
	updated: function () {
		this.isSub = location.href.indexOf('/device/') > -1;
	},
	mounted: function () {
		//load data here;
		var _this = this;
		window.Services.getLines(function (data) {
			// var data = {"lines":[
			// {"id":1,"name":"\u56fa\u5b9a\u7ebf\u8def000000","score":"1111","payoff":"\u6bcf\u65e502:00","yestodayIncome":"123","onlineTime":"2019-01-01 01:01:11","status":"\u5728\u7ebf"},
			// {"id":2,"name":"\u56fa\u5b9a\u7ebf\u8def000000","score":"1111","payoff":"\u6bcf\u65e502:00","yestodayIncome":"123","onlineTime":"2019-01-01 01:01:11","status":"离线"}
			// ]};	
			_this.lines = data.lines || [];
			// _this.store = data.store || {};
		});
	},
	methods: {
		thisOrder: function (line) {
			// alert('待增加线路互动页，line.id=' + line.id);
			main.$router.push('/device/detail');
			window.currentLine = line;
		}
	}
}

Pages.deviceDetail = {
	template: HLT.getTpl('#deviceDetail'),
	data: function () {
    var limited = this.$parent.$parent.totalScore;
    // var limited = 200;
    var account = this.$parent.$parent.name
    var line = window.currentLine || {};
    var online = line.status == '在线';
		return {
			line: line,
      score4charge: '',
      tempScore: limited, // 可用余额
      limited: limited,
      lineScore: line.score,
      account: account, // 账号
      isOnline: online
		}
  },
  watch: {
    'score4charge': function(n,o) {
      this.checknum();
      this.tempScore = this.limited - this.score4charge;
    }
  },
	methods: {
    // 回收
		recycle2score: function () {
			var _this = this;
			var line = _this.line;
			// console.log("line:");
			// console.log(line);
			window.Services.recycle2score({
				"lineId": line.id
			}, function (data) {
        window.Services.dashboard(function (data) {
          _this.$parent.$parent.totalScore = data.totalScore;
          _this.limited = data.totalScore;
          MINT.Toast({
            message: '回收成功',
            position: 'middle',
            duration: 2000
          });
        });
			});
		},
		checknum: function () {
			var limited = this.limited;
			var score4charge = this.score4charge / 1;
			score4charge = Math.max(score4charge, 1);
			if (score4charge > limited) {
				score4charge = limited;
			}
			this.score4charge = score4charge;
    },
    // 充值
		charge: function () {
			var _this = this;
			var line = _this.line;
			var point = _this.score4charge;
			window.Services.charge4Line({
				"lineId": line.id,
				"value": point,
				"point": point,
				"score": point
			}, function (data) {
_this.lineScore = _this.lineScore + _this.limited - _this.tempScore;				
        _this.limited = _this.tempScore;
        _this.score4charge = '';
        _this.$parent.$parent.totalScore = _this.tempScore;

				MINT.Toast({
					message: '充值成功',
					position: 'top',
					duration: 2000
				});
			});
    },
    // 上线
		online: function () {
      var _this = this;
      if (this.isOnline) {
        return false;
      }
			window.Services.turnOnTheLine({
				"lineId": _this.line.id
			}, function (data) {
        _this.isOnline = !_this.isOnline;
				_this.line.status = '在线';
			});
		}
  }
};

Pages.aip = {
	template: HLT.getTpl('#aip'),
	data: function () {
		var limited = this.$parent.totalScore;
		return {
			limited: limited,
			store: {
				score: 0,
				lineName: "未激活",
				status: '未激活',
				score4charge: limited
			}
		}
	},
	mounted: function () {
		var _this = this;
		var limited = this.$parent.totalScore;
		window.Services.aip(function (data) {
			// var data = {"store":{"name":"", "status":"未激活", "级别":"10002","今日收益":"","button":"上线|充值"}};
			var store = data.store || {};
			store.score = store.score || 0;
			store.status = store.status || '未激活';
			store.score4charge = limited;

			_this.store = store;
		});

	},
	methods: {
		onlineStore: function (store) {
			store.status = '激活';
			window.Services.turnOnStore({
				"lineId": store.id
			}, function (data) {});
		},
		charge: function (store) {
			var limited = this.limited;
			var point = Math.min(store.score4charge / 1, limited / 1);
			point = Math.max(1, point);

			this.store.score4charge = point;

			window.Services.charge4Equity({
				"value": point
			}, function (data) {

			});
		}
	}
}

Pages.order = {
	template: HLT.getTpl('#order'),
	data: function () {
		return {
			orders: []
		}
	},
	mounted: function () {
		//load data here;
		var _this = this;
		var orderId = HLT.cache.get('orderId');

		window.Services.getOrders(function (data) {
			// var data = [{"id":"1","value":"194","createTime":"2019-09-11 11:13:03","lineId":"1","lineNo":"NDWK-5K41BS8M8","userId":"1","matchTime":"2019-09-11 11:13:06"},{"id":"2","value":"175","createTime":"2019-09-11 11:13:06","lineId":"1","lineNo":"NDWK-5K41BS8M8","userId":"1","matchTime":"2019-09-11 11:13:09"},{"id":"3","value":"178","createTime":"2019-09-11 11:13:07","lineId":"1","lineNo":"NDWK-5K41BS8M8","userId":"1","matchTime":"2019-09-11 11:13:10"},{"id":"4","value":"149","createTime":"2019-09-11 11:13:08","lineId":"1","lineNo":"NDWK-5K41BS8M8","userId":"1","matchTime":"2019-09-11 11:13:11"}];

			if (orderId != '') {
				// alert('filter')
				_this.orders = _.filter(data, function (item) {
					return item.lineId == orderId;
				});
			} else {
				// alert('all')
				_this.orders = data;
			}
		});
	},
	methods: {
		isHighlight: function (status) {
			return status == '已成交' || status == '待付款' || false;
		}
	}
}

Pages.mine = {
	template: HLT.getTpl('#mine'),
	data: function () {
		return {
			isSub: false,
			num: {
				D10Flow: 0,
				todayCommission: 0,
				todayFlow: 0,
				todayIncome: 0,
				totalCommission: 0,
				totalIncome: 0,
				totalScore: 0
			}
		}
	},
	updated: function () {
		this.isSub = location.href.indexOf('/mine/') > -1;
	},
	mounted: function () {
		var Services = window.Services;
		var _this = this;
		Services.dashboard(function (data) {
			_this.num = data;
			_this.name = data.name;
			_this.$parent.totalScore = data.totalScore;
		});
	},
	methods: {}
}

Pages.mineAudit = {
	template: HLT.getTpl('#audit'),
	data: function () {
		return {
			mine: {
				name: '',
				idcard: '',
				photo1: '',
				photo2: ''
			},
			isUsing: window.User.isUsing
		}
	},
	updated: function () {
		var cache = HLT.cache;

		cache.set('mine', this.mine);
	},
	mounted: function () {
		//load data here;
		var _this = this;
		var cache = HLT.cache;

		var mine = cache.get('mine');
		if (mine) {
			this.mine = mine;
		}

		window.Services.getPersonalInfo(function (data) {
			console.log(data)
			// MINT.Toast({
			// 	message: '获取用户信息失败',
			// 	position: 'top',
			// 	duration: 2000
			// });
			_this.mine = data;
			var invites = data.invites || 0;
			HLT.cache.set('invites', invites);
		});
	},
	methods: {
		isNotValid: function (json) {
			return HLT.isEmptyJson(json);
		},
		upload: function (type, event) {
			var _this = this;
			var node = event.target;
			HLT.getLocalImageData(node.files[0], function (base64) {
				var length = base64.length;
				var fileLength = parseInt(length - (length / 8) * 2);
				if (fileLength > 1024 * 1024) {
					MINT.Toast({
						message: '图片超出大小（请上传小于1M的图片）',
						position: 'center',
						duration: 2000
					});
					return false
				}
				_this.mine[type] = base64;
				// console.log(_this.mine);
			});
		},
		save: function () {
			var data = this.mine;
			//post to server
			window.Services.setPersonalInfo(data, function (data) {
				MINT.Toast({
					message: '上传保存成功',
					position: 'top',
					duration: 2000
				});
			});
		}
	}
};


Pages.mineProfile = {
	template: HLT.getTpl('#profile'),
	data: function () {
		return {
			psw: {
				oldpassword: '',
				newpassword: ''
			},
			cashPSW: {
				loginpass: '',
				oldpassword: '',
				newpassword: ''
			},
			phone: {
				number: ''
			}
		}
	},
	methods: {
		isNotValid: function (json) {
			return HLT.isEmptyJson(json);
		},
		setPassword: function () {
			var data = this.psw;
			//post to server
			if (data.oldpassword == data.newpassword) {
				MINT.Toast({
					message: '新密码不能与旧密码相同',
					position: 'top',
					duration: 2000
				});
				return;
			}
			window.Services.setPassword(data, function (data) {
				MINT.Toast({
					message: '修改密码成功',
					position: 'top',
					duration: 2000
				});
			});
		},
		updateMobile: function () {
			var data = this.phone;
			//post to server
			window.Services.updateMobile(data, function (data) {
				MINT.Toast({
					message: '修改手机号码成功',
					position: 'middle',
					duration: 2000
				});
			});
		},
		updateCashoutPassword: function () {
			var data = this.cashPSW;
			//post to server
			if (data.oldpassword == data.newpassword) {
				MINT.Toast({
					message: '新密码不能与旧密码相同',
					position: 'bottom',
					duration: 2000
				});
				return;
			}
			window.Services.updateCashoutPassword(data, function (data) {
				MINT.Toast({
					message: '修改提现密码成功',
					position: 'bottom',
					duration: 2000
				});
			});
		}
	}
};


Pages.mineCharge = {
	template: HLT.getTpl('#charge'),
	data: function () {
		return {
			charge: 50000,
			number: '',
			// chargeList: [100,200,500,1000,2000,3000,5000,10000,20000,30000,40000,50000],
			summury: {},
			chargeOK: false,
			chargeOKdata: {},
			popupCardList: false,
			cards: [],
			popupChargeList: false,
			popupCashout: false,
			popupCharge: false,
			record: {
				cashout: [],
				charge: []
			},
			times: {
				cashout: 0,
				score: 0

			}
		}
	},
	mounted: function () {
		//load data here;
		var _this = this;
		window.Services.getAccountInfo(function (data) {
			_this.summury = data || {};
		});
	},
	methods: {
		checkValue: function () {
			var number = this.number;
			this.number = Math.max(1, number);
			this.number = Math.min(50, number);
		},
		cashoutRecord: function () {
			this.popupCashout = true;
			var cashoutRecord = window.Services.cashoutRecord;
			var _this = this;
			cashoutRecord(function (data) {
				_this.record.cashout = data;
			});
		},
		chargeRecord: function () {
			this.popupCharge = true;
			var chargeRecord = window.Services.chargeRecord;
			var _this = this;
			chargeRecord(function (data) {
				_this.record.charge = data;
			});
		},
		income2balance: function () {
			window.Services.income2balance(function (data) {
				MINT.Toast('转余额成功');
				// location.reload();
			});
		},
		commission2balance: function () {
			window.Services.commission2balance(function (data) {
				MINT.Toast('转余额成功');
				// location.reload();
			});
		},
		cashOutPop: function () {
			var _this = this;
			_this.popupCardList = true;
			window.Services.getCards(function (data) {
				_this.cards = data;
			});
		},
		cashOut: function (account) {
			var _this = this;
			var times = _this.summury.limit.cashout;
			window.Services.cashOut(account, function (data) {
				_this.summury.limit.cashout -= 1;

				var msg = '本日还有' + _this.summury.limit.cashout + '次体现机会。';
				if (_this.summury.limit.cashout <= 3) {
					msg = '本日三次机会都已使用。';
				}
				MINT.Toast({
					className: "chargeOKToast",
					message: "提现成功，" + msg,
					position: 'left',
					duration: 1000
				});
				// location.reload();
			});
		},
		balance2score: function () {
			var _this = this;
			var times = _this.summury.limit.convert
			window.Services.balance2score(function (data) {
				_this.summury.limit.convert -= 1;

				var msg = '本日还有' + _this.summury.limit.convert + '次体现机会。';
				if (_this.summury.limit.convert <= 0) {
					msg = '本日三次机会都已使用。';
				}
				MINT.Toast('转积分成功');
				// location.reload();
			});
		},
		chargeSubmit: function () {
			// alert(this.charge);
			var _this = this;
			window.Services.charge({
				value: this.number * 1000
			}, function (data) {
				_this.chargeOKdata = data;
				_this.chargeOK = true;
				// MINT.Toast('订单已生成，可查看充值记录');
			});
		},
		chargeOKclose: function () {
			this.chargeOK = false;
		},
		copyInfo: function () {
			var Url2 = document.getElementById("chargeInfo");
			Url2.select(); // 选择对象
			document.execCommand("Copy"); // 执行浏览器复制命令
			MINT.Toast({
				className: "chargeOKToast",
				message: "复制成功",
				position: 'left',
				duration: 1000
			});
		}
	}
};

Pages.mineAddCard = {
	template: HLT.getTpl('#addCard'),
	data: function () {
		return {
			cards: [],
			card: {
				account: '',
				bank: '',
				branch: ''
      },
      fullHeight: document.documentElement.clientHeight,
      allHeight: document.documentElement.clientHeight,
      showButtomText: true
		}
	},
	updated: function () {
		var cache = HLT.cache;

		cache.set('cards', this.card);
	},
	mounted: function () {
		//load data here;
		var _this = this;
		var cache = HLT.cache;

		var card = cache.get('card');
		if (card) {
			this.card = card;
		}
    var that = this;
    window.onresize = function() {
        window.fullHeight = document.documentElement.clientHeight;
        _this.fullHeight = window.fullHeight;
        // < 说明安卓软键盘弹起来了
        if (_this.fullHeight < _this.allHeight) {
          _this.showButtomText = false;
        } else {
          _this.showButtomText = true;
        }
    };
		window.Services.getCards(function (data) {
			_this.cards = data;
		});
  },
	methods: {
		isNotValid: function (json) {
			return HLT.isEmptyJson(json);
		},
		isEmpty: function (cards) {
			cards = HLT.trim.both(cards);
			return cards == '';
		},
		saveCard: function () {
			var _this = this;
			var card = this.card;
			// cards = HLT.trim.both(cards);
			window.Services.addCard(card, function (data) {
				MINT.Toast({
					message: '添加成功',
					duration: 2000
				});
				_this.card = {};
			});
		}
	}
};

Pages.mineShare = {
	template: HLT.getTpl('#share'),
	data: function () {
		var invites = HLT.cache.get('invites') || 0;
		return {
			isOther: window.User.isOther,
			code: '',
			expire: '',
			qr: '',
			invites: invites,
			showQr:false
		}
	},
	updated: function () {},
	mounted: function () {
		var _this = this;
		window.Services.getPersonalInfo(function (data) {
			_this.invites = data.invites;
		});
	},
	methods: {
		hideQr: function(){
			this.showQr = false
			var node = document.getElementsByClassName('qr')[0]
			node.innerHTML=""
		},
		createCode: function () {
			this.showQr = true
			var _this = this;
			var isOther = _this.isOther;
			if (isOther) {
				MINT.Toast({
					message: "账号无权限使用，请联系管理员进行账号验证",
					position: 'bottom',
					duration: 1000
				});
				return;
			}
			window.Services.invite(function (data) {
				_this.code = data.invite;
				_this.expire = data.expire;
				_this.$nextTick(function(){
					var node = document.getElementsByClassName('qr')[0]
					new QRCode(node, data.url);
				})
			});
		},
		copyCode: function () {
			var Url2 = document.getElementById("mineCodes");
			Url2.select(); // 选择对象
			document.execCommand("Copy"); // 执行浏览器复制命令
			MINT.Toast({
				message: "已复制好，可贴粘。",
				position: 'bottom',
				duration: 1000
			});
		}
	}
};

Pages.mineCS = {
	template: HLT.getTpl('#cs'),
	data: function () {
		return {
			qq: ''
		}
	},
	mounted: function () {
		var _this = this;
		window.Services.getSetting(function (setting) {
			_this.qq = setting.service;
		});
	}
};

Pages.mineNotice = {
	template: HLT.getTpl('#notice'),
	data: function () {
		return {
			notice: {}
		}
	},
	mounted: function () {
		var _this = this;
		window.Services.notice(function (notices) {
			_this.notice = notices || [];
		});
	},
};

