<?php
ini_set("display_errors","on");

class GetOptW
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
			"FORMAT_SYNTAX_ERROR" => "格式描述的语法错误",

			// 参数部分错误，或参数数据错误
			"DATA_NOT_MATCHED" => "数据格式不匹配",
			"DATA_NOT_IN_VALID_RANGE" => "数据超合理范围",
			"DATA_NOT_IN_SET_RANGE" => "数据超出要求范围",
			"DATA_NOT_EXIST" => "KEY不存在",

			// 解析过程中，格式与数据匹配问题
			"TYPE_NO_MATCHED" => "没有匹配的类型",
			"TYPE_WITHOUT_METHOD" => "没有匹配的解析方法",
		);

		$this->all_errors = array();
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
			"string"=>".*", 
			"text"  =>".*", 
			"bool"  =>"", 

			// 扩展类型
			"year" => "[0-9]{4}",
			"month"=> "[12][0-9]",
			"date"=>"[0-9]{4}[-/ ]?[0-9]{2}[-/ ]?[0-9]{2}",	
			"time"=>"[0-9]{2}:[0-9]{2}:[0-9]{2}", 
			"datetime"=>"", 
			"weekday"=>"(Sun|Mon|Tue|Sat)",
			"age"=>"[0-9]{3}",
			"currency", // 数字

			"phone","mobile", // 带格式符号的数字
			"weekday", // 字母组合 
			"idcard", "plateNumber","verify","retCode", "MD5", // 字母数字组合
			"base64","email", "inlineImage",// 特定格式的字母数字符号的组合
			"username","password", // 有格式要求和一定顺序要求的字母数字符号的组合
			"lower","upper","letter", // 字母、数字的子集的组合
		);
	}

	public function getAllErrors()
	{
		return $this->all_errors;
	}

	public function getLastError()
	{
		return $this->last_error;
	}

	public function cleanErrors()
	{
		unset($this->all_errors);
		$this->all_errors = array();
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

	/**
	 * 用 format 格式检查 $var 变量是否符合规范, 并将可能的数值通过 $result 返回
	 */
	public function checkValue( $format, $var, &$result )
	{
		$f = $this->getFormat($format);

		if(!$f) {$this->last_error = "format can not be parse";return false;}

		if(!in_array($f['name'],$this->types))
		{
			$this->last_error = $this->errors['TYPE_NO_MATCHED'];
			$this->all_errors[] = $this->last_error ;
			$result = false ;
			return false ; 
		}

		if(method_exists($this,'CHECK'.$f['name']))
		{
			$result = call_user_func(array("GetOptW", 'CHECK'.$f['name']), $f, $var);
			return $result;
		}
		else
		{
			$this->last_error = $this->errors['TYPE_WITHOUT_METHOD'];
			$this->all_errors[] = $this->last_error ;
			$result = false ;
			return false; 
		}
	}

	public function CHECKemail($format, $value)
	{
		$result = '';
		$ret = preg_match("/[a-zA-Z]+[0-9a-zA-Z\-\._]*@[a-zA-Z0-9\.]*.[a-zA-Z]*/",$value, $matches);

		//echo $value,'<pre>';print_r($matches);print_r($format);
		if($ret)
		{
			$result = $matches[0];

			// email类型，只支持range检查，不做数值范围检查
			if($format['left']=='{')
			{
				$enum = explode(",",$format['range']);

				if(in_array($result, $enum))
					return $result;
				else
				{
					$this->last_error = $format['name'].":格式正确; 但不在 枚举 范围内。";
					$this->all_errors[] = $this->last_error;
					return false;
				}
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKint($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9]*/",$value, $matches);

		if($ret)
		{
			$result = $matches[0];

			if( $format['left']=='(' || $format['left']=='[' )
			{
				$range = explode(",",$format['range']);
				$min = intval($range[0]);
				$max = intval(array_pop($range));

				$outofrange = false;
				if($format['left']=='(' && intval($result) <= intval($min) )
				{
					$outofrange = true;
				}
				if($format['left']=='[' && intval($result) < intval($min) )
					$outofrange = true;
				if($format['right']==')' && intval($result) >= intval($max) )
					$outofrange = true;
				if($format['right']==']' && intval($result) > intval($max) )
					$outofrange = true;

				if($outofrange==true)
				{
					if($format['default']!='')
						$result = intval($format['default']);
					else
						$result = false;
				}
			}
			else
			{
				if($matches[0]=='' && $format['default']!='')
					$result = intval($format['default']);
				else if($matches[0]=='' && $format['default']=='')
					$result = false;
				else
					$result = intval($matches[0]);
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKfloat($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9\.]*/",$value, $matches);

		if($ret)
		{
			$result = $matches[0];

			if( $format['left']=='(' || $format['left']=='[' )
			{
				$range = explode(",",$format['range']);
				$min = floatval($range[0]);
				$max = floatval(array_pop($range));

				$outofrange = false;
				if($format['left']=='(' && floatval($result) <= $min )
					$outofrange = true;
				if($format['left']=='[' && floatval($result) < $min )
					$outofrange = true;
				if($format['right']==')' && floatval($result) >= $max )
					$outofrange = true;
				if($format['right']==']' && floatval($result) > $max )
					$outofrange = true;

				if($outofrange==true)
				{
					if($format['default']!='')
						$result = floatval($format['default']);
					else
						$result = false;
				}
			}			
			else
			{
				if($mathes[0]=='' && $format['default']!='')
					$result = floatval($format['default']);
				else if($matches[0]=='' && $format['default']=='')
					$result = false;
				else
					$result = floatval($matches[0]);
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKdouble($format, $value)
	{
		return $this->CHECKfloat($format, $value);
	}

	public function CHECKage($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9]*/",$value, $matches);

		if($ret)
		{
			$result = $matches[0];

			if( intval($result)<0 && intval($result)>120 )
			{
				$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_IN_VALID_RANGE'];
				$this->all_errors[] = $this->last_error;
				return false;
			}

			if( $format['left']=='(' || $format['left']=='[' )
			{
				$range = explode(",",$format['range']);
				$min = intval($range[0]);
				$max = intval(array_pop($range));

				$outofrange = false;
				if($format['left']=='(' && intval($result) <= intval($min) )
				{
					$outofrange = true;
				}
				if($format['left']=='[' && intval($result) < intval($min) )
					$outofrange = true;
				if($format['right']==')' && intval($result) >= intval($max) )
					$outofrange = true;
				if($format['right']==']' && intval($result) > intval($max) )
					$outofrange = true;

				if($outofrange==true)
				{
					if($format['default']!='')
						$result = intval($format['default']);
					else
						$result = false;
				}
			}
			else
			{
				if($mathes[0]=='' && $format['default']!='')
					$result = intval($format['default']);
				else if($matches[0]=='' && $format['default']=='')
					$result = false;
				else
					$result = intval($matches[0]);
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKphone($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9 \-]*/",$value, $matches);

		if($ret)
		{
			$result = $matches[0];

			if( $format['left']=='(' || $format['left']=='[' )
			{
				// 电话号码不做范围检查，但可以做枚举检查
			}
			elseif($format['left']=='{')
			{
				$enum = explode(",",$format['range']);

				if(in_array($result, $enum))
					return $result;
				else
				{
					$this->last_error = $format['name'].":格式正确; 但不在 枚举 范围内。";
					$this->all_errors[] = $this->last_error;
					return false;
				}
			}			
			else
			{
				if($matches[0]=='' && $format['default']!='')
					$result = $format['default'];
				else if($matches[0]=='' && $format['default']=='')
					$result = false;
				else
					$result = $matches[0];
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKmobile($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9 \+]*/",$value, $matches);

		if($ret)
		{
			$result = $matches[0];

			if( $format['left']=='(' || $format['left']=='[' )
			{
				// 手机号不需要检查范围
			}
			elseif($format['left']=='{')
			{
				$enum = explode(",",$format['range']);

				if(in_array($result, $enum))
					return $result;
				else
				{
					$this->last_error = $format['name'].":格式正确; 但不在 枚举 范围内。";
					$this->all_errors[] = $this->last_error;
					return false;
				}
			}	
			else
			{
				if($matches[0]=='' && $format['default']!='')
					$result = $format['default'];
				else if($matches[0]=='' && $format['default']=='')
					$result = false;
				else
					$result = $matches[0];
			}

			// 不做取值范围检查
			return $result;
		}
		else
		{
			$this->last_error = $format['name'].':'.$this->errors['DATA_NOT_MATCHED'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKcurrency($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9]*\.[0-9]{2}/",$value, $matches);

		return $this->CHECKfloat($format, $value);
	}

	public function CHECKinlineImage($format, $value)
	{
		// data:image/png;base64,		
	}

	public function isValidateJSONString($strJSON)
	{
		$json = json_decode($strJSON);
		$err  = json_last_error_msg();

		if('No error'==$err)
		{
			return $json;
		}
		else
			return false;
	}

	// 解析基础数据类型
	private function parseData($jsonFormat, $jsonObject)
	{
		$arrFormat = $this->getFormat($jsonFormat);

		// print_r(array($arrFormat,$jsonObject));die('断路施工。。。');
		if('*'==$arrFormat['option'] && stricmp($arrFormat['option'],'bool')==0 && $jsonObject===false)
		{
			return true;
		}
		else if('*'==$arrFormat['option'] && stricmp($arrFormat['option'],'bool')==0 && $jsonObject===false)
		{
			return true;
		}
		else if('*'==$arrFormat['option'] && $jsonObject!=false)
		{
			return true;
		}
		else if(''==$arrFormat['option']) // jsonObject==false or jsonObject!=false 
		{
			return true;
		}
		else if('*'==$arrFormat['option'] && $jsonObject=='0')
		{
			$callstack = implode('->',$this->callStack);
			$this->last_error = "Line".__LINE__.":".$callstack.':'.$this->errors['DATA_NOT_EXIST'];
			$this->all_errors[] = $this->last_error ;

			return false;
		}
		/*
		// TODO: 完成对基础数据类型的解析
		$ret = $this->checkValue( $v, isset($jsonObject->$k)?$jsonObject->$k:false, $value );

		// 传入值符合要求, 则返回true;传入值不能检测通过则返回false
		if($ret)
		{
			// TODO: 对数值匹配结果进行处理
		}
		*/
	}

	// 解析数组格式
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

	// 解析对象格式
	private function parseObject($jsonFormat, $jsonObject)
	{
		$result = new stdClass();

		if(!is_object($jsonObject))
		{
			$this->error_msg = "DATA_NOT_MATCHED";
			$this->last_error = "DATA_NOT_MATCHED";
			$this->all_errors[] = $this->error_msg;
			return false;
		}

		if( !is_object($jsonFormat) )
		{
			// TODO: 
			$this->error_msg = "DATA_NOT_SUPPORT_MULTIFORMAT_ARRAY";
			return false;
		}

		// obj 模式下, $format 内的 key 的个数应该大于等于 $obj 内 key 的个数
		foreach($jsonFormat as $k=>$v)
		{
			$value = '';
			if(is_array($v))
			{
				if(!isset($jsonObject->$k))
				{
					$callstack = implode('->',$this->callStack);
					$this->last_error = $callstack.'->'.$k.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
					$this->all_errors[] = $this->last_error ;
					return $result;
				}

				$this->callStack[] = '(Array)';
				$this->parseArray( $v, $jsonObject->$k );
				array_pop($this->callStack);
			}
			else if(is_object($v))
			{
				if(!isset($jsonObject->$k))
				{
					$callstack = implode('->',$this->callStack);
					$this->last_error = $callstack.'->'.$k.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
					$this->all_errors[] = $this->last_error ;
					return $result;
				}

				$this->callStack[] = "(Object)$k";
				$this->parseObject( $v, $jsonObject->$k );
				array_pop($this->callStack);
			}
			else 
			{
				$this->callStack[] = "(Data)$k";
				$this->parseData( $v, isset($jsonObject->$k)?$jsonObject->$k:false );
				array_pop($this->callStack);
			}
		}
		return $result;
	}

	/**
	 * format 是格式字符串, 可以单级可以多级，
	 */
	public function isFormatOK($format)
	{
		$noerror = 'No error';

		if($format=='') $format='{}';

		$json = json_decode($format);

		$str = json_last_error_msg();
		

		// 目前的检查, 只能判断为符合json语法，尚未能判断是否
		if($noerror==$str)
		{
			// 保存接口格式
			$this->format = $json;

			// TODO: 按照参数接口格式进行检查
			#$ret = $this->parseData($json, $obj);

			return true; //$ret;
		}
		else
		{
			#$this->last_error = 'FORMAT_SYNTAX_ERROR';
			$callstack = implode('->',$this->callStack);
			$this->last_error = $callstack.':'.$this->errors['FORMAT_SYNTAX_ERROR'];
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	// 用检查过的格式字符串从参数表中获取参数
	public function parseParams($objParams)
	{
		if(is_array($this->format))
		{
			$this->callStack = [];
			$this->callStack[] = 'Array';
			return $this->parseArray($this->format, $objParams);
		}

		if(is_object($this->format))
		{
			$this->callStack = [];
			$this->callStack[] = 'Object';
			return $this->parseObject($this->format, $objParams);
		}

		// TODO: 能接受没有 [] {} 包裹的纯值吗? 暂定为不能支持吧
		return false;
	}

	public function matchFormatAndParams($format, $params, $type='json')
	{

	}

	public function getWebParams($format, $inputString)
	{
		$isFormatOk = $this->isFormatOK($format);

	}

    /*
    usage:
    $params = $this->getJSONParams($format);
    if(null==$params) return $this->echoParamsErrorMessage('JSON');
     */
    public function getJSONParams($format)
    {
        // TODO: 1. 检查 format 语法是否符合要求
        // TODO: 2. 获取参数
        // TODO: 3. 检查参数是否符合 format 要求
       
    
        $opt = new GetOptW();
        $ret = $opt->isFormatOK($format);
        if(!$ret)
        {
            $this->paramsParseErrors = "Paramaters Format Error";
            return false;
        }

        $strInput = file_get_contents("php://input");
        if($strInput==""){
            $params = new stdClass();
        }
        else{
            $params = json_decode($strInput,false);
        }

        $params = json_decode($strInput,false);

        $str = json_last_error_msg();

        if('No error'==$str)
        {
            $ret = $opt->parseParams($params);
           
            if( count($opt->all_errors)<1 )
            {
                return json_decode(json_encode($params),true);
            }
            else
            {
                $this->paramsParseErrors = implode("\n",$opt->all_errors);
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
    public function getPOSTParams($format)
    {
        return $_POST;
    }
    public function getGETParams($format)
    {
        return $_GET;
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

}



function _test()
{
	$format = "int";
	$format = "int(100,1000]";
	$format = "int:10";
	$format = "int(100,1000]:17:100";
	$format = "*int(100,1000]:17:100";

	$tool = new GetOptW;
	$var  = "5460";

	$value = false;
	$ret   = $tool->checkValue($format, $var, $value);
	
	if(!$value)
		echo $tool->getLastError();
	else
		echo $value;
}

function _testJSON()
{
	$format = '
{
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
	"vip":"*bool"
}	
	';
	$string = '
{
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
}	
	';

	$t = new GetOptW();

	$params = json_decode($string);

	// 1. 检查格式是否有问题
	// 2. 检查参数是否为 json 格式
	// 3. 

	if($t->isFormatOK($format))
	{
		$result = $t->parseParams($params);	
		if( count($t->all_errors)<1 )
			print_r($json);
		else
			print_r($t->all_errors);
	}
	else
	{
		echo 'Format String Error';
	}

	
}
