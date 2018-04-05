<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bot Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bot-settings-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Bot Settings', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

//            'id',
//            'bot_name',
//            'name',
            [
//                'class' => 'kartik\grid\DataColumn',
                'attribute'=>'name',
                'format'=>'html',
                'contentOptions' => [
                    'style'=>'width: 20%; overflow: scroll;word-wrap: break-word;white-space:pre-line;'
                ],
            ],
            [
                'attribute'=>'discription',
                'format'=>'html',
                'contentOptions' => [
                    'style'=>'width: 30%; overflow: scroll;word-wrap: break-word;white-space:pre-line;'
                ],
            ],

            [
                'attribute'=>'value',
                'format'=>'html',
                'contentOptions' => [
                    'style'=>'width: 40%; overflow: scroll;word-wrap: break-word;white-space:pre-line;'
                ],
            ],

            [
                'class' => \yii\grid\ActionColumn::className(),
                'contentOptions' => [
                    'style'=>'width: 10%; overflow: scroll;word-wrap: break-word;white-space:pre-line;'
                ],
                'buttons' => [
                    'delete'=>function($url,$model){
                        return false;
                    },

                ]
            ],
        ],
    ]); ?>
</div>
