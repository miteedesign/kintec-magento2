<?php

namespace Custom\Changes\Controller\Comment;


class Save extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
    	$checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
    	$checkoutSession->setShippingComment($this->getRequest()->getParam('comment'));
       // return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
