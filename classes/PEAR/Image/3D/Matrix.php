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
 * @version    CVS: $Id: Matrix.php,v 1.5 2005/12/02 16:01:29 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */


/**
 * Image_3D_Matrix
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
class Image_3D_Matrix {
	
	protected $_matrix;
	
	public function __construct() {
		$this->setUnitMatrix();
	}
	
	public function setValue($x, $y, $value) {
		if (!isset($this->_matrix[$x][$y])) return false;
		$this->_matrix[$x][$y] = (float) $value;
	}
	
	public function getValue($x, $y) {
		if (!isset($this->_matrix[$x][$y])) return false;
		return (float) $this->_matrix[$x][$y];
	}
	
	public function setUnitMatrix() {
		$this->_matrix = array(	array(1., 0., 0., 0.),
								array(0., 1., 0., 0.),
								array(0., 0., 1., 0.),
								array(0., 0., 0., 1.)
						);
	}
	
	public function diff(Image_3D_Matrix $matrix) {
		$result = new Image_3D_Matrix();
		for ($i = 0; $i < 4; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$result->setValue($i, $j, $matrix->getValue($i, $j) - $this->_getValue($i, $j));
			}
		}
		return $result;
	}
	
	public function multiplySkalar($skalar) {
		for ($i = 0; $i < 4; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$this->_matrix[$i][$j] *= $skalar;
			}
		}
	}
	
	public function dump() {
		foreach ($this->_matrix as $row) {
			printf("|\t%2.2f\t%2.2f\t%2.2f\t%2.2f\t|\n", $row[0], $row[1], $row[2], $row[3]);
		}
	}
	
	public function setRotationMatrix($rotationX, $rotationY, $rotationZ) {
		
		$rotationY = (float) $rotationY;
		$rotationZ = (float) $rotationZ;
		
		if (!empty($rotationX)) {
			// Normalisierung der Rotation von Grad auf radiale Berechnung
			$rotationX = (float) $rotationX;
			$rotationX *= pi() / 180;
			// Setzen der Rotationsmatrix fuer Drehungen an der X-Achse
			$matrix = new Image_3D_Matrix();
			
			$matrix->setValue(1, 1, cos($rotationX));
			$matrix->setValue(1, 2, sin($rotationX));
			$matrix->setValue(2, 1, -sin($rotationX));
			$matrix->setValue(2, 2, cos($rotationX));

			// Setzen der Transformationsmatrix
			$this->_multiply($matrix);
			unset($matrix);
		}
		
		if (!empty($rotationY)) {
			// Normalisierung der Rotation von Grad auf radiale Berechnung
			$rotationY = (float) $rotationY;
			$rotationY *= pi() / 180;
			// Setzen der Rotationsmatrix fuer Drehungen an der X-Achse
			$matrix = new Image_3D_Matrix();
			
			$matrix->setValue(0, 0, cos($rotationY));
			$matrix->setValue(0, 2, -sin($rotationY));
			$matrix->setValue(2, 0, sin($rotationY));
			$matrix->setValue(2, 2, cos($rotationY));

			// Setzen der Transformationsmatrix
			$this->_multiply($matrix);
			unset($matrix);
		}
		
		if (!empty($rotationZ)) {
			// Normalisierung der Rotation von Grad auf radiale Berechnung
			$rotationZ = (float) $rotationZ;
			$rotationZ *= pi() / 180;
			// Setzen der Rotationsmatrix fuer Drehungen an der X-Achse
			$matrix = new Image_3D_Matrix();
			
			$matrix->setValue(0, 0, cos($rotationZ));
			$matrix->setValue(0, 1, sin($rotationZ));
			$matrix->setValue(1, 0, -sin($rotationZ));
			$matrix->setValue(1, 1, cos($rotationZ));

			// Setzen der Transformationsmatrix
			$this->_multiply($matrix);
			unset($matrix);
		}
	}
	
	public function setMoveMatrix($moveX, $moveY, $moveZ) {
		$matrix = new Image_3D_Matrix();
		$matrix->setValue(3, 0, (float) $moveX);
		$matrix->setValue(3, 1, (float) $moveY);
		$matrix->setValue(3, 2, (float) $moveZ);
		
		$this->_multiply($matrix);
	}
	
	public function setScaleMatrix($scaleX, $scaleY, $scaleZ) {
		$matrix = new Image_3D_Matrix();
		$matrix->setValue(0, 0, (float) $scaleX);
		$matrix->setValue(1, 1, (float) $scaleY);
		$matrix->setValue(2, 2, (float) $scaleZ);
		
		$this->_multiply($matrix);
	}
	
	protected function _multiply(Image_3D_Matrix $matrix) {
		$new = clone($this);

		for ($i = 0; $i < 4; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$sum = 0;
				for ($k = 0; $k < 4; $k++) {
					$sum += $new->getValue($i, $k) * $matrix->getValue($k, $j);
				}
				$this->setValue($i, $j, $sum);
			}
		}
	}
}

?>
