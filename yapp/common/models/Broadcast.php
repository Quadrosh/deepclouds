<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "broadcast".
 *
 * @property integer $id
 * @property string $bot
 * @property string $name
 * @property string $text
 * @property integer $count
 * @property integer $all_count
 * @property integer $start_time
 * @property integer $end_time
 * @property string $status
 * @property integer $created_at
 */
class Broadcast extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'broadcast';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['text'], 'string'],
            [['count', 'all_count', 'start_time', 'end_time', 'created_at'], 'integer'],
            [['bot', 'name', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bot' => 'Bot',
            'name' => 'Name',
            'text' => 'Text',
            'count' => 'Count',
            'all_count' => 'All Count',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
