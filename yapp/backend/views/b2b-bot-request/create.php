<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\B2bBotRequest */

$this->title = 'Create B2b Bot Request';
$this->params['breadcrumbs'][] = ['label' => 'B2b Bot Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-bot-request-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
