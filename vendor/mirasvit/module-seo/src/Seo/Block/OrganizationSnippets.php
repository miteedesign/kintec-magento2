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
 * @version   1.0.51
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Block;

class OrganizationSnippets extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeLists;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @param \Mirasvit\Seo\Model\Config                       $config
     * @param \Magento\Framework\Locale\ListsInterface         $localeLists
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mirasvit\Seo\Helper\Data                        $seoData
     * @param array                                            $data
     */
    public function __construct(
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\View\Element\Template\Context $context,
        \Mirasvit\Seo\Helper\Data $seoData,
        array $data = []
    ) {
        $this->config = $config;
        $this->localeLists = $localeLists;
        $this->assetRepo = $context->getAssetRepository();
        $this->context = $context;
        $this->seoData = $seoData;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isOrganizationSnippets()
    {
        return $this->config->isOrganizationSnippetsEnabled();
    }

    /**
     * @return bool|string
     */
    public function getName()
    {
        if ($this->config->getNameOrganizationSnippets()) {
            $name = $this->config->getManualNameOrganizationSnippets();
        } else {
            $name = trim($this->context->getScopeConfig()->getValue('general/store_information/name'));
        }

        if ($name) {
            return "\"name\" : \"$name\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getCountryAddress()
    {
        if ($this->config->getCountryAddressOrganizationSnippets()) {
            $countryAddress = $this->config->getManualCountryAddressOrganizationSnippets();
        } else {
            $countryAddress = trim($this->localeLists
                ->getCountryTranslation($this->context
                        ->getScopeConfig()
                        ->getValue('general/store_information/country_id')));
        }

        if ($countryAddress) {
            return "\"addressCountry\": \"$countryAddress\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getAddressLocality()
    {
        if ($this->config->getLocalityAddressOrganizationSnippets()) {
            $addressLocality = $this->config->getManualLocalityAddressOrganizationSnippets();
        } else {
            $addressLocality = trim($this->context->getScopeConfig()->getValue('general/store_information/city'));
        }

        if ($addressLocality) {
            return "\"addressLocality\": \"$addressLocality\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getPostalCode()
    {
        if ($this->config->getPostalCodeOrganizationSnippets()) {
            $postalCode = $this->config->getManualPostalCodeOrganizationSnippets();
        } else {
            $postalCode = trim($this->context->getScopeConfig()->getValue('general/store_information/postcode'));
        }

        if ($postalCode) {
            return "\"postalCode\": \"$postalCode\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getStreetAddress()
    {
        if ($this->config->getStreetAddressOrganizationSnippets()) {
            $streetAddress = $this->config->getManualStreetAddressOrganizationSnippets();
        } else {
            $streetAddress = trim($this->context->getScopeConfig()->getValue('general/store_information/street_line1').
                ' '.$this->context->getScopeConfig()->getValue('general/store_information/street_line2'));
        }

        if ($streetAddress) {
            return "\"streetAddress\": \"$streetAddress\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getTelephone()
    {
        if ($this->config->getTelephoneOrganizationSnippets()) {
            $telephone = $this->config->getManualTelephoneOrganizationSnippets();
        } else {
            $telephone = trim($this->context->getScopeConfig()->getValue('general/store_information/phone'));
        }

        if ($telephone) {
            return "\"telephone\": \"$telephone\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getFaxNumber()
    {
        if ($faxNumber = $this->config->getManualFaxnumberOrganizationSnippets()) {
            return "\"faxNumber\": \"$faxNumber\",";
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getEmail()
    {
        if ($this->config->getEmailOrganizationSnippets()) {
            $email = $this->config->getManualEmailOrganizationSnippets();
        } else {
            $email = trim($this->context->getScopeConfig()->getValue('trans_email/ident_general/email'));
        }

        if ($email) {
            return "\"email\" : \"$email\",";
        }

        return false;
    }

    /**
     * @param string $countryAddress
     * @param string $addressLocality
     * @param string $postalCode
     * @param string $streetAddress
     * @return string
     */
    public function preparePostalAddress($countryAddress, $addressLocality, $postalCode, $streetAddress)
    {
        $postalAddress = $countryAddress.$addressLocality.$postalCode.$streetAddress;
        if ($postalAddress && substr($postalAddress, -1) == ',') {
            $postalAddress = substr($postalAddress, 0, -1);
        }

        return $postalAddress;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->seoData->getLogoUrl();
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->context->getUrlBuilder()->getBaseUrl();
    }
}
