<?php

class Webapi
{
	private $callStack = [];

	private $format = false ;

	private $result = false;

	private $last_error = false ;

	private $baseTypes = ["int", "float", "string","text","bool","datetime","date","time"];
	private $getValFunc = ["int"=>'intval', "float"=>'floatval',"string"=>'strval',"text"=>'strval',"bool"=>'boolval'];

	private $types = [
		// 基础数据类型
		"int", "float", "string","text","bool", "datetime",

		// 扩展类型
		"year","month", "day","age","currency", // 数字
		"date",	"time", "phone","mobile", // 带格式符号的数字
		"weekday", // 字母组合 
		"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
		"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
		"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
		"lower","upper","letter", // 字母、数字的子集的组合
	];
	private $funcs = [];

	// DONE: int email
	// TODO: float", "double", "age",			"date",	"time", "datetime","year","phone","mobile", "base64","MD5","username","password","lower","upper","letter","string",

	private $errors = array(
		// 格式解析错误，或格式内的值有错
		"FORMAT_VALUEFORMAT_SYNTAX_ERROR" => "格式描述的参数值有语法错误",
		"FORMAT_JSON_STRUCT_ERROR" 		  => "格式描述的 JSON 字符串有语法错误",
		"FORMAT_SYNTAX_ERROR" 			  => "格式描述有语法错误",
		"FORMAT_UNKNOWN_KEYTYPE_ERROR" 	  => "格式描述中指定的数据类型未定义",
		"FORMAT_NOT_SUPPORT_MULTIFORMAT_ARRAY"  => "格式描述中的数组只能有且只有1个格式",
		"FORMAT_NOT_SUPPORT_NOFORMAT_ARRAY"  	=> "格式描述中的数组使用了不支持的格式",

		// 参数部分错误，或参数数据错误
		"DATA_NOT_MATCHED" 			=> "传入数据格式不匹配",
		"DATA_NOT_IN_VALID_RANGE" 	=> "传入数据不在合理的范围",
		"DATA_NOT_IN_SET_RANGE" 	=> "传入数据不符合要求的范围",
		"DATA_KEY_NEED_EXIST" 		=> "传入数据缺少了必填的KEY",
		"DATA_NOT_EXIST" 			=> "传入数据的KEY不存在",
		"DATA_UNKNOWN_KEY_ERROR" 	=> "传入数据存在格式中不存在的参数",

		// 解析过程中，格式与数据匹配问题
		"TYPE_NO_MATCHED" 		=> "没有匹配的类型",
		"TYPE_WITHOUT_METHOD" 	=> "没有匹配的解析方法",
	);

	private $morekeys = '...';  // 参数的格式表中存在这个key，则可以接受不在格式设定中的参数，否则，多余的参数会被抛弃

	private $all_errors = array();

	private $paramsParseErrors = '';

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
	private $formats = array(
		"int"  =>"/(\+|\-)?[0-9]+/", 
		"float" =>"/[+-]?[0-9]+(\.[0-9]+)?/", 
		"string"=>"/[a-zA-Z0-9\_\-@#!$%^&]*/", // TODO: 这个，应该根据参数对字符串进行安全转码
		"text"  =>"/.*/", // TODO: 这个，应该根据参数对字符串进行安全转码
		"bool"  =>"/(true|false)/", 
		"datetime"=>"/[0-9]{4}[-\/ ]?[0-9]{2}[-\/ ]?[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", 

		// 扩展类型
		"year" => "/[0-9]{4}/",
		"month"=> "/[12][0-9]/",
		"date"=>"/[0-9]{4}[-\/ ]?[0-9]{2}[-\/ ]?[0-9]{2}/",	
		"time"=>"/[0-9]{2}:[0-9]{2}:[0-9]{2}/", 
		"weekday"=>"/(Sun|Mon|Tue|Sat)/",
		"age"=>"/[0-9]{3}/",
		"currency"=>"/[0-9]*\.[0-9]{2}/", // 数字

		"phone"=>"/[\-\+0-9 ]{16}/", // 带格式符号的数字
		"mobile"=>"/[\-\+0-9 ]{16}/", // 带格式符号的数字
		"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
		"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
		"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
		"lower","upper","letter", // 字母、数字的子集的组合
		"retCode" // string{ok,fail,error,deny}//ok为调用成功,fail为逻辑失败,error为系统报错,deny为没有权限
	);

	public function __construct()
	{
	}

	public function getAllErrors()
	{
		return $this->all_errors;
	}

