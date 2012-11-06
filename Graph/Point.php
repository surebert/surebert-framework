<?php

/**
 * Used to plot simple point and line graphs.  Requires \sb\Math_RangeMapper
 * @author paul.visco@roswellpark.org
 * @package Graph
 * 
 */
namespace sb\Graph;

class Point
{

    /**
     * Determines if plotted points are connected by a line
     *
     * @var boolean
     */
    public $connect_points = 1;

    /**
     * Determines if the hinted y axis is shown
     *
     * @var boolean
     */
    public $y_axis_hints = 1;

    /**
     * Determines if the hinted x axis is shown
     *
     * @var boolean
     */
    public $x_axis_hints = 1;

    /**
     * The offset for each axis labels
     *
     * @var integer
     */
    public $axis_offset = 30;

    /**
     * The number of decimal places to use for r\rounding
     * @var integer
     */
    public $precision = 2;

    /**
     * The image resource that is being drawn
     *
     * @var GD resource
     */
    public $im;

    /**
     * The values derived from the from last argument of the constructor
     *
     * @var Array
     */
    private $values = Array();

    /**
     * Create the blank graph image
     *
     * @param integer $width  The total width of the graph in pixels
     * @param integer $height The total height of the graph in pixels
     * @param string $values A line resturn delimted, comma-delimited
     *  value pair decribing the label and value for each point plotted. 
     * <code>
     * //set the graph width and height plus values and labels
     * //set the graph width and height plus values and labels
     * $chart = new \sb\Graph\Point(600, 300,  Array(
     *     'A' => 1.27,
     *     'B' => 1.45,
     *     'C' => 1.20,
     *     'D' => 1.55,
     *     'E' => null, //graphs nothing for that column but still adds the column
     *     'F' => 2.55,
     *    'G' => 1.45,
     *    'H' => 1.35,
     *    'I' => 1.33,
     *     'J' => 0.98
     * ));
     *
     * //these all have defaults, optional
     * $chart->setYaxisLabelIncrement(0.5);
     * $chart->connect_points = 1;
     * $chart->x_axis_hints = 1;
     * $chart->y_axis_hints = 1;
     *
     * //setting the colors, optional
     * $chart->setBackgr\roundColor(25, 45, 65);
     * $chart->setTextColor(255, 255, 255);
     * $chart->setAxisColor(95, 95, 95);
     * $chart->setPointColor(223, 65, 15);
     * $chart->setLineColor(145, 45, 45);
     *
     * //add additional horizontal lines, optional
     * $chart->addHorizontalLine(1.54, 'red', 'average');
     * $chart->addHorizontalLine(2.0, 'purple', 'otherLine');
     * //draw the chart
     * $chart->draw();
     * header("Content-type: image/gif");
     * // return the image using imagepng or imagejpeg.
     * \imagegif($chart->output());
     * </code>
     */
    public function __construct($width, $height, $values)
    {

        $this->width = $width;
        $this->height = $height;

        $this->graph_width = $width - $this->axis_offset;
        $this->graph_height = $height - $this->axis_offset;

        $this->im = imagecreate($this->width, $this->height);
        $this->allocateColors();
        $this->setValues($values);
        $this->createBackground();
    }

    /**
     * Sets the spacing increment inteval for the y axis legend
     *
     * @param float $increment
     */
    public function setYaxisLabelIncrement($increment = '')
    {

        if (is_numeric($increment)) {
            $this->y_axis_legend_increment = $increment;
        }
    }

    /**
     * Sets the point color in rgb format
     *
     * @param integer $r The red value 0-255
     * @param integer $g The green value 0-255
     * @param integer $b The blue value 0-255
     */
    public function setPointColor($r, $g, $b)
    {

        $this->ink['point'] = imagecolorallocate($this->im, $r, $g, $b);
    }

    /**
     * Sets the line color in rgb format
     *
     * @param integer $r The red value 0-255
     * @param integer $g The green value 0-255
     * @param integer $b The blue value 0-255
     */
    public function setLineColor($r, $g, $b)
    {

        $this->ink['line'] = imagecolorallocate($this->im, $r, $g, $b);
    }

