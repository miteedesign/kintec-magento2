<?php

namespace Custom\Changes\Model\Sales;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditmemoCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ImportCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection as ShipmentCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection as HistoryCollection;
class Order extends \Magento\Sales\Model\Order
{

	/**
     * Returns shipping_description
     *
     * @return string|null
     */
    public function getShippingDescription()
    {
        $request = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\App\Request\Http');
        if($request->getActionName()=='print'){
           $des = strip_tags($this->getData(OrderInterface::SHIPPING_DESCRIPTION), '<img>');
           if($this->getPickupPerson() && $this->getPickupPerson()!=='')
                $des = $des.',  Pickup Person : '.$this->getPickupPerson().' ';
           if($this->getShippingComment() && $this->getShippingComment()!=='')
                $des = $des.',  Shipping Comment : '.$this->getShippingComment() ;
            

            return $des;
        }
        
        return $this->getData(OrderInterface::SHIPPING_DESCRIPTION) ;//str_replace('<br>',PHP_EOL,  $string);
    }
}