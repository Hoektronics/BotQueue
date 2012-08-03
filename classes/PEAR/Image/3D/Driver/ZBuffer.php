<?php

class Image_3D_Driver_ZBuffer extends Image_3D_Driver {
	
	protected $_filetype;
	protected $_points;
	protected $_heigth;

	public function __construct() {
		parent::__construct();
		
		$this->_filetype = 'png';
		$this->_points = array();
		$this->_heigth = array();
	}
	
	public function createImage($x, $y) {
		$this->_image = imagecreatetruecolor($x, $y);
		imagealphablending($this->_image, true);
		imageSaveAlpha($this->_image, true);
	}
	
	protected function _getColor(Image_3D_Color $color, $alpha = 1.) {
		$values = $color->getValues();
		
		$values[0] = (int) round($values[0] * 255);
		$values[1] = (int) round($values[1] * 255);
		$values[2] = (int) round($values[2] * 255);
		$values[3] = (int) round((1 - ((1 - $values[3]) * $alpha)) * 127);

    	if ($values[3] > 0) {
			// Tranzparente Farbe allokieren
			$color = imageColorExactAlpha($this->_image, $values[0], $values[1], $values[2], $values[3]);
			if ($color === -1) {
				// Wenn nicht Farbe neu alloziieren
				$color = imageColorAllocateAlpha($this->_image, $values[0], $values[1], $values[2], $values[3]);
			}
		} else {
			// Deckende Farbe allozieren
			$color = imageColorExact($this->_image, $values[0], $values[1], $values[2]);
			if ($color === -1) {
				// Wenn nicht Farbe neu alloziieren
				$color = imageColorAllocate($this->_image, $values[0], $values[1], $values[2]);
			}
		}
		
		return $color;
	}
	
	public function setBackground(Image_3D_Color $color) {
		$bg = $this->_getColor($color);
		imagefill($this->_image, 1, 1, $bg);
	}
	
	protected function _drawLine(Image_3D_Point $p1, Image_3D_Point $p2) {
	    list($x1, $y1) = $p1->getScreenCoordinates();
	    list($x2, $y2) = $p2->getScreenCoordinates();
	    $z1 = $p1->getZ(); $z2 = $p2->getZ();
	    
	    $steps = ceil(max(abs($x1 - $x2), abs($y1 - $y2)));
	    
	    $xdiff = ($x2 - $x1) / $steps;
	    $ydiff = ($y2 - $y1) / $steps;
	    $zdiff = ($z2 - $z1) / $steps;
	    
	    $points = array('height' => array(), 'coverage' => array());
	    for ($i = 0; $i < $steps; $i++) {
    		$x = $x1 + $i * $xdiff;
    		$xFloor = floor($x);
    		$xCeil = ceil($x);
    		$xOffset = $x - $xFloor;
    		
    		$y = $y1 + $i * $ydiff;
    		$yFloor = floor($y);
    		$yCeil = ceil($y);
    		$yOffset = $y - $yFloor;
	        
    		if (!isset($points['coverage'][(int) $xFloor][(int) $yCeil])) {
    		    $points['height'][(int) $xFloor][(int) $yCeil] = $z1 + $i * $zdiff;
    		    $points['coverage'][(int) $xFloor][(int) $yCeil] = (1 - $xOffset) * $yOffset;
    		} else {
    		    $points['coverage'][(int) $xFloor][(int) $yCeil] += (1 - $xOffset) * $yOffset;
    		}
	        
    		if (!isset($points['coverage'][(int) $xFloor][(int) $yFloor])) {
    		    $points['height'][(int) $xFloor][(int) $yFloor] = $z1 + $i * $zdiff;
    		    $points['coverage'][(int) $xFloor][(int) $yFloor] = (1 - $xOffset) * (1 - $yOffset);
    		} else {
    		    $points['coverage'][(int) $xFloor][(int) $yFloor] += (1 - $xOffset) * (1 - $yOffset);
    		}
	        
    		if (!isset($points['coverage'][(int) $xCeil][(int) $yCeil])) {
    		    $points['height'][(int) $xCeil][(int) $yCeil] = $z1 + $i * $zdiff;
    		    $points['coverage'][(int) $xCeil][(int) $yCeil] = $xOffset * $yOffset;
    		} else {
    		    $points['coverage'][(int) $xCeil][(int) $yCeil] += $xOffset * $yOffset;
    		}
	        
    		if (!isset($points['coverage'][(int) $xCeil][(int) $yFloor])) {
    		    $points['height'][(int) $xCeil][(int) $yFloor] = $z1 + $i * $zdiff;
    		    $points['coverage'][(int) $xCeil][(int) $yFloor] = $xOffset * (1 - $yOffset);
    		} else {
    		    $points['coverage'][(int) $xCeil][(int) $yFloor] += $xOffset * (1 - $yOffset);
    		}
	    }
	    return $points;
	}
	
	protected function _getPolygonOutlines($pointArray) {
	    $map = array('height' => array(), 'coverage' => array());
	    
	    $last = end($pointArray);
	    foreach ($pointArray as $point) {
	        $line = $this->_drawLine($last, $point);
	        $last = $point;
	        // Merge line to map
	        foreach ($line['height'] as $x => $row) {
	            foreach ($row as $y => $height) {
	                $map['height'][(int) $x][(int) $y] = $height;
	                $map['coverage'][(int) $x][(int) $y] = $line['coverage'][(int) $x][(int) $y];
	            }
	        }
	    }
	    
	    return $map;
	}
	
	public function drawPolygon(Image_3D_Polygon $polygon) {
		$points = $this->_getPolygonOutlines($polygon->getPoints());

		foreach ($points['coverage'] as $x => $row) {
	        if (count($row) < 2) continue;
	        
	        $start = min(array_keys($row));
	        $end = max(array_keys($row));
	        
	        $zStart = $points['height'][$x][$start];
	        $zEnd = $points['height'][$x][$end];
	        $zStep = ($zEnd - $zStart) / ($end - $start);

	        // Starting point
            $this->_heigth[$x][$start][(int) ($zStart * 100)] = $this->_getColor($polygon->getColor(), $points['coverage'][$x][$start]);
            
            // the way between
            for ($y = $start + 1; $y < $end; $y++) {
                $this->_heigth[$x][$y][(int) (($zStart + $zStep * ($y - $start)) * 100)] = $this->_getColor($polygon->getColor());
	        }

	        // Ending point
            $this->_points[$x][$end][(int) ($zEnd * 100)] = $this->_getColor($polygon->getColor(), $points['coverage'][$x][$end]);
		}
	}
	
	public function drawGradientPolygon(Image_3D_Polygon $polygon) {
		$this->drawPolygon($polygon);
	}
	
	public function setFiletye($type) {
		$type = strtolower($type);
		if (in_array($type, array('png', 'jpeg'))) {
			$this->_filetype = $type;
			return true;
		} else {
			return false;
		}
	}
	
	public function save($file) {
	    
		foreach ($this->_heigth as $x => $row) {
		    foreach ($row as $y => $points) {
		        krsort($points);
		        foreach ($points as $color) imagesetpixel($this->_image, $x, $y, $color);
		    }
		}
	    
		switch ($this->_filetype) {
			case 'png':
				return imagepng($this->_image, $file);
			case 'jpeg':
				return imagejpeg($this->_image, $file);
		}
	}

	public function getSupportedShading() {
		return array(	Image_3D_Renderer::SHADE_NO, 
						Image_3D_Renderer::SHADE_FLAT, 
//						Image_3D_Renderer::SHADE_GAUROUD,
						);
	}
}

?>
