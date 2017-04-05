<?php

/**
 * RunCommand class - CLI
 */
class RunCommand extends CConsoleCommand
{
    protected $stepIndex;
    protected $percent;

    public function actionIndex($step = -1, $limit = false)
    {
        Yii::app()->db->createCommand("SET FOREIGN_KEY_CHECKS=0")->execute();

        $this->percent = UBMigrate::getPercentByStatus(UBMigrate::STATUS_FINISHED, [1]);
        if ($step > 0) { //has specify step
            if ($step >= 2 AND $step <= UBMigrate::MAX_STEP_INDEX) {
                $this->stepIndex = $step;
                $controllerName = "Step{$this->stepIndex}Controller";
                $step = new $controllerName("{step{$this->stepIndex}}");
                $step->activeCLI();
                if ($limit > 0) {
                    $step->setLimit($limit);
                }
                $this->_migrateData($step);
            } else {
                echo "ATTENTION: You can run command lines for steps 2, 3, 4, 5, 6, 7, and 8 only.\n";
            }

        } else { //run all steps
            $steps = [2, 3, 4, 5, 6, 7, 8];
            foreach ($steps as $step) {
                $this->stepIndex = $step;
                $controllerName = "Step{$this->stepIndex}Controller";
                $step = new $controllerName("{step{$this->stepIndex}}");
                $step->activeCLI();
                if ($limit > 0) {
                    $step->setLimit($limit);
                }
                $this->_migrateData($step);
            }
            echo "\n********** Data migration was done with all steps **********\n";
        }

        Yii::app()->db->createCommand("SET FOREIGN_KEY_CHECKS=1")->execute();
    }

    private function _migrateData($step)
    {
        do {
            echo "Processing in step #{$this->stepIndex}...";
            $result = $step->actionRun();
            $status = $result['status'];
            echo "{$result['message']}\n";
            //update percent finished
            /*if (isset ($result['percent_up']) AND $result['percent_up']) {
                $this->percent += (float)$result['percent_up'];
            }
            $value = round($this->percent);
            if ($status == 'ok') {
                echo "Total Data Migrated: {$value}%\n";
            }*/
        } while ($status == 'ok');

        if ($status == 'fail') {
            $msg = (isset($result['notice']) AND $result['notice']) ? $result['notice'] : (($result['errors']) ? $result['errors'] : '');
            echo "Status: {$status}\n";
            echo "Message: {$msg}\n";
        } else { //done
            $value = UBMigrate::getPercentByStatus(UBMigrate::STATUS_FINISHED, [1]);
            echo "Total Data Migrated: {$value}%\n";
        }
    }

}