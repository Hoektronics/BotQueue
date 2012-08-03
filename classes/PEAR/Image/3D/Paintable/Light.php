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
 * @version    CVS: $Id: Light.php,v 1.6 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */


/**
 * Image_3D_Light
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
class Image_3D_Light extends Image_3D_Coordinate implements Image_3D_Interface_Paintable {
	
	protected $_color;
	
	public function __construct($x, $y, $z) {
		parent::__construct($x, $y, $z);
		$this->_color = null;
		$this->_position = null;
	}
	
	public function getPolygonCount() {
		return 0;
	}
	
	public function setColor(Image_3D_Color $color) {
		$this->_color = $color;
	}
	
	public function setOption($option, $value) {
		$this->_option[$option] = $value;
	}
	
	public function getColor(Image_3D_Interface_Enlightenable $polygon) {
		$color = clone ($polygon->getColor());
		
		$light = new Image_3D_Vector($this->_x, $this->_y, $this->_z);
		$light->sub($polygon->getPosition());
		$light->unify();
		$light->add(new Image_3D_Vector(0, 0, -1));
		
		$normale = $polygon->getNormale();
		
		$angle = abs(1 - $normale->getAngle($light));
		
		$color->addLight($this->_color, $angle);
		return $color;
	}
}

?>
