<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "b2b_dealer".
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
class B2bDealer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'b2b_dealer';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
//                'updatedAtAttribute' => false,
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['updated_at', 'created_at'], 'integer'],
            [['name', 'email', 'phone', 'status'], 'string', 'max' => 255],
            [['entry_phones'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'entry_phones' => 'Entry Phones',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
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
