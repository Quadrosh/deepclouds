<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property string $site
 * @property string $name
 * @property string $address
 * @property string $workload
 * @property string $statua
 * @property integer $created_at
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
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
            [['name', 'address'], 'required'],
            [['created_at'], 'integer'],
            [['site', 'name', 'address', 'statua'], 'string', 'max' => 255],
            [['workload'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site' => 'Site',
            'name' => 'Name',
            'address' => 'Address',
            'workload' => 'Workload',
            'statua' => 'Statua',
            'created_at' => 'Created At',
        ];
    }
}
