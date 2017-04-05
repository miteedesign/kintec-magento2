<?php
    $settingData = $step->getSettingData();
    $selectedObjects = (isset($settingData['objects'])) ? $settingData['objects'] : [];
    $selectedChildObjects = (isset($settingData['child_objects'])) ? $settingData['child_objects'] : [];
?>
<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings'); ?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>
<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">
    <div id="step-content" class="step7">
        <blockquote><p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p></blockquote>
        <ul class="list-group">
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Select Data Objects'); ?>:
                </h3>
                <input type="checkbox" id="select-all" name="select_all" value="1" <?php echo (sizeof($selectedObjects) == sizeof($objects)) ? "checked" : ''; ?> title="<?php echo Yii::t('frontend', 'Click here to select all.')?>" />
                <label for="select-all" title="<?php echo Yii::t('frontend', 'Click here to select all.')?>"><?php echo Yii::t('frontend', 'Select All');?></label>
                <?php if ($objects): ?>
                    <ul class="list-group">
                        <?php foreach ($objects as $object => $info): ?>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    <?php if (in_array($object, $selectedObjects) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                    <?php endif; ?>
                                    <?php
                                    //$checked = (!sizeof($selectedObjects) OR in_array($object, $selectedObjects)) ? 'checked' : '';
                                    $checked = (in_array($object, $selectedObjects)) ? 'checked' : '';
                                    $disabled = '';
                                    $hasChild = (isset($info['related']) AND $info['related']) ? 1 : 0;
                                    ?>
                                    <label for="object_<?php echo ($hasChild) ? "" : $object; ?>" class="checkbox-inline">
                                        <input id="object_<?php echo $object; ?>" name="objects[]"
                                               type="checkbox" <?php echo($checked) ?> <?php echo $disabled; ?>
                                               value="<?php echo $object; ?>"/>
                                        <?php
                                        if (in_array($object, array('tax_data', 'increment_ids', 'email_template_newsletter'))) {
                                            $total = null;
                                        } else {
                                            $total = UBMigrate::getTotalItemOfObject($object);
                                        }
                                        $endfix = (!is_null($total)) ?  " (". $total .")" : '';
                                        ?>
                                        <span> <?php echo $info['label'] . $endfix; ?> </span>
                                        <?php if ($hasChild): ?>
                                            <span class="head-tip">(<?php echo Yii::t('frontend', 'Click the title to show/hide child content.')?>)</span>
                                        <?php endif; ?>
                                    </label>
                                </h4>
                                <?php if ($hasChild) : ?>
                                    <ul class="list-group" style="display: none;">
                                        <?php foreach ($info['related'] as $childObject => $label) : ?>
                                            <li class="list-group-item">
                                                <h5 class="list-group-item-heading">
                                                    <?php
                                                    $checked = (in_array($childObject, $selectedChildObjects)) ? 'checked' : '';
                                                    $keyName = 'child_objects';
                                                    ?>
                                                    <?php if (in_array($childObject, $selectedChildObjects) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                                    <?php endif; ?>
                                                    <input id="<?php echo $keyName ."_". $childObject; ?>" name="<?php echo $keyName; ?>[]"
                                                           type="checkbox" <?php echo($checked) ?> <?php echo $disabled; ?>
                                                           value="<?php echo $childObject; ?>"/>
                                                    <label for="<?php echo $keyName ."_". $childObject; ?>"> <?php echo " {$label}" . " (" . UBMigrate::getTotalItemOfObject($childObject) . ")"; ?> </label>
                                                </h5>
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
        <?php $this->renderPartial('/base/_buttons', array('step' => $step)); ?>
    </div>
</form>
