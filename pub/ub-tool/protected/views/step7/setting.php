<?php
    $settingData = $step->getSettingData();
    $selectedSalesObjects = (isset($settingData['sales_objects'])) ? $settingData['sales_objects'] : [];
    $selectedSalesAggregatedTables = (isset($settingData['sales_aggregated_tables'])) ? $settingData['sales_aggregated_tables'] : [];
?>
<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings'); ?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>
<form role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">
    <div id="step-content" class="step7">
        <blockquote><p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p></blockquote>
        <ul class="list-group">
            <li class="list-group-item">
                <h3 class="list-group-item-heading">
                    <?php echo Yii::t('frontend', 'Sales Data Objects'); ?>:
                </h3>
                <?php if ($salesObjects): ?>
                    <ul class="list-group">
                        <?php foreach ($salesObjects as $object => $info): ?>
                            <li class="list-group-item">
                                <h4 class="list-group-item-heading">
                                    <?php if (in_array($object, $selectedSalesObjects) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                    <?php endif; ?>
                                    <?php
                                    $checked = (($object != 'sales_aggregated_data' AND !sizeof($selectedSalesObjects)) OR in_array($object, $selectedSalesObjects)) ? 'checked' : '';
                                    $disabled = ($object != 'sales_aggregated_data') ? 'readonly="readonly" onclick="event.preventDefault();"' : '';
                                    $hasChild = (isset($info['related']) AND $info['related']) ? 1 : 0;
                                    ?>
                                    <label for="sales_object_<?php echo ($hasChild) ? "" : $object; ?>" class="checkbox-inline">
                                        <input id="sales_object_<?php echo $object; ?>" name="sales_objects[]"
                                               type="checkbox" <?php echo($checked) ?> <?php echo $disabled; ?>
                                               value="<?php echo $object; ?>"/>
                                        <?php $labelEnfix = ($object != 'sales_aggregated_data') ? " (" . UBMigrate::getTotalSalesChildObject($object, $strSelectedStoreIds, null) . ")" : ''; ?>
                                        <span> <?php echo $info['label'] . $labelEnfix; ?> </span>
                                        <?php if ($hasChild): ?>
                                            <span class="head-tip">(<?php echo Yii::t('frontend', 'Click the title to show/hide child content.')?>)</span>
                                        <?php endif; ?>
                                    </label>
                                </h4>
                                <?php if ($hasChild) : ?>
                                    <ul class="list-group" style="display: none;">
                                        <?php foreach ($info['related'] as $relatedObject => $label) : ?>
                                            <li class="list-group-item">
                                                <h5 class="list-group-item-heading">
                                                    <?php
                                                    $checked = ($object == 'order' OR in_array($relatedObject, $selectedSalesAggregatedTables)) ? 'checked' : '';
                                                    $disabled = ($object == 'order') ? 'readonly="readonly" onclick="event.preventDefault();"' : '';
                                                    $keyName = ($object == 'order') ? 'related_order_objects' : 'sales_aggregated_tables';
                                                    ?>
                                                    <?php if (in_array($relatedObject, $selectedSalesAggregatedTables) AND $step->status == UBMigrate::STATUS_FINISHED): ?>
                                                        <span class="glyphicon glyphicon-ok-sign text-success"></span>
                                                    <?php endif; ?>
                                                    <input id="<?php echo $keyName ."_". $relatedObject; ?>" name="<?php echo $keyName; ?>[]"
                                                           type="checkbox" <?php echo($checked) ?> <?php echo $disabled; ?>
                                                           value="<?php echo $relatedObject; ?>"/>
                                                    <label for="<?php echo $keyName ."_". $relatedObject; ?>"> <?php echo " {$label}" . " (" . UBMigrate::getTotalSalesChildObject($relatedObject, $strSelectedStoreIds, null) . ")"; ?> </label>
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
