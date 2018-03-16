<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'B2b Bot Requests';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-bot-request-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create B2b Bot Request', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'update_id',
            'user_id',
            'request:ntext',
            'answer:ntext',
//            'user_time',
            [
                'attribute'=>'user_time',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['user_time'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],
//            'request_time',
            [
                'attribute'=>'request_time',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['request_time'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],
//            'answer_time'
            [
                'attribute'=>'answer_time',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['answer_time'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
