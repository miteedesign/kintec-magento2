<?php

namespace Custom\Changes\Controller\Index;


class Index extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {

    	$product = $this->_objectManager->get('Magento\Catalog\Model\Product');
    	$product = $product->load(221942);
    	var_dump($product->isAvailable());
    	var_dump($product->isSaleable());die;
       // return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
