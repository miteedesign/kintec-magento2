<?php

namespace Custom\Changes\Controller\Swatch;


class Cache extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $force = false;
        if($id = $this->getRequest()->getParam('force')){
            $force = $this->getRequest()->getParam('force');
        }
    	if($id = $this->getRequest()->getParam('id')){
           $product = $this->_objectManager->get('Magento\Catalog\Model\Product');
            $product = $product->load($id);
            
            if($product->getTypeId()=='configurable'){
                $renderer = $this->_objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
                $renderer->setProduct($product);
                $renderer->getListSwatchJs($force);
                /*
                $renderer = $this->_objectManager->get('Magento\Swatches\Block\Product\Renderer\Configurable');
                $renderer->setProduct($product);
                $renderer->getListSwatchJs($force);
                */
            }  
        }
        else
        {
            $productCollection = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter('type_id', array('eq' => 'configurable'));
            foreach($productCollection as $product){
                /*if($product->getTypeId()=='configurable')*/{
                    //$product->load($product->getId());
                    $renderer = $this->_objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
                    $renderer->setProduct($product);
                    $renderer->getListSwatchJs($force);
                }  
            }
        }
    	
    	
       // return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
