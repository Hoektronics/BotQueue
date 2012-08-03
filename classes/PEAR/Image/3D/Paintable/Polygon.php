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
 * @version    CVS: $Id: Polygon.php,v 1.6 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */


/**
 * Image_3D_Polygon
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
class Image_3D_Polygon implements Image_3D_Interface_Paintable, Image_3D_Interface_Enlightenable {
	
	protected $_color;
	protected $_colorCalculated;
	
	protected $_option;
	
	protected $_points;
	
	protected $_visible;
	protected $_normale;
	protected $_position;
	
	public function __construct() {
		$this->_points = array();
		$this->_option = array();
		$this->_color = null;
		$this->_colorCalculated = false;
		$this->_visible = true;
		$this->_normale = null;
		$this->_position = null;
		
		if (func_num_args()) {
			$args = func_get_args();
			for ($i = 0; $i < func_num_args(); $i++) {
				if (is_object($args[$i]) && ($args[$i] instanceof Image_3D_Point)) {
					$this->addPoint($args[$i]);
				}
			}
		}
	}

	public function calculateColor($lights) {
		foreach ($lights as $light) $this->_color = $light->getColor($this);
		$this->_color->calculateColor();
	}
	
	public function getColor() {
		return $this->_color;
	}
	
	protected function _calcNormale() {
		if (count($this->_points) < 3) {
			$this->_normale = new Image_3D_Vector(0, 0, 0);
			return false;
		}
		
		$a1 = $this->_points[0]->getX() - $this->_points[1]->getX();
		$a2 = $this->_points[0]->getY() - $this->_points[1]->getY();
		$a3 = $this->_points[0]->getZ() - $this->_points[1]->getZ();
		$b1 = $this->_points[2]->getX() - $this->_points[1]->getX();
		$b2 = $this->_points[2]->getY() - $this->_points[1]->getY();
		$b3 = $this->_points[2]->getZ() - $this->_points[1]->getZ();
		$this->_normale = new Image_3D_Vector($a2 * $b3 - $a3 * $b2, $a3 * $b1 - $a1 * $b3, $a1 * $b2 - $a2 * $b1);
		
		// Backface Culling
		if (($this->_normale->getZ() <= 0) && (@$this->_option[Image_3D::IMAGE_3D_OPTION_BF_CULLING])) $this->setInvisible();
	}
	
	public function getNormale() {
		if (!($this->_normale instanceof Image_3D_Vector)) $this->_calcNormale();
		return $this->_normale;	
	}
	
	protected function _calcPosition() {
		$position = array(0, 0, 0);
		foreach ($this->_points as $point) {
			$position[0] += $point->getX();
			$position[1] += $point->getY();
			$position[2] += $point->getZ();
		}
		$count = count($this->_points);
		$this->_position = new Image_3D_Vector($position[0] / $count, $position[1] / $count, $position[2] / $count);
	}
	
	public function getPosition() {
		if (!($this->_position instanceof Image_3D_Vector)) $this->_calcPosition();
		return $this->_position;	
	}
	
	public function getPolygonCount() {
		return 1;
	}
	
	public function setColor(Image_3D_Color $color) {
		$this->_color = $color;
	}
	
	public function isVisible() {
		return $this->isVisible();
	}
	
	public function setInvisible() {
		$this->_visible = false;
	}
	
	public function setOption($option, $value) {
		$this->_option[$option] = $value;
		foreach ($this->_points as $point) $point->setOption($option, $value);
	}
	
	public function addPoint(Image_3D_Point $point) {
		$this->_points[] = $point;
	}
	
	public function getPoints() {
		return $this->_points;
	}

	public function transform(Image_3D_Matrix $matrix, $id = null) {
		
		if ($id === null) $id = substr(md5(microtime()), 0, 8);
		foreach ($this->_points as $point) $point->transform($matrix, $id);
	}
	
	public function getMidZ() {
		$z = 0;
		foreach ($this->_points as $point) $z += $point->getZ();
		return $z / count($this->_points);
	}
	
	public function getMaxZ() {
		$z = -500;
		foreach ($this->_points as $point) $z = max($point->getZ(), $z);
		return $z;
	}
}

?>
