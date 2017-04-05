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



namespace Mirasvit\Seo\Block\Adminhtml\Template;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
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
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'template_id';
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'Mirasvit_Seo';

        $this->buttonList->add('saveandcontinue', [
            'label' => __('Save And Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndContinueEdit',
                        'target' => '#edit_form',
                        'eventData' => ['action' => ['args' => ['auto_apply' => 1]]],
                    ],
                ],
            ],
        ], -100);

        if (!$this->registry->registry('current_template_model') ||
            !$this->registry->registry('current_template_model')->getId()) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('reset');
        }

        return $this;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($this->registry->registry('current_template_model') &&
            $this->registry->registry('current_template_model')->getId()) {
            return __(
                'Edit SEO Template (ID: %1)',
                $this->escapeHtml($this->registry->registry('current_template_model')->getId())
            );
        } else {
            return __('Add SEO Template');
        }
    }
}
