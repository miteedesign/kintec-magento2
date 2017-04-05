<?php
    //get selected website ids
    $selectedWebsiteIds = UBMigrate::getSetting(2, 'website_ids');
    $strSelectedWebsiteIds = implode(',', $selectedWebsiteIds);

    $selectedStoreIds = UBMigrate::getSetting(2, 'store_ids');
    $strSelectedStoreIds = implode(',', $selectedStoreIds);

    $settingData = $step->getSettingData();
    $selectedCustomerGroupIds = (isset($settingData['customer_group_ids'])) ? $settingData['customer_group_ids'] : [];
?>
<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>
<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">
    <div id="step-content">
        <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>
        <ul class="list-group">
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Customer Groups'); ?>:
                </h3>
                <input type="checkbox" id="select-all" name="select_all" value="1" <?php echo (sizeof($selectedCustomerGroupIds) == sizeof($customerGroups)) ? "checked" : ''; ?> title="<?php echo Yii::t('frontend', 'Click here to select all.')?>" />
                <label for="select-all" title="<?php echo Yii::t('frontend', 'Click here to select all.')?>"><?php echo Yii::t('frontend', 'Select All');?></label>
                <?php if ($customerGroups): ?>
                    <ul class="list-group">
                        <?php foreach ($customerGroups as $customerGroup): ?>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    <?php if (in_array($customerGroup->customer_group_id, $selectedCustomerGroupIds) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                    <?php endif; ?>
                                    <?php
                                        $checked = (in_array($customerGroup->customer_group_id, $selectedCustomerGroupIds)) ? 'checked' : '';
                                    ?>
                                    <label for="customer_group_<?php echo $customerGroup->customer_group_id; ?>" class="checkbox-inline">
                                        <input id="customer_group_<?php echo $customerGroup->customer_group_id; ?>" name="customer_group_ids[]" type="checkbox" <?php echo ($checked) ?> value="<?php echo $customerGroup->customer_group_id; ?>" />
                                        <?php echo $customerGroup->customer_group_code . " (". UBMigrate::getTotalCustomersByGroup($customerGroup->customer_group_id, $strSelectedWebsiteIds, $strSelectedStoreIds) .")"; ?>
                                    </label>
                                </h4>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Total Customers: %s items', array('%s' => UBMigrate::getTotalCustomers($strSelectedWebsiteIds, $strSelectedStoreIds))); ?>
                </h3>
            </li>
        </ul>
        <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
    </div>
</form>
