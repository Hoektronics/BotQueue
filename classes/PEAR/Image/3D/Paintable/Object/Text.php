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
 * @version    CVS: $Id: Text.php,v 1.5 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Matrix/Move.php');
require_once('Image/3D/Paintable/Object.php');
require_once('Image/3D/Paintable/Object/Cube.php');

/**
 * Image_3D_Object_Text
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
class Image_3D_Object_Text extends Image_3D_Object {
	
	protected $_text;
	protected $_characterSpacing;
	
	protected $_chars;
	
	public function __construct($string) {
		parent::__construct();
		
		$this->_text = (string) $string;
		$this->_points = array();
		$this->_characterSpacing = 5.5;
		
		$textdata = '@data_dir@/Image_3D/data/TextData.dat';
		if (is_readable($textdata)) {
			$this->_chars = unserialize(file_get_contents($textdata));
		} elseif (is_readable('data/TextData.dat')) {
			$this->_chars = unserialize(file_get_contents('data/TextData.dat'));
		} else {
			throw new Exception('File for textdata not found.');
		}
		
		$this->_generateCubes();
	}
	
	public function setCharSpacing($charSpacing) {
		$this->_characterSpacing = 5 + (float) $charSpacing;
	}
	
	protected function _generateCubes() {
		$length = strlen($this->_text);
		
		for ($i = 0; $i < $length; $i++) {
			$char = $this->_chars[ord($this->_text{$i})];
			foreach ($char as $x => $row) {
				foreach ($row as $y => $pixel) {
//printf("Dot %d %.1f %.1f\n", $pixel, $x + $i * $this->_characterSpacing, $y);
					if ($pixel) {
						$tmp = new Image_3D_Object_Cube(array(1, 1, 1));
						$tmp->transform(new Image_3D_Matrix_Move(array($x + $i * $this->_characterSpacing, $y, 0)));
						$polygones = $tmp->getPolygones();
						foreach ($polygones as $polygon) $this->_addPolygon($polygon);
						unset($tmp);
					}
				}
			}
		}
	}
}
