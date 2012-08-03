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
 * @version    CVS: $Id: Bezier.php,v 1.1 2006/03/11 17:14:11 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

require_once('Image/3D/Paintable/Object/Map.php');

/**
 * Image_3D_Object_Bezier
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
 * @since      Class available since Release 0.3.0
 */
class Image_3D_Object_Bezier extends Image_3D_Object_Map {
	
	public function __construct($options) {
        // Fetch options
        $x_detail = max(2, (int) @$options['x_detail']);
        $y_detail = max(2, (int) @$options['y_detail']);

        if (!isset($options['points']) || !is_array($options['points'])) return false;
        
        $points = array();
        foreach ($options['points'] as $row) {
            if (!is_array($row)) continue;
            $points[] = array();
            $akt_row = count($points) - 1;

            foreach ($row as $point) {
                if (!is_array($point)) continue;
                $points[$akt_row][] = $point;
            }
        }
        
        $n = count($points) - 1;
        $m = count($points[0]) - 1;
        $map = array();
        
        for ($u = 0; $u <= $x_detail; ++$u) {
            for ($v = 0; $v <= $y_detail; ++$v) {
                $point = array(0, 0, 0);

                for ($i = 0; $i <= $n; ++$i) {
                    for ($j = 0; $j <= $m; ++$j) {
                        $factor = $this->_bernstein($i, $n, $u / $x_detail) * $this->_bernstein($j, $m, $v / $y_detail);
                        $point[0] += $points[$i][$j][0] * $factor;
                        $point[1] += $points[$i][$j][1] * $factor;
                        $point[2] += $points[$i][$j][2] * $factor;
                    }
                }

                $map[$u][$v] = new Image_3D_Point($point[0], $point[1], $point[2]);
            }
        }

        parent::__construct($map);
	}

    protected function _binomial_coefficient($n, $k) {
        if ($k > $n) return 0;
        if ($k == 0) return 1;

        if (2 * $k > $n) {
            $result = $this->_binomial_coefficient($n, $n - $k);
        } else {
            $result = $n;
            for ($i = 2; $i <= $k; ++$i) {
                $result *= $n + 1 - $i;
                $result /= $i;
            }
        }

        return $result;
    }

    protected function _bernstein($i, $n, $t) {
        return $this->_binomial_coefficient($n, $i) * pow($t, $i) * pow(1 - $t, $n - $i);
    }

}
