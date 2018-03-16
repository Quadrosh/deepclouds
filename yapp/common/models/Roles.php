<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 */
class Roles extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'auth_item';
    }

    /**
     * Список ролей + описание
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRoles() {
        return self::find()->select( 'name, description' )->andWhere(['type'=>1])->asArray()->all();
    }
}