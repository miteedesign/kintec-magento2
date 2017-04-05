<?php

namespace Dotdigitalgroup\Email\Model\Sync\Td;

class Delete extends \Dotdigitalgroup\Email\Model\Sync\Contact\Delete
{
    /**
     * Sync.
     *
     * @param $collection
     */
    public function sync($collection)
    {
        foreach ($collection as $item) {
            $result = true;
            $websiteId = $item->getWebsiteId();
            if ($this->_helper->isEnabled($websiteId)) {
                $this->_client = $this->_helper->getWebsiteApiClient($websiteId);
                $importData = unserialize($item->getImportData());

                if ($this->_client) {
                    $key = $importData[0];
                    $collectionName = $item->getImportType();
                    $this->_client->deleteContactsTransactionalData($key, $collectionName);
                    $this->_handleSingleItemAfterSync($item, $result);
                }
            }
        }
    }
}
