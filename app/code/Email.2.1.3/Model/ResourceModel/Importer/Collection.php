<?php

namespace Dotdigitalgroup\Email\Model\ResourceModel\Importer;

class Collection extends
    \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection.
     */
    protected function _construct()
    {
        $this->_init('Dotdigitalgroup\Email\Model\Importer', 'Dotdigitalgroup\Email\Model\ResourceModel\Importer');
    }

    /**
     * Reset collection.
     */
    public function reset()
    {
        $this->_reset();
    }
}
