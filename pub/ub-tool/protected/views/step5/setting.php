<?php
    //get selected store ids
    $selectedStoreIds = UBMigrate::getSetting(2, 'store_ids');
    $strSelectedStoreIds = implode(',', $selectedStoreIds);

    $settingData = $step->getSettingData();
    $selectedProductTypes = (isset($settingData['product_types'])) ? $settingData['product_types']  : [];
?>
<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>
<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">
    <div id="step-content">
        <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>
        <ul class="list-group">
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Product Types'); ?>:
                </h3>
                <input type="checkbox" id="select-all" name="select_all" <?php echo (sizeof($selectedProductTypes) == 6) ? "checked" : ''; ?> value="1" title="<?php echo Yii::t('frontend', 'Click here to select all product types.')?>" />
                <label for="select-all" title="<?php echo Yii::t('frontend', 'Click here to select all product types.')?>"><?php echo Yii::t('frontend', 'Select All');?></label>
                <?php if ($productTypes): ?>
                    <ul class="list-group">
                        <?php foreach ($productTypes as $productType): ?>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    <?php if (in_array($productType, $selectedProductTypes) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                    <?php endif; ?>
                                    <?php
                                    //We always migrate the simple products
                                    $disabled = ($productType == 'simple') ? 'disabled' : '';
                                    $checked = ($productType == 'simple' || in_array($productType, $selectedProductTypes)) ? 'checked' : '';
                                    ?>
                                    <?php if ($productType == 'simple'): ?>
                                        <!-- we always migrated the simple products-->
                                        <input type="hidden" name="product_types[]" value="simple" />
                                    <?php endif; ?>
                                    <label for="product_type_<?php echo $productType; ?>" class="checkbox-inline">
                                        <input id="product_type_<?php echo $productType; ?>" name="product_types[]" type="checkbox" <?php echo ($checked) ?> <?php echo $disabled; ?> value="<?php echo $productType; ?>" />
                                        <?php echo Yii::t('frontend', '%s Products', array('%s'=> ucfirst($productType))) . " (". UBMigrate::getTotalProductsByType($productType, $strSelectedStoreIds) .")"; ?>
                                    </label>
                                </h4>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Total Products: %s items', array('%s' => UBMigrate::getTotalProducts($strSelectedStoreIds, $productTypes))); ?>
                </h3>
            </li>
        </ul>
        <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
    </div>
</form>
