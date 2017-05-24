<?php


abstract class StorageInterface extends Model
{
	public abstract function exists();

	public abstract function getUploadFields();

	public abstract function getUploadURL();

	public abstract function upload($srcPath, $dstPath);

	public abstract function download($srcPath, $dstPath);

	public abstract function moveTo($dstPath);

	public abstract function getSize();

	public abstract function getHash();

	public abstract function getType();

	public abstract function getDownloadURL();

	public function uploadNice($srcPath, $name, $prefix="") {
		$this->upload($srcPath, $prefix.StorageInterface::getNiceDir($name));
	}

	public function getBasename()
	{
		return basename($this->get('path'));
	}

	public function getExtension()
	{
		$data = pathinfo($this->get('path'));

		return $data['extension'];
	}

	public function getName()
	{
		return $this->getBasename();
	}

	public function getUrl()
	{
		return "/file:{$this->id}";
	}

	public function getUser()
	{
		return new User($this->get('user_id'));
	}

	public function getAPIData()
	{
		$d = array();
		$d['id'] = $this->id;
		$d['name'] = $this->getName();
		$d['url'] = $this->getDownloadURL();
		$d['type'] = $this->get('type');
		$d['md5'] = $this->get('hash');
		$d['size'] = $this->get('size');

		return $d;
	}

	public function isKnownType()
	{
		return $this->isGCode() || $this->is3DModel() || $this->isMakerbot();
	}

	public function isGCode()
	{
		return preg_match("/(g|gcode)$/i", $this->get('path'));
	}

	public function isMakerbot()
	{
		return preg_match("/(s3g|x3g|makerbot)$/i", $this->get('path'));
	}

	public function is3DModel()
	{
		return preg_match("/(stl|obj|amf)$/i", $this->get('path'));
	}

	public function getJobs()
	{
		$sql = "SELECT id
		    	FROM jobs
		    	WHERE source_file_id = ?
		      	OR file_id = ?
		    	ORDER BY id DESC";

		$jobs = new Collection($sql, array($this->id, $this->id));
		$jobs->bindType('id', 'Job');

		return $jobs;
	}

	public function getParent()
	{
		return Storage::get($this->get('parent_id'));
	}

	public function getChildren()
	{
		$sql = "SELECT id
		    	FROM s3_files
		    	WHERE parent_id = ?
		    	ORDER BY id DESC";

		$children = new Collection($sql, array($this->id));
		$children->bindType('id', get_class($this));

		return $children;
	}

	public static function createHashDirectory()
	{
		$hash = sha1(mt_rand() . mt_rand() . mt_rand() . mt_rand());

		$directory = substr($hash, 0, 2);
		$directory .= "/";
		$directory .= substr($hash, 2, 2);
		$directory .= "/";
		$directory .= substr($hash, 4, 2);
		$directory .= "/";
		$directory .= substr($hash, 6, 2);
		$directory .= "/";
		$directory .= substr($hash, 8, 2);

		return $directory;
	}

	public static function getNiceDir($path)
	{
		$dir = self::createHashDirectory();
		$file = self::removeHash($path);

		return "$dir/$file";
	}

	public static function removeHash($file)
	{
		$file = basename($file);
		$file = preg_replace("/[0-9a-f]{32}-/i", "", $file);

		return $file;
	}
} 