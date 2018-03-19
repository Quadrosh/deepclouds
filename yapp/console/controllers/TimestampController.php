<?php

namespace app\commands;

use app\models\Feedback;
use app\models\NFeedback;
use app\models\Preorders;
use app\models\Visit;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class TimestampController extends Controller
{
    /**
     * Convert from sql timestamp to utm
     *
     */
    public function actionConvert()
    {

        $feedbacks = Feedback::find()->all();
        foreach ($feedbacks as $feedback) {
            $newFeedback = new NFeedback();
            $newFeedback->attributes = $feedback->attributes;
            $newFeedback['date'] = strtotime($feedback['date']);
            $newFeedback->save();
        }
        echo 'done';

    }
}

// запуск из консоли php yii timestamp/convert