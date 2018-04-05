<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\BotSettings */

$this->title = 'Update Bot Settings: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bot Settings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bot-settings-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_update_form', [
        'model' => $model,
    ]) ?>

</div>
