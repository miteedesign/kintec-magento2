<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Changes\Block\Swatches\Product\Renderer\Listing;

/**
 * Swatch renderer block in Category page
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    public function getListSwatchJs($force=false){
        /*
        $dir = BP.'/var'.'/cache'.'/swatches'.'/list/';
        $path = $dir.$this->getProduct()->getId();
       
        if(file_exists($path) && $force==false){
            $swatchJs = file_get_contents($path);
        }
        else
        {
            if (!file_exists($dir)) {
               mkdir($dir, 0777, true);
            }
            if(file_exists($path))
                unlink($path);
            $swatchJs = '    $(\'.swatch-opt-'.$this->getProduct()->getId().'\').SwatchRenderer({
            selectorProduct: \'.product-item-details\',
            onlySwatches: true,
            enableControlLabel: false,
            numberToShow: '.$this->getNumberSwatchesPerProduct().',
            jsonConfig: '.$this->getJsonConfig().',
            jsonSwatchConfig: '.$this->getJsonSwatchConfig().',
            mediaCallback: \''.$this->getMediaCallback() .'\'
        }); ';
            file_put_contents($path, $swatchJs);
        }
        */
        $swatchJs = '    $(\'.swatch-opt-'.$this->getProduct()->getId().'\').SwatchRenderer({
            selectorProduct: \'.product-item-details\',
            onlySwatches: true,
            enableControlLabel: false,
            numberToShow: '.$this->getNumberSwatchesPerProduct().',
            jsonConfig: '.$this->getJsonConfig().',
            jsonSwatchConfig: '.$this->getJsonSwatchConfig().',
            mediaCallback: \''.$this->getMediaCallback() .'\'
        }); ';
        return $swatchJs;
    }
    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->_template;
    }

    /**
     * Produce and return block's html output.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $output = '';
        if ($this->isProductHasSwatchAttribute()) {
            $output = parent::_toHtml();
        }

        return $output;
    }

    /**
     * @deprecated
     * @return string
     */
    protected function getHtmlOutput()
    {
        $output = '';
        if ($this->isProductHasSwatchAttribute()) {
            $output = parent::getHtmlOutput();
        }

        return $output;
    }

    /**
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        $result = [];
        $swatchAttributeData = parent::getSwatchAttributesData();
        foreach ($swatchAttributeData as $attributeId => $item) {
            if (!empty($item['used_in_product_listing'])) {
                $result[$attributeId] = $item;
            }
        }
        return $result;
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $this->unsetData('allow_products');
        return parent::getJsonConfig();
    }
}
