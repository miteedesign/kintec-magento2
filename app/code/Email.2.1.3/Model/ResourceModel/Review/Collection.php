<?php

namespace Dotdigitalgroup\Email\Model\ResourceModel\Review;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection.
     */
    public function _construct()
    {
        $this->_init('Dotdigitalgroup\Email\Model\Review',
            'Dotdigitalgroup\Email\Model\ResourceModel\Review');
    }
}
