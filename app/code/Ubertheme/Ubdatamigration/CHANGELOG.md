3.0.2
=====
* Fix and Improvement:
    * (Fine-tuning): Notification improvement for Command-line interface
    * (Fine-tuning): rename the log file to 'ub_data_migration.log'
    * Fixed: applied tweaks for some special database:
        - https://www.ubertheme.com/question/upgrading-ub-data-migration-pro-error/
        - https://www.ubertheme.com/question/migration-tool-pro-sales-orders/
        - https://www.ubertheme.com/question/sales-import-not-completing/
        
3.0.1
=====
* Fix and Improvement:
    * Tested compatible with Magento CE 2.1.2
    * Supported CLI commands:  After done needed settings for each step, Users can migrate data by run command lines in CLI mode. (Solved issue #4)
    * Handle some special case: 
        * https://www.ubertheme.com/question/compatibility-2/
        * https://www.ubertheme.com/question/cdbexception-at-step-1/
        * https://www.ubertheme.com/question/i-am-getting-an-error-while-importing-products/
    
3.0.0
=====
* Improve solution to migrate data:
    * Support incremental data migration
    * Improvement UI/UX and performance of data migration: Only with 8 steps to migrate your key data objects.
    * Used ajax requests in all steps of data migration 
    * Solved all issues in versions 2.x: Security issue...
    * Don't required high performance web server to migrate data, Easy migrate data with big volume.
    * Don't use SQLite from versions 3.x

2.0.6
=====
* Fix issues: 
    * https://www.ubertheme.com/question/getting-errors-after-data-migration/
* Tuning to improve performance: 
    * Improve way to migrate attribute sets, attribute groups. (step 3)
    * Improve categories listing in form (step 4)
    * Improve and tuning Products Stock data migration (step 5)
* Handle for all redirect types of Categories and Products Rewrite Urls: No redirect, 301, 302 (step 4, step 5)
* Tested compatible with Magento CE 2.1.1

2.0.5
=====
* Fix issues: 
    * https://www.ubertheme.com/question/error-while-migrating-sales/
    * https://www.ubertheme.com/question/getting-error-in-migrating-customers/
    * https://www.ubertheme.com/question/404-page/
     
2.0.4
=====
* Allow migrate custom product tax classes
* Fixed issue not found when access this tool in back-end after install module successfully in some case. 

2.0.3
=====
* Upgrade compatible with CE 2.1.0, some tables was change data structure: (Compared CE 2.1.0 vs CE 2.0.x)
    * `eav_attribute`: http://i.prntscr.com/7c14c90a6ace46e39accdb4020d1db89.png (initial attributes with fresh installation)
    * `eav_attribute_group`: http://i.prntscr.com/1899719ba3c245468b5cc86c81a8c4b5.png
    * `eav_entity_attribute`: http://i.prntscr.com/adda815076c346b0b8b080c28ca4a64c.png
    * `catalog_category_product`: http://i.prntscr.com/44187be28d784930ad4f8b30cf68e566.png
    * `catalogrule`: http://i.prntscr.com/f5985929f291424f9803b578d058a19f.png
    * `catalogrule_product`: http://i.prntscr.com/cef4fb6b5997477fa850be99cbcd3892.png
    * `customer_entity`: http://i.prntscr.com/b520b54d3dbd41359803189a59e49f11.png
    * `sales_invoice_grid`: http://i.prntscr.com/97e8379ef3a44dd28409915857169b1c.png
    * Some tables in sales data structure was remove CONSTRAINT:
          * `sales_bestsellers_aggregated_daily, sales_bestsellers_aggregated_monthly, sales_bestsellers_aggregated_yearly`: http://i.prntscr.com/70d0d47dcd2147e4be956420409ed012.png
  
2.0.2
=====
* Improvement and fixed bugs:
    * Tuning to support Nginx server.
    * Fixed bugs:https://www.ubertheme.com/question/ub-dm-pro-error/
    
2.0.1
=====
* Improvement and fixed bugs:
    * Fixed bugs: 
        * Issue #1: https://bitbucket.org/joomsolutions/ub-module-ubdatamigration-pro/issues/1/issue-with-sales_order_status_label-model
    * Allow convert `group_price` data to `tier_price` data
        * Issue: #2: https://bitbucket.org/joomsolutions/ub-module-ubdatamigration-pro/issues/2/issue-with-group-price
    * Tuning and improve performance
    * Tested compatible with Magento CE 2.0.6, CE 2.0.7

2.0.0
=====
* First released
