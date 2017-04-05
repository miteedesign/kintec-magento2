<?php

namespace Dotdigitalgroup\Email\Block\Adminhtml\Config\Dynamic;

class Couponcode extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_dataHelper;

    /**
     * Couponcode constructor.
     *
     * @param \Dotdigitalgroup\Email\Helper\Data $dataHelper
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Dotdigitalgroup\Email\Helper\Data $dataHelper,
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->_dataHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        //base url
        $baseUrl = $this->_dataHelper->generateDynamicUrl();
        //config code
        $passcode = $this->_dataHelper->getPasscode();

        if (!strlen($passcode)) {
            $passcode = '[PLEASE SET UP A PASSCODE]';
        }

        //full url
        $text = $baseUrl . 'connector/email/coupon/id/[INSERT ID HERE]/code/'
            . $passcode . '/expire_days/[INSERT NUMBER OF DAYS HERE]/@EMAIL@';
        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');

        return parent::_getElementHtml($element);
    }
}
