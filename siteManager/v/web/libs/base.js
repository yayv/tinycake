window.HLT = {};

HLT.name = 'HLT';

HLT.$ = function(selector){
	return document.querySelector(selector);
}

HLT.getTpl = function(id){
    return '<div>' + HLT.$(id).innerHTML + '</div>';
}

HLT.getFullAPI = function(api){
    var base = 'http://dev.ellipsetrade.motorstore.cn';

    if(api.indexOf('http') == 0){
        return api;
    }
    return base + api;
}

HLT.fetch = function(url, callback){
    url = HLT.getFullAPI(url);

    fetch(url, {
        credentials: 'include',
        method: 'GET'
    }).then(function(response){
        //打印返回的json数据
        response.json().then(function(data){
            callback(data);
        })
    }).catch(function(e){
        console.log('error: ' + e.toString());
    });
}

HLT.post = function(params, callback){
    url = HLT.getFullAPI(params.url);
    
    var data = params.data || {};
    fetch(url, {
        headers: new Headers({
            'Content-Type': 'application/json'
        }),
        method: 'POST',
        credentials: 'include',
        body: JSON.stringify(data)
    }).then(function(response){
        //打印返回的json数据
        response.json().then(function(data){
            callback(data);
        })
    }).catch(function(e){
        callback({code:'fail'});
        console.log('error: ' + e.toString());
    });
}

HLT.log = function(params){
    // if(HLT.config.debug){
        console.log(params);
    // }
}

HLT.getLocalImageData = function(file, callback){
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function(evt){
        var file = evt.target.result;
        // compressImage(file, function (base64) {
            callback && callback(file);
        // });
        // file = window.URL.createObjectURL(file);
    }
}

HLT.isEmptyJson = function(json){
    var times = 0;
    var steps = 0;
    for(var prop in json){
        times += 1;
        if(json[prop]){
            steps += 1;
        }
    }
    // console.log(json)
    // console.log(times, steps)
    return times != steps;
}


HLT.jsonMerge = function(a, b, isWrite, filter){
    for (var prop in b) 
    if (isWrite || typeof a[prop] === 'undefined' || a[prop] === null) 
        a[prop] = filter ? filter(b[prop]) : b[prop];
    return a;
}

HLT.guid = function(){
    return 'hlt-' + (Math.random() * (1 << 30)).toString(16).replace('.', '');
}

HLT.trim = {
    left : function(str){
        return str.replace( /^\s*/, '');
    },
    right : function(str){
        return str.replace(/(\s*$)/g, "");
    },
    both : function(str){
        return str.replace(/^\s+|\s+$/g,"");
    },
    all : function(str){
        return str.replace(/\s+/g,"");
    }
}


//cache
HLT.cache = (function() {
    /*
    说明：
    1: JSON.stringfy --> set --> get --> JSON.parse
    2: data format well return as set`s
    3: undefined in array will be null after stringfy+parse
    4: NS --> namespace 缩写
    */
    var keyNS = 'hg-default-';

    function get(key) {
        /*
        legal data: "" [] {} null flase true

        illegal: undefined
            1: key not set
            2: key is cleared
            3: key removed
            4: wrong data format
        */
        var tempKey = keyNS + key;
        if (!isKeyExist(tempKey)) {
            return null;
        }
        // maybe keyNS could avoid conflict
        var val = localStorage.getItem(tempKey) || sessionStorage.getItem(tempKey);
        val = JSON.parse(val);
        // val format check
        if (val !== null
            && Object.prototype.hasOwnProperty.call(val, 'type')
            && Object.prototype.hasOwnProperty.call(val, 'data')) {
            return val.data;
        }
        return null;
    }
    // isPersistent
    function set(key, val, isTemp) {
        var store;
        if (isTemp) {
            store = sessionStorage;
        } else {
            store = localStorage;
        }
        store.setItem(keyNS + key, JSON.stringify({
            data: val,
            type: (typeof val)
        }));
    }

    function remove(key) {
        var tempKey = keyNS + key;
        localStorage.removeItem(tempKey);
        sessionStorage.removeItem(tempKey);
    }

    function isKeyExist(key) {
        // do not depend on value cause of ""和0
        return Object.prototype.hasOwnProperty.call(localStorage, key)
            || Object.prototype.hasOwnProperty.call(sessionStorage, key);
    }

    function setKeyNS(NS) {
        var isString = typeof NS === 'string';
        if (isString && NS !== '') {
            keyNS = NS;
        }
    }

    return {
        setKeyNS: setKeyNS,
        get: get,
        set: set,
        remove: remove
    };
})();



HLT.qrcode = function(url, callback){
    var size = 480;

    var canvas = document.createElement("canvas");
        canvas.width = size;
        canvas.height = size;
    var context = canvas.getContext("2d");
    
    var node = document.createElement("div");
    new QRCode(node, url.toString());
    setTimeout(draw,10);

    function draw(){
        var qcodeOrg = node.getElementsByTagName("img")[0].src;
        var img = new Image();
            img.src  = qcodeOrg;
        context.drawImage(img, 0, 0, size, size);
        var newImageData = canvas.toDataURL("image/png");
        callback(qcodeOrg);
    }
}


HLT.getPara = function(url,name){
    // url = url.split("&apm;").join("&");
    if(url == ''){
        return '';
    }

    var v = '', _p = name + '=';

    if(url.indexOf("&" + _p)>-1){
        v = url.split("&" + _p)[1] || '';
    }

    if(url.indexOf("?" + _p)>-1){
        v = url.split("?" + _p)[1] || '';
    }
    v = v.split("&")[0] || '';
    return v;
}