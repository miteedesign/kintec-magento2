<?php

namespace Fooman\PdfCustomiser\Block;

class Invoice extends AbstractSalesDocument
{
    const XML_PATH_TITLE = 'sales_pdf/invoice/invoicetitle';
    const XML_PATH_ADDRESSES = 'sales_pdf/invoice/invoiceaddresses';
    const XML_PATH_COLUMNS = 'sales_pdf/invoice/columns';
    const XML_PATH_CUSTOMTEXT = 'sales_pdf/invoice/invoicecustom';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser_invoice';

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
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
            ['invoice' => $this->getInvoice()]
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    public function getSalesObject()
    {
        return $this->getInvoice();
    }

    /**
     * get main heading for invoice title ie TAX INVOICE
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
            \Magento\Sales\Model\Order\Pdf\AbstractPdf::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
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
