<?php

include_once('BaseController.php');

/**
 * @todo: Other data migration
 *
 * Class Step8Controller
 */
class Step8Controller extends BaseController
{
    protected $stepIndex = 8;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //declare objects data to migrate
            $objects = array(
                'review' => ['label' => Yii::t('frontend', 'Reviews')],
                'rating' => [
                    'label' => Yii::t('frontend', 'Rating'),
                    'related' => [
                        //'rating_option' => Yii::t('frontend', 'Rating Option'), // this wil auto migrate with rating
                        'rating_option_vote' => Yii::t('frontend', 'Rating Option Votes'),
                        'rating_option_vote_aggregated' => Yii::t('frontend', 'Rating Option Vote Aggregated')
                    ]
                ],
                'catalog_product_entity_tier_price' => ['label' => Yii::t('frontend', 'Product Tier Prices')],
                'catalog_product_entity_group_price' => ['label' => Yii::t('frontend', 'Product Group Prices')],
                'tax_data' => [
                    'label' => Yii::t('frontend', 'Tax Data'),
                    'related' => [
                        'tax_class' => Yii::t('frontend', 'Tax Classes'),
                        'tax_calculation_rate' => Yii::t('frontend', 'Tax Calculation Rate'),
                        //'tax_calculation_rate_title' => Yii::t('frontend', 'Tax Calculation Rate Title'),
                        'tax_calculation_rule' => Yii::t('frontend', 'Tax Calculation Rules'),
                        //'tax_calculation' => Yii::t('frontend', 'Tax Calculation'), // this will auto migrate with tax_calculation_rule
                        'tax_order_aggregated_created' => Yii::t('frontend', 'Tax Order Aggregated Created'),
                        'tax_order_aggregated_updated' => Yii::t('frontend', 'Tax Order Aggregated Updated')
                    ]
                ],
                'catalogrule' => ['label' => Yii::t('frontend', 'Catalog Rules')],
                'email_template_newsletter' => [
                    'label' => Yii::t('frontend', 'Email Templates and Newsletter Data'),
                    'related' => [
                        'core_email_template' => Yii::t('frontend', 'Email templates'),
                        'newsletter_subscriber' => Yii::t('frontend', 'Newsletter Subscriber'),
                        'newsletter_template' => Yii::t('frontend', 'Newsletter templates'),
                        //below object will be migrate with newsletter_template
                        //'newsletter_queue' => Yii::t('frontend', 'Newsletter Queue'),
                        //'newsletter_problem' => Yii::t('frontend', 'Newsletter Problem'),
                    ]
                ],
                'increment_ids' => ['label' => Yii::t('frontend', 'Update System Increment IDs (EAV Entity Store)')],
            );

            //for Magento CE 1.6.x only
            if (UBMigrate::getMG1Version() == 'mage16x') {
                unset($objects['catalog_product_entity_group_price']);
            }

