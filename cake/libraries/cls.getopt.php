<?php
class Getopt
{
	public function __construct()
	{
		$this->_formats = array(
			"email"=>"/[a-zA-Z0-9]+@/",
		);
	}

	public static function json_check_foramt($format, $params)
	{
		return true;
	}


	private function getJSONFormatExample()
	{
		$strFormat = '
		{
			"key0":"email",   // comment 这里是注释
			"key1":"string",   // comment 这里是注释
			"*key2":"number",  // key前的 "*" 表示这个key为必填参数
			"key3":"int",      // 表述数据类型包括 "string phone mobile email number int float double object array"
			"key4":"float[min,,max]",	   // 表述数据范围的格式包括 数字范围[min,,max], [1,10)
			"key5":"object|name",   // 这里可以再次调用format进行检查，但不在本类内进行递归
			"key6":"array|name",
		}
		';

		return $strFormat;
	}

	private function getArrayFormatExample()
	{
		$strFormat = "

		";

		return $strFormat;
	}

	public function addFormat($name, $format)
	{

	}

	public function checkJsonFormat($strFormat, $objParams)
	{

	}

	public function checkArrayFormat($strFormat, $arrParams)
	{

	}

}
