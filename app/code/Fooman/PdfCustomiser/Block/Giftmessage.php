<?php

namespace Fooman\PdfCustomiser\Block;

class Giftmessage extends \Fooman\PdfCore\Block\Pdf\Block
{
    protected $_template = 'Fooman_PdfCustomiser::pdf/giftmessage.phtml';

    /**
     * @return \Magento\GiftMessage\Api\Data\MessageInterface
     */
    public function getGiftmessage()
    {
        return $this->getData('giftmessage');
    }
}
