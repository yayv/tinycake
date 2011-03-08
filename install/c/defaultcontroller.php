<?php 
class defaultcontroller extends Controller
{
	public function initParams()
	{
	}
	
	public function missing($controller, $action)
	{
		#echo "your controller: $controller is MISSING\n";
		#echo "your $action of controller is MISSING";
		
		if($action=='')
		{
			$file = file_get_contents('../framework/templates/controller.template');
			
			echo "控制器 $controller 类程序不存在, 请复制以下代码，并以 $controller.php 为文件名保存在你的 c 目录下。<br/>";
			echo "<textarea style='width:90%;height:60%;'>";
			echo strtr($file, array('{$name}'=>$controller));
			echo "</textarea>";
		}
		else
		{
			$file = file_get_contents('../framework/templates/ctrl.function.template');
			
			echo "控制器方法 $action 不存在, 请复制以下代码，增加到你的 c/$controller.php 文件中。<br/>";
			echo "<textarea style='width:90%;height:60%'>";
			echo strtr($file, array('{$name}'=>$action));
			echo "</textarea>";
		}
	}
	
	public function index()
	{
		parent::initTemplateEngine('./v/def/','./v/_run/');
		parent::initAssign();
		
		$body = file_get_contents('data/todo.txt');
		$body = strtr($body,array("\n"=>'<br/>',' '=>'&nbsp;', "\t"=>'&nbsp;&nbsp;&nbsp;&nbsp;'));
		
		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	
		$this->tpl->display('index.tpl.html');
	}
}
