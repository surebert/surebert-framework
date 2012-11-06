<?php

/**
 * Used to draw bar graphs
 * @author paul.visco@roswellpark.org
 * @package Graph
 *
 */
/*
  $chunkChart = new \sb\Graph\Bar3D(240, 130);
  $chunkChart->title ="comments on e:strip per 24 hrs";
  $chunkChart->padding =5;
  //$chunkChart->setColors("#BBC086", "#AEB55C", "#949D2F", "#FFFFFF", "#757E10");
  $chunkChart->setColors("#c08686", "#b55c5c", "#9d2f2f", "#FFFFFF", "#7e1010");

  $chunkChart->values =Array(8, 17, 5, 4, 9, 15);
  $chunkChart->draw();

  header("Content-type: image/gif");
  // return the image using imagepng or imagejpeg.
  imagegif($chunkChart->graph);
 */
namespace sb\Graph;

class Bar3D
{

    public $values = array();

    public $graph; //the graph image

    public $width = 200;

    public $height = 100;

    public $padding = 15;

    public $colors;

    public $title = "";

    public function __construct($width = 200, $height = 100, $padding = 1)
    {

        $this->width = $width;
        $this->height = $height;
        $this->padding = 1;
        $this->colors = new \stdClass();

        $this->graph = imagecreate($width, $height);
    }

    public function hexrgb($hexstr)
    {
        $int = hexdec($hexstr);

        return array("r" => 0xFF & ($int >> 0x10),
            "g" => 0xFF & ($int >> 0x8),
            "b" => 0xFF & $int);
    }

    public function setColors($front, $top, $right, $background, $text = "#000000")
    {


        $front = $this->hexrgb($front);
        $top = $this->hexrgb($top);
        $right = $this->hexrgb($right);
        $background = $this->hexrgb($background);
        $text = $this->hexrgb($text);

        $this->colors->front = imagecolorallocate($this->graph, $front['r'],
            $front['g'], $front['b']);
        $this->colors->top = imagecolorallocate($this->graph, $top['r'],
            $top['g'], $top['b']);
        $this->colors->right = imagecolorallocate($this->graph, $right['r'],
            $right['g'], $right['b']);
        $this->colors->background = imagecolorallocate($this->graph,
            $background['r'], $background['g'], $background['b']);

        $this->colors->text = imagecolorallocate($this->graph, $text['r'],
            $text['g'], $text['b']);
    }

    public function draw()
    {

        $columns = count($this->values);
        $column_width = floor($this->width / $columns) - 3;
        imagefilledrectangle($this->graph, 0, 0, $this->width, $this->height,
            $this->colors->background);

        // write the title at the top left
        imagestring($this->graph, 2, 0, 0, $this->title, $this->colors->text);

        // The first change - we need to reduce the maximum height
        // of the columns to allow for the vertical effect.
        $maxv = 0;
        $max_height = $this->height - $column_width;

        for ($i = 0; $i < $columns; $i++) {
            $maxv = max($this->values[$i], $maxv);
        }
        for ($i = 0; $i < $columns; $i++) {
            if ($this->values[$i] == 0) {
                $column_height = 0;
            } else {
                $column_height = ($max_height / 100) * (( $this->values[$i] / $maxv) * 100);
            }

            $x1 = $i * $column_width + 4;
            $y1 = $this->height - $column_height;
            $x2 = (($i + 1) * $column_width) - $this->padding;
            $y2 = $this->height;
            imagefilledrectangle($this->graph, $x1, $y1, $x2, $y2,
                $this->colors->front);

            // This is the offset for the 3D angle
            $offset = ($column_width - $this->padding) / 2;

            // Create an array for the top part of the column
            $pt = array($x1, $y1,
                $x1 + $offset, $y1 - $offset,
                $x2 + $offset, $y1 - $offset,
                $x2, $y1);

            // Now draw it.
            imagefilledpolygon($this->graph, $pt, 4, $this->colors->top);

            // Create the side of the column
            $pt = array($x2, $y1,
                $x2 + $offset, $y1 - $offset,
                $x2 + $offset, $y2 - $offset,
                $x2, $y2);

            // And draw that part too
            imagefilledpolygon($this->graph, $pt, 4, $this->colors->right);

            // Draw the value on
            imagestring($this->graph, 2, $x1 + 2, $y1 + 2, $this->values[$i],
                $this->colors->text);
        }
    }
}

