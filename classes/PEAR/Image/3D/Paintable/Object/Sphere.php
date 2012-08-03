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
 * @version    CVS: $Id: Sphere.php,v 1.5 2005/12/02 16:01:30 kore Exp $
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
class Image_3D_Object_Sphere extends Image_3D_Object {
	
	protected $_points;
	protected $_virtualPolygon;
	protected $_radius;
	
	public function __construct($options) {
		parent::__construct();
		
		$this->_lines = array();
		$this->_points = array();
		$this->_virtualPolygon = array();
		$this->_radius = (float) @$options['r'];
		$detail = (int) @$options['detail'];
		
		$this->_createTetraeder();
		
		for ($step = 0; $step < $detail; $step++) $this->_sierpinsky();
		
		$this->_getRealPolygones();
	}
	
	protected function _sierpinsky() {
		$newPolygones = array();
		$proceededLines = array();
		foreach ($this->_virtualPolygon as $points) {
			
			$lines = array(
				array(min($points[0], $points[1]), max($points[0], $points[1])),
				array(min($points[1], $points[2]), max($points[1], $points[2])),
				array(min($points[2], $points[0]), max($points[2], $points[0]))
			);
			
			$new = array();
			foreach ($lines as $line) {
				if (!isset($proceededLines[$line[0]][$line[1]])) {
					// Calculate new point
					$newX = ($this->_points[$line[0]]->getX() + $this->_points[$line[1]]->getX()) / 2;
					$newY = ($this->_points[$line[0]]->getY() + $this->_points[$line[1]]->getY()) / 2;
					$newZ = ($this->_points[$line[0]]->getZ() + $this->_points[$line[1]]->getZ()) / 2;
					
					$multiplikator = $this->_radius / sqrt(pow($newX, 2) + pow($newY, 2) + pow($newZ, 2));
					
					$this->_points[] = new Image_3D_Point($newX * $multiplikator, $newY * $multiplikator, $newZ * $multiplikator);

					$proceededLines[$line[0]][$line[1]] = count($this->_points) - 1;
				}
				$new[] = $proceededLines[$line[0]][$line[1]];
			}
			
			$newPolygones[] = array($points[0], $new[0], $new[2]);
			$newPolygones[] = array($points[1], $new[1], $new[0]);
			$newPolygones[] = array($points[2], $new[2], $new[1]);
			$newPolygones[] = array($new[0], $new[1], $new[2]);
		}
		$this->_virtualPolygon = $newPolygones;
	}
	
	protected function _createTetraeder() {
		$laenge = $this->_radius / sqrt(3);
		$this->_points[] = new Image_3D_Point(sqrt(2) * -$laenge, $laenge, 0);
		$this->_points[] = new Image_3D_Point(sqrt(2) * $laenge, $laenge, 0);
		$this->_points[] = new Image_3D_Point(0, -$laenge, sqrt(2) * -$laenge);
		$this->_points[] = new Image_3D_Point(0, -$laenge, sqrt(2) * $laenge);
		
		$this->_virtualPolygon[] = array(0, 1, 3);
		$this->_virtualPolygon[] = array(1, 2, 3);
		$this->_virtualPolygon[] = array(0, 2, 1);
		$this->_virtualPolygon[] = array(0, 3, 2);
	}
	
	protected function _getRealPolygones() {
		foreach ($this->_virtualPolygon as $points) {
			$this->_addPolygon(new Image_3D_Polygon($this->_points[$points[0]], $this->_points[$points[1]], $this->_points[$points[2]]));
		}
	}
}