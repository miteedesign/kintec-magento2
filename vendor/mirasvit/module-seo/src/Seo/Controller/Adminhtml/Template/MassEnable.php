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
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Controller\Adminhtml\Template;

class MassEnable extends \Mirasvit\Seo\Controller\Adminhtml\Template
{
    /**
     * @return void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('template_id');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select item(s)'));
        } else {
            $isDisabled = [];
            $isEnabled = [];
            try {
                foreach ($ids as $id) {
                    $model = $this->templateFactory->create()
                        ->load($id)
                        ->setIsActive(true);
                    if ($model->getName()) {
                        $model->save();
                        $isEnabled[] = $id;
                    } else {
                        $isDisabled[] = $id;
                    }
                }
                if (!$isDisabled) {
                    $this->messageManager->addSuccess(
                        __(
                            'Total of %1 record(s) were successfully enabled',
                            count($ids)
                        )
                    );
                } else {
                    if ($isEnabled) {
                        $this->messageManager->addSuccess(
                            __(
                                'Total of %1 record(s) were successfully enabled',
                                count($isEnabled)
                            )
                        );
                    }
                    if ($isDisabled) {
                        $this->messageManager->addError(
                            __(
                                'Total of %1 record(s) were not enabled. Please fill all required fields.',
                                count($isDisabled)
                            )
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
