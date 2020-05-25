<?php

class Webapi
{
	public function __construct()
	{
		$this->callStack = [];

		$this->format = false ;

		$this->result = false;

		$this->last_error = false ;

		$this->baseTypes = ["int", "float", "double", "string","text","bool"];
		$this->checkFunc = ['is_int', 'is_float','is_double','is_string','is_string','is_bool'];

		$this->types = array(
			// 基础数据类型
			"int", "float", "double", "string","text","bool",

			// 扩展类型
			"year","month", "day","age","currency", // 数字
			"date",	"time", "datetime", "phone","mobile", // 带格式符号的数字
			"weekday", // 字母组合 
			"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
			"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
			"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
			"lower","upper","letter", // 字母、数字的子集的组合
		);

		// DONE: int email
		// TODO: float", "double", "age",			"date",	"time", "datetime","year","phone","mobile", "base64","MD5","username","password","lower","upper","letter","string",

		$this->errors = array(
			// 格式解析错误，或格式内的值有错
			"FORMAT_VALUEFORMAT_SYNTAX_ERROR" => "参数值的格式描述的语法错误",
			"FORMAT_JSON_STRUCT_ERROR" => "参数表的 JSON 格式有语法错误",
			"FORMAT_SYNTAX_ERROR" => "格式描述的语法错误",
			"FORMAT_UNKNOWN_KEYTYPE_ERROR" => "格式中指定的数据类型未定义",
			"FORMAT_NOT_SUPPORT_MULTIFORMAT_ARRAY" => "数组中元素的格式目前只能有且只有1个格式描述",
			"FORMAT_NOT_SUPPORT_NOFORMAT_ARRAY" => "数组中元素的格式目前只能有且只有1个格式描述",

			// 参数部分错误，或参数数据错误
			"DATA_NOT_MATCHED" => "数据格式不匹配",
			"DATA_NOT_IN_VALID_RANGE" => "数据超合理范围",
			"DATA_NOT_IN_SET_RANGE" => "数据超出要求范围",
			"DATA_KEY_NEED_EXIST" => "缺少了必填的KEY",
			"DATA_NOT_EXIST" => "KEY不存在",
			"DATA_UNKNOWN_KEY_ERROR" => "存在格式中不存在的参数",

			// 解析过程中，格式与数据匹配问题
			"TYPE_NO_MATCHED" => "没有匹配的类型",
			"TYPE_WITHOUT_METHOD" => "没有匹配的解析方法",
		);

		$this->morekeys = '...';  // 参数的格式表中存在这个key，则可以接受不在格式设定中的参数，否则，多余的参数会被抛弃

		$this->all_errors = array();

		$this->paramsParseErrors = '';

		$this->supportFormats();
	}

	public function supportFormats()
	{
		// Format Syntax: "[*]<format_name>[data range][:length][#default_value]//COMMENT"
		// data range syntax:
		// 	int float double: (1, 100), (1,100], [1,100),[1,100] 
		//  date time: 
		//  string : enum {papa,mama,grandpa,grandma,grandma-inlaw}
		// example: "role":"*string{papa, mama}:4#papa//role name 
		// 格式语法 : "[*|#]<格式名>[数值范围][:长度][#默认值]//说明"
		// 开头的 *，#，或无，表示该参数项是否必须填写， * 为必填， #为
		// 格式名，目前所支持的格式包括: email, phone, mobile, date, time, datetime, int, float, base64, MD5 ...
		// 取值范围,以{[(三个符号中的一个开始，由{开始则必须由}结束, 如{a,b,c}, 表示为枚举类型; [()] 的组合表示为 时间和数字可以用集合形式表示取值范围如 (1,100], 表示 大于1小于等于100的范围.
		// :长度,表示为需要检查的变量里原始值的字符长度
		// 默认值，当取值失败，或者取值超范围时，以默认值做为返回值，同时发出一个错误信息
		// 说明，参数格式的表达过于技术化，需要有给产品或业务相关人员看得懂的说明，更好的表达这些设置的目的
		$this->formats = array(
			"int"  =>"[+-]?[0-9]*", 
			"float" =>"[+-]?[0-9]*\.[0-9]*", 
			"double"=>"[+-]?[0-9]*\.[0-9]*", 
			"string"=>".*", // TODO: 这个，应该根据参数对字符串进行安全转码
			"text"  =>".*", // TODO: 这个，应该根据参数对字符串进行安全转码
			"bool"  =>"(true|false)", 

			// 扩展类型
			"year" => "[0-9]{4}",
			"month"=> "[12][0-9]",
			"date"=>"[0-9]{4}[-/ ]?[0-9]{2}[-/ ]?[0-9]{2}",	
			"time"=>"[0-9]{2}:[0-9]{2}:[0-9]{2}", 
			"datetime"=>"", 
			"weekday"=>"(Sun|Mon|Tue|Sat)",
			"age"=>"[0-9]{3}",
			"currency"=>"[0-9]*\.[0-9]{2}", // 数字

			"phone", // 带格式符号的数字
			"mobile", // 带格式符号的数字
			"weekday", // 字母组合 
			"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
			"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
			"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
			"lower","upper","letter", // 字母、数字的子集的组合
		);
	}

