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



namespace Mirasvit\Seo\Block\Adminhtml\Template;

use Mirasvit\Seo\Model\Config as Config;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Seo\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @param \Mirasvit\Seo\Model\TemplateFactory   $templateFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Helper\Data          $backendHelper
     */
    public function __construct(
        \Mirasvit\Seo\Model\TemplateFactory $templateFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->templateFactory = $templateFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('templateGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->templateFactory->create()
            ->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('template_id', [
            'header' => __('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'template_id',
        ]);

        $this->addColumn('name', [
            'header' => __('Internal rule name'),
            'align'  => 'left',
            'width'  => '150px',
            'index'  => 'name',
        ]);

        $this->addColumn('template_settings', [
            'header'                    => __('Template Settings'),
            'renderer'                  => 'Mirasvit\Seo\Block\Adminhtml\System\TemplateRenderer',
            'filter_condition_callback' => [$this, 'templateSettingsFilter'],
        ]);

        $this->addColumn('rule_type', [
            'header'  => __('Rule type'),
            'align'   => 'left',
            'width'   => '100px',
            'index'   => 'rule_type',
            'type'    => 'options',
            'options' => [
                Config::PRODUCTS_RULE                   => __('Products'),
                Config::CATEGORIES_RULE                 => __('Categories'),
                Config::RESULTS_LAYERED_NAVIGATION_RULE => __('Layered navigation'),
            ],
        ]);

        $this->addColumn('sort_order', [
            'header' => __('Sort Order'),
            'align'  => 'left',
            'width'  => '30px',
            'index'  => 'sort_order',
        ]);

        $this->addColumn('is_active', [
            'header'  => __('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => [
                1 => __('Enabled'),
                0 => __('Disabled'),
            ],
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('template_id');
        $this->getMassactionBlock()->setFormFieldName('template_id');

        $this->getMassactionBlock()->addItem('enable', [
            'label' => __('Enable'),
            'url'   => $this->getUrl('*/*/massEnable'),
        ]);

        $this->getMassactionBlock()->addItem('disable', [
            'label' => __('Disable'),
            'url'   => $this->getUrl('*/*/massDisable'),
        ]);

        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * @param string $collection
     * @param string $column
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function templateSettingsFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $this->getCollection()->getSelect()->where(
            'meta_title like ?
            OR meta_keywords like ?
            OR meta_description like ?
            OR title like ?
            OR description like ?
            OR short_description like ?
            OR full_description like ?',
            "%$value%"
        );

        return $this;
    }
}
