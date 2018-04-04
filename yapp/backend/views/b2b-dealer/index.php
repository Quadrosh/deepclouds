<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Дилеры';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-dealer-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>Авторизация происходит по основному телефону.</p>

    <p>
        <?= Html::a('Создать дилера', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
//            'email:email',
            'phone',
//            'entry_phones',
            'status',
            //'updated_at',
            //'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
