<?php $this->pageTitle = 'Migrate Data - ' . Yii::app()->name; ?>
<h2 class="page-header"><?php echo Yii::t('frontend', 'Start Migrate Your Data');?></h2>
<table class="table table-hover migrate-steps">
    <thead>
        <tr>
            <th style="text-align: left;"><?php echo Yii::t('frontend', 'Task');?></th>
            <th style="text-align: center;"><?php echo Yii::t('frontend', 'Status');?></th>
            <th style="text-align: right;"><?php echo Yii::t('frontend', 'Action');?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($steps as $key => $step): ?>
            <?php
                $trClasses = ['step'];
                if ($step->status == UBMigrate::STATUS_FINISHED) {
                    //$trClasses[] = 'success';
                    $trClasses[] = '';
                } else if ($step->status == UBMigrate::STATUS_SKIPPING) {
                    $trClasses[] = 'skipped';
                }
                $trClasses = implode(' ', $trClasses);
            ?>
        <tr class="<?php echo $trClasses; ?>">
            <?php
                $stepTitle = ($step->sorder > 1) ? Yii::t('frontend', "Migrate %s", array('%s' => $step->title)) : $step->title ." ". Yii::t('frontend', 'Settings');
                $stepTitle = (++$key) . " - " . $stepTitle;
            ?>
            <td><?php echo  $stepTitle; ?></td>
            <td id="step-status-<?php echo $step->sorder; ?>" style="text-align: center;"><?php echo $step->getStepStatusText();?></td>
            <td style="text-align: right;">
                <?php if ($step->status != UBMigrate::STATUS_PENDING AND $step->sorder != 1): ?>
                    <?php $actionClass = ($step->status == UBMigrate::STATUS_SKIPPING) ? "run disabled" : 'run'; ?>
                    <a id="run-step-<?php echo $step->sorder; ?>" class="btn btn-primary btn-xs <?php echo $actionClass; ?>" data-step-index="<?php echo $step->sorder; ?>" title="<?php echo Yii::t('frontend', 'Click to run migrate data in this step'); ?>" href="<?php echo UBMigrate::getRunUrl($step->sorder); ?>">
                        <span class="glyphicon glyphicon-transfer"></span>
                        <?php echo ($step->status == UBMigrate::STATUS_FINISHED) ? Yii::t('frontend', 'Re-run') : Yii::t('frontend', 'Run'); ?>
                    </a>
                    <a id="reset-step-<?php echo $step->sorder; ?>" class="btn btn-danger btn-xs reset" title="<?php echo Yii::t('frontend', 'Click to reset this step'); ?>" data-step-index="<?php echo $step->sorder; ?>" href="<?php echo UBMigrate::getResetUrl($step->sorder); ?>" ><span class="glyphicon glyphicon-refresh"></span> <?php echo Yii::t('frontend', 'Reset'); ?></a>
                <?php endif; ?>
                <a id="setting-step-<?php echo $step->sorder; ?>" title="<?php echo Yii::t('frontend', 'Click to re-setting this step'); ?>" href="<?php echo UBMigrate::getSettingUrl($step->sorder); ?>" class="btn btn-default btn-xs setting"><span class="glyphicon glyphicon-wrench"></span> <?php echo ($step->status == UBMigrate::STATUS_PENDING) ? Yii::t('frontend', 'Settings') : Yii::t('frontend', 'Edit Settings'); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" style="text-align: center;">
                <!-- allow only run all steps when all step was setting/migrate processed. -->
                <?php $percentFinished = UBMigrate::getPercentByStatus(UBMigrate::STATUS_FINISHED, [1]); ?>
                <?php $percentPending = UBMigrate::getPercentByStatus([UBMigrate::STATUS_PENDING]); ?>

                <!-- process bar -->
                <?php if ( $percentPending == 0 ): ?>
                    <div id="all-steps-process" class="progress" style="display: <?php echo ($percentFinished) ? 'block' : 'none'?>;">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                            <span class="value">0</span>% <?php echo Yii::t('frontend', 'Completed'); ?>
                        </div>
                        <script type="text/javascript">
                            var percentFinished = <?php echo $percentFinished; ?>;
                            $(document).ready(function(){
                                //update process bar info
                                $('#all-steps-process').find('.progress-bar').css({"width" : percentFinished + '%'}).attr('aria-valuenow', percentFinished).html('<span class="value">'+percentFinished + '</span>% Completed');
                            });
                        </script>
                    </div>
                <?php endif;?>
                <!--// process bar -->

                <!-- run all steps button-->
                <?php $label = ($percentFinished == 100) ? Yii::t('frontend', 'Re-run') : Yii::t('frontend', 'Run'); ?>
                <?php $runClass = ($percentPending == 0) ? "btn btn-primary btn-md run-all" : "btn btn-primary btn-lg run-all disabled"; ?>
                <a id="run-all-steps" onclick="$.runAllSteps();" title="<?php echo Yii::t('frontend', 'Click to %s all steps migrate data', array('%s'=>$label)); ?>" href="#" class="<?php echo $runClass; ?>"><span class="glyphicon glyphicon-transfer"></span> <?php echo Yii::t('frontend', '%s migrate data all steps', array('%s'=>$label)); ?></a>
                <!--// run all steps button-->

                <!-- reset all steps button -->
                <?php $resetClass = ($percentPending == 0) ? "btn btn-md btn-danger" : 'btn btn-lg btn-danger disabled'; ?>
                <a href="javascript:void(0);" onclick="$.resetAllSteps();" title="<?php echo Yii::t('frontend', 'Click to reset all steps.'); ?>" class="<?php echo $resetClass; ?>">
                    <span class="glyphicon glyphicon-refresh"></span> <?php echo Yii::t('frontend', 'Reset all steps'); ?>
                </a>
                <!--// reset all steps button -->
            </td>
        </tr>
    </tbody>
