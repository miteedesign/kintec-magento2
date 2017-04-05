<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/css/images/favicon.ico">
    <!-- bootstrap -->
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap/css/bootstrap-theme.min.css">
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.blockui.min.js"></script>
    <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/script.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
</head>
<body>
<div id="page" class="container">
    <div class="row">
<!--        <div id="header">-->
            <!--<div id="logo"><?php /*echo CHtml::encode(Yii::app()->name); */?></div>-->
<!--        </div>-->
        <!--// header -->
        <?php echo $content; ?>
        
        <div id="footer">
            Copyright &copy; <?php echo date('Y'); ?> by <a href="//www.ubertheme.com/" target="_blank">UberTheme</a>.
            All Rights Reserved.<br/>
            <?php //echo Yii::powered(); ?>
            <div id="report-bugs">
                <a target="_blank" href="//www.ubertheme.com/ask-question/" title="<?php echo Yii::t('frontend', 'Report Bugs/Ask a question')?>">
                    <strong><?php echo Yii::t('frontend', 'Report Bugs/Ask a question')?></strong>
                </a>
            </div>
        </div><!-- footer -->
    </div>
</div><!-- page -->
<!-- processing message -->
<div id="migrate-loading" class="loading-mask" style="display: none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/migrate.gif"/><br/>
    <?php echo Yii::t('frontend', 'Data migration in progress. Please wait….'); ?><br/>
    <i>(<?php echo Yii::t('frontend', 'If your data has up to 5,000+ records, the migration can take up to a few hours to complete.'); ?>)</i>
</div>
<div id="reset-loading" class="loading-mask" style="display: none;">
    <img src="<?php echo Yii::app()->request->baseUrl; ?>/css/images/reset.gif"/><br/>
    <?php echo Yii::t('frontend', 'Data resetting. Please wait…'); ?><br/>
    <i>(<?php echo Yii::t('frontend', 'If your data has up to 5,000+ records, this task can take up to a few hours to complete.'); ?>)</i>
</div>
<!--// processing message-->
</body>
</html>