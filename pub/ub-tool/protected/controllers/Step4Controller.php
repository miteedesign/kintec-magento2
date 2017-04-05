<?php

include_once('BaseController.php');

/**
 * @todo: Catalog categories migration
 *
 * Class Step4Controller
 */
class Step4Controller extends BaseController
{
    protected $stepIndex = 4;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //get total categories from Magento 1
            $totalCategories = Mage1CatalogCategoryEntity::model()->count("level > 0");
            //get all root categories from Magento1
            $rootCategories = Mage1CatalogCategoryEntity::model()->findAll("level = 1");

            if (Yii::app()->request->isPostRequest) {
                $selectAll = Yii::app()->request->getParam('select_all_categories', false);
                //get selected data ids
                $selectedCategoryIds = Yii::app()->request->getParam('category_ids', array());
                if ($selectedCategoryIds) {
                    //make setting data to save
                    $settingData = [
                        'category_ids' => $selectedCategoryIds,
                        'select_all_category' => (sizeof($selectedCategoryIds) == $totalCategories) ? 1 : 0
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
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have to select at least one Product category to migrate.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'totalCategories' => $totalCategories,
                'rootCategories' => $rootCategories
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
            //get migrated store ids
            $mappingStores = UBMigrate::getMappingData('core_store', 2);
            //get setting data
            $settingData = $step->getSettingData();
            $selectedCategoryIds = (isset($settingData['category_ids'])) ? $settingData['category_ids'] : [];
            try {
                //start migrate data by settings
                if ($selectedCategoryIds) {
                    //build condition to get data
                    if (!UBMigrate::getSetting(4, 'select_all_category')) {
                        $strSelectedCategoryIds = implode(',', $selectedCategoryIds);
                        $condition = "entity_id IN ({$strSelectedCategoryIds})";
                    } else {
                        $condition = 'entity_id > 1';
                    }
                    //get max total
                    $max = Mage1CatalogCategoryEntity::model()->count($condition);
                    $offset = UBMigrate::getCurrentOffset(4, Mage1CatalogCategoryEntity::model()->tableName());
                    //get data by limit and offset
                    $categories = UBMigrate::getListObjects('Mage1CatalogCategoryEntity', $condition, $offset, $this->limit, "level ASC, entity_id ASC");
                    if ($categories) {
                        $this->_migrateCatalogCategories($categories, $mappingStores);
                    }
                    //log
                    Yii::log(($offset == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                    if ($offset == 0) {
                        //update status of this step to processing
                        $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                    }
                }
                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    if ($offset >= $max) {
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1CatalogCategoryEntity::model()->tableName(), $max, $this->stepIndex);

                            //re-update default root category id for stores
                            $mappingStoreGroups = UBMigrate::getMappingData('core_store_group', 2);
                            $strStoreGroups = implode(',', $mappingStoreGroups);
                            //get all store groups migrated
                            $storeGroups = Mage2StoreGroup::model()->findAll("group_id IN ({$strStoreGroups})");
                            foreach ($storeGroups as $storeGroup) {
                                if ($storeGroup->root_category_id) {
                                    $newRootCategoryId = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $storeGroup->root_category_id);
                                    if ($newRootCategoryId) {
                                        $storeGroup->root_category_id = $newRootCategoryId;
                                        $storeGroup->update();
                                    }
                                }
                            }

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
                        UBMigrate::updateCurrentOffset(Mage1CatalogCategoryEntity::model()->tableName(), ($offset + $this->limit), $this->stepIndex);

                        //start calculate percent run ok
                        $totalSteps = UBMigrate::getTotalStepCanRunMigrate();
                        $percentOfOnceStep = (1 / $totalSteps) * 100;
                        $n = ceil($max / $this->limit);
                        $percentUp = $percentOfOnceStep / $n;
                        //end calculate percent run ok

                        //update result to respond
                        $rs['status'] = 'ok';
                        $rs['percent_up'] = $percentUp;
                        $msg = ($offset == 0) ? 'Migrated data in step #%s ok with' : '(Continued) migrated data in step #%s ok with';
                        $rs['message'] = Yii::t(
                            'frontend',
                            "{$msg} %s1 Catalog Categories",
                            array('%s' => $this->stepIndex, '%s1' => sizeof($categories))
                        );
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

    private function _migrateCatalogCategories($categories, $mappingStores)
    {
        /**
         * Table: catalog_category_entity
         */
        foreach ($categories as $category) {
            $m2Id = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $category->entity_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) {
                $category2 = new Mage2CatalogCategoryEntity();
                $category2->entity_id = null; //we will not take old entity_id
                $category2->attribute_set_id = UBMigrate::getMage2AttributeSetId($category->attribute_set_id, UBMigrate::CATEGORY_TYPE_CODE);
                $category2->parent_id = ($category->parent_id > 1) ? UBMigrate::getM2EntityId(4, 'catalog_category_entity', $category->parent_id) : $category->parent_id;
                $category2->created_at = $category->created_at;
                $category2->updated_at = $category->updated_at;
                $category2->path = $category->path;
                $category2->position = $category->position;
                $category2->level = $category->level;
                $category2->children_count = $category->children_count;
                //save
                if (!$category2->save()) {
                    $this->errors[] = get_class($category2) . ": " . UBMigrate::getStringErrors($category2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $category->tableName(),
                        'm1_id' => $category->entity_id,
                        'm2_id' => $category2->entity_id,
                        'm2_model_class' => get_class($category2),
                        'm2_key_field' => 'entity_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);

                    //re-update path for this category
                    $path = explode('/', $category2->path); //1/2/3/4
                    $m = (sizeof($path) - 1);
                    for ($i = 1; $i < $m; $i++) {
                        $path[$i] = UBMigrate::getM2EntityId(4, 'catalog_category_entity', $path[$i]);
                    }
                    $path[$m] = $category2->entity_id;
                    $category2->path = implode('/', $path);
                    $category2->update();

                    //migrate category EAV  data
                    $this->_migrateCatalogCategoryEAV($category->entity_id, $category2->entity_id, $mappingStores);
                    //migrate url_rewrite for category
                    $this->_migrateCatalogCategoryURLRewrite($category->entity_id, $category2->entity_id, $mappingStores);
                }
            }
        }

        return true;
    }

    private function _migrateCatalogCategoryEAV($entityId1, $entityId2, $mappingStores)
    {
        //get string migrated store ids
        $strStoreIds = implode(',', array_keys($mappingStores));
        /*
         * Get black list attribute ids
         * We do not migrate values of bellow attributes
         * So, we will map to reset values of it to default values
        */
        $entityTypeId = UBMigrate::getMage1EntityTypeId(UBMigrate::CATEGORY_TYPE_CODE);
        $entityTypeId2 = UBMigrate::getMage2EntityTypeId(UBMigrate::CATEGORY_TYPE_CODE);
        $resetAttributes = array(
            UBMigrate::getMage1AttributeId('display_mode', $entityTypeId) => 'PRODUCTS',
            UBMigrate::getMage1AttributeId('landing_page', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_design', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_design_from', $entityTypeId) => null,
            UBMigrate::getMage1AttributeId('custom_design_to', $entityTypeId) => null,
            UBMigrate::getMage1AttributeId('page_layout', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_layout_update', $entityTypeId) => '',
            UBMigrate::getMage1AttributeId('custom_apply_to_products', $entityTypeId) => 1,
            UBMigrate::getMage1AttributeId('custom_use_parent_settings', $entityTypeId) => 1,
        );
        $resetAttributeIds = array_keys($resetAttributes);

        $eavTables = [
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_int',
            'catalog_category_entity_text',
            'catalog_category_entity_varchar'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage1{$className}";
            $className2 = "Mage2{$className}";
            $models = $className1::model()->findAll("entity_id = {$entityId1} AND store_id IN ({$strStoreIds})");
            if ($models) {
                foreach ($models as $model) {
                    $storeId2 = $mappingStores[$model->store_id];
                    $attributeId2 = UBMigrate::getMage2AttributeId($model->attribute_id, $entityTypeId2);
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

        return true;
    }

    private function _migrateCatalogCategoryURLRewrite($entityId1, $entityId2, $mappingStores)
    {
        //get string migrated store ids
        $strStoreIds = implode(',', array_keys($mappingStores));
        //url_rewrite for category
        $condition = "category_id = {$entityId1} AND product_id IS NULL AND store_id IN ({$strStoreIds})";
        $urls = Mage1UrlRewrite::model()->findAll($condition);
        if ($urls) {
            foreach ($urls as $url) {
                $storeId2 = $mappingStores[$url->store_id];
                $condition = "store_id = {$storeId2} AND entity_id = {$entityId2} AND entity_type = 'category'";
                $url2 = Mage2UrlRewrite::model()->find($condition);
                if (!$url2) { //add new
                    $url2 = new Mage2UrlRewrite();
                    $url2->entity_type = 'category';
                    $url2->entity_id = $entityId2;
                    $url2->request_path = $url->request_path;
                    //re-update category id for target_path
                    $url2->target_path = substr($url->target_path, 0, strrpos($url->target_path, '/')) .'/'. $entityId2;
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
                    //save
                    if (!$url2->save()) {
                        $this->errors[] = get_class($url2) . ": " . UBMigrate::getStringErrors($url2->getErrors());
                    }
                }
            }
        }

        return true;
    }

}
