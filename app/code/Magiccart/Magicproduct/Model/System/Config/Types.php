<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-11 23:15:05
 * @@Modify Date: 2016-04-26 17:49:56
 * @@Function:
 */

namespace Magiccart\Magicproduct\Model\System\Config;

class Types implements \Magento\Framework\Option\ArrayInterface
{

    const ALL        = '0';
    const BEST 		 = 'bestseller';
    const FEATURED 	 = 'featured';
    const FEATUREDONE   = 'featuredone';
    const FEATUREDTWO   = 'featuredtwo';
    const LATEST     = 'latest';
    const MOSTVIEWED = 'mostviewed';
    const NEWPRODUCT = 'new';
    const RANDOM 	 = 'random';
    const REVIEW     = 'review';
    const SALE 	     = 'sale';
    const SPECIAL 	 = 'special';

    public function toArray()
    {
        return [
            self::BEST =>   __('Best Seller'),
            self::FEATURED =>   __('Featured Products'),
            self::FEATUREDONE =>   __('Women\'s Styles'),
            self::FEATUREDTWO =>   __('Men\'s Styles'),
            self::LATEST   =>  __('Latest Products'),
            // self::MOSTVIEWED => __('Most Viewed'),
            self::NEWPRODUCT => __('New Products'),
            self::RANDOM   =>   __('Random Products'),
            self::SALE =>   __('Sale Products'),
        ];
    }

    public function toOptionArray()
    {
        return [
            [ 'value' =>  self::BEST, 'label' =>   __('Best Seller') ],
            [ 'value' =>  self::FEATURED, 'label' =>   __('Featured Products') ],
            [ 'value' =>  self::FEATUREDONE, 'label' =>   __('Women\'s Styles') ],
            [ 'value' =>  self::FEATUREDTWO, 'label' =>   __('Men\'s Styles') ],
            [ 'value' =>  self::LATEST, 'label' =>   __('Latest Products') ],
            // [ 'value' =>  self::MOSTVIEWED, 'label' =>   __('Most Viewed') ],
            [ 'value' =>  self::NEWPRODUCT, 'label' =>   __('New Products') ],
            [ 'value' =>  self::RANDOM, 'label' =>   __('Random Products') ],
            [ 'value' =>  self::SALE, 'label' =>   __('Sale Products') ],
        ];
    }

    public function toOptionAll()
    {
        return [
            [ 'value' =>  self::ALL, 'label' =>   __('All') ],
            [ 'value' =>  self::BEST, 'label' =>   __('Best Seller') ],
            [ 'value' =>  self::FEATURED, 'label' =>   __('Featured Products') ],
            [ 'value' =>  self::FEATUREDONE, 'label' =>   __('Women\'s Styles') ],
            [ 'value' =>  self::FEATUREDTWO, 'label' =>   __('Men\'s Styles') ],
            [ 'value' =>  self::LATEST, 'label' =>   __('Latest Products') ],
            // [ 'value' =>  self::MOSTVIEWED, 'label' =>   __('Most Viewed') ],
            [ 'value' =>  self::NEWPRODUCT, 'label' =>   __('New Products') ],
            [ 'value' =>  self::RANDOM, 'label' =>   __('Random Products') ],
            [ 'value' =>  self::SALE, 'label' =>   __('Sale Products') ],
        ];
    }

}
