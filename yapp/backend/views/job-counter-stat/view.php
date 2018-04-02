<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\JobCounterStat */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Job Counter Stats', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-counter-stat-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'count',
//            'created_at',
            [
                'attribute'=>'created_at',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['created_at'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],
        ],
    ]) ?>

</div>