	private function getValueFormat($format)
	{
		$ret = preg_match("/([\*|#])?([0-9a-zA-Z@]*)(([\{\[\(])(.*)([\)\]\}]))?(:([0-9]*))?(#([^\/]*))?(\/\/(.*))?/",$format, $matches);
		if($ret)
		{#print_r($matches);die();
			$format_result = array(
				"option"	=>isset($matches[1])?$matches[1]:'',
				"name"		=>$matches[2],
				"left"		=>isset($matches[4])?$matches[4]:'',
				"range"		=>isset($matches[5])?$matches[5]:'',	
				"right"		=>isset($matches[6])?$matches[6]:'',
				"length"	=>isset($matches[8])?$matches[8]:'',
				"default"	=>isset($matches[10])?$matches[10]:'',
				"comment"	=>isset($matches[12])?$matches[12]:''
			);
			return $format_result;
		}
		else
			return false;
	}

	public function getKeyFormat($strKey)
	{
		// key: ..., *, *[n], *[n,m]
		/*
		(
		    [0] => *number
		    [1] => *
		    [2] => 
		    [3] => 
		    [4] => 
		    [5] => 
		    [6] => number
		)

		(
		    [0] => *[12,100]number
		    [1] => *
		    [2] => [12,100]
		    [3] => 12
		    [4] => ,100
		    [5] => 100
		    [6] => number
		)
		*/
		$result = ["require"=>false, "range"=>false, "min"=>'0',"max"=>999999,"name"=>''];
		$reg = "/(\*)?(\[([0-9]*)(,([0-9]*))?\])?([a-zA-Z]*[a-zA-Z0-9-_]*)/";
		$ret = preg_match($reg, $strKey, $matches);
		if($ret)
		{
			$result['require'] = ($matches[1]=='*');

			if( $matches[3] || $matches[5] ) 
			{
				$result['range'] = true;
				$result['min'] = $matches[3];
				if($matches[5])
					$result['max'] = $matches[5];
				else
					$result['min'] = $result['max'] = $matches[3];
			}

			$result['name'] = $matches[6];
		}
		if($result['name']=='')
			return false;
		else
			return $result;
	}

	private function parseObject($jsonFormat, $jsonObject)
	{
		$result = new stdClass();
		$morekey= false;
		/*
		TODO:根据 format 的 key 依次取值, 找不到的检查是否为必填参数, 最后剩余的检查 ... key是否存在
		*/
		foreach($jsonFormat as $k=>$v)
		{
			if($k=='...') 
			{
				$morekey = true; 
				continue ;
			}

			if(isset($jsonObject->$k))
			{
				 if( is_object($v) && is_object($jsonObject->$k) ){
				 	// parseObject
				 } else if( is_array($v) && is_array($jsonObject->$k) ){
				 	// parseArray
				 } else if( is_string($v) ){
				 	// 格式为 string , value 则要根据解析,具体判断
					// 对应key 的value存在，且 format 为 object, value 也为 object
					$this->callStack[] = "(Value)".$v;
					$result->$k = $this->getValue($v, $jsonObject->$k);
					array_pop($this->callStack);
				 }else{
				 	// error 
					$callstack = implode('->',$this->callStack);
					$this->last_error = "Line".__LINE__.":".$callstack.':'.$this->errors['DATA_NOT_MATCHED'];
					$this->all_errors[] = $this->last_error ;
				 }
			}
			else 
			{
				// check format, is this format required
				$key = $this->getKeyFormat($k);
				
				if($key['require'])
				{
					$callstack = implode('->',$this->callStack);
					$this->last_error = "Line".__LINE__.":".$callstack.':'.$this->errors['DATA_NOT_EXIST'];
					$this->all_errors[] = $this->last_error ;
				}
				else
				{

				}

			}

			unset($jsonObject->$k);

		}

		// 还有扩展的key
		if(count((array)$jsonObject)>0)
		foreach($jsonObject as $k=>$v)
		{

		}

		return $result;
	}

