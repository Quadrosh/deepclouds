<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\JobCounterStat */

$this->title = 'Create Job Counter Stat';
$this->params['breadcrumbs'][] = ['label' => 'Job Counter Stats', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-counter-stat-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
