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



namespace Mirasvit\Seo\Controller\Adminhtml\Rewrite;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\Seo\Controller\Adminhtml\Rewrite
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $_model = $this->rewriteFactory->create();
        $this->registry->register('rewrite_data', $_model);
        $this->registry->register('current_rewrite', $_model);

        $this->_initAction();

        $resultPage->getConfig()->getTitle()->prepend(__('Add Rewrite'));

        $this->_addContent($resultPage->getLayout()->createBlock('\Mirasvit\Seo\Block\Adminhtml\Rewrite\Edit'))
            ->_addLeft($resultPage->getLayout()->createBlock('\Mirasvit\Seo\Block\Adminhtml\Rewrite\Edit\Tabs'));

        return $resultPage;
    }
}
