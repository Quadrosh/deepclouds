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
    public function execute(\GearmanJob $job = null)
    {

        $tasks = Task::find()->where([
            'site'=>'b2b',
            'name'=>'sendToUser',
        ])->all();


        foreach ($tasks as $task) {
            $options = unserialize($task['workload']);

            $chat_id = $options['chat_id'];
            $urlEncodedText = urlencode($options['text']);

            $sender = new B2bSender;
            $result = $sender->sendToUser('https://api.telegram.org/bot' .
                Yii::$app->params['b2bBotToken'].
                '/sendMessage?chat_id='.$chat_id .
                '&text='.$urlEncodedText, $options, true);


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


}