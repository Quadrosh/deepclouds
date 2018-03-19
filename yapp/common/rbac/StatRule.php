<?php
namespace common\rbac;

use yii\rbac\Rule;

class StatRule extends Rule
{
    public $name = 'Stat';

    public function execute($user, $item, $params)
    {
        return true;
    }
}