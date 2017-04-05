<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.51
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Controller\Adminhtml\Rewrite;

use Magento\Framework\Controller\ResultFactory;

class MassStatus extends \Mirasvit\Seo\Controller\Adminhtml\Rewrite
{
    /**
     * @return void
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $status = ($status == 1) ? true : false;
        $ids = $this->getRequest()->getParam('rewrite');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select rewrite(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->rewriteFactory->create()
                        ->load($id)
                        ->setIsActive($status);
                    $model->save();
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
