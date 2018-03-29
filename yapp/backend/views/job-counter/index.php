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

    <p>
        <?= Html::a('Create Job Counter', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
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
                },
                'format'=> 'html',
            ],
            'count',
            'status',
//             'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
