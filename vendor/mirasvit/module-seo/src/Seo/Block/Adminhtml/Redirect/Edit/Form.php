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



namespace Mirasvit\Seo\Block\Adminhtml\Redirect\Edit;

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
        $model = $this->registry->registry('current_redirect_model');

        $form = $this->formFactory->create()->setData([
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $fieldset = $form->addFieldset('general_fieldset', [
            'legend' => __('General'),
            'class' => 'fieldset-wide',
        ]);

        if ($model->getId()) {
            $fieldset->addField('redirect_id', 'hidden', [
                'name' => 'redirect_id',
                'value' => $model->getId(),
            ]);
        }

        $fieldset->addField('url_from', 'text', [
            'name' => 'url_from',
            'label' => __('Request Url'),
            'required' => true,
            'value' => $model->getUrlFrom(),
            'note' => 'Redirect if user opens this URL. E.g. \'/some/old/page\'.<br/>
                            You can use wildcards. E.g. \'/some/old/category/*\'.',
        ]);

        $fieldset->addField('url_to', 'text', [
            'name' => 'url_to',
            'label' => __('Target Url'),
            'required' => true,
            'value' => $model->getUrlTo(),
            'note' => 'Redirect to this URL. E.g. \'/some/new/page/\'.',
        ]);

        $fieldset->addField('is_redirect_only_error_page', 'checkbox', [
            'label' => __('Redirect only if request URL can\'t be found (404)'),
            'name' => 'is_redirect_only_error_page',
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'checked' => $model->getIsRedirectOnlyErrorPage(),
        ]);

        $fieldset->addField('comments', 'textarea', [
            'label' => __('Comments'),
            'name' => 'comments',
            'value' => $model->getComments(),
        ]);

        $fieldset->addField('is_active', 'select', [
            'name' => 'is_active',
            'label' => __('Status'),
            'options' => ['1' => __('Active'), '0' => __('Inactive')],
            'value' => $model->getIsActive(),
        ]);

        /*
         * Check is single store mode
         */
        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', [
                'name' => 'store_ids[]',
                'label' => __('Visible in Store View'),
                'title' => __('Visible in Store View'),
                'required' => true,
                'values' => $this->systemStore->getStoreValuesForForm(false, true),
                'value' => $model->getStoreIds(),
            ]);
        } else {
            $fieldset->addField('store_id', 'hidden', [
                'name' => 'store_ids[]',
                'value' => $this->context->getStoreManager()->getStore(true)->getId(),
            ]);
            $model->setStoreId($this->context->getStoreManager()->getStore(true)->getId());
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
