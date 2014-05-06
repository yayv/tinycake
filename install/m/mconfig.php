<?php 

class mconfig extends model
{

	public function getAllConfigFiles()
	{
		$files = array(
			'domains'=>array(),
			'others'=>array()
		);
		$d = dir('./configs/');

		while (false !== ($entry = $d->read())) 
		{
			if(in_array($entry, array('.','..')))
				continue;

			if($ret = preg_match("/cfg\.(.*)\.php/",$entry,$matches))
				$files['domains'][$matches[1]] = $entry;
			else
				$files['others'][] = $entry;
		}
		$d->close();

		return $files;	
	}

	public function getConfigFileContent($filename)
	{

	}
}
