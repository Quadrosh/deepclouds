<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "menu_side".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $link
 * @property int $num_order
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'num_order'], 'integer'],
            [['name'], 'required'],
            [['menu_name','icon', 'name', 'link'], 'string', 'max' => 255],
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
            'menu_name' => 'Menu Name',
            'parent_id' => 'Parent ID',
            'icon' => 'Icon',
            'name' => 'Name',
            'link' => 'Link',
            'num_order' => 'Num Order',
        ];
    }
}
