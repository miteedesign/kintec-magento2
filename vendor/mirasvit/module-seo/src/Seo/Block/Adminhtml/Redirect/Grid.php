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



namespace Mirasvit\Seo\Block\Adminhtml\Redirect;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mirasvit\Seo\Model\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Mirasvit\Seo\Model\RedirectFactory   $redirectFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Helper\Data          $backendHelper
     * @param array                                 $data
     */
    public function __construct(
        \Mirasvit\Seo\Model\RedirectFactory $redirectFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->redirectFactory = $redirectFactory;
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
        $this->setId('redirectGrid');
        $this->setDefaultSort('redirect_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->redirectFactory->create()
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
        $this->addColumn('redirect_id', [
                'header' => __('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'redirect_id',
            ]);

        $this->addColumn('url_from', [
                'header' => __('Request Url'),
                'align' => 'left',
                'index' => 'url_from',
            ]);

        $this->addColumn('url_to', [
                'header' => __('Target Url'),
                'align' => 'left',
                'index' => 'url_to',
            ]);

        $this->addColumn('is_redirect_only_error_page', [
                'header' => __('Redirect only if request URL can\'t be found (404)'),
                'align' => 'left',
                'index' => 'is_redirect_only_error_page',
                'type' => 'options',
                'options' => [0 => 'No', 1 => 'Yes'],
                'width' => '280px',
            ]);

        $this->addColumn('comments', [
                'header' => __('Comments'),
                'align' => 'left',
                'index' => 'comments',
            ]);

        $this->addColumn('is_active', [
            'header' => __('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
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
        $this->setMassactionIdField('redirect_id');
        $this->getMassactionBlock()->setFormFieldName('redirect_id');

        $this->getMassactionBlock()->addItem('enable', [
            'label' => __('Enable'),
            'url' => $this->getUrl('*/*/massEnable'),
        ]);

        $this->getMassactionBlock()->addItem('disable', [
            'label' => __('Disable'),
            'url' => $this->getUrl('*/*/massDisable'),
        ]);

        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
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
}
