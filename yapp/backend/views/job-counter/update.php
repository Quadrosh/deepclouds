<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\JobCounter */

$this->title = 'Update Job Counter: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Job Counters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="job-counter-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
