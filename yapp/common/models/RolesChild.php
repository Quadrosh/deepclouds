<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tbl_auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 * @property AuthItem $parent0
 * @property AuthItem $child0
 */
class RolesChild extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'auth_item_child';
    }
}
