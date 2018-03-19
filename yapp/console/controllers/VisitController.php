<?php

namespace app\commands;

use app\models\Visit;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class VisitController extends Controller
{
    /**
     * Compress visits by day - UTM source on days ago
     * 2 run by cron
     */
    public function actionCompress()
    {
        $daysAgo = 30;
        $today = time();
        $oldTime = $today - ($daysAgo*86400); // 24*60*60 = 86400
        $allVisits = Visit::find()
            ->where(['<','created_at',$oldTime])
            ->andWhere(['comment'=>null])
            ->orderBy([
                'lp_hrurl'=> SORT_ASC,
                'utm_source'=> SORT_ASC,
                'utm_medium'=> SORT_ASC,
                'utm_campaign'=> SORT_ASC
            ])
            ->all();
        $values = $allVisits;
        ArrayHelper::multisort($values, ['created_at'], [SORT_DESC]);
        $max = $values[0]['created_at'];
        ArrayHelper::multisort($values, ['created_at'], [SORT_ASC]);
        $min = $values[0]['created_at'];

        $visitsByDay=[];

        for($dayStart = $min - ($min % 86400);$dayStart < $oldTime; $dayStart += 86400){
            $dayEnd = $dayStart + 86400;
            $dayVisits = Visit::find()
                ->where(['>','created_at',$dayStart])
                ->andWhere(['<','created_at',$dayEnd])
                ->andWhere(['comment'=>null])
                ->orderBy([
                    'lp_hrurl'=> SORT_ASC,
                    'utm_source'=> SORT_ASC,
                    'utm_medium'=> SORT_ASC,
                    'utm_campaign'=> SORT_ASC
                ])
                ->all();

            if ($dayVisits!=null) {
                $visitsByDay[] = $dayVisits;
            }
        }

        foreach ($visitsByDay as $visits) {
            $result = new Visit();
            $oldVisit = null;
            foreach ($visits as $visit) {
                if($oldVisit == null){
                    $result['lp_hrurl'] = $visit['lp_hrurl'];
                    $result['utm_source'] = $visit['utm_source'] ;
                    $result['utm_medium'] = $visit['utm_medium'] ;
                    $result['utm_campaign'] = $visit['utm_campaign'] ;
                    $result['qnt'] = $visit['qnt'];
                    $result['comment'] = ''.$visit['created_at'];
                    $oldVisit = $result;
                } else {
                    if($visit['lp_hrurl'] == $oldVisit['lp_hrurl']){
                        if ($visit['utm_source']==$oldVisit['utm_source']) {
                            if($visit['utm_medium']==$oldVisit['utm_medium']){
                                if ($visit['utm_campaign']==$oldVisit['utm_campaign']) {
                                    $result['qnt']+=$visit['qnt'];
                                } else {   // $visit['utm_campaign']!=$oldVisit['utm_campaign']
                                    $result->save();
                                    $result = new Visit();
                                    $result['lp_hrurl'] = $visit['lp_hrurl'];
                                    $result['utm_source'] = $visit['utm_source'];
                                    $result['utm_medium'] = $visit['utm_medium'];
                                    $result['utm_campaign'] = $visit['utm_campaign'];
                                    $result['comment'] = ''.$visit['created_at'];
                                    $result['qnt'] = $visit['qnt'] ;
                                    $oldVisit = $result;
                                }
                            } else {   // $visit['utm_medium']!=$oldVisit['utm_medium']
                                $result->save();
                                $result = new Visit();
                                $result['lp_hrurl'] = $visit['lp_hrurl'];
                                $result['utm_source'] = $visit['utm_source'];
                                $result['utm_medium'] = $visit['utm_medium'];
                                $result['utm_campaign'] = $visit['utm_campaign'];
                                $result['comment'] = ''.$visit['created_at'];
                                $result['qnt'] = $visit['qnt'] ;
                                $oldVisit = $result;
                            }
                        } else {   // $visit['utm_source']!=$oldVisit['utm_source']
                            $result->save();
                            $result = new Visit();
                            $result['lp_hrurl'] = $visit['lp_hrurl'];
                            $result['utm_source'] = $visit['utm_source'];
                            $result['utm_medium'] = $visit['utm_medium'];
                            $result['utm_campaign'] = $visit['utm_campaign'];
                            $result['comment'] = ''.$visit['created_at'];
                            $result['qnt'] = $visit['qnt'] ;
                            $oldVisit = $result;
                        }
                    } else { //   $visit['lp_hrurl'] != $oldVisit['lp_hrurl']
                        $result->save();
                        $result = new Visit();
                        $result['lp_hrurl'] = $visit['lp_hrurl'];
                        $result['utm_source'] = $visit['utm_source'];
                        $result['utm_medium'] = $visit['utm_medium'];
                        $result['utm_campaign'] = $visit['utm_campaign'];
                        $result['comment'] = ''.$visit['created_at'];
                        $result['qnt'] = $visit['qnt'] ;
                        $oldVisit = $result;
                    }
                }
                $visit->delete();
            }
            $result->save();
        }

    }
}

// запуск из консоли php yii visit/compress