<?php

namespace Dotdigitalgroup\Email\Model\ResourceModel\Catalog;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection.
     */
    public function _construct()
    {
        $this->_init('Dotdigitalgroup\Email\Model\Catalog',
            'Dotdigitalgroup\Email\Model\ResourceModel\Catalog');
    }
}
