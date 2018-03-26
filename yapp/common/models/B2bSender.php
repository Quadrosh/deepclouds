<?php

namespace common\models;

use shakura\yii2\gearman\JobWorkload;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\base\Model;
use yii\filters\ContentNegotiator;
use yii\helpers\Json;
use yii\web\Response;

/**
 * This is the sender class.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $entry_phones
 * @property string $status
 * @property int $updated_at
 * @property int $created_at
 */
class B2bSender extends Model
{

    public function behaviors() {
        return [
            'contentNegotiator' => [
                'class'   => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],

        ];
    }



    /**
     * get info from server
     *
     * @param string $url
     * @param string []  $options
     *
     * @return string json
     */

    public function sendToServer($url, $options = [])
    {
        $options['apiKey']= Yii::$app->params['b2bServerApiKey'];
        $optQuery = http_build_query($options);
        $ch = curl_init($url.'?'.$optQuery);


        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
//        curl_setopt($ch, CURLOPT_ENCODING,'gzip,deflate');
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $optQuery);
        curl_setopt($ch, CURLOPT_POST, true); // Content-Type: application/x-www-form-urlencoded' header.

        // debug
//        $fp = fopen('../runtime/logs/curl_debug_log.txt', 'w');
//        curl_setopt($ch, CURLOPT_VERBOSE, 1);
//        curl_setopt($ch, CURLOPT_STDERR, $fp);

        $r = curl_exec($ch);

        if($r == false){
            $text = 'curl error '.curl_error($ch);
            Yii::info($text, 'b2bBot');
            return $text;
        } else {
            $info = curl_getinfo($ch);
            $info['url'] = str_replace(Yii::$app->params['b2bServerApiKey'],'_not_logged_',  $info['url']);
            $options['apiKey']='_not_logged_';
            $info = [
                    'action'=>'curl to Server',
                    'options'=>$options,
                    'curl_version'=>curl_version(),
                ] + $info;
            Yii::info($info, 'b2bBot');
            if ($info['http_code'] == 500) {
                $serverError = [];
                $serverError['error'] = 1;
                $serverError['message'] = 'Извините, на сервере технические проблемы.'
                    .PHP_EOL .'В данный момент запрос не может быть обработан';
                $serverError['code'] = 500;
                curl_close($ch);
                return Json::encode($serverError);
            }
            if ($info['http_code'] == 400) {
                $serverError = [];
                $serverError['error'] = 1;
                $serverError['message'] = 'Извините, у нас проблемы со связью.'
                    .PHP_EOL .'В данный момент запрос не может быть обработан.';
                $serverError['code'] = 400;
                curl_close($ch);
                return Json::encode($serverError);
            }
        }
        curl_close($ch);
        return $r;
    }



    /**
     * Sends message to user
     *
     * @param string $url
     * @param string []  $options
     * @param boolean  $dataInBody
     *
     * @return string json
     */
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
            $text = 'curl error '.curl_error($ch);
            Yii::info($text, 'b2bBot');
        } else {
            $info = curl_getinfo($ch);
            $info['url'] = str_replace(Yii::$app->params['b2bBotToken'],'_not_logged_',  $info['url']);
            $info = [
                    'action'=>'curl to User',
                    'options'=>$options,
                    'dataInBody'=>$dataInBody,
                ] + $info;
            Yii::info($info, 'b2bBot');
        }
        curl_close($ch);
        return $r;
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     * @return bool whether the email was sent
     */
    public function sendEmail($text,$from)
    {
        return Yii::$app->mailer->compose()
            ->setTo(Yii::$app->params['b2bMainInputEmail'])
            ->setFrom(Yii::$app->params['b2bFromEmail'])
            ->setSubject($this->phone.'-'.$from)
            ->setTextBody($text)
            ->setHtmlBody(
                nl2br($text)
            )
            ->send();
    }



    public function sendByWorker()
    {

        $info = [
            'action'=>'sender sendByWorker',
        ];
        Yii::info($info, 'b2bBot');
        Yii::$app->gearman->getDispatcher()->background('syncCalendar', new JobWorkload([
            'params' => [
                'data' => 'value'
            ]
        ]));

        return 'sent to gearman';
//        $client = new \GearmanClient();
//        $client->addServer();
//        $client->setTimeout(29000);
//
//        $data = 'slon yooo';
//        $res = $client->doNormal('revert_string', $data);
//        return $res;
    }
}
