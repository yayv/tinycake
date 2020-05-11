<?php

class Webapi
{
	public function __construct()
	{
		$this->callStack = [];

		$this->format = false ;

		$this->last_error = false ;

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
			"FORMAT_UNKNOWN_KEY_ERROR" => "存在未设置的参数",

			// 参数部分错误，或参数数据错误
			"DATA_NOT_MATCHED" => "数据格式不匹配",
			"DATA_NOT_IN_VALID_RANGE" => "数据超合理范围",
			"DATA_NOT_IN_SET_RANGE" => "数据超出要求范围",
			"DATA_KEY_NEED_EXIST" => "缺少了必填的KEY",
			"DATA_NOT_EXIST" => "KEY不存在",

			// 解析过程中，格式与数据匹配问题
			"TYPE_NO_MATCHED" => "没有匹配的类型",
			"TYPE_WITHOUT_METHOD" => "没有匹配的解析方法",
		);

		$this->morekeys = '...';  // 参数的格式表中存在这个key，则可以接受不在格式设定中的参数，否则，多余的参数会被抛弃

		$this->all_errors = array();

		$this->paramsParseErrors = '';
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
		$formats = array(
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
			"currency", // 数字

			"phone", // 带格式符号的数字
			"mobile", // 带格式符号的数字
			"weekday", // 字母组合 
			"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
			"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
			"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
			"lower","upper","letter", // 字母、数字的子集的组合
		);
	}

	private function getFormat($format)
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
		}

		return $format_result;
	}

	// 检查每行格式字符串是否正确
	public function parseFormatString($strFormat)
	{
		$format = $this->getFormat($strFormat);
		/*
		$call = implode("->", $this->callStack);
		echo $call;
		print_r($format);
		*/
	}

	private function parseObject($jsonFormat, $jsonObject)
	{
		$result = [];
		if(!is_array($jsonObject))
		{
			$this->error_msg = "DATA_NOT_MATCHED";
			return false;
		}

		if(count($jsonFormat)>1)
		{
			// TODO: 
			$this->error_msg = "DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY";
			return false;
		}

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_array($jsonFormat[0]))
			{
				// TODO: 向下一层继续解析
				#$this->parse
			}
			else if(is_object($jsonFormat[0]))
			{

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
					$value = $this->parseData($jsonFormat[0], $vv);
					// var_dump($this->callStack);
					array_pop($this->callStack);
				}
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				$callstack = implode('->',$this->callStack);
				print_r($callstack);
				$this->last_error = 'CS:'.$callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}
