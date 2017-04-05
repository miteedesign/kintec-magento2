<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Framework\Model\Resource;
use Unirgy\RapidFlow\Model\Profile;

class Resume extends AbstractProfile
{
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                throw new \Exception("INVALID ID");
            }
            $this->_profile->load($id)->factory()->resume()->save();
            $this->messageManager->addSuccess(__('Profile resumed successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', ['_current'=>true]);
    }
}
