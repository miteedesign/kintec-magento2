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



namespace Mirasvit\Seo\Block\Adminhtml\Template\Edit\Tab;

use Mirasvit\Seo\Model\Config as Config;

class General extends \Magento\Backend\Block\Widget\Form
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_template_model');
        $form = $this->formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('template_form', [
            'legend' => __('General Information'),
        ]);

        $fieldset->addField('rule_type', 'select', [
            'label'    => __('Rule type'),
            'required' => true,
            'name'     => 'rule_type',
            'onchange' => 'getSelectedValue(this)',
            'value'    => $model->getRuleType(),
            'values'   => [
                Config::PRODUCTS_RULE                   => 'Products',
                Config::CATEGORIES_RULE                 => 'Categories',
                Config::RESULTS_LAYERED_NAVIGATION_RULE => 'Results of layered navigation'
            ],
            'disabled' => $model->getId() ? true : false,
        ]);

        if ($model->getId()) {
            $fieldset->addField('template_id', 'hidden', [
                'name'  => 'template_id',
                'value' => $model->getId(),
            ]);
        }

        switch ($model->getRuleType()) {
            case Config::PRODUCTS_RULE:
                $note = '<b>Template variables</b><br>
                        [product_&lt;product field or attribute&gt;] (e.g. [product_name], [product_price],
                         [product_color]) <br>
                        [category_name], [category_description], [category_url],
                         [category_parent_name], [category_parent_url], <br>
                        [store_name], [store_url], [store_address], [store_phone], [store_email]';
                break;
            case Config::CATEGORIES_RULE:
                $note = '<b>Template variables</b><br>
                        [category_name], [category_description], [category_url], [category_parent_name],
                        [category_parent_url], [category_parent_parent_name], [category_page_title],
                        [store_name], [store_url], [store_address], [store_phone], [store_email]';
                break;
            case Config::RESULTS_LAYERED_NAVIGATION_RULE:
                $note = '<b>Template variables</b><br>
                        [category_name],  [category_description], [category_url],
                         [category_parent_name], [category_parent_url], <br>
                        [filter_selected_options], [filter_named_selected_options]<br>
                        [store_name], [store_url], [store_address], [store_phone], [store_email]';
                break;
            default:
                $note = '';
                break;
        }

        $descriptionNote = 'Will be added in the bottom of the page.';
        if ($model->getRuleType() != Config::PRODUCTS_RULE) {
            $descriptionNote .= '<br/>' . $note;
        }

        if ($model && $model->getId()) {
            $fieldset->addField('name', 'text', [
                'name'     => 'name',
                'label'    => __('Internal rule name'),
                'required' => true,
                'value'    => $model->getName(),
            ]);

            $fieldset->addField('meta_title', 'text', [
                'name'  => 'meta_title',
                'label' => __('Meta title'),
                'value' => $model->getMetaTitle(),
            ]);

            $fieldset->addField('meta_keywords', 'textarea', [
                'name'  => 'meta_keywords',
                'label' => __('Meta keywords'),
                'value' => $model->getMetaKeywords(),
            ]);

            $fieldset->addField('meta_description', 'textarea', [
                'name'  => 'meta_description',
                'label' => __('Meta description'),
                'value' => $model->getMetaDescription(),
            ]);

            $fieldset->addField('title', 'text', [
                'name'  => 'title',
                'label' => __('Title (H1)'),
                'value' => $model->getTitle(),
            ]);

            $fieldset->addField('description_position', 'select', [
                'label'    => __('SEO description position'),
                'required' => false,
                'name'     => 'description_position',
                'onchange' => 'getSelectedValue(this)',
                'value'    => $model->getDescriptionPosition(),
                'values'   => $this->_getDescriptionPositionList($model->getRuleType()),
            ]);

            $fieldset->addField('description', 'textarea', [
                'name'  => 'description',
                'label' => __('SEO description'),
                'value' => $model->getDescription(),
                'note'  => $descriptionNote,
            ]);

            if ($model->getRuleType() == Config::PRODUCTS_RULE) {
                $fieldset->addField('short_description', 'textarea', [
                    'name'  => 'short_description',
                    'label' => __('Product short description'),
                    'value' => $model->getShortDescription(),
                ]);

                $fieldset->addField('full_description', 'textarea', [
                    'name'  => 'full_description',
                    'label' => __('Product description'),
                    'value' => $model->getFullDescription(),
                    'note'  => $note,
                ]);
            }

            $fieldset->addField('is_active', 'select', [
                'name'    => 'is_active',
                'label'   => __('Status'),
                'options' => ['1' => __('Active'), '0' => __('Inactive')],
                'value'   => $model->getIsActive(),
            ]);

            /*
             * Check is single store mode
             */
            if (!$this->context->getStoreManager()->isSingleStoreMode()) {
                $fieldset->addField('store_id', 'multiselect', [
                    'name'     => 'store_ids[]',
                    'label'    => __('Apply for Store View'),
                    'title'    => __('Apply for Store View'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                    'value'    => $model->getStoreIds(),
                ]);
            } else {
                $fieldset->addField('store_id', 'hidden', [
                    'name'  => 'store_ids[]',
                    'value' => $this->context->getStoreManager()->getStore(true)->getId(),
                ]);
                $model->setStoreId($this->context->getStoreManager()->getStore(true)->getId());
            }

            $fieldset->addField('sort_order', 'text', [
                'name'  => 'sort_order',
                'label' => __('Sort Order'),
                'value' => $model->getSortOrder(),
            ]);
        }

        return parent::_prepareForm();
    }

     /**
      * @param int $ruleType
      * @return array
      */
    protected function _getDescriptionPositionList($ruleType)
    {
        switch ($ruleType) {
            case Config::PRODUCTS_RULE:
                $descriptionPositionList = [
                    Config::BOTTOM_PAGE             => 'Bottom of the page',
                    Config::UNDER_SHORT_DESCRIPTION => 'Under Short Description',
                    Config::UNDER_FULL_DESCRIPTION  => 'Under Full Description',
                ];
                break;
            case Config::CATEGORIES_RULE:
            case Config::RESULTS_LAYERED_NAVIGATION_RULE:
               $descriptionPositionList = [
                    Config::BOTTOM_PAGE             => 'Bottom of the page',
                    Config::UNDER_PRODUCT_LIST => 'Under Product List',
                ];
                break;
            default:
                $descriptionPositionList = [];
                break;
        }

        return $descriptionPositionList;
    }
}
