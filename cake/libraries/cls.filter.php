<?php

/*
'==========================================
'Author			:Thatday
'LastModify		:2008-06-21
'WorkTeam		:www.CEGSoft.com
'==========================================
*/

/*

调用:
include_once('cls.filter.php');
$objFilter	= new filter();
$strResult	= $objFilter->checkContent($strContent);

//返回'OK'表示检测正常,则否返回检测到的敏感词列表

if($strResult == 'OK')
{
	echo '内容良好';
}
else
{
	echo '发现敏感词:'.$strResult;
}

*/

class filter
{
	var $arrFilterWords = array();
	//缓存文件
	var $strCacheFile	= '';
	//是否启用缓存
	var $isCacheEnable	= true;
	
	function __construct()
	{
		$this->strCacheFile		= dirname(__FILE__) . '/data.filter.dat';
		$this->arrFilterWords	= $this->loadFilterWordsAll();
	}
	
	//检测通过返回OK,不通过返回相应的关键词
	function checkContent($strContent)
	{
		global $CONFIG,$DB,$db_prefix;
		
		$strContent		= trim($strContent);
		
		if($strContent == '')
		{
			//如果内容为空
			return false;
		}
		
		$arrFilterWords	= $this->arrFilterWords;

		$strContent			= $this->fixContent($strContent);
		$strContent_Lower	= mb_strtolower($strContent,'UTF-8');
	
		//第二步:不良信息检测
		//Edit By Thatday 20081113
		//暂时去除不良信息的检测
		$arrFilterResult 	= $this->contentFilter($strContent_Lower,$arrFilterWords['main']);
	
		//如果结果非数组，表示没有检测到不良信息关键字
		if(is_array($arrFilterResult['index']))
		{
			$arrShowWords = array();
			
			//将已经检测出来的关键词存至索引表filer_index中
			foreach ($arrFilterResult['index']  as $strWord => $intWordCount)
			{
				$arrShowWords[] = $strWord;
			}
			
			foreach ($arrFilterResult['counter']  as $strWordType => $arrWordCount)
			{
				$strCountKey = 'count_'.substr($strWordType,-1);
				$strTimesKey = 'times_'.substr($strWordType,-1);
				$arrResultSql[$strCountKey] += $arrWordCount['count'];
				$arrResultSql[$strTimesKey] += $arrWordCount['times'];
			}
			
			$strShowWords = implode(',',$arrShowWords);
			/*
			if(mb_strlen($strShowWords,'UTF8') >20)
			{
				$strShowWords = mb_substr($strShowWords,0,20,'UTF8').'...';
			}
			*/
			
			return $strShowWords;
			//showMessage($intDocId . " [" . intval($arrResultSql['count_1']) . "-" . intval($arrResultSql['count_2'])."] ".$strShowWords);
		}
		else
		{
			return 'OK';
		}
	}
	
	/*
	修正文本内容
	*/
	function fixContent($strContent)
	{
		global $CONFIG;
	
		$strContent = html_entity_decode($strContent);
	
		//去除无意义的标点符号
		$strContent = strtr($strContent,$CONFIG['DATA']['arrSbcToDbc']);
	
		$arrCleanChar = array('《》','「」','"','\'','*','#','$','ˉ','&','+','―','?','/');
		$strContent = str_replace($arrCleanChar,'',$strContent);
	
		$strContent = preg_replace('/([\t ])+/i',' ',$strContent);
		$strContent = preg_replace('/([\(\[\<\{][ _-]*[\)\]\>\}])/i','',$strContent);
	
		//修正"报　告　书"->"报告书"
		$strContent = preg_replace('/([\x80-\xff]) /i',"$1",$strContent);
	
		//先替换双字节为单字节字符串，不然容易出乱码
		$strContent = str_replace('…','...',$strContent);
		
		//清除长数字串如:电话号码之类
		//$strContent = preg_replace('/([0-9. \-,\(\)\[\]\/+:;Ee]){8,}/i','',$strContent);
	
		//去除,,,,,, 及,,>>  .等堆砌的符号
		$strContent = preg_replace('/([^\w\x80-\xff\(\) \r\n]){2,}/i',"$1",$strContent);
	
		//使用文本编辑器在c m a i n f r a m e类的消息映射中加入下列代码,代码应添加在c l a s s wi z a r d { { } }之后:
		//使用文本编辑器在cmainfram e类的消息映射中加入下列代码,代码应添加在class wizard { { } }之后:
		$strContent = preg_replace('/(\w[,.]?) +/i',"$1",$strContent);
	
		return  $strContent;
	}	
	