</table>

<!--Migrate data by commands in CLI-->
<div class="cold-md-12">
    <fieldset class="migrate-log">
        <legend class="legend">
            <a href="javascript:void(0);" data-target="#migrate-cli-commands" data-toggle="collapse">
                <span class="text-uppercase">(Optional)</span>
                <?php echo Yii::t('frontend', 'Use Command Line Interface (CLI) to proceed the Migration step')?>
            </a>
        </legend>
        <div id="migrate-cli-commands" class="row collapse">
            <div id="cli-commands" class="col-md-12">
                <blockquote>
                    <p class="tip">
                    <strong><span class="text-uppercase"><mark>NOTE:</mark></span></strong> Make sure you complete all Pre-migration Setting (8) steps first. Then open your terminal, navigate to your Magento 2 folder and run one of following commands. Once done, please follow the step 6 guideline - REQUIRED STEPS TO COMPLETE MIGRATION PROCESS in our Readme.txt file.
                    </p>
                </blockquote>
                <ul>
                    <li>
                        <label>Migrates Data All Steps:</label><br/>
                        <code>php bin/ubdatamigration run</code>
                    </li>
                    <li>
                        <label>Migrates Single Step:</label><br/>
                        <span>Eg: The command to proceed migration in step #2 - Migrate Websites, Stores:</span><br/>
                        <code>php bin/ubdatamigration run --step=2</code>
                    </li>
                    <li>
                        <label>Migrates Data Single Step with custom number of records per each batch runtime:</label><br/>
                        <span>Eg. The command to proceed the migration in step #5 with a custom 200 records</span>:<br/>
                        <code>php bin/ubdatamigration run --step=5 --limit=200</code>
                    </li>
                    <li>
                        <label>Resets Migrated Data All Steps:</label><br/>
                        <code>php bin/ubdatamigration reset</code>
                    </li>
                    <li>
                        <label>Resets Migrated Data Single Step:</label><br/>
                        <span>Eg. The command to reset migration in only step #2 - Migrate Websites, Stores:</span><br/>
                        <code>php bin/ubdatamigration reset --step=2</code><br/>
                    </li>
                </ul>
            </div>
        </div>
    </fieldset>
</div>
<!--// Migrate data by commands in CLI-->

<!--Migrate log-->
<div class="cold-md-12">
    <fieldset class="migrate-log">
        <legend class="legend">
            <a id="migrate-log-action" href="javascript:void(0);" data-target="#migrate-log" data-toggle="collapse"><?php echo Yii::t('frontend', 'Migration Log')?></a>
        </legend>
        <input type="hidden" id="log-url" name="log-url" value="<?php echo UBMigrate::getLogUrl(); ?>"/>
        <div id="migrate-log" class="row collapse">
            <div id="migrate-log-content" class="log-content col-md-12"></div>
        </div>
    </fieldset>
