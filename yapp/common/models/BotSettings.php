<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "bot_settings".
 *
 * @property integer $id
 * @property string $bot_name
 * @property string $name
 * @property string $discription
 * @property string $value
 * @property integer $created_at
 */
class BotSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bot_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['value'], 'string'],
            [['created_at'], 'integer'],
            [['bot_name', 'name', 'discription'], 'string', 'max' => 255],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
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
            'bot_name' => 'Bot Name',
            'name' => 'Setting',
            'discription' => 'Description',
            'value' => 'Value',
            'created_at' => 'Created At',
        ];
    }
}