	//检测不良关键词
	function contentFilter($strContent,$arrWordsGroup,$arrFilterResult = array())
	{
		$arrFilterWords		= $this->arrFilterWords;
		
		$arrFilterWordsSkip = $arrFilterWords['skip'];
	
		//获取内容中存在的关键词
		//并对内容进行归类
		foreach ($arrWordsGroup as $strFilterWord => $strWordValue)
		{
			$intResultCount = mb_substr_count($strContent, $strFilterWord,'UTF-8');
			
			if($intResultCount > 0)
			{
				//如果是数据则进行递归处理
				if(is_array($strWordValue))
				{
					$arrFilterResult = $this->contentFilter($strContent,$strWordValue,$arrFilterResult);
				}
				else 
				{
					//如果找到的词岐意,则进一步排除
					if(is_array($arrFilterWordsSkip[(string)$strFilterWord]))
					{
						//把存在岐意的正常内容替换掉
						$strSkipContent = str_replace($arrFilterWordsSkip[(string)$strFilterWord],'',$strContent);
						//如果仍然检测出此关键词,则确认关键词无岐意
						$intResultCount = mb_substr_count($strSkipContent,$strFilterWord,'UTF-8');
	
						$blnConfrim = ($intResultCount > 0)?true:false;
					}
					else
					{
						$blnConfrim = true;
					}
		
					if($blnConfrim)
					{
						$arrFilterResult['index'][(string)$strFilterWord] = $intResultCount;
						$arrFilterResult['counter'][$strWordValue]['count'] ++;
						$arrFilterResult['counter'][$strWordValue]['times'] +=$intResultCount;
					}
				}
			}
		}
	
		return $arrFilterResult;
	}		
	
	
	//加载所有的关键词列表
	function loadFilterWordsAll()
	{
		global $DB,$CONFIG,$db_prefix;
	
		//检测缓存,*并且缓存有效期为24小时
		if(is_file($this->strCacheFile) && $this->isCacheEnable )
		{
			$strCacheData = file_get_contents($this->strCacheFile);
			
			$arrFilterWords = unserialize($strCacheData);
			
			if($arrFilterWords)
			{
				return $arrFilterWords;
			}
		}
	
		/*
		目前数据库中的记录有
		词类:A-政治类,B-违禁类,C-情色类
		词性:1-封杀词,2-敏感词
		*/
	
		$arrWordType = array(
		'政治类'=>'A',
		'违禁类'=>'B',
		'情色类'=>'C');
	
		$arrWordLevel = array(
		'封杀词'=>'1',
		'敏感词'=>'2');
	
		$sqlMain 	= "
				SELECT
					word,wordtype,wordlevel
				FROM
					".$db_prefix."filter_words
				WHERE
					isactive='1'
					";
	
		$queryMain = $DB->query($sqlMain);
	
		while($listItem = $DB->fetch_assoc($queryMain))
		{
			$strWord		= $listItem['word'];
			$strWordType	= $listItem['wordtype'];
			$strWordLevel	= $listItem['wordlevel'];
	
			if(mb_strlen($strWord)>1)
			{
				$arrWords[(string)$strWord] = $arrWordType[$strWordType].$arrWordLevel[$strWordLevel];
			}
		}
	
		$arrReturn['main'] 	= $this->loadWordsGroup($arrWords);
		$arrReturn['skip'] 	= $this->loadFilterWordsSkip();
	
		$strCacheData = serialize($arrReturn);
		file_put_contents($this->strCacheFile,$strCacheData);
	
		return $arrReturn;
	}
	