	private function parseArray($jsonFormat, $jsonObject, $min=0,$max=999999)
	{
		$result = [];
		if(!is_array($jsonObject))
		{
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error ;

			$this->error_msg = "DATA_NOT_MATCHED";
			return false;
		}

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_array($jsonFormat[0]))
			{
				// TODO: 向下一层继续解析
				$this->callStack[] = $k;
				$value = $this->parseArray($jsonFormat[0],$v);
				$result[] = $value ;
				array_pop($this->callStack);
			}
			else if(is_object($jsonFormat[0]))
			{
				$this->callStack[] = $k;
				$value = $this->parseObject($jsonFormat[0],$v);
				$result[] = $value ;
				array_pop($this->callStack);
			}
			else if(is_string($jsonFormat[0]))
			{
				// TODO:解析格式, 检查参数的值是否匹配
				foreach($jsonObject as $kk=>$vv)
				{
					// if($vv==null){
					// 	continue;
					// }
					$this->callStack[] = "KEY $k";
					$value = $this->parseString($jsonFormat[0], $vv);
					// var_dump($this->callStack);
					array_pop($this->callStack);
				}
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				$callstack = implode('->',$this->callStack);
				$this->last_error = $callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}

		return $result;
	}

	public function isFormatStringOk($strFormat)
	{
		$format = $this->getValueFormat($strFormat);

		if(!array_key_exists($format['name'],$this->formats))
		{
			$this->last_error = $this->errors['FORMAT_UNKNOWN_KEYTYPE_ERROR'];
			$this->all_errors[] = $this->last_error ;
			return false;
		}

		return true;
	}

	public function isValueFormatStringOk($stringFormat)
	{
		// 这里判断具体一个值是否符合格式说明的要求
	}

	private function parseFormatObject($jsonFormat)
	{
		$result = [];

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_object($jsonFormat->$k))
			{
				$this->callStack[] = '(Object)'.$k;
				// TODO: 向下一层继续解析
				$this->parseFormatObject($v);

				array_pop($this->callStack);
			}
			else if(is_array($jsonFormat->$k))
			{
				$this->callStack[] = '(Array)'.$k;
				// TODO: 向下一层继续解析
				$this->parseFormatArray($v);

				array_pop($this->callStack);
			}
			else if(is_string($jsonFormat->$k))
			{
				// TODO:解析格式, 检查参数的值是否匹配
				$this->callStack[] = $k;

				$ret = $this->isFormatStringOk($v);

				array_pop($this->callStack);
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				$callstack = implode('->',$this->callStack);
				print_r($callstack.$this->errors['FORMAT_SYNTAX_ERROR']);
				$this->last_error = $callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}

		return true;
	}

	private function parseFormatArray($jsonFormat)
	{
		$result = [];

		$c = count($jsonFormat);

		if($c>1){

			$this->error_msg = "DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY";
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY'];
			$this->all_errors[] = $this->last_error ;

			return false;

		} else if($c<1) {

			$this->error_msg = "FORMAT_NOT_SUPPORT_NOFORMAT_ARRAY";
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['FORMAT_NOT_SUPPORT_NOFORMAT_ARRAY'];
			$this->all_errors[] = $this->last_error ;

			return false;
		} else {
			// do nothing
		}

		$v = $jsonFormat[0];

		// 根据格式,依次检查Obj
		if(is_array($v))
		{
			// TODO: 向下一层继续解析
			$this->callStack[] = '(Array)0';
			$this->parseFormatArray($v);
			array_pop($this->callStack);
		}
		else if(is_object($v))
		{
			$this->callStack[] = '(Object)0';
			$ret = $this->parseFormatObject($v);
			array_pop($this->callStack);
		}
		else if(is_string($v))
		{
			$this->callStack[] = 'string';
			$ret = $this->isFormatStringOk($v);
			array_pop($this->callStack);
		}
		else
		{
			// 不是对象,不是数组,不是字符串,那格式出错了
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
			$this->all_errors[] = $this->last_error ;
			return false;
		}

		return true;
	}

	// 这里只是入口，不可能在下层出现对这个函数的调用 
	public function parseFormat($jsonFormat)
	{
		// 所有的 json 都解析为 array ，不再用 object 的方式进行判断。口亨，就因为他不支持 count !!!
		if(is_object($jsonFormat))
		{
			// 循环 item
			$this->callStack = [];
			$this->callStack[] = 'Object';
			return $this->parseFormatObject($jsonFormat);
		}
		else if(is_array($jsonFormat))
		{
			// 循环 item
			$this->callStack = [];
			$this->callStack[] = 'Array';
			return $this->parseFormatArray($jsonFormat);
		}
		else // is format string
		{
			// 按照 php 里 json_decode 的执行逻辑， 纯字符串解析会报错。所以不可能出现纯字符串的格式描述
			$this->last_error = $this->errors['FORMAT_SYNTAX_ERROR'].':'."不接受非对象且非数组的纯变量形式";
			$this->all_errors[] = $this->last_error ;
			return false;
		}

		return true;
	}

	public function parseParams($params)
	{
		//1. 根据 format 依次 读取 params 里的数据
		if(is_object($this->format)){
			$tmp = $this->parseObject($this->format, $params);
			if($tmp)
				$this->result = $tmp;
			else
				return $tmp;
		}
		else if (is_array($this->format)) {

			$tmp = $this->parseArray($this->format, $params);
			if($tmp)
				$this->result = $tmp ;
			else
				return $tmp;

		} else {
			$this->last_error = $this->errors['FORMAT_JSON_STRUCT_ERROR'].":"."JSON必须是对象或数组";
			$this->all_errors[] = $this->last_error ;
			return false;
		}
		return true;
	}

	/*
	获取对应参数格式描述的参数值. 这里的 format 只能是 string 了。
	*/
	public function getValue($strFormat, $key)
	{
		$value = false;

		// property_exists
		$f = $this->getValueFormat($strFormat);

		#in_array($name, $this->baseTypes) ;

		return $value;
	}

	/**
	检查格式描述文本是否格式正确
	TODO 这个以后要增加对每种数据格式的正则语法的检查
	*/
	public function isFormatOK($format)
	{
		if($format=='') $format='{}';

		$json = json_decode($format);

		$str = json_last_error_msg();		

		// 目前的检查, 只能判断为符合json语法，尚未能判断是否
		if('No error'==$str)
		{
			// 保存接口格式
			$this->format = $json;

			$result = $this->parseFormat($json);

			return $result;
		}
		else
		{
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
			$this->all_errors[] = $this->last_error;
			return false;
		}

		return true;
	}


	/*
	如果解析过程中发生错误，则会在解析器状态里进行记录。
	这个函数就是用来获取解析器的解析状态，并判断是否有错误发生。
	目前只记录导致解析失败的错误，将来可以把一些警告也进行记录
	*/
	public function isParseOk()
	{
		if( count( $this->all_errors ) > 0 )
			return false;

		return true;
	}

    public function echoParamsErrorMessage($type)
    {
        if (strcmp('json', $type) == 0) {
            #header('Content-Type: application/json');
            echo '{"code":"fail","message":"' . $this->paramsParseErrors . '"}';
        } else {
            #header('Content-Type: text/html');
            echo '参数解析失败:' . $this->paramsParseErrors;
        }
        return;
    }

	public function getJSONParams($strFormat, $strParams)
	{
		// 1. 检查 strFormat 是否符合 json 格式 
        $ret = $this->isFormatOK($strFormat);
        if(!$ret)
        {
			$this->last_error = $this->errors['FORMAT_JSON_STRUCT_ERROR'];
			$this->all_errors[] = $this->last_error;
			$this->paramsParseErrors = implode("\n",$this->all_errors);
            return false;
        }

		// 2. 检查 strParams 是否符合 json 格式 		
        $params = json_decode($strParams);
        $str = json_last_error_msg();
        if('No error'!=$str)
        {
        	$this->all_errors[] = $this->errors['FORMAT_VALUEFORMAT_SYNTAX_ERROR'];
        	$this->paramsParseErrors = implode("\n",$this->all_errors);

            return false;
        }

        // else $str == 'No error'
		// 3. 根据 strFormat 按层次获取 strParams 里的参数
        $ret = $this->parseParams($params);

        if( false==$ret || count($this->all_errors)>0 )
        {
        	$this->all_errors[] = '';
            $this->paramsParseErrors = implode("\n",$this->all_errors);
            return false;
        }

        // 4. .返回解析的结果
        return $this->result;
	}
}

