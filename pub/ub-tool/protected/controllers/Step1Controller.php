<?php

/**
 * @todo: Databases settings
 *
 * Class Step1Controller
 */
class Step1Controller extends Controller
{
    public $layout = '2column_left';

    protected $stepIndex = 1;

    /**
     * @todo: Setting
     */
    public function actionSetting()
    {
        //get step object
        $step = UBMigrate::model()->find("code = 'step{$this->stepIndex}'");

        if (Yii::app()->request->isPostRequest) {
            //check and save new settings
            $step->setting_data = json_encode($_POST);

            //validate databases
            mysqli_report(MYSQLI_REPORT_STRICT);
            $err_msg = array();
            $validate = true;
            //validate magento1 database
            $port1 = (!empty($_POST['mg1_db_port'])) ? $_POST['mg1_db_port'] : ini_get("mysqli.default_port");
            try {
                $connection = mysqli_connect($_POST['mg1_host'], $_POST['mg1_db_user'], $_POST['mg1_db_pass'], $_POST['mg1_db_name'], $port1);
                if ($connection) {
                    //validate magento2 database
                    /*$port2 = (!empty($_POST['mg2_db_port'])) ? $_POST['mg2_db_port'] : ini_get("mysqli.default_port");
                    try {
                        $connection = mysqli_connect($_POST['mg2_host'], $_POST['mg2_db_user'], $_POST['mg2_db_pass'], $_POST['mg2_db_name'], $port2);
                    } catch (Exception $e ) {
                        $err_msg[] = Yii::t('frontend', "Couldn't connected to Magento 2 database: ".$e->getMessage());
                        $validate = false;
                    }*/
                }
            } catch (Exception $e) {
                $err_msg[] = Yii::t('frontend', "Couldn't connected to Magento 1 database: " . $e->getMessage());
                $validate = false;
            }
            if (isset($connection) AND $connection) {
                mysqli_close($connection);
            }

            if ($validate) {
                //save to config file
                $configFilePath = Yii::app()->basePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "db.php";
                if (file_exists($configFilePath) && is_writable($configFilePath)) {
                    $configTplFilePath = Yii::app()->basePath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "db.tpl";
                    $configs = file_get_contents($configTplFilePath);
                    //replace needed configs
                    $configs = str_replace('{MG1_HOST}', $_POST['mg1_host'], $configs);
                    $configs = str_replace('{MG1_DB_PORT}', $port1, $configs);
                    $configs = str_replace('{MG1_DB_NAME}', $_POST['mg1_db_name'], $configs);
                    $configs = str_replace('{MG1_DB_USER}', $_POST['mg1_db_user'], $configs);
                    $configs = str_replace('{MG1_DB_PASS}', $_POST['mg1_db_pass'], $configs);
                    $configs = str_replace('{MG1_TABLE_PREFIX}', $_POST['mg1_db_prefix'], $configs);
                    $configs = str_replace('{MG1_VERSION}', $_POST['mg1_version'], $configs);
                    //Mage2
                    /*$configs = str_replace('{MG2_HOST}', $_POST['mg2_host'], $configs);
                    $configs = str_replace('{MG2_DB_PORT}', $port2, $configs);
                    $configs = str_replace('{MG2_DB_NAME}', $_POST['mg2_db_name'], $configs);
                    $configs = str_replace('{MG2_DB_USER}', $_POST['mg2_db_user'], $configs);
                    $configs = str_replace('{MG2_DB_PASS}', $_POST['mg2_db_pass'], $configs);
                    $configs = str_replace('{MG2_TABLE_PREFIX}', $_POST['mg2_db_prefix'], $configs);*/

                    //save
                    if (file_put_contents($configFilePath, $configs)) {
                        //save settings to database
                        $step->status = UBMigrate::STATUS_FINISHED;
                        if ($step->save()) {
                            //alert message
                            Yii::app()->user->setFlash('success', Yii::t('frontend', "Your settings was saved successfully."));
                            //get next step index
                            $stepIndex = ($this->stepIndex < UBMigrate::MAX_STEP_INDEX) ? ++$this->stepIndex : 1;
                            sleep(3);
                            //go to next step
                            $this->redirect(UBMigrate::getSettingUrl($stepIndex));
                        }
                    }
                } else {
                    Yii::app()->user->setFlash('note', Yii::t('frontend', "The config file was not exists or not ablewrite permission.<br/>Please make writeable for config file and try again."));
                }
            } else {
                Yii::app()->user->setFlash('error', implode('</br>', $err_msg));
            }
        } else {
            //auto load database of magento 2 settings if exists
            $configFilePath = Yii::app()->basePath . "/../../../app/etc/env.php";
            if (file_exists($configFilePath)) {
                $configData = require $configFilePath;
                $settings = (object)json_decode($step->setting_data);
                $settings->mg2_host = (isset($configData['db']['connection']['default']['host'])) ? $configData['db']['connection']['default']['host'] : '';
                $settings->mg2_db_user = (isset($configData['db']['connection']['default']['username'])) ? $configData['db']['connection']['default']['username'] : '';
                $settings->mg2_db_pass = (isset($configData['db']['connection']['default']['password'])) ? $configData['db']['connection']['default']['password'] : '';
                $settings->mg2_db_name = (isset($configData['db']['connection']['default']['dbname'])) ? $configData['db']['connection']['default']['dbname'] : '';
                $settings->mg2_db_prefix = (isset($configData['db']['table_prefix'])) ? $configData['db']['table_prefix'] : '';
                $hostInfo = explode(':', $settings->mg2_host);
                $settings->mg2_db_port = isset($hostInfo[1]) ? $hostInfo[1] : ini_get("mysqli.default_port");
            }
        }

        if (!isset($settings)) {
            $settings = (object)json_decode($step->setting_data);
        }

        $assign_data = array(
            'step' => $step,
            'settings' => $settings
        );
        $this->render("setting", $assign_data);
    }
}
