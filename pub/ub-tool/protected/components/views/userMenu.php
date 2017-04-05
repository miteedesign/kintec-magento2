<ul class="nav nav-pills nav-stacked">
    <li class="active">
        <?php echo CHtml::link('<strong>- '.Yii::t('frontend', 'Pre - Migration Settings').' </strong>', UBMigrate::getSettingUrl(), array('class' => 'has-child')); ?>
        <ul class="nav nav-stacked">
            <?php foreach ($steps as $step): ?>
                <?php
                    $aClasses = ['setting-step'];
                    if (Yii::app()->controller->id == $step->code) {
                        $aClasses[] = 'active';
                    }
                    if ($step->status == UBMigrate::STATUS_FINISHED) {
                        $aClasses[] = 'finished';
                    }
                    else if ($step->status == UBMigrate::STATUS_SKIPPING) {
                        $aClasses[] = 'skipped';
                    }
                    $class = implode(' ', $aClasses);
                    $title = "{$step->sorder} - {$step->title}";
                    if ($step->status == UBMigrate::STATUS_FINISHED) {
                        $title = $title.' <span class="glyphicon glyphicon-ok-sign text-success"></span>';
                    } else if ($step->status == UBMigrate::STATUS_SKIPPING) {
                        $title .= '<span class="skipped"> ('.Yii::t('frontend', 'skipped').')</span>';
                    }
                ?>
                <li id="setting-step-<?php echo $step->sorder;?>" class="<?php echo $class?>">
                    <?php echo CHtml::link($title, UBMigrate::getSettingUrl($step->sorder)); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
    <li class="active">
        <?php echo CHtml::link('<strong>- '.Yii::t('frontend', 'Migrating Data').' (<span id="migrate-status-all-steps"><span class="value">'.UBMigrate::getPercentByStatus(UBMigrate::STATUS_FINISHED, [1]).'</span>%</span>)</strong>', UBMigrate::getStartUrl(), array('class' => 'has-child')); ?>
    </li>
</ul>
