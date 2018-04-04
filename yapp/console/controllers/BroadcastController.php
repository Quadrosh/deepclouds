<?php

namespace console\controllers;

use common\models\Broadcast;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class BroadcastController extends Controller
{
    /**
     * шлет по списку
     *
     */
    public function actionSend($id)
    {

        $broadcast = Broadcast::find()->where(['id'=>$id])->one();
        $addressesText = $broadcast['addresses'];

        $addressesArr = explode(',', $addressesText);


    }
}

// запуск из консоли php yii broadcast/send