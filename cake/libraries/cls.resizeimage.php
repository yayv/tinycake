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
		$this->__dest = imagecreatetruecolor($this->__width, $this->__height);
		imagecopyresampled($this->__dest,$this->__srcimage,
							0,0,$this->__fromx,$this->__fromy,
							$this->__width,$this->__height,
							$this->__fromw,$this->__fromh);
	}
		
	public function saveImage($type, $dest_filepath=false)
	{
		$this->doScale();
		
		if(!$dest_filepath) 
			$dest_filepath = $this->__dest;
			
		imageJpeg($this->__srcimage, $dest_filepath);
	}
	
	public function showImage()
	{
		$this->doScale();
		
		header('Content-Type:image/jpeg');
		if($this->__dest)
			imageJpeg($this->__dest);
		else
			imageJpeg($this->__srcimage);
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
	public function cutImageHeight($y1, $height)
	{
		$this->__fromh	= $this->__fromw * $height / $this->__width ;
		$this->__height = $height;
	}
	
	// 纵向裁剪图片 取x1到x2之间部分，高度不变
	public function cutImageWidth($x1, $width)
	{
		$this->__fromw = $this->__fromh * $width / $this->__height;
		$this->__width = $width;
	}
}


