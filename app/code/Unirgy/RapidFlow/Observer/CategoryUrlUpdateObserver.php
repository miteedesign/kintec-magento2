<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CategoryUrlUpdateObserver implements ObserverInterface
{
    /**
     * @var \Unirgy\RapidFlow\Helper\Data
     */
    protected $helper;

    /**
     * CategoryUrlUpdateObserver constructor.
     * @param \Unirgy\RapidFlow\Helper\Data $helper
     */
    public function __construct(\Unirgy\RapidFlow\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->helper->updateCategoriesUrlRewrites();
    }
}
