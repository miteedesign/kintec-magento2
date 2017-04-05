<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class Pause extends AbstractProfile
{

    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                throw new \Exception("INVALID ID");
            }
            $this->_profile->load($id)->factory()->pause()->save();
            $this->messageManager->addSuccess(__('Profile paused successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', ['_current'=>true]);
    }
}
