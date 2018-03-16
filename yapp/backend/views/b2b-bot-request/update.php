<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\B2bBotRequest */

$this->title = 'Update B2b Bot Request: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'B2b Bot Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="b2b-bot-request-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
