<?php
namespace Fooman\PrintOrderPdf\Controller\Adminhtml\Order\PrintAction;

/**
 * Interceptor class for @see \Fooman\PrintOrderPdf\Controller\Adminhtml\Order\PrintAction
 */
class Interceptor extends \Fooman\PrintOrderPdf\Controller\Adminhtml\Order\PrintAction implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository, \Fooman\PrintOrderPdf\Model\Pdf\OrderFactory $orderPdfFactory, \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        $this->___init();
        parent::__construct($context, $fileFactory, $orderRepository, $orderPdfFactory, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        if (!$pluginInfo) {
            return parent::execute();
        } else {
            return $this->___callPlugins('execute', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
