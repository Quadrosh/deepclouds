<?php

namespace common\jobs;

use common\models\B2bSender;
use common\models\Task;
use Yii;

/**
 * Class YiiJob.
 */
class YiiJob extends \yii\base\Object implements \yii\queue\RetryableJob
//class YiiJob extends \yii\base\Object implements \yii\queue\Job
{
    public $options;


//    public static $startOfPeriod;
//    public static $count;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $periodInSec = 20;
        $jobLimit = 2;


//        if (self::$startOfPeriod == null) {
//            self::$startOfPeriod = time();
//        }
//        if (self::$count == null) {
//            self::$count = 0;
//        }
//
//        self::$count++;
//
//        if (self::$count > $jobLimit) {
//            self::$startOfPeriod = self::$startOfPeriod + $periodInSec;
//            self::$count = 1;
//        }



        $info = [
            'action'=>'B2B Yii Gearman start job',
            'time'=>time(),
//            'startOfPeriod'=>self::$startOfPeriod,
//            'myCount'=>self::$count,
        ];
        file_put_contents(dirname(dirname(__DIR__)).'/frontend/runtime/logs/job.log',
            '----------------'.PHP_EOL
            .date(" g:i a, F j, Y").PHP_EOL.print_r($info,true).PHP_EOL, FILE_APPEND);


//        if (self::$startOfPeriod > time()) {
//            time_sleep_until(self::$startOfPeriod);
//        }


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


