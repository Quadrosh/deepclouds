<?php
namespace common\rbac;

use yii\rbac\Rule;

class CreatorRule extends Rule
{
    public $name = 'Creator';

    public function execute($user, $item, $params)
    {
        return true;
    }
}