<?php

namespace Dotdigitalgroup\Email\Controller\Adminhtml\Studio;

class Disconnect extends \Magento\Backend\App\AbstractAction
{
    /**
     * Disconnect and remote the refresh token.
     */
    public function execute()
    {
        try {
            $adminUser = $this->_auth->getUser();

            if ($adminUser->getRefreshToken()) {
                $adminUser->setRefreshToken('')
                    ->save();
            }
            $this->messageManager->addSuccess('Successfully disconnected');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('*/system_config/*');
    }
}
