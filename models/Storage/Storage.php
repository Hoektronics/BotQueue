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
		$class = STORAGE_METHOD;
		return new $class($id);
	}

} 