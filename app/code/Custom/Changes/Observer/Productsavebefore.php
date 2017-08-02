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
        $file = fopen(BP.'/logs.log','a');
        fwrite($file,$_product->getTypeId());
        if($_product->getTypeId()=='configurable'){
        	if(!is_null($_product->getColor())){
        		$_product->setColor('');
        	}
        	if($_product->getId()){
        		/*
        		$urlManager = $objectManager->get('Magento\Framework\Url');
	        	$url = $urlManager->getUrl('changes/swatch/cache',['id'=>$_product->getId(),'force'=>1]);
		        $ch = curl_init();
		        fwrite($file,$url);
		        curl_setopt($ch, CURLOPT_URL, $url);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
		        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		        $output = curl_exec($ch);
		        $info = curl_getinfo($ch);
		         fwrite($file,print_r($info,true));
		         fclose($file);
		        curl_close($ch);
		        */
		        $renderer = $objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
                $renderer->setProduct($_product);
                $renderer->getListSwatchJs(true);
                /*
                $renderer = $objectManager->get('Magento\Swatches\Block\Product\Renderer\Configurable');
                $renderer->setProduct($_product);
                $renderer->getListSwatchJs(true); 
                */
        	}
        	
        }
        else
        {
        	if($_product->getId()){
        		$products = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($_product->getId());
        		if(is_array($products)){
        			foreach($products as $productId)
				    {
				    	$product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
				    	if($product->getTypeId()=='configurable'){
				    		$renderer = $objectManager->create('Magento\Swatches\Block\Product\Renderer\Listing\Configurable');
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