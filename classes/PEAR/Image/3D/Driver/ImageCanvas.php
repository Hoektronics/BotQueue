<?php

require_once 'Image/Canvas.php';

class Image_3D_Driver_ImageCanvas extends Image_3D_Driver {
	
	protected $_filetype;
	protected $_type;

	public function __construct() {
		parent::__construct();
	}
	
	public function setImageType($type) {
		$this->_type = (string) $type;
	}
	
	public function createImage($x, $y) {
		$this->_image = Image_Canvas::factory($this->_type, array('width' => $x, 'height' => $y, 'antialias' => 'driver'));
	}
	
	protected function _getColor(Image_3D_Color $color) {
		$values = $color->getValues();
		return sprintf('#%02x%02x%02x@%f', (int) ($values[0] * 255), (int) ($values[1] * 255), (int) ($values[2] * 255), 1 - $values[3]);
	}
	
	public function setBackground(Image_3D_Color $color) {
		$this->_image->setFillColor($this->_getColor($color));
		$this->_image->rectangle(array('x0' => 0, 'y0' => 0, 'x1' => $this->_image->getWidth(), 'y1' => $this->_image->getHeight()));
	}
	
	public function drawPolygon(Image_3D_Polygon $polygon) {
		
		// Build pointarray
		$pointArray = array();
		$points = $polygon->getPoints();
		foreach ($points as $point) {
			$screenCoordinates = $point->getScreenCoordinates();
			$this->_image->addVertex(array('x' => $screenCoordinates[0], 'y' => $screenCoordinates[1]));
		}
		$this->_image->setFillColor($this->_getColor($polygon->getColor()));
		$this->_image->setLineColor(false);
		$this->_image->polygon(array('connect' => true));
	}
	
	public function drawGradientPolygon(Image_3D_Polygon $polygon) {
		$this->drawPolygon($polygon);
	}
	
	public function save($file) {
		$this->_image->save(array('filename' => $file));
	}

	public function getSupportedShading() {
		return array(Image_3D_Renderer::SHADE_NO, Image_3D_Renderer::SHADE_FLAT);
	}
}

?>