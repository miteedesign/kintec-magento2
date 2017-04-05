<?php

include_once('BaseController.php');

/**
 * @todo: Catalog Products migration
 *
 * Class Step5Controller
 */
class Step5Controller extends BaseController
{
    protected $stepIndex = 5;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //get selected category ids
            $selectedCategoryIds = UBMigrate::getSetting(4, 'category_ids');

            //product types
            $productTypes = array('simple', 'configurable', 'grouped', 'virtual', 'bundle', 'downloadable');

            if (Yii::app()->request->isPostRequest) {
                if ($selectedCategoryIds) {
                    $selectAll = Yii::app()->request->getParam('select_all', false);
                    //get selected data ids
                    $selectedProductTypes = Yii::app()->request->getParam('product_types', array());
                    if ($selectedProductTypes) {
                        //make setting data to save
                        $settingData = [
                            'product_types' => $selectedProductTypes,
                            'select_all_product' => (sizeof($selectedProductTypes) == sizeof($productTypes)) ? 1 : 0
                        ];
                        $step->setting_data = serialize($settingData);
                        $step->status = UBMigrate::STATUS_SETTING;

                        //save settings data
                        if ($step->update()) {
                            //alert message
                            Yii::app()->user->setFlash('success', "Your settings was saved successfully.");
                            //get next step index
                            $stepIndex = ($this->stepIndex < UBMigrate::MAX_STEP_INDEX) ? ++$this->stepIndex : 1;
                            //go to next step
                            $this->redirect(UBMigrate::getSettingUrl($stepIndex));
                        }
                    } else {
                        Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have to select at least one Product type to migrate or you can skip this step.'));
                    }
                } else {
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have to finish settings in step #4 (Categories) before setting in this step.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'productTypes' => $productTypes,
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
            //get mapping attributes
            $mappingAttributes = UBMigrate::getMappingData('eav_attribute', '3_attribute');

            //get setting data
            $settingData = $step->getSettingData();
            $selectedProductTypes = (isset($settingData['product_types'])) ? $settingData['product_types'] : [];
            //is selected all products
            $isSelectedAll = UBMigrate::getSetting(5, 'select_all_product');

            //some variables for paging
            $max = $offset = $max1 = $offset1 = $max2 = $offset2 = $max3 = $offset3 = $max4 = $offset4 = $max5 = $offset5 = $max6 = $offset6 = $max7 = $offset7 = 0;
            try {
                //start migrate data by settings
                if ($selectedProductTypes) {
                    /**
                     * Table: catalog_product_entity
                     */
                    //make condition to get data
                    $strSelectedProductTypeIds = "'" . implode("','", $selectedProductTypes) . "'";
                    $condition = "type_id IN ({$strSelectedProductTypeIds})";
                    //get max total
                    $max = Mage1CatalogProductEntity::model()->count($condition);
                    $offset = UBMigrate::getCurrentOffset(5, Mage1CatalogProductEntity::model()->tableName());
                    //get data by limit and offset
                    $products = UBMigrate::getListObjects('Mage1CatalogProductEntity', $condition, $offset, $this->limit, "entity_id ASC");
                    if ($products) {
                        //migrate product and related data
                        $this->_migrateCatalogProducts($products, $mappingWebsites, $mappingStores);
                    }

                    //log
                    Yii::log(($offset == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                    if ($offset == 0) {
                        //update status of this step to processing
                        $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                    }

                    if ($offset >= $max) { //if has migrated all products

                        if (!$isSelectedAll) {
                            //get mapping products
                            $mappingProducts = UBMigrate::getMappingData('catalog_product_entity', 5);
                            //get string migrated product ids
                            $strMigratedProductIds = implode(',', array_keys($mappingProducts));
                        }

                        //start migrate other data related with a product
                        //start Cross sell, Up sell, Related & Grouped Products
                        /** catalog_product_link_type:
                         * 1 - relation - Related Products
                         * 2 - bundle - Bundle products
                         * 3 - super - Grouped Products
                         * 4 - up_sell - Up Sell Products
                         * 5 - cross_sell - Cross Sell Products
                         *
                         * Note: Tables: catalog_product_link_type & catalog_product_link_attribute was not changed.
                         * So, we don't migrate these tables. But careful with id was changed in catalog_product_link_attribute
                         */
                        /**
                         * Table: catalog_product_link
                         */
                        /**
                         * Because some case the link_type_id can changed
                         * So we get again link type ids in Magento 1 to migrate
                         */
                        $linkTypeIds = array(
                            UBMigrate::getMage1ProductLinkTypeId('relation'),
                            UBMigrate::getMage1ProductLinkTypeId('up_sell'),
                            UBMigrate::getMage1ProductLinkTypeId('cross_sell')
                        );
                        if (in_array('grouped', $selectedProductTypes)) {
                            $linkTypeIds[] = UBMigrate::getMage1ProductLinkTypeId('super');
                        }
                        if (in_array('bundle', $selectedProductTypes)) {
                            $linkTypeIds[] = UBMigrate::getMage1ProductLinkTypeId('bundle');
                        }
                        $strLinkTypeIds = implode(',', array_filter($linkTypeIds));
                        //build condition
                        $condition = "link_type_id IN ({$strLinkTypeIds})";
                        if (!$isSelectedAll) {
                            $condition .= " AND product_id IN ({$strMigratedProductIds})";
                        }
                        //get max total
                        $max1 = Mage1CatalogProductLink::model()->count($condition);
                        $offset1 = UBMigrate::getCurrentOffset(5, Mage1CatalogProductLink::model()->tableName());
                        //get data by limit and offset
                        $productLinks = UBMigrate::getListObjects('Mage1CatalogProductLink', $condition, $offset1, $this->limit, "link_id ASC");
                        if ($productLinks) {
                            $this->_migrateCatalogProductLinks($productLinks);
                        }
                        //end Cross sell, Up sell, Related & Grouped Products

                        //configurable products
                        if (in_array('configurable', $selectedProductTypes)) {
                            //catalog_product_super_link
                            if (!$isSelectedAll) {
                                $condition = "product_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }
                            //get max total
                            $max2 = Mage1CatalogProductSuperLink::model()->count($condition);
                            $offset2 = UBMigrate::getCurrentOffset(5, Mage1CatalogProductSuperLink::model()->tableName());
                            //get data by limit and offset
                            $productSuperLinks = UBMigrate::getListObjects('Mage1CatalogProductSuperLink', $condition, $offset2, $this->limit, "link_id ASC");
                            if ($productSuperLinks) {
                                //migrate product super links
                                $this->_migrateCatalogProductSuperLinks($productSuperLinks);
                            }

                            //catalog_product_super_attribute
                            /*if (!$isSelectedAll) {
                                $condition = "product_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }*/
                            //get max total
                            $max3 = Mage1CatalogProductSuperAttribute::model()->count($condition);
                            $offset3 = UBMigrate::getCurrentOffset(5, Mage1CatalogProductSuperAttribute::model()->tableName());
                            //get data by limit and offset
                            $productSuperAttributes = UBMigrate::getListObjects('Mage1CatalogProductSuperAttribute', $condition, $offset3, $this->limit, "product_super_attribute_id ASC");
                            if ($productSuperAttributes) {
                                //migrate catalog product super attributes
                                $this->_migrateCatalogProductSuperAttributes($productSuperAttributes, $mappingStores, $mappingAttributes);
                            }

                            //catalog_product_relation
                            if (!$isSelectedAll) {
                                $condition = "parent_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }
                            $max4 = Mage1CatalogProductRelation::model()->count($condition);
                            $offset4 = UBMigrate::getCurrentOffset(5, Mage1CatalogProductRelation::model()->tableName());
                            //get data by limit and offset
                            $productRelations = UBMigrate::getListObjects('Mage1CatalogProductRelation', $condition, $offset4, $this->limit);
                            if ($productRelations) {
                                //migrate catalog product relation
                                $this->_migrateCatalogProductRelations($productRelations);
                            }
                        }
                        //end Configurable products

                        //start migrate Bundle products
                        if (in_array('bundle', $selectedProductTypes)) {
                            //catalog_product_bundle_option
                            if (!$isSelectedAll) {
                                $condition = "parent_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }
                            $max5 = Mage1CatalogProductBundleOption::model()->count($condition);
                            $offset5 = UBMigrate::getCurrentOffset(5, Mage1CatalogProductBundleOption::model()->tableName());
                            //get data by limit and offset
                            $productBundleOptions = UBMigrate::getListObjects('Mage1CatalogProductBundleOption', $condition, $offset5, $this->limit, "option_id ASC");
                            if ($productBundleOptions) {
                                //migrate product bundle options
                                $this->_migrateCatalogProductBundleOptions($productBundleOptions, $mappingWebsites, $mappingStores);
                            }
                        }
                        //end migrate Bundle products

                        //start Downloadable products
                        if (in_array('downloadable', $selectedProductTypes)) {
                            //downloadable_link
                            if (!$isSelectedAll) {
                                $condition = "product_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }
                            $max6 = Mage1DownloadableLink::model()->count($condition);
                            $offset6 = UBMigrate::getCurrentOffset(5, Mage1DownloadableLink::model()->tableName());
                            //get data by limit and offset
                            $downloadableLinks = UBMigrate::getListObjects('Mage1DownloadableLink', $condition, $offset6, $this->limit, "link_id ASC");
                            if ($downloadableLinks) {
                                //migrate download links
                                $this->_migrateCatalogProductDownloadableLinks($downloadableLinks, $mappingWebsites, $mappingStores);
                            }

                            //downloadable_sample
                            /*if (!$isSelectedAll) {
                                $condition = "product_id IN ({$strMigratedProductIds})";
                            } else {
                                $condition = '';
                            }*/
                            $max7 = Mage1DownloadableSample::model()->count($condition);
                            $offset7 = UBMigrate::getCurrentOffset(5, Mage1DownloadableSample::model()->tableName());
                            //get data by limit and offset
                            $downloadSamples = UBMigrate::getListObjects('Mage1DownloadableSample', $condition, $offset7, $this->limit, "sample_id ASC");
                            if ($downloadSamples) {
                                //migrate download samples
                                $this->_migrateCatalogProductDownloadableSamples($downloadSamples, $mappingStores);
                            }
                        }
                        //end Downloadable products
                        //end migrate other data related a product
                    }
                }

                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    //if all selected data migrated
                    if ($offset >= $max AND $offset1 >= $max1 AND $offset2 >= $max2
                        AND $offset3 >= $max3 AND $offset4 >= $max4 AND $offset5 >= $max5
                        AND $offset6 >= $max6 AND $offset7 >= $max7) {
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntity::model()->tableName(), $max, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductLink::model()->tableName(), $max1, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductSuperLink::model()->tableName(), $max2, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductSuperAttribute::model()->tableName(), $max3, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductRelation::model()->tableName(), $max4, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductBundleOption::model()->tableName(), $max5, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1DownloadableLink::model()->tableName(), $max6, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1DownloadableSample::model()->tableName(), $max7, $this->stepIndex);

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
                        if ($max) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductEntity::model()->tableName(), ($offset + $this->limit), $this->stepIndex);
                        }
                        if ($max1) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductLink::model()->tableName(), ($offset1 + $this->limit), $this->stepIndex);
                        }
                        if ($max2) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductSuperLink::model()->tableName(), ($offset2 + $this->limit), $this->stepIndex);
                        }
                        if ($max3) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductSuperAttribute::model()->tableName(), ($offset3 + $this->limit), $this->stepIndex);
                        }
                        if ($max4) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductRelation::model()->tableName(), ($offset4 + $this->limit), $this->stepIndex);
                        }
                        if ($max5) {
                            UBMigrate::updateCurrentOffset(Mage1CatalogProductBundleOption::model()->tableName(), ($offset5 + $this->limit), $this->stepIndex);
                        }
                        if ($max6) {
                            UBMigrate::updateCurrentOffset(Mage1DownloadableLink::model()->tableName(), ($offset6 + $this->limit), $this->stepIndex);
                        }
                        if ($max7) {
                            UBMigrate::updateCurrentOffset(Mage1DownloadableSample::model()->tableName(), ($offset7 + $this->limit), $this->stepIndex);
                        }

                        //start calculate percent run ok
                        $totalSteps = UBMigrate::getTotalStepCanRunMigrate();
                        $percentOfOnceStep = (1 / $totalSteps) * 100;
                        if ($max1) { //has migrated all catalog_product_entity items
                            $_max = max($max1, $max2, $max3, $max4, $max5, $max6, $max7);
                            $n = ceil($_max / $this->limit);
                        } else {
                            $n = ceil($max / $this->limit);
                        }
                        $percentUp = ($percentOfOnceStep / 2) / $n;
                        //end calculate percent run ok

                        //update result to respond
                        $rs['status'] = 'ok';
                        $rs['percent_up'] = $percentUp;
                        //build message
                        $msg = ($offset == 0) ? 'Migrated data in step #%s ok with' : '(Continued) migrated data in step #%s ok with';
                        $data['%s'] = $this->stepIndex;
                        if (isset($products) AND $products) {
                            $msg .= ' %s1 Products.';
                            $data['%s1'] = sizeof($products);
                        }
                        if (isset($productLinks) AND $productLinks) {
                            $msg .= ' %s2 Product Links.';
                            $data['%s2'] = sizeof($productLinks);
                        }
                        if (isset($productSuperLinks) AND $productSuperLinks) {
                            $msg .= ' %s3 Product Super Links.';
                            $data['%s3'] = sizeof($productSuperLinks);
                        }
                        if (isset($productSuperAttributes) AND $productSuperAttributes) {
                            $msg .= ' %s4 Product Super Attributes.';
                            $data['%s4'] = sizeof($productSuperAttributes);
                        }
                        if (isset($productRelations) AND $productRelations) {
                            $msg .= ' %s5 Product Relations.';
                            $data['%s5'] = sizeof($productRelations);
                        }
                        if (isset($productBundleOptions) AND $productBundleOptions) {
                            $msg .= ' %s6 Product Bundle Options.';
                            $data['%s6'] = sizeof($productBundleOptions);
                        }
                        if (isset($downloadableLinks) AND $downloadableLinks) {
                            $msg .= ' %s7 Product Downloadable Links.';
                            $data['%s7'] = sizeof($downloadableLinks);
                        }
                        if (isset($downloadSamples) AND $downloadSamples) {
                            $msg .= ' %s8 Product Downloadable Samples.';
                            $data['%s8'] = sizeof($downloadSamples);
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

    private function _migrateCatalogProducts($products, $mappingWebsites, $mappingStores)
    {
        //get mapping attribute sets
        $mappingAttributeSets = UBMigrate::getMappingData('eav_attribute_set', 3);
        //get mapping attributes
        $mappingAttributes = UBMigrate::getMappingData('eav_attribute', '3_attribute');

        foreach ($products as $product) {
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $product->entity_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$productId2) {
                $product2 = Mage2CatalogProductEntity::model()->find("sku = '{$product->sku}'");
                if (!$product2) { //add new
                    $product2 = new Mage2CatalogProductEntity();
                    foreach ($product2->attributes as $key => $value) {
                        if ($key != 'entity_id' AND isset($product->$key)) { //we don't take old product id
                            $product2->$key = $product->$key;
                        }
                    }
                    //because attribute_set_id was changed
                    $product2->attribute_set_id = $mappingAttributeSets[$product->attribute_set_id];
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save
                if ($product2->save()) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $product->tableName(),
                        'm1_id' => $product->entity_id,
                        'm2_id' => $product2->entity_id,
                        'm2_model_class' => get_class($product2),
                        'm2_key_field' => 'entity_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                } else {
                    $this->errors[] = get_class($product2) . ": " . UBMigrate::getStringErrors($product2->getErrors());
                }
            } else { //update
                $product2 = Mage2CatalogProductEntity::model()->find("entity_id = {$productId2}");
            }
            //start migrate related data with a product
            if ($product2->entity_id) {
                //migrate product EAV data
                $this->_migrateCatalogProductEAV($product->entity_id, $product2->entity_id, $mappingStores, $mappingAttributes);

                //migrate product gallery
                $this->_migrateCatalogProductGallery($product->entity_id, $product2->entity_id, $mappingStores, $mappingAttributes);

                //migrate product options
                $this->_migrateCatalogProductOptions($product->entity_id, $product2->entity_id, $mappingStores);

                //migrate product stock item
                $this->_migrateCatalogProductStockItem($product->entity_id, $product2->entity_id);

                //migrate product URLs rewrite
                $this->_migrateCatalogProductUrlReWrite($product->entity_id, $product2->entity_id, $mappingStores);

                //migrate product website relation
                $this->_migrateCatalogProductWebsite($product->entity_id, $product2->entity_id, $mappingWebsites);

                //migrate product category relation
                $this->_migrateCatalogCategoryProduct($product->entity_id, $product2->entity_id);
            }
        }// end foreach products

        return true;
    }

    private function _migrateCatalogProductEAV($entityId, $entityId2, $mappingStores, $mappingAttributes)
    {
        /*
         * Get list attributes which we have to reset value on it to default values
        */
        $entityTypeId = UBMigrate::getMage1EntityTypeId(UBMigrate::PRODUCT_TYPE_CODE);
        $resetAttributes = array(
            UBMigrate::getMage1AttributeId('custom_design', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_design_from', $entityTypeId) => null,
            UBMigrate::getMage1AttributeId('custom_design_to', $entityTypeId) => null,
            UBMigrate::getMage1AttributeId('page_layout', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_layout_update', $entityTypeId) => null,
        );
        $resetAttributeIds = array_keys($resetAttributes);

        /**
         * Because some system product attribute has change the backend_type value
         * Example:
         * + Attribute with code: media_gallery has change backend_type from `varchar` => `static`
         * So we will check to ignore values of these attributes
         */
        $ignoreAttributeIds = array(
            UBMigrate::getMage1AttributeId('media_gallery', $entityTypeId)
        );

        //get string migrated store ids
        $strMigratedStoreIds = implode(',', array_keys($mappingStores));

        $eavTables = [
            'catalog_product_entity_int',
            'catalog_product_entity_text',
            'catalog_product_entity_varchar',
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage1{$className}";
            $className2 = "Mage2{$className}";
            $models = $className1::model()->findAll("entity_id = {$entityId} AND store_id IN ({$strMigratedStoreIds})");
            if ($models) {
                foreach ($models as $model) {
                    if (!in_array($model->attribute_id, $ignoreAttributeIds)) {
                        $storeId2 = $mappingStores[$model->store_id];
                        $attributeId2 = isset($mappingAttributes[$model->attribute_id]) ? $mappingAttributes[$model->attribute_id] : null; //coming soon
                        if ($attributeId2) {
                            $condition = "entity_id = {$entityId2} AND attribute_id = {$attributeId2} AND store_id = {$storeId2}";
                            $model2 = $className2::model()->find($condition);
                            if (!$model2) { //add new
                                $model2 = new $className2();
                                $model2->attribute_id = $attributeId2;
                                $model2->store_id = $storeId2;
                                $model2->entity_id = $entityId2;
                                //note: we need check and fixed for some attributes
                                if (in_array($model->attribute_id, $resetAttributeIds)) {
                                    $model2->value = $resetAttributes[$model->attribute_id];
                                } else {
                                    $model2->value = $model->value;
                                    if ($table == 'catalog_product_entity_int') {
                                        /**
                                         * because related system ids was changed (eav_attribute_option, ...)
                                         * we will check has custom option in eav_attribute_option table, if yes we have to get back new option_id
                                         * maybe coming soon
                                         */
                                        $count = Mage1AttributeOption::model()->count("attribute_id = {$model->attribute_id}");
                                        if ($count AND $model->value) {
                                            //get back new option_id
                                            $model2->value = UBMigrate::getM2EntityId('3_attribute_option', 'eav_attribute_option', $model->value);
                                        } else {
                                            /**
                                             * we will check and migrate related product tax classes in here
                                             */
                                            $attributeCode1 = UBMigrate::getMage1AttributeCode($model->attribute_id);
                                            if ($attributeCode1 == 'tax_class_id') {
                                                $taxClass1 = Mage1TaxClass::model()->findByPk($model->value);
                                                if ($taxClass1) {
                                                    $m2Id = UBMigrate::getM2EntityId(5, 'tax_class', $taxClass1->class_id);
                                                    $canReset = UBMigrate::RESET_YES;
                                                    if (!$m2Id) {
                                                        $taxClass2 = Mage2TaxClass::model()->find("class_name = '{$taxClass1->class_name}' AND class_type = '{$taxClass1->class_type}'");
                                                        if (!$taxClass2) {
                                                            $taxClass2 = new Mage2TaxClass();
                                                            $taxClass2->class_name = $taxClass1->class_name;
                                                            $taxClass2->class_type = $taxClass1->class_type;
                                                        } else {
                                                            $canReset = UBMigrate::RESET_NO;
                                                        }
                                                        //save
                                                        if ($taxClass2->save()) {
                                                            //save to map table
                                                            UBMigrate::log([
                                                                'entity_name' => $taxClass1->tableName(),
                                                                'm1_id' => $taxClass1->class_id,
                                                                'm2_id' => $taxClass2->class_id,
                                                                'm2_model_class' => get_class($taxClass2),
                                                                'm2_key_field' => 'class_id',
                                                                'can_reset' => $canReset,
                                                                'step_index' => $this->stepIndex
                                                            ]);
                                                            //update new product tax class_id
                                                            $model2->value = $taxClass2->class_id;
                                                        } else {
                                                            $this->errors[] = get_class($taxClass2) . ": " . UBMigrate::getStringErrors($taxClass2->getErrors());
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                //save
                                if (!$model2->save()) {
                                    $this->errors[] = "{$className2}: " . UBMigrate::getStringErrors($model2->getErrors());
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductGallery($entityId, $entityId2, $mappingStores, $mappingAttributes)
    {
        //get migrated store ids
        $strMigratedStoreIds = implode(',', array_keys($mappingStores));
        /**
         * Table: catalog_product_entity_gallery
         */
        $models = Mage1CatalogProductEntityGallery::model()->findAll("entity_id = {$entityId} AND store_id IN ({$strMigratedStoreIds})");
        if ($models) {
            foreach ($models as $model) {
                $storeId2 = $mappingStores[$model->store_id];
                $attributeId2 = $mappingAttributes[$model->attribute_id];
                if ($attributeId2) {
                    $condition = "entity_id = {$entityId2} AND attribute_id = {$attributeId2} AND store_id = {$storeId2}";
                    $model2 = Mage2CatalogProductEntityGallery::model()->find($condition);
                    if (!$model2) { //add new
                        $model2 = new Mage2CatalogProductEntityGallery();
                        $model2->attribute_id = $attributeId2;
                        $model2->store_id = $storeId2;
                        $model2->entity_id = $entityId2;
                        $model2->position = $model->position;
                        $model2->value = $model->value;
                        //save
                        if (!$model2->save()) {
                            $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                        }
                    }
                }
            }
        }

        /**
         * Table: catalog_product_entity_media_gallery
         */
        $models = Mage1CatalogProductEntityMediaGallery::model()->findAll("entity_id = {$entityId}");
        if ($models) {
            foreach ($models as $model) {
                $attributeId2 = $mappingAttributes[$model->attribute_id];
                if ($attributeId2) {
                    $condition = "attribute_id = {$attributeId2} AND value = '{$model->value}'";
                    $model2 = Mage2CatalogProductEntityMediaGallery::model()->find($condition);
                    if (!$model2) { //add new
                        $model2 = new Mage2CatalogProductEntityMediaGallery();
                        $model2->attribute_id = $attributeId2;
                        $model2->media_type = 'image'; //default value
                        $model2->disabled = 0; //this is new field in Magento 2, Default value is 0
                        //$model2->entity_id = $model->entity_id; //This field not use from CE 2.0.0
                        $model2->value = $model->value;
                        //save
                        if (!$model2->save()) {
                            $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                        }
                    }
                    if ($model2->value_id) {
                        /**
                         * Table:catalog_product_entity_media_gallery_value
                         * we don't need to map this because this will auto delete by CONSTRAINT if we have delete parent entity
                         */
                        if ($mappingStores) {
                            $migratedStoreIds = array_keys($mappingStores);
                            foreach ($migratedStoreIds as $storeId) {
                                $storeId2 = $mappingStores[$storeId];
                                $galleryValue = Mage1CatalogProductEntityMediaGalleryValue::model()->find("value_id = {$model->value_id} AND store_id = {$storeId}");
                                if ($galleryValue) {
                                    $galleryValue2 = Mage2CatalogProductEntityMediaGalleryValue::model()->find("value_id = {$model2->value_id}");
                                    if (!$galleryValue2) { //add new
                                        $galleryValue2 = new Mage2CatalogProductEntityMediaGalleryValue();
                                        $galleryValue2->value_id = $model2->value_id;
                                        $galleryValue2->store_id = $storeId2;
                                        $galleryValue2->entity_id = $entityId2; //product entity_id was changed
                                        $galleryValue2->label = $galleryValue->label;
                                        $galleryValue2->position = $galleryValue->position;
                                        $galleryValue2->disabled = $galleryValue->disabled;
                                    }
                                    //save/update
                                    if (!$galleryValue2->save()) {
                                        $this->errors[] = get_class($galleryValue2) . ": " . UBMigrate::getStringErrors($galleryValue2->getErrors());
                                    }
                                }
                            }
                        }
                        /**
                         * Table: catalog_product_entity_media_gallery_value_to_entity
                         * this table is new in Magento 2
                         * we don't need to map this because this will auto delete by CONSTRAINT if we have delete parent entity
                         */
                        $condition = "value_id = {$model2->value_id} AND entity_id = {$entityId2}"; //coming soon
                        $galleryValueToEntity2 = Mage2CatalogProductEntityMediaGalleryValueToEntity::model()->find($condition);
                        if (!$galleryValueToEntity2) { //add new
                            $galleryValueToEntity2 = new Mage2CatalogProductEntityMediaGalleryValueToEntity();
                            $galleryValueToEntity2->value_id = $model2->value_id;
                            $galleryValueToEntity2->entity_id = $entityId2;
                            if (!$galleryValueToEntity2->save()) {
                                $this->errors[] = get_class($galleryValueToEntity2) . ": " . UBMigrate::getStringErrors($galleryValueToEntity2->getErrors());
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptions($entityId, $entityId2, $mappingStores)
    {
        /**
         * Table: catalog_product_option
         */
        $productOptions = Mage1CatalogProductOption::model()->findAll("product_id = {$entityId}");
        if ($productOptions) {
            foreach ($productOptions as $productOption) {
                $optionId2 = UBMigrate::getM2EntityId('5_product_option', 'catalog_product_option', $productOption->option_id);
                if (!$optionId2) {
                    $productOption2 = new Mage2CatalogProductOption();
                    foreach ($productOption2->attributes as $key => $value) {
                        if (isset($productOption->$key)) {
                            $productOption2->$key = $productOption->$key;
                        }
                    }
                    $productOption2->option_id = null;
                    //because product id was changed
                    $productOption2->product_id = $entityId2;
                    if ($productOption2->save()) {
                        //save to map table
                        UBMigrate::log([
                            'entity_name' => $productOption->tableName(),
                            'm1_id' => $productOption->option_id,
                            'm2_id' => $productOption2->option_id,
                            'm2_model_class' => get_class($productOption2),
                            'm2_key_field' => 'option_id',
                            'can_reset' => UBMigrate::RESET_YES,
                            'step_index' => "5ProductOption"
                        ]);
                    } else {
                        $this->errors[] = get_class($productOption2) . ": " . UBMigrate::getStringErrors($productOption2->getErrors());
                    }
                } else { //update
                    $productOption2 = Mage2CatalogProductOption::model()->find("option_id = {$optionId2}");
                }
                //migrate related data
                if ($productOption2->option_id) {
                    //migrate option type value
                    $this->_migrateCatalogProductOptionTypeValue($productOption->option_id, $productOption2->option_id, $mappingStores);
                    /**
                     * Tables: catalog_product_option_price and catalog_product_option_title
                     * We have to migrate by migrated stores
                     */
                    $migratedStoreIds = array_keys($mappingStores);
                    foreach ($migratedStoreIds as $storeId) {
                        //migrate catalog product option price
                        $this->_migrateCatalogProductOptionPrice($productOption->option_id, $productOption2->option_id, $storeId, $mappingStores[$storeId]);
                        //migrate catalog product option title
                        $this->_migrateCatalogProductOptionTitle($productOption->option_id, $productOption2->option_id, $storeId, $mappingStores[$storeId]);
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptionPrice($optionId1, $optionId2, $storeId, $storeId2)
    {
        /**
         * Table: catalog_product_option_price
         */
        $optionPrice = Mage1CatalogProductOptionPrice::model()->find("option_id = {$optionId1} AND store_id = {$storeId}");
        if ($optionPrice) {
            $optionPrice2 = Mage2CatalogProductOptionPrice::model()->find("option_id = {$optionId2} AND store_id = {$storeId2}");
            if (!$optionPrice2) {
                $optionPrice2 = new Mage2CatalogProductOptionPrice();
                $optionPrice2->price = $optionPrice->price;
                $optionPrice2->price_type = $optionPrice->price_type;
                //because ids was changed
                $optionPrice2->option_id = $optionId2;
                $optionPrice2->store_id = $storeId2;
                //save
                if (!$optionPrice2->save()) {
                    $this->errors[] = get_class($optionPrice2) . ": " . UBMigrate::getStringErrors($optionPrice2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptionTitle($optionId1, $optionId2, $storeId, $storeId2)
    {
        /**
         * Table: catalog_product_option_title
         */
        $optionTitle = Mage1CatalogProductOptionTitle::model()->find("option_id = {$optionId1} AND store_id = {$storeId}");
        if ($optionTitle) {
            $optionTitle2 = Mage2CatalogProductOptionTitle::model()->find("option_id = {$optionId2} AND store_id = {$storeId2}");
            if (!$optionTitle2) {
                $optionTitle2 = new Mage2CatalogProductOptionTitle();
                $optionTitle2->title = $optionTitle->title;
                //because ids was changed
                $optionTitle2->option_id = $optionId2;
                $optionTitle2->store_id = $storeId2;
                //save
                if (!$optionTitle2->save()) {
                    $this->errors[] = get_class($optionTitle2) . ": " . UBMigrate::getStringErrors($optionTitle2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptionTypeValue($optionId1, $optionId2, $mappingStores)
    {
        /**
         * Table: catalog_product_option_type_value
         */
        $optionTypeValues = Mage1CatalogProductOptionTypeValue::model()->findAll("option_id = {$optionId1}");
        if ($optionTypeValues) {
            foreach ($optionTypeValues as $optionTypeValue) {
                $optionTypeValue2 = new Mage2CatalogProductOptionTypeValue();
                $optionTypeValue2->sku = $optionTypeValue->sku;
                $optionTypeValue2->sort_order = $optionTypeValue->sort_order;
                //because option_id was changed
                $optionTypeValue2->option_id = $optionId2;
                if (!$optionTypeValue2->save()) {
                    $this->errors[] = get_class($optionTypeValue2) . ": " . UBMigrate::getStringErrors($optionTypeValue2->getErrors());
                } else {
                    $migratedStoreIds = array_keys($mappingStores);
                    foreach ($migratedStoreIds as $storeId) {
                        //migrate catalog product option type price
                        $this->_migrateCatalogProductOptionTypePrice($optionTypeValue->option_type_id, $optionTypeValue2->option_type_id, $storeId, $mappingStores[$storeId]);
                        //migrate catalog product option type title
                        $this->_migrateCatalogProductOptionTypeTitle($optionTypeValue->option_type_id, $optionTypeValue2->option_type_id, $storeId, $mappingStores[$storeId]);
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptionTypePrice($optionTypeId1, $optionTypeId2, $storeId, $storeId2)
    {
        /**
         * Table: catalog_product_option_type_price
         */
        $condition = "option_type_id = {$optionTypeId1} AND store_id = {$storeId}";
        $optionTypePrice = Mage1CatalogProductOptionTypePrice::model()->find($condition);
        if ($optionTypePrice) {
            $m2Id = UBMigrate::getM2EntityId('5_product_option', 'catalog_product_option_type_price', $optionTypePrice->option_type_price_id);
            if (!$m2Id) {
                $optionTypePrice2 = new Mage2CatalogProductOptionTypePrice();
                foreach ($optionTypePrice2->attributes as $key => $value) {
                    if (isset($optionTypePrice->$key)) {
                        $optionTypePrice2->$key = $optionTypePrice->$key;
                    }
                }
                $optionTypePrice2->option_type_price_id = null;
                //because ids was changed
                $optionTypePrice2->option_type_id = $optionTypeId2;
                $optionTypePrice2->store_id = $storeId2;
                //save
                if ($optionTypePrice2->save()) {
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $optionTypePrice->tableName(),
                        'm1_id' => $optionTypePrice->option_type_price_id,
                        'm2_id' => $optionTypePrice2->option_type_price_id,
                        'm2_model_class' => get_class($optionTypePrice2),
                        'm2_key_field' => 'option_type_price_id',
                        'can_reset' => UBMigrate::RESET_YES,
                        'step_index' => "5ProductOption"
                    ]);
                } else {
                    $this->errors[] = get_class($optionTypePrice2) . ": " . UBMigrate::getStringErrors($optionTypePrice2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductOptionTypeTitle($optionTypeId1, $optionTypeId2, $storeId, $storeId2)
    {
        /**
         * Table: catalog_product_option_type_title
         */
        $condition = "option_type_id = {$optionTypeId1} AND store_id = {$storeId}";
        $optionTypeTitle = Mage1CatalogProductOptionTypeTitle::model()->find($condition);
        if ($optionTypeTitle) {
            $optionTypeTitle2 = Mage2CatalogProductOptionTypeTitle::model()->find("option_type_id = {$optionTypeId2} AND store_id = {$storeId2}");
            if (!$optionTypeTitle2) {
                $optionTypeTitle2 = new Mage2CatalogProductOptionTypeTitle();
                $optionTypeTitle2->title = $optionTypeTitle->title;
                $optionTypeTitle2->option_type_id = $optionTypeId2;
                $optionTypeTitle2->store_id = $storeId2;
                //save
                if (!$optionTypeTitle2->save()) {
                    $this->errors[] = get_class($optionTypeTitle2) . ": " . UBMigrate::getStringErrors($optionTypeTitle2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductStockItem($entityId, $entityId2)
    {
        /**
         * Table: cataloginventory_stock_item
         */
        $stockItems = Mage1StockItem::model()->findAll("product_id = {$entityId}");
        $websiteId = 0;
        if ($stockItems) {
            foreach ($stockItems as $stockItem) {
                $stockItem2 = Mage2StockItem::model()->find("product_id = {$entityId2} AND website_id = {$websiteId}");
                if (!$stockItem2) {
                    $stockItem2 = new Mage2StockItem();
                    foreach ($stockItem2->attributes as $key => $value) {
                        if ($key != 'item_id' && isset($stockItem->$key)) {
                            $stockItem2->$key = $stockItem->$key;
                        }
                    }
                    $stockItem2->product_id = $entityId2;
                    //this field is new in Magento 2
                    $stockItem2->website_id = $websiteId;
                    //save
                    if (!$stockItem2->save()) {
                        $this->errors[] = get_class($stockItem2) . ": " . UBMigrate::getStringErrors($stockItem2->getErrors());
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductUrlRewrite($entityId, $entityId2, $mappingStores)
    {
        /**
         * Table: url_rewrite
         */
        $strMigratedStoreIds = implode(',', array_keys($mappingStores));
        $condition = "product_id = {$entityId} AND store_id IN ({$strMigratedStoreIds})";
        $urls = Mage1UrlRewrite::model()->findAll($condition);
        if ($urls) {
            foreach ($urls as $url) {
                $storeId2 = $mappingStores[$url->store_id];
                $url2 = Mage2UrlRewrite::model()->find("request_path = '{$url->request_path}' AND store_id = {$storeId2}");
                if (!$url2) { //add new
                    $url2 = new Mage2UrlRewrite();
                    $url2->entity_type = 'product';
                    $url2->entity_id = $entityId2;
                    $url2->request_path = $url->request_path;
                    $url2->target_path = $url->target_path;
                    if ($url->options == 'RP') { //Permanent (301)
                        $url2->redirect_type = 301;
                    } elseif ($url->options == 'R') { // Temporary (302)
                        $url2->redirect_type = 302;
                    } else { //No Redirect
                        $url2->redirect_type = 0;
                    }
                    $url2->store_id = $storeId2;
                    $url2->description = $url->description;
                    $url2->is_autogenerated = $url->is_system;
                    $url2->metadata = null;
                    if ($url->category_id) {
                        $categoryId2 = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $url->category_id);
                        $url2->metadata = serialize(array('category_id' => $categoryId2));
                    }
                    //because product id was changed, we have to update new product id for target_path has format: catalog/product/view/id/...
                    if (substr($url2->target_path, 0, 20) == 'catalog/product/view') {
                        if ($url->category_id AND isset($categoryId2)) {
                            $url2->target_path = "catalog/product/view/id/{$entityId2}/category/{$categoryId2}";
                        } else {
                            $url2->target_path = "catalog/product/view/id/{$entityId2}";
                        }
                    }
                    //save
                    if ($url2->save()) {
                        //catalog_url_rewrite_product_category => this table is new in Magento 2
                        if ($url->category_id AND $categoryId2) {
                            $catalogUrl2 = Mage2CatalogUrlRewriteProductCategory::model()->find("url_rewrite_id = {$url2->url_rewrite_id}");
                            if (!$catalogUrl2) {
                                $catalogUrl2 = new Mage2CatalogUrlRewriteProductCategory();
                                $catalogUrl2->url_rewrite_id = $url2->url_rewrite_id;
                            }
                            $catalogUrl2->category_id = $categoryId2;
                            $catalogUrl2->product_id = $url2->entity_id;
                            if (!$catalogUrl2->save()) {
                                $this->errors[] = get_class($catalogUrl2) . ": " . UBMigrate::getStringErrors($catalogUrl2->getErrors());
                            }
                        }
                    } else {
                        $this->errors[] = get_class($url2) . ": " . UBMigrate::getStringErrors($url2->getErrors());
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductWebsite($productId1, $productId2, $mappingWebsites)
    {
        /**
         * Table: catalog_product_website
         */
        $strMigratedWebsiteIds = implode(',', array_keys($mappingWebsites));
        $condition = "product_id = {$productId1} AND website_id IN ({$strMigratedWebsiteIds})";
        $models = Mage1CatalogProductWebsite::model()->findAll($condition);
        if ($models) {
            foreach ($models as $model) {
                $websiteId2 = $mappingWebsites[$model->website_id];
                $model2 = Mage2CatalogProductWebsite::model()->find("product_id = {$productId2} AND website_id = {$websiteId2}");
                if (!$model2) {
                    $model2 = new Mage2CatalogProductWebsite();
                    $model2->product_id = $productId2;
                    $model2->website_id = $websiteId2;
                    if (!$model2->save()) {
                        $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogCategoryProduct($productId1, $productId2)
    {
        /**
         * Table: catalog_category_product
         */
        $models = Mage1CatalogCategoryProduct::model()->findAll("product_id = {$productId1}");
        if ($models) {
            foreach ($models as $model) {
                $categoryId2 = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $model->category_id);
                if ($categoryId2) {
                    $model2 = Mage2CatalogCategoryProduct::model()->find("product_id = {$productId2} AND category_id = {$categoryId2}");
                    if (!$model2) {
                        $model2 = new Mage2CatalogCategoryProduct();
                        $model2->category_id = $categoryId2;
                        $model2->product_id = $productId2;
                        $model2->position = $model->position;
                        if (!$model2->save()) {
                            $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductLinks($productLinks)
    {
        /**
         * Table: catalog_product_link
         */
        foreach ($productLinks as $model) {
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->product_id);
            $linkedProductId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->linked_product_id);
            $linkTypeId2 = UBMigrate::getMage2ProductLinkTypeId($model->link_type_id);
            $condition = "link_type_id = {$linkTypeId2} AND product_id = {$productId2} AND linked_product_id = {$linkedProductId2}";
            $model2 = Mage2CatalogProductLink::model()->find($condition);
            if (!$model2) { //add new
                $model2 = new Mage2CatalogProductLink();
                //fill values
                $model2->product_id = $productId2;
                $model2->linked_product_id = $linkedProductId2;
                $model2->link_type_id = $linkTypeId2;
                //save
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                }
            }
            //migrate related data
            if ($model2->link_id) {
                //migrate product links eav data
                $this->_migrateCatalogProductLinksEAV($model->link_id, $model2->link_id);
            }
        }

        return true;
    }

    private function _migrateCatalogProductLinksEAV($linkId1, $linkId2)
    {
        $eavTables = [
            'catalog_product_link_attribute_decimal',
            'catalog_product_link_attribute_int',
            'catalog_product_link_attribute_varchar'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage1{$className}";
            $className2 = "Mage2{$className}";
            $items = $className1::model()->findAll("link_id = {$linkId1}");
            if ($items) {
                foreach ($items as $item) {
                    $productLinkAttributeId2 = UBMigrate::getMage2ProductLinkAttrId($item->product_link_attribute_id);
                    if ($productLinkAttributeId2) {
                        $condition = "product_link_attribute_id = {$productLinkAttributeId2} AND link_id = {$linkId2}";
                        $item2 = $className2::model()->find($condition);
                        if (!$item2) { //add new
                            $item2 = new $className2();
                            $item2->product_link_attribute_id = $productLinkAttributeId2;
                            $item2->link_id = $linkId2;
                            $item2->value = $item->value;
                            if (!$item2->save()) {
                                $this->errors[] = get_class($item2) . ": " . UBMigrate::getStringErrors($item2->getErrors());
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductSuperLinks($productSuperLinks)
    {
        /**
         * Table: catalog_product_super_link
         */
        foreach ($productSuperLinks as $model) {
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->product_id);
            $parentId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->parent_id);
            $condition = "product_id = {$productId2} AND parent_id = {$parentId2}";
            $model2 = Mage2CatalogProductSuperLink::model()->find($condition);
            if (!$model2) { //add new
                $model2 = new Mage2CatalogProductSuperLink();
                //fill values
                $model2->product_id = $productId2;
                $model2->parent_id = $parentId2;
                //save
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductSuperAttributes($productSuperAttributes, $mappingStores, $mappingAttributes)
    {
        /**
         * Table: catalog_product_super_attribute
         */
        foreach ($productSuperAttributes as $model) {
            $attributeId2 = $mappingAttributes[$model->attribute_id];
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->product_id);
            if ($attributeId2) {
                $condition = "product_id = {$productId2} AND attribute_id = {$attributeId2}";
                $model2 = Mage2CatalogProductSuperAttribute::model()->find($condition);
                if (!$model2) { //add new
                    $model2 = new Mage2CatalogProductSuperAttribute();
                    //fill values
                    $model2->product_id = $productId2;
                    $model2->attribute_id = $attributeId2;
                    $model2->position = $model->position;
                    if (!$model2->save()) {
                        $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                    }
                }
                //migrate related data
                if ($model2->product_super_attribute_id) {
                    /**
                     * catalog_product_super_attribute_label
                     */
                    $strMigratedStoreIds = implode(',', array_keys($mappingStores));
                    $condition = "product_super_attribute_id = {$model->product_super_attribute_id}";
                    $condition .= " AND store_id IN ({$strMigratedStoreIds})";
                    $superAttributeLabels = Mage1CatalogProductSuperAttributeLabel::model()->findAll($condition);
                    if ($superAttributeLabels) {
                        foreach ($superAttributeLabels as $superAttributeLabel) {
                            $storeId2 = $mappingStores[$superAttributeLabel->store_id];
                            $condition = "product_super_attribute_id = {$model2->product_super_attribute_id} AND store_id = {$storeId2}";
                            $superAttributeLabel2 = Mage2CatalogProductSuperAttributeLabel::model()->find($condition);
                            if (!$superAttributeLabel2) { //add new
                                $superAttributeLabel2 = new Mage2CatalogProductSuperAttributeLabel();
                                $superAttributeLabel2->product_super_attribute_id = $model2->product_super_attribute_id;
                                $superAttributeLabel2->store_id = $storeId2;
                                $superAttributeLabel2->use_default = $superAttributeLabel->use_default;
                                $superAttributeLabel2->value = $superAttributeLabel->value;
                                //save
                                if (!$superAttributeLabel2->save()) {
                                    $this->errors[] = get_class($superAttributeLabel2) . ": " . UBMigrate::getStringErrors($superAttributeLabel2->getErrors());
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductRelations($productRelations)
    {
        /**
         * Table: catalog_product_relation
         */
        foreach ($productRelations as $model) {
            $parentId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->parent_id);
            $childId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->child_id);
            $condition = "parent_id = {$parentId2} AND child_id = {$childId2}";
            $model2 = Mage2CatalogProductRelation::model()->find($condition);
            if (!$model2) {
                $model2 = new Mage2CatalogProductRelation();
                $model2->parent_id = $parentId2;
                $model2->child_id = $childId2;
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductBundleOptions($productBundleOptions, $mappingWebsites, $mappingStores)
    {
        /**
         * Table: catalog_product_bundle_option
         */
        foreach ($productBundleOptions as $model) {
            $parentId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->parent_id);
            $optionId2 = UBMigrate::getM2EntityId('5_product_option', 'catalog_product_bundle_option', $model->option_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$optionId2) {
                $condition = "parent_id = {$parentId2} AND position = {$model->position} AND type = '{$model->type}'";
                $model2 = Mage2CatalogProductBundleOption::model()->find($condition);
                if (!$model2) { //add new
                    $model2 = new Mage2CatalogProductBundleOption();
                    $model2->parent_id = $parentId2;
                    $model2->required = $model->required;
                    $model2->position = $model->position;
                    $model2->type = $model->type;
                    if (!$model2->save()) {
                        $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                    }
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->option_id,
                    'm2_id' => $model2->option_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'option_id',
                    'can_reset' => $canReset,
                    'step_index' => "5ProductOption"
                ]);
            } else { //update
                $model2 = Mage2CatalogProductBundleOption::model()->find("option_id = {$optionId2}");
            }
            //migrate related data
            if ($model2->option_id) {
                //get string migrated store ids
                $strMigratedStoreIds = implode(',', array_keys($mappingStores));
                /**
                 * Table: catalog_product_bundle_option_value
                 */
                $condition = "option_id = {$model->option_id} AND store_id IN ({$strMigratedStoreIds})";
                $bundleOptionValues = Mage1CatalogProductBundleOptionValue::model()->findAll($condition);
                if ($bundleOptionValues) {
                    foreach ($bundleOptionValues as $bundleOptionValue) {
                        $storeId2 = $mappingStores[$bundleOptionValue->store_id];
                        $condition = "option_id = {$model2->option_id} AND store_id = {$storeId2}";
                        $bundleOptionValue2 = Mage2CatalogProductBundleOptionValue::model()->find($condition);
                        if (!$bundleOptionValue2) { //add new
                            $bundleOptionValue2 = new Mage2CatalogProductBundleOptionValue();
                            $bundleOptionValue2->option_id = $model2->option_id;
                            $bundleOptionValue2->store_id = $storeId2;
                            $bundleOptionValue2->title = $bundleOptionValue->title;
                            //save
                            if (!$bundleOptionValue2->save()) {
                                $this->errors[] = get_class($bundleOptionValue2) . ": " . UBMigrate::getStringErrors($bundleOptionValue2->getErrors());
                            }
                        }
                    }
                }

                /**
                 * Table: catalog_product_bundle_selection
                 */
                $condition = "option_id = {$model->option_id}";
                if (!UBMigrate::getSetting(5, 'select_all')) {
                    $mappingProducts = UBMigrate::getMappingData('catalog_product_entity', 5);
                    $strMigratedProductIds = implode(',', array_keys($mappingProducts));
                    $condition .= " AND product_id IN ({$strMigratedProductIds})";
                }
                $bundleSelections = Mage1CatalogProductBundleSelection::model()->findAll($condition);
                if ($bundleSelections) {
                    foreach ($bundleSelections as $bundleSelection) {
                        if (isset($mappingProducts) AND $mappingProducts) {
                            $parentProductId2 = $mappingProducts[$bundleSelection->parent_product_id];
                            $productId2 = $mappingProducts[$bundleSelection->product_id];
                        } else {
                            $parentProductId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $bundleSelection->parent_product_id);
                            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $bundleSelection->product_id);
                        }
                        $m2Id = UBMigrate::getM2EntityId('5_product_option', 'catalog_product_bundle_selection', $bundleSelection->selection_id);
                        $canReset = UBMigrate::RESET_YES;
                        if (!$m2Id) {
                            $condition = "option_id = {$model2->option_id} AND parent_product_id = {$parentProductId2} AND product_id = {$productId2} AND position = {$bundleSelection->position}";
                            $bundleSelection2 = Mage2CatalogProductBundleSelection::model()->find($condition);
                            if (!$bundleSelection2) { //add new
                                $bundleSelection2 = new Mage2CatalogProductBundleSelection();
                                //fill values
                                $bundleSelection2->option_id = $model2->option_id;
                                $bundleSelection2->parent_product_id = $parentId2;
                                $bundleSelection2->product_id = $productId2;
                                $bundleSelection2->position = $bundleSelection->position;
                                $bundleSelection2->is_default = $bundleSelection->is_default;
                                $bundleSelection2->selection_price_type = $bundleSelection->selection_price_type;
                                $bundleSelection2->selection_price_value = $bundleSelection->selection_price_value;
                                $bundleSelection2->selection_qty = $bundleSelection->selection_qty;
                                $bundleSelection2->selection_can_change_qty = $bundleSelection->selection_can_change_qty;
                                //save
                                if (!$bundleSelection2->save()) {
                                    $this->errors[] = get_class($bundleSelection2) . ": " . UBMigrate::getStringErrors($bundleSelection2->getErrors());
                                }
                            } else {
                                $canReset = UBMigrate::RESET_NO;
                            }
                            //save to map table
                            UBMigrate::log([
                                'entity_name' => $bundleSelection->tableName(),
                                'm1_id' => $bundleSelection->selection_id,
                                'm2_id' => $bundleSelection2->selection_id,
                                'm2_model_class' => get_class($bundleSelection2),
                                'm2_key_field' => 'selection_id',
                                'can_reset' => $canReset,
                                'step_index' => "5ProductOption"
                            ]);
                        } else { //update
                            $bundleSelection2 = Mage2CatalogProductBundleSelection::model()->find("selection_id = {$m2Id}");
                        }
                        //migrate child data
                        if ($bundleSelection2->selection_id) {
                            /**
                             * Table: catalog_product_bundle_selection_price
                             */
                            $strMigratedWebsiteIds = implode(',', array_keys($mappingWebsites));
                            $condition = "selection_id = {$bundleSelection->selection_id} AND website_id IN ({$strMigratedWebsiteIds})";
                            $selectionPrices = Mage1CatalogProductBundleSelectionPrice::model()->findAll($condition);
                            if ($selectionPrices) {
                                foreach ($selectionPrices as $selectionPrice) {
                                    $websiteId2 = $mappingWebsites[$selectionPrice->website_id];
                                    $selectionPrice2 = Mage2CatalogProductBundleSelectionPrice::model()->find("selection_id = {$bundleSelection2->selection_id} AND website_id = {$websiteId2}");
                                    if (!$selectionPrice2) {
                                        $selectionPrice2 = new Mage2CatalogProductBundleSelectionPrice();
                                        $selectionPrice2->selection_id = $bundleSelection2->selection_id;
                                        $selectionPrice2->website_id = $websiteId2;
                                        $selectionPrice2->selection_price_type = $selectionPrice->selection_price_type;
                                        $selectionPrice2->selection_price_value = $selectionPrice->selection_price_value;
                                        if (!$selectionPrice2->save()) {
                                            $this->errors[] = get_class($selectionPrice2) . ": " . UBMigrate::getStringErrors($selectionPrice2->getErrors());
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductDownloadableLinks($downloadableLinks, $mappingWebsites, $mappingStores)
    {
        /**
         * Table: downloadable_link
         */
        foreach ($downloadableLinks as $model) {
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->product_id);
            $linkId2 = UBMigrate::getM2EntityId('5_product_download', 'downloadable_link', $model->link_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$linkId2) {
                $condition = "product_id = {$productId2} AND sort_order = {$model->sort_order} AND link_file = '{$model->link_file}'";
                $model2 = Mage2DownloadableLink::model()->find($condition);
                if (!$model2) { //add new
                    $model2 = new Mage2DownloadableLink();
                    foreach ($model2->attributes as $key => $value) {
                        if (isset($model->$key)) {
                            $model2->$key = $model->$key;
                        }
                    }
                    $model2->link_id = null;
                    $model2->product_id = $productId2;
                    if (!$model2->save()) {
                        $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                    }
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save to map table
                UBMigrate::log([
                    'entity_name' => $model->tableName(),
                    'm1_id' => $model->link_id,
                    'm2_id' => $model2->link_id,
                    'm2_model_class' => get_class($model2),
                    'm2_key_field' => 'link_id',
                    'can_reset' => $canReset,
                    'step_index' => "5ProductDownload"
                ]);
            } else { //update
                $model2 = Mage2DownloadableLink::model()->find("link_id = {$linkId2}");
            }
            //migrate related data
            if ($model2->link_id) {
                /**
                 * Table: downloadable_link_price
                 */
                $strMigratedWebsiteIds = implode(',', array_keys($mappingWebsites));
                $linkPrices = Mage1DownloadableLinkPrice::model()->findAll("link_id = {$model->link_id} AND website_id IN ({$strMigratedWebsiteIds})");
                if ($linkPrices) {
                    foreach ($linkPrices as $linkPrice) {
                        $websiteId2 = $mappingWebsites[$linkPrice->website_id];
                        $linkPrice2 = Mage2DownloadableLinkPrice::model()->find("link_id = {$model2->link_id} AND website_id = {$websiteId2}");
                        if (!$linkPrice2) { //add new
                            $linkPrice2 = new Mage2DownloadableLinkPrice();
                            $linkPrice2->link_id = $model2->link_id;
                            $linkPrice2->website_id = $websiteId2;
                            $linkPrice2->price = $linkPrice->price;
                            if (!$linkPrice2->save()) {
                                $this->errors[] = get_class($linkPrice2) . ": " . UBMigrate::getStringErrors($linkPrice2->getErrors());
                            }
                        }
                    }
                }
                /**
                 * Table: downloadable_link_title
                 */
                $strMigratedStoreIds = implode(',', array_keys($mappingStores));
                $linkTitles = Mage1DownloadableLinkTitle::model()->findAll("link_id = {$model->link_id} AND store_id IN ({$strMigratedStoreIds})");
                if ($linkTitles) {
                    foreach ($linkTitles as $linkTitle) {
                        $storeId2 = $mappingStores[$linkTitle->store_id];
                        $linkTitle2 = Mage2DownloadableLinkTitle::model()->find("link_id = {$model2->link_id} AND store_id = {$storeId2}");
                        if (!$linkTitle2) { //add new
                            $linkTitle2 = new Mage2DownloadableLinkTitle();
                            $linkTitle2->link_id = $model2->link_id;
                            $linkTitle2->store_id = $storeId2;
                            $linkTitle2->title = $linkTitle->title;
                            //save
                            if (!$linkTitle2->save()) {
                                $this->errors[] = get_class($linkTitle2) . ": " . UBMigrate::getStringErrors($linkTitle2->getErrors());
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCatalogProductDownloadableSamples($downloadSamples, $mappingStores)
    {
        /**
         * Table: downloadable_sample
         */
        foreach ($downloadSamples as $model) {
            $productId2 = UBMigrate::getM2EntityId(5, 'catalog_product_entity', $model->product_id);
            $condition = "product_id = {$productId2} AND sort_order = {$model->sort_order} AND sample_file = '{$model->sample_file}'";
            $model2 = Mage2DownloadableSample::model()->find($condition);
            if (!$model2) { //add new
                $model2 = new Mage2DownloadableSample();
                foreach ($model2->attributes as $key => $value) {
                    if (isset($model->$key)) {
                        $model2->$key = $model->$key;
                    }
                }
                $model2->sample_id = null;
                $model2->product_id = $productId2;
                if (!$model2->save()) {
                    $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                }
            }
            //migrate related data
            if ($model2->sample_id) {
                /**
                 * Table: downloadable_sample_title
                 */
                $strMigratedStoreIds = implode(',', array_keys($mappingStores));
                $condition = "sample_id = {$model->sample_id} AND store_id IN ({$strMigratedStoreIds})";
                $sampleTitles = Mage1DownloadableSampleTitle::model()->findAll($condition);
                if ($sampleTitles) {
                    foreach ($sampleTitles as $sampleTitle) {
                        $storeId2 = $mappingStores[$sampleTitle->store_id];
                        $sampleTitle2 = Mage2DownloadableSampleTitle::model()->find("sample_id = {$model2->sample_id} AND store_id = {$storeId2}");
                        if (!$sampleTitle2) { //add new
                            $sampleTitle2 = new Mage2DownloadableSampleTitle();
                            $sampleTitle2->sample_id = $model2->sample_id;
                            $sampleTitle2->store_id = $storeId2;
                            $sampleTitle2->title = $sampleTitle->title;
                            if (!$sampleTitle2->save()) {
                                $this->errors[] = get_class($sampleTitle2) . ": " . UBMigrate::getStringErrors($sampleTitle2->getErrors());
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

}
