<?php
namespace Custom\Changes\Controller\Adminhtml\Swatchcache;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Clear extends \Magento\Catalog\Controller\Adminhtml\Product
{
	/**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;

        foreach ($collection->getItems() as $product) {
            if($product->getTypeId()=='configurable'){
                $renderer = $this->_objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
                $renderer->setProduct($product);

                $renderer->getListSwatchJs(true);
                /*
                $renderer = $this->_objectManager->get('Magento\Swatches\Block\Product\Renderer\Configurable');
                $renderer->setProduct($product);
                $renderer->getListSwatchJs($force);
                */
            }
            else{
            	$products = $this->_objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());
        		if(is_array($products)){
        			foreach($products as $productId)
				    {
				    	$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
				    	if($product->getTypeId()=='configurable'){
				    		$renderer = $this->_objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
			                $renderer->setProduct($product);
			                $renderer->getListSwatchJs(true);
			               /* $renderer = $objectManager->get('Magento\Swatches\Block\Product\Renderer\Configurable');
			                $renderer->setProduct($product);
			                $renderer->getListSwatchJs(true); 
			                */
				    	}
				    	
				    }
        		}
            }  
            $count++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 cache(s) have been updated.', $count)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
    }
}