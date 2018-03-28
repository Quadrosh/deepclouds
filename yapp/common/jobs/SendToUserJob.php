<?php

namespace common\jobs;
use common\models\B2bSender;
use common\models\Task;
use shakura\yii2\gearman\JobWorkload;
use Yii;


use shakura\yii2\gearman\JobBase;
use yii\helpers\Json;

class SendToUserJob extends JobBase
{
    private static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance != null) {
            return self::$_instance;
        }
        return new self;
    }




    public function execute(\GearmanJob $job = null)
    {
        $this->_instance = self::getInstance();
        $periodInSec = 20;
        $jobLimit = 2;
        $startOfPeriod = time();
        $jobIter = 0;

        $info = [
            'action'=>'B2B Gearman start job',
            'startOfPeriod'=>$startOfPeriod,
            'jobIter'=>$jobIter,
        ];
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);

        $tasks = Task::find()->where([
            'site'=>'b2b',
            'name'=>'sendToUser',
        ])->all();


        $info = [
            'action'=>'B2B Gearman found tasks',
            'tasks'=> $tasks,
        ];

        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);

        foreach ($tasks as $task) {

            if ($startOfPeriod + $periodInSec > time()) {

                if ($jobIter <= $jobLimit) {

                    $this->process($task);
                    $jobIter ++;

                } else {
                    $jobIter = 0;
                    time_sleep_until($startOfPeriod + $periodInSec);
                    $startOfPeriod = time();
                    $this->process($task);

                }

            } else {

            }

        }

    }

    /*
     * @var common\models\Task  $task
     * */
    private function process($task)
    {
        $options = unserialize($task['workload']);
        $chat_id = $options['chat_id'];
        $urlEncodedText = urlencode($options['text']);
        $sender = new B2bSender;
        $result = $sender->sendToUser('https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'].
            '/sendMessage?chat_id='.$chat_id .
            '&text='.$urlEncodedText, $options, true);
        if ($result) {
            $task->delete();
        }
        $info = [
            'action'=>'B2B Gearman Job send 2 user',
            'options'=>$options,
            'result'=>$result,
        ];
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);
    }


}