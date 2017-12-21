<?php
namespace Dotdigitalgroup\Email\Model\Mail\Transport;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Model\Mail\Transport
 */
class Interceptor extends \Dotdigitalgroup\Email\Model\Mail\Transport implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Zend_Mail_Transport_Sendmail $sendmail, \Magento\Framework\Mail\MessageInterface $message, \Dotdigitalgroup\Email\Helper\Transactional $helper)
    {
        $this->___init();
        parent::__construct($sendmail, $message, $helper);
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'sendMessage');
        if (!$pluginInfo) {
            return parent::sendMessage();
        } else {
            return $this->___callPlugins('sendMessage', func_get_args(), $pluginInfo);
        }
    }
}
