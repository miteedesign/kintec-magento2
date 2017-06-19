<?php

namespace Fooman\PdfCustomiser\Block;

use \Magento\Framework\View\Element\Template\Context;

class Table extends \Fooman\PdfCore\Block\Pdf\Table
{
    protected $orderItemRepository;

    protected $bundleProductItemHelper;

    public function __construct(
        Context $context,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Fooman\PdfCustomiser\Helper\BundleProductItem $bundleProductItemHelper,
        array $data = []
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->bundleProductItemHelper = $bundleProductItemHelper;
        parent::__construct($context, $data);
    }


    public function hasExtras(\Magento\Framework\DataObject $item)
    {
        $item = $this->getOrderItem($item);
        
        // we want to display bundle products info as extras
        if ($this->bundleProductItemHelper->isItemBundleProduct($item)) {
            return true;
        }
        
        $options = $item->getProductOptions();
        try {
            $this->orderItemRepository->get($item->getOrderId(), $item->getItemId());
            $hasGiftMessage = true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $hasGiftMessage = false;
        }
        return (!empty($options) || $hasGiftMessage);
    }

    public function getExtras(\Magento\Framework\DataObject $item)
    {
        $html = '';
        $item = $this->getOrderItem($item);
        if ($this->bundleProductItemHelper->isItemBundleProduct($item)) {
             $html .= $this->bundleProductItemHelper
                ->getBundleProductExtrasContent($item);
        }
        $options = $item->getProductOptions();
        $arrayKeys = ['options', 'additional_options', 'attributes_info'];
        $optOutput = [];
        foreach ($arrayKeys as $key) {
            if (isset($options[$key])) {
                foreach ($options[$key] as $option) {
                    $optOutput[] = $this->escapeHtml($option['label']) . ': '
                        . $this->escapeHtml($option['value']);
                }
            }
        }
        if (!empty($optOutput)) {
            $html .= '&nbsp;&nbsp;&nbsp;&nbsp;' . implode('<br/>&nbsp;&nbsp;&nbsp;&nbsp;', $optOutput);
        }

        try {
            $giftMessage = $this->orderItemRepository->get($item->getOrderId(), $item->getItemId());
            $html .= '<br/><strong>' . __('Gift Message') . '</strong><br/>';
            $html .= '<b>' . __('From:') . '</b>' . $this->escapeHtml($giftMessage->getSender()) . '<br/>';
            $html .= '<b>' . __('To:') . '</b>' . $this->escapeHtml($giftMessage->getRecipient()) . '<br/>';
            $html .= '<b>' . __('Message:') . '</b>' . $this->escapeHtml($giftMessage->getMessage()) . '<br/>';
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //Nothing to do - no associated gift message
        }

        return $html;
    }


    protected function getOrderItem($item)
    {
        if ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            return $item;
        }
        return $item->getOrderItem();
    }

}
