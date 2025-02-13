<?php

/*
example_role.json
{
	"role":"example",	
	"order":["allow","deny"],
	"allow":[
		"path1/method1",
		"path2/method2",
		"path3/method3",
	],
	"deny":[
		"path4/method4",
		"path5/method5",
		"path6/method6",
	]
}
*/
class roleAccessControll
{
	/*
	$this->_path = '';
	$this->_roles = [];
	*/

	public function __construct($role=['common'], $path='data')
	{
		$this->_path = $path;
		$this->_roles = $role;
		$this->_acl = [];

		$this->_message = "";

		$this->load();
	}

	public function load()
	{
		if(!is_dir($this->_path))
			return false;
		
		$this->_message = '';
		if(is_array($this->_roles))
		foreach($this->_roles as $k=>$v){
			$filename = "./".$this->_path."/".$v.".json";
			if(is_file($filename))
			{
				$this->_acl[$v] = json_decode(file_get_contents($filename));
			}
			else
			{
				$this->_message .= "Missing file ./".$this->_path."/$v.json\n";
			}
		}

		if($this->_message=='')
			return true;
		else
			return false;
	}

	public function isAllow($model,$method,$list)
	{
		foreach($list as $k=>$v)
		{

		}

		return true;
	}

	public function isDeny($model, $method ,$list)
	{
		foreach($list as $k=>$v)
		{

		}

		return true;
	}

	public function canAccess($model, $method)
	{
		$isAllow = false;
		$isDeny = false;

		foreach($this->_acl as $k=>$v)
		{
			$isAllow = false;
			if($v->order=='allow,deny')
			{
				// check allow
				$allow = $this->isAllow($model, $method, $v->allow);

				// check deny
				$deny = $this->isDeny($model, $method, $v->deny);

				if($deny==true)
					$isAllow = $allow;
			}
			else // deny,allow
			{
				// check deny
				$deny = $this->isDeny($model, $method, $v->deny);

				// check allow
				$allow = $this->isAllow($model, $method, $v->allow);

				if($allow==true)
					$isAllow=$deny;
			}
			if($isAllow)
				return true;
		}

		return false;
	}


}

