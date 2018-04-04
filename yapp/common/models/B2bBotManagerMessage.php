<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "b2b_bot_manager_message".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $text
 * @property integer $created_at
 */
class B2bBotManagerMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'b2b_bot_manager_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'integer'],
            [['text'], 'required'],
            [['text'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'text' => 'Text',
            'created_at' => 'Created At',
        ];
    }
}
