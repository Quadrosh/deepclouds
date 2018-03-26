<?php

namespace common\jobs;
use Yii;


use shakura\yii2\gearman\JobBase;

class SimpleJob extends JobBase
{
    public function execute(\GearmanJob $job = null)
    {
        // Do something
//        $content = $job->workload();

        $info = [
            'action'=>'Gearman job',
            'job->workload'=>$job->workload(),
        ];
        Yii::info($info, 'b2bBot');

        $options = [
            'chat_id' => '232544919',
            'text' => 'чек чек gearman',
        ];
        $chat_id = $options['chat_id'];
        $urlEncodedText = urlencode($options['text']);
        $this->sendToUser('https://api.telegram.org/bot' .
            'https://api.telegram.org/bot' .
            Yii::$app->params['b2bBotToken'].
            '/sendMessage?chat_id='.$chat_id .
            '&text='.$urlEncodedText, $options, true);

    }

    public static function sendToUser($url, $options = [], $dataInBody = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Telebot');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (count($options)) {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($dataInBody) {
                $bodyOptions = $options;
                unset($bodyOptions['chat_id']);
                unset($bodyOptions['text']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyOptions);
            }
        }
        $r = curl_exec($ch);
        if($r == false){
            $text = 'Gearman curl error '.curl_error($ch);
            Yii::info($text, 'b2bBot');
        } else {
            $info = curl_getinfo($ch);
            $info['url'] = str_replace(Yii::$app->params['b2bBotToken'],'_not_logged_',  $info['url']);
            $info = [
                    'action'=>'Gearman curl to User',
                    'options'=>$options,
                    'dataInBody'=>$dataInBody,
                ] + $info;
            Yii::info($info, 'b2bBot');
        }
        curl_close($ch);
        return $r;
    }
}