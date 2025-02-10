/*
	接口服务，
	请注意，service并非与接口一一对应，更多是面向业务，可能是整合多个接口
*/
var w = 'http://ellipsetrade.motorstore.cn'
;(function(){
	var resultCheck = function(result, url, callback){
		if(!result && typeof result != 'object'){
			alert('系统错误，请联系管理员！');
			console.log('接口 ' + url + ' 请求失败', result.message);
			return;
		}

		if(result.code == 'ok'){
			callback && callback(result.data);	
			return;	
		}
		MINT.Toast(result.message||'网络错误');
	}

	var setUserSession = function(user, callback){
		var url = w + '/api/test/setenv?userName=' + user.username;

		HLT.fetch(url, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A01
	var verifyUrl = function(){
		return HLT.getFullAPI('/api/verify');
	}

	//A02
	var login = function(userInfo, callback){
		//对 userInfo 做必要处理

		userInfo = userInfo || {};
		var url  = w + '/api/user/login';
		HLT.post({
			url: url,
			data: userInfo
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}
	// 获取短信验证码
	var smsverify = function(userInfo, callback){
		//对 userInfo 做必要处理
		userInfo = userInfo || {};
		var url  = w + '/api/user/smsverify';
		console.log(url)
		HLT.fetch({
			url: url
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}
	//A03 
	var logout = function(callback){
		var url = w + '/api/user/logout';
		HLT.post({
			url: url
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A04 邀请码
	var invite = function(callback){
		var url = w + '/api/user/inviteCode';

		HLT.post({
			url: url
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A05
	var reg = function(userInfo, callback){
		//对 userInfo 做必要处理

		userInfo = userInfo || {};
		var url = w + '/api/register';
		HLT.post({
			url: url,
			data: userInfo
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A06 提交实名信息
	var setPersonalInfo = function(params, callback){
		var url = w + '/api/user/uploadIdInfo';

		HLT.post({
			url: url,
			data: params
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A07 
	var getPersonalInfo = function(callback){
		var url = w + '/api/user/getIdInfo';

		HLT.fetch(url, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A08 修改密码
	var setPassword = function(params, callback){
		var url = w + '/api/user/updatePassword';

		//"oldpassword":"", "newpassword":"

		HLT.post({
			url: url,
			data: params
		}, function(result){
			//对 result 可以做必要的处理后返回
			resultCheck(result, url, callback);
		});
	}

	//A09 获取帐号的 余额，佣金，收益，积分等信息
	var getAccountInfo = function(callback){
		var url = w + '/api/user/getInfo';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A10 获取关联银行卡信息
	var getCards = function(callback){
		var url = w + '/api/account/getInfo';

		HLT.post({
			url: url
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A11 添加银行卡
	var addCard = function(params, callback){
		var url = w + '/api/account/addBankAccount';

		/*
		params = {
			"account":"xxxx",
			"bank":"xxx",
			"branch":"开户行"
		}
		*/

		HLT.post({
			url: url,
			data: params
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A12 提交充值请求
	var charge = function(pramas, callback){
		var url = w + '/api/account/requestCharge';
		
		/*
		pramas = {
			value: "10000"
		}
		*/
		
		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	// A13: 提交提款请求
	var cashOut = function(pramas, callback){
		var url = w + '/api/account/requestCash';
		
		/*
		pramas = {
			number: "10000"
		}
		*/
		
		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A14: 收益转余额
	var income2balance = function(callback){
		var url = w + '/api/account/transferIncome';

		HLT.post({
			url: url
		}, function(result){
			resultCheck(result, url, callback);
		});
	}
	
	//A15: 佣金转余额
	var commission2balance = function(callback){
		var url = w + '/api/account/transferCommission';
		
		HLT.post({
			url: url
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A16: 余额转积分
	var balance2score = function(callback){
		var url = w + '/api/account/convertBalance';
		
		HLT.post({
			url: url
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A17: 为线路充值
	var charge4Line = function(pramas, callback){
		var url = w + '/api/account/chargeToLine';
		console.log('为线路充值', pramas)
		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A18: 为权益线路充值
	var charge4Equity = function(pramas, callback){
		// var url = '/api/account/chargeToEquity';
		var url = w + '/api/account/chargeToStore';
		
		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A19 充值记录：
	var chargeRecord = function(callback){
		var url = w + '/api/account/getChargeHistory';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A20 提现记录：
	var cashoutRecord = function(callback){
		var url = w + '/api/account/getCashoutHistory';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A25: 获得设备列表
	var getLines = function(callback){
		var url = w + '/api/lines/getAll';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}


	//A26: 获得订单列表
	var getOrders = function(callback){
		var url = w + '/api/lines/getOrders';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A27: 获得定存收益列表
	var getDayIncome = function(callback){
		var url = w + '/api/lines/getDayIncome';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A28: 固定线路上线
	var turnOnTheLine = function(pramas, callback){
		var url = w + '/api/lines/turnOnTheLine';
		
		//pramas = {"lineNo":"xxxx"}

		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A29: 设备上线
	var turnOnStore = function(pramas, callback){
		var url = w + '/api/lines/turnOnStore';
		
		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A30 今日信息
	var dashboard = function(callback){
		var url = w + '/api/summary/all';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	// A31: 离线线路的余额回收为积分
	var recycle2score = function(params, callback){
		var url = w + '/api/lines/recycleLine';
		/*
		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
		*/
		console.log("params:")
		console.log(params)
		HLT.post({
			url: url,
			data: params
		}, function(result){
			resultCheck(result, url, callback);
		});

	}

	//A32: 获得定存设备
	var aip = function(callback){
		var url = w + '/api/lines/getStore';

		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A33: 修改提现密码
	var updateCashoutPassword = function(pramas, callback){
		var url = w + '/api/user/updateCashoutPassword';
		
		//{"loginpass":"password","oldpassword":"", "newpassword":""}

		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A34: 修改手机号
	var updateMobile = function(pramas, callback){
		var url = w + '/api/user/updateMobile';
		
		//{"phone":""}

		HLT.post({
			url: url,
			data: pramas
		}, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A40:用户端通知接口 
	var notice = function(callback){
		var url = w + '/api/notifications';
		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A41:客户端配置接口 
	var getSetting = function(callback){
		var url = w + '/api/setting';
		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	//A42: app下载地址
	var getAppInfo = function(callback){
		var url = w + '/api/setting/download';
		HLT.fetch(url, function(result){
			resultCheck(result, url, callback);
		});
	}

	window.Services = {
		setUserSession,
		verifyUrl,
		smsverify,
		login,
		logout,
		invite,
		reg,
		setPersonalInfo,
		getPersonalInfo,
		setPassword,
		getAccountInfo,
		getCards,
		addCard,
		charge,
		cashOut,
		income2balance,
		commission2balance,
		balance2score,
		charge4Line,
		charge4Equity,
		chargeRecord,
		cashoutRecord,
		getLines,
		getOrders,
		//getDayIncome,
		turnOnTheLine,
		turnOnStore,
		dashboard,
		recycle2score,
		aip,
		updateCashoutPassword,
		updateMobile,
		notice,
		getSetting,
		getAppInfo
	};
})();