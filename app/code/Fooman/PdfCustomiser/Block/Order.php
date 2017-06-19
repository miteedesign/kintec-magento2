<?php

namespace Fooman\PdfCustomiser\Block;

class Order extends AbstractSalesDocument
{
    const XML_PATH_TITLE = 'sales_pdf/order/ordertitle';
    const XML_PATH_ADDRESSES = 'sales_pdf/order/orderaddresses';
    const XML_PATH_COLUMNS = 'sales_pdf/order/columns';
    const XML_PATH_CUSTOMTEXT = 'sales_pdf/order/ordercustom';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser_order';

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    /**
     * return array of variables to be passed to the template
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return array_merge(
            parent::getTemplateVars(),
            ['order' => $this->getOrder()]
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getSalesObject()
    {
        return $this->getOrder();
    }

    /**
     * get visible order items
     * overridden as property different for orders
     *
     * @return array
     */
    public function getVisibleItems()
    {
        $items = [];
        $allItems = $this->getSalesObject()->getItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                if ($this->shouldDisplayItem($item)) {
                    $items[] = $this->prepareItem($item);
                }
            }
        }
        return $items;
    }

    /**
     * We generally don't want to display subitems
     *
     * @param $item
     *
     * @return bool
     */
    public function shouldDisplayItem($item)
    {
        return !$item->getParentItemId();
    }

    /**
     * Remove some fields on bundles
     *
     * @param $item
     *
     * @return mixed
     */
    public function prepareItem($item)
    {
        $this->addProductAttributeValues($item);
        return $item;
    }

    /**
     * get main heading for order title ie ORDER CONFIRMATION
     *
     * @param void
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getAddressesToDisplay()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ADDRESSES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getColumnConfig()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_COLUMNS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return mixed
     */
    public function getCustomText()
    {
        return $this->processCustomVars(
            $this->_scopeConfig->getValue(
                self::XML_PATH_CUSTOMTEXT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            ),
            $this->getTemplateVars()
        );
    }
}
