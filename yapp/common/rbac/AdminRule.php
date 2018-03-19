<?php
namespace common\rbac;

use yii\rbac\Rule;

class AdminRule extends Rule
{
    public $name = 'Admin';

    public function execute($user, $item, $params)
    {
        return true;
    }
}