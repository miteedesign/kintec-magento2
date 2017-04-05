<?php

include_once('BaseController.php');

/**
 * @todo: Customers migration
 *
 * Class Step6Controller
 */
class Step6Controller extends BaseController
{
    protected $stepIndex = 6;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("id = {$this->stepIndex}");
        $result = UBMigrate::checkStep($step->sorder);
        if ($result['allowed']) {
            //get all current customer groups in Magento 1
            $customerGroups = Mage1CustomerGroup::model()->findAll();

            if (Yii::app()->request->isPostRequest) {
                $selectAll = Yii::app()->request->getParam('select_all', false);
                //get selected data ids
                $selectedCustomerGroupIds = Yii::app()->request->getParam('customer_group_ids', array());
                if ($selectedCustomerGroupIds) {
                    //make setting data to save
                    $settingData = [
                        'customer_group_ids' => $selectedCustomerGroupIds,
                        'select_all_customer' => (sizeof($selectedCustomerGroupIds) == sizeof($customerGroups)) ? 1 : 0
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
                    Yii::app()->user->setFlash('note', Yii::t('frontend', 'You have to select at least one Customer Group to migrate or you can skip this step.'));
                }
            }

            $assignData = array(
                'step' => $step,
                'customerGroups' => $customerGroups,
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
            //get migrated website ids
            $strMigratedWebsiteIds = implode(',', array_keys($mappingWebsites));

            //get mapping stores
            $mappingStores = UBMigrate::getMappingData('core_store', 2);
            //get migrated store ids
            $strMigratedStoreIds = implode(',', array_keys($mappingStores));

            //get mapping customer groups
            $mappingCustomerGroups = UBMigrate::getMappingData('customer_group', 6);
            //get migrated customer group ids
            $strMigratedCustomerGroupIds = implode(',', array_keys($mappingCustomerGroups));

            //get setting data
            $settingData = $step->getSettingData();
            $selectedCustomerGroupIds = (isset($settingData['customer_group_ids'])) ? $settingData['customer_group_ids'] : [];

            //some variables for paging
            $max1 = $offset1 = $max2 = $offset2 = 0;
            try {
                //start migrate data by settings
                if ($selectedCustomerGroupIds) {
                    /**
                     * Table: customer_group
                     */
                    //make condition to get data
                    $strSelectedCustomerGroupIds = implode(',', $selectedCustomerGroupIds);
                    $condition = "customer_group_id IN ({$strSelectedCustomerGroupIds})";
                    //get max total
                    $max1 = Mage1CustomerGroup::model()->count($condition);
                    $offset1 = UBMigrate::getCurrentOffset(6, Mage1CustomerGroup::model()->tableName());
                    //log
                    Yii::log(($offset1 == 0) ? "Start running step #{$this->stepIndex}" : "Continue running step #{$this->stepIndex}", 'info', 'ub_data_migration');
                    if ($offset1 == 0) {
                        //update status of this step to processing
                        $step->updateStatus(UBMigrate::STATUS_PROCESSING);
                    }
                    //get data by limit and offset
                    $customerGroups = UBMigrate::getListObjects('Mage1CustomerGroup', $condition, $offset1, $this->limit, "customer_group_id ASC");
                    if ($customerGroups) {
                        $this->_migrateCustomerGroups($customerGroups, $mappingCustomerGroups);
                    }
                    // if has migrated all customer groups selected
                    if ($offset1 >= $max1) {
                        //start migrate other data related with a customer group
                        if ($strMigratedCustomerGroupIds) {
                            /**
                             * Table: customer_entity
                             */
                            $condition = "group_id IN ({$strMigratedCustomerGroupIds})";
                            if (!UBMigrate::getSetting(2, 'select_all_website')) {
                                $condition .= " AND (website_id IN ({$strMigratedWebsiteIds}) OR website_id IS NULL)";
                            }
                            if (!UBMigrate::getSetting(2, 'select_all_store')) {
                                $condition .= " AND store_id IN ({$strMigratedStoreIds})";
                            }
                            //get max total
                            $max2 = Mage1CustomerEntity::model()->count($condition);
                            $offset2 = UBMigrate::getCurrentOffset(6, Mage1CustomerEntity::model()->tableName());
                            //get data by limit and offset
                            $customers = UBMigrate::getListObjects('Mage1CustomerEntity', $condition, $offset2, $this->limit, "entity_id ASC");
                            if ($customers) {
                                $this->_migrateCustomers($customers, $mappingWebsites, $mappingStores, $mappingCustomerGroups);
                            }
                        }
                    }

                    /**
                     * Some tables in customer data structure is system tables
                     * Because we don't migrate customized customer attributes so we don't care these tables in here.
                     * //customer_eav_attribute
                     * //customer_eav_attribute_website
                     * //customer_form_attribute
                     * coming soon
                     */
                }
                //make result to respond
                if ($this->errors) {
                    $strErrors = implode('<br/>', $this->errors);
                    $rs['errors'] = $strErrors;
                    Yii::log($rs['errors'], 'error', 'ub_data_migration');
                } else {
                    //if all selected data migrated
                    if ($offset1 >= $max1 AND $offset2 >= $max2) {
                        //update status of this step to finished
                        if ($step->updateStatus(UBMigrate::STATUS_FINISHED)) {
                            //update current offset to max
                            UBMigrate::updateCurrentOffset(Mage1CustomerGroup::model()->tableName(), $max1, $this->stepIndex);
                            UBMigrate::updateCurrentOffset(Mage1CustomerEntity::model()->tableName(), $max2, $this->stepIndex);

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
                            UBMigrate::updateCurrentOffset(Mage1CustomerGroup::model()->tableName(), ($offset1 + $this->limit), $this->stepIndex);
                        }
                        if ($max2) {
                            UBMigrate::updateCurrentOffset(Mage1CustomerEntity::model()->tableName(), ($offset2 + $this->limit), $this->stepIndex);
                        }

                        //start calculate percent run ok
                        $totalSteps = UBMigrate::getTotalStepCanRunMigrate();
                        $percentOfOnceStep = (1 / $totalSteps) * 100;
                        $max = ($max2) ? $max2 : $max1;
                        $n = ceil($max / $this->limit);
                        $percentUp = ($percentOfOnceStep / 2) / $n;
                        //end calculate percent run ok

                        //update result to respond
                        $rs['status'] = 'ok';
                        $rs['percent_up'] = $percentUp;
                        //build message
                        $msg = ($offset1 == 0) ? 'Migrated data in step #%s ok with' : '(Continued) migrated data in step #%s ok with';
                        $data['%s'] = $this->stepIndex;
                        if (isset($customerGroups) AND $customerGroups) {
                            $msg .= ' %s1 Customer Groups.';
                            $data['%s1'] = sizeof($customerGroups);
                        }
                        if (isset($customers) AND $customers) {
                            $msg .= ' %s2 Customers.';
                            $data['%s2'] = sizeof($customers);
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

    private function _migrateCustomerGroups($customerGroups, $mappingCustomerGroups)
    {
        foreach ($customerGroups as $customerGroup1) {
            $map = isset($mappingCustomerGroups[$customerGroup1->customer_group_id]) ? true : false;
            $canReset = UBMigrate::RESET_YES;
            if (!$map) {
                $customerGroup2 = new Mage2CustomerGroup();
                $customerGroup2->customer_group_code = $customerGroup1->customer_group_code;
                //we will have to re-update tax_class_id when migrate tax classes in later (coming soon)
                $customerGroup2->tax_class_id = $customerGroup1->tax_class_id;
                //save
                if ($customerGroup2->save()) {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $customerGroup1->tableName(),
                        'm1_id' => $customerGroup1->customer_group_id,
                        'm2_id' => $customerGroup2->customer_group_id,
                        'm2_model_class' => get_class($customerGroup2),
                        'm2_key_field' => 'customer_group_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                    //we will migrate related customer tax_class here
                    $taxClass1 = Mage1TaxClass::model()->findByPk($customerGroup1->tax_class_id);
                    if ($taxClass1) {
                        $m2Id = UBMigrate::getM2EntityId(6, 'tax_class', $taxClass1->class_id);
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
                            //save/update
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
                                //re-update new tax_class_id for customer group
                                $customerGroup2->tax_class_id = $taxClass2->class_id;
                                $customerGroup2->update();
                            } else {
                                $this->errors[] = get_class($taxClass2) . ": " . UBMigrate::getStringErrors($taxClass2->getErrors());
                            }
                        }
                    }
                } else {
                    $this->errors[] = get_class($customerGroup2) . ": " . UBMigrate::getStringErrors($customerGroup2->getErrors());
                }
            }
        }

        return true;
    }

    private function _migrateCustomers($customers, $mappingWebsites, $mappingStores, $mappingCustomerGroups)
    {
        /**
         * Table: customer_entity
         */
        foreach ($customers as $customer) {
            $websiteId2 = isset($mappingWebsites[$customer->website_id]) ? $mappingWebsites[$customer->website_id] : null;
            $storeId2 = $mappingStores[$customer->store_id];
            $groupId2 = $mappingCustomerGroups[$customer->group_id];
            $m2Id = UBMigrate::getM2EntityId(6, 'customer_entity', $customer->entity_id);
            $canReset = UBMigrate::RESET_YES;
            if (!$m2Id) {
                $email2 = addslashes($customer->email);
                $condition = is_null($websiteId2) ? "email = '{$email2}' AND website_id IS NULL" : "email = '{$email2}' AND website_id = {$websiteId2}";
                $customer2 = Mage2CustomerEntity::model()->find($condition);
                if (!$customer2) { //add new
                    $customer2 = new Mage2CustomerEntity();
                    foreach ($customer2->attributes as $key => $value) {
                        if (isset($customer->$key)) {
                            $customer2->$key = $customer->$key;
                        }
                    }
                    $customer2->entity_id = null;
                    //because website_id, store_id was changed
                    $customer2->group_id = $groupId2;
                    $customer2->website_id = $websiteId2;
                    $customer2->store_id = $storeId2;
                } else {
                    $canReset = UBMigrate::RESET_NO;
                }
                //save/update
                if (!$customer2->save()) {
                    $this->errors[] = get_class($customer2) . ": " . UBMigrate::getStringErrors($customer2->getErrors());
                } else {
                    //for trace in CLI only
                    if ($this->isCLI) {
                        echo ".";
                    }
                    //save to map table
                    UBMigrate::log([
                        'entity_name' => $customer->tableName(),
                        'm1_id' => $customer->entity_id,
                        'm2_id' => $customer2->entity_id,
                        'm2_model_class' => get_class($customer2),
                        'm2_key_field' => 'entity_id',
                        'can_reset' => $canReset,
                        'step_index' => $this->stepIndex
                    ]);
                }
            } else {
                $customer2 = Mage2CustomerEntity::model()->find("entity_id = {$m2Id}");
            }
            //migrate related data
            if ($customer2->entity_id) {
                $flagUpdateCustomer2 = false;

                //migrate customer eav data
                $this->_migrateCustomerEAV($customer, $customer2, $flagUpdateCustomer2);

                //migrate customer address entity
                $this->_migrateCustomerAddressEntity($customer, $customer2, $flagUpdateCustomer2);

                //update value of some fields in main table has fill values from child tables
                if ($flagUpdateCustomer2) {
                    $customer2->update();
                }
            }
        }

        return true;
    }

    private function _migrateCustomerEAV($customer, &$customer2, &$flagUpdateCustomer2)
    {
        /**
         * Because some change in data structure of customer in Magento 2 from 0.74.0 - beta 12
         * Some attribute was move to parent entity. We have to declare this to re-update values of it from child tables
         */
        $neededUpdateAttr = array(
            'created_in',
            'firstname',
            'middlename',
            'lastname',
            'password_hash',
            'rp_token',
            'rp_token_created_at',
            'prefix',
            'suffix',
            'dob',
            'default_billing',
            'default_shipping',
            'taxvat',
            'confirmation',
            'gender'
        );
        //get customer entity type id in Magento2
        $entityTypeId = UBMigrate::getMage2EntityTypeId(UBMigrate::CUSTOMER_TYPE_CODE);
        $eavTables = [
            'customer_entity_datetime',
            'customer_entity_decimal',
            'customer_entity_int',
            'customer_entity_text',
            'customer_entity_varchar'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage1{$className}";
            $className2 = "Mage2{$className}";
            $models = $className1::model()->findAll("entity_id = $customer->entity_id");
            if ($models) {
                foreach ($models as $model) {
                    //because customer attribute id in Magento 2 can difference from Magento 1
                    $attributeId2 = UBMigrate::getMage2AttributeId($model->attribute_id, $entityTypeId);
                    if ($attributeId2) {
                        $condition = "entity_id = {$customer2->entity_id} AND attribute_id = {$attributeId2}";
                        $model2 = $className2::model()->find($condition);
                        if (!$model2) { //add new
                            $model2 = new $className2();
                            $model2->attribute_id = $attributeId2;
                            $model2->entity_id = $customer2->entity_id;
                            $model2->value = $model->value;
                            if (!$model2->save()) {
                                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                            }
                        }
                        /**
                         * Because some change in data structure of customer in Magento 2 from 0.74.0 - beta 12
                         * We have to do this to re-update values of it in parent entity
                         */
                        $attributeCode1 = UBMigrate::getMage1AttributeCode($model->attribute_id);
                        if (in_array($attributeCode1, $neededUpdateAttr) AND $customer2->hasAttribute($attributeCode1)) {
                            $customer2->$attributeCode1 = $model->value;
                            //we have to do this, because the Magento CE 2.0.0 or later was change method to hash password: md5() -> sha256()
                            if ($table == 'customer_entity_varchar' AND $attributeCode1 == 'password_hash') {
                                $customer2->$attributeCode1 .= ":0"; // In Magento2: 0 is HASH_VERSION_MD5
                            }
                            $flagUpdateCustomer2 = true;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function _migrateCustomerAddressEntity($customer, &$customer2, &$flagUpdateCustomer2)
    {
        /**
         * Table: customer_address_entity
         */
        $addressEntities = Mage1CustomerAddressEntity::model()->findAll("parent_id = {$customer->entity_id}");
        if ($addressEntities) {
            foreach ($addressEntities as $addressEntity) {
                $m2Id = UBMigrate::getM2EntityId('6_customer_address', 'customer_address_entity', $addressEntity->entity_id);
                $canReset = UBMigrate::RESET_YES;
                if (!$m2Id) {
                    $addressEntity2 = new Mage2CustomerAddressEntity();
                    foreach ($addressEntity2->attributes as $key => $value) {
                        if (isset($addressEntity->$key)) {
                            $addressEntity2->$key = $addressEntity->$key;
                        }
                    }
                    $addressEntity2->entity_id = null;
                    //because parent id was changed
                    $addressEntity2->parent_id = $customer2->entity_id;
                    /**
                     * some fields is new in Magento2 and required at this table, so we need to do this
                     * and we will update correct value for them later
                     */
                    $addressEntity2->country_id = '0';
                    $addressEntity2->firstname = 'unknown';
                    $addressEntity2->lastname = 'unknown';
                    $addressEntity2->street = 'unknown';
                    $addressEntity2->telephone = 'unknown';
                    $addressEntity2->city = 'unknown';
                    //save
                    if ($addressEntity2->save()) {
                        //for trace in CLI only
                        if ($this->isCLI) {
                            echo ".";
                        }
                        //update to map log
                        UBMigrate::log([
                            'entity_name' => $addressEntity->tableName(),
                            'm1_id' => $addressEntity->entity_id,
                            'm2_id' => $addressEntity2->entity_id,
                            'm2_model_class' => get_class($addressEntity2),
                            'm2_key_field' => 'entity_id',
                            'can_reset' => $canReset,
                            'step_index' => "6CustomerAddress"
                        ]);
                        /**
                         * Because customer_address_entity ids was changed
                         * we have to re-update the default_billing and default_shipping for each customer migrated here
                         **/
                        if ($customer2->default_billing AND ($customer2->default_billing == $addressEntity->entity_id)) {
                            $customer2->default_billing = $addressEntity2->entity_id;
                            $flagUpdateCustomer2 = true;
                        }
                        if ($customer2->default_shipping AND ($customer2->default_shipping == $addressEntity->entity_id)) {
                            $customer2->default_shipping = $addressEntity2->entity_id;
                            $flagUpdateCustomer2 = true;
                        }
                    } else {
                        $this->errors[] = get_class($addressEntity2) . ": " . UBMigrate::getStringErrors($addressEntity2->getErrors());
                    }
                } else { //update
                    $addressEntity2 = Mage2CustomerAddressEntity::model()->find("entity_id = {$m2Id}");
                }
                //start migrate child tables
                if ($addressEntity2->entity_id) {
                    //migrate customer address entity eav data
                    $this->_migrateCustomerAddressEntityEAV($addressEntity, $addressEntity2);
                }
            }
        }

        return true;
    }

    private function _migrateCustomerAddressEntityEAV($addressEntity, &$addressEntity2)
    {
        /**
         * Because some change in data structure of customer in Magento 2 from 0.74.0 - beta 12
         * We have to declare this to re-update values of it from child tables
         */
        $neededUpdateAttr2 = array(
            'country_id',
            'firstname',
            'lastname',
            'middlename',
            'street',
            'telephone',
            'city',
            'fax',
            'company',
            'country_id',
            'postcode',
            'prefix',
            'region',
            'region_id',
            'suffix',
            'vat_id',
            'vat_is_valid',
            'vat_request_date',
            'vat_request_id',
            'vat_request_success'
        );
        //flag to update parent entity
        $flagUpdateAddress2 = false;
        //get customer address entity type id in Magento2
        $entityTypeId = UBMigrate::getMage2EntityTypeId(UBMigrate::CUSTOMER_ADDRESS_TYPE_CODE);
        $eavTables = [
            'customer_address_entity_datetime',
            'customer_address_entity_decimal',
            'customer_address_entity_int',
            'customer_address_entity_text',
            'customer_address_entity_varchar'
        ];
        foreach ($eavTables as $table) {
            $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
            $className1 = "Mage1{$className}";
            $className2 = "Mage2{$className}";
            $models = $className1::model()->findAll("entity_id = $addressEntity->entity_id");
            if ($models) {
                foreach ($models as $model) {
                    //because customer attribute id in Magento 2 can difference from Magento 1
                    $attributeId2 = UBMigrate::getMage2AttributeId($model->attribute_id, $entityTypeId);
                    if ($attributeId2) {
                        $condition = "entity_id = {$addressEntity2->entity_id} AND attribute_id = {$attributeId2}";
                        $model2 = $className2::model()->find($condition);
                        if (!$model2) { //add new
                            $model2 = new $className2();
                            $model2->attribute_id = $attributeId2;
                            $model2->entity_id = $addressEntity2->entity_id;
                            if ($table == 'customer_address_entity_text' OR $table == 'customer_address_entity_varchar') {
                                $model2->value = (!empty(trim($model->value))) ? $model->value : "Not set";
                            } else {
                                $model2->value = $model->value;
                            }
                            if (!$model2->save()) {
                                $this->errors[] = get_class($model2) . ": " . UBMigrate::getStringErrors($model2->getErrors());
                            }
                        }
                        /**
                         * Because some change in data structure of customer in Magento 2 from 0.74.0 - beta 12
                         * We have to do this to re-update values of it in parent table
                         */
                        $attributeCode1 = UBMigrate::getMage1AttributeCode($model->attribute_id);
                        if (in_array($attributeCode1, $neededUpdateAttr2)) {
                            $addressEntity2->$attributeCode1 = $model->value;
                            $flagUpdateAddress2 = true;
                        }
                    }
                }
            }
        }
        //update value of some fields in main table has fill values from child tables
        if ($flagUpdateAddress2) {
            $addressEntity2->update();
        }

        return true;
    }

}
