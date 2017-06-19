<?php

namespace Fooman\PdfCustomiser\Block;

abstract class AbstractSalesDocument extends \Fooman\PdfCore\Block\Pdf\DocumentRenderer
{
    const XML_PATH_OWNERADDRESS = 'sales_pdf/all/allowneraddress';
    const XML_PATH_PRINTCOMMENTS = 'sales_pdf/all/page/allprintcomments';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser';

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Fooman\PdfCore\Helper\Logo
     */
    protected $logoHelper;

    /**
     * @var string
     */
    protected $integratedLabelsConfigPath;

    /**
     * @var \Magento\GiftMessage\Api\OrderRepositoryInterface
     */
    protected $giftMessageOrderRepo;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * we probably need to use \Magento\Sales\Api\Data\OrderInterface here eventually
     * however the current code base around the address renderer does not use interfaces
     *
     * @return \Magento\Sales\Model\Order
     */
    abstract public function getOrder();

    /**
     * @return string
     */
    abstract public function getAddressesToDisplay();

    /**
     * @return mixed
     */
    abstract public function getSalesObject();

    /**
     * serialized config value for columns
     *
     * @return string
     */
    abstract public function getColumnConfig();

    /**
     * AbstractSalesDocument constructor.
     *
     * @param \Magento\Backend\Block\Template\Context               $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode         $maliciousCode
     * @param \Fooman\PdfCore\Model\Template                        $template
     * @param \Magento\Sales\Model\Order\Address\Renderer           $addressRenderer
     * @param \Magento\Payment\Helper\Data                          $paymentHelper
     * @param \Fooman\PdfCore\Helper\Logo                           $logoHelper
     * @param \Fooman\PdfCore\Model\IntegratedLabels\ContentFactory $integratedLabelsContentFactory
     * @param \Magento\Catalog\Model\ProductFactory                 $productFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory            $attributeFactory
     * @param \Magento\GiftMessage\Api\OrderRepositoryInterface     $giftMessageOrderRepo
     * @param \Magento\Framework\App\AreaList                       $areaList
     * @param \Fooman\PdfCore\Helper\ParamKey                       $paramKeyHelper
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Fooman\PdfCore\Model\Template $template,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Fooman\PdfCore\Helper\Logo $logoHelper,
        \Fooman\PdfCore\Model\IntegratedLabels\ContentFactory $integratedLabelsContentFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepo,
        \Magento\Framework\App\AreaList $areaList,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->timezone = $context->getLocaleDate();
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->logoHelper = $logoHelper;
        $this->integratedLabelsContentFactory = $integratedLabelsContentFactory;
        $this->productFactory = $productFactory;
        $this->attributeFactory = $attributeFactory;
        $this->giftMessageOrderRepo = $giftMessageOrderRepo;
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $maliciousCode, $template, $areaList, $data);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getSalesObject()->getStoreId();
    }

    /**
     * store owner address
     *
     * @return  string | bool
     * @access public
     */
    public function getOwnerAddress()
    {
        return $this->processCustomVars(
            $this->_scopeConfig->getValue(
                self::XML_PATH_OWNERADDRESS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            ),
            $this->getTemplateVars()
        );
    }

    /**
     * variables to be made available in the template
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return [
            'order' => $this->getOrder()
        ];
    }

    /**
     * @return string
     */
    protected function getTemplateText()
    {
        $templateVars = array_keys($this->getTemplateVars());
        $templateText = '{{layout handle="'.static::LAYOUT_HANDLE.'"';
        foreach ($templateVars as $var) {
            $templateText .= ' '.$var.'=$'.$var;
        }
        $templateText .= '}}';
        return $templateText;
    }

    /**
     * @return bool
     */
    public function shouldDisplayBothAddresses()
    {
        return $this->getAddressesToDisplay() ==
        \Fooman\PdfCustomiser\Model\System\AddressOptions::BOTH_ADDRESSES;
    }

