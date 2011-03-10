<?php
/**
 * @Descrition: 从模板中寻找段引用变量名，和段外变量名
 * @return : 
 */ 
$times = 0;
function parse_template($template, $regular)
{
    $names = array();    
    $pos   = array();
    
    // 匹配段名
    $i = 0;
    foreach($regular['inloop_start'] as $k=>$v)
    {
        do
        {
            $namearray 
                = parse_piece(
                          $template, 
                          $v, 
                          $regular['inloop_end'][$k],
                          $regular['inloop_var'][$k],
                          $regular['attr']['showname'],
                          $regular['attr']['alt']
                  );
            
            // push name for section
            if($namearray)
                $names = array_merge($names, $namearray);

        }while($namearray!==false);
        
    }

    
    // 匹配段外内容
    $singles = parse_varname($template, $regular['variables'], $regular['attr']['showname'], $regular['attr']['alt']);

    $names = array_merge(
                  $names, 
                  $singles
                  );

    return $names;
}

/**
 * @Description: 根据指定开始规则和结束规则
 * @Return: false for error 
 */ 
function parse_piece(&$template, $start, $end, $var, $showname, $alt)
{
    global $times;
    
    $times ++;
    $varname = false;
    $pos = array();
    
    $ret = preg_match($start, $template, $matches, PREG_OFFSET_CAPTURE);

    if($ret)
    {
        $pos['start'] = $matches[0][1];
        
        $ret = preg_match($end, 
                          $template, 
                          $matches, 
                          PREG_OFFSET_CAPTURE);
        
        if($ret)
        {
            $pos['end'] = $matches[0][1];
            $pos['len'] = strlen($matches[0][0]);
        }
        else
            return false;
            
        $piece    = substr(
                  $template, 
                  $pos['start'], 
                  $pos['end']-$pos['start']+$pos['len']);

        $template = substr_replace(
                  $template, 
                  '', 
                  $pos['start'], 
                  $pos['end']-$pos['start']+$pos['len']
                  );
                  
        $ret     = preg_match($var, $piece, $matches);
        
        // 找到{foreach或者{section
        $names        = array();
        $tagname      = $matches[1]; 
        $names[$tagname] = array();
        $names[$tagname]['type'] = 'list';

        // 获取相关的 showname 和 alt
        $pos['tagend'] = strpos($piece, '}');
        $wholetag=substr($piece, 0,$pos['tagend']);

        $ret = preg_match($showname, $wholetag, $matches);
        if($ret)
            $names[$tagname]['showname'] = $matches[1];
        else
		{
			if(!isset($names[$tagname]['showname']))
				$names[$tagname]['showname'] = $tagname;
		}

        $ret = preg_match($alt, $wholetag, $matches);
        if($ret)
            $names[$tagname]['alt'] = $matches[1];
        else
		{
			if(!isset($names[$tagname]['alt']))
				$names[$tagname]['alt'] = $tagname;
        }    
    }
    else
        return false;
    
    return $names;
}

/**
 * @Description: 寻找段内的变量名
 */ 
function parse_varname($template, $var, $showname, $alt)
{
    $names = array();
    $pos = 0;

    
    $pos = 181;
    do
    {
        //print_r(strpos($template, '{$keyword', $pos));
        $retag= preg_match($var, $template, $matches, PREG_OFFSET_CAPTURE,$pos);

        if($retag)
        {
            $pos      = $matches[1][1];
            $pos2     = strpos($template, '}', $pos);
            $wholetag = substr($template, $pos, $pos2-$pos);
            $tagname  = $matches[1][0];
            
            $names[$matches[1][0]]['type'] = 'single';

            $ret = preg_match($showname, $wholetag, $matches);
            if($ret)
                $names[$tagname]['showname'] = $matches[1];
            else
			{
				if(!isset($names[$tagname]['showname']))
					$names[$tagname]['showname'] = $tagname;
            }

            $ret = preg_match($alt, $wholetag, $matches);
            if($ret)
                $names[$tagname]['alt'] = $matches[1];
            else
			{
				if(!isset($names[$tagname]['alt']))
					$names[$tagname]['alt'] = $tagname;
			}
        }

    }while($retag);

    return $names;
}
