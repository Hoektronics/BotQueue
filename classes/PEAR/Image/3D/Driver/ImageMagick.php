<?php

class Image_3D_Driver_ImageMagick extends Image_3D_Driver {
	
    /**
     * Array of parameter strings passed to 'convert' binary. 
     * 
     * @var array
     * @access protected
     */
    protected $_commandQueue = array();

	public function __construct() {
		parent::__construct();
	}
	
	public function createImage($x, $y) {
		$this->_image = tempnam();
        $this->_dimensions = array('x' => $x, 'y' => $y);
        $this->_commandQueue[] = "-size {$x}x{$y} xc:transparent";
	}
	
	protected function _getColor(Image_3D_Color $color) {
		$values = $color->getValues();

		$values[0] = (int) round($values[0] * 255);
		$values[1] = (int) round($values[1] * 255);
		$values[2] = (int) round($values[2] * 255);
		$values[3] = (int) round($values[3] * 127);
		
        $color = '';
        if ($values[3] > 0) {
            $color = 'rgba('.implode(',', $values).')';
        } else {
            unset($values[3]);
            $color = 'rgb('.implode(',', $values).')';
        }
        
		return $color;
	}
	
	public function setBackground(Image_3D_Color $color) {
        $colorString = $this->_getColor($color);
        array_splice(
            $this->_commandQueue, 
            1, 
            0, 
            '-fill '.escapeshellarg($colorString).' '.
            '-draw '.escapeshellarg('rectangle 0,0 '.implode(',', $this->_dimensions))
        );
	}
	
	public function drawPolygon(Image_3D_Polygon $polygon) {
		// Get points
		$points = $polygon->getPoints();
		$coords = array();

        $coords = '';
		foreach ($points as $point) {
            $pointCoords = $point->getScreenCoordinates();
            $coords .= (int)$pointCoords[0].','.(int)$pointCoords[1].' ';
        }
		
        $command = '-fill '.escapeshellarg($this->_getColor($polygon->getColor()));
        $command .= ' -draw '.escapeshellarg('polygon '.trim($coords));
        $this->_commandQueue[] = $command;
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
        $command = '';
        $commandCount = 1;
        $firstRun = true;
        for ($i = 0; $i < count($this->_commandQueue); $i++) {
            $command .= ' '.$this->_commandQueue[$i].' ';
            if (strlen($command) > 1000 || $i == count($this->_commandQueue) - 1) {
                $firstRun === false ? $command = $file . ' ' . $command : $firstRun = false;
                
                $command =  'convert ' . $command . ' ' . $file;
                // echo "Excuting command run <".$commandCount++.">\n";
                shell_exec($command);
                $command = '';
            }
        }
        shell_exec($command);
	}

	public function getSupportedShading() {
		return array(Image_3D_Renderer::SHADE_NO, Image_3D_Renderer::SHADE_FLAT);
	}
}

?>
