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
 * @version    CVS: $Id: Coordinate.php,v 1.5 2005/12/02 16:01:29 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

// {{{ Image_3D_Coordinate

/**
 * Image_3D_Coordinate
 *
 * Base class for coordinates eg. points in the space
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
class Image_3D_Coordinate {
	
    // {{{ properties

    /**
     * X Coordiante
     *
     * @var float
     */
	protected $_x;

    /**
     * Y Coordiante
     *
     * @var float
     */
	protected $_y;

    /**
     * Z Coordiante
     *
     * @var float
     */
	protected $_z;

    /**
     * ID of the last transformation
     *
     * @var string
     */
	protected $_lastTransformation;

    /**
     * Variable saves if all relevant calculations for this point are done
     *
     * @var boolean
     */
	protected $_processed;
	
    /**
     * Screen coordinates (2d) of 3d-point
     *
     * @var array
     */
	protected $_screenCoordinates;
	
    // }}}
    // {{{ __construct()

    /**
     * Constructor for Image_3D_Coordinate
     *
     * Create a Point with the given coordinates
     * 
     * @param   mixed       $x              X Coordinate
     * @param   mixed       $y              Y Coordinate
     * @param   mixed       $z              Z Coordinate
     * @return  Image_3D_Coordinate         Instance of Coordinate
     */
	public function __construct($x, $y, $z) {
		$this->_x = (float) $x;
		$this->_y = (float) $y;
		$this->_z = (float) $z;
	}
	
    // }}}
    // {{{ transform()

    /**
     * Transform the Coordinate
     *
     * Use a transformationmatrix to transform (move) the point
     * 
     * @param   Image_3D_Matrix $matrix     Transformationmatrix
     * @param   string          $id         Transformationid
     * @return  void
     */
	public function transform(Image_3D_Matrix $matrix, $id = null) {
		// Point already transformed?
		if (($id !== null) && ($this->_lastTransformation === $id)) return false;
		$this->_lastTransformation = $id;
		
		$point = clone($this);
		
		$this->_x =	$point->getX() * $matrix->getValue(0, 0) +
					$point->getY() * $matrix->getValue(1, 0) +
					$point->getZ() * $matrix->getValue(2, 0) +
					$matrix->getValue(3, 0);
		$this->_y =	$point->getX() * $matrix->getValue(0, 1) +
					$point->getY() * $matrix->getValue(1, 1) +
					$point->getZ() * $matrix->getValue(2, 1) +
					$matrix->getValue(3, 1);
		$this->_z =	$point->getX() * $matrix->getValue(0, 2) +
					$point->getY() * $matrix->getValue(1, 2) +
					$point->getZ() * $matrix->getValue(2, 2) +
					$matrix->getValue(3, 2);
		$this->_screenCoordinates = null;
	}
	
    // }}}
    // {{{ processed()

    /**
     * Set Coordinate processed
     *
     * Store the coordinate as processed
     * 
     * @return  void
     */
	public function processed() {
		$this->_processed = true;
	}
	
    // }}}
    // {{{ isProcessed()

    /**
     * Coordinate already processed
     *
     * Return if coordinate already was processsed
     * 
     * @return  bool	processed
     */
	public function isProcessed() {
		return $this->_processed;
	}
	
    // }}}
    // {{{ getX()

    /**
     * Return X coordinate
     *
     * Returns the X coordinate of the coordinate
     * 
     * @return  float	X coordinate
     */
	public function getX() {
		return $this->_x;
	}
	
    // }}}
    // {{{ getY()

    /**
     * Return Y coordinate
     *
     * Returns the Y coordinate of the coordinate
     * 
     * @return  float	Y coordinate
     */
	public function getY() {
		return $this->_y;
	}
	
    // }}}
    // {{{ getZ()

    /**
     * Return Z coordinate
     *
     * Returns the Z coordinate of the coordinate
     * 
     * @return  float	Z coordinate
     */
	public function getZ() {
		return $this->_z;
	}

    // }}}
    // {{{ setScreenCoordinates()

    /**
     * Set precalculated screen coordinates
     *
     * Store the screen coordinates calculated by the Renderer
     * 
     * @param	float	$x	X coordinate
     * @param	float	$y	X coordinate
     * @return  void
     */
	public function setScreenCoordinates($x, $y) {
		$this->_screenCoordinates = array((float) $x, (float) $y);
	}
	
    // }}}
    // {{{ getScreenCoordinates()

    /**
     * Get screen coordinates
     *
     * Return an array with the screen coordinates
     * array ( 	0 =>	(float) $x,
     			1 =>	(float) $y )
     * 
     * @return  array	Screen coordinates
     */
	public function getScreenCoordinates() {
		return $this->_screenCoordinates;
	}
	
    // }}}
    // {{{ __toString()

    /**
     * Returns coordinate as string
     * 
     * @return  string	Coordinate
     */
	public function __toString() {
		return sprintf('Coordinate: %2.f %2.f %2.f', $this->_x, $this->_y, $this->_z);
	}
	
	// }}}
}

// }}}
