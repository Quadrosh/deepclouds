<?php

namespace common\jobs;

use common\models\B2bSender;
use common\models\JobCounter;
use common\models\Task;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class YiiJob.
 */
class SendLimitedJob extends \yii\base\Object implements \yii\queue\RetryableJob
{
    public $options;


    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $periodInSec = 10;
        $jobLimit = 2;
//        $key = null;


        $counter = JobCounter::find()->where(['name'=>'sendToUser'])->one();
        $this->log([
            'action'=>'counter init find',
            'counter count'=>$counter['count'],
            'counter queue'=>$counter['queue'],
            'counter start' => $counter['start'],
            'now'=>microtime(true),
        ]);

        if ($counter == null) {
            $counter = new JobCounter();
            $counter['name']='sendToUser';
            $counter['start'] = microtime(true);
            $counter['count'] = 0;
            $counter['queue'] = 0;
            $counter->save();
        }
        elseif ($counter['queue'] < 1  &&  $counter['start'] < (microtime(true)-$periodInSec)) {
            $counter['start'] = microtime(true);
            $counter['count'] = 0;
            $counter['queue'] = 0;
            $counter->save();

            $this->log([
                'action'=>'queue < 1',
                'counter count'=>$counter['count'],
                'counter queue'=>$counter['queue'],
                'counter start' => $counter['start'],
                'now'=>microtime(true),
            ]);
        }
//        elseif ($counter['start'] < time() - 60 * 5) {
//            $counter['start'] = time();
//            $counter['count'] = 0;
//            $counter['queue'] = 0;
//            $counter->save();
//        }

        $counter['count'] = $counter['count']+1;
        $counter['queue'] = $counter['queue']+1;
        $save =  $counter->save();

        $this->log([
            'action'=>'counter +1',
            '$save'=>$save,
            'errors'=>$counter->errors,
            'counter count'=>$counter['count'],
            'counter queue'=>$counter['queue'],
            'counter start' => $counter['start'],
            'now'=>microtime(true),
        ]);

        if ($counter['count'] > $jobLimit) {
            $counter['start'] = $counter['start'] + $periodInSec;
            $counter['count'] = $counter['count'] - $jobLimit;
            $save = $counter->save();


            $this->log([
                'action'=>'count > $jobLimit',
                '$counter count'=>$counter['count'],
                '$counter start'=>$counter['start'],
                'now'=>microtime(true),
                '$save'=>$save,
            ]);
        }


//        $this->log([
//            'action'=>'just counter',
////            '$counter'=>ArrayHelper::toArray($counter, [], false),
//        ]);


        if ($counter['start'] > microtime(true)) {
            $timeToSleep = $counter['start'] - microtime(true);
            $this->log([
                'action'=>'B2B Job start > now',
                '$counter count'=>$counter['count'],
                '$counter start'=>$counter['start'],
                '$timeToSleep'=>$timeToSleep,
                'now'=>microtime(true),
            ]);

            usleep($timeToSleep*1000000);
           // time_sleep_until($counter['start']);   // usleep
        }


        $result = $this->process();

        if ($result == true) {
            $counter = JobCounter::find()->where(['name'=>'sendToUser'])->one();
//            $this->log([
//                'action'=>'counter find after complete job',
//                '$counter'=>ArrayHelper::toArray($counter, [], false),
//                'now'=>time(),
//            ]);
            $counter['queue'] = $counter['queue']-1;
            if ($counter['queue'] < 1 ) {
//                $counter['count'] = 0;
            }
            $counter->save();
            $this->log([
                'action'=>'counter save after complete job',
                '$counter'=>ArrayHelper::toArray($counter, [], false),
                'now'=>microtime(true),
            ]);
        }
    }


    private function process()
    {
        $options = $this->options;
        $chat_id = $options['chat_id'];
        $urlEncodedText = urlencode($options['text']);
        $sender = new B2bSender;
        $result = $sender->sendToUser('https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'].
            '/sendMessage?chat_id='.$chat_id .
            '&text='.$urlEncodedText, $options, true);

        $this->log([
            'action'=>'B2B Yii Gearman Job send 2 user',
//            'result'=>$result,
        ]);

        return $result;
    }

    private function log($info)
    {
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);
    }
    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}


