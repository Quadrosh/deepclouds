<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\JobCounter */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Job Counters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-counter-view">

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
            'status',
//            'created_at',
            [
                'attribute'=>'created_at',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['created_at'], 'dd/MM/yy HH:mm:ss');
//                    return date(" g:i a, F j, Y",$data['start']);
                },
                'format'=> 'html',
            ],
        ],
    ]) ?>

</div>
