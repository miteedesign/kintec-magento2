<?php $this->pageTitle = $step->title . ' - ' . Yii::app()->name; ?>

<h2 class="page-header"><?php echo Yii::t('frontend', 'Migrate Settings');?> > <?php echo Yii::t('frontend', $step->title); ?> </h2>

<form id="frm-settings" data-toggle="validator" role="form" method="post" action="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>">

    <div id="step-content">
        <blockquote> <p class="tip"> <?php echo Yii::t('frontend', $step->descriptions); ?> </p> </blockquote>

        <div style="width: 45%;display: inline-block;">
            <h3><?php echo Yii::t('frontend', 'Magento 1.x');?></h3>
            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="mg1-version"><?php echo Yii::t('frontend', 'Version');?>:</label>
                            <select class="form-control" id="mg1-version" name="mg1_version">
                                <?php $options = UBMigrate::getMG1VersionOptions()?>
                                <?php foreach ($options as $value => $label):?>
                                    <option value="<?php echo $value; ?>" <?php echo (isset($settings->mg1_version) && $settings->mg1_version == $value) ? 'selected="selected"' : ''?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mg1-host" class="control-label"><?php echo Yii::t('frontend', 'Database Server Host');?></label>
                            <input type="text" class="form-control" id="mg1-host" name="mg1_host" value="<?php echo isset($settings->mg1_host) ? $settings->mg1_host : '' ?>" placeholder="localhost" required/>
                        </div>
                        <div class="form-group">
                            <label for="mg1-db-port" class="control-label"><?php echo Yii::t('frontend', 'Database Server Port');?></label>
                            <input type="text" class="form-control" id="mg1-db-port" name="mg1_db_port" value="<?php echo isset($settings->mg1_db_port) ? $settings->mg1_db_port : '' ?>" placeholder="Leave blank to use default port: <?php echo ini_get("mysqli.default_port"); ?>"/>
                        </div>
                        <div class="form-group">
                            <label for="mg1-db-user" class="control-label"><?php echo Yii::t('frontend', 'Database Server Username');?></label>
                            <input type="text" class="form-control" id="mg1-db-user" name="mg1_db_user" value="<?php echo isset($settings->mg1_db_user) ? $settings->mg1_db_user : '' ?>" placeholder="username" required />
                        </div>
                        <div class="form-group">
                            <label for="mg1-db-pass" class="control-label"><?php echo Yii::t('frontend', 'Database Server Password');?></label>
                            <input type="password" class="form-control" id="mg1-db-pass" name="mg1_db_pass" value="<?php echo isset($settings->mg1_db_pass) ? $settings->mg1_db_pass : '' ?>" placeholder="optional" />
                        </div>
                        <div class="form-group">
                            <label for="mg1-db-name" class="control-label"><?php echo Yii::t('frontend', 'Database Name');?></label>
                            <input type="text" class="form-control" id="mg1-db-name" name="mg1_db_name" value="<?php echo isset($settings->mg1_db_name) ? $settings->mg1_db_name : '' ?>" placeholder="database name" required />
                        </div>
                        <div class="form-group">
                            <label for="mg1-db-prefix" class="control-label"><?php echo Yii::t('frontend', 'Table Prefix');?></label>
                            <input type="text" class="form-control" id="mg1-db-prefix" name="mg1_db_prefix" value="<?php echo isset($settings->mg1_db_prefix) ? $settings->mg1_db_prefix : '' ?>" placeholder="optional" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="width: 45%;display: inline-block;float: right;">
            <h3><?php echo Yii::t('frontend', 'Magento 2.x');?></h3>
            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="mg2-host" class="control-label"><?php echo Yii::t('frontend', 'Database Server Host');?></label>
                            <input type="text" class="form-control" disabled="disabled" id="mg2-host" name="mg2_host" value="<?php echo isset($settings->mg2_host) ? $settings->mg2_host : '' ?>" placeholder="localhost" required/>
                        </div>
                        <div class="form-group">
                            <label for="mg2-db-port" class="control-label"><?php echo Yii::t('frontend', 'Database Server Port');?></label>
                            <input type="text" class="form-control" disabled="disabled" id="mg2-db-port" name="mg2_db_port" value="<?php echo isset($settings->mg2_db_port) ? $settings->mg2_db_port : '' ?>" />
                        </div>
                        <div class="form-group">
                            <label for="mg2-db-user" class="control-label"><?php echo Yii::t('frontend', 'Database Server Username');?></label>
                            <input type="text" class="form-control" disabled="disabled" id="mg2-db-user" name="mg2_db_user" value="<?php echo isset($settings->mg2_db_user) ? $settings->mg2_db_user : '' ?>" placeholder="username" required />
                        </div>
                        <div class="form-group">
                            <label for="mg2-db-pass" class="control-label"><?php echo Yii::t('frontend', 'Database Server Password');?></label>
                            <input type="password" class="form-control" disabled="disabled" id="mg2-db-pass" name="mg2_db_pass" value="<?php echo isset($settings->mg2_db_pass) ? $settings->mg2_db_pass : '' ?>" placeholder="optional" />
                        </div>
                        <div class="form-group">
                            <label for="mg2-db-name" class="control-label"><?php echo Yii::t('frontend', 'Database Name');?></label>
                            <input type="text" class="form-control" disabled="disabled" id="mg2-db-name" name="mg2_db_name" value="<?php echo isset($settings->mg2_db_name) ? $settings->mg2_db_name : '' ?>" placeholder="database name" required />
                        </div>
                        <div class="form-group">
                            <label for="mg2-db-prefix" class="control-label"><?php echo Yii::t('frontend', 'Table Prefix');?></label>
                            <input type="text" class="form-control" disabled="disabled" id="mg2-db-prefix" name="mg2_db_prefix" value="<?php echo isset($settings->mg2_db_prefix) ? $settings->mg2_db_prefix : '' ?>" placeholder="optional" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Buttons-->
        <div class="step-controls">
            <?php if ($step->status == UBMigrate::STATUS_PENDING): ?>
                <button title="<?php echo Yii::t('frontend', 'Click to save settings and go to next step'); ?>" type="submit" id="step-<?php echo $step->sorder; ?>" class="btn btn-primary need-validate-form"><span class="glyphicon glyphicon-save"></span> <?php echo Yii::t('frontend', 'Save And Continue'); ?></button>
            <?php else: ?>
                <button title="<?php echo Yii::t('frontend', 'Click to update settings and go to next step'); ?>" type="submit" id="step-<?php echo $step->sorder; ?>" class="btn btn-danger need-validate-form"><span class="glyphicon glyphicon-save"></span> <?php echo Yii::t('frontend', 'Update And Continue'); ?></button>
                <a title="<?php echo Yii::t('frontend', 'Click to go next step'); ?>" href="<?php echo UBMigrate::getSettingUrl($step->sorder, true); ?>" class="btn btn-primary"><span class="glyphicon glyphicon-forward"></span> <?php echo Yii::t('frontend', 'Next Step'); ?></a>
            <?php endif; ?>
        </div>
        <!--// Form Buttons-->
    </div>
</form>
