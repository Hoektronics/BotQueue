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
 * @version    CVS: $Id: Renderer.php,v 1.8 2005/12/02 16:01:29 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

// {{{ Image_3D_Renderer

/**
 * Image_3D_Renderer
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
abstract class Image_3D_Renderer {
	
    // {{{ properties

    /**
     * Worlds polygones
     *
     * @var array
     */
	protected $_polygones;

    /**
     * Worlds points
     *
     * @var array
     */
	protected $_points;

    /**
     * Worlds lights
     *
     * @var array
     */
	protected $_lights;
	
    /**
     * Driver we use
     *
     * @var array
     */
	protected $_driver;
	
    /**
     * Size of the Image
     *
     * @var array
     */
	protected $_size;

    /**
     * Backgroundcolol
     *
     * @var Image_3D_Color
     */
	protected $_background;
	
    /**
     * Type of Shading used
     *
     * @var integer
     */
	protected $_shading;
	
    // }}}
    // {{{ Constants

	/*
     * No Shading
     */
    const SHADE_NO			= 0;
	/*
     * Flat Shading
     */
	const SHADE_FLAT		= 1;
	/*
     * Gauroud Shading
     */
	const SHADE_GAUROUD		= 2;
	/*
     * Phong Shading
     */
	const SHADE_PHONG		= 3;

    // }}}
    // {{{ __construct()

    /**
     * Constructor for Image_3D_Renderer
     *
     * Initialises the environment
     *
     * @return  Image_3D_Renderer           Instance of Renderer
     */
	public function __construct() {
	    $this->reset();
	}
	
    // }}}
    // {{{ reset()

    /**
     * Reset all changeable variables
     *
     * Initialises the environment
     *
     * @return  void
     */
	public function reset() {
		$this->_objects = array();
		$this->_polygones = array();
		$this->_points = array();
		$this->_lights = array();
		$this->_size = array(0, 0);
		$this->_background = null;
		
		$this->_driver = null;
		
		$this->_shading = self::SHADE_PHONG;
	}

    // }}}
    // {{{ _getPolygones()
	
    /**
     * Get and merge polygones
     *
     * Get polygones and points from an object and merge them unique to local
     * polygon- and pointarrays.
     *
     * @param   Image_3D_Object $object Object to merge
     * @return  void
     */
	protected function _getPolygones(Image_3D_Object $object) {
		$newPolygones = $object->getPolygones();
		$this->_polygones = array_merge($this->_polygones, $newPolygones);
		
		// Add points unique to points-Array
		foreach ($newPolygones as $polygon) {
			$points = $polygon->getPoints();
			foreach ($points as $point) {
				if (!$point->isProcessed()) {
					$point->processed();
					array_push($this->_points, $point);
				}
			}
		}
	}
	
    // }}}
    // {{{ _calculateScreenCoordiantes()
	
    /**
     * Caclulate Screen Coordinates
     *
     * Calculate screen coordinates for a point according to the perspektive 
     * the renderer should display
     *
     * @param   Image_3D_Point  $point  Point to process
     * @return  void
     */
	abstract protected function _calculateScreenCoordiantes(Image_3D_Point $point);
	
    // }}}
    // {{{ _sortPolygones()
	
    /**
     * Sort polygones
     *
     * Set the order the polygones will be displayed
     *
     * @return  void
     */
	abstract protected function _sortPolygones();
	
    // }}}
    // {{{ addObjects()
	
    /**
     * Add objects to renderer
     *
     * Add objects to renderer. Only objects which are added will be 
     * displayed
     *
     * @param   array           $objetcs    Array of objects
     * @return  void
     */
	public function addObjects($objects) {
		if (is_array($objects)) {
			foreach ($objects as $object) {
				if ($object instanceof Image_3D_Object) {
					$this->_getPolygones($object);
				}
			}
		} elseif ($objects instanceof Image_3D_Object) {
			$this->_getPolygones($objects);
		}
	}
	
    // }}}
    // {{{ addObjects()
	
    /**
     * Add objects to renderer
     *
     * Add objects to renderer. Only objects which are added will be 
     * displayed
     *
     * @param   array           $objetcs    Array of objects
     * @return  void
     */
	public function addLights($lights) {
		$this->_lights = array_merge($this->_lights, $lights);
	}
	
    // }}}
    // {{{ setSize()
	
    /**
     * Set image size
     *
     * Set the size of the destination image.
     *
     * @param   integer         $x          Width
     * @param   integer         $y          Height
     * @return  void
     */
	public function setSize($x, $y) {
		$this->_size = array($x / 2, $y / 2);
	}
	
    // }}}
    // {{{ setBackgroundColor()
	
    /**
     * Set the Backgroundcolor
     *
     * Set the backgroundcolor of the destination image.
     *
     * @param   Image_3D_Color  $color      Backgroundcolor
     * @return  void
     */
	public function setBackgroundColor(Image_3D_Color $color) {
		$this->_background = $color;
	}
	
    // }}}
    // {{{ setShading()
	
    /**
     * Set the quality of the shading
     *
     * Set the quality of the shading. Standard value is the maximum shading
     * quality the driver is able to render.
     *
     * @param   integer         $shading    Shading quality
     * @return  void
     */
	public function setShading($shading) {
		$this->_shading = min($this->_shading, (int) $shading);
	}
	
    // }}}
    // {{{ setDriver()
	
    /**
     * Set the driver
     *
     * Set the driver the image should be rendered with
     *
     * @param   Image_3D_Driver $driver     Driver to use
     * @return  void
     */
	public function setDriver(Image_3D_Driver $driver) {
		$this->_driver = $driver;
		
		$this->setShading(max($driver->getSupportedShading()));
	}
	
    // }}}
    // {{{ getPolygonCount()
	
    /**
     * Return polygon count
     *
     * Return the number of used polygones in this image
     *
     * @return  integer     Number of Polygones
     */
	public function getPolygonCount() {
		return count($this->_polygones);
	}
	
    // }}}
    // {{{ getPointCount()
	
    /**
     * Return point count
     *
     * Return the number of used points in this image
     *
     * @return  integer     Number of Points
     */
	public function getPointCount() {
		return count($this->_points);
	}
	
    // }}}
    // {{{ getLightCount()
	
    /**
     * Return light count
     *
     * Return the number of used lights in this image
     *
     * @return  integer     Number of Lights
     */
	public function getLightCount() {
		return count($this->_lights);
	}
	
    // }}}
    // {{{ _calculatePolygonColors()
	
    /**
     * Calculate the color of all polygones
     *
     * Let each polygon calculate his color based on the lights which are
     * registered for this image
     *
     * @return  void
     */
	protected function _calculatePolygonColors() {
		foreach ($this->_polygones as $polygon) {
			$polygon->calculateColor($this->_lights);
		}
	}
	
    // }}}
    // {{{ _calculatePointColors()
	
    /**
     * Calculate the colors of all points
     *
     * Let each point calculate his color based on his normale which is
     * calculated on his surrounding polygones and the lights which are
     * registered for this image
     *
     * @return  void
     */
	protected function _calculatePointColors() {
		foreach ($this->_polygones as $polygon) {
			$normale = $polygon->getNormale();
			$color = $polygon->getColor();
			
			$points = $polygon->getPoints();
			foreach ($points as $point) {
				$point->addVector($normale);
				$point->addColor($color);
			}
		}
		
		foreach ($this->_points as $point) $point->calculateColor($this->_lights);
	}
	
    // }}}
    // {{{ _shade()
	
    /**
     * Draw all polygones
     *
     * Draw all polygones concerning the type of shading wich was set for the renderer
     *
     * @return  void
     */
	protected function _shade() {
		switch ($this->_shading) {
			case self::SHADE_NO:
				foreach ($this->_polygones as $polygon) $this->_driver->drawPolygon($polygon);
			break;
			
			case self::SHADE_FLAT:
				$this->_calculatePolygonColors();
				foreach ($this->_polygones as $polygon) $this->_driver->drawPolygon($polygon);
			break;
			
			case self::SHADE_GAUROUD:
				$this->_calculatePointColors();
				foreach ($this->_polygones as $polygon) $this->_driver->drawGradientPolygon($polygon);
			break;
			
			default:
				throw new Exception('Shading type not supported.');
			break;
		}
	}
	
    // }}}
    // {{{ render()
	
    /**
     * Render the image
     *
     * Render the image into the metioned file
     *
     * @param   string              $file       Filename
     * @return  void
     */
	public function render($file) {
		if (empty($this->_driver)) return false;
		
		// Calculate screen coordinates
		foreach ($this->_points as $point) $this->_calculateScreenCoordiantes($point);
		$this->_sortPolygones();
		
		// Draw background
		$this->_driver->createImage($this->_size[0] * 2, $this->_size[1] * 2);
		$this->_driver->setBackground($this->_background);
		
		// Create polygones in driver
		$this->_shade();
		
		// Save image
		$this->_driver->save($file);
	}

    // }}}
}

// }}}
