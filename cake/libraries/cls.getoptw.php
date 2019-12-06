<?php
ini_set("display_errors","on");

class GetOptW
{
	public function __construct()
	{
		$this->types = array(
			"int", "float", "double", "age",
			"date",	"time", "datetime","year",
			"email","phone","mobile",
			"base64","MD5",
			"username","password",
			"lower","upper","letter","string",
		);
		// DONE: int email
		// TODO: float", "double", "age",			"date",	"time", "datetime","year","phone","mobile", "base64","MD5","username","password","lower","upper","letter","string",

		$this->errors = array(
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
		//  string : enum {1,2,3,4,5}
		// example: "role":"*string{papa, mama}:4#papa//role name 
		// 格式语法 : "[*]<格式名>[数值范围][:长度][#默认值]//说明"
		// 格式名，目前所支持的格式包括: email, phone, mobile, date, time, datetime, int, float, base64, MD5 ...
		// 取值范围,以{[(三个符号中的一个开始，由{开始则必须由}结束, 如{a,b,c}, 表示为枚举类型; [()] 的组合表示为 时间和数字可以用集合形式表示取值范围如 (1,100], 表示 大于1小于等于100的范围.
		// :长度,表示为需要检查的变量里原始值的字符长度
		// 默认值，当取值失败，或者取值超范围时，以默认值做为返回值，同时发出一个错误信息
		// 说明，参数格式的表达过于技术化，需要有给产品或业务相关人员看得懂的说明，更好的表达这些设置的目的

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
		$ret = preg_match("/([\*]*)?([0-9a-zA-Z@]*)(([\{\[\(])(.*)([\)\]\}]))?(:([0-9]*))?(#([^\/]*))?(\/\/(.*))?/",$format, $matches);
		if($ret)
		{
			$format_result = array(
				"option"	=>$matches[1],
				"name"		=>$matches[2],
				"left"		=>$matches[4],
				"range"		=>$matches[5],	
				"right"		=>$matches[6],
				"length"	=>$matches[8],
				"default"	=>$matches[10],
				"comment"	=>$matches[12]);
		}

		return $format_result;
	}

	public function getValue($var, $format)
	{
		$f = $this->getFormat($format);

		if(!$f) {$this->last_error = "format can not be parse";return false;}

		if(!in_array($f['name'],$this->types))
		{
			$this->last_error = $this->errors['TYPE_NO_MATCHED'];
			$this->all_errors[] = $this->last_error ;
			return false; 
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
			$this->last_error = '数据不匹配';
			$this->all_errors[] = $this->last_error;
			return false;
		}
	}

	public function CHECKint($format, $value)
	{
		$result = '';
		$ret = preg_match("/(\+|\-)?[0-9]*/",$value, $matches);

		//echo $value,'<pre>';print_r($matches);print_r($format);
		if($ret)
		{
			$result = $matches[0];

			// email类型，只支持range检查，不做数值范围检查
			if( $format['left']=='(' || $format['left']=='[' )
			{
				$range = explode(",",$format['range']);
				$min = intval($range[0]);
				$max = intval(array_pop($range));

				$outofrange = false;
				if($format['left']=='(' && intval($result) <= $min )
					$outofrange = true;
				if($format['left']=='[' && intval($result) < $min )
					$outofrange = true;
				if($format['left']==')' && intval($result) >= $max )
					$outofrange = true;
				if($format['left']==']' && intval($result) > $max )
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
			$this->last_error = '数据不匹配';
			$this->all_errors[] = $this->last_error;
			return false;
		}		
	}

	public function checkJSON()
	{

	}

	public function checkArray()
	{

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

	$value = $tool->getValue($var, $format);
	
	if(!$value)
		echo $tool->getLastError();
	else
		echo $value;
}

