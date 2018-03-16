<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\B2bBotUser */

$this->title = 'Update B2b Bot User: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'B2b Bot Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="b2b-bot-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