</div>
<!--//Migrate log-->

<!-- Report list -->
<div class="cold-md-12" style="display: <?php echo (isset($percentFinished) AND $percentFinished) ? 'block' : 'none'?>;">
    <fieldset class="migrate-report">
        <legend class="legend">
            <a id="migrate-report-action" href="javascript:void(0);" data-target="#migrate-report" data-toggle="collapse"><?php echo Yii::t('frontend', 'Migration Quick Report')?></a>
        </legend>
        <div id="migrate-report" class="row collapse">
            <table id="report-content" class="table table-hover report-content">
                <thead>
                <tr>
                    <th><?php echo Yii::t('frontend', 'STT');?></th>
                    <th><?php echo Yii::t('frontend', 'Entity Name');?></th>
                    <!--<th><?php /*echo Yii::t('frontend', 'Total in Magento 1 (items)');*/?></th>-->
                    <th><?php echo Yii::t('frontend', 'Total Migrated (items)');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                    $reportItems = [
                        'core_website' => ['label' => Yii::t('frontend', 'Websites'), 'map_table' => 'ub_migrate_map_step_2'],
                        'core_store_group' => ['label' => Yii::t('frontend', 'Stores'), 'map_table' => 'ub_migrate_map_step_2'],
                        'core_store' => ['label' => Yii::t('frontend', 'Store Views'), 'map_table' => 'ub_migrate_map_step_2'],
                        'eav_attribute_set' => ['label' => Yii::t('frontend', 'Product Attribute Sets'), 'map_table' => 'ub_migrate_map_step_3'],
                        'eav_attribute_group' => ['label' => Yii::t('frontend', 'Product Attribute Groups'), 'map_table' => 'ub_migrate_map_step_3'],
                        'eav_attribute' => ['label' => Yii::t('frontend', 'Product Attributes'), 'map_table' => 'ub_migrate_map_step_3_attribute'],
                        'catalog_category_entity' => ['label' => Yii::t('frontend', 'Catalog Categories'), 'map_table' => 'ub_migrate_map_step_4'],
                        'catalog_product_entity' => ['label' => Yii::t('frontend', 'Catalog Products'), 'map_table' => 'ub_migrate_map_step_5'],
                        'customer_group' => ['label' => Yii::t('frontend', 'Customer Groups'), 'map_table' => 'ub_migrate_map_step_6'],
                        'customer_entity' => ['label' => Yii::t('frontend', 'Customers'), 'map_table' => 'ub_migrate_map_step_6'],
                        'salesrule' => ['label' => Yii::t('frontend', 'Cart Price Rules'), 'map_table' => 'ub_migrate_map_step_7'],
                        'sales_order_status' => ['label' => Yii::t('frontend', 'Sales Order Statuses'), 'map_table' => 'ub_migrate_map_step_7'],
                        'sales_flat_order' => ['label' => Yii::t('frontend', 'Sales Orders'), 'map_table' => 'ub_migrate_map_step_7_order'],
                        'catalogrule' => ['label' => Yii::t('frontend', 'Catalog Price Rule'), 'map_table' => 'ub_migrate_map_step_8'],
                        'review' => ['label' => Yii::t('frontend', 'Reviews'), 'map_table' => 'ub_migrate_map_step_8_review'],
                        'newsletter_subscriber' => ['label' => Yii::t('frontend', 'Newsletter Subscriber'), 'map_table' => 'ub_migrate_map_step_8_subscriber'],
                    ];
                    UBMigrate::makeMigrateReport($reportItems);
                ?>
                <?php $i=1; foreach ($reportItems as $entityName => $reportItem):?>
                    <tr>
                        <td><?php echo ($i)?></td>
                        <td><?php echo $reportItem['label']; ?></td>
                        <!--<td><?php /*echo $reportItem['m1_total'];*/?></td>-->
                        <td><?php echo $reportItem['migrated_total'];?></td>
                    </tr>
                <?php $i++; endforeach;?>
                </tbody>
            </table>
        </div>
    </fieldset>
</div>
<!--// Report list -->
