<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class Stop extends AbstractProfile
{

    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                throw new \Exception("INVALID ID");
            }
            $this->_profile->load($id)->factory()->stop()->save();
            $this->messageManager->addSuccess(__('Profile stopped successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', ['_current' => true]);
    }
}
