<?php

namespace Dotdigitalgroup\Email\Plugin;

class CustomerManagement
{
    /**
     * @var \Dotdigitalgroup\Email\Model\Automation
     */
    protected $_automation;

    /**
     * NewAutomation constructor.
     *
     * @param \Dotdigitalgroup\Email\Model\Automation $automation
     */
    public function __construct(
        \Dotdigitalgroup\Email\Model\Automation $automation
    ) {
        $this->_automation = $automation;
    }

    /**
     * Plugin for create function.
     *
     * @param \Magento\Sales\Model\Order\CustomerManagement $subject
     * @param $customer
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function afterCreate(\Magento\Sales\Model\Order\CustomerManagement $subject, $customer)
    {
        //New Automation enrolment to queue
        $this->_automation->newCustomerAutomation($customer);
        return $customer;
    }
    //codingStandardsIgnoreEnd
}