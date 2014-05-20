<?php


class FileUploadHandler
{

	public static function fromName($name)
	{
		//some basic error checking.
		if (!preg_match('/(gcode|stl|obj|amf|zip)$/i', $name))
			throw new Exception("Only .gcode, .stl, .obj, .amf, and .zip files are allowed at this time.");

		//make our file.
		$file = self::_createFile($name);

		//is it a zip file?  do some magic on it.
		if (preg_match("/\\.zip$/i", $name)) {
			$path = tempnam("/tmp", "BQ");
			$file->download($file->get('path'), $path);
			self::_handleZipFile($path, $file);
		}

		return $file;
	}

	private static function _createFile($path)
	{
		//format the name and stuff
		$filename = basename($path);
		$filename = str_replace(" ", "_", $filename);
		$filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
		$newPath = "assets/" . StorageInterface::getNiceDir($filename);

		//create new file and upload it
		$file = Storage::newFile();
		$file->set('path', $path);
		$file->moveTo($newPath);
		$file->getHash();
		$file->getSize();
		$file->getType();
		$file->set('user_id', User::$me->id);
		$file->set('add_date', date('Y-m-d H:i:s'));
		$file->save();

		return $file;
	}

	public static function _handleZipFile($zip_path, $zip_file)
	{
		$za = new ZipArchive();
		$za->open($zip_path);

		for ($i = 0; $i < $za->numFiles; $i++) {
			//look up file info.
			$filename = $za->getNameIndex($i);

			//okay, is it a supported file?
			if (preg_match('/(gcode|stl|obj|amf)$/i', $filename)) {
				$temp_file = tempnam("/tmp", "BQ");
				copy("zip://" . $zip_path . "#" . $filename, $temp_file);

				//format for upload
				$filename = str_replace(" ", "_", $filename);
				$filename = preg_replace("/[^-_.[0-9a-zA-Z]/", "", $filename);
				$path = "assets/" . StorageInterface::getNiceDir($filename);

				//create our file
				$file = Storage::newFile();
				$file->set('parent_id', $zip_file->id);
				$file->set('user_id', User::$me->id);
				$file->upload($temp_file, $path);
			}
		}

		//exit;
	}

} 