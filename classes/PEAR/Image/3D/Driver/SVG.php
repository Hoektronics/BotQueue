<?php

class Image_3D_Driver_SVG extends Image_3D_Driver {

    protected $_x;
    protected $_y;

    protected $_id;

    protected $_gradients;
    protected $_polygones;

    public function __construct() {
        $this->_image = '';
        $this->_id = 1;
        $this->_gradients = array();
        $this->_polygones = array();
    }

    public function createImage($x, $y) {
        $this->_x = (int) $x;
        $this->_y = (int) $y;

        $this->_image = <<<EOF
<?xml version="1.0" ?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg xmlns="http://www.w3.org/2000/svg" x="0" y="0" width="{$this->_x}" height="{$this->_y}">
EOF;
        $this->_image .= "\n\n";
    }

    public function setBackground(Image_3D_Color $color) {
        $this->_addPolygon(sprintf("\t<polygon id=\"background%d\" points=\"0,0 %d,0 %d,%d 0,%d\" style=\"%s\" />\n",
            $this->_id++,
            $this->_x,
            $this->_x,
            $this->_y,
            $this->_y,
            $this->_getStyle($color)));
    }

    protected function _getStyle(Image_3D_Color $color) {
        $values = $color->getValues();

        $values[0] = (int) round($values[0] * 255);
        $values[1] = (int) round($values[1] * 255);
        $values[2] = (int) round($values[2] * 255);
        $values[3] = 1 - $values[3];

        return sprintf('fill: #%02x%02x%02x; fill-opacity: %.2f; stroke: none;', $values[0], $values[1], $values[2], $values[3]);
    }

    protected function _getStop(Image_3D_Color $color, $offset = 0, $alpha = null) {
        $values = $color->getValues();

        $values[0] = (int) round($values[0] * 255);
        $values[1] = (int) round($values[1] * 255);
        $values[2] = (int) round($values[2] * 255);
        if ($alpha === null) {
            $values[3] = 1 - $values[3];
        } else {
            $values[3] = 1 - $alpha;
        }

        return sprintf("\t\t\t<stop id=\"stop%d\" offset=\"%.1f\" style=\"stop-color: rgb(%d, %d, %d); stop-opacity: %.4f;\" />\n", $this->_id++, $offset, $values[0], $values[1], $values[2], $values[3]);
    }

    protected function _addGradient($string) {
        $id = 'linearGradient' . $this->_id++;
        $this->_gradients[] = str_replace('[id]', $id, $string);
        return $id;
    }

    protected function _addPolygon($string) {
        $id = 'polygon' . $this->_id++;
        $this->_polygones[] = str_replace('[id]', $id, $string);
        return $id;
    }

    public function drawPolygon(Image_3D_Polygon $polygon) {

        $list = '';
        $points = $polygon->getPoints();
        foreach ($points as $point) {
            $pointarray = $point->getScreenCoordinates();
            $list .= sprintf('%.2f,%.2f ', $pointarray[0], $pointarray[1]);
        }

        $this->_addPolygon(sprintf("\t<polygon points=\"%s\" style=\"%s\" />\n",
            substr($list, 0, -1),
            $this->_getStyle($polygon->getColor())));
    }

    public function drawGradientPolygon(Image_3D_Polygon $polygon) {
        $points = $polygon->getPoints();

        $list = '';
        $pointarray = array();
        foreach ($points as $nr => $point) {
            $pointarray[$nr] = $point->getScreenCoordinates();
            $list .= sprintf('%.2f,%.2f ', $pointarray[$nr][0], $pointarray[$nr][1]);
        }

        // Groessen
        $xOffset = min($pointarray[0][0], $pointarray[1][0], $pointarray[2][0]);
        $yOffset = min($pointarray[0][1], $pointarray[1][1], $pointarray[2][1]);

        $xSize = max(abs($pointarray[0][0] - $pointarray[1][0]), abs($pointarray[0][0] - $pointarray[2][0]), abs($pointarray[1][0] - $pointarray[2][0]));
        $ySize = max(abs($pointarray[0][1] - $pointarray[1][1]), abs($pointarray[0][1] - $pointarray[2][1]), abs($pointarray[1][1] - $pointarray[2][1]));

        // Base Polygon
        $lg = $this->_addGradient(sprintf("\t\t<linearGradient id=\"[id]\" x1=\"%.2f\" y1=\"%.2f\" x2=\"%.2f\" y2=\"%.2f\">\n%s\t\t</linearGradient>\n",
            ($pointarray[0][0] - $xOffset) / $xSize,
            ($pointarray[0][1] - $yOffset) / $ySize,
            ($pointarray[1][0] - $xOffset) / $xSize,
            ($pointarray[1][1] - $yOffset) / $ySize,
            $this->_getStop($points[0]->getColor()) . $this->_getStop($points[1]->getColor(), 1)));

        $this->_addPolygon(sprintf("\t<polygon id=\"[id]\" points=\"%s\" style=\"fill: url(#%s); stroke: none; fill-opacity: 1;\" />\n", $list, $lg));

        // Overlay Polygon
        $lg = $this->_addGradient(sprintf("\t\t<linearGradient id=\"[id]\" x1=\"%.2f\" y1=\"%.2f\" x2=\"%.2f\" y2=\"%.2f\">\n%s\t\t</linearGradient>\n",
            ($pointarray[2][0] - $xOffset) / $xSize,
            ($pointarray[2][1] - $yOffset) / $ySize,
            ((($pointarray[0][0] + $pointarray[1][0]) / 2) - $xOffset) / $xSize,
            ((($pointarray[0][1] + $pointarray[1][1]) / 2) - $yOffset) / $ySize,
            $this->_getStop($points[2]->getColor()) . $this->_getStop($points[2]->getColor(), 1, 1)));

        $this->_addPolygon(sprintf("\t<polygon id=\"[id]\" points=\"%s\" style=\"fill: url(#%s); stroke: none; fill-opacity: 1;\" />\n", $list, $lg));
    }

    public function save($file) {
        $this->_image .= sprintf("\t<defs id=\"defs%d\">\n", $this->_id++);
        $this->_image .= implode('', $this->_gradients);
        $this->_image .= sprintf("\t</defs>\n\n");

        $this->_image .= implode('', $this->_polygones);
        $this->_image .= "</svg>\n";
        file_put_contents($file, $this->_image);
    }

    public function getSupportedShading() {
        return array(Image_3D_Renderer::SHADE_NO, Image_3D_Renderer::SHADE_FLAT, Image_3D_Renderer::SHADE_GAUROUD);
    }
}

?>
