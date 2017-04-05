<!--  Form Buttons-->
<div class="step-controls">
    <a title="<?php echo Yii::t('frontend', 'Click to go previous step'); ?>" href="<?php echo UBMigrate::getSettingUrl(($step->sorder-1)) ?>" class="btn btn-default"><span class="glyphicon glyphicon-backward"></span> <?php echo Yii::t('frontend', 'Back'); ?></a>
<!--    <a title="--><?php //echo Yii::t('frontend', 'Click to reset this step'); ?><!--" href="--><?php //echo UBMigrate::getResetUrl($step->sorder); ?><!--" class="btn btn-danger"><span class="glyphicon glyphicon-refresh"></span> --><?php //echo Yii::t('frontend', 'Reset'); ?><!--</a>-->
    <button title="<?php echo Yii::t('frontend', 'Click to save settings and go to next step'); ?>" type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-floppy-save"></span> <?php echo Yii::t('frontend', 'Save And Continue'); ?></button>
    <?php if (in_array($step->sorder, UBMigrate::$allowSkipSteps)): ?>
        <a title="<?php echo Yii::t('frontend', 'Skip migrate data in this step and continue with next step'); ?>" href="<?php echo UBMigrate::getSkipUrl($step->sorder); ?>" class="btn btn-danger"><span class="glyphicon glyphicon-bookmark"></span> <?php echo Yii::t('frontend', 'Skip And Continue'); ?></a>
    <?php endif;?>
    <a title="<?php echo Yii::t('frontend', 'Click to go next step'); ?>" href="<?php echo UBMigrate::getSettingUrl($step->sorder, true) ?>" class="btn btn-default"><span class="glyphicon glyphicon-forward"></span> <?php echo Yii::t('frontend', 'Next Step'); ?></a>
</div>
<!--// Form Buttons-->