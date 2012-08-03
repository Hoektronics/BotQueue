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
 * @version    CVS: $Id: Torus.php,v 1.3 2005/12/02 16:01:30 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object.php');

/**
 * Image_3D_Object_Sphere
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
class Image_3D_Object_Torus extends Image_3D_Object {
	
	public function __construct($options) {
		parent::__construct();
		
		$inner_radius = (float) $options['inner_radius'];
		$outer_radius = (float) $options['outer_radius'];
		
		$r1 = ($outer_radius - $inner_radius) / 2;
		$r2 = $inner_radius + $r1;
		
		$d1 = (int) round(max(1, $options['detail_1']) * 4);
		$d2 = (int) round(max(1, $options['detail_2']) * 4);
		
		$rings = array();
		for ($i = 0; $i < $d1; ++$i) {
			$rings[$i] = array();
    		for ($j = 0; $j < $d2; ++$j) {
    			$_i = $i / $d1;
    			$_j = $j / $d2;
    		
				$z = cos($_j * pi() * 2) * $r1;
				$z2 = sin($_j * pi() * 2) * $r1;

				$x = ($r2 + $z2) * cos($_i * pi() * 2);
				$y = ($r2 + $z2) * sin($_i * pi() * 2);

    			$rings[$i][] = new Image_3D_Point($x, $y, $z);
    		}
		}
		
		foreach($rings as $i => $ring) {
			$i_next = ($i + 1) % count($rings);
			foreach($ring as $j => $point) {
				$j_next = ($j + 1) % count($ring);
				
				$this->_addPolygon(new Image_3D_Polygon($rings[$i_next][$j], $rings[$i][$j], $rings[$i][$j_next]));
				$this->_addPolygon(new Image_3D_Polygon($rings[$i_next][$j], $rings[$i][$j_next], $rings[$i_next][$j_next]));
			}
		}
	}

}