            if (Yii::app()->request->isPostRequest) {
                //get selected data ids
                $selectAll = Yii::app()->request->getParam('select_all', false);
                $selectedObjects = Yii::app()->request->getParam('objects', array());
                $selectedChildObjects = Yii::app()->request->getParam('child_objects', array());
                if ($selectedObjects) {
                    //make setting data to save
                    $settingData = [
                        'select_all' => $selectAll,
                        'objects' => $selectedObjects,
                        'child_objects' => $selectedChildObjects
                    ];
                    $step->setting_data = serialize($settingData);
                    $step->status = UBMigrate::STATUS_SETTING;

                    //save settings data
                    if ($step->update()) {
                        //alert message
                        Yii::app()->user->setFlash('success', "You have finished all needed settings. Let's start migrate data by your settings.");
                        //go to migrate step
                        $this->redirect(UBMigrate::getStartUrl());
                    }
                } else {
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have not selected any objects to migrate yet.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'objects' => $objects,
            );
            $this->render("setting", $assignData);
        } else {
            Yii::app()->user->setFlash('note', Yii::t('frontend', "The first you need to finish settings in the step #%s.", array("%s" => ($result['back_step_index']))));
            $this->redirect($result['back_step_url']);
        }
    }

    /**
     * @todo: Run Migrate data
     */
    public function actionRun()
    {
        //get current step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $rs = [
            'step_index' => $this->stepIndex,
            'status' => 'fail',
            'message' => '',
            'errors' => '',
            'offset' => 0
        ];

        //check can run migrate data
        $check = $step->canRun();
        if ($check['allowed']) {
            //get mapping websites
            $mappingWebsites = UBMigrate::getMappingData('core_website', 2);
            //get mapping stores
            $mappingStores = UBMigrate::getMappingData('core_store', 2);
            $strStoreIds = implode(',', array_keys($mappingStores));
            //get mapping customer groups
            $mappingCustomerGroups = UBMigrate::getMappingData('customer_group', 6);

            //get setting data
            $settingData = $step->getSettingData();
            $selectedObjects = (isset($settingData['objects'])) ? $settingData['objects'] : [];
            $selectedChildObjects = (isset($settingData['child_objects'])) ? $settingData['child_objects'] : [];

            //some variables for paging
            $max1 = $offset1 = $max2 = $offset2 = $max3 = $offset3 = $max4 = $offset4 = $max5 = $offset5 = 0;
            $max6 = $offset6 = $max7 = $offset7 = $max8 = $offset8 = $max9 = $offset9 = $max10 = $offset10 = 0;
            $max11 = $offset11 = $max12 = $offset12 = $max13 = $offset13 = $max14 = $offset14 = $max15 = $offset15 = 0;
            $max16 = $offset16 = $max17 = $offset17 = 0;

            try {
                if ($selectedObjects) {
                    /**
                     * Migrate reviews
                     * review_status, review_entity -> these tables was not changed
                     */
                    if (in_array('review', $selectedObjects)) {
                        /**
                         * Table: review
                         */
                        $max1 = Mage1Review::model()->count();
                        $offset1 = UBMigrate::getCurrentOffset(8, Mage1Review::model()->tableName());
                        //get list objects by limit and offset
                        $list1 = UBMigrate::getListObjects('Mage1Review', '', $offset1, $this->limit, 'review_id ASC');
                        if ($list1) {
                            $this->_migrateReview($list1, $mappingStores);
                        }

                        /**
                         * Table: review_entity_summary
                         */
                        if ($offset1 >= $max1) { //has finished with review table
                            $condition = "store_id IN ({$strStoreIds}) OR store_id IS NULL";
                            $max2 = Mage1ReviewEntitySummary::model()->count($condition);
                            $offset2 = UBMigrate::getCurrentOffset(8, Mage1ReviewEntitySummary::model()->tableName());
                            //get list objects by limit and offset
                            $list2 = UBMigrate::getListObjects('Mage1ReviewEntitySummary', $condition, $offset2, $this->limit, 'primary_id ASC');
                            if ($list2) {
                                $this->_migrateReviewSummary($list2, $mappingStores);
                            }
                        }

                        //change step status and update log
                        Yii::log(($offset1 == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                        if ($offset1 == 0) {
                            //update status of this step to processing
                            $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                        }
                    }

                    /**
                     * Migrate rating data
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2) ? 1 : 0;
                    if (in_array('rating', $selectedObjects) AND $canRun) {
                        /**
                         * Table: rating
                         */
                        $max3 = Mage1Rating::model()->count();
                        $offset3 = UBMigrate::getCurrentOffset(8, Mage1Rating::model()->tableName());
                        $list3 = UBMigrate::getListObjects('Mage1Rating', '', $offset3, $this->limit, 'rating_id ASC');
                        if ($list3) {
                            $this->_migrateRating($list3, $mappingStores);
                        }

                        /**
                         * Table: rating_option_vote
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3) ? 1 : 0;
                        if (in_array('rating_option_vote', $selectedChildObjects) AND $canRun) {
                            $max4 = Mage1RatingOptionVote::model()->count();
                            $offset4 = UBMigrate::getCurrentOffset(8, Mage1RatingOptionVote::model()->tableName());
                            $list4 = UBMigrate::getListObjects('Mage1RatingOptionVote', '', $offset4, $this->limit, 'vote_id ASC');
                            if ($list4) {
                                $this->_migrateRatingOptionVotes($list4, $mappingStores);
                            }
                        }

                        /**
                         * Table: rating_option_vote_aggregated
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3 AND $offset4 >= $max4) ? 1 : 0;
                        if (in_array('rating_option_vote_aggregated', $selectedChildObjects) AND $canRun) {
                            if (!UBMigrate::getSetting(2, 'select_all_store')) {
                                $strStoreIds = implode(',', array_keys($mappingStores));
                                $condition .= " AND store_id IN ({$strStoreIds})";
                            } else {
                                $condition = null;
                            }
                            $max5 = Mage1RatingOptionVoteAggregated::model()->count($condition);
                            $offset5 = UBMigrate::getCurrentOffset(8, Mage1RatingOptionVoteAggregated::model()->tableName());
                            $list5 = UBMigrate::getListObjects('Mage1RatingOptionVoteAggregated', $condition, $offset5, $this->limit, 'primary_id ASC');
                            if ($list5) {
                                $this->_migrateRatingOptionVoteAggregated($list5, $mappingStores);
                            }
                        }
                    }

                    /**
                     * Migrate tier prices
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3 AND $offset4 >= $max4 AND $offset5 >= $max5) ? 1 : 0;
                    if (in_array('catalog_product_entity_tier_price', $selectedObjects) AND $canRun) {
                        /**
                         * Table: catalog_product_entity_tier_price
                         */
                        //build condition
                        $condition = [];
                        if (!UBMigrate::getSetting(2, 'select_all_website')) {
                            $strWebsiteIds = implode(',', array_keys($mappingWebsites));
                            $condition[] = "website_id IN ({$strWebsiteIds})";
                        }
                        if (!UBMigrate::getSetting(6, 'select_all_customer')) {
                            $strCustomerGroupIds = implode(',', array_keys($mappingCustomerGroups));
                            $condition[] = "customer_group_id IN ({$strCustomerGroupIds})";
                        }
                        if (!UBMigrate::getSetting(5, 'select_all_product')) {
                            $mappingProducts = UBMigrate::getMappingData('catalog_product_entity', 5);
                            $strProductIds = implode(',', array_keys($mappingProducts));
                            $condition[] = "entity_id IN ({$strProductIds})";
                        }
                        $condition = implode(' AND ', $condition);

                        $max6 = Mage1CatalogProductEntityTierPrice::model()->count($condition);
                        $offset6 = UBMigrate::getCurrentOffset(8, Mage1CatalogProductEntityTierPrice::model()->tableName());
                        $list6 = UBMigrate::getListObjects('Mage1CatalogProductEntityTierPrice', $condition, $offset6, $this->limit, 'value_id ASC');
                        if ($list6) {
                            $this->_migrateProductTierPrice($list6, $mappingWebsites);
                        }
                    }

                    /**
                     * Migrate Group Prices
                     * group_price was removed in M2, and we will convert group_price to tier_price
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6) ? 1 : 0;
                    if (in_array('catalog_product_entity_group_price', $selectedObjects) AND $canRun) {
                        /**
                         * Table: catalog_product_entity_group_price
                         */
                        //build condition
                        $condition = [];
                        if (!UBMigrate::getSetting(2, 'select_all_website')) {
                            $strWebsiteIds = implode(',', array_keys($mappingWebsites));
                            $condition[] = "website_id IN ({$strWebsiteIds})";
                        }
                        if (!UBMigrate::getSetting(6, 'select_all_customer')) {
                            $strCustomerGroupIds = implode(',', array_keys($mappingCustomerGroups));
                            $condition[] = "customer_group_id IN ({$strCustomerGroupIds})";
                        }
                        if (!UBMigrate::getSetting(5, 'select_all_product')) {
                            $mappingProducts = UBMigrate::getMappingData('catalog_product_entity', 5);
                            $strProductIds = implode(',', array_keys($mappingProducts));
                            $condition[] = "entity_id IN ({$strProductIds})";
                        }
                        $condition = implode(' AND ', $condition);

                        $max7 = Mage1CatalogProductEntityGroupPrice::model()->count($condition);
                        $offset7 = UBMigrate::getCurrentOffset(8, Mage1CatalogProductEntityGroupPrice::model()->tableName());
                        $list7 = UBMigrate::getListObjects('Mage1CatalogProductEntityGroupPrice', $condition, $offset7, $this->limit, 'value_id ASC');
                        if ($list7) {
                            $this->_migrateProductTierPrice($list7, $mappingWebsites);
                        }
                    }

                    /**
                     * Migrate tax data
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6 AND $offset7 >= $max7) ? 1 : 0;
                    if (in_array('tax_data', $selectedObjects) AND $canRun) {
                        /**
                         * Table: tax_class
                         */
                        if (in_array('tax_class', $selectedChildObjects) AND $canRun) {
                            $max8 = Mage1TaxClass::model()->count();
                            $offset8 = UBMigrate::getCurrentOffset(8, Mage1TaxClass::model()->tableName());
                            $list8 = UBMigrate::getListObjects('Mage1TaxClass', '', $offset8, $this->limit, 'class_id ASC');
                            if ($list8) {
                                $this->_migrateTaxClass($list8);
                            }
                        }
                        /**
                         * Table: tax_calculation_rate
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8) ? 1 : 0;
                        if (in_array('tax_calculation_rate', $selectedChildObjects) AND $canRun) {
                            $max9 = Mage1TaxCalculationRate::model()->count();
                            $offset9 = UBMigrate::getCurrentOffset(8, Mage1TaxCalculationRate::model()->tableName());
                            $list9 = UBMigrate::getListObjects('Mage1TaxCalculationRate', '', $offset9, $this->limit, 'tax_calculation_rate_id ASC');
                            if ($list9) {
                                $this->_migrateTaxCalculationRate($list9, $mappingStores);
                            }
                        }
                        /**
                         * Table: tax_calculation_rule
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9) ? 1 : 0;
                        if (in_array('tax_calculation_rule', $selectedChildObjects) AND $canRun) {
                            $max10 = Mage1TaxCalculationRule::model()->count();
                            $offset10 = UBMigrate::getCurrentOffset(8, Mage1TaxCalculationRule::model()->tableName());
                            $list10 = UBMigrate::getListObjects('Mage1TaxCalculationRule', '', $offset10, $this->limit, 'tax_calculation_rule_id ASC');
                            if ($list10) {
                                $this->_migrateTaxCalculationRule($list10);
                            }
                        }
                        /**
                         * Table: tax_order_aggregated_created
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                            AND $offset10 >= $max10) ? 1 : 0;
                        if (in_array('tax_order_aggregated_created', $selectedChildObjects) AND $canRun) {
                            $condition = '';
                            if (!UBMigrate::getSetting(2, 'select_all_store')) {
                                $strStoreIds = implode(',', array_keys($mappingStores));
                                $condition = "store_id IN ({$strStoreIds})";
                            }
                            $m1Class = 'Mage1TaxOrderAggregatedCreated';
                            $m2Class = 'Mage2TaxOrderAggregatedCreated';
                            $max11 = $m1Class::model()->count($condition);
                            $offset11 = UBMigrate::getCurrentOffset(8, $m1Class::model()->tableName());
                            $list11 = UBMigrate::getListObjects($m1Class, $condition, $offset11, $this->limit);
                            if ($list11) {
                                $this->_migrateTaxOrderAggregated($list11, $m2Class, $mappingStores);
                            }
                        }
                        /**
                         * Table: tax_order_aggregated_updated
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                            AND $offset10 >= $max10 AND $offset11 >= $max11) ? 1 : 0;
                        if (in_array('tax_order_aggregated_updated', $selectedChildObjects) AND $canRun) {
                            $m1Class = 'Mage1TaxOrderAggregatedUpdated';
                            $m2Class = 'Mage2TaxOrderAggregatedUpdated';
                            $max12 = $m1Class::model()->count($condition);
                            $offset12 = UBMigrate::getCurrentOffset(8, $m1Class::model()->tableName());
                            $list12 = UBMigrate::getListObjects($m1Class, $condition, $offset12, $this->limit);
                            if ($list12) {
                                $this->_migrateTaxOrderAggregated($list12, $m2Class, $mappingStores);
                            }
                        }
                    }

                    /**
                     * Migrate catalog rules
                     * Table: catalogrule
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                        AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                        AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12) ? 1 : 0;
                    if (in_array('catalogrule', $selectedObjects) AND $canRun) {
                        $max13 = Mage1Catalogrule::model()->count();
                        $offset13 = UBMigrate::getCurrentOffset(8, Mage1Catalogrule::model()->tableName());
                        $list13 = UBMigrate::getListObjects('Mage1Catalogrule', '', $offset13, $this->limit, 'rule_id ASC');
                        if ($list13) {
                            $this->_migrateCatalogRule($list13, $mappingWebsites, $mappingCustomerGroups);
                        }
                    }
                    /**
                     * Migrate email templates and newsletter data
                     */
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                        AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                        AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12 AND $offset13 >= $max13) ? 1 : 0;
                    if (in_array('email_template_newsletter', $selectedObjects) AND $canRun) {
                        /**
                         * Migrate email_template
                         */
                        if (in_array('core_email_template', $selectedChildObjects) AND $canRun) {
                            $max14 = Mage1EmailTemplate::model()->count();
                            $offset14 = UBMigrate::getCurrentOffset(8, Mage1EmailTemplate::model()->tableName());
                            $list14 = UBMigrate::getListObjects('Mage1EmailTemplate', '', $offset14, $this->limit, 'template_id ASC');
                            if ($list14) {
                                $this->_migrateEmailTemplates($list14);
                            }
                        }
                        /**
                         * Table: newsletter_subscriber
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                            AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12
                            AND $offset13 >= $max13 AND $offset14 >= $max14) ? 1 : 0;
                        if (in_array('newsletter_subscriber', $selectedChildObjects) AND $canRun) {
                            $condition = '';
                            if (!UBMigrate::getSetting(2, 'select_all_store')) {
                                $strStoreIds = implode(',', array_keys($mappingStores));
                                $condition = "store_id IN ({$strStoreIds})";
                            }
                            $max15 = Mage1NewsletterSubscriber::model()->count($condition);
                            $offset15 = UBMigrate::getCurrentOffset(8, Mage1NewsletterSubscriber::model()->tableName());
                            $list15 = UBMigrate::getListObjects('Mage1NewsletterSubscriber', $condition, $offset15, $this->limit, 'subscriber_id ASC');
                            if ($list15) {
                                $this->_migrateNewsletterSubscribers($list15, $mappingStores);
                            }
                        }
                        /**
                         * Table: newsletter_template
                         */
                        $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                            AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                            AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                            AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12
                            AND $offset13 >= $max13 AND $offset14 >= $max14 AND $offset15 >= $max15) ? 1 : 0;
                        if (in_array('newsletter_template', $selectedChildObjects) AND $canRun) {
                            $max16 = Mage1NewsletterTemplate::model()->count();
                            $offset16 = UBMigrate::getCurrentOffset(8, Mage1NewsletterTemplate::model()->tableName());
                            $list16 = UBMigrate::getListObjects('Mage1NewsletterTemplate', '', $offset16, $this->limit, 'template_id ASC');
                            if ($list16) {
                                $this->_migrateNewsletterTemplates($list16, $mappingStores);
                            }
                        }
                    }
                    //system increment ids in eav_entity_store table
                    $canRun = ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                        AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                        AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12
                        AND $offset13 >= $max13 AND $offset14 >= $max14 AND $offset15 >= $max15 AND $offset16 >= $max16) ? 1 : 0;
                    if (in_array('increment_ids', $selectedObjects) AND $canRun) {
                        $condition = '';
                        if (!UBMigrate::getSetting(2, 'select_all_store')) {
                            $strStoreIds = implode(',', array_keys($mappingStores));
                            $condition = "store_id IN ({$strStoreIds})";
                        }
                        $max17 = Mage1EAVEntityStore::model()->count($condition);
                        $offset17 = UBMigrate::getCurrentOffset(8, Mage1EAVEntityStore::model()->tableName());
                        $list17 = UBMigrate::getListObjects('Mage1EAVEntityStore', $condition, $offset17, $this->limit, 'entity_store_id ASC');
                        if ($list17) {
                            $this->_migrateEAVEntityStores($list17, $mappingStores);
                        }
                    }

                } //end migrate data by selected objects

                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    //if all selected data migrated
                    if ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3
                        AND $offset4 >= $max4 AND $offset5 >= $max5 AND $offset6 >= $max6
                        AND $offset7 >= $max7 AND $offset8 >= $max8 AND $offset9 >= $max9
                        AND $offset10 >= $max10 AND $offset11 >= $max11 AND $offset12 >= $max12
                        AND $offset13 >= $max13 AND $offset14 >= $max14 AND $offset15 >= $max15
                        AND $offset16 >= $max16 AND $offset17 >= $max17
                    ) {
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1Review::model()->tableName(), $max1, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1ReviewEntitySummary::model()->tableName(), $max2, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1Rating::model()->tableName(), $max3, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1RatingOptionVote::model()->tableName(), $max4, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1RatingOptionVoteAggregated::model()->tableName(), $max5, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntityTierPrice::model()->tableName(), $max6, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntityGroupPrice::model()->tableName(), $max7, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1TaxClass::model()->tableName(), $max8, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1TaxCalculationRate::model()->tableName(), $max9, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1TaxCalculationRule::model()->tableName(), $max10, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1TaxOrderAggregatedCreated::model()->tableName(), $max11, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1TaxOrderAggregatedUpdated::model()->tableName(), $max12, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1Catalogrule::model()->tableName(), $max13, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1EmailTemplate::model()->tableName(), $max14, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1NewsletterSubscriber::model()->tableName(), $max15, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1NewsletterTemplate::model()->tableName(), $max16, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1EAVEntityStore::model()->tableName(), $max17, $this->stepIndex);

                            //update result to respond
                            $rs['status'] = 'done';
                            $rs['percent_done'] = UBMigrate::getPercentByStatus(UBMigrate::STATUS_FINISHED, [1]);
                            $rs['step_status_text'] = $step->getStepStatusText();
                            $rs['message'] = Yii::t('frontend', 'Step #%s was finished.', array('%s' => $this->stepIndex));
                            Yii::log($rs['message'], 'info', 'ub_data_migration');
                            Yii::log('--------------------', 'info', 'ub_data_migration');
                        }
                    } else {
                        //update current offset for next run
                        if ($max1) {
                            UBMigrate::updateCurrentOffset(Mage1Review::model()->tableName(), ($offset1 + $this->limit), $this->stepIndex);
                            $max = $max1;
                        }
                        if ($max2) {
                            UBMigrate::updateCurrentOffset(Mage1ReviewEntitySummary::model()->tableName(), ($offset2 + $this->limit), $this->stepIndex);
                            $max = $max2;
                        }
                        if ($max3) {
                            UBMigrate::updateCurrentOffset(Mage1Rating::model()->tableName(), ($offset3 + $this->limit), $this->stepIndex);
                            $max = $max3;
                        }
                        if ($max4) {
                            UBMigrate::updateCurrentOffset(Mage1RatingOptionVote::model()->tableName(), ($offset4 + $this->limit), $this->stepIndex);
                            $max = $max4;
                        }
                        if ($max5) {
                            UBMigrate::updateCurrentOffset(Mage1RatingOptionVoteAggregated::model()->tableName(), ($offset5 + $this->limit), $this->stepIndex);
                            $max = $max5;
                        }
                        if ($max6) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntityTierPrice::model()->tableName(), ($offset6 + $this->limit), $this->stepIndex);
                            $max = $max6;
                        }
                        if ($max7) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntityGroupPrice::model()->tableName(), ($offset7 + $this->limit), $this->stepIndex);
                            $max = $max7;
                        }
                        if ($max8) {
                            UBMigrate::updateCurrentOffset(Mage1TaxClass::model()->tableName(), ($offset8 + $this->limit), $this->stepIndex);
                            $max = $max8;
                        }
                        if ($max9) {
                            UBMigrate::updateCurrentOffset(Mage1TaxCalculationRate::model()->tableName(), ($offset9 + $this->limit), $this->stepIndex);
                            $max = $max9;
                        }
                        if ($max10) {
                            UBMigrate::updateCurrentOffset(Mage1TaxCalculationRule::model()->tableName(), ($offset10 + $this->limit), $this->stepIndex);
                            $max = $max10;
                        }
                        if ($max11) {
                            UBMigrate::updateCurrentOffset(Mage1TaxOrderAggregatedCreated::model()->tableName(), ($offset11 + $this->limit), $this->stepIndex);
                            $max = $max11;
                        }
                        if ($max12) {
                            UBMigrate::updateCurrentOffset(Mage1TaxOrderAggregatedUpdated::model()->tableName(), ($offset12 + $this->limit), $this->stepIndex);
                            $max = $max12;
                        }
                        if ($max13) {
                            UBMigrate::updateCurrentOffset(Mage1Catalogrule::model()->tableName(), ($offset13 + $this->limit), $this->stepIndex);
                            $max = $max13;
                        }
                        if ($max14) {
                            UBMigrate::updateCurrentOffset(Mage1EmailTemplate::model()->tableName(), ($offset14 + $this->limit), $this->stepIndex);
                            $max = $max14;
                        }
                        if ($max15) {
                            UBMigrate::updateCurrentOffset(Mage1NewsletterSubscriber::model()->tableName(), ($offset15 + $this->limit), $this->stepIndex);
                            $max = $max15;
                        }
                        if ($max16) {
                            UBMigrate::updateCurrentOffset(Mage1NewsletterTemplate::model()->tableName(), ($offset16 + $this->limit), $this->stepIndex);
                            $max = $max16;
                        }
                        if ($max17) {
                            UBMigrate::updateCurrentOffset(Mage1EAVEntityStore::model()->tableName(), ($offset17 + $this->limit), $this->stepIndex);
                            $max = $max17;
                        }

                        //start calculate percent run ok
                        $totalSteps = UBMigrate::getTotalStepCanRunMigrate();
                        $percentOfOnceStep = (1 / $totalSteps) * 100;
                        $n = ceil($max / $this->limit);
                        $percentUp = ($percentOfOnceStep / 17) / $n;
                        //end calculate percent run ok

                        //update result to respond
                        $rs['status'] = 'ok';
                        $rs['percent_up'] = $percentUp;

                        //build message
                        $msg = ($offset1 == 0) ? 'Migrated data in step #%s ok with' : '(Continued) migrated data in step #%s ok with';
                        $data['%s'] = $this->stepIndex;
                        if (isset($list1) AND $list1) {
                            $msg .= ' %s1 Reviews';
                            $data['%s1'] = sizeof($list1);
                        } elseif (isset($list2) AND $list2) {
                            $msg .= ' %s2 ReviewEntitySummary items.';
                            $data['%s2'] = sizeof($list2);
                        } elseif (isset($list3) AND $list3) {
                            $msg .= ' %s3 Rating items.';
                            $data['%s3'] = sizeof($list3);
                        } elseif (isset($list4) AND $list4) {
                            $msg .= ' %s4 RatingOptionVote items.';
                            $data['%s4'] = sizeof($list4);
                        } elseif (isset($list5) AND $list5) {
                            $msg .= ' %s5 RatingOptionVoteAggregated items.';
                            $data['%s5'] = sizeof($list5);
                        } elseif (isset($list6) AND $list6) {
                            $msg .= ' %s6 CatalogProductEntityTierPrice items.';
                            $data['%s6'] = sizeof($list6);
                        } elseif (isset($list7) AND $list7) {
                            $msg .= ' %s7 CatalogProductEntityGroupPrice items.';
                            $data['%s7'] = sizeof($list7);
                        } elseif (isset($list8) AND $list8) {
                            $msg .= ' %s8 TaxClass items.';
                            $data['%s8'] = sizeof($list8);
                        } elseif (isset($list9) AND $list9) {
                            $msg .= ' %s9 TaxCalculationRate items.';
                            $data['%s9'] = sizeof($list9);
                        } elseif (isset($list10) AND $list10) {
                            $msg .= ' %s10 TaxCalculationRule items.';
                            $data['%s10'] = sizeof($list10);
                        } elseif (isset($list11) AND $list11) {
                            $msg .= ' %s11 TaxOrderAggregatedCreated items.';
                            $data['%s11'] = sizeof($list11);
                        } elseif (isset($list12) AND $list12) {
                            $msg .= ' %s12 TaxOrderAggregatedUpdated items.';
                            $data['%s12'] = sizeof($list12);
                        } elseif (isset($list13) AND $list13) {
                            $msg .= ' %s13 Catalogrule items.';
                            $data['%s13'] = sizeof($list13);
                        } elseif (isset($list14) AND $list14) {
                            $msg .= ' %s14 EmailTemplate items.';
                            $data['%s14'] = sizeof($list14);
                        } elseif (isset($list15) AND $list15) {
                            $msg .= ' %s15 NewsletterSubscriber items.';
                            $data['%s15'] = sizeof($list15);
                        } elseif (isset($list16) AND $list16) {
                            $msg .= ' %s16 NewsletterTemplate items.';
                            $data['%s16'] = sizeof($list16);
                        } elseif (isset($list17) AND $list17) {
                            $msg .= ' %s17 EAVEntityStore items.';
                            $data['%s17'] = sizeof($list17);
                        }
                        $rs['message'] = Yii::t('frontend', $msg, $data);
                        Yii::log($rs['message'], 'info', 'ub_data_migration');
                    }
                }
            } catch (Exception $e) {
                $rs['errors'] = $e->getMessage();
                Yii::log($rs['errors'], 'error', 'ub_data_migration');
            }

        } else {
            if ($step->status == UBMigrate::STATUS_PENDING) {
                $rs['notice'] = Yii::t('frontend', "Step #%s has not settings.", array('%s' => $this->stepIndex));
            } elseif ($step->status == UBMigrate::STATUS_SKIPPING) {
                $rs['status'] = 'done';
                $rs['notice'] = Yii::t('frontend', "Step #%s was skipping.", array('%s' => $this->stepIndex));
            } else {
                if (isset($check['required_finished_step_index'])) {
                    $rs['notice'] = Yii::t('frontend', "Before migrate data in the step #%s1, you have to finished data migration in the step #%s2.", array('%s1' => $step->sorder, '%s2' => $check['required_finished_step_index']));
                }
            }
        }

        //respond result
        if ($this->isCLI) {
            return $rs;
        } else {
            echo json_encode($rs);
            Yii::app()->end();
        }
    }

    private function _migrateReview($models, $mappingStores)
    {
        foreach ($models as $model) {
            $m2Id = UBMigrate::getM2EntityId('8_review', 'review', $model->review_id);
            if (!$m2Id) {
                $model2 = new Mage2Review();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->review_id = null;
                if ($model2->entity_id == 1) { // review of product
                    $model2->entity_pk_value = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model2->entity_pk_value);
                } elseif ($model2->entity_id == 2) { // review of customer
                    $model2->entity_pk_value = UBMigrate::getM2EntityId(6, 'customer_entity', $model2->entity_pk_value);
                } elseif ($model2->entity_id == 3) { // review of category
                    $model2->entity_pk_value = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $model2->entity_pk_value);
                }
                //save
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $model->tableName(),
                        'm1_id' => $model->review_id,
                        'm2_id' => $model2->review_id,
                        'm2_model_class' => get_class($model2),
                        'm2_key_field' => 'review_id',
                        'can_reset' => UBMigrate::RESET_YES,
                        'step_index' => "8Review"
                    ]);
                }
            } else {
                $model2 = Mage2Review::model()->find("review_id = {$m2Id}");
            }
            //migrate related data
            if ($model2->review_id) {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
                /**
                 * Table: review_detail
                 */
                $reviewDetails = Mage1ReviewDetail::model()->findAll("review_id = {$model->review_id}");
                if ($reviewDetails) {
                    foreach ($reviewDetails as $reviewDetail) {
                        $reviewDetail2 = new Mage2ReviewDetail();
                        foreach ($reviewDetail2->attributes as $key => $value) {
                            if (isset($reviewDetail->$key)) {
                                $reviewDetail2->$key = $reviewDetail->$key;
                            }
                        }
                        $reviewDetail2->detail_id = null;
                        $reviewDetail2->review_id = $model2->review_id;
                        $reviewDetail2->store_id = $mappingStores[$reviewDetail->store_id];
                        if ($reviewDetail2->customer_id) {
                            $reviewDetail2->customer_id = UBMigrate::getM2EntityId(6, 'customer_entity', $reviewDetail2->customer_id);
                        }
                        if (!$reviewDetail2->save()) {
                            $this->errors[] = get_class($reviewDetail2) . ": " . UBMigrate::getStringErrors($reviewDetail2->getErrors());
                        }
                    }
                }
                /**
                 * Table: review_store
                 */
                $reviewStores = Mage1ReviewStore::model()->findAll("review_id = {$model->review_id}");
                if ($reviewStores) {
                    foreach ($reviewStores as $reviewStore) {
                        $reviewStore2 = new Mage2ReviewStore();
                        $reviewStore2->review_id = $model2->review_id;
                        $reviewStore2->store_id = $mappingStores[$reviewStore->store_id];
                        if (!$reviewStore2->save()) {
                            $this->errors[] = get_class($reviewStore2) . ": " . UBMigrate::getStringErrors($reviewStore2->getErrors());
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateReviewSummary($models, $mappingStores)
    {
        foreach ($models as $model) {
            $model2 = new Mage2ReviewEntitySummary();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->primary_id = null;
            if ($model2->entity_type == 1) { // review of product
                $model2->entity_pk_value = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model2->entity_pk_value);
            } elseif ($model2->entity_type == 2) { // review of customer
                $model2->entity_pk_value = UBMigrate::getM2EntityId(6, 'customer_entity', $model2->entity_pk_value);
            } elseif ($model2->entity_type == 3) { // review of category
                $model2->entity_pk_value = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $model2->entity_pk_value);
            }
            $model2->store_id = isset($mappingStores[$model2->store_id]) ? $mappingStores[$model2->store_id] : 0;
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //save to map table, we have mapping this table for reset function
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->primary_id,
                    'm2_id' => $model2->primary_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'primary_id',
                    'can_reset' => UBMigrate::RESET_YES,
                    'step_index' => "8ReviewSummary"
                ]);

                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateRating($models, $mappingStores)
    {
        /**
         * Table: rating
         */
        foreach ($models as $model) {
            $canReset = UBMigrate::RESET_YES;
            $m2Id = UBMigrate::getM2EntityId('8_rating', 'rating', $model->rating_id);
            if (!$m2Id) {
                $model2 = Mage2Rating::model()->find("rating_code = '{$model->rating_code}'");
                if (!$model2) {
                    $model2 = new Mage2Rating();
                    foreach ($model2->attributes as $key => $value) {
                        if (isset($model->$key)) {
                            $model2->$key = $model->$key;
                        }
                    }
                    $model2->rating_id = null;
                    //save
                    if (!$model2->save()) {
                        $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                    }
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->rating_id,
                    'm2_id' => $model2->rating_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'rating_id',
                    'can_reset' => $canReset,
                    'step_index' => "8Rating"
                ]);
            } else {
                $model2 = Mage2Rating::model()->find("rating_id = '{$m2Id}'");
            }
            //migrate related data
            if ($model2->rating_id) {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
                /**
                 * Table: rating_option
                 */
                $ratingOptions = Mage1RatingOption::model()->findAll("rating_id = {$model->rating_id}");
                if ($ratingOptions) {
                    foreach ($ratingOptions as $ratingOption) {
                        $canReset = UBMigrate::RESET_YES;
                        $condition = "code = '{$ratingOption->code}' AND value = {$ratingOption->value}";
                        $ratingOption2 = Mage2RatingOption::model()->find($condition);
                        if (!$ratingOption2) {
                            $ratingOption2 = new Mage2RatingOption();
                            foreach ($ratingOption2->attributes as $key => $value) {
                                if (isset($ratingOption->$key)) {
                                    $ratingOption2->$key = $ratingOption->$key;
                                }
                            }
                            $ratingOption2->option_id = null;
                            $ratingOption2->rating_id = $model2->rating_id;
                            //save
                            if (!$ratingOption2->save()) {
                                $this->errors[] = get_class($ratingOption2) . ": " . UBMigrate::getStringErrors($ratingOption2->getErrors());
                            }
                        } else {
                            $canReset = UBMigrate::RESET_NO;
                        }
                        //save to map table
                        UBMigrate::log([
                            'entity_name' => $ratingOption->tableName(),
                            'm1_id' => $ratingOption->option_id,
                            'm2_id' => $ratingOption2->option_id,
                            'm2_model_class' => get_class($ratingOption2),
                            'm2_key_field' => 'option_id',
                            'can_reset' => $canReset,
                            'step_index' => "8Rating"
                        ]);
                    }
                }
                /**
                 * rating_store
                 */
                $condition = "rating_id = {$model->rating_id}";
                if (!UBMigrate::getSetting(2, 'select_all_store')) {
                    $strStoreIds = implode(',', array_keys($mappingStores));
                    $condition .= " AND store_id IN ({$strStoreIds})";
                }
                $ratingStores = Mage1RatingStore::model()->findAll($condition);
                if ($ratingStores) {
                    foreach ($ratingStores as $ratingStore) {
                        $storeId2 = $mappingStores[$ratingStore->store_id];
                        $ratingStore2 = Mage2RatingStore::model()->find("rating_id = {$model2->rating_id} AND store_id = {$storeId2}");
                        if (!$ratingStore2) {
                            $ratingStore2 = new Mage2RatingStore();
                            $ratingStore2->rating_id = $model2->rating_id;
                            $ratingStore2->store_id = $storeId2;
                            if (!$ratingStore2->save()) {
                                $this->errors[] = get_class($ratingStore2) . ": " . UBMigrate::getStringErrors($ratingStore2->getErrors());
                            }
                        }
                    }
                }
                /**
                 * Table: rating_title
                 */
                $ratingTiles = Mage1RatingTitle::model()->findAll($condition);
                if ($ratingTiles) {
                    foreach ($ratingTiles as $ratingTile) {
                        $storeId2 = $mappingStores[$ratingTile->store_id];
                        $ratingTitle2 = Mage2RatingTitle::model()->find("rating_id = {$model2->rating_id} AND store_id = {$storeId2}");
                        if (!$ratingTitle2) {
                            $ratingTitle2 = new Mage2RatingTitle();
                            $ratingTitle2->rating_id = $model2->rating_id;
                            $ratingTitle2->store_id = $storeId2;
                            $ratingTitle2->value = $ratingTile->value;
                            if (!$ratingTitle2->save()) {
                                $this->errors[] = get_class($ratingTitle2) . ": " . UBMigrate::getStringErrors($ratingTitle2->getErrors());
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateRatingOptionVotes($models)
    {
        /**
         * Table: rating_option_vote
         */
        foreach ($models as $model) {
            $model2 = new Mage2RatingOptionVote();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->vote_id = null;
            $model2->option_id = UBMigrate::getM2EntityId('8_rating', 'rating_option', $model2->option_id);
            if ($model2->customer_id) {
                $model2->customer_id = UBMigrate::getM2EntityId(6, 'customer_entity', $model2->customer_id);
            }
            if ($model2->entity_pk_value) {
                $model2->entity_pk_value = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model2->entity_pk_value);
            }
            $model2->rating_id = UBMigrate::getM2EntityId('8_rating', 'rating', $model2->rating_id);
            if ($model2->review_id) {
                $model2->review_id = UBMigrate::getM2EntityId('8_review', 'review', $model2->review_id);
            }
            //save
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateRatingOptionVoteAggregated($models, $mappingStores)
    {
        /**
         * Table: rating_option_vote_aggregated
         */
        foreach ($models as $model) {
            $model2 = new Mage2RatingOptionVoteAggregated();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->primary_id = null;
            $model2->store_id = $mappingStores[$model->store_id];
            $model2->rating_id = UBMigrate::getM2EntityId('8_rating', 'rating', $model2->rating_id);
            if ($model2->entity_pk_value) {
                $model2->entity_pk_value = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model2->entity_pk_value);
            }
            //save
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateProductTierPrice($models, $mappingWebsites)
    {
        /**
         * Table: catalog_product_entity_tier_price vs catalog_product_entity_group_price
         */
        foreach ($models as $model) {
            $qty = (isset($model->qty)) ? $model->qty : 1;
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->entity_id);
            $customerGroupId2 = UBMigrate::getM2EntityId(6, 'customer_group', $model->customer_group_id);
            $websiteId2 = $mappingWebsites[$model->website_id];
            $condition = "entity_id = {$productId2} AND all_groups = {$model->all_groups} AND customer_group_id = {$customerGroupId2} AND qty = {$qty} AND website_id = {$websiteId2}";
            if (!Mage2CatalogProductEntityTierPrice::model()->find($condition)) {
                $model2 = new Mage2CatalogProductEntityTierPrice();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->value_id = null;
                $model2->entity_id = $productId2;
                $model2->customer_group_id = $customerGroupId2;
                $model2->website_id = $websiteId2;
                $model2->qty = $qty;
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateTaxClass($taxClasses)
    {
        foreach ($taxClasses as $taxClass) {
            $classType = $taxClass->class_type;
            //check has migrate in step 5
            $m2Id = UBMigrate::getM2EntityId(5, 'tax_class', $taxClass->class_id);
            if (!$m2Id) {
                //check has migrate in step 6
                $m2Id = UBMigrate::getM2EntityId(6, 'tax_class', $taxClass->class_id);
            }
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) { //has not migrated in step 5 and step 6
                $taxClass2 = Mage2TaxClass::model()->find("class_name = '{$taxClass->class_name}' AND class_type = '{$classType}'");
                if (!$taxClass2) {
                    $taxClass2 = new Mage2TaxClass();
                    $taxClass2->class_name = $taxClass->class_name;
                    $taxClass2->class_type = $classType;
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save
                if (!$taxClass2->save()) {
                    $this->errors[] = get_class($taxClass2) . ": " . UBMigrate::getStringErrors($taxClass2->getErrors());
                } else {
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $taxClass->tableName(),
                        'm1_id' => $taxClass->class_id,
                        'm2_id' => $taxClass2->class_id,
                        'm2_model_class' => get_class($taxClass2),
                        'm2_key_field' => 'class_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateTaxCalculationRate($models, $mappingStores)
    {
        /**
         * Table: tax_calculation_rate
         */
        foreach ($models as $model) {
            $model2 = new Mage2TaxCalculationRate();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->tax_calculation_rate_id = null;
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->tax_calculation_rate_id,
                    'm2_id' => $model2->tax_calculation_rate_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'tax_calculation_rate_id',
                    'can_reset' => UBMigrate::RESET_YES,
                    'step_index' => $this->stepIndex
                ]);
                /**
                 * Table: tax_calculation_rate_title
                 */
                $condition = "tax_calculation_rate_id = {$model->tax_calculation_rate_id}";
                if (!UBMigrate::getSetting(2, 'select_all_store')) {
                    $strStoreIds = implode(',', array_keys($mappingStores));
                    $condition .= " AND store_id IN ({$strStoreIds})";
                }
                $rateTitles = Mage1TaxCalculationRateTitle::model()->findAll($condition);
                if ($rateTitles) {
                    foreach ($rateTitles as $rateTitle) {
                        $rateTitle2 = new Mage2TaxCalculationRateTitle();
                        foreach ($rateTitle2->attributes as $key => $value) {
                            if (isset($rateTitle->$key)) {
                                $rateTitle2->$key = $rateTitle->$key;
                            }
                        }
                        $rateTitle2->store_id = $mappingStores[$rateTitle->store_id];
                        $rateTitle2->tax_calculation_rate_id = $model2->tax_calculation_rate_id;
                        if (!$rateTitle2->save()) {
                            $this->errors[] = get_class($rateTitle2) . ": " . UBMigrate::getStringErrors($rateTitle2->getErrors());
                        }
                    }
                }
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateTaxCalculationRule($models)
    {
        /**
         * Table: tax_calculation_rule
         */
        foreach ($models as $model) {
            $model2 = new Mage2TaxCalculationRule();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->tax_calculation_rule_id = null;
            //this only for Magento 1.6.x or 1.7.x
            if (!isset($model2->calculate_subtotal)) {
                $model2->calculate_subtotal = 0;
            }
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->tax_calculation_rule_id,
                    'm2_id' => $model2->tax_calculation_rule_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'tax_calculation_rule_id',
                    'can_reset' => UBMigrate::RESET_YES,
                    'step_index' => $this->stepIndex
                ]);
                /**
                 * Table: tax_calculation
                 */
                $condition = "tax_calculation_rule_id = {$model->tax_calculation_rule_id}";
                $taxCalculations = Mage1TaxCalculation::model()->findAll($condition);
                if ($taxCalculations) {
                    foreach ($taxCalculations as $taxCalculation) {
                        $taxCalculation2 = new Mage2TaxCalculation();
                        foreach ($taxCalculation2->attributes as $key => $value) {
                            if (isset($taxCalculation->$key)) {
                                $taxCalculation2->$key = $taxCalculation->$key;
                            }
                        }
                        $taxCalculation2->tax_calculation_id = null;
                        $taxCalculation2->tax_calculation_rate_id = UBMigrate::getM2EntityId(8, 'tax_calculation_rate', $taxCalculation2->tax_calculation_rate_id);
                        $taxCalculation2->tax_calculation_rule_id = $model2->tax_calculation_rule_id;
                        $customerTaxClassId2 = UBMigrate::getM2EntityId(6, 'tax_class', $taxCalculation2->customer_tax_class_id);
                        if (!$customerTaxClassId2) {
                            $customerTaxClassId2 = UBMigrate::getM2EntityId(8, 'tax_class', $taxCalculation2->customer_tax_class_id);
                        }
                        $taxCalculation2->customer_tax_class_id = $customerTaxClassId2;
                        $productTaxClassId2 = UBMigrate::getM2EntityId(5, 'tax_class', $taxCalculation2->product_tax_class_id);
                        if (!$productTaxClassId2) {
                            $productTaxClassId2 = UBMigrate::getM2EntityId(8, 'tax_class', $taxCalculation2->product_tax_class_id);
                        }
                        $taxCalculation2->product_tax_class_id = $productTaxClassId2;
                        //save
                        if (!$taxCalculation2->save()) {
                            $this->errors[] = get_class($taxCalculation2) . ": " . UBMigrate::getStringErrors($taxCalculation2->getErrors());
                        }
                    }
                }
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateTaxOrderAggregated($models, $modelClass, $mappingStores)
    {
        foreach ($models as $model) {
            $storeId2 = isset($mappingStores[$model->store_id]) ? $mappingStores[$model->store_id] : null;
            $con = "period = '{$model->period}' AND order_status = '{$model->order_status}'";
            $con .= " AND code = '{$model->code}' AND percent = {$model->percent}";
            if (!is_null($storeId2)) {
                $con .= " AND store_id = {$storeId2}";
            } else {
                $con .= " AND store_id IS NULL";
            }
            $model2 = $modelClass::model()->find($con);
            if (!$model2) {
                $model2 = new $modelClass();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->id = null;
                $model2->store_id = $storeId2;
                //save
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogRule($models, $mappingWebsites, $mappingCustomerGroups)
    {
        foreach ($models as $model) {
            $model2 = new Mage2Catalogrule();
            foreach ($model2->attributes as $key => $value) {
                if (isset($model->$key)) {
                    $model2->$key = $model->$key;
                }
            }
            $model2->rule_id = null;
            /**
             * Because model class name and related ids was changed in Magento2 after migrated
             * So we have to convert conditions and actions,
             **/
            $model2->conditions_serialized = $this->_convertCatalogRuleCondition($model2->conditions_serialized);
            $model2->actions_serialized = $this->_convertCatalogRuleAction($model2->actions_serialized);
            if (!$model2->save()) {
                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
            } else {
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->rule_id,
                    'm2_id' => $model2->rule_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'rule_id',
                    'can_reset' => UBMigrate::RESET_YES,
                    'step_index' => $this->stepIndex
                ]);
                //migrate related data
                /**
                 * Table: catalogrule_product
                 */
                $condition = "rule_id = {$model->rule_id}";
                $selectAllWebsite = UBMigrate::getSetting(2, 'select_all_website');
                $selectAllCustomer = UBMigrate::getSetting(6, 'select_all_customer');
                if (!$selectAllWebsite) {
                    $strWebsiteIds = implode(',', array_keys($mappingWebsites));
                    $condition .= " AND website_id IN ({$strWebsiteIds})";
                }
                if (!$selectAllCustomer) {
                    $strCustomerGroupIds = implode(',', array_keys($mappingCustomerGroups));
                    $condition .= " AND customer_group_id IN ({$strCustomerGroupIds})";
                }
                $ruleProducts = Mage1CatalogruleProduct::model()->findAll($condition);
                if ($ruleProducts) {
                    $this->_migrateCatalogruleProducts($model2->rule_id, $ruleProducts, $mappingWebsites, $mappingCustomerGroups);
                }

                /**
                 * Table: catalogrule_group_website
                 */
                $groupWebsites = Mage1CatalogruleGroupWebsite::model()->findAll($condition);
                if ($groupWebsites) {
                    $this->_migrateCatalogruleGroupWebsite($model2->rule_id, $groupWebsites, $mappingWebsites, $mappingCustomerGroups);
                }
                /**
                 * Table: catalogrule_product_price => this table will auto generate by indexer
                 */

                //we only migrate bellow tables for Magento >= 1.7.x
                $mg1Version = UBMigrate::getMG1Version();
                if ($mg1Version != 'mage16x') {
                    /**
                     * Table: catalogrule_website
                     */
                    $condition = "rule_id = {$model->rule_id}";
                    if (!$selectAllWebsite) {
                        $condition .= " AND website_id IN ({$strWebsiteIds})";
                    }
                    $ruleWebsites = Mage1CatalogruleWebsite::model()->findAll($condition);
                    if ($ruleWebsites) {
                        foreach ($ruleWebsites as $ruleWebsite) {
                            $ruleWebsite2 = new Mage2CatalogruleWebsite();
                            $ruleWebsite2->rule_id = $model2->rule_id;
                            $ruleWebsite2->website_id = $mappingWebsites[$ruleWebsite->website_id];
                            if (!$ruleWebsite2->save()) {
                                $this->errors[] = get_class($ruleWebsite2) . ": " . UBMigrate::getStringErrors($ruleWebsite2->getErrors());
                            }
                        }
                    }
                    /**
                     * Table: catalogrule_customer_group
                     */
                    $condition = "rule_id = {$model->rule_id}";
                    if (!$selectAllCustomer) {
                        $condition .= " AND customer_group_id IN ({$strCustomerGroupIds})";
                    }
                    $ruleCustomerGroups = Mage1CatalogruleCustomerGroup::model()->findAll($condition);
                    if ($ruleCustomerGroups) {
                        foreach ($ruleCustomerGroups as $ruleCustomerGroup) {
                            $ruleCustomerGroup2 = new Mage2CatalogruleCustomerGroup();
                            $ruleCustomerGroup2->rule_id = $model2->rule_id;
                            $ruleCustomerGroup2->customer_group_id = $mappingCustomerGroups[$ruleCustomerGroup->customer_group_id];
                            if (!$ruleCustomerGroup2->save()) {
                                $this->errors[] = get_class($ruleCustomerGroup2) . ": " . UBMigrate::getStringErrors($ruleCustomerGroup2->getErrors());
                            }
                        }
                    }
                } elseif ($mg1Version == 'mage16x') { //this only for Magento 1.6.x
                    if (isset($model->customer_group_ids) AND $model->customer_group_ids) {
                        $customerGroupIds = explode(',', $model->customer_group_ids);
                        if ($customerGroupIds) {
                            foreach ($customerGroupIds as $id) {
                                if (isset($mappingCustomerGroups[$id])) {
                                    $row = new Mage2CatalogruleCustomerGroup();
                                    $row->rule_id = $model2->rule_id;
                                    $row->customer_group_id = $mappingCustomerGroups[$id];
                                    if (!$row->save()) {
                                        $this->errors[] = get_class($row) . ": " . UBMigrate::getStringErrors($row->getErrors());
                                    }
                                }
                            }
                        }
                    }
                    if (isset($model->website_ids) AND $model->website_ids) {
                        $websiteIds = explode(',', $model->website_ids);
                        if ($websiteIds) {
                            foreach ($websiteIds as $id) {
                                if (isset($mappingWebsites[$id])) {
                                    $row = new Mage2CatalogruleWebsite();
                                    $row->rule_id = $model2->rule_id;
                                    $row->website_id = $mappingWebsites[$id];
                                    if (!$row->save()) {
                                        $this->errors[] = get_class($row) . ": " . UBMigrate::getStringErrors($row->getErrors());
                                    }
                                }
                            }
                        }
                    }
                }

                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _convertCatalogRuleCondition($conditionsSerialized)
    {
        $conditions = unserialize($conditionsSerialized);
        //convert type of condition
        if (isset($conditions['type']) AND $conditions['type']) {
            UBMigrate::convertConditionType($conditions['type']);
        }
        //convert sub conditions
        if (isset($conditions['conditions']) AND $conditions['conditions']) {
            UBMigrate::convertConditions($conditions['conditions']);
        }

        return serialize($conditions);
    }

    private function _convertCatalogRuleAction($actionsSerialized)
    {
        $actions = unserialize($actionsSerialized);
        //convert type of condition
        if (isset($actions['type']) AND $actions['type']) {
            UBMigrate::convertConditionType($actions['type']);
        }
        //convert sub conditions
        if (isset($actions['conditions']) AND $actions['conditions']) {
            UBMigrate::convertConditions($actions['conditions']);
        }

        return serialize($actions);
    }

    private function _migrateCatalogruleProducts($ruleId2, $ruleProducts, $mappingWebsites, $mappingCustomerGroups)
    {
        foreach ($ruleProducts as $ruleProduct) {
            $websiteId2 = $mappingWebsites[$ruleProduct->website_id];
            $customerGroupId2 = $mappingCustomerGroups[$ruleProduct->customer_group_id];
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $ruleProduct->product_id);
            $con = "rule_id = {$ruleId2} AND from_time = {$ruleProduct->from_time} AND to_time = {$ruleProduct->to_time}";
            $con .= " AND website_id = {$websiteId2} AND customer_group_id = {$customerGroupId2} AND product_id = {$productId2}";
            $con .= " AND sort_order = {$ruleProduct->sort_order}";
            $ruleProduct2 = Mage2CatalogruleProduct::model()->find($con);
            if (!$ruleProduct2) {
                $ruleProduct2 = new Mage2CatalogruleProduct();
                foreach ($ruleProduct2->attributes as $key => $value) {
                    if (isset($ruleProduct2->$key)) {
                        $ruleProduct2->$key = $ruleProduct->$key;
                    }
                }
                $ruleProduct2->rule_product_id = null;
                $ruleProduct2->rule_id = $ruleId2;
                $ruleProduct2->customer_group_id = $customerGroupId2;
                $ruleProduct2->website_id = $websiteId2;
                $ruleProduct2->product_id = $productId2;
                if (!$ruleProduct2->save()) {
                    $this->errors[] = get_class($ruleProduct2) . ": " . UBMigrate::getStringErrors($ruleProduct2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogruleGroupWebsite($ruleId2, $groupWebsites, $mappingWebsites, $mappingCustomerGroups)
    {
        foreach ($groupWebsites as $groupWebsite) {
            $websiteId2 = $mappingWebsites[$groupWebsite->website_id];
            $customerGroupId2 = $mappingCustomerGroups[$groupWebsite->customer_group_id];
            $groupWebsite2 = new Mage2CatalogruleGroupWebsite();
            $groupWebsite2->rule_id = $ruleId2;
            $groupWebsite2->customer_group_id = $customerGroupId2;
            $groupWebsite2->website_id = $websiteId2;
            if (!$groupWebsite2->save()) {
                $this->errors[] = get_class($groupWebsite2) . ": " . UBMigrate::getStringErrors($groupWebsite2->getErrors());
            }
        }

        return true;
    }

    private function _migrateEmailTemplates($emailTemplates)
    {
        foreach ($emailTemplates as $model) {
            $model2 = Mage2EmailTemplate::model()->find("template_code = '{$model->template_code}'");
            if (!$model2) {
                $model2 = new Mage2EmailTemplate();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                    $model2->template_id = null;
                    if (empty($model2->added_at)) {
                        $model2->added_at = date("Y-m-d H:i:s");
                    }
                    if (empty($model2->modified_at)) {
                        $model2->modified_at = date("Y-m-d H:i:s");
                    }
                }
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateNewsletterSubscribers($models, $mappingStores)
    {
        foreach ($models as $model) {
            $m2Id = UBMigrate::getM2EntityId('8_subscriber', 'newsletter_subscriber', $model->subscriber_id);
            if (!$m2Id) {
                $model2 = new Mage2NewsletterSubscriber();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->subscriber_id = null;
                if ($model2->customer_id) {
                    $model2->customer_id = UBMigrate::getM2EntityId(6, 'customer_entity', $model2->customer_id);
                }
                $model2->store_id = $mappingStores[$model2->store_id];
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $model->tableName(),
                        'm1_id' => $model->subscriber_id,
                        'm2_id' => $model2->subscriber_id,
                        'm2_model_class' => get_class($model2),
                        'm2_key_field' => 'subscriber_id',
                        'can_reset' => UBMigrate::RESET_YES,
                        'step_index' => "8Subscriber"
                    ]);
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateNewsletterTemplates($newsletterTemplates, $mappingStores)
    {
        foreach ($newsletterTemplates as $model) {
            $templateCode = addslashes($model->template_code);
            $model2 = Mage2NewsletterTemplate::model()->find("template_code = '{$templateCode}'");
            if (!$model2) {
                $model2 = new Mage2NewsletterTemplate();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->template_id = null;
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $model->tableName(),
                        'm1_id' => $model->template_id,
                        'm2_id' => $model2->template_id,
                        'm2_model_class' => get_class($model2),
                        'm2_key_field' => 'template_id',
                        'can_reset' => UBMigrate::RESET_YES,
                        'step_index' => $this->stepIndex
                    ]);
                    /**
                     * Table: newsletter_queue
                     */
                    $newsletterQueues = Mage1NewsletterQueue::model()->findAll("template_id = {$model->template_id}");
                    if ($newsletterQueues) {
                        $this->_migrateNewsletterQueue($model2->template_id, $newsletterQueues, $mappingStores);
                    }
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

    private function _migrateNewsletterQueue($templateId2, $newsletterQueues, $mappingStores)
    {
        foreach ($newsletterQueues as $newsletterQueue) {
            $newsletterQueue2 = new Mage2NewsletterQueue();
            foreach ($newsletterQueue2->attributes as $key => $value) {
                if (isset($newsletterQueue->$key)) {
                    $newsletterQueue2->$key = $newsletterQueue->$key;
                }
            }
            $newsletterQueue2->queue_id = null;
            $newsletterQueue2->template_id = $templateId2;
            //save
            if (!$newsletterQueue2->save()) {
                $this->errors[] = get_class($newsletterQueue2) . ": " . UBMigrate::getStringErrors($newsletterQueue2->getErrors());
            } else {
                /**
                 * Table: newsletter_problem
                 */
                $newsletterProblems = Mage1NewsletterProblem::model()->findAll("queue_id = {$newsletterQueue->queue_id}");
                if ($newsletterProblems) {
                    foreach ($newsletterProblems as $newsletterProblem) {
                        $newsletterProblem2 = new Mage2NewsletterProblem();
                        foreach ($newsletterProblem2->attributes as $key => $value) {
                            if (isset($newsletterProblem->$key)) {
                                $newsletterProblem2->$key = $newsletterProblem->$key;
                            }
                        }
                        $newsletterProblem2->problem_id = null;
                        $newsletterProblem2->queue_id = $newsletterQueue2->queue_id;
                        $newsletterProblem2->subscriber_id = UBMigrate::getM2EntityId('8_subscriber', 'newsletter_subscriber', $newsletterProblem2->subscriber_id);
                        if (!$newsletterProblem2->save()) {
                            $this->errors[] = get_class($newsletterProblem2) . ": " . UBMigrate::getStringErrors($newsletterProblem2->getErrors());
                        }
                    }
                }
                /**
                 * Table: newsletter_queue_store_link
                 */
                $newsletterQueueStoreLinks = Mage1NewsletterQueueStoreLink::model()->findAll("queue_id = {$newsletterQueue->queue_id}");
                if ($newsletterQueueStoreLinks) {
                    foreach ($newsletterQueueStoreLinks as $newsletterQueueStoreLink) {
                        if (isset($mappingStores[$newsletterQueueStoreLink->store_id])) {
                            $newsletterQueueStoreLink2 = new Mage2NewsletterQueueStoreLink();
                            $newsletterQueueStoreLink2->queue_id = $newsletterQueue2->queue_id;
                            $newsletterQueueStoreLink2->store_id = $mappingStores[$newsletterQueueStoreLink->store_id];
                            if (!$newsletterQueueStoreLink2->save()) {
                                $this->errors[] = get_class($newsletterQueueStoreLink2) . ": " . UBMigrate::getStringErrors($newsletterQueueStoreLink2->getErrors());
                            }
                        }
                    }
                }
                /**
                 * Table: newsletter_queue_link
                 */
                $queueLinks = Mage1NewsletterQueueLink::model()->findAll("queue_id = {$newsletterQueue->queue_id}");
                if ($queueLinks) {
                    foreach ($queueLinks as $queueLink) {
                        $queueLink2 = new Mage2NewsletterQueueLink();
                        $queueLink2->queue_id = $newsletterQueue2->queue_id;
                        $queueLink2->subscriber_id = UBMigrate::getM2EntityId('8_subscriber', 'newsletter_subscriber', $queueLink->subscriber_id);
                        $queueLink2->letter_sent_at = $queueLink->letter_sent_at;
                        if (empty($queueLink2->letter_sent_at)) {
                            $queueLink2->letter_sent_at = date('Y-m-d H:i:s');
                        }
                        if (!$queueLink2->save()) {
                            $this->errors[] = get_class($queueLink2) . ": " . UBMigrate::getStringErrors($queueLink2->getErrors());
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateEAVEntityStores($models, $mappingStores)
    {
        foreach ($models as $model) {
            $entityTypeId2 = UBMigrate::_getMage2EntityTypeId($model->entity_type_id);
            if ($entityTypeId2) {
                $model2 = new Mage2EAVEntityStore();
                $model2->entity_type_id = $entityTypeId2;
                $model2->store_id = $mappingStores[$model->store_id];
                $model2->increment_prefix = $model->increment_prefix;
                $model2->increment_last_id = $model->increment_last_id;
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                }
            }
        }

        return true;
    }

}
