<?php

namespace Wyomind\SimpleGoogleShopping\Block\Adminhtml\Feeds\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    protected $_coreHelper = null;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Wyomind\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $data);
        $this->_coreHelper = $coreHelper;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        
        $actions = [
            [// Edit
                'caption' => __('Edit'),
                'url' => [
                    'base' => '*/*/edit'
                ],
                'field' => 'id'
            ],
            [// Generate
                'caption' => __('Generate'),
                'url' => '#',
                'onclick' => "SimpleGoogleShopping.feeds.generate('" . $this->getUrl('simplegoogleshopping/feeds/generate', ['id' => $row->getId()]) . "');"
            ],
            [// Preview
                'caption' => __('Preview (%1 items)', $this->_coreHelper->getStoreConfig("simplegoogleshopping/system/preview")),
                'url' => [
                    'base' => '*/*/preview',
                    'params' => ['limit' => 10]
                ],
                'field' => 'simplegoogleshopping_id',
                'popup' => true
            ],
            [// Report
                'caption' => __('Show Report'),
                'url' => [
                    'base' => '*/*/showreport'
                ],
                'field' => 'simplegoogleshopping_id',
                'popup' => true
            ],
            [// Delete
                'caption' => __('Delete'),
                'url' => "#",
                'onclick' => "SimpleGoogleShopping.feeds.delete('" . $this->getUrl('simplegoogleshopping/feeds/delete', ['id' => $row->getId()]) . "');"
            ]
        ];

        if ($this->getRequest()->getParam('debug')) {
            $actions[] = [
                'caption' => __('Debug'),
                'url' => [
                    'base' => '*/*/debug'
                ],
                'field' => 'simplegoogleshopping_id'
            ];
        }
        
        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }

    protected function _toOptionHtml(
        $action,
        \Magento\Framework\DataObject $row
    ) {
        $actionAttributes = new \Magento\Framework\DataObject();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        $onClick = null;
        if (isset($action['onclick'])) {
            $onClick = $action['onclick'];
            unset($action['onclick']);
        }

        $htmlAttributes = ['value' => $this->escapeHtml($this->_jsonEncoder->encode($action))];
        $actionAttributes->setData($htmlAttributes);
        return '<option ' . ($onClick != null ? 'onClick="' . $onClick . '"' : '') . $actionAttributes->serialize() . '>' . $actionCaption . '</option>';
    }
}
