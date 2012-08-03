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
 * @version    CVS: $Id: Cube.php,v 1.5 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object.php');

/**
 * Image_3D_Object_Cube
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
class Image_3D_Object_Cube extends Image_3D_Object {
	
	protected $_points;
	
	public function __construct($parameter) {
		parent::__construct();

		$x = (float) @$parameter[0];		
		$y = (float) @$parameter[1];		
		$z = (float) @$parameter[2];		
		
		$this->_points = array();
		
		$this->_points[1] = new Image_3D_Point(-$x / 2, -$y / 2, -$z / 2);
		$this->_points[2] = new Image_3D_Point(-$x / 2, -$y / 2,  $z / 2);

		$this->_points[3] = new Image_3D_Point(-$x / 2,  $y / 2, -$z / 2);
		$this->_points[4] = new Image_3D_Point(-$x / 2,  $y / 2,  $z / 2);

		$this->_points[5] = new Image_3D_Point( $x / 2, -$y / 2, -$z / 2);
		$this->_points[6] = new Image_3D_Point( $x / 2, -$y / 2,  $z / 2);

		$this->_points[7] = new Image_3D_Point( $x / 2,  $y / 2, -$z / 2);
		$this->_points[8] = new Image_3D_Point( $x / 2,  $y / 2,  $z / 2);
		
		// Oben & Unten
		$this->_addPolygon(new Image_3D_Polygon($this->_points[3], $this->_points[4], $this->_points[8]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[3], $this->_points[8], $this->_points[7]));
		
		$this->_addPolygon(new Image_3D_Polygon($this->_points[2], $this->_points[1], $this->_points[6]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[1], $this->_points[5], $this->_points[6]));

		// Links & Rechts
		$this->_addPolygon(new Image_3D_Polygon($this->_points[3], $this->_points[2], $this->_points[4]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[3], $this->_points[1], $this->_points[2]));
		
		$this->_addPolygon(new Image_3D_Polygon($this->_points[8], $this->_points[5], $this->_points[7]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[8], $this->_points[6], $this->_points[5]));

		// Rueck- & Frontseite
		$this->_addPolygon(new Image_3D_Polygon($this->_points[2], $this->_points[8], $this->_points[4]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[2], $this->_points[6], $this->_points[8]));
		
		$this->_addPolygon(new Image_3D_Polygon($this->_points[1], $this->_points[7], $this->_points[5]));
		$this->_addPolygon(new Image_3D_Polygon($this->_points[1], $this->_points[3], $this->_points[7]));
	}
	
	public function getPoint($int) {
		if (isset($this->_points[$int])) {
			return $this->_points[$int];
		} else {
			return false;
		}
	}
}