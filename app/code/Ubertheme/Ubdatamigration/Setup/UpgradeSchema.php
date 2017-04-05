<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */

namespace Ubertheme\Ubdatamigration\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $version = $context->getVersion();
        if ($version AND version_compare($version, '3.0.0', '<')) {
            //install library of tool
            UBMigrationSetup::deployLibrary($installer);
            //create needed tables for this tool
            UBMigrationSetup::createTables($installer);
        } elseif ($version AND version_compare($version, '3.0.2', '<')) {
            //back-up current settings
            UBMigrationSetup::backupConfig();
            //deploy new source of lib
            UBMigrationSetup::deployLibrary($installer);
        }

        $installer->endSetup();
    }

}