function _testJSON()
{
	$test = [
		"开发测试"=>[
			"format"=>'{"a":"int"}',
			"string"=>'{"a":123}',
			"note"=>'开发过程中需要的数据，随时修改',
		],
		"正常数据一层key/value"=>[
			"format" => '{"a":"int","b":"*int","c":"string","...":"string"}',
			"string" => '{"a":123,"b":111,"c":123,"d":"asd","e":"asd"}',
			"note"=>"测试一层key/value数据, 包括可选参数，值也针对可选和必选参数分别对应，这组数据不包括不符合格式的情况"
		],
		"正常数据多层key/value"=>[
			"format" => '{"a":"*int","b":"*int","c":{"d":"int","e":"bool","f":"datetime"}}',
			"string" => '{"a":false,"b":"kjkjk","c":{"d":111,"e":false,"f":"2010/01/01"}}',
			"note"=>'',
		],
		"正常数据多层数组"=>[
			"format" => '{"a":"int","b":"*int"}',
			"string" => '{"a":false,"b":"kjkjk"}',
			"note"=>'',
		],
		"a"=>[
			
			"format" => '"string//测试纯字符串值"',
			"string" => '"this is a string"',
			"note"=>'',
		],
		"more"=>[
			"format" => '{
			"enterpriseId":"*int//企业id",

			"username":"*string//username",
			"mobile":"*mobile//mobile",
			"idcard":"*idcard//idcard",

			"province":"*string//province",
			"city":"*string//city",

			"countPeople":"int//可选countPeople",
			"babySeats":"int//可选babySeats",
			"vehicleModelId":"*int//车系id",
			"vehicleId":"*int//车型id,可能不需要",

			"rentRetailId":"*int//取车门店id",
			"returnRetailId":"*int//换车门店id",

			"orderStartDate":"*datetime//StartDate",
			"orderEndDate":"*datetime//EndDate",

			"baseService":"*bool//基础服务费。价格由服务器端配置决定，这里只做开关选择",
			"carService":"*bool//整备服务费。价格由服务器端配置决定，这里只做开关选择",
			"deliveryService":"*bool//基础服务费。价格由服务器端配置决定，这里只做开关选择",
			"insurance":"*bool//insurance",
			"vip":"*bool//vip",

			"normalArray":["string//普通数组，每个元素都是字符串"],
			"objArray":[{
				"customerId":"*int//obj用户id",
				"customerName":"*string//obj用户名",
				"customerIdcard":"*idcard//obj用户身份证号",
				"customerMobile":"*mobile//obj用户手机号"
				}]
		}',

		"string" => '{
		    "enterpriseId":"1",
		    "username":"闫大瑶",
		    "mobile":"18618193355",
		    "idcard":"110102198312082405",
		    "province":"北京市",
		    "city":"北京",
		    "countPeople":"3",
		    "babySeats":"1",
		    "vehicleModelId":"1",
		    "vehicleId":"2",
		    "rentRetailId":"1",
		    "returnRetailId":"1",
		    "orderStartDate":"2020-02-14",
		    "orderEndDate":"2020-02-18",
		    "baseService":"1",
		    "carService":"1",
		    "deliveryService":"1",
		    "insurance":"1",
		    "vip":false
		}'
		]
	];

	foreach($test as $k=>$v)
	{
		echo $k,":\n";
		if(isset($v['note']) && $v['note']!='')
			echo "\t",$v['note'],"\n\n";
		_testJSON1($v['format'], $v['string']);	
		echo "\n\n\n\n";
		break;
	}
	
}

function _testJSON1($format, $string)
{
	$t = new Webapi();
	$params = $t->getJSONParams($format, $string);
	if($t->isParseOk())
	{
		echo 'result:',"\n";
		print_r($params);
	}
	else
	{
		echo 'error/warning:',"\n";
		print_r($t->echoParamsErrorMessage('json'));
	}
}

/**
 * TODO: 
 * 1. value 中 带* 的必填值
 * 2. false 值的解析
 * 3. ... 代表更多的 key 是否支持。 带 ... key时可以传递未定义的key, 不带...时，应该忽略未定义key的参数
 * 4. key中带 * 的必填项, [] 限定数组长度, [n] 表示数组只能为n个元素，[n,m]表示数组长度在n,m之间，不带[]为长度不受限制
 * 5. 各种值的解析
 * 6. 默认值 该怎么解释？ 解析成功，使用参数值；解析失败, 有默认值怎么处理，是否报错？
 * 7. 限制数组元素中的个数 "*[0,2]name":["string//名称"] , 这样是不是解析起来比较容易呢？
 * 8. 按文档的URL自动生成配置的，适用于自动化更新测试代码
 * 9. 把参数复制到代码的，适用于代码发开发过程，代码稳定之后，可以把参数注释，改为用url读取配置
 */


