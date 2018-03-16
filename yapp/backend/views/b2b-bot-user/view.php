<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\B2bBotUser */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'B2b Bot Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-bot-user-view">

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
            'b2b_dealer_id',
            'telegram_user_id',
            'first_name',
            'last_name',
            'real_first_name',
            'real_last_name',
            'username',
            'email:email',
            'phone',
            'bot_command',
            'status',
//            'updated_at',
            [
                'attribute'=>'updated_at',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['updated_at'], 'dd/MM/yy HH:mm:ss');
                },
                'format'=> 'html',
            ],
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
