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
	}

	function __destruct()
	{
		$this->clean();
	}

	public function clean()
	{
		if($this->__srcimage)
			imagedestroy($this->__srcimage);
	}

	public function loadImage($src)
	{
		$this->__srcimage = imagecreatefromjpeg($src);
		
		$this->__srcwidth  = ImageSX($this->__image);
		$this->__srcheight = ImageSY($this->__image); 
	}
	
	public function getImageInfo()
	{
		// width height mime-type
		return array($this->__srcwidth, $this->__srcheight);
	}
	
	public function resetInfo()
	{
		$this->__fromx = 0;
		$this->__fromy = 0;
		$this->__fromw = $this->__srcwidth;
		$this->__fromh = $this->__srcheight;
	}
	
	public function doScale()
	{
		imagecopyresampled($tmp,$this->__srcimage,
							$this->__fromx,$this->__fromy,0,0,
							$this->__fromw,$this->__fromh,
							$this->__width,$this->__height);
	}
		
	public function saveImage($type, $dest_filepath=false)
	{
		$this->doScale();
		
		if(!$dest_filepath) 
			$dest_filepath = $this->__dest;
			
		imageJpeg($this->__image, $dest_filepath);
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
		$newheight = $this->__srcheight * $width/$this->__srcwidth;

		$tmp = imagecreatetruecolor($width, $newheight);
		
		imagecopyresized($tmp,$this->__image,
							0,0,0,0,
							$width,$newheight,
							$this->__srcwidth,$this->__srcheight);
		
		$this->__dest = $tmp;
	}
	
	// 等比缩放图片到指定高度
	public function scaleToHeight($height)
	{
		$newwidth = $this->__srcwidth * $height / $this->__srcheight ;

		$tmp = imagecreatetruecolor($newwidth, $height);
		
		imagecopyresized($tmp,$this->__image,
							0,0,0,0,
							$newwidth,$height,
							$this->__srcwidth,$this->__srcheight);
		
		$this->__dest = $tmp;
	}
	
	// 横向裁剪图片 取y1到y2之间部分, 宽度不变
	public function cutImageHeight($y1, $y2)
	{
		$width = $this->__srcwidth;
		
		$tmp = imagecreatetruecolor($width, $y2-$y1);
		
		imagecopyresized($tmp, $this->__image,
						0,0,0,$y1,
						$this->__srcwidth, $y2-$y1,
						$this->__srcwidth, $y2-$y1);
						
		$this->__dest = $tmp;
	}
	
	// 纵向裁剪图片 取x1到x2之间部分，高度不变
	public function cutImageWidth($x1, $x2)
	{
		$height = $this->__srcheight;
		
		$tmp = imagecreatetruecolor($x2-$x1, $height);
		
		imagecopyresized($tmp, $this->__image,
						0,0,$x1,0,
						$this->__srcwidth, $x2-$x1,
						$this->__srcwidth, $x2-$x1);
						
		$this->__dest = $tmp;
	}
		
	public function tem()
	{
		$this->__image = $this->__dest;
		
		$this->__srcwidth  = imageSX($this->__image);
		$this->__srcheight = imageSY($this->__image);
	}		
}


	function oldfun()
	{
	
	$link = mysql_connect('192.168.0.225', 'root','');
			mysql_select_db('world', $link);
	
	$ids = unserialize(file_get_contents('dumpids.txt'));
	$count = 0;
	#if($ret)
	{
	  #while($row = mysql_fetch_assoc($ret)) 
	  foreach($ids as $id)
	  {
		  $sql = "select * from image where id=$id";
		  $ret = mysql_query($sql);
		  $row = mysql_fetch_object($ret);
			
		  $src = '/app/img/html/sns_photo_move/'.$row->new_filename;
		  $date = explode('/', $row->new_filename);
		   
		  // .........
		  if(is_file($src))
		  {
			  // ..............
			  $image = @imagecreatefromjpeg($src);
				if(!$image){
				   echo 'bad:',$row->id,"\n";
					 continue;
				}
				$image_width = ImageSX($image);
				$image_height = ImageSY($image);          
			  
			  // ..
			  // ..120x90
			  $dst = '/app/img/html/s/'.$row->new_filename;          
			  @mkdir('/app/img/html/s/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 120, 90);
			  resize_another($dst, $image, $image_width, $image_height, 
							120, 90);
			  
			  // ..640
			  $dst = '/app/img/html/m/'.$row->new_filename;          
			  @mkdir('/app/img/html/m/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 640);
			  resize_another($dst, $image, $image_width, $image_height, 
				  640);
	
			  // ..240
			  $dst = '/app/img/html/c/240/'.$row->new_filename;          
			  @mkdir('/app/img/html/c/240/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 240);
			  resize_another($dst, $image, $image_width, $image_height, 
				  240);
	
			  // ..60x60
			  $dst = '/app/img/html/c/60x60/'.$row->new_filename;          
			  @mkdir('/app/img/html/c/60x60/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 60, 60);
			  resize_another($dst, $image, $image_width, $image_height, 
							60, 60);
			  
			  // ..90x90
			  $dst = '/app/img/html/c/90x90/'.$row->new_filename;          
			  @mkdir('/app/img/html/c/90x90/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 90, 90);
			  resize_another($dst, $image, $image_width, $image_height, 
				  90, 90);
	
			  
			  // ..220x220
			  $dst = '/app/img/html/c/220x220/'.$row->new_filename;          
			  @mkdir('/app/img/html/c/220x220/'.$date[0], 0777, true);
			  #resize_up_myhands($src, $dst, 220, 220);
			  resize_another($dst, $image, $image_width, $image_height, 
				  220, 220);
	
			  // release memory
			  imagedestroy($image);
		  }
		  
		  
		  $count++;
		  if($count%1000==0)
			  echo "\n",date('m-d H:i:s'),"\n",$row->id,"\n\n";
				
	  }
	}
	}
