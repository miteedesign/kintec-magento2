<?php

namespace Fooman\PdfCustomiser\Block;

class Creditmemo extends AbstractSalesDocument
{
    const XML_PATH_TITLE = 'sales_pdf/creditmemo/creditmemotitle';
    const XML_PATH_ADDRESSES = 'sales_pdf/creditmemo/creditmemoaddresses';
    const XML_PATH_COLUMNS = 'sales_pdf/creditmemo/columns';
    const XML_PATH_CUSTOMTEXT = 'sales_pdf/creditmemo/creditmemocustom';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser_creditmemo';

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
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
            ['creditmemo' => $this->getCreditmemo()]
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function getSalesObject()
    {
        return $this->getCreditmemo();
    }

    /**
     * get main heading for creditmemo title ie PACKING SLIP
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
     * @return bool
     */
    public function showOrderIncrementId()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Pdf\AbstractPdf::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
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
