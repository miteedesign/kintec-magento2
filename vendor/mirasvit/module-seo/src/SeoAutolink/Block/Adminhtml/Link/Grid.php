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



namespace Mirasvit\SeoAutolink\Block\Adminhtml\Link;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\SeoAutolink\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory
     * @param \Magento\Backend\Block\Widget\Context   $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param array                                   $data
     */
    public function __construct(
        \Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->linkFactory = $linkFactory;
        $this->context = $context;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
        $this->setDefaultSort('link_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /* @var $collection \Mirasvit\SeoAutolink\Model\ResourceModel\Link\Collection */
        $collection = $this->linkFactory->create()
            ->getCollection();
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('link_id', [
            'header' => __('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'link_id',
        ]);

        $this->addColumn('keyword', [
            'header' => __('Keyword'),
            'align'  => 'left',
            'index'  => 'keyword',
        ]);

        $this->addColumn('url', [
            'header' => __('URL'),
            'align'  => 'left',
            'index'  => 'url',
        ]);

        $this->addColumn('sort_order', [
            'header' => __('Sort order'),
            'align'  => 'left',
            'width'  => '80px',
            'index'  => 'sort_order',
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header'                    => __('Store View'),
                'align'                     => 'left',
                'width'                     => '200px',
                'index'                     => 'store_id',
                'type'                      => 'store',
                'store_all'                 => true,
                'store_view'                => true,
                'sortable'                  => false,
                'filter_condition_callback' => [$this, '_filterStoreCondition'],
            ]);
        }

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
        $this->setMassactionIdField('link_id');
        $this->getMassactionBlock()->setFormFieldName('link_id');

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
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject      $column
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
