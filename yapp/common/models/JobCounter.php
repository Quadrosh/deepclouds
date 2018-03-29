<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "job_counter".
 *
 * @property integer $id
 * @property string $name
 * @property integer $start
 * @property integer $count
 * @property string $status
 * @property integer $created_at
 */
class JobCounter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'job_counter';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
                'createdAtAttribute'=>'created_at',
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'start', 'count'], 'required'],
            [['start', 'count', 'created_at'], 'integer'],
            [['name', 'status'], 'string', 'max' => 255],
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
            'start' => 'Start',
            'count' => 'Count',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
