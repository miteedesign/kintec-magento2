<?php

namespace Dotdigitalgroup\Email\Block\Adminhtml\Catalog;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var
     */
    protected $_gridFactory;

    /**
     * @var \Dotdigitalgroup\Email\Model\ResourceModel\Catalog\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var
     */
    protected $imported;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                              $context
     * @param \Magento\Backend\Helper\Data                                         $backendHelper
     * @param \Dotdigitalgroup\Email\Model\ResourceModel\Catalog\CollectionFactory $gridFactory
     * @param \Dotdigitalgroup\Email\Model\Adminhtml\Source\Contact\Imported       $imported
     * @param array                                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Dotdigitalgroup\Email\Model\ResourceModel\Catalog\CollectionFactory $gridFactory,
        \Dotdigitalgroup\Email\Model\Adminhtml\Source\Contact\Imported $imported,
        array $data = []
    ) {
        $this->_collectionFactory = $gridFactory;
        $this->imported = $imported;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare the grid collumns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header' => __('Product ID'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'product_id',
            'type' => 'number',
            'escape' => true,
        ])->addColumn('imported', [
            'header' => __('Imported'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'imported',
            'type' => 'options',
            'escape' => true,
            'renderer' => 'Dotdigitalgroup\Email\Block\Adminhtml\Column\Renderer\Imported',
            'options' => $this->imported->getOptions(),
            'filter_condition_callback' => [$this, 'filterCallbackContact'],
        ])->addColumn('modified', [
            'header' => __('Modified'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'modified',
            'type' => 'options',
            'escape' => true,
            'renderer' => 'Dotdigitalgroup\Email\Block\Adminhtml\Column\Renderer\Imported',
            'options' => $this->imported->getOptions(),
            'filter_condition_callback' => [$this, 'filterCallbackContact'],
        ])->addColumn('created_at', [
            'header' => __('Created At'),
            'width' => '50px',
            'align' => 'center',
            'index' => 'created_at',
            'type' => 'datetime',
            'escape' => true,
        ])->addColumn('updated_at', [
            'header' => __('Updated At'),
            'width' => '50px',
            'align' => 'center',
            'index' => 'updated_at',
            'type' => 'datetime',
            'escape' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Callback action for the imported subscribers/contacts.
     *
     * @param $collection
     * @param $column
     */
    public function filterCallbackContact($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex()
            : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == 'null') {
            $collection->addFieldToFilter($field, ['null' => true]);
        } else {
            $collection->addFieldToFilter($field, ['notnull' => true]);
        }
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        return $this;
    }
}
