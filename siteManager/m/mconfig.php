<?php 

class mconfig extends model
{

	public function getAllConfigFiles($projectroot)
	{
		$files = array(
			'domains'=>array(),
			'others'=>array()
		);

		$d = dir("$projectroot/configs/");

		while (false !== ($entry = $d->read())) 
		{
			if(in_array($entry, array('.','..')))
				continue;

			if($ret = preg_match("/cfg\.(.*)\.php/",$entry,$matches))
				$files['domains'][$matches[1]] = $entry;
			else
				$files['others'][] = substr($entry,0,-4);
		}
		$d->close();

		return $files;	
	}

	public function getConfigFileContent($filename)
	{

	}
}
