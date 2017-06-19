<?php

namespace Fooman\PdfCustomiser\Block;

class Shipping extends \Fooman\PdfCore\Block\Pdf\Block
{
    const XML_PATH_PRINT_SHIPPING_BARCODE = 'sales_pdf/all/allprinttrackingbarcode';

    protected $_template = 'Fooman_PdfCustomiser::pdf/shipping.phtml';

    protected $tracks;
    protected $description;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fooman\PdfCore\Helper\ParamKey                  $paramKeyHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $tracks
     *
     * @return $this
     */
    public function setTracks(\Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $tracks)
    {
        $this->tracks = $tracks;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param $desc
     *
     * @return $this
     */
    public function setShippingDescription($desc)
    {
        $this->description = $desc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShippingDescription()
    {
        return $this->description;
    }

    /**
     * should we print a barcode of the tracking number?
     *
     * @param  void
     *
     * @return bool
     * @access public
     */
    public function shouldPrintTrackingBarcode()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRINT_SHIPPING_BARCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getEncodedParams(array $params)
    {
        return $this->paramKeyHelper->getEncodedParams($params);
    }
}
