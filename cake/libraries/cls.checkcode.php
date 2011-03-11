<?php
/************************************************************************/
/* CEG SOFT                                                             */
/* ========                                                             */
/*                                                                      */
/* Copyright (c) 2005-2006 by the CEG SOFT developers                   */
/* For more information visit: http://www.cegsoft.com                   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; 								        */
/* 																        */
/* EMAIL: Thatday.box@gmail.com									        */
/************************************************************************/

class CEG_CheckCode
{
    var $_backgroundimage;
    var $_width;
    var $_height;

    /*
    函数：ceg_getRandColor
    用途：生成随机的颜色值
    参数：$img(图像资源),$min(最小的色彩值),$max(最大的色彩值)
    返回：图片色彩值
    */
    function ceg_getRandColor($img,$min=0,$max=255)
    {
	    $arrColor[1] = rand(0,80);
	    $arrColor[2] = rand(50,130);
	    $arrColor[3] = rand(0,30);
	
	    $color = imagecolorallocate($img,$arrColor[1],$arrColor[2],$arrColor[3]);
	
	    return $color;
    }
	
    /*
    函数：ceg_getRandColor
    用途：生成随机的颜色值
    参数：$img(图像资源),$min(最小的色彩值),$max(最大的色彩值)
    返回：图片色彩值
    */
    function ceg_getRandAngle()
    {
	    $arrAngle = range('-20','20');
	    shuffle($arrAngle);
	
	    return $arrAngle[0];
    }
		
    /*
    函数：ceg_setRandPoint
    用途：生成随机的杂点
    参数：$img(图像资源),$total(增加杂点的总量)
    返回：无
    */
    function ceg_setRandPoint($img,$total)
    {
	    $width  = imagesx($img);
	    $height = imagesy($img);
	
	    for($i=1;$i<=$total;$i++)
	    {
		    $x = rand(0,$width);
		    $y = rand(0,$height);
		    imagesetpixel($img,$x,$y,ceg_getRandColor($img,180,200));
	    }
    }
	
	
    /*
    函数：ceg_generateCheckCode

    用途：生成随机的验证码
    参数：$intLen(验证码的位数)
    返回：验证码
    */
    function ceg_generateCheckCode($intLen=4)
    {
	    //生成字符库数组
	    $arrCode = range(0,9);
	
	    shuffle($arrCode);
	
	    //随机取出其中5位数字
	    $arrRand = array_rand ($arrCode, $intLen);
	
	    for($i=0;$i<=$intLen;$i++)
	    {
		    $strCheckCode .=$arrCode[$arrRand[$i]];
	    }
	
	    return $strCheckCode;
	
    }
    
    function genCheckCodePic()
    {
	    //运行配置
       	$image_bg  = dirname(__FILE__)."/checkcode.jpg";	//背景图片名
	
	    //latha.ttf
	    //tahoma.ttf
	    //BEARPAW_.TTF
	    //BOWLOR__.TTF
	    $text_font = dirname(__FILE__)."/erasdemi.ttf";		//字体文件
	    $text_size = 15;					//文字大小
	    $checkcode_len  = 4;				//验证码的长度
	
        $img = @imagecreatefromjpeg($image_bg);

        // FIXME: 这里不能用 exit        
        if(!$img)
        {
        	exit();
        }
	
	    //验证码图片的大小由背景图片的大小决定
	    $img_width  = imagesx($img);
	    $img_height = imagesy($img);
	
	    //增加杂点
	    //ceg_setRandPoint($img,$img_width * $img_height * 0.1);

	    //生成验证码
	    $checkcode = ceg_generateCheckCode($checkcode_len);
		
	    //验证码输出的Y轴位置
        $start_y = 18;
	
	    for($i=0;$i<=$checkcode_len;$i++)
	    {
		    //验证码输出的Y轴位置
		    $start_x = $i * 18 + 10;
		    $char = substr($checkcode,$i,1);
		    $angle = rand(-20,20);
		    imagettftext($img, $text_size, $angle, $start_x+1,$start_y+2, imagecolorallocate($img,236,236,236), $text_font, $char);

		    imagettftext($img, $text_size, $angle, $start_x,$start_y, ceg_getRandColor($img,0,100), $text_font, $char);
	    }
	
	    //增加杂点
	    //ceg_setRandPoint($img,$img_width * $img_height * 0.1);
        
	    header("Content-Type: image/jpeg");
        imagepng($img);
    }

    function outputCheckCodePic()
    {
	    //存至SESSION中供其它页面验证
	    $_SESSION['checkcode'] = $checkcode;

    }
}

class CheckCode_withBG extends CEG_CheckCode
{
    function __construct($backgroundimage, $font)
    {
	    srand ((float)microtime()*1000000);
    }
    
}

class CheckCode extends CEG_CheckCode
{
    function __construct($width, $height, $font)
    {
	    srand ((float)microtime()*1000000);
    }

}


