<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;


use Unirgy\RapidFlow\Model\Profile;

class Delete extends AbstractProfile
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = $this->_profile;

                $model->setId($id)
                    ->delete();

                $this->messageManager->addSuccess(__('Profile was successfully deleted'));
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->_redirect('*/*/edit', ['id' => $id]);
            }
        }
        return $this->_redirect('*/*/');
    }
}
