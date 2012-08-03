<?php

require_once('Image/3D/Paintable/Object.php');

class Image_3D_Object_Cone extends Image_3D_Object {
	
	public function __construct($parameter) {
		parent::__construct();

		$radius = 1;
		$height = 1;
		$detail = max(3, (int) $parameter['detail']);

		// Generate points according to parameters
		$top = new Image_3D_Point(0, $height, 0);
		$bottom = new Image_3D_Point(0, 0, 0);

		$last = new Image_3D_Point(1, 0, 0);
		$points[] = $last;

		for ($i = 1; $i <= $detail; ++$i) {
			$actual = new Image_3D_Point(cos($i / $detail * 2 * pi()), 0, sin($i / $detail * 2 * pi()));
			$points[] = $actual;
			
			// Build polygon
			$this->_addPolygon(new Image_3D_Polygon($top, $last, $actual));
			$this->_addPolygon(new Image_3D_Polygon($bottom, $last, $actual));
			$last = $actual;
		}

		// Build closing polygon
		$this->_addPolygon(new Image_3D_Polygon($top, $last, $points[0]));
		$this->_addPolygon(new Image_3D_Polygon($bottom, $last, $points[0]));
	}
}
