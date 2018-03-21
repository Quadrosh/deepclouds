<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\B2bDealer */

$this->title = 'Создание дилера';
$this->params['breadcrumbs'][] = ['label' => 'Дилеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-dealer-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