    /**
     * @return bool
     */
    public function shouldDisplayBillingAddress()
    {
        return $this->getAddressesToDisplay() ==
        \Fooman\PdfCustomiser\Model\System\AddressOptions::BILLING_ONLY;
    }

    /**
     * @return bool
     */
    public function shouldDisplayShippingAddress()
    {
        return $this->getAddressesToDisplay() ==
        \Fooman\PdfCustomiser\Model\System\AddressOptions::SHIPPING_ONLY;
    }

    /**
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->filterAddress($this->addressRenderer->format($this->getOrder()->getBillingAddress(), 'pdf'));
    }

    /**
     * @return string
     */
    public function getShippingAddress()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return '';
        } else {
            return $this->filterAddress($this->addressRenderer->format($this->getOrder()->getShippingAddress(), 'pdf'));
        }
    }

    protected function getIntegratedLabelsConfigPath()
    {
        if (is_null($this->integratedLabelsConfigPath)) {
            $entityType = $this->getSalesObject()->getEntityType();
            $this->integratedLabelsConfigPath = 'sales_pdf/' . $entityType . '/' . $entityType . 'integratedlabels';
        }
        return $this->integratedLabelsConfigPath;
    }

    public function canApplyIntegratedLabelsContent()
    {
        $value = $this->getScopeConfig()->getValue(
            $this->getIntegratedLabelsConfigPath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return (($value != '0') && (!is_null($value)));
    }

    public function getIntegratedLabelsContent()
    {
        $value = $this->getScopeConfig()->getValue(
            $this->getIntegratedLabelsConfigPath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $content = $this->integratedLabelsContentFactory->create();
        switch ($value) {
            case 'double':
                $content->setLeft($this->getBillingAddress());
                $content->setRight($this->getShippingAddress());
                break;
            case 'singlebilling':
                $content->setLeft($this->getBillingAddress());
                break;
            case 'singleshipping':
                $content->setLeft($this->getShippingAddress());
                break;
            case 'shipping-giftmessage':
                $content->setLeft($this->getShippingAddress());
                try {
                    $giftMessage = $this->giftMessageOrderRepo->get($this->getOrder()->getEntityId());
                    $content->setRight(
                        '<table width="70"><tr><td align="center">'.
                        $giftMessage->getMessage().
                        '</td></tr></table>'
                    );
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    //Nothing to do - no associated gift message
                }
                break;
        }
        return $content;
    }

    /**
     * @return string
     */
    public function getShippingBlock()
    {
        $block = $this->getLayout()->createBlock('\Fooman\PdfCustomiser\Block\Shipping', 'pdfcustomiser.shipping');
        $tracks = $this->getTracksCollection();
        if (!empty($tracks)) {
            $block->setTracks($tracks);
        }
        $block->setShippingDescription($this->getOrder()->getShippingDescription())
            ->setTotalWeight();
        return $block->toHtml();
    }

    public function getTracksCollection()
    {
        return $this->getOrder()->getTracksCollection();
    }

    /**
     * @return string
     */
    public function getPaymentBlock()
    {
        $paymentBlock = $this->paymentHelper->getInfoBlock(
            $this->getOrder()->getPayment(),
            $this->getLayout()
        );
        $paymentBlock->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($this->getStoreId());
        return trim(strip_tags($paymentBlock->toHtml()));
    }

    /**
     * @return string
     */
    public function getTotalsBlock()
    {
        $block = $this->getLayout()->createBlock('\Fooman\PdfCustomiser\Block\Totals', 'pdfcustomiser.totals');
        $block->setOrder($this->getOrder());
        $block->setSalesObject($this->getSalesObject());
        return $block->toHtml();
    }

    /**
     * @return string|bool
     */
    public function getPrintComments()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRINTCOMMENTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getCommentsBlock()
    {
        $printCommentsConfig = $this->getPrintComments();
        if ($printCommentsConfig) {
            $comments = [];
            $salesObject = $this->getSalesObject();
            if ($salesObject instanceof \Magento\Sales\Model\Order) {
                switch ($printCommentsConfig) {
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_ALL:
                        $commentObject = $salesObject->getAllStatusHistory();
                        break;
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_FRONTEND_VISIBLE:
                        $commentObject = $salesObject->getVisibleStatusHistory();
                        break;
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_BACKEND_VISIBLE:
                        $allCommentObject = $salesObject->getAllStatusHistory();
                        $commentObject = [];
                        foreach ($allCommentObject as $history) {
                            if (!$history->getIsVisibleOnFront()) {
                                $commentObject[] = $history;
                            }
                        }
                        break;
                }

                if (!empty($commentObject)) {
                    foreach ($commentObject as $history) {
                        $comments[] = [
                            'date'    => $this->timezone->formatDate(
                                $history->getCreatedAtStoreDate(),
                                \IntlDateFormatter::MEDIUM
                            ),
                            'label'   => $history->getStatusLabel(),
                            'comment' => $history->getComment()
                        ];
                    }
                }
            } else {
                if ($salesObject->getCommentsCollection()) {
                    switch ($printCommentsConfig) {
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_ALL:
                            $commentObject = $salesObject->getCommentsCollection();
                            break;
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_FRONTEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            foreach ($allCommentObject as $comment) {
                                if ($comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_BACKEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            $commentObject = [];
                            foreach ($allCommentObject as $comment) {
                                if (!$comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                    }
                    if (!empty($commentObject)) {
                        foreach ($commentObject as $comment) {
                            if ($comment->getCreatedAt()) {
                                $date = $this->timezone->formatDate(
                                    $comment->getCreatedAtStoreDate(),
                                    \IntlDateFormatter::MEDIUM
                                );
                            } else {
                                $date = '';
                            }
                            $comments[] = [
                                'date'    => $date,
                                'label'   => '',
                                'comment' => $comment->getComment()
                            ];
                        }
                    }
                }
            }
            if (count($comments) > 0) {
                $block = $this->getLayout()->createBlock(
                    '\Fooman\PdfCustomiser\Block\Comments',
                    'pdfcustomiser.comments' . uniqid(),
                    ['data' => ['comments' => $comments]]
                );
                return $block->toHtml();
            }
        }
        return '';
    }

    public function getGiftmessageBlock()
    {
        try {
            $giftMessage = $this->giftMessageOrderRepo->get($this->getOrder()->getEntityId());
            $block = $this->getLayout()->createBlock(
                '\Fooman\PdfCustomiser\Block\Giftmessage',
                'pdfcustomiser.giftmessage' . uniqid(),
                ['data' => ['giftmessage' => $giftMessage]]
            );

            return $block->toHtml();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //Nothing to do - no associated gift message
        }
        return '';
    }

    /**
     * @return string
     */
    public function getLogoBlock()
    {
        $block = $this->getLayout()->createBlock(
            '\Fooman\PdfCore\Block\Pdf\Template\Logo',
            'pdfcore.logo' . uniqid(),
            ['data' => ['storeId' => $this->getSalesObject()->getStoreId()]]
        );

        return $block->toHtml();
    }

    /**
     * @param array $styling
     *
     * @return mixed
     */
    public function getItemsBlock($styling = [])
    {
        $block = $this->getLayout()->createBlock(
            '\Fooman\PdfCustomiser\Block\Table',
            'pdfcustomiser.items' . uniqid(),
            ['data' => [
                'tableColumns'  => $this->getTableColumns(),
                'currency_code' => $this->getOrder()->getOrderCurrency()->getCode()
            ]
            ]
        );
        $block->setStyling($styling);
        $block->setCollection($this->getVisibleItems());
        return $block->toHtml();
    }

    /**
     * get line items to display
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
        $orderItem = $item->getOrderItem();
        return !$orderItem->getParentItemId();
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
        $orderItem = $item->getOrderItem();
        $this->addProductAttributeValues($orderItem);

        if ($orderItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $item->unsPrice();
        }

        return $item;
    }

    /**
     * @param $item
     */
    protected function addProductAttributeValues(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {

        $productAttributes = $this->getProductAttributes();
        if (count($productAttributes) == 0) {
            return;
        }

        if ($item->getProductType()
            == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ) {
            //we want to load the attribute of the simple product if part of a configurable
            $product = $this->productFactory->create()->loadByAttribute('sku', $item->getSku());
        } else {
            $product = $this->productFactory->create()->load($item->getProductId());
        }

        if (!$product) {
            return;
        }

        foreach ($productAttributes as $productAttribute) {
            $attribute = $this->attributeFactory
                ->create()
                ->loadByCode('catalog_product', $productAttribute);

            if (($attribute->getFrontendInput() == 'select') && !$attribute->getSourceModel()) {
                $value = $product->getAttributeText($productAttribute);
            } else {
                $value = $product->getData($productAttribute);
            }

            $item->setData('product_' . $productAttribute, $value);
        }
    }

    /**
     * @return array
     */
    protected function getProductAttributes()
    {
        $productAttributes = [];
        $config = $this->getColumnConfig();
        if ($config) {
            $config = json_decode($config, true);
            foreach ($config as $column) {
                if (strpos($column['columntype'], 'product/') !== false) {
                    $productAttributes[] = str_replace('product/', '', $column['columntype']);
                }
            }
        }
        return $productAttributes;
    }

    /**
     * converts pipe character to linebreak
     * removes empty lines
     *
     * @param $input
     *
     * @return string
     */
    protected function filterAddress($input)
    {
        $input = $this->escapeHtml($input);
        $input = str_replace(['|', PHP_EOL], '<br/>', $input);
        return preg_replace('/(<br\s*\/?>\s*)+/', '<br/>', $input);
    }

    /**
     * convert pdf row separator into proper linebreaks
     *
     * @param $input
     *
     * @return string
     */
    protected function filterPaymentBlock($input)
    {
        return str_replace('{{pdf_row_separator}}', '<br/>', $input);
    }

    /**
     * @param $createdAt
     *
     * @return string
     */
    public function getFormattedDate($createdAt)
    {
        return $this->timezone->formatDate(
            $this->timezone->scopeDate(
                $this->getOrder()->getStore(),
                $createdAt,
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
    }

    /**
     * prepare column config value for use in table constructor
     *
     * @return array
     */
    public function getTableColumns()
    {
        $return = [];
        $config = $this->getColumnConfig();
        if ($config) {
            $config = json_decode($config, true);
            foreach ($config as $column) {
                $currentColumn = [];
                $currentColumn['index'] = $column['columntype'];
                if (isset($column['width']) && $column['width'] > 0) {
                    $currentColumn['width'] = $column['width'];
                }
                if (isset($column['title'])) {
                    $currentColumn['title'] = $column['title'];
                }
                $return[] = $currentColumn;
            }
        }
        return $return;
    }

    /**
     * @return bool
     */
    public function isLogoOnRight()
    {
        return $this->logoHelper->isLogoOnRight();
    }

    /**
     * @return array
     */
    public function getDefaultItemStyling()
    {
        return [
            'header' => [
                'default' => 'border-bottom:1px solid black;',
                'first'   => 'border-bottom:1px solid black;',
                'last'    => 'border-bottom:1px solid black;'
            ],
            'row'    => [
                'default' => 'border-bottom:0px none transparent;',
                'last'    => 'border-bottom:0px solid black;',
                'first'   => 'border-bottom:0px none transparent;'
            ],
            'table'  => ['default' => 'padding: 2px 0px;']
        ];
    }

    public function getIncrement()
    {
        return $this->getSalesObject()->getIncrementId();
    }

    public function processCustomVars($input, $templateVars)
    {

        /** @var $template \Fooman\PdfCore\Model\Template */
        $template = $this->template->setArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $template->setTemplateText($this->maliciousCode->filter($input));

        $template->setDesignConfig(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->getStoreId()]
        );

        return $template->getProcessedTemplate($templateVars);
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