    /**
     * Sets the text color in rgb format - labels
     *
     * @param integer $r The red value 0-255
     * @param integer $g The green value 0-255
     * @param integer $b The blue value 0-255
     */
    public function setTextColor($r, $g, $b)
    {
        $this->ink['text'] = imagecolorallocate($this->im, $r, $g, $b);
    }

    /**
     * Sets the axis color in rgb format - axis
     *
     * @param integer $r The red value 0-255
     * @param integer $g The green value 0-255
     * @param integer $b The blue value 0-255
     */
    public function setAxisColor($r, $g, $b)
    {
        $this->ink['axis'] = imagecolorallocate($this->im, $r, $g, $b);
    }

    public function setBackgroundColor($r, $g, $b)
    {

        $this->ink['background'] = imagecolorallocate($this->im, $r, $g, $b);
        imagefilledrectangle($this->im, 0, 0, $this->width, $this->height,
            $this->ink['background']);
    }

    /**
     * Create the backgrround image
     *
     */
    private function createBackground()
    {

        imagefilledrectangle($this->im, 0, 0, $this->width, $this->height,
            $this->ink['black']);
    }

    /**
     * Allocates the default color used
     *
     */
    private function allocateColors()
    {

        $this->ink['red'] = imagecolorallocate($this->im, 0xff, 0x00, 0x00);
        $this->ink['orange'] = imagecolorallocate($this->im, 0xd2, 0x8a, 0x00);
        $this->ink['yellow'] = imagecolorallocate($this->im, 0xff, 0xff, 0x00);
        $this->ink['green'] = imagecolorallocate($this->im, 0x00, 0xff, 0x00);
        $this->ink['blue'] = imagecolorallocate($this->im, 0x00, 0x00, 0xff);

        $this->ink['purple'] = imagecolorallocate($this->im, 0x70, 0x70, 0xf9);
        $this->ink['white'] = imagecolorallocate($this->im, 0xff, 0xff, 0xff);
        $this->ink['black'] = imagecolorallocate($this->im, 0x00, 0x00, 0x00);
        $this->ink['gray'] = imagecolorallocate($this->im, 0xaf, 0xaf, 0xaf);

        $this->ink['axis'] = imagecolorallocate($this->im, 95, 95, 95);

        $this->ink['line'] = imagecolorallocate($this->im, 0xff, 0xff, 0x00);
        $this->ink['background'] = imagecolorallocate($this->im, 0x00, 0x00,
            0x00);
        $this->ink['text'] = imagecolorallocate($this->im, 0xff, 0xff, 0xff);
        $this->ink['point'] = imagecolorallocate($this->im, 0xff, 0xff, 0xff);
    }

    /**
     * Converts the range from point to pixel value
     *
     * @param integer $value The value to convert
     * @return integer The number as converted into the pixel range on the graph
     */
    private function mapYvalue($value)
    {

        $rangeMapper = new \sb\Math\RangeMapper(Array(30, $this->graph_height), Array($this->min, $this->max));
        return $rangeMapper->convert($value);
    }

    /**
     * Converts the values into usable data for the drawing of the graph
     *
     * @param array $values
     */
    private function setValues($values)
    {

        $numbers = Array();
        foreach ($values as $key => $val) {

            $value = new \stdClass();
            $value->label = trim($key);

            if (!is_numeric($val)) {
                $val = null;
            }

            $value->value = $val;
            $this->values[] = $value;
            $numbers[] = $val;
        }
        $min_max = Array();
        foreach ($numbers as $number) {
            if (!is_null($number)) {
                array_push($min_max, $number);
            }
        }
        $this->min = min($min_max);
        $this->max = max($min_max);

        $this->total_values = count($numbers);

        $separation_dist = (($this->graph_width - 40) / $this->total_values);
        $i = 0;

        $this->points = Array();

        foreach ($this->values as $value) {
            $point = new \stdClass();
            $point->x = ($i * $separation_dist) + $this->axis_offset + 40;
            $point->y = $this->plotValue($value->value);
            $point->label = $value->label;
            $point->value = $value->value;
            $this->points[] = $point;
            $i++;
        }

        return $this->points;
    }

