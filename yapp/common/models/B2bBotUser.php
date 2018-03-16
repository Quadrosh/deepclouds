<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "b2b_bot_user".
 *
 * @property int $id
 * @property int $telegram_user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $real_first_name
 * @property string $real_last_name
 * @property int $b2b_dealer_id
 * @property string $email
 * @property string $phone
 * @property string $status
 * @property string $bot_command
 * @property int $updated_at
 * @property int $created_at
 *
 * @property B2bDealer $dealer
 */
class B2bBotUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'b2b_bot_user';
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
            [['telegram_user_id'], 'required'],
            [
                [
                'b2b_dealer_id',
                'telegram_user_id',
                'updated_at',
                'created_at'
                ], 'integer'
            ],
            [
                [
                'bot_command',
                'first_name',
                'last_name',
                'real_first_name',
                'real_last_name',
                'username',
                'email',
                'phone',
                'status'
                ], 'string', 'max' => 255
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'telegram_user_id' => 'Telegram User ID',
            'bot_command' => 'Bot Command',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'real_first_name' => 'Real First Name',
            'real_last_name' => 'Real Last Name',
            'username' => 'Username',
            'b2b_dealer_id' => 'B2b Dealer ID',
            'email' => 'Email',
            'phone' => 'Phone',
            'status' => 'Status',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function getDealer()
    {
        return $this->hasOne(B2bDealer::className(), ['id'=>'b2b_dealer_id']);
    }
}
