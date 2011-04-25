<?php

// ----------------------------- 通用分页函数 -----------------------------------------

// 去掉直接获取 $_REQUEST, 保证本函数的整洁
function getPages($intTotal,$intCurrent = 0,$intPageSize = 50)
{
	$arrPage['size'] 	= $intPageSize;	//分页大小
	$arrPage['current'] = $intCurrent;	//当前的分页参数	
	
	$arrPage['total'] 	= $intTotal;				
		
	$arrPage['count'] 	= ceil($arrPage['total']/$arrPage['size']); 		
	$arrPage['current'] = $arrPage['current'] < 1?1:$arrPage['current'];
	$arrPage['current'] = $arrPage['current'] > $arrPage['count']?$arrPage['count']:$arrPage['current'];

	//根据当前页码计算记录列表的起始位置
	$arrPage['startid'] = ($arrPage['current'] - 1) * $arrPage['size'];;

	return $arrPage;
}

//取得分页链接
function getPageLinks($arrPage)
{
	//显示多少个分页链接
	$intShowLinkCount = 4;

	//显示链接页个数
	$arrPage['start']	= $arrPage['current'] - $intShowLinkCount;
	$arrPage['end']		= $arrPage['current'] + $intShowLinkCount - 1;

	if($arrPage['end'] > $arrPage['count'])
	{
		$arrPage['end'] = $arrPage['count'];
	}

	if($arrPage['start'] < 1)
	{
		$arrPage['start'] = 1;
	}

	//取得上页及下页的页数号
	$arrPage['prev'] 	= $arrPage['current'] - 1;
	$arrPage['next'] 	= $arrPage['current'] + 1;

	//判断是否显示向前
	if($arrPage['start'] <= 3)
	{
		$arrPage['ext_start'] =  1;
	}
	else 
	{
		$arrPage['ext_start'] =  $arrPage['start'];
		
		$arrPageLinks[] = array('id'=>1,'title'=>1,'link'=>1);
		$arrPageLinks[] = array('id'=>2,'title'=>2,'link'=>2);
		$arrPageLinks[] = array('id'=>$arrPage['start']-1,'title'=>'...','link'=>$arrPage['start']-1);
	}
	
	if($arrPage['end'] > $arrPage['count']-3)
	{
		$arrPage['ext_end'] =  $arrPage['count'];
	}
	else 
	{
		$arrPage['ext_end'] =  $arrPage['end'];
	}

	//显示中间过渡页链接
	for($i=$arrPage['ext_start'];$i<=$arrPage['ext_end'];$i++)
	{
		$arrPageLinks[] = array('id'=>$i,'title'=>''.$i.'','$arrPage'=>$i,'link'=>$i);
	}

	//判断是否显示尾页
	if($arrPage['ext_end'] <= $arrPage['count'] -3)
	{
		$arrPageLinks[] = array('id'=>$arrPage['end']+1,'title'=>'...','link'=>$arrPage['end']+1);
		$arrPageLinks[] = array('id'=>$arrPage['count']-1,'title'=>$arrPage['count']-1,'link'=>$arrPage['count']-1);
		$arrPageLinks[] = array('id'=>$arrPage['count'],'title'=>$arrPage['count'],'link'=>$arrPage['count']);
	}
	
	return $arrPageLinks;
}

// ------------------------------------  结束通用分页函数   -------------------------

// ------------------------------------  又一个版本的分页程序 -----------------------
function get_page_links($page)
{
	//显示多少个分页链接
	$link_num = 5;

	//显示链接页个数
	//$page_start = floor(($page_current-1)/$link_num) * $link_num + 1;
	$page['start']	= $page['current'] - $link_num ;
	$page['end']	= $page['current'] + $link_num - 1;

	if($page['end'] > $page['count'])
	{
		$page['end'] = $page['count'];
	}

	if($page['start'] < 1)
	{
		$page['start'] = 1;
	}

	//取得上页及下页的页数号
	$page['prev'] 	= $page['current'] - 1;
	$page['next'] 	= $page['current'] + 1;

	//判断是否显示首页
	if($page['current'] > 1)
	{
		$page_link[] = array('title'=>'首页','link'=>1);
		$page_link[] = array('title'=>'上一页','link'=>$page['prev']);
	}

	//显示向前的...
	if($page['start'] > 1)
	{
		$page_link[] = array('title'=>'...','link'=>($page['start']-1));
	}

	//显示中间过渡页链接
	for($i=$page['start'];$i<=$page['end'];$i++)
	{
		$page_link[] = array('title'=>$i,'link'=>$i);
	}

	//显示向后
	if($page['end'] < $page['count'])
	{
		$page_link[] = array('title'=>'...','link'=>($page['end']+1));
	}

	//判断是否显示尾页
	if($page['current'] < $page['count'])
	{
		$page_link[] = array('title'=>'下一页','link'=>$page['next']);
		$page_link[] = array('title'=>'尾页','link'=>$page['count']);
		
	}

	return $page_link;
}
// --------------------------------------------------

// 获取客户端IP, 考虑了使用代理上网，和手机上网的一些状况
function getRemoteIp() {
	if (isset($_SERVER)) {
		if (isset($_SERVER[HTTP_X_FORWARDED_FOR])) {
			$realip = $_SERVER[HTTP_X_FORWARDED_FOR];
		} elseif (isset($_SERVER[HTTP_CLIENT_IP])) {
			$realip = $_SERVER[HTTP_CLIENT_IP];
		} else {
			$realip = $_SERVER[REMOTE_ADDR];
		}
	} else {
		if (getenv("HTTP_X_FORWARDED_FOR")) {
			$realip = getenv( "HTTP_X_FORWARDED_FOR");
		} elseif (getenv("HTTP_CLIENT_IP")) {
			$realip = getenv("HTTP_CLIENT_IP");
		} else {
			$realip = getenv("REMOTE_ADDR");
		}
	}
	return $realip;
}

