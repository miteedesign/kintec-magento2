<?php

namespace Custom\Changes\Controller\Comment;


class Save extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
    	$checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
    	if($this->getRequest()->getParam('comment'))
    		$checkoutSession->setShippingComment($this->getRequest()->getParam('comment'));
    	if($this->getRequest()->getParam('firstname'))
    		$checkoutSession->setPickfirst($this->getRequest()->getParam('firstname'));
    	if($this->getRequest()->getParam('lastname'))
    		$checkoutSession->setPicklast($this->getRequest()->getParam('lastname'));
       // return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
