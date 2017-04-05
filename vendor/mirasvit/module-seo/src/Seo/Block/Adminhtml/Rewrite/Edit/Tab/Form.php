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



namespace Mirasvit\Seo\Block\Adminhtml\Rewrite\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @param \Magento\Store\Model\System\Store     $systemStore
     * @param \Magento\Framework\Data\FormFactory   $formFactory
     * @param \Magento\Framework\Registry           $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('rewrite_data');
        $form = $this->formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rewrite_form', ['legend' => __('General Information')]);
        $fieldset->addField('url', 'text', [
            'label' => __('Pattern of Url or Action name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'url',
            'value' => $model->getUrl(),
            'note' => 'Can be a full action name or a request path. Wildcard allowed.
                    Examples:<br>
                    /customer/account/login/</br>
                    /customer/account/*<br>
                    customer_account_*<br>
                    *?mode=list',
        ]);

        $fieldset->addField('title', 'text', [
            'label' => __('Title'),
            'name' => 'title',
            'value' => $model->getTitle(),
        ]);

        $fieldset->addField('description', 'textarea', [
            'label' => __('Seo Description'),
            'name' => 'description',
            'value' => $model->getDescription(),
        ]);

        $fieldset->addField('meta_title', 'text', [
            'label' => __('Meta Title'),
            'name' => 'meta_title',
            'value' => $model->getMetaTitle(),
        ]);

        $fieldset->addField('meta_keywords', 'textarea', [
            'label' => __('Meta Keywords'),
            'name' => 'meta_keywords',
            'value' => $model->getMetaKeywords(),
        ]);

        $fieldset->addField('meta_description', 'textarea', [
            'label' => __('Meta Description'),
            'name' => 'meta_description',
            'value' => $model->getMetaDescription(),
        ]);

        $fieldset->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'name' => 'is_active',
            'values' => [0 => __('No'), 1 => __('Yes')],
            'value' => $model->getIsActive(),
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', [
                'label' => __('Visible In'),
                'required' => true,
                'name' => 'stores[]',
                'values' => $this->systemStore->getStoreValuesForForm(),
                'value' => $model->getStoreId(),
            ]);
        } else {
            $fieldset->addField('stores', 'hidden', [
                'name' => 'stores[]',
                'value' => $this->context->getStoreManager()->getStore(true)->getId(),
            ]);
        }

        return parent::_prepareForm();
    }
}
