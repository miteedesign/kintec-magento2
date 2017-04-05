<?php
    $settingData = $step->getSettingData();
    $selectedWebsiteIds = (isset($settingData['website_ids'])) ? $settingData['website_ids']  : [];
    $selectedStoreGroupIds = (isset($settingData['store_group_ids'])) ? $settingData['store_group_ids'] : [];
    $selectedStoreIds = (isset($settingData['store_ids'])) ? $settingData['store_ids'] : [];

    $totalWebsite = sizeof($websites);
    $totalStoreGroup = Mage1StoreGroup::model()->count("website_id > 0");
    $totalStores = Mage1Store::model()->count("website_id > 0");
?>

<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>

<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>

<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">

<div id="step-content">
    <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>
    <?php
        $checkedAll = ((sizeof($selectedWebsiteIds) -1) == $totalWebsite
        AND (sizeof($selectedStoreGroupIds) -1) == $totalStoreGroup
        AND (sizeof($selectedStoreIds) -1) == $totalStores ) ? 'checked' : '';
    ?>
    <input type="checkbox" id="select-all" name="select_all" <?php echo $checkedAll; ?> value="1" title="<?php echo Yii::t('frontend', 'Click here to select all websites and Stores.')?>" />
    <label for="select-all" title="<?php echo Yii::t('frontend', 'Click here to select all websites and stores.')?>"><?php echo Yii::t('frontend', 'Select All');?></label>
    <ul>
        <li><?php echo Yii::t('frontend', "Total Websites: %s", array('%s' => sizeof($websites))); ?></li>
        <li><?php echo Yii::t('frontend', "Total Stores: %s", array('%s' => $totalStoreGroup)); ?></li>
        <li><?php echo Yii::t('frontend', "Total Store Views: %s", array('%s' => $totalStores)); ?></li>
    </ul>
    <?php foreach ($websites as $website): ?>
    <ul class="list-group">
        <li class="list-group-item website">
            <h4 class="list-group-item-heading">
                <?php
                    //check has selected to migrate
                    $checked = in_array($website->website_id, $selectedWebsiteIds);
                    //check has migrated
                    $m2Id = UBMigrate::getM2EntityId(2, $website->tableName(), $website->website_id);
                ?>
                <?php if ($m2Id): ?>
                    <span class="glyphicon glyphicon-ok-sign text-success"></span>
                <?php endif; ?>
                <label class="checkbox-inline">
                    <input type="checkbox" id="website-<?php echo $website->website_id; ?>" <?php echo ($checked) ? "checked" : ''; ?> name="website_ids[]" value="<?php echo $website->website_id?>" />
                    <span><?php echo $website->name; ?></span>
                </label>
            </h4>
            <?php
                //get list store groups of current website
                $storeGroups = Mage1StoreGroup::model()->findAll("website_id = {$website->website_id}");
            ?>
            <?php if ($storeGroups): ?>
            <ul class="list-group">
                <?php foreach ($storeGroups as $storeGroup): ?>
                    <li class="list-group-item store">
                        <h5 class="list-group-item-heading">
                            <?php
                                //check has selected to migrate
                                $checked = in_array($storeGroup->group_id, $selectedStoreGroupIds);
                                //check has migrated
                                $m2Id = UBMigrate::getM2EntityId(2, $storeGroup->tableName(), $storeGroup->group_id);
                            ?>
                            <?php if ($m2Id): ?>
                                <span class="glyphicon glyphicon-ok-sign text-success"></span>
                            <?php endif; ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" id="store-group-<?php echo $storeGroup->group_id; ?>" <?php echo ($checked) ? "checked" : ''; ?> name="store_group_ids[]" class="store-group-<?php echo $website->website_id; ?>" value="<?php echo $storeGroup->group_id?>" />
                                <span><?php echo $storeGroup->name; ?></span>
                            </label>
                        </h5>
                        <?php
                            //get list stores of current store group
                            $stores = Mage1Store::model()->findAll("website_id = {$website->website_id} AND group_id = {$storeGroup->group_id}");
                        ?>
                        <?php if ($stores): ?>
                            <ul class="list-group">
                                <?php foreach ($stores as $store): ?>
                                    <li class="list-group-item store-view">
                                        <?php
                                            $checked = in_array($store->store_id, $selectedStoreIds);
                                            //check has migrated
                                            $m2Id = UBMigrate::getM2EntityId(2, $store->tableName(), $store->store_id);
                                        ?>
                                        <?php if ($m2Id): ?>
                                            <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                        <?php endif; ?>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="store-<?php echo $store->store_id; ?>" <?php echo ($checked) ? "checked" : ''; ?> name="store_ids[]" class="store-<?php echo $storeGroup->group_id; ?>" value="<?php echo $store->store_id?>" />
                                            <span><?php echo $store->name; ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
    </ul>
    <?php endforeach; ?>

    <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
</div>

</form>