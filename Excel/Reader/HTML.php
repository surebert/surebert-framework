<?php

/**
 * An Excel Spreadhseet (97/2003) Reading package based on http://code.google.com/p/php-excel-reader/
 *
 * Extends Excel_Reader to do excel to html mapping with css
 * @package Excel
 */
namespace sb\Excel;

class Excel_Reader_HTML extends Excel_Reader 
    {

    /**
     * Converts excel file cell style info into css
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function get_css_style($row, $col, $sheet=0) 
    {
        $css = "";
        $font = $this->font($row, $col, $sheet);
        if ($font != "") {
            $css .= "font-family:$font;";
        }
        $align = $this->align($row, $col, $sheet);
        if ($align != "") {
            $css .= "text-align:$align;";
        }
        $height = $this->height($row, $col, $sheet);
        if ($height != "") {
            $css .= "font-size:$height" . "pt;";
        }
        $bgcolor = $this->bgColor($row, $col, $sheet);
        if ($bgcolor != "") {
            $bgcolor = $this->colors[$bgcolor];
            $css .= "background-color:$bgcolor;";
        }
        $color = $this->color($row, $col, $sheet);
        if ($color != "") {
            $css .= "color:$color;";
        }
        $bold = $this->bold($row, $col, $sheet);
        if ($bold) {
            $css .= "font-weight:bold;";
        }
        $italic = $this->italic($row, $col, $sheet);
        if ($italic) {
            $css .= "font-style:italic;";
        }
        $underline = $this->underline($row, $col, $sheet);
        if ($underline) {
            $css .= "text-decoration:underline;";
        }
        // Borders
        $bLeft = $this->borderLeft($row, $col, $sheet);
        $bRight = $this->borderRight($row, $col, $sheet);
        $bTop = $this->borderTop($row, $col, $sheet);
        $bBottom = $this->borderBottom($row, $col, $sheet);
        $bLeftCol = $this->borderLeftColor($row, $col, $sheet);
        $bRightCol = $this->borderRightColor($row, $col, $sheet);
        $bTopCol = $this->borderTopColor($row, $col, $sheet);
        $bBottomCol = $this->borderBottomColor($row, $col, $sheet);
        // Try to output the minimal required style
        if ($bLeft != "" && $bLeft == $bRight && $bRight == $bTop && $bTop == $bBottom) {
            $css .= "border:" . $this->lineStylesCss[$bLeft] . ";";
        } else {
            if ($bLeft != "") {
                $css .= "border-left:" . $this->lineStylesCss[$bLeft] . ";";
            }
            if ($bRight != "") {
                $css .= "border-right:" . $this->lineStylesCss[$bRight] . ";";
            }
            if ($bTop != "") {
                $css .= "border-top:" . $this->lineStylesCss[$bTop] . ";";
            }
            if ($bBottom != "") {
                $css .= "border-bottom:" . $this->lineStylesCss[$bBottom] . ";";
            }
        }
        // Only output border colors if there is an actual border specified
        if ($bLeft != "" && $bLeftCol != "") {
            $css .= "border-left-color:" . $bLeftCol . ";";
        }
        if ($bRight != "" && $bRightCol != "") {
            $css .= "border-right-color:" . $bRightCol . ";";
        }
        if ($bTop != "" && $bTopCol != "") {
            $css .= "border-top-color:" . $bTopCol . ";";
        }
        if ($bBottom != "" && $bBottomCol != "") {
            $css .= "border-bottom-color:" . $bBottomCol . ";";
        }

        return $css;
    }

    /**
     * get hyperlink if cell is hyperlinked
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function hyperlink($row, $col, $sheet=0) 
    {
        $link = isset($this->sheets[$sheet]['cellsInfo'][$row][$col]['hyperlink']) ? $this->sheets[$sheet]['cellsInfo'][$row][$col]['hyperlink'] : false;
        if ($link) {
            return $link['link'];
        }
        return '';
    }

    /**
     * get cell format
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function format($row, $col, $sheet=0) 
    {
        return $this->info($row, $col, 'format', $sheet);
    }

    public function formatIndex($row, $col, $sheet=0) 
    {
        return $this->info($row, $col, 'formatIndex', $sheet);
    }

    public function formatColor($row, $col, $sheet=0) 
    {
        return $this->info($row, $col, 'formatColor', $sheet);
    }

    public function xfRecord($row, $col, $sheet=0) 
    {
        $xfIndex = $this->info($row, $col, 'xfIndex', $sheet);
        if ($xfIndex != "") {
            return $this->xfRecords[$xfIndex];
        }
        return null;
    }

    public function xfProperty($row, $col, $sheet, $prop) 
    {
        $xfRecord = $this->xfRecord($row, $col, $sheet);
        if ($xfRecord != null) {
            return $xfRecord[$prop];
        }
        return "";
    }

    public function align($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'align');
    }

    /**
     * get cell bgColor
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function bgColor($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'bgColor');
    }

    public function borderLeft($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'borderLeft');
    }

    public function borderRight($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'borderRight');
    }

    public function borderTop($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'borderTop');
    }

    public function borderBottom($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'borderBottom');
    }

    public function borderLeftColor($row, $col, $sheet=0) 
    {
        $c = $this->xfProperty($row, $col, $sheet, 'borderLeftColor');
        return isset($this->colors[$c]) ? $this->colors[$c] : false;
    }

    public function borderRightColor($row, $col, $sheet=0) 
    {
        $c = $this->xfProperty($row, $col, $sheet, 'borderRightColor');
        return isset($this->colors[$c]) ? $this->colors[$c] : false;
    }

    public function borderTopColor($row, $col, $sheet=0) 
    {
        $c = $this->xfProperty($row, $col, $sheet, 'borderTopColor');
        return isset($this->colors[$c]) ? $this->colors[$c] : false;
    }

    public function borderBottomColor($row, $col, $sheet=0) 
    {
        $c = $this->xfProperty($row, $col, $sheet, 'borderBottomColor');
        return isset($this->colors[$c]) ? $this->colors[$c] : false;
    }

    public function fontRecord($row, $col, $sheet=0) 
    {
        $xfRecord = $this->xfRecord($row, $col, $sheet);
        if ($xfRecord != null) {
            $font = $xfRecord['fontIndex'];
            if ($font != null) {
                return $this->fontRecords[$font];
            }
        }
        return null;
    }

    public function fontProperty($row, $col, $sheet=0, $prop) 
    {
        $font = $this->fontRecord($row, $col, $sheet);
        if ($font != null) {
            return $font[$prop];
        }
        return false;
    }

    public function fontIndex($row, $col, $sheet=0) 
    {
        return $this->xfProperty($row, $col, $sheet, 'fontIndex');
    }

    public function color($row, $col, $sheet=0) 
    {
        $formatColor = $this->formatColor($row, $col, $sheet);
        if ($formatColor != "") {
            return $formatColor;
        }
        $ci = $this->fontProperty($row, $col, $sheet, 'color');
        return $this->rawColor($ci);
    }

    public function rawColor($ci) 
    {
        if (($ci <> 0x7FFF) && ($ci <> '')) {
            return $this->colors[$ci];
        }
    }

    public function bold($row, $col, $sheet=0) 
    {
        return $this->fontProperty($row, $col, $sheet, 'bold');
    }

    public function italic($row, $col, $sheet=0) 
    {
        return $this->fontProperty($row, $col, $sheet, 'italic');
    }

    /**
     * Gets the underline property of a cell
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function underline($row, $col, $sheet=0) 
    {
        return $this->fontProperty($row, $col, $sheet, 'under');
    }

    /**
     * Gets the height property of a cell
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function height($row, $col, $sheet=0) 
    {
        return $this->fontProperty($row, $col, $sheet, 'height');
    }

    /**
     * Gets the font property of a cell
     * @param int $row
     * @param int $col
     * @param int $sheet
     * @return string
     */
    public function font($row, $col, $sheet=0) 
    {
        return $this->fontProperty($row, $col, $sheet, 'font');
    }

    /**
     * Dumps an HTML file representing xls data, removed htmlentities to allow for UTF chars
     * @param boolean $row_numbers Should row numbers show
     * @param boolean $col_letters Should col letters show
     * @param integer $sheet Which sheet to dump
     * @param string $table_class Which class to give the table
     * @return string
     */
    public function to_html($row_numbers=false, $col_letters=false, $sheet=0, $table_class='excel') 
    {

        $out = "<table class=\"$table_class\" cellspacing=0>";
        if ($col_letters) {
            $out .= "<thead>\n\t<tr>";
            if ($row_numbers) {
                $out .= "\n\t\t<th>&nbsp</th>";
            }
            for ($i = 1; $i <= $this->colcount($sheet); $i++) {
                $style = "width:" . ($this->colwidth($i, $sheet) * 1) . "px;";
                if ($this->colhidden($i, $sheet)) {
                    $style .= "display:none;";
                }
                $out .= "\n\t\t<th style=\"$style\">" . strtoupper($this->colindexes[$i]) . "</th>";
            }
            $out .= "</tr></thead>\n";
        }

        $out .= "<tbody>\n";

        $row_count = $this->rowcount($sheet);
        for ($row = 1; $row <= $this->rowcount($sheet); $row++) {

            $rowheight = $this->rowheight($row, $sheet);
            $style = "height:" . ($rowheight * (4 / 3)) . "px;";
            if ($this->rowhidden($row, $sheet)) {
                $style .= "display:none;";
            }
            $out .= "\n\t<tr style=\"$style\">";
            if ($row_numbers) {
                $out .= "\n\t\t<th>$row</th>";
            }
            $colcount = $this->colcount($sheet);
            for ($col = 1; $col <= $colcount; $col++) {
                // Account for Rowspans/Colspans
                $rowspan = $this->rowspan($row, $col, $sheet);
                $colspan = $this->colspan($row, $col, $sheet);
                for ($i = 0; $i < $rowspan; $i++) {
                    for ($j = 0; $j < $colspan; $j++) {
                        if ($i > 0 || $j > 0) {
                            $this->sheets[$sheet]['cellsInfo'][$row + $i][$col + $j]['dontprint'] = 1;
                        }
                    }
                }
                if (!isset($this->sheets[$sheet]['cellsInfo'][$row][$col]['dontprint']) || !$this->sheets[$sheet]['cellsInfo'][$row][$col]['dontprint']) {
                    $style = $this->get_css_style($row, $col, $sheet);
                    if ($this->colhidden($col, $sheet)) {
                        $style .= "display:none;";
                    }
                    $out .= "\n\t\t<td style=\"$style\"" . ($colspan > 1 ? " colspan=$colspan" : "") . ($rowspan > 1 ? " rowspan=$rowspan" : "") . ">";
                    $val = $this->val($row, $col, $sheet);
                    if ($val == '') {
                        $val = "&nbsp;";
                    } else {
                        $val = htmlentities($val, ENT_NOQUOTES, 'utf-8');
                        $link = $this->hyperlink($row, $col, $sheet);
                        if ($link != '') {
                            $val = "<a href=\"$link\">$val</a>";
                        }
                    }
                    $out .= "<nobr>" . nl2br($val) . "</nobr>";
                    $out .= "</td>";
                }
            }
            $out .= "</tr>\n";
        }
        $out .= "</tbody></table>";
        return $out;
    }

}

