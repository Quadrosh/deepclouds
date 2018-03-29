<?php

namespace common\jobs;

use common\models\B2bSender;
use common\models\JobCounter;
use common\models\Task;
use Yii;

/**
 * Class YiiJob.
 */
class YiiJob extends \yii\base\Object implements \yii\queue\RetryableJob
//class YiiJob extends \yii\base\Object implements \yii\queue\Job
{
    public $options;


//    public $startOfPeriod;
//    public $count;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $periodInSec = 10;
        $jobLimit = 2;
        $key = null;


        $counter = JobCounter::find()->where(['name'=>'sendToUser'])->one();
        if ($counter == null) {
            $counter = new JobCounter();
            $counter['name']='sendToUser';
            $counter['start'] = time();
            $counter['count'] = 0;
            $counter->save();
        }
        elseif ( $counter['count'] < 1) {
            $counter['start'] = time();
            $counter->save();
        }

        $key = $counter['start'];
        $counter['count'] = $counter['count']+1;
        $counter->save();


        if ($counter['count'] > $jobLimit) {
            $counter['start'] = $counter['start'] + $periodInSec;
            $counter['count'] = $counter['count']-$jobLimit+1;
            $counter->save();
        }


        if ($counter['start'] > time()) {
            time_sleep_until($counter['start']);
        }


        $this->process();

//        if ($result == true) {
//            $counter = JobCounter::find()->where(['name'=>'sendToUser'])->one();
//            if ($counter['start'] == $key) {
//                $counter['count'] = $counter['count']-1;
//                $counter->save();
//            }
//        }
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


        $info = [
            'action'=>'B2B Yii Gearman Job send 2 user',
            'options'=>$options,
            'result'=>$result,
        ];
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);

        return $result;
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


