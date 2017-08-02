<?php
namespace Custom\Changes\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;


    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }


    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
            $salesSetup = $this->salesSetupFactory->create(['setup' => $objectManager->get('Magento\Framework\Setup\ModuleDataSetupInterface')]);


            /**
             * Remove previous attributes
             */
            $attributes =       ['pickup_person'];
            foreach ($attributes as $attr_to_remove){
                $salesSetup->removeAttribute(\Magento\Sales\Model\Order::ENTITY,$attr_to_remove);

            }



            /**
             * Add 'shipping_comment' attributes for order
             */
            $options = ['type' => 'text', 'visible' => false, 'required' => false];
            $salesSetup->addAttribute('order', 'pickup_person', $options);

        }

        
        $setup->endSetup();

    }
}