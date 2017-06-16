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
 * @version   1.0.58
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Controller\Adminhtml\Template;

class Save extends \Mirasvit\Seo\Controller\Adminhtml\Template
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $data['sort_order'] = (isset($data['sort_order']) &&
                trim($data['sort_order']) != '') ? (int) trim($data['sort_order']) : 10;
            $data = $this->prepareStoreIds($data);
            $model = $this->_initModel();
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('Item was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

     /**
     * @param array $data
     * @return array
     */
    protected function prepareStoreIds($data) {
        if (isset($data['store_ids'])
            && count($data['store_ids']) > 1
            && in_array(0, $data['store_ids'])) {
            $data['store_ids'] = array(0);
        }

        return $data;
    }
}
