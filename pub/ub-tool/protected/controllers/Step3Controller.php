<?php

include_once('BaseController.php');

/**
 * @todo: Product Attribute sets, Product Attribute Groups, Product Attributes migration
 *
 * Class Step3Controller
 */
class Step3Controller extends BaseController
{
    protected $stepIndex = 3;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {

            //get product entity type id
            $productEntityTypeId = UBMigrate::getMage1EntityTypeId(UBMigrate::PRODUCT_TYPE_CODE);

            //get all product attribute sets in magento1
            $attributeSets = Mage1AttributeSet::model()->findAll("entity_type_id = {$productEntityTypeId}");

            //Magento2 was not used some attributes
            $ignoreAttributeCodes = "'group_price', 'msrp_enabled'";

            //get all product attributes with ignore condition
            $criteria = new CDbCriteria(array(
                "order" => "is_user_defined DESC, attribute_id ASC",
                "condition" => "entity_type_id = {$productEntityTypeId} AND attribute_code NOT IN ({$ignoreAttributeCodes})"
            ));
            $attributes = Mage1Attribute::model()->findAll($criteria);

            if (Yii::app()->request->isPostRequest) {
                //get selected data ids
                $selectedAttrSetIds = Yii::app()->request->getParam('attribute_set_ids', array());
                $selectedAttrGroupIds = Yii::app()->request->getParam('attribute_group_ids', array());
                $selectedAttrIds = Yii::app()->request->getParam('attribute_ids', array());
                if ($selectedAttrSetIds AND $selectedAttrGroupIds AND $selectedAttrIds) {
                    //make setting data to save
                    $settingData = [
                        'attribute_set_ids' => $selectedAttrSetIds,
                        'attribute_group_ids' => $selectedAttrGroupIds,
                        'attribute_ids' => $selectedAttrIds
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
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have not select data to migrate yet.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'productEntityTypeId' => $productEntityTypeId,
                'attributeSets' => $attributeSets,
                'attributes' => $attributes
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
            //get mapping stores
            $mappingStores = UBMigrate::getMappingData('core_store', 2);

            //get setting data
            $settingData = $step->getSettingData();
            $selectedAttrSetIds = (isset($settingData['attribute_set_ids'])) ? $settingData['attribute_set_ids'] : [];
            $selectedAttrGroupIds = (isset($settingData['attribute_group_ids'])) ? $settingData['attribute_group_ids'] : [];
            $selectedAttrIds = (isset($settingData['attribute_ids'])) ? $settingData['attribute_ids'] : [];

            //some variables for paging
            $max1 = $max2 = $max3 = $max4 = 0;
            $offset1 = $offset2 = $offset3 = $offset4 = 0;
            try {
                //start migrate data by settings
                if ($selectedAttrSetIds AND $selectedAttrGroupIds AND $selectedAttrIds) {
                    //get product entity type id
                    $productEntityTypeId = UBMigrate::getMage1EntityTypeId(UBMigrate::PRODUCT_TYPE_CODE);

                    /**
                     * Table: eav_attribute_set
                     */
                    //build condition to get data
                    $strSelectedAttrSetIds = implode(',', $selectedAttrSetIds);
                    $condition = "entity_type_id = {$productEntityTypeId} AND attribute_set_id IN ({$strSelectedAttrSetIds})";
                    //get max total
                    $max1 = Mage1AttributeSet::model()->count($condition);
                    $offset1 = UBMigrate::getCurrentOffset(3, Mage1AttributeSet::model()->tableName());
                    $attributeSets = UBMigrate::getListObjects('Mage1AttributeSet', $condition, $offset1, $this->limit, "attribute_set_id ASC");
                    if ($attributeSets) {
                        $this->_migrateAttributeSets($attributeSets);
                    }

                    //log for first entry
                    Yii::log(($offset1 == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                    if ($offset1 == 0) {
                        //update status of this step to processing
                        $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                    }

                    /**
                     * Table: eav_attribute_group
                     * we only start migrate attribute groups when migrated all attribute sets
                     */
                    if ($offset1 >= $max1) {
                        //condition to get data
                        $strSelectedAttrGroupIds = implode(',', $selectedAttrGroupIds);
                        $condition = "attribute_group_id IN ({$strSelectedAttrGroupIds})";
                        //get max total
                        $max2 = Mage1AttributeGroup::model()->count($condition);
                        $offset2 = UBMigrate::getCurrentOffset(3, Mage1AttributeGroup::model()->tableName());
                        //get data by limit and offset
                        $attributeGroups = UBMigrate::getListObjects('Mage1AttributeGroup', $condition, $offset2, $this->limit, "attribute_group_id ASC");
                        if ($attributeGroups) {
                            $this->_migrateAttributeGroups($attributeGroups);
                        }
                    }

                    /**
                     * Table: eav_attribute
                     * we only migrate attributes when all attribute set and attribute groups was migrated
                     */
                    if ($offset1 >= $max1 AND $offset2 >= $max2) {
                        //condition to get data
                        $strSelectedAttrIds = implode(',', $selectedAttrIds);
                        $condition = "entity_type_id = {$productEntityTypeId} AND attribute_id IN ({$strSelectedAttrIds})";
                        //get max total
                        $max3 = Mage1Attribute::model()->count($condition);
                        $offset3 = UBMigrate::getCurrentOffset(3, Mage1Attribute::model()->tableName());
                        //get data by limit and offset
                        $attributes = UBMigrate::getListObjects('Mage1Attribute', $condition, $offset3, $this->limit, "attribute_id ASC");
                        if ($attributes) {
                            $this->_migrateAttributes($attributes, $mappingStores);
                        }
                    }

                    /**
                     * Table: eav_entity_attribute
                     */
                    //if has migrated all attribute sets, attribute groups and attributes
                    if ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3) {
                        //migrate data in table eav_entity_attribute
                        $this->_migrateEavEntityAttribute($productEntityTypeId, $strSelectedAttrSetIds, $strSelectedAttrGroupIds, $strSelectedAttrIds);
                    }
                }

                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    if ($offset1 >= $max1 AND $offset2 >= $max2 AND $offset3 >= $max3) {
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1AttributeSet::model()->tableName(), $max1, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1AttributeGroup::model()->tableName(), $max2, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1Attribute::model()->tableName(), $max3, $this->stepIndex);

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
                            UBMigrate::updateCurrentOffset(Mage1AttributeSet::model()->tableName(), ($offset1 + $this->limit), $this->stepIndex);
                        }
                        if ($max2) { // has done with attribute sets
                            UBMigrate::updateCurrentOffset(Mage1AttributeGroup::model()->tableName(), ($offset2 + $this->limit), $this->stepIndex);
                        }
                        if ($max3) { // has done with attributes
                            UBMigrate::updateCurrentOffset(Mage1Attribute::model()->tableName(), ($offset3 + $this->limit), $this->stepIndex);
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
                        if (isset($attributeSets) AND $attributeSets) {
                            $msg .= ' %s1 Attribute Sets';
                            $data['%s1'] = sizeof($attributeSets);
                        }
                        if (isset($attributeGroups) AND $attributeGroups) {
                            $msg .= ' %s2 Attribute Groups';
                            $data['%s2'] = sizeof($attributeGroups);
                        }
                        if (isset($attributes) AND $attributes) {
                            $msg .= ' %s3 Attributes';
                            $data['%s3'] = sizeof($attributes);
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

    private function _migrateAttributeSets($attributeSets)
    {
        /**
         * Table: eav_attribute_set
         */
        foreach ($attributeSets as $attributeSet) {
            //we will change name of attribute set migrated from M1
            $attributeSetName2 = $attributeSet->attribute_set_name . UBMigrate::ATTR_SET_ENDFIX;
            $entityTypeId2 = UBMigrate::_getMage2EntityTypeId($attributeSet->entity_type_id);
            $m2Id = UBMigrate::getM2EntityId(3, 'eav_attribute_set', $attributeSet->attribute_set_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) {
                $condition = "entity_type_id = {$entityTypeId2} AND attribute_set_name = '{$attributeSetName2}'";
                $attributeSet2 = Mage2AttributeSet::model()->find($condition);
                if (!$attributeSet2) { //add new
                    $attributeSet2 = new Mage2AttributeSet();
                    $attributeSet2->entity_type_id = $entityTypeId2;
                    $attributeSet2->attribute_set_name = $attributeSetName2;
                    $attributeSet2->sort_order = $attributeSet->sort_order;
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save/update
                if ($attributeSet2->save()) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $attributeSet->tableName(),
                        'm1_id' => $attributeSet->attribute_set_id,
                        'm2_id' => $attributeSet2->attribute_set_id,
                        'm2_model_class' => get_class($attributeSet2),
                        'm2_key_field' => 'attribute_set_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                } else {
                    $this->errors[] = get_class($attributeSet2) . ": " . UBMigrate::getStringErrors($attributeSet2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateAttributeGroups($attributeGroups)
    {
        /**
         * Table: eav_attribute_set
         */
        foreach ($attributeGroups as $attributeGroup) {
            $attributeSetId2 = UBMigrate::getM2EntityId(3, 'eav_attribute_set', $attributeGroup->attribute_set_id);
            //we will change the name of Attribute Group migrated from M1
            $attributeGroupName2 = $attributeGroup->attribute_group_name . UBMigrate::ATTR_GROUP_ENDFIX;
            //NOTE: this values is new added in Magento2
            $attributeGroupCode2 = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($attributeGroupName2)), '-');
            $tabGroupCode2 = null;
            //we have to make some convert
            $attributeGroupCode2 = ($attributeGroupCode2 == 'general-migrated') ? 'product-details' : $attributeGroupCode2;
            $attributeGroupCode2 = ($attributeGroupCode2 == 'prices-migrated') ? 'advanced-pricing' : $attributeGroupCode2;
            $attributeGroupCode2 = ($attributeGroupCode2 == 'design-migrated') ? 'design' : $attributeGroupCode2;
            $tabGroupCode2 = ($attributeGroupCode2 == 'product-details') ? 'basic' : $tabGroupCode2;
            $tabGroupCode2 = ($attributeGroupCode2 == 'advanced-pricing') ? 'advanced' : $tabGroupCode2;
            //check map
            $m2Id = UBMigrate::getM2EntityId(3, 'eav_attribute_group', $attributeGroup->attribute_group_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) {
                $condition = "attribute_set_id = {$attributeSetId2} AND attribute_group_name = '{$attributeGroupName2}'";
                $attributeGroup2 = Mage2AttributeGroup::model()->find($condition);
                if (!$attributeGroup2) { //add new
                    $attributeGroup2 = new Mage2AttributeGroup();
                    $attributeGroup2->attribute_set_id = $attributeSetId2;
                    $attributeGroup2->attribute_group_name = $attributeGroupName2;
                    $attributeGroup2->attribute_group_code = $attributeGroupCode2;
                    $attributeGroup2->tab_group_code = $tabGroupCode2;
                    $attributeGroup2->sort_order = $attributeGroup->sort_order;
                    $attributeGroup2->default_id = $attributeGroup->default_id;
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save/update
                if ($attributeGroup2->save()) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $attributeGroup->tableName(),
                        'm1_id' => $attributeGroup->attribute_group_id,
                        'm2_id' => $attributeGroup2->attribute_group_id,
                        'm2_model_class' => get_class($attributeGroup2),
                        'm2_key_field' => 'attribute_group_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                } else {
                    $this->errors[] = get_class($attributeGroup2) . ": " . UBMigrate::getStringErrors($attributeGroup2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateAttributes($attributes, $mappingStores)
    {
        /**
         * Table: eav_attribute
         */
        foreach ($attributes as $attribute) {
            $m2Id = UBMigrate::getM2EntityId('3_attribute', 'eav_attribute', $attribute->attribute_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) {
                $attributeCode2 = $attribute->attribute_code;
                $entityTypeId2 = UBMigrate::_getMage2EntityTypeId($attribute->entity_type_id);
                $condition = "entity_type_id = {$entityTypeId2} AND attribute_code = '{$attributeCode2}'";
                $attribute2 = Mage2Attribute::model()->find($condition);
                if (!$attribute2) { //add new
                    $attribute2 = new Mage2Attribute();
                    foreach ($attribute2->attributes as $key => $value) {
                        if (isset($attribute->$key) AND $key != 'attribute_id') {
                            $attribute2->$key = $attribute->$key;
                        }
                    }
                    //we need re-update some values
                    $attribute2->is_user_defined = 1;
                    $attribute2->attribute_model = null;
                    $attribute2->frontend_model = null;
                    $attribute2->entity_type_id = $entityTypeId2;
                    $attribute2->backend_model = UBMigrate::getM2BackendModel($attribute->backend_model);
                    $attribute2->source_model = UBMigrate::getM2SourceModel($attribute->source_model);
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save/update
                if ($attribute2->save()) {
                    //update to map log
                    UBMigrate::log([
                        'entity_name' => $attribute->tableName(),
                        'm1_id' => $attribute->attribute_id,
                        'm2_id' => $attribute2->attribute_id,
                        'm2_model_class' => get_class($attribute2),
                        'm2_key_field' => 'attribute_id',
                        'can_reset' => $canReset,
                        'step_index' => "3Attribute"
                    ]);
                } else {
                    $this->errors[] = get_class($attribute2) . ": " . UBMigrate::getStringErrors($attribute2->getErrors());
                }
            } else { //update
                $attribute2 = Mage2Attribute::model()->find("attribute_id = {$m2Id}");
            }

            //start migrate Attribute EAV
            if ($attribute2->attribute_id) {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
                /**
                 * Table: eav_attribute_label
                 */
                $this->_migrateAttributeLabels($attribute, $attribute2, $mappingStores);
                /**
                 * Table: eav_attribute_option
                 */
                $this->_migrateAttributeOptions($attribute, $attribute2, $mappingStores);
                /**
                 * Table: catalog_eav_attribute
                 */
                $this->_migrateAttributeSettings($attribute, $attribute2);
            }
        }//end foreach attributes

        return true;
    }

    private function _migrateAttributeLabels($attribute, $attribute2, $mappingStores)
    {
        /**
         * Table: eav_attribute_label
         */
        $strMigratedStoreIds = implode(',', array_keys($mappingStores));
        $condition = "attribute_id = {$attribute->attribute_id} AND store_id IN ({$strMigratedStoreIds})";
        $attributeLabels = Mage1AttributeLabel::model()->findAll($condition);
        if ($attributeLabels) {
            foreach ($attributeLabels as $attributeLabel) {
                $storeId2 = $mappingStores[$attributeLabel->store_id];
                $condition = "attribute_id = {$attribute2->attribute_id} AND store_id = {$storeId2}";
                $attributeLabel2 = Mage2AttributeLabel::model()->find($condition);
                if (!$attributeLabel2) { //add new
                    $attributeLabel2 = new Mage2AttributeLabel();
                    $attributeLabel2->attribute_id = $attribute2->attribute_id;
                    $attributeLabel2->store_id = $storeId2;
                    $attributeLabel2->value = $attributeLabel->value;
                    //save
                    if (!$attributeLabel2->save()) {
                        $this->errors[] = get_class($attributeLabel2) . ": " . UBMigrate::getStringErrors($attributeLabel2->getErrors());
                    } else {
                        //for trace in CLI only
                        if ($this->isCLI) {
                            echo ".";
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateAttributeOptions($attribute, $attribute2, $mappingStores)
    {
        /**
         * Table: eav_attribute_option
         */
        $strMigratedStoreIds = implode(',', array_keys($mappingStores));
        $attributeOptions = Mage1AttributeOption::model()->findAll("attribute_id = {$attribute->attribute_id}");
        if ($attributeOptions) {
            foreach ($attributeOptions as $attributeOption) {
                $m2Id = UBMigrate::getM2EntityId('3_attribute_option', 'eav_attribute_option', $attributeOption->option_id);
                $canReset = UBMigrate::RESET_YES;
                if (!$m2Id) {
                    $attributeOption2 = new Mage2AttributeOption();
                    $attributeOption2->attribute_id = $attribute2->attribute_id;
                    $attributeOption2->sort_order = $attributeOption->sort_order;
                    if ($attributeOption2->save()) {
                        //update to map log
                        UBMigrate::log([
                            'entity_name' => $attributeOption->tableName(),
                            'm1_id' => $attributeOption->option_id,
                            'm2_id' => $attributeOption2->option_id,
                            'm2_model_class' => get_class($attributeOption2),
                            'm2_key_field' => 'option_id',
                            'can_reset' => $canReset,
                            'step_index' => "3AttributeOption"
                        ]);
                    } else {
                        $this->errors[] = get_class($attributeOption2) . ": " . UBMigrate::getStringErrors($attributeOption2->getErrors());
                    }
                } else { //update
                    $attributeOption2 = Mage2AttributeOption::model()->find("option_id = {$m2Id}");
                }
                //start migrate attribute option values
                if ($attributeOption2->option_id) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    /**
                     * Table: eav_attribute_option_value
                     */
                    $condition = "option_id = {$attributeOption->option_id} AND store_id IN ({$strMigratedStoreIds})";
                    $optionValues = Mage1AttributeOptionValue::model()->findAll($condition);
                    if ($optionValues) {
                        foreach ($optionValues as $optionValue) {
                            $storeId2 = $mappingStores[$optionValue->store_id];
                            $optionValue2 = Mage2AttributeOptionValue::model()->find("option_id = {$attributeOption2->option_id} AND store_id = {$storeId2}");
                            if (!$optionValue2) { //add new
                                $optionValue2 = new Mage2AttributeOptionValue();
                                $optionValue2->option_id = $attributeOption2->option_id;
                                $optionValue2->store_id = $storeId2;
                                $optionValue2->value = $optionValue->value;
                                if (!$optionValue2->save()) {
                                    $this->errors[] = get_class($optionValue2) . ": " . UBMigrate::getStringErrors($optionValue2->getErrors());
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateAttributeSettings($attribute, $attribute2)
    {
        /**
         * Table: catalog_eav_attribute
         */
        $attributeSetting = Mage1CatalogEavAttribute::model()->find("attribute_id = {$attribute->attribute_id}");
        if ($attributeSetting) {
            $attributeSetting2 = Mage2CatalogEavAttribute::model()->find("attribute_id = {$attribute2->attribute_id}");
            if (!$attributeSetting2) { //add new (a custom attribute)
                $attributeSetting2 = new Mage2CatalogEavAttribute();
                foreach ($attributeSetting2->attributes as $key => $value) {
                    if (isset($attributeSetting->$key)) {
                        $attributeSetting2->$key = $attributeSetting->$key;
                    }
                }
                //this is new field in Magento2. Default value is 0
                $attributeSetting2->is_required_in_admin_store = 0;
                //this was changed in Magento 2
                $attributeSetting2->attribute_id = $attribute2->attribute_id;
                $attributeSetting2->frontend_input_renderer = UBMigrate::getM2FrontendInputRenderer($attributeSetting2->frontend_input_renderer);
                //because some new rules with configurable attribute in Magento 2
                if ($attributeSetting->is_configurable) {
                    $attributeSetting2->apply_to = null;
                }
            } else {
                //update some values
                //$attributeSetting2->is_visible = $attributeSetting->is_visible;
                /*$attributeSetting2->is_searchable = $attributeSetting->is_searchable;
                $attributeSetting2->is_filterable = $attributeSetting->is_filterable;
                $attributeSetting2->is_html_allowed_on_front = $attributeSetting->is_html_allowed_on_front;
                $attributeSetting2->is_filterable_in_search = $attributeSetting->is_filterable_in_search;
                $attributeSetting2->used_in_product_listing = $attributeSetting->used_in_product_listing;
                $attributeSetting2->used_for_sort_by = $attributeSetting->used_for_sort_by;*/
                /*$attributeSetting2->is_comparable = $attributeSetting->is_comparable;
                $attributeSetting2->is_global = $attributeSetting->is_global;
                $attributeSetting2->is_visible_on_front = $attributeSetting->is_visible_on_front;
                $attributeSetting2->apply_to = $attributeSetting->apply_to;
                $attributeSetting2->is_visible_in_advanced_search = $attributeSetting->is_visible_in_advanced_search;//can split here (coming soon)
                */
                $attributeSetting2->position = $attributeSetting->position;
                $attributeSetting2->is_wysiwyg_enabled = $attributeSetting->is_wysiwyg_enabled;
                $attributeSetting2->is_used_for_price_rules = $attributeSetting->is_used_for_price_rules;
                $attributeSetting2->is_used_for_promo_rules = $attributeSetting->is_used_for_promo_rules;
            }
            //save/update
            if (!$attributeSetting2->save()) {
                $this->errors[] = get_class($attributeSetting2) . ": " . UBMigrate::getStringErrors($attributeSetting2->getErrors());
            } else {
                //for trace in CLI only
                if ($this->isCLI) {
                    echo ".";
                }
            }
        }

        return true;
    }

    private function _migrateEavEntityAttribute($productEntityTypeId, $strSelectedAttrSetIds, $strSelectedAttrGroupIds, $strSelectedAttrIds)
    {
        //get needed mapping data
        $mappingAttributeSets = UBMigrate::getMappingData('eav_attribute_set', 3);
        $mappingAttributeGroups = UBMigrate::getMappingData('eav_attribute_group', 3);
        $mappingAttributes = UBMigrate::getMappingData('eav_attribute', '3_attribute');
        /**
         * Table: eav_entity_attribute
         * we only migrate records related with products in here
         */
        $condition = "entity_type_id = {$productEntityTypeId} AND attribute_id IN ($strSelectedAttrIds)";
        $condition .= " AND attribute_set_id IN ({$strSelectedAttrSetIds})";
        $condition .= " AND attribute_group_id IN ({$strSelectedAttrGroupIds})";
        $entityAttributes = Mage1EntityAttribute::model()->findAll($condition);
        if ($entityAttributes) {
            foreach ($entityAttributes as $entityAttribute) {
                $entityTypeId2 = UBMigrate::_getMage2EntityTypeId($entityAttribute->entity_type_id);
                $attributeSetId2 = $mappingAttributeSets[$entityAttribute->attribute_set_id];
                $attributeGroupId2 = $mappingAttributeGroups[$entityAttribute->attribute_group_id];
                $attributeId2 = isset($mappingAttributes[$entityAttribute->attribute_id]) ? $mappingAttributes[$entityAttribute->attribute_id] : null;
                if ($attributeSetId2 AND $attributeGroupId2 AND $attributeId2) {
                    $condition = "attribute_id = {$attributeId2} AND attribute_group_id = {$attributeGroupId2}";
                    $entityAttribute2 = Mage2EntityAttribute::model()->find($condition);
                    if (!$entityAttribute2) { //add new
                        $entityAttribute2 = new Mage2EntityAttribute();
                        //fill values
                        $entityAttribute2->entity_type_id = $entityTypeId2;
                        $entityAttribute2->attribute_set_id = $attributeSetId2;
                        $entityAttribute2->attribute_group_id = $attributeGroupId2;
                        $entityAttribute2->attribute_id = $attributeId2;
                        $entityAttribute2->sort_order = $entityAttribute->sort_order;
                        //save or update
                        if (!$entityAttribute2->save()) {
                            $this->errors[] = get_class($entityAttribute2) . ": " . UBMigrate::getStringErrors($entityAttribute2->getErrors());
                        } else {
                            //for trace in CLI only
                            if ($this->isCLI) {
                                echo ".";
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

}
