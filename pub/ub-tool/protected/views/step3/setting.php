<?php
$settingData = $step->getSettingData();
$selectedAttrSetIds = (isset($settingData['attribute_set_ids'])) ? $settingData['attribute_set_ids']  : [];
$selectedAttrGroupIds = (isset($settingData['attribute_group_ids'])) ? $settingData['attribute_group_ids']  : [];
$selectedAttrIds = (isset($settingData['attribute_ids'])) ? $settingData['attribute_ids']  : [];
?>

<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>

<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>

<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">

    <div id="step-content">
        <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>
        <div class="panel-group" id="attribute-settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" title="<?php echo Yii::t('frontend', 'Click to show/hide Attribute sets, Attribute Groups')?>">
                        <a data-toggle="collapse" data-parent="#attribute-settings" href="#attribute-sets">
                            <?php echo Yii::t('frontend', 'Product Attribute Sets > Attribute Groups'); ?> (<span class="sub-head-title"><?php echo sizeof($attributeSets); ?> Attribute Sets </span>)
                        </a>
                        <span class="head-tip">(<?php echo Yii::t('frontend', 'Click the title to show/hide child content.')?>)</span>
                    </h3>
                </div>
                <div id="attribute-sets" class="panel-collapse collapse">
                    <div class="panel-body">
                        <?php if ($attributeSets): ?>
                            <ul class="list-group">
                                <?php foreach ($attributeSets as $key => $attributeSet): ?>
                                    <li class="list-group-item attribute-set">
                                        <h4 class="list-group-item-heading">
                                            <?php
                                            //check has selected to migrate
                                            $checked = in_array($attributeSet->attribute_set_id, $selectedAttrSetIds);
                                            $checked = true; //we will always select
                                            //check has migrated
                                            $m2Id = UBMigrate::getM2EntityId(3, $attributeSet->tableName(), $attributeSet->attribute_set_id);
                                            ?>
                                            <?php if ($m2Id): ?>
                                                <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                            <?php endif; ?>
                                            <label class="checkbox-inline" for="attribute-set-<?php echo $attributeSet->attribute_set_id; ?>">
                                                <input type="checkbox" id="attribute-set-<?php echo $attributeSet->attribute_set_id; ?>" readonly="readonly" onclick="event.preventDefault();" <?php echo ($checked) ? 'checked="checked"' : ''; ?> name="attribute_set_ids[]" value="<?php echo $attributeSet->attribute_set_id; ?>" />
                                                <span><?php echo ($key+1) .' - '. $attributeSet->attribute_set_name; ?></span>
                                            </label>
                                        </h4>
                                        <?php
                                        //get all attribute groups of current attribute set
                                        $condition = "attribute_set_id = {$attributeSet->attribute_set_id}";
                                        $attributeGroups = Mage1AttributeGroup::model()->findAll($condition);
                                        ?>
                                        <?php if ($attributeGroups): ?>
                                            <ul class="list-group">
                                                <?php foreach ($attributeGroups as $attributeGroup): ?>
                                                    <li class="list-group-item attribute-group">
                                                        <h5 class="list-group-item-heading">
                                                            <?php
                                                            //check has selected to migrate
                                                            $checked = in_array($attributeGroup->attribute_group_id, $selectedAttrGroupIds);
                                                            $checked = true; //we will always select
                                                            //check has migrated
                                                            $m2Id = UBMigrate::getM2EntityId(3, $attributeGroup->tableName(), $attributeGroup->attribute_group_id);
                                                            ?>
                                                            <?php if ($m2Id): ?>
                                                                <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                                            <?php endif; ?>
                                                            <label class="checkbox-inline" for="attribute-group-<?php echo $attributeGroup->attribute_group_id; ?>">
                                                                <input type="checkbox" id="attribute-group-<?php echo $attributeGroup->attribute_group_id; ?>" readonly="readonly" onclick="event.preventDefault();" <?php echo ($checked) ? "checked" : ''; ?> name="attribute_group_ids[]" class="attribute-group-<?php echo $attributeSet->attribute_set_id; ?>" value="<?php echo $attributeGroup->attribute_group_id?>" />
                                                                <?php echo $attributeGroup->attribute_group_name; ?>
                                                            </label>
                                                        </h5>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif;?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" title="<?php echo Yii::t('frontend', 'Click to show/hide Attribute sets, Attribute Groups')?>">
                        <a data-toggle="collapse" data-parent="#attribute-settings" href="#attributes">
                            <?php echo Yii::t('frontend', 'Product Attributes'); ?> (<span class="sub-head-title"><?php echo sizeof($attributes);?> attributes. There are <?php echo UBMigrate::getTotalVisibleProductsAttr(); ?> attributes visible in back-end.</span>)
                        </a>
                        <span class="head-tip">(<?php echo Yii::t('frontend', 'Click the title to show/hide child content.')?>)</span>
                    </h3>
                </div>
                <div id="attributes" class="panel-collapse collapse">
                    <div class="panel-body">
                        <?php if ($attributes): ?>
                            <ul class="list-group">
                                <?php foreach ($attributes as $key => $attribute): ?>
                                    <li class="list-group-item attributes">
                                        <h4 class="list-group-item-heading">
                                            <?php
                                            //check has selected to migrate
                                            $checked = in_array($attribute->attribute_id, $selectedAttrIds);
                                            //we will always select if has not custom settings yet
                                            $checked = ($step->status == UBMigrate::STATUS_PENDING || $step->status == UBMigrate::STATUS_SKIPPING) ? 'checked' : ($checked ? 'checked' : '');
                                            $disable = (!$attribute->is_user_defined) ? 'readonly="readonly" onclick="event.preventDefault();"' : '';
                                            //check has migrated
                                            $m2Id = UBMigrate::getM2EntityId(3, $attribute->tableName(), $attribute->attribute_id);
                                            ?>
                                            <?php if ($m2Id): ?>
                                                <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                            <?php endif; ?>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" id="attribute-<?php echo $attribute->attribute_id; ?>" <?php echo $checked; ?> <?php echo $disable; ?> name="attribute_ids[]" value="<?php echo $attribute->attribute_id; ?>" />
                                                <?php echo ($key+1) .' - '. $attribute->frontend_label . ' ('.$attribute->attribute_code.')'; ?>

                                                <?php if (!$attribute->is_user_defined) :?>
                                                    <span class="head-tip">(<?php echo Yii::t('frontend', 'You can\'t un-check the system attribute.')?>)</span>
                                                <?php endif; ?>
                                            </label>
                                        </h4>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
    </div>

</form>
