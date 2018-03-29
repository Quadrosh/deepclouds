<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\JobCounter */

$this->title = 'Create Job Counter';
$this->params['breadcrumbs'][] = ['label' => 'Job Counters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-counter-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