    /**
     * Draws the y axis on the graph at each point in a dashed line 
     * fashion.  This is totally optional and only happens if 
     * $this->draw_y_axis ==1
     *
     */
    private function drawYaxis()
    {

        $min =
            round($this->min, $this->precision);
        $max =
            round($this->max, $this->precision);

        if (!isset($this->y_axis_legend_increment)) {

            $increment =
                round(($max - $min) / $this->total_values, $this->precision);
        } else {
            $increment = $this->y_axis_legend_increment;
        }

        if ($increment == 0) {
            $increment = 1;
        }

        //$spacing = \round($spacing, 10);
        for ($label = $min; $label <= $max + $increment; $label+=$increment) {

            $px_position = $this->plotValue($label);
            if ($this->x_axis_hints == 1) {
                \imageline($this->im, 0, $px_position, $this->width,
                    $px_position, $this->ink['axis']);
            }
            imagestring($this->im, 1, 10, $px_position - 4, $label,
                $this->ink['text']);
        }
    }

    /**
     * Converts points on the graph to pixels
     *
     * @param integer $y
     * @return integer The value in pixels
     */
    private function plotValue($y)
    {
        $rangeMapper = new \sb\Math\RangeMapper(Array(
            $this->axis_offset,
            $this->graph_height - $this->axis_offset
        ), Array($this->max, $this->min));
        return $rangeMapper->convert($y);
    }

    /**
     * Connect the points on a graph
     *
     */
    private function connectPoints()
    {
        foreach ($this->points as $point) {

            if (is_null($point->value)) {
                $last_x = $point->x;
                $last_y = $point->y;
                $last_val = $point->value;
                continue;
            }

            if (isset($last_x) && (isset($last_val) && !is_null($last_val))) {
                imageline($this->im, $last_x, $last_y, $point->x, $point->y,
                    $this->ink['line']);
            }
            $last_val = $point->value;
            $last_x = $point->x;
            $last_y = $point->y;
        }
    }

    /**
     * Draw the basic graph and plot the points
     *
     */
    public function draw()
    {

        $this->drawYaxis();

        if ($this->connect_points == 1) {
            $this->connectPoints();
        }

        foreach ($this->points as $point) {

            if ($this->y_axis_hints == 1) {
                imagedashedline($this->im, $point->x, $this->height, $point->x,
                    0, $this->ink['axis']);
            } else {

                //add axis line
                imageline($this->im, $point->x, $this->graph_height, $point->x,
                    $this->graph_height + 10, $this->ink['axis']);
            }

            //add axis label
            imagestring($this->im, 1, $point->x + 5, $this->graph_height + 10,
                $point->label, $this->ink['text']);

            //don't plot actual point if it is null
            if (is_null($point->value)) {
                continue;
            }

            //plot point
            imagefilledellipse($this->im, $point->x, $point->y, 7, 7,
                $this->ink['point']);

            //add point label
            if ($point->y <= 5) {
                $posy = $point->y + 5;
            } elseif ($point->y >= $this->graph_height - 5) {
                $posy = $point->y - 20;
            } else {
                $posy = $point->y - 15;
            }

            imagestring($this->im, 3, $point->x + 10, $posy, $point->value,
                $this->ink['point']);
        }
    }

    /**
     * Add a horizontal line
     *
     * @param integer $y The y value
     * @param string $color red, orange, yellow, green, blue, purple
     * @param string $label The line label
     */
    public function addHorizontalLine($y, $color = 'red', $label = '')
    {

        if (!array_key_exists($color, $this->ink)) {
            throw(new \Exception("Ink color must be in " . implode(",",
                    \array_keys($this->ink))));
        }

        $y = $this->plotValue($y);
        imageline($this->im, 0, $y, $this->width, $y, $this->ink[$color]);

        imagestring($this->im, 2, $this->graph_width / 2 + $this->axis_offset,
            $y, $label, $this->ink[$color]);
    }

    /**
     * Output the graph image resouce for use with imagegif, imagepng, imagejpg
     *
     * @return unknown
     */
    public function output()
    {
        return $this->im;
    }
}

