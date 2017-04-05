<?php

include_once('BaseController.php');

/**
 * @todo: Websites, Store Groups and Stores Migration
 *
 * Class Step2Controller
 */
class Step2Controller extends BaseController
{
    protected $stepIndex = 2;
    protected $websites2 = [];
    protected $storeGroups2 = [];

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {

            //get list front-end websites from magento1 and exclude the admin website
            $condition = "code <> 'admin'";
            $websites = Mage1Website::model()->findAll($condition);

            if (Yii::app()->request->isPostRequest) {
                //get selected data ids
                $selectedWebsiteIds = Yii::app()->request->getParam('website_ids', array());
                $selectedStoreGroupIds = Yii::app()->request->getParam('store_group_ids', array());
                $selectedStoreIds = Yii::app()->request->getParam('store_ids', array());
                $selectAll = Yii::app()->request->getParam('select_all', false);

                if ($selectedWebsiteIds AND $selectedStoreGroupIds AND $selectedStoreIds) {
                    //add admin website id
                    array_push($selectedWebsiteIds, '0');
                    //add store group admin
                    array_push($selectedStoreGroupIds, '0');
                    //add admin store id
                    array_push($selectedStoreIds, '0');

                    //get current default website in M2
                    $defaultWebsite = Mage2Website::model()->find("is_default = 1");

                    //save settings data
                    $totalStore = sizeof(Mage1Store::model()->count());
                    $settingData = [
                        'website_ids' => $selectedWebsiteIds,
                        'store_group_ids' => $selectedStoreGroupIds,
                        'store_ids' => $selectedStoreIds,
                        'select_all' => $selectAll,
                        'select_all_website' => ((sizeof($selectedWebsiteIds) - 1) == sizeof($websites)) ? 1 : 0,
                        'select_all_store' => (sizeof($selectedStoreIds) == $totalStore) ? 1 : 0,
                        'default_website_id' => $defaultWebsite->website_id,
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
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have to select at least one website, one store group, one store to migrate.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'websites' => $websites,
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
            try {
                //get setting data
                $settingData = $step->getSettingData();
                $selectedWebsiteIds = (isset($settingData['website_ids'])) ? $settingData['website_ids'] : [];
                $selectedStoreGroupIds = (isset($settingData['store_group_ids'])) ? $settingData['store_group_ids'] : [];
                $selectedStoreIds = (isset($settingData['store_ids'])) ? $settingData['store_ids'] : [];
                //some variables for paging
                $max1 = $max2 = $max3 = 0;
                $offset1 = $offset2 = $offset3 = 0;

                //start migrate data by settings
                if ($selectedWebsiteIds AND $selectedStoreGroupIds AND $selectedStoreIds) {
                    /**
                     * Migrate websites
                     */
                    $strWebsiteIds = implode(',', $selectedWebsiteIds);
                    $condition = "website_id IN ({$strWebsiteIds})";
                    $max1 = Mage1Website::model()->count($condition);
                    $offset1 = UBMigrate::getCurrentOffset(2, Mage1Website::model()->tableName());
                    $websites = UBMigrate::getListObjects('Mage1Website', $condition, $offset1, $this->limit, "website_id ASC");
                    if ($websites) {
                        $this->_migrateWebsites($websites);
                    }

                    //log for first entry
                    Yii::log(($offset1 == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                    if ($offset1 == 0) {
                        //update status of this step to processing
                        $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                    }

                    /**
                     * Migrate Store Groups
                     */
                    if ($offset1 >= $max1) { //has migrated all selected websites
                        //get mapping websites
                        $mappingWebsites = UBMigrate::getMappingData('core_website', 2);
                        //build condition
                        $strGroupIds = implode(',', $selectedStoreGroupIds);
                        $condition = "group_id IN ({$strGroupIds})";
                        $max2 = Mage1StoreGroup::model()->count($condition);
                        $offset2 = UBMigrate::getCurrentOffset(2, Mage1StoreGroup::model()->tableName());
                        $storeGroups = UBMigrate::getListObjects('Mage1StoreGroup', $condition, $offset2, $this->limit, "group_id ASC");
                        if ($storeGroups) {
                            $this->_migrateStoreGroups($storeGroups, $mappingWebsites);
                        }
                    }

                    if ($offset1 >= $max1 AND $offset2 >= $max2) { //has migrated all selected Websites and Store Groups
                        //get mapping store groups
                        $mappingStoreGroups = UBMigrate::getMappingData('core_store_group', 2);
                        //build condition
                        $strStoreIds = implode(',', $selectedStoreIds);
                        $condition = "store_id IN ({$strStoreIds})";
                        $max3 = Mage1Store::model()->count($condition);
                        $offset3 = UBMigrate::getCurrentOffset(2, Mage1Store::model()->tableName());
                        $stores = UBMigrate::getListObjects('Mage1Store', $condition, $offset3, $this->limit, "store_id ASC");
                        if ($stores) {
                            $this->_migrateStores($stores, $mappingWebsites, $mappingStoreGroups);
                        }
                    }
                }

                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    if ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3) { //has migrated all
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1Website::model()->tableName(), $max1, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1StoreGroup::model()->tableName(), $max2, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1Store::model()->tableName(), $max3, $this->stepIndex);

                            //because only one website can be default website in system. So, we will unset old default website
                            $oldDefaultWebsiteId = UBMigrate::getSetting(2, 'default_website_id');
                            $oldDefaultWebsite = Mage2Website::model()->find("website_id = {$oldDefaultWebsiteId}");
                            if ($oldDefaultWebsite) {
                                $oldDefaultWebsite->is_default = 0;
                                $oldDefaultWebsite->update();
                            }

                            //get mapping stores
                            $mappingWebsites = UBMigrate::getMappingData('core_website', 2);
                            $mappingStoreGroups = UBMigrate::getMappingData('core_store_group', 2);
                            $mappingStores = UBMigrate::getMappingData('core_store', 2);

                            //re-update default_group_id for website
                            if ($mappingWebsites) {
                                $strWebsiteIds = implode(',', $mappingWebsites);
                                $websites2 = Mage2Website::model()->findAll("website_id IN ({$strWebsiteIds})");
                                foreach ($websites2 as $website2) {
                                    if (isset($mappingStoreGroups[$website2->default_group_id])) {
                                        $website2->default_group_id = $mappingStoreGroups[$website2->default_group_id];
                                        $website2->update();
                                    }
                                }
                            }
                            //re-update default_store_id for store group
                            if ($mappingStoreGroups) {
                                $strStoreGroupIds = implode(',', $mappingStoreGroups);
                                $storeGroups2 = Mage2StoreGroup::model()->findAll("group_id IN ({$strStoreGroupIds})");
                                foreach ($storeGroups2 as $storeGroup2) {
                                    if (isset($mappingStores[$storeGroup2->default_store_id])) {
                                        $storeGroup2->default_store_id = $mappingStores[$storeGroup2->default_store_id];
                                        $storeGroup2->update();
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
                        if ($max1) {
                            UBMigrate::updateCurrentOffset(Mage1Website::model()->tableName(), ($offset1 + $this->limit), $this->stepIndex);
                        }
                        if ($max2) { // has done with attribute sets
                            UBMigrate::updateCurrentOffset(Mage1StoreGroup::model()->tableName(), ($offset2 + $this->limit), $this->stepIndex);
                        }
                        if ($max3) { // has done with attributes
                            UBMigrate::updateCurrentOffset(Mage1Store::model()->tableName(), ($offset3 + $this->limit), $this->stepIndex);
                        }

                        //start calculate percent run ok
                        $totalSteps = UBMigrate::getTotalStepCanRunMigrate();
                        $percentOfOnceStep = (1 / $totalSteps) * 100;
                        $max = ($max3) ? $max3 : (($max2) ? $max2 : $max1);
                        $n = ceil($max / $this->limit);
                        $percentUp = ($percentOfOnceStep / 3) / $n;
                        //end calculate percent run ok

                        //update result to respond
                        $rs['status'] = 'ok';
                        $rs['percent_up'] = $percentUp;
                        $msg = ($offset1 == 0) ? 'Migrated data in step #%s ok with' : '(Continued) migrated data in step #%s ok with';
                        $data['%s'] = $this->stepIndex;
                        if (isset($websites) AND $websites) {
                            $msg .= ' %s1 Websites';
                            $data['%s1'] = sizeof($websites);
                        }
                        if (isset($storeGroups) AND $storeGroups) {
                            $msg .= ' %s2 Store Groups';
                            $data['%s2'] = sizeof($storeGroups);
                        }
                        if (isset($stores) AND $stores) {
                            $msg .= ' %s3 Stores';
                            $data['%s3'] = sizeof($stores);
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

    private function _migrateWebsites($websites)
    {
        foreach ($websites as $website1) {
            $m2Id = UBMigrate::getM2EntityId(2, 'core_website', $website1->website_id);
            $canReset = UBMigrate::RESET_YES;
            if (!is_null($m2Id)) {
                $websiteCode2 = ($website1->code == 'admin') ? $website1->code : "{$website1->code}_migrated";
                $website2 = Mage2Website::model()->find("code = '{$websiteCode2}'");
                if (!$website2) { //add new
                    $website2 = new Mage2Website();
                    $website2->code = $websiteCode2;
                    $website2->name = $website1->name;
                    $website2->sort_order = $website1->sort_order;
                    $website2->default_group_id = $website1->default_group_id; //we will re-update this when all store_group was migrated
                    $website2->is_default = $website1->is_default;
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save/update
                if ($website2->save()) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    $this->websites2[] = $website2;
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $website1->tableName(),
                        'm1_id' => $website1->website_id,
                        'm2_id' => $website2->website_id,
                        'm2_model_class' => get_class($website2),
                        'm2_key_field' => 'website_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                } else {
                    $this->errors[] = get_class($website2) . ": " . UBMigrate::getStringErrors($website2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateStoreGroups($storeGroups, $mappingWebsites)
    {
        if ($storeGroups) {
            foreach ($storeGroups as $storeGroup1) {
                $m2Id = UBMigrate::getM2EntityId(2, 'core_store_group', $storeGroup1->group_id);
                $websiteId2 = $mappingWebsites[$storeGroup1->website_id];
                $canReset = UBMigrate::RESET_YES;
                if (!is_null($m2Id)) {
                    //we will merge admin store group
                    if ($storeGroup1->website_id == 0 AND $storeGroup1->name = 'Default') {
                        $storeGroup2 = Mage2StoreGroup::model()->find("website_id = 0 AND name = 'Default'");
                        $canReset = UBMigrate::RESET_NO;
                    } else {
                        $storeGroup2 = new Mage2StoreGroup();
                        $storeGroup2->name = $storeGroup1->name;
                        $storeGroup2->website_id = $websiteId2;
                        $storeGroup2->root_category_id = $storeGroup1->root_category_id; //we will update this after migrate all product categories
                        $storeGroup2->default_store_id = $storeGroup1->default_store_id; //we will update this after migrated all stores
                    }
                    //save/update
                    if ($storeGroup2->save()) {
                        //for trace in CLI only
                        if ($this->isCLI) {
                            echo ".";
                        }
                        $this->storeGroups2[] = $storeGroup2;
                        //update to map log
                        UBMigrate::log([
                            'entity_name' => $storeGroup1->tableName(),
                            'm1_id' => $storeGroup1->group_id,
                            'm2_id' => $storeGroup2->group_id,
                            'm2_model_class' => get_class($storeGroup2),
                            'm2_key_field' => 'group_id',
                            'can_reset' => $canReset,
                            'step_index' => $this->stepIndex
                        ]);
                    } else {
                        $this->errors[] = get_class($storeGroup2) . ": " . UBMigrate::getStringErrors($storeGroup2->getErrors());
                    }
                }
            }
        }

        return true;
    }

    private function _migrateStores($stores, $mappingWebsites, $mappingStoreGroups)
    {
        if ($stores) {
            foreach ($stores as $store1) {
                $m2Id = UBMigrate::getM2EntityId(2, 'core_store', $store1->store_id);
                $canReset = UBMigrate::RESET_YES;
                if (!is_null($m2Id)) {
                    $storeCode2 = ($store1->code == 'admin') ? $store1->code : "{$store1->code}_migrated";
                    $store2 = Mage2Store::model()->find("code = '{$storeCode2}'");
                    if (!$store2) { //add new
                        $store2 = new Mage2Store();
                        $store2->code = $storeCode2;
                        $store2->name = $store1->name;
                        $store2->website_id = $mappingWebsites[$store1->website_id];
                        $store2->group_id = $mappingStoreGroups[$store1->group_id];
                        $store2->sort_order = $store1->sort_order;
                        $store2->is_active = $store1->is_active;
                    } else {
                        $canReset = UBMigrate::RESET_NO;
                    }
                    //save/update
                    if ($store2->save()) {
                        //for trace in CLI only
                        if ($this->isCLI) {
                            echo ".";
                        }
                        //update to map log
                        UBMigrate::log([
                            'entity_name' => $store1->tableName(),
                            'm1_id' => $store1->store_id,
                            'm2_id' => $store2->store_id,
                            'm2_model_class' => get_class($store2),
                            'm2_key_field' => 'store_id',
                            'can_reset' => $canReset,
                            'step_index' => $this->stepIndex
                        ]);
                    } else {
                        $this->errors[] = get_class($store2) . ": " . UBMigrate::getStringErrors($store2->getErrors());
                    }
                }
            }
        }

        return true;
    }

}
