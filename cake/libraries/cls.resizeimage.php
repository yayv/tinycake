<?php

class ImageResizer
{
	public $__srcfile;
	public $__srcimage;
	public $__srcwidth;
	public $__srcheight;

	public $__fromx;
	public $__fromy;
	public $__fromw;
	public $__fromh;

	public $__dest;
	public $__width;
	public $__height;
	
	function __construct($srcpath=false)
	{
		if($srcpath)
		{
			$this->loadImage($srcpath);
		}
		else
		{
			$this->__image = false;
		}
		
		$this->__width = $this->__height = false;
	}

	function __destruct()
	{
		if($this->__srcimage)
			imagedestroy($this->__srcimage);

		$this->clean();
	}

	public function clean()
	{
		if($this->__dest)
			imagedestroy($this->__dest);
		
		$this->__dest = false;
		$this->__width = 0;
		$this->__height = 0;
	}

	public function loadImage($src)
	{
		if($this->__srcimage)
			imagedestroy($this->__srcimage);
			
		$this->__srcimage = imagecreatefromjpeg($src);
		
		$this->__srcwidth  = ImageSX($this->__srcimage);
		$this->__srcheight = ImageSY($this->__srcimage); 
	}
		
	public function resetRect()
	{
		$this->__fromx = 0;
		$this->__fromy = 0;
		$this->__fromw = $this->__srcwidth;
		$this->__fromh = $this->__srcheight;
		
		if($this->__dest) imagedestroy($this->__dest);
		
		$this->__width = false;
		$this->__height= false;
	}
	
	public function doScale()
	{
		if($this->__dest)
			imagedestroy($this->__dest);
			
		$this->__dest = imagecreatetruecolor($this->__width, $this->__height);
		imagecopyresampled($this->__dest,$this->__srcimage,
							0,0,$this->__fromx,$this->__fromy,
							$this->__width,$this->__height,
							$this->__fromw,$this->__fromh);
	}
		
	public function saveImage($type, $dest_filepath=false)
	{
		if(!$dest_filepath) 
			$dest_filepath = $this->__dest;

		$this->doScale();
		imageJpeg($this->__dest, $dest_filepath);
	}
	
	public function showImage()
	{		
		header('Content-Type:image/jpeg');
		$this->doScale();
		imageJpeg($this->__dest);
	}
		
	// 等比缩放图片到指定宽度, 
	public function scaleToWidth($width)
	{
		$height = $this->__srcheight * $width/$this->__srcwidth;

		$this->__fromx = 0;
		$this->__fromy = 0;
		$this->__fromw = $this->__srcwidth;
		$this->__fromh = $this->__srcheight;
		
		$this->__width = $width;
		$this->__height= $height;
	}
	
	// 等比缩放图片到指定高度
	public function scaleToHeight($height)
	{
		$width = $this->__srcwidth * $height / $this->__srcheight ;

		$this->__fromx = 0;
		$this->__fromy = 0;
		$this->__fromw = $this->__srcwidth;
		$this->__fromh = $this->__srcheight;
		
		$this->__width = $width;
		$this->__height= $height;
	}
	
	// 横向裁剪图片 取y1到y2之间部分, 宽度不变
	public function cutImageHeight($height, $y1)
	{
		if($y1===false || $y1<0)
			$y1 = 0 ;
		else if(is_int($y1) && $y1>=0 && $y1<$this->__height){
			/* do nothing  */ }
		else if($y1=='middle')
			$y1  = ($this->__height - $height ) * $this->__fromh / ( $this->__height * 2 );
		else if($y1=='bottom')
			$y1 = $this->__fromh - $this->__fromh * $height / $this->__height; 
		else 
			$y1 = 0;
			
		$this->__fromy = $y1;
		$this->__fromh = $this->__fromw * $height / $this->__width;
		$this->__height = $height;
	}
	
	// 纵向裁剪图片 取x1到x2之间部分，高度不变
	// Param x1 can be a string in ('left', 'middle', 'right') or any int number
	public function cutImageWidth($width, $x1=false)
	{
		if($x1===false || $x1<0)
			$x1 = 0 ;
		else if(is_int($x1) && $x1>=0 && $x1<$this->__width){
			/* do nothing  */ }
		else if($x1=='middle'){
			$x1  = ($this->__width - $width ) * $this->__fromw / ( $this->__width * 2 );}
		else if($x1=='right')
			$x1 = $this->__fromw - $this->__fromw * $width / $this->__width; 
		else 
			$x1 = 0;
			
		$this->__fromx = $x1;
		$this->__fromw = $this->__fromh * $width / $this->__height;
		$this->__width = $width;
	}
}


