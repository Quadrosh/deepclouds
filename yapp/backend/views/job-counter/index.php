<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Job Counters';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-counter-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
//            'start',
            [
                'attribute'=>'start',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['start'], 'dd/MM/yy HH:mm:ss');
//                    return date(" g:i a, F j, Y",$data['start']);
                },
                'format'=> 'html',
            ],
            'count',
            'queue',
            'max_count',
            [
                'attribute'=>'reset_date',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['reset_date'], 'dd/MM/yy HH:mm:ss');
//                    return date(" g:i a, F j, Y",$data['start']);
                },
                'format'=> 'html',
            ],

//            'status',
//             'created_at',
//            [
//                'attribute'=>'start',
//                'value' => function($data)
//                {
//                    return \Yii::$app->formatter->asDatetime($data['start'], 'dd/MM/yy HH:mm:ss');
////                    return date(" g:i a, F j, Y",$data['start']);
//                },
//                'format'=> 'html',
//            ],

//            ['class' => 'yii\grid\ActionColumn'],
            [
                'class' => \yii\grid\ActionColumn::className(),
                'buttons' => [
                    'delete'=>function($url,$model){
                        $newUrl = Yii::$app->getUrlManager()->createUrl(['/job-counter/reset','id'=>$model['id']]);
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-refresh"></span>', $newUrl,
                            ['title' => Yii::t('yii', 'Reset'), 'data-pjax' => '0','data-method'=>'post']);
                    },
//                    'view'=>function($url,$model){
//                        return false;
//                    },
//                    'update'=>function($url,$model){
//                        return false;
//                    },

                ]
            ],
        ],
    ]); ?>
</div>
