<?php

namespace common\jobs;

use common\models\B2bSender;
use common\models\JobCounter;
use common\models\JobCounterStat;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class YiiJob.
 */
class SendLimitedJob extends \yii\base\Object implements \yii\queue\RetryableJob
{
    public $requestId;
    public $options;
    public $url;
    public $dataInBody;

    public function behaviors()
    {
        return [
            // anonymous behavior, behavior class name only
//            MyBehavior::className(),



//            \yii\queue\LogBehavior::className(),



            // named behavior, behavior class name only
//            'myBehavior2' => MyBehavior::className(),
//
//            // anonymous behavior, configuration array
//            [
//                'class' => MyBehavior::className(),
//                'prop1' => 'value1',
//                'prop2' => 'value2',
//            ],


        ];
    }


    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $periodInSec = 10;
        $jobLimit = 2;
//        $key = null;


        $counter = JobCounter::find()->where(['name'=>'sendToUser'])->one();

//        $this->log([
//            'action'=>'counter init find',
//            'counter count'=>$counter['count'],
//            'counter queue'=>$counter['queue'],
//            'counter start' => $counter['start'],
//            'now'=>microtime(true),
//        ]);

        if ($counter == null) {
            $this->log([
                'action'=>'$counter == null',
                'now'=>microtime(true),
            ]);
            $counter = new JobCounter();
            $counter['name']='sendToUser';
            $counter['start'] = microtime(true);
            $counter['count'] = 0;
            $counter['queue'] = 0;
            $counter['max_count'] = 0;
            $counter->save();

//            $this->log([
//                'action'=>'create counter',
//                'counter start'=>$counter['start'],
//                'counter save'=>$save,
//                'save errors'=>$counter->errors,
//                'now'=>microtime(true),
//            ]);
        }
        elseif ($counter['queue'] < 1  &&  $counter['start'] < (microtime(true)-$periodInSec*2)) {
            $counter['start'] = microtime(true);
            $counter['count'] = 0;
            $counter['queue'] = 0;
            $counter->save();

//            $this->log([
//                'action'=>'queue < 1',
//                'counter count'=>$counter['count'],
//                'counter queue'=>$counter['queue'],
//                'counter start' => $counter['start'],
//                'now'=>microtime(true),
//            ]);
        }
        elseif ($counter['start'] < microtime(true) - 60 * 5) {
            $counter['start'] = microtime(true);
            $counter['count'] = 0;
            $counter['queue'] = 0;
            $counter->save();
        }

        $counter['count'] = $counter['count']+1;
        $counter['queue'] = $counter['queue']+1;
        $counter->save();

        if ($counter['count'] > $counter['max_count']) {
            $counter['max_count']=$counter['count'];
            $counter->save();
        }

//        $this->log([
//            'action'=>'counter +1',
//            '$save'=>$save,
//            'errors'=>$counter->errors,
//            'counter count'=>$counter['count'],
//            'counter queue'=>$counter['queue'],
//            'counter start' => $counter['start'],
//            'now'=>microtime(true),
//        ]);

        if ($counter['count'] > $jobLimit) {
            $counter['start'] = $counter['start'] + $periodInSec;
            $counter['count'] = $counter['count'] - $jobLimit;
            $counter->save();
            $counterStat = new JobCounterStat;
            $counterStat['count'] = $jobLimit;
            $counterStat->save();

//            $this->log([
//                'action'=>'count > $jobLimit',
//                '$counter count'=>$counter['count'],
//                '$counter start'=>$counter['start'],
//                'now'=>microtime(true),
//                '$save'=>$save,
//            ]);
        }


//        $this->log([
//            'action'=>'just counter',
////            '$counter'=>ArrayHelper::toArray($counter, [], false),
//        ]);


        if ($counter['start'] > microtime(true)) {
            $timeToSleep = $counter['start'] - microtime(true);

//            $this->log([
//                'action'=>'B2B Job start > now',
//                '$counter count'=>$counter['count'],
//                '$counter start'=>$counter['start'],
//                '$timeToSleep'=>$timeToSleep,
//                'now'=>microtime(true),
//            ]);

            usleep($timeToSleep*1000000);
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
            $counter->save();

//            $this->log([
//                'action'=>'counter save after complete job',
//                '$counter'=>ArrayHelper::toArray($counter, [], false),
//                'now'=>microtime(true),
//            ]);

        }
    }


    private function process()
    {

        $sender = new B2bSender;
        $result = $sender->sendToUser($this->requestId, $this->url, $this->options, $this->dataInBody);

        $this->log([
            'action'=>'B2B Yii Gearman Job send 2 user',
            'requestId'=>$this->requestId,
            'result'=>$result,
        ]);

        return $result;
    }

    private function log($info)
    {
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);
        Yii::info('job done');
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


