<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.58
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Seo\Model\Config as Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Snippet implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Seo\Helper\Snippets
     */
    protected $seoSnippets;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogImage;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var bool
     */
    public $appliedSnippets = false;

    /**
     * @var bool
     */
    public $isProductPage = false;
    /**
     * @var bool
     */
    public $isCategoryPage = false;
    /**
     * @var bool
     */
    public $appliedCategorySnippets = false;
    /**
     * @var string
     */
    public $goodrelationsUrl = 'http://purl.org/goodrelations/v1#';

    /**
     * @param \Magento\Catalog\Model\CategoryFactory             $categoryFactory
     * @param \Magento\Payment\Model\Config                      $paymentConfig
     * @param \Magento\Shipping\Model\Config                     $shippingMethodConfig
     * @param \Mirasvit\Seo\Model\Config                         $config
     * @param \Mirasvit\Seo\Helper\Snippets                      $seoSnippets
     * @param \Magento\Catalog\Helper\Image                      $catalogImage
     * @param \Mirasvit\Seo\Helper\Data                          $seoData
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry                        $registry
     * @param \Magento\Framework\App\RequestInterface            $request
     * @param \Magento\Framework\Model\Context                   $context
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory             $categoryFactory,
        \Magento\Payment\Model\Config                      $paymentConfig,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Mirasvit\Seo\Model\Config                         $config,
        \Mirasvit\Seo\Helper\Snippets                      $seoSnippets,
        \Magento\Catalog\Helper\Image                      $catalogImage,
        \Mirasvit\Seo\Helper\Data                          $seoData,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry                        $registry,
        \Magento\Framework\App\RequestInterface            $request,
        \Magento\Framework\Model\Context                   $context
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->seoSnippets = $seoSnippets;
        $this->catalogImage = $catalogImage;
        $this->seoData = $seoData;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->request = $request;
        $this->context = $context;

        if ($this->request->getControllerName() === 'product') {
            $this->isProductPage = true;
        }
    }

    /**
     * @param string $e
     * @param bool|Magento\Framework\App\Response\Http $response
     *
     * @return bool|Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProductSnippets($e, $response = false)
    {
        $product = $this->registry->registry('current_product');

        $applyForCache = ($response) ? true : false;

        if ($applyForCache && (!$product
            || $this->appliedSnippets
            || $this->seoData->isIgnoredActions())) {
            return $response;
        } elseif (!$applyForCache && (!is_object($e)
            || !$product
            || $this->appliedSnippets
            || $this->seoData->isIgnoredActions())) {
            return $response;
        }

        $changeDefaultSnippets = $this->config->isForceProductSnippets();

        if (!$applyForCache) {
            $response = $e->getResponse();
        }

        $body = $response->getBody();

        $availability = $this->_getItemAvailability($product);

        $paymentMethods = $this->_getPaymentMethods();

        $deliveryMethods = $this->_getDeliveryMethods();

        $productCondition = $this->_getProductCondition($product);

        $offerSnippets = $availability.$paymentMethods.$deliveryMethods.$productCondition;

        $image = $this->_getImage($product);

        $categoryName = $this->_getCategoryName($product);

        $brand = $this->_getBrand($product);

        $model = $this->_getModel($product);

        $color = $this->_getColor($product);

        $weight = $this->_getWeight($product);

        $dimensions = $this->_getDimensions($product);

        $description = $this->_getDescription($body, $product);

        $productSnippets = $image.$categoryName.$brand.$model.$color.$weight.$dimensions.$description;

        if (!$productSnippets && !$offerSnippets && !$changeDefaultSnippets) {
            return $response;
        }

        if (strpos($body, 'itemtype="http://schema.org/Product"') === false
            || strpos($body, 'itemtype="http://schema.org/Offer"') === false
            || $changeDefaultSnippets) {
            $body = $this->_deleteWrongSnippets($body);
            $snippetBlock = $this->_getProductSnippetsBlock($product, $productSnippets, $offerSnippets);
            $body = preg_replace('/<\\/body>/', $snippetBlock . '</body>', $body, 1);
        } else {
            $body = preg_replace(
                '/http:\\/\\/schema.org\\/Offer(.*?)>/i',
                'http://schema.org/Offer$1>'.$offerSnippets,
                $body,
                1
            );
            $body = preg_replace(
                '/http:\\/\\/schema.org\\/Product(.*?)>/i',
                'http://schema.org/Product$1>'.$productSnippets,
                $body,
                1
            );
        }

        $response->setBody($body);

        $this->appliedSnippets = true;

        if ($applyForCache) {
            return $response;
        }
    }

    /**
     * @param string $product
     * @param string $productSnippets
     * @param string $offerSnippets
     * @return string
     */
    protected function _getProductSnippetsBlock($product, $productSnippets, $offerSnippets)
    {
        $snippetBlock = '<div itemscope itemtype="http://schema.org/Product">'.
                        '<meta itemprop="name" content="'.$product->getName().'"/>'.
                        '<meta itemprop="sku" content="'.$product->getSku().'"/>'.
                        $this->_getManufacturerPartNumber($product).
                        $productSnippets.
                        $this->_getAggregateRating($product).
                        $this->_getOffer($product, $offerSnippets).
                        '</div>';

        return $snippetBlock;
    }

    /**
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function _getManufacturerPartNumber($product)
    {
        $mpn = '';
        if ($this->config->isEnabledRichSnippetsManufacturerPartNumber()) {
            $mpn = '<meta itemprop="mpn" content="'.$product->getSku().'"/>';
        }

        return $mpn;
    }

    /**
     * @param string $body
     * @param string $product
     * @return string
     */
    protected function _getDescription($body, $product)
    {
        $description = '';

        if ($this->config->getRichSnippetsDescription() == Config::DESCRIPTION_SNIPPETS) {
            $description = $product->getDescription();
        }
        if ($this->config->getRichSnippetsDescription() == Config::META_DESCRIPTION_SNIPPETS) {
            preg_match('/meta name\\=\\"description\\" content\\=\\"(.*?)\\"\\/\\>/i', $body, $descriptionArray);
            if (isset($descriptionArray[1])) {
                $description = trim($descriptionArray[1]);
            }
        }

        if ($description) {
            $description = '<meta itemprop="description" content="'.str_replace(
                '"',
                '&#34;',
                strip_tags($description)
            ).'"/>';
        }

        return $description;
    }

    /**
     * @param string $product
     * @return $this|string
     */
    protected function _getImage($product)
    {
        $image = false;
        if ($this->config->isEnabledRichSnippetsItemImage()
            && ($image = $this->catalogImage->init($product, 'product_page_image_large'))) {
                $image = '<meta itemprop="image" content="'.$image->getUrl().'"/>';
        }

        return $image;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getItemAvailability($product)
    {
        $availability = '';

        if (!$this->config->isEnabledRichSnippetsItemAvailability()) {
            return $availability;
        }

        $productAvailability = (method_exists($product, 'isAvailable')) ?
            $product->isAvailable() : $product->isInStock();

        if ($productAvailability) {
            $availability = '<link itemprop="availability" href="http://schema.org/InStock" />';
        } else {
            $availability = '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
        }

        return $availability;
    }

    /**
     * @return string
     */
    protected function _getPaymentMethods()
    {
        $paymentMethods = '';

        if ($this->config->isEnabledRichSnippetsPaymentMethod()
            && ($activePaymentMethods = $this->_getActivePaymentMethods())) {
            foreach ($activePaymentMethods as $method) {
                $paymentMethods .= '<link itemprop="acceptedPaymentMethod" href="'.$method.'" />';
            }
        }

        return $paymentMethods;
    }

    /**
     * @return array
     */
    protected function _getActivePaymentMethods()
    {
        $payments = $this->paymentConfig->getActiveMethods();
        $methods = [];
        foreach (array_keys($payments) as $paymentCode) {
            if (strpos($paymentCode, 'paypal') !== false) {
                $methods[] = $this->goodrelationsUrl.'PayPal';
            }
            if (strpos($paymentCode, 'googlecheckout') !== false) {
                $methods[] = $this->goodrelationsUrl.'GoogleCheckout';
            }
            if (strpos($paymentCode, 'cash') !== false) {
                $methods[] = $this->goodrelationsUrl.'Cash';
            }
            if ($paymentCode == 'ccsave') {
                if ($existingMethods = $this->_getActivePaymentCctypes()) {
                    $methods = array_merge($methods, $existingMethods);
                }
            }
        }

        return array_unique($methods);
    }

    /**
     * @return array|bool
     */
    protected function _getActivePaymentCctypes()
    {
        $existingMethods = [];
        $methods = [
            'AE' => 'AmericanExpress',
            'VI' => 'VISA',
            'MC' => 'MasterCard',
            'DI' => 'Discover',
            'JCB' => 'JCB',
        ];

        if ($cctypes = $this->scopeConfig->getValue(
            'payment/ccsave/cctypes',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->storeManager->getStore()->getStoreId()
        )
            ) {
            $cctypesArray = explode(',', $cctypes);
            foreach ($cctypesArray as $cctypeValue) {
                if (isset($methods[$cctypeValue])) {
                    $existingMethods[] = $this->goodrelationsUrl.$methods[$cctypeValue];
                }
            }

            return $existingMethods;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function _getDeliveryMethods()
    {
        $deliveryMethods = '';

        if ($this->config->isEnabledRichSnippetsDeliveryMethod()
            && ($activeDeliveryMethods = $this->_getActiveDeliveryMethods())) {
            foreach ($activeDeliveryMethods as $method) {
                $deliveryMethods .= '<link itemprop="availableDeliveryMethod" href="'.$method.'" />';
            }
        }

        return $deliveryMethods;
    }

    /**
     * @return array
     */
    protected function _getActiveDeliveryMethods()
    {
        $existingMethods = [];
        $methods = [
            'flatrate' => 'DeliveryModeFreight',
            'freeshipping' => 'DeliveryModeFreight',
            'tablerate' => 'DeliveryModeFreight',
            'dhl' => 'DHL',
            'fedex' => 'FederalExpress',
            'ups' => 'UPS',
            'usps' => 'DeliveryModeMail',
            'dhlint' => 'DHL',
        ];

        $deliveryMethods = $this->shippingMethodConfig->getActiveCarriers();
        foreach (array_keys($deliveryMethods) as $code) {
            if (isset($methods[$code])) {
                $existingMethods[] = $this->goodrelationsUrl.$methods[$code];
            }
        }

        return array_unique($existingMethods);
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getCategoryName($product)
    {
        $categoryName = '';

        if (!$this->config->isEnabledRichSnippetsProductCategory()) {
            return $categoryName;
        }

        if ($category = $this->registry->registry('current_category')) {
            $categoryName = $category->getName();
        } else {
            $categoryIds = $product->getCategoryIds();
            $categoryIds = array_reverse($categoryIds);
            if (isset($categoryIds[0])) {
                $categoryName = $this->categoryFactory->create()
                                ->setStoreId($this->storeManager->getStore()->getStoreId())
                                ->load($categoryIds[0])
                                ->getName();
            }
        }

        if ($categoryName) {
            $categoryName = '<meta itemprop="category" content="'.$categoryName.'"/>';
        }

        return $categoryName;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getBrand($product)
    {
        $brand = '';
        $attributeBrand = false;
        if ($brandPrepared = $this->config->getRichSnippetsBrandAttributes()) {
            $attributeBrand = $this->_getRichSnippetsAttributeValue($brandPrepared, $product);
        }
        if ($attributeBrand) {
            $brand = '<meta itemprop="brand" content="'.$attributeBrand.'" />';
        }

        return $brand;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getModel($product)
    {
        $model = '';
        $attributeModel = false;

        if ($modelPrepared = $this->config->getRichSnippetsModelAttributes()) {
            $attributeModel = $this->_getRichSnippetsAttributeValue($modelPrepared, $product);
        }
        if ($attributeModel) {
            $model = '<meta itemprop="model" content="'.$attributeModel.'"/>';
        }

        return $model;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getColor($product)
    {
        $color = '';
        $attributeColor = false;

        if ($colorPrepared = $this->config->getRichSnippetsColorAttributes()) {
            $attributeColor = $this->_getRichSnippetsAttributeValue($colorPrepared, $product);
        }
        if ($attributeColor) {
            $color = '<meta itemprop="color" content="'.$attributeColor.'"/>';
        }

        return $color;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getWeight($product)
    {
        $weight = '';

        if (($weightPrepared = $product->getWeight()) && ($weightCode = $this->config->getRichSnippetsWeightCode())) {
            $weight = '
            <span itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">
                <meta itemprop="value" content="'.$weightPrepared.'"/>
                <meta itemprop="unitCode" content="'.$weightCode.'">
            </span>';
        }

        return $weight;
    }

    /**
     * @param string $product
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getDimensions($product)
    {
        $dimensions = '';
        $attributeHeight = false;
        $attributeWidth = false;
        $attributeDepth = false;

        if (!$this->config->isEnabledRichSnippetsDimensions()) {
            return $dimensions;
        }

        $dimensionalUnit = $this->config->getRichSnippetsDimensionUnit();
        if ($dimensionalUnit) {
            $dimensionalUnit = $this->seoSnippets->prepareDimensionCode($dimensionalUnit);
        }
        if ($height = $this->config->getRichSnippetsHeightAttributes()) {
            $attributeHeight = $this->_getRichSnippetsAttributeValue($height, $product);
        }
        if ($attributeHeight) {
            $dimensions .= '
            <span itemprop="height" itemscope itemtype="http://schema.org/QuantitativeValue">
                <meta itemprop="value" content="'.$attributeHeight.'"/>';
            if ($dimensionalUnit) {
                $dimensions .= '<meta itemprop="unitCode" content="'.$dimensionalUnit.'">';
            }
            $dimensions .= '</span>';
        }

        if ($width = $this->config->getRichSnippetsWidthAttributes()) {
            $attributeWidth = $this->_getRichSnippetsAttributeValue($width, $product);
        }
        if ($attributeWidth) {
            $dimensions .= '
            <span itemprop="width" itemscope itemtype="http://schema.org/QuantitativeValue">
                 <meta itemprop="value" content="'.$attributeWidth.'"/>';
            if ($dimensionalUnit) {
                $dimensions .= '<meta itemprop="unitCode" content="'.$dimensionalUnit.'">';
            }
            $dimensions .= '</span>';
        }

        if ($depth = $this->config->getRichSnippetsDepthAttributes()) {
            $attributeDepth = $this->_getRichSnippetsAttributeValue($depth, $product);
        }
        if ($attributeDepth) {
            $dimensions .= '
            <span itemprop="depth" itemscope itemtype="http://schema.org/QuantitativeValue">
                 <meta itemprop="value" content="'.$attributeDepth.'"/>';
            if ($dimensionalUnit) {
                $dimensions .= '<meta itemprop="unitCode" content="'.$dimensionalUnit.'">';
            }
            $dimensions .= '</span>';
        }

        return $dimensions;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getProductCondition($product)
    {
        $condition = '';
        $richSnippetsCondition = $this->config->getRichSnippetsCondition();

        if ($richSnippetsCondition == Config::CONDITION_RICH_SNIPPETS_CONFIGURE
            && ($conditionAttribute = $this->config->getRichSnippetsConditionAttribute())
            && ($attributeCondition = $this->_getRichSnippetsAttributeValue($conditionAttribute, $product))) {
            switch (strtolower($attributeCondition)) {
                case (strtolower($this->config->getRichSnippetsNewConditionValue())):
                    $condition = '<link itemprop="itemCondition" href="http://schema.org/NewCondition" />';
                    break;
                case (strtolower($this->config->getRichSnippetsUsedConditionValue())):
                    $condition = '<link itemprop="itemCondition" href="http://schema.org/UsedCondition" />';
                    break;
                case (strtolower($this->config->getRichSnippetsRefurbishedConditionValue())):
                    $condition = '<link itemprop="itemCondition" href="http://schema.org/RefurbishedCondition" />';
                    break;
                case (strtolower($this->config->getRichSnippetsDamagedConditionValue())):
                    $condition = '<link itemprop="itemCondition" href="http://schema.org/DamagedCondition" />';
                    break;
            }
        } elseif ($richSnippetsCondition == Config::CONDITION_RICH_SNIPPETS_NEW_ALL) {
            $condition = '<link itemprop="itemCondition" href="http://schema.org/NewCondition"; />';
        }

        return $condition;
    }

    /**
     * @param string $attributeArray
     * @param string $product
     * @return bool|string
     */
    protected function _getRichSnippetsAttributeValue($attributeArray, $product)
    {
        $attributeValue = false;
        foreach ($attributeArray as $attributeName) {
            if ($attribute = $product->getResource()->getAttribute($attributeName)) {
                $attributeValue = trim($attribute->getFrontend()->getValue($product));
            }
            if ($attributeValue && $attributeValue != 'No' && $attributeValue != 'Нет') {
                return $attributeValue;
            }
        }

        return false;
    }

    /**
     * @param string $html
     * @return array|string|null
     */
    protected function _deleteWrongSnippets($html)
    {
        $breadcumbPattern = '/\\<span class="breadcrumbsbefore"\\>\\<\\/span\\>(.*?)
                            \\<span class="breadcrumbsafter"\\>\\<\\/span\\>/ims';
        preg_match($breadcumbPattern, $html, $breadcumb);
        $pattern = ['/itemprop="(.*?)"/ims',
                        '/itemprop=\'(.*?)\'/ims',
                        '/itemtype="(.*?)"/ims',
                        '/itemtype=\'(.*?)\'/ims',
                        '/itemscope="(.*?)"/ims',
                        '/itemscope=\'(.*?)\'/ims',
                        '/itemscope=\'\'/ims',
                        '/itemscope=""/ims',
                        '/itemscope\s/ims',
                        ];
        $html = preg_replace($pattern, '', $html);
        if (isset($breadcumb[1]) && $breadcumb[1]) {
            $html = preg_replace($breadcumbPattern, $breadcumb[1], $html);
        }

        return $html;
    }

    /**
     * @param string $product
     * @return string
     */
    protected function _getAggregateRating($product)
    {
        $ratingData = '';
        if (!is_object($product->getRatingSummary())) {
            return $ratingData;
        }
        if (($ratingValue = $product->getRatingSummary()->getRatingSummary())
            && ($reviewsCount = $product->getRatingSummary()->getReviewsCount())) {
            $ratingValue = number_format($ratingValue *5/100, 2);
            $ratingData .= '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">'.
                                   '<meta itemprop="ratingValue" content="'.$ratingValue.'" />'.
                                   '<meta itemprop="bestRating" content="5" />'.
                                   '<meta itemprop="reviewCount" content="'.$reviewsCount.'" />'.
                               '</div>';
        }

        return $ratingData;
    }

    /**
     * @param string $product
     * @param string $offerSnippets
     * @return string
     */
    protected function _getOffer($product, $offerSnippets)
    {
        $price = '';

        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        if ($productFinalPrice = $this->seoData->getCurrentProductFinalPrice($product, true)) {
            /* Google Structured Data Testing Tool throws Warning on price attribute with comma like price = 3,99 */
            if (substr_count($productFinalPrice, ',') + substr_count($productFinalPrice, '.') > 1) {
                $productFinalPrice = str_replace(',', '', $productFinalPrice);
            } elseif (strpos($productFinalPrice, ',') !== false) {
                $productFinalPrice = str_replace(',', '.', $productFinalPrice);
            }
            $price = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">'.
                        $offerSnippets.
                        '<meta itemprop="price" content="'.$productFinalPrice.'" />'.
                        '<meta itemprop="priceCurrency" content="'.$currencyCode.'" />'.
                     '</span>';
        }

        return $price;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->addProductSnippets($observer);
    }
}
