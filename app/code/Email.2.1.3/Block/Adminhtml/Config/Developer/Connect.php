<?php

namespace Dotdigitalgroup\Email\Block\Adminhtml\Config\Developer;

class Connect extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_buttonLabel = 'Connect';

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_sessionModel;


    /**
     * @var \Dotdigitalgroup\Email\Helper\Config
     */
    protected $_configHelper;

    /**
     * Connect constructor.
     *
     * @param \Dotdigitalgroup\Email\Helper\Data      $helper
     * @param \Magento\Backend\Model\Auth             $auth
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Dotdigitalgroup\Email\Helper\Config $configHelper,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\Auth\Session $sessionModel,
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    ) {
        $this->_helper = $helper;
        $this->_configHelper = $configHelper;
        $this->_sessionModel = $sessionModel;

        $this->_auth = $auth;

        parent::__construct($context, $data);
    }

    /**
     * @param $buttonLabel
     *
     * @return $this
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;

        return $this;
    }

    /**
     * Get the button and scripts contents.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @codingStandardsIgnoreStart
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //@codingStandardsIgnoreEnd
        $url = $this->getAuthoriseUrl();
        $ssl = $this->_checkForSecureUrl();
        $disabled = false;
        //disable for ssl missing
        if (!$ssl) {
            $disabled = true;
        }

        $adminUser = $this->_auth->getUser();
        $refreshToken = $adminUser->getRefreshToken();

        $title = ($refreshToken) ? __('Disconnect') : __('Connect');

        $url = ($refreshToken) ? $this->getUrl(
            'dotdigitalgroup_email/studio/disconnect'
        ) : $url;

        return $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )
            ->setType('button')
            ->setLabel(__($title))
            ->setDisabled($disabled)
            ->setOnClick("window.location.href='" . $url . "'")
            ->toHtml();
    }

    /**
     * Check the base url is using ssl.
     * @return $this|bool
     */
    protected function _checkForSecureUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB, true
        );

        if (!preg_match('/https/', $baseUrl)) {
            return false;
        }

        return $this;
    }

    /**
     * Autorisation url for OAUTH.
     *
     * @return string
     */
    public function getAuthoriseUrl()
    {
        $clientId = $this->_scopeConfig->getValue(
            \Dotdigitalgroup\Email\Helper\Config::XML_PATH_CONNECTOR_CLIENT_ID
        );

        //callback uri if not set custom
        $redirectUri = $this->getRedirectUri();
        $redirectUri .= 'connector/email/callback';

        $adminUser = $this->_sessionModel->getUser();
        //query params
        $params = [
            'redirect_uri' => $redirectUri,
            'scope' => 'Account',
            'state' => $adminUser->getId(),
            'response_type' => 'code',
        ];

        $authorizeBaseUrl = $this->_configHelper
            ->getAuthorizeLink();
        $url = $authorizeBaseUrl . http_build_query($params)
            . '&client_id=' . $clientId;

        return $url;
    }
}
