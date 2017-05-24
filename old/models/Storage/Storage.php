<?php


class Storage {

	/**
	 * @return StorageInterface
	 */
	public static function newFile() {
		return self::get(null);
	}

	/**
	 * @param $id
	 * @return StorageInterface
	 */
	public static function get($id) {
		if(!defined("STORAGE_METHOD"))
			define("STORAGE_METHOD", "S3File");
		$class = STORAGE_METHOD;
		return new $class($id);
	}

} 