	public function getValueFormat($format)
	{
		$ret = preg_match("/(\*)?([a-zA-Z]?[0-9a-zA-Z@]*)(([\{\[\(])(.*)([\)\]\}]))?(:([0-9]*))?(#([^\/]*))?(\/\/(.*))?/",$format, $matches);
		if($ret)
		{
			$format_result = array(
				"require"	=>isset($matches[1])?$matches[1]:'',
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
		// key: ..., *keyname, *[n]keyname, *[n,m]keyname
		// keyname: [a-zA-Z0-9]
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
		$result = ["require"=>false, "items"=>false, "min"=>'0',"max"=>999999,"name"=>''];
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

	/*
	获取对应参数格式描述的参数值. 这里的 format 只能是 string 了。
	*/
	public function getValue($oFormat, $value)
	{
		$result = false;
		
		// property_exists
		$f = $oFormat;//$this->getValueFormat($strFormat);

		if(in_array($f['name'], $this->baseTypes))
		{
			// 基本數據類型
			return $this->getValueOfBaseType($f, $value);
		}
		else if (in_array($f['name'], $this->types))
		{
			// 扩展数据类型
			return $this->getValueOfExtraType($f,$value);
		}
		else
		{
			// 不支持的数据类型
			return $value;
		}

		return $result;
	}

	private function getValueOfBaseType($format, $value)
	{
		// Format Syntax: "[*]<format_name>[data range][:length][#default_value]//COMMENT"
		// data range syntax:
		// 	int float double: (1, 100), (1,100], [1,100),[1,100] 
		//  date time: 
		//  string : enum {papa,mama,grandpa,grandma,grandma-inlaw}
		// example: "role":"*string{papa, mama}:4#papa//role name 
		// 格式语法 : "[*|#]<格式名>[数值范围][:长度][#默认值]//说明"
		/*
		“boolean”（从 PHP 4 起）
		“integer”
		“double”（由于历史原因，如果是 float 则返回“double”，而不是“float”）
		“string”
		“array”
		“object” 
		*/
		/*
			"format"=>'{"a":"*string[1,100]:3#33//測試"}',
			"string"=>'{"a":"2324"}',
			"note"=>'开发过程中需要的数据，随时修改',
		*/		
		$error = false;

		$overrange  = false;
		$overlength = false;
		$valueformat = false;

		switch($format['name'])
		{
			case 'float':
			case 'double':
			case 'int':
			case 'string':
			case 'text':
			case 'bool':			

				// 数据长度的检查
				if(isset($format['length']) && $format['length']!='' && strlen(''.$value)>$format['length'])
				{
					$overlength = true;
					$error = true;
				}

				// 获取参数值的参数值
				#if( preg_match("/(\+|\-)?[0-9]+/", ''.$value, $matches) )
				if( preg_match($this->formats[$format['name']], ''.$value, $matches) )
				{
					#$v = intval($value);
					$v = $this->getValFunc[$format['name']]($value);
				}
				else
				{
					$v = false;
					$valueformat = true;
					$error = true;
				}		

				// 对参数值的数值范围进行检查
				if( !$error && $format['range'] )
				{
					list($min,$max) = explode(',',$format['range']);

					// 格式包含 range 时, 对取值范围进行检查
					if( !$overrange && $format['left']=='('  && $v <= $min ) $overrange = true;
					if( !$overrange && $format['left']=='['  && $v <  $min ) $overrange = true;
					if( !$overrange && $format['right']==')' && $v >= $max ) $overrange = true;
					if( !$overrange && $format['right']==']' && $v >  $max ) $overrange = true;

					if( $overrange ) $error=true;
				}

				if($error)
				{
					// TODO: 处理解析报错，需要看参数的设置。
					// 先不管参数，统一处理成使用默认值的方式
					// 如果数据超范围，使用默认值
					if( $overrange || $overlength )
					{
						$v = $format['default']?$this->getValFunc[$format['name']]($format['default']):false;
						if(!$v)
						{
							$callstack = implode('->',$this->callStack);
							$this->last_error = $callstack.$this->errors['DATA_NOT_IN_SET_RANGE'];
							$this->all_errors[] = $this->last_error ;
						}
					}

					if( $valueformat )
					{
						$v = $format['default']?intval($format['default']):false;	
						if(!$v)
						{
							$callstack = implode('->',$this->callStack);
							$this->last_error = $callstack.$this->errors['DATA_NOT_IN_SET_RANGE'];
							$this->all_errors[] = $this->last_error ;
						}
					}

					// 这里的参数需要控制的情况包括: 必填参数和默认值如何处理？
					/*
						必填参数出错情况:
							1. 参数未设置
							2. 参数值格式错误,
							3. 参数取值范围或长度超出格式要求
						处理方式:
							不能获取正常的输入参数值时，有格式设置的默认参数，是使用默认参数还是报错？

						选填参数出错情况:
							1. 参数值格式错误,
							2. 参数取值范围或长度超出格式要求
						处理方式:
							不能获取正常的输入参数值时，有格式设置的默认参数，是使用默认参数还是报错？

						有默认值的选填参数，要不要为参数表补填默认值？
					*/
					return $v;
				}
				else
					return $v;
				break;
			case 'datetime':
				return date('Y-m-d H:i:s',strtotime($value));
				break;
			case 'date':
				// 检查时间范围，检查默认值, 不检查变量长度
				return date('Y-m-d',strtotime($value));
				break;
			default:
				die('這裡不應該寫die，應該拋出錯誤');
				break;
		}

	}

	private function getValueOfExtraType($format, $value)
	{
			// 扩展类型
			$formats = [
			"year","month", "day","age","currency", // 数字
			"date",	"time", "phone","mobile", // 带格式符号的数字
			"weekday", // 字母组合 
			"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
			"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
			"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
			"lower","upper","letter", // 字母、数字的子集的组合
			];	

		return $value;
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
			if($k==$this->morekeys)  // $key == '...'
			{
				$morekey = true; 
				continue ;
			}
			
			#$this->lastkey = $k;

			if(isset($jsonObject->$k))
			{
				 if( is_object($v) && is_object($jsonObject->$k) ){
				 	// parseObject
				 	$this->callStack[] = "(Object)$k";
				 	$result->$k = $this->parseObject($jsonFormat->$k, $jsonObject->$k);
				 	array_pop($this->callStack);
				 } else if( is_array($v) && is_array($jsonObject->$k) ){
				 	// parseArray
				 	$this->callStack[] = "(Array)$k";
				 	$result->$k = $this->parseArray($jsonFormat->$k, $jsonObject->$k);
				 	array_pop($this->callStack);
				 } else if( !is_object($jsonObject->$k) && !is_array($jsonObject->$k)) 
				 {
				 	// 格式为 string , value 则要根据解析,具体判断
					// 对应key 的value存在，且 format 为 object, value 也为 object
					$this->callStack[] = "(Value)$k";
					if(is_bool($jsonObject->$k))
					{
						// property_exists
						$f = $this->getValueFormat($v);
						if($f['require']!='*' && $f['name']!='string' && $vv=='')
						{
							unset($jsonObject->$kk);
							continue;
						}

						$result->$k = $this->getValue($f, $jsonObject->$k?"true":"false");	
					}
					else
					{
						$f = $this->getValueFormat($v);
						if($f['require']!='*' && $f['name']!='string' && $vv=='')
						{
							unset($jsonObject->$kk);
							continue;
						}

						$result->$k = $this->getValue($f, $jsonObject->$k);
					}
					
					array_pop($this->callStack);
				 }else{
				 	// error 
					$callstack = implode('->',$this->callStack);
					$this->last_error = "Line".__LINE__.":".$callstack."->$k:".$this->errors['DATA_NOT_MATCHED'];
					$this->all_errors[] = $this->last_error ;
				 }
			}
			else 
			{
				// check format, is this format required
				if(!is_string($jsonFormat->$k))
				{
					$koption = $this->getKeyFormat($k);	
					if($koption['require']=='*')
					{
						$callstack = implode('->',$this->callStack);
						$this->last_error = "Line".__LINE__.":".$callstack."->$k".':'.$this->errors['DATA_NOT_EXIST'];
						$this->all_errors[] = $this->last_error ;
						return false;
					}
				}
				else
				{
					$voption = $this->getValueFormat($jsonFormat->$k);

					if($voption['require']=='*')
					{
						$callstack = implode('->',$this->callStack);
						$this->last_error = "Line".__LINE__.":".$callstack."->$k:".$this->errors['DATA_NOT_EXIST'];
						$this->all_errors[] = $this->last_error ;
						return false;
					}
				}		

			}

			unset($jsonObject->$k);
		}

		// 还有扩展的key
		if($morekey && count((array)$jsonObject)>0 )
		{
			foreach($jsonObject as $k=>$v)
			{
				$result->$k =$v;
			}

			$result->$k = $v;
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
				foreach($jsonObject as $kk=>$vv)
				{
					$this->callStack[] = $kk;
					$value = $this->parseObject($jsonFormat[0],$jsonObject[$kk]);
					array_pop($this->callStack);
				}
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
					$f = $this->getValueFormat($jsonFormat[0]);
					if($f['require']!='*' && $f['name']!='string' && $vv=='')
					{
						unset($jsonObject->$kk);
						continue;
					}

					$value = $this->getValue($f, $vv);
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
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['FORMAT_UNKNOWN_KEYTYPE_ERROR'];
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
			$this->last_error = $this->errors['FORMAT_SYNTAX_ERROR'].':'."不接受非对象且非数组的纯变量形式:".$jsonFormat;
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
		} 
		else {
			$this->last_error = $this->errors['FORMAT_JSON_STRUCT_ERROR'].":"."JSON必须是对象或数组";
			$this->all_errors[] = $this->last_error ;
			return false;
		}
		return true;
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
		$err = json_last_error();		

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
		"bug测试"=>[
			"format"=>'{
                "userId":"*int//userId",
                "data":{
                    "plateNo":"*string//车牌号",
                    "type":"string//类型（事故/故障，默认为事故）",
                    "reason":"*string//事故原因（双方、单方、多方）",
                    "responsibility":"*string//事故责任（我方、对方、同等）",
                    "maintenanceMode":"*string//维修方式（自费、保险、第三方）",
                    "orderNo":"string//关联订单 账单唯一编号",
                    "driverName":"*string//事故责任人",
                    "amount":"float#0.0//定损金额",
                    "address":"string//事故发生地点",
                    "maintenanceCompany":"*string//维修单位",
                    "description":"*string//事故经过",
                    "occurrenceTime":"*datetime//事故时间",
                    "operator":"*string//经办人",
                    "finishTime":"datetime//完成时间",
                    "status":"string//状态（注销、定损中、结案）默认为定损中",
                    "note":"string//备注"
                }
            }',
			"string"=>'{"userId":"1","data":{"plateNo":"奥A4L7789","type":"事故","reason":"双方","responsibility":"对方","maintenanceCompany":"123","maintenanceMode":"保险","operator":"123","driverName":"123","amount":"","description":"123","occurrenceTime":"2019-12-31T16:00:00.000Z","finishTime":"2020-01-09T16:00:00.000Z","status":"结案","note":"123"}}',
			"note"=>"测试 *float 格式的值为 0 的情况",
		],
		"开发测试"=>[
			"format"=>'{
				"amount":"*float",
                "username":"*string",
                "password":"*string",
                "verify":"string",
                "start":"*datetime// 企业唯一代码 enterprise uniqe Code"
            }',
			"string"=>'{"amount":"0","id":"ZMMDEMO1","username":"13800138001","password":"12344321","start":1234}',
			"note"=>'开发过程中需要的数据，随时修改',
		],
		"正常数据一层key/value"=>[
			"format" => '{"a":"int","b":"*int","c":"string","...":"string"}',
			"string" => '{"a":123,"b":111,"c":123,"d":"asd","e":"asd"}',
			"note"=>"测试一层key/value数据, 包括可选参数，值也针对可选和必选参数分别对应，这组数据不包括不符合格式的情况"
		],
		"正常数据多层key/value"=>[
			"format" => '{"a":"*int","b":"*int","c":{"d":"int","e":"bool","f":"datetime"}}',
			"string" => '{"a":123,"b":"33","c":{"d":false,"e":false,"f":"2010/01/01"}}',
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
		    "mobile":"13800138000",
		    "idcard":"123321199001011234",
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
		],
		"using"=>[
			"format"=>'{
    "orderId":"*int//订单id",
    "data":{
        "list":[
            {
                "type":"*string{事故,违章}//每一个列表项都是一个事故记录",
                "occurrence":"*datetime//事故或违章时间",
                "address":"*string//事故或违章的发生地点",
                "description":"*string",
                "amount":"int//罚款或定损金额(分)",
                "score":"int//违章扣分",
                "driver":"string//驾驶人姓名",
                "driverIDCard":"string//驾驶人身份证号"
            }
        ]
    }
}',
			"string"=>'{
    "orderId": 1,
    "data": {
        "list": [
            {
                "type":"事故",
                "occurrence":"20200202 12:34:56",
                "address":"kkkkk",
                "description": "超速",
                "amount": 1000,
                "score": 6,
                "driver": "赵六",
                "driverIDCard": "220181199801018890"
            }
        ]
    }
}'
		]
	];

	#unset($test['开发测试']);
	#unset($test['正常数据一层key/value']);
	#unset($test['正常数据多层key/value']);
	#unset($test['正常数据多层数组']);
	#unset($test['a']);
	#unset($test['more']);
	
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
		echo json_encode($params);
	}
	else
	{
		echo 'error/warning:',"\n";
		print_r($t->echoParamsErrorMessage('json'));
	}

	echo "\n\n--------\n\n";
	echo "format:",$format,"\n";

	echo "params:",$string,"\n";

}

/**
 * 
 * 注：新增规则非必填项，数据类型非string时，允许传值为空串(''), 解析系统自动忽略该参数，以未传该参数的方式进行处理
 */

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

/**
整理思路
1. 递归检查每个层级的参数，是否都有必填项，和数据格式是否正确
2. 以下几个特殊情况下，做特殊处理
2.1 在必填项不存在或有错时，同时格式约定有默认值时，使用默认值，或者报错。要看调用参数的要求。
2.2 在格式约定有默认值，选填项数据有错时，报错还是使用默认值？
2.3 对数据的长度进行检查


*/


