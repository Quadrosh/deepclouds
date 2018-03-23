<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace console\controllers;

use common\models\RolesAssignment;

use common\rbac\AdminRule;
use common\rbac\CreatorRule;
use common\rbac\StatRule;
use yii\console\Controller;

class WorkerController extends Controller {

    public function actionIndex() {

        $worker = new \GearmanWorker();
        $worker->addServer();

//        $worker->addFunction("reverse", "reverse_cb", $count);
        $worker->addFunction("reverse", "php yii worker/dojob");
//        $worker->work();

        while($worker->work());


    }

    public function actionDojob($job) {

            return  strrev($job->workload());

    }
}

// запуск из консоли php yii worker