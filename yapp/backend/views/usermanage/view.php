<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use \common\models\RolesAssignment;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Admins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'auth_key',
            'password_hash',
            'password_reset_token',
            'email:email',
            'status',
//            'role',
            [
                'attribute'=>'role',
                'value' => function($data)
                {
                    $theData = \common\models\RolesAssignment::find()->where(['user_id'=>$data['id']])->one();
                    return $theData['item_name'];
                },
            ],
//            'created_at',
            [
                'attribute'=>'created_at',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['created_at'], 'dd/MM/yy HH:mm');
                },
            ],
//            'updated_at',
            [
                'attribute'=>'updated_at',
                'value' => function($data)
                {
                    return \Yii::$app->formatter->asDatetime($data['updated_at'], 'dd/MM/yy HH:mm');
                },
            ],
        ],
    ]) ?>

</div>
<!--    назначение роли -->
<div class="row mt20 bt pt20">
    <div class="col-xs-6 col-xs-offset-3 ">
        <h4>Роль</h4>
        <?php $form = yii\bootstrap\ActiveForm::begin([
            'method' => 'post',
            'action' => ['/usermanage/assign?id='.$model['id']],
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{beginWrapper}\n{input}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'wrapper' => 'col-sm-12',
                    'error' => '',
                    'hint' => 'статус',
                ],
            ],
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <?= $form->field($roleAssign, 'item_name',[
            'inputTemplate' => '<div class="input-group"><span class="lRound">{input}</span><span class="input-group-btn">'.
                '<button type="submit" class="btn rRound btn-primary">Назначить <i class="fa fa-share" aria-hidden="true"></i></button></span></div>',
        ])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\Roles::find()->where(['type'=>1])->orderBy('name')->all(), 'name','name')) ?>
        <?= $form->field($roleAssign, 'user_id')->hiddenInput(['value'=>$model['id']])->label(false) ?>
        <?php yii\bootstrap\ActiveForm::end() ?>
    </div>
</div>
<!--   / назначение роли -->

<!--    назначение пароля -->
<div class="row mt20 bt pt20">
    <div class="col-xs-6 col-xs-offset-3 ">
        <h4>Пароль</h4>

        <?php $form = yii\bootstrap\ActiveForm::begin([
            'method' => 'post',
            'action' => ['/usermanage/set-pass?id='.$model['id']],
            'id' => 'setpass',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{beginWrapper}\n{input}\n{error}\n{endWrapper}",
                'horizontalCssClasses' => [
                    'wrapper' => 'col-sm-12 col-sm-offset-0',
                    'error' => '',
                    'hint' => 'статус',
                ],
            ],
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <?= $form->field($model, 'password_hash',[
            'inputTemplate' => '<div class="input-group"><span class="lRound">{input}</span><span class="input-group-btn">'.
                '<button type="submit" class="btn rRound btn-primary">Изменить <i class="fa fa-share" aria-hidden="true"></i></button></span></div>',
        ])->textInput(['value'=>''])->label(false) ?>
        <?php yii\bootstrap\ActiveForm::end() ?>
    </div>
</div>
<!--   / назначение пароля -->