	function loadWordsGroup($arrWords)
	{
		global $DB,$CONFIG,$db_prefix;
	
		$sqlMain 	= "
				SELECT
					word,wordlen
				FROM
					".$db_prefix."filter_words_group
				WHERE
					frequency < '80'
					AND
					status = '0'
				ORDER BY
					wordlen DESC,frequency 
					";
	
		$queryMain = $DB->query($sqlMain);
	
		while($listItem = $DB->fetch_assoc($queryMain))
		{
			$strWord 		= $listItem['word'];
			$intWordLen 	= $listItem['wordlen'];
	
			if($intWordLen > 1)
			{
				$arrGroupSub[(string)$strWord] = array();
			}
			else
			{
				$arrGroupMain[(string)$strWord] = array();
			}
		}
	
		//多字词组先取词
		foreach ($arrGroupSub as $strGroupKey => $arrGroupValue)
		{
			//遍历数组
			foreach ($arrWords as $strWord => $strWordTypeLevel)
			{
				$intWordCount = mb_substr_count($strWord,$strGroupKey,'UTF-8');
	
				if($intWordCount > 0)
				{
					//showMessage($strGroupKey."\t".$strWord."\t".$intWordPos);
					$arrGroupSub[(string)$strGroupKey][(string)$strWord] = $strWordTypeLevel;
					unset($arrWords[(string)$strWord]);
				}
			}
		}
		
		//输出多字词组报告
		//showMessage("多字词有效的组数:".count($arrGroupSub));
		//showMessage("　未分组的项目数:".count($arrWords));
		//print_r($arrGroupSub);
		
		//修正多字词组，去除为空的组及内容少于三项的组
		foreach ($arrGroupSub as $strGroupSubKey => $arrTempGroupSub)
		{
			$intSubCount = count($arrTempGroupSub);
			
			if($intSubCount < 3)
			{
				
				if($intSubCount == 0)
				{
					//如果为空则直接去除组
					unset($arrGroupSub[(string)$strGroupSubKey]);
					$arrGroupA[] = "'".$strGroupSubKey."'";				
				}
				else 
				{
					//如果子项目少于3，则去除组，并还原组内的项目至$arrWord以供单字组配词
					foreach($arrTempGroupSub as $strTempKey => $strTempValue)
					{
						$arrWords[(string)$strTempKey] = $strTempValue;
						unset($arrGroupSub[(string)$strGroupSubKey]);
						$arrGroupB[] = "'".$strGroupSubKey."'";					
					}
				}
				
			}
		}
		
		//showMessage("修正后的数据");
		//showMessage("多字词有效的组数:".count($arrGroupSub));
		//showMessage("　未分组的项目数:".count($arrWords));
		//print_r($arrGroupSub);
	
		//单字词组后取词
		foreach ($arrGroupMain as $strGroupKey => $arrGroupValue)
		{
			//遍历数组
			foreach ($arrWords as $strWord => $strWordTypeLevel)
			{
				$intWordCount =  mb_substr_count($strWord,$strGroupKey,'UTF-8');
	
				if($intWordCount > 0)
				{
					//showMessage($strGroupKey."\t".$strWord."\t".$intWordPos);
					$arrGroupMain[(string)$strGroupKey][(string)$strWord] = $strWordTypeLevel;
					unset($arrWords[(string)$strWord]);
				}
			}
		}
	
		//多字词组归属单字词组
		foreach ($arrGroupMain as $strGroupMainKey => $arrTempGroupMain)
		{
			//遍历数组
			foreach ($arrGroupSub as $strGroupSubKey => $arrTempGroupSub)
			{
				$intWordPos = mb_strpos($strGroupSubKey,$strGroupMainKey,0,'UTF-8');
	
				if($intWordPos > 0 && count($arrTempGroupSub)>=2)
				{
					$arrGroupMain[(string)$strGroupMainKey][(string)$strGroupSubKey] = $arrTempGroupSub;
					unset($arrGroupSub[(string)$strGroupSubKey]);
				}
			}
		}
	
		//输出多字词组报告
		//showMessage("单字词有效的组数:".count($arrGroupMain));
		//showMessage("　未分组的项目数:".count($arrWords));
	
		
		//修正最终词组，去除为空的组及内容少于三项的组
		foreach ($arrGroupMain as $strGroupSubKey => $arrTempGroupSub)
		{
			$intSubCount = count($arrTempGroupSub);
			
			if($intSubCount < 3)
			{
				
				if($intSubCount == 0)
				{
					//如果为空则直接去除组
					unset($arrGroupMain[(string)$strGroupSubKey]);
					$arrGroupA[] = "'".$strGroupSubKey."'";	
				}
				else 
				{
					//如果子项目少于3，则去除组，并还原组内的项目至$arrWord以供单字组配词
					foreach($arrTempGroupSub as $strTempKey => $strTempValue)
					{
						$arrWords[(string)$strTempKey] = $strTempValue;
						unset($arrGroupMain[(string)$strGroupSubKey]);
						$arrGroupB[] = "'".$strGroupSubKey."'";					
					}
				}
				
			}
		}
	
		//修正后
		//showMessage("单字词有效的组数:".count($arrGroupMain));
		//showMessage("　未分组的项目数:".count($arrWords));	
		
		
		//showMessage("空组:".@implode(',',$arrGroupA));	
		//showMessage("次组:".@implode(',',$arrGroupB));	
		
		//遍历数组
		foreach ($arrWords as $strWord => $strWordTypeLevel)
		{
			$arrGroupMain[(string)$strWord] = $strWordTypeLevel;
		}
	
		//特别注意
		//如果$strWord为数字字符串时,用array_merge函数会将其识别为数字,而导致
		//array['234523'] 成为 array[0]
		//$arrReturn = array_merge($arrGroupMain,$arrWords);
	
		return $arrGroupMain;
	}
	
	//加载岐意关键词屏蔽数据
	function loadFilterWordsSkip()
	{
		global $CONFIG,$DB,$db_prefix;
	
		$sqlMain 	= "
				SELECT
					word,wordskip
				FROM
					".$db_prefix."filter_words_skip
					";
	
		$queryMain = $DB->query($sqlMain);
	
		while($listItem = $DB->fetch_assoc($queryMain))
		{
			$strWord 		= $listItem['word'];
			$strWordSkip 	= $listItem['wordskip'];
	
			$arrSkipKeywords[$strWord][] = $strWordSkip;
		}
	
		return $arrSkipKeywords;
	}
}
?>