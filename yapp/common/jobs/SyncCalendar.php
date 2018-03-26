<?php

namespace common\jobs;

use Yii;

use shakura\yii2\gearman\JobBase;

class SyncCalendar extends JobBase
{
    public function execute(\GearmanJob $job = null)
    {
        $info = [
            'action'=>'sync calendar job',
        ];
        Yii::info($info, 'b2bBot');
        return 'job done';
    }
}