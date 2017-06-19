<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Model\Tcpdf;

class Pdf extends \TCPDF
{
    protected $cell_height_ratio = 1.25;

    protected $footerContent;

    protected $backgroundImage;

    /**
     * @param \Fooman\PdfCore\Helper\Page $pageHelper
     *
     */
    public function __construct(
        \Fooman\PdfCore\Helper\Page $pageHelper
    ) {
        parent::__construct(
            $pageHelper->getOrientation(),
            'mm',
            $pageHelper->getSize(),
            true,
            'UTF-8',
            false,
            false
        );
        $this->tcpdflink = false;
    }


    public function Header()
    {
        if ($this->backgroundImage) {
            $this->SetAutoPageBreak(false);
            $this->Image(
                $this->backgroundImage,
                0,
                0,
                $this->getPageWidth(),
                $this->getPageHeight(),
                $type = '',
                $link = '',
                $align = '',
                $resize = false,
                $dpi = 300,
                $palign = '',
                $ismask = false,
                $imgmask = false,
                $border = 0,
                $fitbox = true,
                $hidden = false
            );
            $this->SetAutoPageBreak(true, $this->getFooterMargin() + 10);
        }

        // Line break
        $this->Ln();
    }


    public function setBackgroundImage($image)
    {
        $this->backgroundImage = $image;
    }

    public function Footer()
    {
        $this->writeHTML($this->footerContent);
    }

    public function setFooterContent($html)
    {
        $this->footerContent = $html;
    }

    /**
     * override parent function to change default style
     *
     * @param        $code
     * @param        $type
     * @param string $x
     * @param string $y
     * @param string $w
     * @param string $h
     * @param float  $xres
     * @param array  $userStyle
     * @param string $align
     *
     * @internal param string $style
     */
    public function write1DBarcode(
        $code,
        $type,
        $x = '',
        $y = '',
        $w = '',
        $h = '',
        $xres = 0.4,
        $userStyle = [],
        $align = 'T'
    ) {
        $this->SetX($this->GetX() + 4);
        $defaultStyle = [
            'position'    => 'S',
            'border'      => false,
            'padding'     => 1,
            'fgcolor'     => [0, 0, 0],
            'bgcolor'     => false,
            'text'        => true,
            'font'        => 'helvetica',
            'fontsize'    => 8,
            'stretchtext' => 4
        ];
        $style = $userStyle + $defaultStyle;
        parent::write1DBarcode($code, $type, $x, $y, $w, $h, $xres, $style, $align);
    }
}
