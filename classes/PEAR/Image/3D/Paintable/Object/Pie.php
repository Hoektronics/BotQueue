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
 * @version    CVS: $Id: Pie.php,v 1.6 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object.php');

/**
 * Image_3D_Object_Pie
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
class Image_3D_Object_Pie extends Image_3D_Object {
	
	public function __construct($parameter) {
		$parameter = $this->_checkParameter($parameter);
		
		if ($parameter['inside'] == 0) {
			$this->_createPie($parameter);
		} else {
			$this->_createDonutPie($parameter);
		}
	}
	
	protected function _createPie($parameter) {
		$step = ($parameter['end'] - $parameter['start']) / $parameter['detail'];

		// center
		$centerTop = new Image_3D_Point(0, 0, .5);
		$centerBottom = new Image_3D_Point(0, 0, -.5);
		
		// Add polygones for top and bottom of the pie
		$x = cos($parameter['start']) * $parameter['outside'];
		$y = sin($parameter['start']) * $parameter['outside'];
		$top = new Image_3D_Point($x, $y, .5);
		$bottom = new Image_3D_Point($x, $y, -.5);
		
		// Polygones for the opening side
		$this->_addPolygon(new Image_3D_Polygon($top, $centerTop, $centerBottom));
		$this->_addPolygon(new Image_3D_Polygon($bottom, $top, $centerBottom));
		
		for ($i = 1; $i <= $parameter['detail']; $i++) {
			$x = cos($parameter['start'] + $i * $step) * $parameter['outside'];
			$y = sin($parameter['start'] + $i * $step) * $parameter['outside'];
			
			$newTop = new Image_3D_Point($x, $y, .5);
			$newBottom = new Image_3D_Point($x, $y, -.5);

			$this->_addPolygon(new Image_3D_Polygon($centerTop, $top, $newTop));
			$this->_addPolygon(new Image_3D_Polygon($centerBottom, $bottom, $newBottom));

			// Rand
			$this->_addPolygon(new Image_3D_Polygon($top, $newBottom, $newTop));
			$this->_addPolygon(new Image_3D_Polygon($top, $bottom, $newBottom));
			
			$top = $newTop; $bottom = $newBottom;
		}
		
		// Polygones for the closing side
		$this->_addPolygon(new Image_3D_Polygon($top, $centerTop, $centerBottom));
		$this->_addPolygon(new Image_3D_Polygon($bottom, $top, $centerBottom));
	}
	
	protected function _checkParameter($array) {
		$array['detail'] = max(1, (int) @$array['detail']);
		$array['outside'] = max(0, (int) @$array['outside']);
		$array['inside'] = min(max(0, (int) @$array['inside']), @$array['outside']);
		$array['start'] = max(0, ((int) @$array['start']) * pi() / 180);
		$array['end'] = max(0, ((int) @$array['end']) * pi() / 180);
		
		return ($array);
	}
}
