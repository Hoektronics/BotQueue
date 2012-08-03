<?php
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
 * @author     Arne Nordmann <3d-rotate@arne-nordmann.de>
 */

/**
 * Creates a SVG, to move and rotate the 3D-object at runtime
 *
 * @category   Image
 * @package    Image_3D
 * @author     Arne Nordmann <3d-rotate@arne-nordmann.de>
 */
class Image_3D_Driver_SVGControl extends Image_3D_Driver {

    /**
     * Width of the image
     */
    protected $_x;
    /**
     * Height of the image
     */
    protected $_y;

    /**
     * Current, increasing element-id (integer)
     */
    protected $_id;

    /**
     * Array of gradients
     */
    protected $_gradients;
    /**
     * Rectangle with background-color
     */
    protected $_background;
    /**
     * Array of polygones
     */
    protected $_polygones;

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->_image = '';
        $this->_id = 1;
        $this->_gradients = array();
        $this->_polygones = array();
    }

    /**
     * Creates image header
     *
     * @param float width of the image
     * @param float height of the image
     */
    public function createImage($x, $y) {
        $this->_x = (int) $x;
        $this->_y = (int) $y;

        $this->_image = <<<EOF
<?xml version="1.0" ?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
         "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">

<svg xmlns="http://www.w3.org/2000/svg" x="0" y="0" width="{$this->_x}" height="{$this->_y}" onload="init(evt)">
EOF;
        $this->_image .= "\n\n";
    }

    /**
     *
     * Adds coloured background to the image
     *
     * Draws a rectangle with the size of the image and the passed colour
     *
     * @param Image_3D_Color Background colour of the image
     */
    public function setBackground(Image_3D_Color $color) {
        $this->_background = "\t<!-- coloured background -->\n";
        $this->_background .= sprintf("\t<rect id=\"background\" x=\"0\" y=\"0\" width=\"%d\" height=\"%d\" style=\"%s\" />\n",
            $this->_x,
            $this->_y,
            $this->_getStyle($color));
    }

    protected function _getStyle(Image_3D_Color $color) {
        $values = $color->getValues();

        $values[0] = (int) round($values[0] * 255);
        $values[1] = (int) round($values[1] * 255);
        $values[2] = (int) round($values[2] * 255);
        $values[3] = 1 - $values[3];

        // optional: "shape-rendering: optimizeSpeed;" to increase Speed
        return sprintf('fill:#%02x%02x%02x; stroke:none; shape-rendering:optimizeSpeed;',
                        $values[0],
                        $values[1],
                        $values[2]);
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

        return sprintf("\t\t\t<stop id=\"stop%d\" offset=\"%.1f\" style=\"stop-color:rgb(%d, %d, %d); stop-opacity:%.4f;\" />\n",
                        $this->_id++,
                        $offset,
                        $values[0],
                        $values[1],
                        $values[2],
                        $values[3]);
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

        $svg = "\t\t\t<polygon id=\"[id]\" "; //Image_3D_P
        foreach ($points as $nr => $point) {
            $svg .= 'x'.$nr.'="'.round($point->getX()).'" ';
            $svg .= 'y'.$nr.'="'.round($point->getY()).'" ';
            $svg .= 'z'.$nr.'="'.round($point->getZ()).'" ';
        }
        $svg .= 'style="'.$this->_getStyle($polygon->getColor())."\" />\n";

        $this->_addPolygon($svg);
    }

    public function drawGradientPolygon(Image_3D_Polygon $polygon) {
    }

    /**
     * Creates scripting area for moving and rotating the object
     *
     */
    protected function _getScript() {
        $p_count = count($this->_polygones);

        // Create entire scripting area for moving and rotating the polygones
        $script = <<<EOF
        <!-- ECMAScript for moving and rotating -->
        <script type="text/ecmascript"> <![CDATA[

            var svgdoc;
            var plist, pcont, t1;

            var moved_x, moved_y, moved_z;

            var width, height;
            var viewpoint, distance;

            // Some kind of constructor
            function init(evt) {
                // Set image dimension
                width = {$this->_x};
                height = {$this->_y};

                // Set viewpoint and distance for perspective
                viewpoint = (width + height) / 2;
                distance = (width + height) / 2;

                // Set path to 0
                mov_x = 0;
                mov_y = 0;
                mov_z = 0;
                rot_z = 0;

                // Reference the SVG-Document
                svgdoc = evt.target.ownerDocument;

                // Reference list of Image_3D_Polygons
                pcont = svgdoc.getElementById('plist');

                // Reference container for polygones
                pcont = svgdoc.getElementById('pcont');

                drawAllPolygones();
            }

            // Draw all polygones
            function drawAllPolygones() {
                var i, j;
                var cont, pcont;
                var p, style, x0, x1, x2, y0, y1, y2, z0, z1, z2;
                var xs, ys;

                // Remove all current polygons by deleting container
                cont = svgdoc.getElementById('cont');
                pcont = svgdoc.getElementById('pcont');
                cont.removeChild(pcont);

                pcont = svgdoc.createElement('g');
                pcont.setAttribute('id', 'pcont');
                cont.appendChild(pcont);

                // Find all polygones
                for (i=1; i<={$p_count}; ++i) {
                    p = svgdoc.getElementById("polygon"+i);

                    // find three points per polygon
                    style = p.getAttribute("style");
                    x0 = parseInt(p.getAttribute("x0"));
                    y0 = parseInt(p.getAttribute("y0"));
                    z0 = parseInt(p.getAttribute("z0"));
                    x1 = parseInt(p.getAttribute("x1"));
                    y1 = parseInt(p.getAttribute("y1"));
                    z1 = parseInt(p.getAttribute("z1"));
                    x2 = parseInt(p.getAttribute("x2"));
                    y2 = parseInt(p.getAttribute("y2"));
                    z2 = parseInt(p.getAttribute("z2"));

                    // Calculate coordinates on screen (perspectively - viewpoint, distance: 500)
                    // 1st point
                    xs0 = Math.round(viewpoint * (x0 + mov_x) / (z0 + distance) + {$this->_x}) / 2;
                    ys0 = Math.round(viewpoint * (y0 + mov_y) / (z0 + distance) + {$this->_y}) / 2;
                    // 2nd point
                    xs1 = Math.round(viewpoint * (x1 + mov_x) / (z1 + distance) + {$this->_x}) / 2;
                    ys1 = Math.round(viewpoint * (y1 + mov_y) / (z1 + distance) + {$this->_y}) / 2;
                    // 3rd point
                    xs2 = Math.round(viewpoint * (x2 + mov_x) / (z2 + distance) + {$this->_x}) / 2;
                    ys2 = Math.round(viewpoint * (y2 + mov_y) / (z2 + distance) + {$this->_y}) / 2;

                    // Draw the polygon
                    p = svgdoc.createElement('polygon');
                    p.setAttribute("style", style);
                    p.setAttribute("points", xs0+','+ys0+' '+xs1+','+ys1+' '+xs2+','+ys2);
                    pcont.appendChild(p);
                }
            }

            // Move the object to the left
            function move_left(steps) {
                if (steps>0) {
                    mov_x -= 3;
                    drawAllPolygones();
                    setTimeout('move_left('+(steps-1)+')', 1);
                }
            }

            // Move the object to the left
            function move_up(steps) {
                if (steps>0) {
                    mov_y -= 3;
                    drawAllPolygones();
                    setTimeout('move_up('+(steps-1)+')', 1);
                }
            }

            // Move the object to the left
            function move_right(steps) {
                if (steps>0) {
                    mov_x += 3;
                    drawAllPolygones();
                    setTimeout('move_right('+(steps-1)+')', 1);
                }
            }

            // Move the object to the left
            function move_down(steps) {
                if (steps>0) {
                    mov_y += 3;
                    drawAllPolygones();
                    setTimeout('move_down('+(steps-1)+')', 1);
                }
            }

            // Zoom in (decrease the distance)
            function zoom_in() {
                distance -= 24;
                viewpoint += 8;
                drawAllPolygones();
            }

            // Zoom out (decrease the distance)
            function zoom_out() {
                distance += 24;
                viewpoint -= 8;
                drawAllPolygones();
            }

            // Back to default position
            function move_to_default() {
                // Move to default Position
                mov_x = 0;
                mov_y = 0;
                mov_z = 0;

                // Zoom to default position
                viewpoint = (width + height) / 2;
                distance = (width + height) / 2;

                // Draw
                drawAllPolygones();
            }

            // In- or decrease one of the three coordinates
            function moveAllPolygones(coord, step) {
                var p, c;

                // Find all polygones
                for (i=1; i<={$p_count}; ++i) {
                    p = svgdoc.getElementById('polygon'+i);

                    switch (coord) {
                        case 0  :   // X
                                    var c = parseInt(p.getAttribute('x0'));
                                    p.setAttribute('x0', (c+step));
                                    var c = parseInt(p.getAttribute('x1'));
                                    p.setAttribute('x1', (c+step));
                                    var c = parseInt(p.getAttribute('x2'));
                                    p.setAttribute('x2', (c+step));
                                 break;
                        case 1  :   // Y
                                    var c = parseInt(p.getAttribute('y0'));
                                    p.setAttribute('y0', (c+step));
                                    var c = parseInt(p.getAttribute('y1'));
                                    p.setAttribute('y1', (c+step));
                                    var c = parseInt(p.getAttribute('y2'));
                                    p.setAttribute('y2', (c+step));
                                 break;
                        case 2  :   // Z
                                    var c = parseInt(p.getAttribute('z0'));
                                    p.setAttribute('z0', (c+step));
                                    var c = parseInt(p.getAttribute('z1'));
                                    p.setAttribute('z1', (c+step));
                                    var c = parseInt(p.getAttribute('z2'));
                                    p.setAttribute('z2', (c+step));
                                 break;
                    }
                }
            }

        ]]> </script>

EOF;

        return $script;
    }

    /**
     * Creates controls for moving and rotating the object
     *
     */
    protected function _getControls() {

        function drawArrow($x, $y, $id, $rot, $funct) {
            $arrow_points=($x+12).','.($y+3).' '.($x+3).','.($y+8).' '.($x+12).','.($y+13);

            $arrow = "\t<g id=\"".$id.'" transform="rotate('.$rot.', '.($x+8).', '.($y+8)
                    .')" onclick="'.$funct."\">\n";
            $arrow .= "\t\t<rect x=\"".$x.'" y="'.$y.'" width="16" height="16" '
                        ." style=\"fill:#bbb; stroke:none;\" />\n";
            $arrow .= "\t\t<rect x=\"".($x+1).'" y="'.($y+1).'" width="14" height="14" '
                        ." style=\"fill:#ddd; stroke:none;\" />\n";
            $arrow .= "\t\t<polygon points=\"".$arrow_points.'" '
                        ." style=\"fill:#000; stroke:none;\" />\n";
            $arrow .= "\t</g>\n";

            return $arrow;
        }

        $controls = "\n\t<!-- Control Elements -->\n";

        // Move left
        $x = 0;
        $y = $this->_y / 2 - 8;
        $controls .= drawArrow($x, $y, 'move_left', 0, 'move_left(15)');

        // Move up
        $x = $this->_x / 2 - 8;
        $y = 0;
        $controls .= drawArrow($x, $y, 'move_up', 90, 'move_up(15)');

        // Move right
        $x = $this->_x - 16;
        $y = $this->_y / 2 - 8;
        $controls .= drawArrow($x, $y, 'move_right', 180, 'move_right(15)');

        // Move down
        $x = $this->_x / 2 - 8;
        $y = $this->_y - 16;
        $controls .= drawArrow($x, $y, 'move_down', -90, 'move_down(15)');

        // Zoom in
        $x = $this->_x * 0.12 - 10;
        $y = $this->_y * 0.88 - 10;
        $controls .= "\t<g id=\"zoom_in\" onclick=\"zoom_in()\">\n";
        $controls .= "\t\t<rect x=\"".$x.'" y="'.$y."\" width=\"20\" height=\"20\" style=\"fill:#bbb; stroke:none;\" />\n";
        $controls .= "\t\t<rect x=\"".++$x.'" y="'.++$y."\" width=\"18\" height=\"18\" style=\"fill:#ddd; stroke:none;\" />\n";
        $controls .= "\t\t<text x=\"".++$x.'" y="'.($y+17)."\" "
                ."style=\"font-size:20pt; font-family:arial, verdana, helvetica, sans-serif;\">+</text>\n";
        $controls .= "\t\t<rect x=\"".($x-2).'" y="'.$y."\" width=\"20\" height=\"20\" style=\"opacity: 0;\" />\n";
        $controls .= "\t</g>\n";

        // Zoom out
        $x = $this->_x * 0.88 - 10;
        $y = $this->_y * 0.88 - 10;
        $controls .= "\t<g id=\"zoom_out\" onclick=\"zoom_out()\">\n";
        $controls .= "\t\t<rect x=\"".$x.'" y="'.$y."\" width=\"20\" height=\"20\" style=\"fill:#bbb; stroke:none;\" />\n";
        $controls .= "\t\t<rect x=\"".++$x.'" y="'.++$y."\" width=\"18\" height=\"18\" style=\"fill:#ddd; stroke:none;\" />\n";
        $controls .= "\t\t<text x=\"".++$x.'" y="'.($y+15)."\" "
                ."style=\"font-size:20pt; font-family:arial, verdana, helvetica, sans-serif;\">-</text>\n";
        $controls .= "\t\t<rect x=\"".($x-2).'" y="'.$y."\" width=\"20\" height=\"20\" style=\"opacity: 0;\" />\n";
        $controls .= "\t</g>\n";

        // "1:1" - Move to default
        $x = $this->_x * 0.1 - 10;
        $y = $this->_y * 0.1 - 10;
        $controls .= "\t<g id=\"move_to_default\" onclick=\"move_to_default()\">\n";
        $controls .= "\t\t<rect x=\"".$x.'" y="'.$y."\" width=\"25\" height=\"25\" style=\"fill:#bbb; stroke:none;\" />\n";
        $controls .= "\t\t<rect x=\"".++$x.'" y="'.++$y."\" width=\"23\" height=\"23\" style=\"fill:#ddd; stroke:none;\" />\n";
        $controls .= "\t\t<text x=\"".++$x.'" y="'.($y+16)."\" "
                ."style=\"font-size:10pt; font-family:arial, verdana, helvetica, sans-serif;\">1:1</text>\n";
        $controls .= "\t\t<rect x=\"".($x-2).'" y="'.$y."\" width=\"25\" height=\"25\" style=\"opacity: 0;\" />\n";
        $controls .= "\t</g>\n";

        return $controls;
    }

    public function save($file) {
        // Start of SVG definition area
        $this->_image .= sprintf("\t<defs id=\"defs%d\">\n", $this->_id++);

        // Add scripting for moving and rotating
        $this->_image .= $this->_getScript();

        // Add gradients in case of GAUROUD-shading
        if (count($this->_gradients)>0) {
            $this->_image .= implode('', $this->_gradients);
        }

        // Add all polygones
        $this->_image .= "\n\t\t<!-- polygon data elements -->\n\t\t<g id=\"plist\">\n";
        $this->_image .= implode('', $this->_polygones);
        $this->_image .= "\t\t</g>\n";

        // End of SVG definition area
        $this->_image .= sprintf("\t</defs>\n\n");

        // Draw background
        $this->_image .= $this->_background;

        // Create container for drawn polygones
        $this->_image .= "\n\t<!-- Container for drawn polygones-->\n\t<g id=\"cont\">\n\t\t<g id=\"pcont\">\n\t\t</g>\n\t</g>\n";

        // Add controls for moving and rotating
        $this->_image .= $this->_getControls();

        $this->_image .= "</svg>\n";
        file_put_contents($file, $this->_image);
    }

    public function getSupportedShading() {
        return array(Image_3D_Renderer::SHADE_NO, Image_3D_Renderer::SHADE_FLAT);
    }
}

?>
