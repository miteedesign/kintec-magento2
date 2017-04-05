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



namespace Mirasvit\SeoAutolink\Block\Adminhtml\Link\Edit;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Mirasvit\SeoAutolink\Model\Config\Source\Urltarget
     */
    protected $configSourceUrltarget;

    /**
     * @var \Mirasvit\SeoAutolink\Model\Config\Source\Occurence
     */
    protected $configSourceOccurence;

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
     * @param \Mirasvit\SeoAutolink\Model\Config\Source\Urltarget $configSourceUrltarget
     * @param \Mirasvit\SeoAutolink\Model\Config\Source\Occurence $configSourceOccurence
     * @param \Magento\Store\Model\System\Store                   $systemStore
     * @param \Magento\Framework\Data\FormFactory                 $formFactory
     * @param \Magento\Framework\Registry                         $registry
     * @param \Magento\Backend\Block\Widget\Context               $context
     * @param array                                               $data
     */
    public function __construct(
        \Mirasvit\SeoAutolink\Model\Config\Source\Urltarget $configSourceUrltarget,
        \Mirasvit\SeoAutolink\Model\Config\Source\Occurence $configSourceOccurence,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->configSourceUrltarget = $configSourceUrltarget;
        $this->configSourceOccurence = $configSourceOccurence;
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');

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
            $fieldset->addField('link_id', 'hidden', [
                'name' => 'link_id',
                'value' => $model->getId(),
            ]);
        }

        $fieldset->addField('keyword', 'text', [
            'name' => 'keyword',
            'label' => __('Keyword'),
            'required' => true,
            'value' => $model->getKeyword(),
        ]);

        $fieldset->addField('url', 'text', [
            'name' => 'url',
            'label' => __('URL'),
            'required' => true,
            'value' => $model->getUrl(),
        ]);

        $fieldset->addField('url_target', 'select', [
            'label' => __('URL Target'),
            'name' => 'url_target',
            'values' => $this->configSourceUrltarget->toOptionArray(),
            'value' => $model->getUrlTarget(),
        ]);

        $fieldset->addField('url_title', 'text', [
            'name' => 'url_title',
            'label' => __('URL Title'),
            'required' => false,
            'value' => $model->getUrlTitle(),
        ]);

        $fieldset->addField('is_nofollow', 'select', [
            'name' => 'is_nofollow',
            'label' => __('Nofollow'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
            'value' => $model->getIsNofollow(),
        ]);

        $fieldset->addField('max_replacements', 'text', [
            'name' => 'max_replacements',
            'label' => __('Number of substitutions'),
            'value' => (int) $model->getMaxReplacements(),
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name' => 'sort_order',
            'label' => __('Sort order'),
            'value' => (int) $model->getSortOrder(),
        ]);

        $fieldset->addField('occurence', 'select', [
            'name' => 'occurence',
            'label' => __('Occurence'),
            'values' => $this->configSourceOccurence->toOptionArray(),
            'value' => $model->getOccurence(),
        ]);

        $fieldset->addField('is_active', 'select', [
            'name' => 'is_active',
            'label' => __('Status'),
            'options' => ['1' => __('Active'), '0' => __('Inactive')],
            'value' => $model->getIsActive(),
        ]);
        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $fieldset->addField('active_from', 'date', [
            'label' => __('Active From'),
            'required' => false,
            'name' => 'active_from',
            'value' => $model->getActiveFrom(),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'date_format' => $dateFormat,
        ]);
        $fieldset->addField('active_to', 'date', [
            'label' => __('Active To'),
            'required' => false,
            'name' => 'active_to',
            'value' => $model->getActiveTo(),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'date_format' => $dateFormat,
        ]);

        /**
         * Check is single store mode.
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
