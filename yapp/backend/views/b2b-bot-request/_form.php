<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\B2bBotRequest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="b2b-bot-request-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'update_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'request')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'answer')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'user_time')->textInput() ?>

    <?= $form->field($model, 'request_time')->textInput() ?>

    <?= $form->field($model, 'answer_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
