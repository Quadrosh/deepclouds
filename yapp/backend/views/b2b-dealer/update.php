<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\B2bDealer */

$this->title = 'Изменение дилера: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Дилеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="b2b-dealer-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
