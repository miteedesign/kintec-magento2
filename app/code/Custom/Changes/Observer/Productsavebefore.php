<?php

namespace Custom\Changes\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$attr = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('colour');
        $_product = $observer->getProduct();  // you will get product object

        if($_product->getTypeId()=='configurable'){
        	if(!is_null($_product->getColor())){
        		$_product->setColor('');
        	}
        	
        }
        else
        {
        	if($_product->getColor()==''){
        		$colour = $attr->getSource()->getOptionText($_product->getColour());

		        $colors = [
				 'red'=>'Red',
				 'pink'=>'Pink',
				 'orange'=>'Orange',
				 
				 'purple'=>'Purple',
				 'grey'=>'Grey',
				 'green'=>'Green',
				 'blue'=>'Blue',
				 
				 'silver'=>'Silver',
				 'yellow'=>'Yellow',
				 'brown'=>'Brown',
				 'maroon'=>'Maroon',
				 'violet'=>'Violet',
				 'chocolate'=>'Chocolate',
				 'orchid'=>'Orchid',
				 'gold'=>'Gold',
				 'black'=>'Black',
				 'white'=>'White',
				];
				$productColor = 'white';
				foreach($colors as $color=>$code){
					if(strpos(strtolower($colour),$color)!==false){
						$productColor = $color;

						break;
					}
				}

				$attr1 = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('color');
		 
				foreach($colors as $color=>$code){
					$id = $attr1->getSource()->getOptionId($code);
					$colors[$color] = $id;
				}
		        $_product->setColor($colors[$productColor]);
        	}
	        
	    }
        //var_dump($_product->getData());die;
        //$_sku=$_product->getSku(); // for sku
        $brandAttr = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('brand');
        $brand = $brandAttr->getSource()->getOptionText($_product->getBrand());
		$model = $_product->getName();
		$brand = $brand ? $brand : '';
        $title = "{$model}, {$brand}. | Kintec";
		$keyword = "{$model}, {$brand}";
		$description = "Buy {$model} online at Kintec Footwear + Orthotics. Huge selection of shoes and accessories. Free shipping on orders over $99.";
		$_product->setMetaTitle($title);
		if(is_null($_product->getMetaKeyword()) || $_product->getMetaKeyword()==''){
			$_product->setMetaKeyword($keyword);
		}
		$_product->setMetaDescription($description);

    }   
}