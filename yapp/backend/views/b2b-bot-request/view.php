<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\B2bBotRequest */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'B2b Bot Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-bot-request-view">

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
        ],
    ]) ?>

</div>
