<?php


class LocalFile extends StorageInterface
{

	public function __construct($id = null)
	{
		parent::__construct($id, "s3_files");
	}

	public function exists()
	{
		if ($this->get('path'))
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
		$url .= SITE_HOSTNAME;
		$url .= "/local";
		return $url;
	}

	private function validPath($path)
	{
		return strpos($path, "/tmp") === 0 || strpos($path, STORAGE_PATH) === 0;
	}

	private function transfer($src, $dst, $deleteOriginal = true)
	{
		// Verify src and dst are either in /tmp or in the STORAGE_PATH
		if ($this->validPath($src) && $this->validPath($dst)) {
			// Delete the original file if it exists
			if (file_exists($dst)) {
				unlink($dst);
			}
			// Create the directory structure if it doesn't exist
			if (!is_dir(dirname($dst))) {
				mkdir(dirname($dst), 0777, true);
			}
			if ($deleteOriginal) {
				return rename($src, $dst);
			}
			else
			{
				return copy($src, $dst);
			}
		}
		return false;
	}

	public function upload($srcPath, $dstPath)
	{
		$this->set('path', $dstPath);
		$result = $this->transfer($srcPath, STORAGE_PATH . "/" . $dstPath, false);
		$this->set('add_date', date("Y-m-d H:i:s"));
		$this->getSize();
		$this->getHash();
		$this->getType();
		$this->save();
		return $result;
	}

	public function download($srcPath, $dstPath)
	{
		return $this->transfer(STORAGE_PATH . "/" . $srcPath, $dstPath, false);
	}

	public function moveTo($dstPath)
	{
		$result = $this->transfer(STORAGE_PATH . "/" . $this->get('path'), STORAGE_PATH . "/" . $dstPath, true);
		$this->set('path', $dstPath);
		$this->save();

		return $result;
	}

	public function getSize()
	{
		if ($this->get('path') && file_exists(STORAGE_PATH . "/" . $this->get('path'))) {
			$this->set('size', filesize(STORAGE_PATH . "/" . $this->get('path')));
		}
		return $this->get('size');
	}

	public function getHash()
	{
		if ($this->get('path') && file_exists(STORAGE_PATH . "/" . $this->get('path')))
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
		$url .= SITE_HOSTNAME;
		$url .= "/local/";
		$url .= $this->get('path');
		return $url;
	}
}