<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 3d Library
 *
 * PHP versions 5
 *
 * LICENSE: 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Image
 * @package    Image_3D
 * @author     Kore Nordmann <3d@kore-nordmann.de>
 * @copyright  1997-2005 Kore Nordmann
 * @license    http://www.gnu.org/licenses/lgpl.txt lgpl 2.1
 * @version    CVS: $Id: 3ds.php,v 1.5 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object.php');
require_once('Image/3D/Paintable/Object/3dsChunks.php');

/**
 * Image_3D_Object_3ds
 *
 *
 *
 * @category   Image
 * @package    Image_3D
 * @author     Kore Nordmann <3d@kore-nordmann.de>
 * @copyright  1997-2005 Kore Nordmann
 * @license    http://www.gnu.org/licenses/lgpl.txt lgpl 2.1
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PackageName
 * @since      Class available since Release 0.1.0
 */
class Image_3D_Object_3ds extends Image_3D_Object {
	
	protected $_file;
	protected $_fileSize;
	protected $_objects;
	
	protected $_chunks;
	
	public function __construct($file) {
		parent::__construct();
		$this->_points = array();
		$this->_chunks = array();
		$this->_objects = array();

		if (!is_file($file) || !is_readable($file)) {
			throw new exception('3ds file could not be loaded.');
		}
		$this->_file = $file;
		
		$this->_readChunks();
	}
	
	protected function _readChunks() {
		$this->_chunks = new Image_3D_Chunk(Image_3D_Chunk::MAIN3DS, substr(file_get_contents($this->_file), 6));
		$this->_chunks->readChunks();
		
		$editor = $this->_chunks->getFirstChunkByType(Image_3D_Chunk::EDIT3DS);
		$editor->readChunks();
		
		$objects = $editor->getChunksByType(Image_3D_Chunk::EDIT_OBJECT);
		foreach ($objects as $object) {
			$object = new Image_3D_Chunk_Object($object->getType(), $object->getContent());
			$object->readChunks($this);
		}
	}
	
	public function addObject($id) {
		$id = (string) $id;
		$this->_objects[$id] = new Image_3D_Object_3ds_Object();
		return $this->_objects[$id];
	}
	
	public function getObjectIDs() {
		return array_keys($this->_objects);
	}
	
	public function getObject($id) {
		if (!@isset($this->_objects[$id])) return false;
		return $this->_objects[$id];
	}
	
	public function paint() {
		foreach ($this->_objects as $object) $object->paint();
	}
	
	public function getPolygonCount() {
		$count = 0;
		foreach ($this->_objects as $object) $count += $object->getPolygonCount();
		return $count;
	}
	
	public function setColor(Image_3D_Color $color) {
		foreach ($this->_objects as $object) $object->setColor($color);
	}
	
	public function setOption($option, $value) {
		foreach ($this->_objects as $object) $object->setOption($option, $value);
	}

	public function transform(Image_3D_Matrix $matrix, $id = null) {
		if ($id === null) $id = substr(md5(microtime()), 0, 8);
		foreach ($this->_objects as $object) $object->transform($matrix, $id);
	}
	
	public function getPolygones() {
		$polygones = array();
		foreach ($this->_objects as $object) $polygones = array_merge($polygones, $object->getPolygones());
		return $polygones;
	}
}

?>
