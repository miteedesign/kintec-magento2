<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Model\Config\Source;

class Fonts implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * supply dropdown choices for fonts
     * generated from contents of lib/tcpdf/fonts directory
     *
     * @return array
     */
    public function toOptionArray()
    {
        $preLoadedFonts = [
            'courier'        => __('Courier'),
            'times'          => __('Times New Roman'),
            'helvetica'      => __('Helvetica'),
            'dejavusans'     => __('DejaVuSans'),
            'dejavusansmono' => __('DejaVuSansMono'),
            'dejavuserif'    => __('DejaVuSerif'),
            'cid0cs'         => __('System Font - Chinese Simplified'),
            'cid0ct'         => __('System Font - Chinese Traditional'),
            'cid0jp'         => __('System Font - Japanese'),
            'cid0kr'         => __('System Font - Korean')
        ];

        foreach ($preLoadedFonts as $fontname => $label) {
            $returnArray[] = ['value' => $fontname, 'label' => $label];
        }
        return $returnArray;
    }
}
