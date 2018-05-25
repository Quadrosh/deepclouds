<?php

namespace common\models;

use common\jobs\SendLimitedJob;
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
//        $start_time = time();
//        $serverError = [];
//
//        while(true) {
//
//            if ((time() - $start_time) > 10) {
//
//                $serverError['error'] = 1;
//                $serverError['message'] = 'Извините, B2B сервер не отвечает'.PHP_EOL .'В данный момент запрос не может быть обработан';
//                return Json::encode($serverError);
//            }
//
////            sleep(28);
//            // Other processing
//
//
//        }

//        $options['apiKey']= Yii::$app->params['b2bServerApiKey'];
//        $optQuery = http_build_query($options);
//        $ch = curl_init($url.'?'.$optQuery);

//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
//        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 27);

//        curl_setopt($ch, CURLOPT_POSTFIELDS, $optQuery);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // return string
//        curl_setopt($ch, CURLOPT_POST, true); // use http post
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // no check sert by remote


        $prxy       = 'http://smzvl.teletype.live:1080'; // proxy_url:port
        $prxy_auth = 'telegram:telegram';       // 'auth_user:auth_pass'



        $options['apiKey']= Yii::$app->params['b2bServerApiKey'];
        $optQuery = http_build_query($options);
        $url = $url.'?'.$optQuery;

        $ch = curl_init();
        curl_setopt_array ($ch, array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true));
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);  // тип прокси
        curl_setopt($ch, CURLOPT_PROXY,  $prxy);                 // ip, port прокси
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $prxy_auth);  // авторизация на прокси
        curl_setopt($ch, CURLOPT_HEADER, false);                // отключение передачи заголовков в запросе

        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 27);    // timeout

        curl_setopt($ch, CURLOPT_POSTFIELDS, $optQuery);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // return string
        curl_setopt($ch, CURLOPT_POST, true); // use http post
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // no check sert by remote

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
     * @param int $requestId
     * @param string $url
     * @param string []  $options
     * @param boolean  $dataInBody
     *
     * @return string json
     */
    public static function sendToUser($requestId, $url, $options = [], $dataInBody = false)
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
        $request = B2bBotRequest::findOne(['id'=>$requestId]);
        $request['status'] = 'answered';
        $request['answer_time'] = time();
        $request->save();
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




    public function sendDebugJob($options)
    {

//        $options['text'] = microtime(true);
//        $id = Yii::$app->queue->push(new SendLimitedJob([
//            'options' => $options,
//        ]));
        $i =10;
        while($i > 0){
            $text = strval($options['text']);
            $microtime = microtime(true);
            $options['text'] = $i . PHP_EOL.' controller - '. $text . PHP_EOL.' sender - '.$microtime;
            Yii::$app->queue->push(new SendLimitedJob([
                'options' => $options,
            ]));
            $i = $i-1;
        }
        return 'its done';
    }

    public function sendJob($requestId, $url, $options, $dataInBody=true)
    {

            Yii::$app->queue->push(new SendLimitedJob([
                'requestId' => $requestId,
                'url' => $url,
                'options' => $options,
                'dataInBody' => $dataInBody,
            ]));

        return 'job in queue';

    }
}
