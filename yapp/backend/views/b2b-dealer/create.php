<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\B2bDealer */

$this->title = 'Create B2b Dealer';
$this->params['breadcrumbs'][] = ['label' => 'B2b Dealers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="b2b-dealer-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
