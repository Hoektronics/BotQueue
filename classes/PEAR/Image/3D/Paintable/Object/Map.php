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
 * @version    CVS: $Id: Map.php,v 1.7 2006/03/11 17:14:11 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object.php');

/**
 * Image_3D_Object_Map
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
class Image_3D_Object_Map extends Image_3D_Object {
	
	protected $_points;
	
	public function __construct($points = array()) {
		parent::__construct();
		$this->_points = array();
		
		foreach ($points as $row) {
			if (is_array($row)) {
				$this->addRow($row);
			} else {
				$this->addRow(array($row));
			}
		}
	}
	
	public function addRow($row) {
		$rowNbr = array_push($this->_points, array()) - 1;
		foreach ($row as $point) {
			if (is_object($point) && ($point instanceof Image_3D_Point)) $this->_points[$rowNbr][] = $point;
		}
		
		if (!count($this->_points[$rowNbr])) {
			unset($this->_points[$rowNbr]);
			return false;
		}
		
		if (count($this->_points) > 1) {
			$newRow = count($this->_points) - 1;
			$lastRow = $newRow - 1;
			
			$newCount = count($this->_points[$newRow]);
			$lastCount = count($this->_points[$lastRow]);
			
			if ($newCount < $lastCount) {
				$tmp = $newRow;
				$newRow = $lastRow;
				$lastRow = $tmp;
				
				$tmp = $newCount;
				$newCount = $lastCount;
				$lastCount = $tmp;
			}
			
			$top = (($newCount == 1) ? 1 : 1 / ($newCount - 1));
			$bottom = (($lastCount == 1) ? 32768 : 1 / ($lastCount - 1));
			
			$k = 0;
			for ($i = 1; $i < $newCount; $i++) {
				if (($i * $top) > ($k * $bottom + $bottom / 2)) {
					// Nach unten geoeffnetes Polygon einfuegen /\
					$this->_addPolygon(new Image_3D_Polygon($this->_points[$newRow][$i - 1], $this->_points[$lastRow][$k + 1], $this->_points[$lastRow][$k]));
					$k++;
				}
				// Nach oben geoeffnetes Polygon einfuegen \/
				$this->_addPolygon(new Image_3D_Polygon($this->_points[$newRow][$i - 1], $this->_points[$newRow][$i], $this->_points[$lastRow][$k]));
			}
		}
		return true;
	}
	
	public function getRow($int) {
		if (!isset($this->_points[$int])) return false;
		return $this->_points[$int];
	}
	
	public function getPoint($x, $y) {
		if (!isset($this->_points[$x][$y])) return false;
		return $this->_points[$x][$y];
	}
	
	public function setOption($option, $value) {
//		if ($option === Image_3D::IMAGE_3D_OPTION_BF_CULLING) $value = false;
		parent::setOption($option, $value);
	}
}
