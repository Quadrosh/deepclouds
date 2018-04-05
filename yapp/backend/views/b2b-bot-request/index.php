<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запросы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-bot-request-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [

            'id',
            'update_id',
            'user_id',
            'request:ntext',
            'status',
            [
                'attribute'=>'answer_time',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['answer_time'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],
            [
                'class' => \yii\grid\ActionColumn::className(),
                'buttons' => [

                    'delete'=>function($url,$model){
                        return false;
                    },
                    'update'=>function($url,$model){
                        return false;
                    },

                ]
            ],
        ],
    ]); ?>
</div>
