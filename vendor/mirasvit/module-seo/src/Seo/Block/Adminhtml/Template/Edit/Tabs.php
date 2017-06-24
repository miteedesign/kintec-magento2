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
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Block\Adminhtml\Template\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Backend\Block\Widget\Context    $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session      $authSession
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        $this->jsonEncoder = $jsonEncoder;
        $this->authSession = $authSession;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('template_edit_tabs')
            ->setDestElementId('edit_form')
            ->setTitle(__('Template Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', [
            'label' => __('General Information'),
            'title' => __('General Information'),
            'content' => $this->getLayout()->createBlock(
                '\Mirasvit\Seo\Block\Adminhtml\Template\Edit\Tab\General'
            )->toHtml(),
        ]);

        $model = $this->registry->registry('current_template_model');
        if ($model && $model->getId()) {
            $this->addTab('rule_section', [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    '\Mirasvit\Seo\Block\Adminhtml\Template\Edit\Tab\Rule'
                )->toHtml(),
            ]);
        }

        return parent::_beforeToHtml();
    }
}
