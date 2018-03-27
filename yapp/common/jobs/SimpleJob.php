<?php

namespace common\jobs;
use common\models\B2bSender;
use Yii;


use shakura\yii2\gearman\JobBase;

class SimpleJob extends JobBase
{
    public function execute(\GearmanJob $job = null)
    {
        // Do something
//        $content = $job->workload();

//        return 'there job done too';

        $info = [
            'action'=>'B2B Gearman job',
            'job->workload'=>$job->workload(),
        ];

        Yii::trace($info, 'b2bBot');
        Yii::trace('chek trace', 'b2bBot');
        Yii::trace('chek trace b2bBot');
        Yii::warning($info);
        Yii::warning('chek trace','b2bBot' );
        Yii::warning('chek trace' );


        $options = [
            'chat_id' => '232544919',
            'text' => 'чек чек gearman',
        ];
        $chat_id = $options['chat_id'];
        $urlEncodedText = urlencode($options['text']);

        $sender = new B2bSender;
        $sender->sendToUser('https://api.telegram.org/bot' .
            'https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'].
            '/sendMessage?chat_id='.$chat_id .
            '&text='.$urlEncodedText, $options, true);

        return 'it seems i send something';
    }


}