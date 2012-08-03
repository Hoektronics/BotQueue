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
 * @version    CVS: $Id: Point.php,v 1.6 2005/12/02 16:01:29 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */


/**
 * Image_3D_Point
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
class Image_3D_Point extends Image_3D_Coordinate implements Image_3D_Interface_Enlightenable {
	
	protected $_option;
	
	protected $_lastTransformation;
	protected $_screenCoordinates;
	
	protected $_processed;
	
	protected $_normale;
	protected $_vectors;
	
	protected $_colors;
	protected $_color;
	
	public function __construct($x, $y, $z) {
		parent::__construct($x, $y, $z);

		$this->_option = array();
		
		$this->_lastTransformation = null;
		$this->_screenCoordinates = null;
		
		$this->_processed = false;
		
		$this->_colors = array();
		$this->_color = null;
	}
	
	public function setOption($option, $value) {
		$this->_option[$option] = $value;
	}

	public function calculateColor($lights) {
		foreach ($lights as $light) $this->_color = $light->getColor($this);
		$this->_color->calculateColor();
	}

	public function addVector(Image_3D_Vector $vector) {
		$this->_vectors[] = $vector;
	}
	
	protected function _calcNormale() {
		$this->_normale = new Image_3D_Vector(0, 0, 0);
		foreach ($this->_vectors as $vector) $this->_normale->add($vector);
		$this->_normale->unify();
	}

	public function getNormale() {
		if (!($this->_normale instanceof Image_3D_Vector)) $this->_calcNormale();
		return $this->_normale;	
	}
	
	public function getPosition() {
		return new Image_3D_Vector($this->_x, $this->_y, $this->_z);	
	}
	
	public function addColor(Image_3D_Color $color) {
		$this->_colors[] = $color;
	}
	
	protected function _mixColors() {
		$i = 0;
		$color = array(0, 0, 0, 0);
		foreach ($this->_colors as $c) {
			$values = $c->getValues();
			$color[0] += $values[0];
			$color[1] += $values[1];
			$color[2] += $values[2];
			$color[3] += $values[3];
			$i++;
		}
		$this->_color = new Image_3D_Color($color[0] / $i, $color[1] / $i, $color[2] / $i, $color[3] / $i);
	}
	
	public function getColor() {
		if ($this->_color === null) $this->_mixColors();
		return $this->_color;
	}
	
	public function __toString() {
		return sprintf('Point: %2.f %2.f %2.f', $this->_x, $this->_y, $this->_z);
	}
}

?>
