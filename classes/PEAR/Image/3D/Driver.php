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
 * @version    CVS: $Id: Driver.php,v 1.4 2005/12/02 16:01:29 kore Exp $
 * @link       http://pear.php.net/package/PackageName
 * @since      File available since Release 0.1.0
 */

// {{{ Image_3D_Driver

/**
 * Image_3D_Driver
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
abstract class Image_3D_Driver {
	
    // {{{ properties

    /**
     * Worlds polygones
     *
     * @var mixed
     */
	protected $_image;
	
    // }}}
    // {{{ __construct()

    /**
     * Constructor for Image_3D_Driver
     *
     * Initialises the environment
     *
     * @return  Image_3D_Driver             Instance of Renderer
     */
	public function __construct() {
		$this->_image = null;
	}
	
    // }}}
    // {{{ createImage()
	
    /**
     * Initialize image
     *
     * Initialize the image with given width and height
     *
     * @param   integer         $x      Width
     * @param   integer         $y      Height
     * @return  void
     */
	abstract public function createImage($x, $y);
	
    // }}}
    // {{{ setBackground()
	
    /**
     * Sets Background
     *
     * Set the background for the image
     *
     * @param   Image_3D_Color  $color  Backgroundcolor
     * @return  void
     */
	abstract public function setBackground(Image_3D_Color $color);
	
    // }}}
    // {{{ drawPolygon()
	
    /**
     * Draws a flat shaded polygon
     *
     * Draws a flat shaded polygon. Methd uses the polygon color
     *
     * @param   Image_3D_Polygon    $polygon    Polygon
     * @return  void
     */
	abstract public function drawPolygon(Image_3D_Polygon $polygon);
	
    // }}}
    // {{{ drawGradientPolygon()
	
    /**
     * Draws a gauroud shaded polygon
     *
     * Draws a gauroud shaded polygon. Methd uses the colors of the polygones
     * points and tries to create a gradient filling for the polygon.
     *
     * @param   Image_3D_Polygon    $polygon    Polygon
     * @return  void
     */
	abstract public function drawGradientPolygon(Image_3D_Polygon $polygon);
	
    // }}}
    // {{{ save()
	
    /**
     * Save image
     *
     * Save image to file
     *
     * @param   string          $file   File
     * @return  void
     */
	abstract public function save($file);
	
	
    // }}}
    // {{{ getSupportedShading()
	
    /**
     * Return supported shadings
     *
     * Return an array with the shading types the driver supports
     *
     * @return  array           Array with supported shading types
     */
	public function getSupportedShading() {
		return array(Image_3D_Renderer::SHADE_NO);
	}
	
	// }}}
}

// }}}
