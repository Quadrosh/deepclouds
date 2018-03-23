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
        $count= 0;
        $worker->addFunction("reverse", "reverse_cb", $count);
//        $worker->work();

        while($worker->work());

        function reverse_cb($job,&$count)
        {
            $count++;
            return "$count: " . strrev($job->workload());
        }

    }
}

// запуск из консоли php yii worker