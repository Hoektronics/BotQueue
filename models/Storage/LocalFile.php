<?php


class LocalFile extends StorageInterface {

	public function __construct($id = null)
	{
		parent::__construct($id, "s3_files");
	}

	public function exists()
	{
		if($this->get('path'))
			return file_exists(STORAGE_PATH . " / " . $this->get('path'));
		return false;
	}

	public function getUploadFields()
	{
		return array();
	}

	public function getUploadURL()
	{
		$url = "http" . (FORCE_SSL ? "s" : "") . "://";
		$url.= SITE_HOSTNAME;
		$url.= "/local";
		return $url;
	}

	private function move($src, $dst) {
		error_log("Src: ".$src);
		error_log("Dst: ".$dst);
		// Verify src and dst are either in /tmp or in the STORAGE_PATH
		if(strpos($src, "/tmp") === 0 || strpos($src, STORAGE_PATH) === 0) {
			if(strpos($dst, "/tmp") === 0 || strpos($dst, STORAGE_PATH) === 0) {
				// Delete the original file if it exists
				if(file_exists($dst)) {
					unlink($dst);
				}
				// Create the directory structure if it doesn't exist
				if(!is_dir(dirname($dst))) {
					mkdir(dirname($dst), 0777, true);
				}
				return rename($src, $dst);
			}
		}
		return false;
	}

	public function upload($srcPath, $dstPath)
	{
		$this->set('path', $dstPath);
		$result = $this->move($srcPath, STORAGE_PATH . "/" . $dstPath);
		$this->getSize();
		$this->getHash();
		$this->getType();
		return $result;
	}

	public function download($srcPath, $dstPath)
	{
		return $this->move(STORAGE_PATH . "/" . $srcPath, $dstPath);
	}

	public function moveTo($dstPath)
	{
		$result =  $this->move(STORAGE_PATH . "/" . $this->get('path'), STORAGE_PATH . "/" . $dstPath);
		$this->set('path', $dstPath);

		return $result;
	}

	public function getSize()
	{
		if($this->get('path') && file_exists(STORAGE_PATH . "/" . $this->get('path'))) {
			$this->set('size', filesize(STORAGE_PATH . "/" . $this->get('path')));
		}
		return $this->get('size');
	}

	public function getHash()
	{
		if($this->get('path') && file_exists(STORAGE_PATH . "/" . $this->get('path')))
			$this->set('hash', md5_file(STORAGE_PATH . "/" . $this->get('path')));
		return $this->get('hash');
	}

	public function getType()
	{
		// TODO: Implement getType() method.
		$this->set('type', "application/octet-stream");
		return $this->get('type');
	}

	public function getDownloadURL()
	{
		$url = "http" . (FORCE_SSL ? "s" : "") . "://";
		$url.= SITE_HOSTNAME;
		$url.= "/local:";
		$url.= $this->id;
		return $url;
	}
}