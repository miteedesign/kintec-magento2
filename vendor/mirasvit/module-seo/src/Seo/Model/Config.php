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



namespace Mirasvit\Seo\Model;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

     /**
      * @var \Mirasvit\Seo\Model\Cookie\Cookie
      */
    protected $cookie;

     /**
      * @var \Magento\Store\Model\StoreManagerInterface
      */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mirasvit\Seo\Model\Cookie\Cookie $cookie,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cookie = $cookie;
        $this->storeManager = $storeManager;
    }

    const NO_TRAILING_SLASH = 1;
    const TRAILING_SLASH = 2;

    const URL_FORMAT_SHORT = 1;
    const URL_FORMAT_LONG = 2;

    const NOINDEX_NOFOLLOW = 1;
    const NOINDEX_FOLLOW = 2;
    const INDEX_NOFOLLOW = 3;

    const CATEGYRY_RICH_SNIPPETS_PAGE = 1;
    const CATEGYRY_RICH_SNIPPETS_CATEGORY = 2;

    const PRODUCTS_WITH_REVIEWS_NUMBER = 1;
    const REVIEWS_NUMBER = 2;

    const BREADCRUMB = 1;
    const BREADCRUMB_LIST = 2;

    const META_TITLE_PAGE_NUMBER_BEGIN = 1;
    const META_TITLE_PAGE_NUMBER_END = 2;
    const META_TITLE_PAGE_NUMBER_BEGIN_FIRST_PAGE = 3;
    const META_TITLE_PAGE_NUMBER_END_FIRST_PAGE = 4;

    const META_DESCRIPTION_PAGE_NUMBER_BEGIN = 1;
    const META_DESCRIPTION_PAGE_NUMBER_END = 2;
    const META_DESCRIPTION_PAGE_NUMBER_BEGIN_FIRST_PAGE = 3;
    const META_DESCRIPTION_PAGE_NUMBER_END_FIRST_PAGE = 4;

    const META_TITLE_MAX_LENGTH = 55;
    const META_DESCRIPTION_MAX_LENGTH = 150;
    const PRODUCT_NAME_MAX_LENGTH = 25;
    const PRODUCT_SHORT_DESCRIPTION_MAX_LENGTH = 90;
    const META_TITLE_INCORRECT_LENGTH = 25;
    const META_DESCRIPTION_INCORRECT_LENGTH = 25;
    const RODUCT_NAME_INCORRECT_LENGTH = 10;
    const PRODUCT_SHORT_DESCRIPTION_INCORRECT_LENGTH = 25;

    const PRODUCT_WEIGHT_RICH_SNIPPETS_KG = 'KGM';
    const PRODUCT_WEIGHT_RICH_SNIPPETS_LB = 'LBR';
    const PRODUCT_WEIGHT_RICH_SNIPPETS_G = 'GRM';

    //seo template rule
    const PRODUCTS_RULE = 1;
    const CATEGORIES_RULE = 2;
    const RESULTS_LAYERED_NAVIGATION_RULE = 3;

    // open graph
    const OPENGRAPH_LOGO_IMAGE = 1;
    const OPENGRAPH_PRODUCT_IMAGE = 2;

    // description rich snippets
    const DESCRIPTION_SNIPPETS = 1;
    const META_DESCRIPTION_SNIPPETS = 2;

    //seo condition rich snippets
    const CONDITION_RICH_SNIPPETS_CONFIGURE             = 1;
    const CONDITION_RICH_SNIPPETS_NEW_ALL               = 2;

    //seo info
    const INFO_IP = 1;
    const INFO_COOKIE = 2;
    const COOKIE_DEL_BUTTON = 'Delete cookie';
    const COOKIE_ADD_BUTTON = 'Add cookie';
    const BYPASS_COOKIE = 'info_bypass_cookie';

    //Description Position
    const BOTTOM_PAGE = 1;
    const UNDER_SHORT_DESCRIPTION = 2;
    const UNDER_FULL_DESCRIPTION = 3;
    const UNDER_PRODUCT_LIST = 4;
    const CUSTOM_TEMPLATE = 5;

     //Description Position
    const X_DEFAULT_AUTOMATICALLY = 'AUTOMATICALLY';

    //amasty_xlanding page
    const AMASTY_XLANDING = 'amasty_xlanding_page_view';

    /**
     * @return bool
     */
    public function isAddCanonicalUrl()
    {
        return $this->scopeConfig->getValue('seo/general/is_add_canonical_url');
    }

    /**
     * @return bool
     */
    public function isAddLongestCanonicalProductUrl()
    {
        return $this->scopeConfig->getValue('seo/general/is_longest_canonical_url');
    }


    /**
     * @return int
     */
    public function getAssociatedCanonicalConfigurableProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_configurable_product');
    }

    /**
     * @return int
     */
    public function getAssociatedCanonicalGroupedProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_grouped_product');
    }

    /**
     * @return int
     */
    public function getAssociatedCanonicalBundleProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_bundle_product');
    }

     /**
      * @param int|bool $store
      * @return int
      */
    public function getCrossDomainStore($store = null)
    {
        return $this->scopeConfig->getValue('seo/general/crossdomain',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isPaginatedCanonical()
    {
        return $this->scopeConfig->getValue('seo/general/paginated_canonical');
    }

    /**
     * @return array
     */
    public function getCanonicalUrlIgnorePages()
    {
        $pages = $this->scopeConfig->getValue('seo/general/canonical_url_ignore_pages');
        $pages = explode("\n", trim($pages));
        $pages = array_map('trim', $pages);

        return $pages;
    }

    /**
     * @return array
     */
    public function getNoindexPages()
    {
        $pages = $this->scopeConfig->getValue('seo/general/noindex_pages2');
        $pages = unserialize($pages);
        $result = [];
        if (is_array($pages)) {
            foreach ($pages as $value) {
                $result[] = new \Magento\Framework\DataObject($value);
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getHttpsNoindexPages()
    {
        return $this->scopeConfig->getValue('seo/general/https_noindex_pages');
    }

    /**
     * @param string $store
     * @return bool
     */
    public function isAlternateHreflangEnabled($store)
    {
        return $this->scopeConfig->getValue(
            'seo/general/is_alternate_hreflang',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isHreflangLocaleCodeAddAutomatical()
    {
        return $this->scopeConfig->getValue('seo/general/is_hreflang_locale_code_automatical');
    }

    /**
     * @return bool
     */
    public function isHreflangCutCategoryAdditionalData()
    {
        return $this->scopeConfig->getValue('seo/general/is_hreflang_cut_category_additional_data');
    }

    /**
     * @return string|int
     */
    public function getXDefault()
    {
        return $this->scopeConfig->getValue('seo/general/is_hreflang_x_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getStore()->getWebsiteId());
    }

    /**
     * @param string $store
     * @return string
     */
    public function getHreflangLocaleCode($store)
    {
        return trim($this->scopeConfig->getValue(
            'seo/general/hreflang_locale_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    /**
     * @return bool
     */
    public function isPagingPrevNextEnabled()
    {
        return $this->scopeConfig->getValue('seo/general/is_paging_prevnext');
    }

    /**
     * @return bool
     */
    public function isCategoryMetaTagsUsed()
    {
        return $this->scopeConfig->getValue('seo/general/is_category_meta_tags_used');
    }

    /**
     * @return bool
     */
    public function isProductMetaTagsUsed()
    {
        return $this->scopeConfig->getValue('seo/general/is_product_meta_tags_used');
    }

    /**
     * @param string $store
     * @return int
     */
    public function getMetaTitlePageNumber($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_title_page_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getMetaDescriptionPageNumber($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_description_page_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getMetaTitleMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_title_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getMetaDescriptionMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_description_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getProductNameMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/product_name_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getProductShortDescriptionMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/product_short_description_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * SEO URL
     *
     * @return bool
     */
    public function isEnabledSeoUrls()
    {
        return $this->scopeConfig->getValue('seo/url/layered_navigation_friendly_urls');
    }

    /**
     * @return int
     */
    public function getTrailingSlash()
    {
        return $this->scopeConfig->getValue('seo/url/trailing_slash');
    }

    /**
     * @return int
     */
    public function getProductUrlFormat()
    {
        return $this->scopeConfig->getValue('seo/url/product_url_format');
    }

    /**
     * @param string $store
     * @return string
     */
    public function getProductUrlKey($store)
    {
        return $this->scopeConfig->getValue(
            'seo/url/product_url_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|null $storeId
     * @param int|null  $websiteId
     * @return bool
     */
    public function isEnabledRemoveParentCategoryPath($storeId = null, $websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                'seo/url/use_category_short_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            'seo/url/use_category_short_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

    }

    /**
     * IMAGE.
     *
     * @return string
     */
    public function getIsEnableImageFriendlyUrls()
    {
        return $this->scopeConfig->getValue('seo/image/is_enable_image_friendly_urls');
    }

    /**
     * @return string
     */
    public function getImageUrlTemplate()
    {
        return $this->scopeConfig->getValue('seo/image/image_url_template');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsEnableImageAlt()
    {
        return $this->scopeConfig->getValue('seo/image/is_enable_image_alt');
    }

    /**
     * @return string
     */
    public function getImageAltTemplate()
    {
        return $this->scopeConfig->getValue('seo/image/image_alt_template');
    }

    /**
     * INFO
     *
     * @param null $storeId
     * @return bool
     */
    public function isInfoEnabled($storeId = null)
    {
        if (!$this->_isInfoAllowed()) {
            return false;
        }

        return $this->scopeConfig->getValue(
            'seo/info/info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isShowAltLinkInfo($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'seo/info/alt_link_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isShowTemplatesRewriteInfo($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'seo/info/templates_rewrite_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    protected function _isInfoAllowed($storeId = null)
    {
        $info = $this->scopeConfig->getValue(
            'seo/info/info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (($info == self::INFO_COOKIE)
            && $this->cookie->isCookieExist()) {
                return true;
        } elseif ($info == self::INFO_IP) {
            $ips = $this->scopeConfig->getValue(
                'seo/info/allowed_ip',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($ips == '') {
                return true;
            }
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                return false;
            }
            $ips = explode(',', $ips);
            $ips = array_map('trim', $ips);

            return in_array($_SERVER['REMOTE_ADDR'], $ips);
        }

        return false;
    }

    /**
     * Rich Snippets and Opengraph.
     * Product
     */

    /**
     * Change default magento snippets
     *
     * @return bool
     */
    public function isForceProductSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/force_product_snippets');
    }

    /**
     * @return int
     */
    public function getRichSnippetsDescription()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_item_description');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsItemImage()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_item_image');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsItemAvailability()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_item_availability');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsPaymentMethod()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_payment_method');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsDeliveryMethod()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_delivery_method');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsProductCategory()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_product_category');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsManufacturerPartNumber()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_manufacturer_part_number');
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsBrandAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_brand_config'
        ));
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsModelAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_model_config'
        ));
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsColorAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_color_config'
        ));
    }

    /**
     * @return string
     */
    public function getRichSnippetsWeightCode()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_weight_config');
    }

    /**
     * @return bool
     */
    public function isEnabledRichSnippetsDimensions()
    {
        return $this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_dimensions_config');
    }

    /**
     * @return string
     */
    public function getRichSnippetsDimensionUnit()
    {
        return trim($this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_dimensional_unit'));
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsHeightAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_height_config'
        ));
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsWidthAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_width_config'
        ));
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsDepthAttributes()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_depth_config'
        ));
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRichSnippetsCondition()
    {
        return $this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_product_condition_config'
        );
    }

    /**
     * @return array|string
     */
    public function getRichSnippetsConditionAttribute()
    {
        return $this->_prepereAttributes($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_product_condition_attribute'
        ));
    }

    /**
     * @return string
     */
    public function getRichSnippetsNewConditionValue()
    {
        return trim($this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_product_condition_new'));
    }

    /**
     * @return string
     */
    public function getRichSnippetsUsedConditionValue()
    {
        return trim($this->scopeConfig->getValue('seo_snippets/product_snippets/rich_snippets_product_condition_used'));
    }

    /**
     * @return string
     */
    public function getRichSnippetsRefurbishedConditionValue()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_product_condition_refurbished'
        ));
    }

    /**
     * @return string
     */
    public function getRichSnippetsDamagedConditionValue()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/product_snippets/rich_snippets_product_condition_damaged'
        ));
    }

    /**
     * @param string $attributes
     * @return array|string
     */
    protected function _prepereAttributes($attributes)
    {
        $attributes = strtolower(trim($attributes));
        $attributes = explode(',', trim($attributes));
        $attributes = array_map('trim', $attributes);
        $attributes = array_diff($attributes, [null]);

        return $attributes;
    }

    /**
     * Category
     *
     * @param string $store
     * @return int
     */
    public function getCategoryRichSnippets($store)
    {
        return $this->scopeConfig->getValue(
            'seo_snippets/category_snippets/category_rich_snippets',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $store
     * @return int
     */
    public function getRichSnippetsRewiewCount($store)
    {
        return $this->scopeConfig->getValue(
            'seo_snippets/category_snippets/category_rich_snippets_review_count',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Organization
     *
     * @return bool
     */
    public function isOrganizationSnippetsEnabled()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/is_organization_snippets');
    }

    /**
     * @return string
     */
    public function getNameOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/name_organization_snippets');
    }

    /**
     * @return string
     */
    public function getManualNameOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_name_organization_snippets'
        ));
    }

    /**
     * @return int
     */
    public function getCountryAddressOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/country_address_organization_snippets');
    }

    /**
     * @return string
     */
    public function getManualCountryAddressOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_country_address_organization_snippets'
        ));
    }

    /**
     * @return int
     */
    public function getLocalityAddressOrganizationSnippets()
    {
        return $this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/locality_address_organization_snippets'
        );
    }

    /**
     * @return string
     */
    public function getManualLocalityAddressOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_locality_address_organization_snippets'
        ));
    }

    /**
     * @return string
     */
    public function getPostalCodeOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/postal_code_organization_snippets'
        ));
    }

    /**
     * @return string
     */
    public function getManualPostalCodeOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_postal_code_organization_snippets'
        ));
    }

    /**
     * @return int
     */
    public function getStreetAddressOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/street_address_organization_snippets');
    }

    /**
     * @return string
     */
    public function getManualStreetAddressOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_street_address_organization_snippets'
        ));
    }

    /**
     * @return int
     */
    public function getTelephoneOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/telephone_organization_snippets');
    }

    /**
     * @return string
     */
    public function getManualTelephoneOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_telephone_organization_snippets'
        ));
    }

    /**
     * @return string
     */
    public function getManualFaxnumberOrganizationSnippets()
    {
        return trim($this->scopeConfig->getValue(
            'seo_snippets/organization_snippets/manual_faxnumber_organization_snippets'
        ));
    }

    /**
     * @return int
     */
    public function getEmailOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/email_organization_snippets');
    }

    /**
     * @return int
     */
    public function getManualEmailOrganizationSnippets()
    {
        return $this->scopeConfig->getValue('seo_snippets/organization_snippets/manual_email_organization_snippets');
    }

    /**
     * Rich Snippets Breadcrumbs
     *
     * @param string $store
     * @return int
     */
    public function getBreadcrumbs($store)
    {
        return $this->scopeConfig->getValue(
            'seo_snippets/breadcrumbs_snippets/is_breadcrumbs',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Opengraph
     *
     * @return int
     */
    public function getCategoryOpenGraph()
    {
        return $this->scopeConfig->getValue('seo_snippets/opengraph/is_category_opengraph');
    }

    /**
     * @return bool
     */
    public function isCmsOpenGraphEnabled()
    {
        return $this->scopeConfig->getValue('seo_snippets/opengraph/is_cms_opengraph');
    }


    /**
     * Check if "Use Categories Path for Product URLs" enabled
     *
     * @param int $storeId
     * @return bool
     */
    public function isProductLongUrlEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
                   \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                   $storeId
               );
    }
}
