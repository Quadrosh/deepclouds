<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\base\Model;


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
}