/*
		foreach($obj as $v)
		{
			if(is_array($v))
			{

			}

			if(is_object($v))
			{

			}

			// is data
			// int float string ...
		}
*/
		// die("这里是断路施工中...\n");
		return $result;
	}

	private function parseArray($jsonFormat, $jsonObject)
	{
		$result = [];
		if(!is_array($jsonObject))
		{
			$this->error_msg = "DATA_NOT_MATCHED";
			return false;
		}

		if(count($jsonFormat)>1)
		{
			// TODO: 
			$this->error_msg = "DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY";
			return false;
		}

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_array($jsonFormat[0]))
			{
				// TODO: 向下一层继续解析
				#$this->parse
			}
			else if(is_object($jsonFormat[0]))
			{

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
					$value = $this->parseData($jsonFormat[0], $vv);
					// var_dump($this->callStack);
					array_pop($this->callStack);
				}
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				$callstack = implode('->',$this->callStack);
				print_r($callstack);
				$this->last_error = 'CS:'.$callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}

		return $result;
	}

	public function isValueFormatStringOk($stringFormat)
	{
		// 这里判断具体一个值是否符合格式说明的要求
	}

	public function getValueFormat($jsonFormat)
	{

	}

	private function parseFormatObject($jsonFormat)
	{
		$result = [];

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_object($jsonFormat->$k))
			{
				$this->callback[] = '(Object)'.$k;
				// TODO: 向下一层继续解析
				$this->parseFormatObject($v);
			}
			else if(is_array($jsonFormat->$k))
			{
				$this->callback[] = '(Array)'.$k;
				// TODO: 向下一层继续解析
				$this->parseFormatArray($v);
			}
			else if(is_string($jsonFormat->$k))
			{
				// TODO:解析格式, 检查参数的值是否匹配
				$ret = $this->parseFormatString($v);
				/*
				{
					// if($vv==null){
					// 	continue;
					// }
					$this->callStack[] = "KEY $k";
					$value = $this->parseData($jsonFormat->$k, $vv);
					// var_dump($this->callStack);
					array_pop($this->callStack);
				}
				*/
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				print_r($callstack);
				$this->last_error = 'CS:'.$callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}

		return $result;
	}

	private function parseFormatArray($jsonFormat)
	{
		$result = [];

		if(count($jsonFormat)>1)
		{
			// TODO: 
			$this->error_msg = "DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY";

			return false;
		}

		foreach($jsonFormat as $k=>$v)
		{
			// 根据格式,依次检查Obj
			if(is_array($jsonFormat[0]))
			{
				// TODO: 向下一层继续解析
				#$this->parse
			}
			else if(is_object($jsonFormat[0]))
			{

			}
			else if(is_string($jsonFormat[0]))
			{
				// TODO:解析格式, 检查参数的值是否匹配
				/*
				foreach($jsonFormat[] as $kk=>$vv)
				{
					// if($vv==null){
					// 	continue;
					// }
					$this->callStack[] = "KEY $k";
					$value = $this->parseFormatString($jsonFormat[0], $vv);
					// var_dump($this->callStack);
					array_pop($this->callStack);
				}
				*/
			}
			else
			{
				// 不是对象,不是数组,不是字符串,那格式出错了
				print_r($callstack);
				$this->last_error = 'CS:'.$callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;
				return false;
			}
		}

		return $result;
	}

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

		if(is_array($jsonFormat))
		{
			// 循环 item
			$this->callStack = [];
			$this->callStack[] = 'Array';
			return $this->parseFormatArray($jsonFormat);
		}
		else // is format string
		{
			if(is_string($jsonFormat))
				$ret = $this->parseFormatString($jsonFormat);	
			else
			{
				$callstack = implode('->',$this->callStack);
				$this->last_error = 'formatString:'.$callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
				$this->all_errors[] = $this->last_error ;				
			}
		}
	}

	/*
	获取对应参数格式描述的参数值
	*/
	public function getValue($key)
	{

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
            header('Content-Type: application/json');
            echo '{"code":"fail","message":"' . $this->paramsParseErrors . '"}';
        } else {
            header('Content-Type: text/html');
            echo '参数解析失败:' . $this->paramsParseErrors;
        }
        return;
    }

	public function getJSONParams($strFormat, $strParams)
	{
		// 入口程序

		// 1. 检查 strFormat 是否符合 json 格式 


		// 2. 检查 strParams 是否符合 json 格式 		

		// 3. 根据 strFormat 按层次获取 strParams 里的参数

        // TODO: 1. 检查 format 语法是否符合要求
        // TODO: 2. 获取参数
        // TODO: 3. 检查参数是否符合 format 要求
       
        $ret = $this->isFormatOK($strFormat);
        if(!$ret)
        {
			$this->last_error = $this->errors['FORMAT_JSON_STRUCT_ERROR'];
			$this->all_errors[] = $this->last_error;
			$this->paramsParseErrors = implode("\n",$this->all_errors);
            return false;
        }

        $params = json_decode($strParams);

        $str = json_last_error_msg();

        if('No error'==$str)
        {
            $ret = $this->parseParams($params);
           
            if( count($this->all_errors)<1 )
            {
                return $params;
            }
            else
            {
                $this->paramsParseErrors = implode("\n",$this->all_errors);
                return false;
            }
        }
        else
        {
            $this->paramsParseErrors = $str ;
            return false;
        }

        return false;
	}
}


function _testJSON()
{
	$format = '{
		"enterpriseId":"*int//企业id",

		"username":"*string",
		"mobile":"*mobile",
		"idcard":"*idcard",

		"province":"*string",
		"city":"*string",

		"countPeople":"int//可选",
		"babySeats":"int//可选",
		"vehicleModelId":"*int//车系id",
		"vehicleId":"*int//车型id,可能不需要",

		"rentRetailId":"*int//取车门店id",
		"returnRetailId":"*int//换车门店id",

		"orderStartDate":"*datetime",
		"orderEndDate":"*datetime",

		"baseService":"*bool//基础服务费。价格由服务器端配置决定，这里只做开关选择",
		"carService":"*bool//整备服务费。价格由服务器端配置决定，这里只做开关选择",
		"deliveryService":"*bool//基础服务费。价格由服务器端配置决定，这里只做开关选择",
		"insurance":"*bool",
		"vip":"*bool",

		"normalArray":["string//普通数组，每个元素都是字符串"],
		"objArray":[{
			"customerId":"*int//用户id",
			"customerName":"*string//用户名",
			"customerIdcard":"*idcard//用户身份证号",
			"customerMobile":"*mobile//用户手机号"
			}]
	}';

	$string = '{
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
	}';

	$t = new Webapi();
	$params = $t->getJSONParams($format, $string);

	if($t->isParseOk()) 
	{
		print_r($params) ;
	}
	else
		print_r($t->echoParamsErrorMessage('json'));

	echo "\n\n";
/*
            $params = $this->getJSONParams($requestRule);

            if (false==$params) {
                return $this->echoParamsErrorMessage('json');
            }
*/
}

