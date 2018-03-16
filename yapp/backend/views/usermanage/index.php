<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Admins';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Admin', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
//            'role',
            [
                'attribute'=>'role',
                'value' => function($data)
                {
                    $theData = common\models\RolesAssignment::find()->where(['user_id'=>$data['id']])->one();
                    return $theData['item_name'];
                },
            ],
//            'auth_key',
//            'password_hash',
//            'password_reset_token',
             'email:email',
             'status',
            [
                'attribute'=>'created_at',
                'value'=> function($data){
                    return  \Yii::$app->formatter->asDatetime($data->created_at, "php:d-m-Y H:i:s");
                }
            ],

            [
                'attribute'=>'updated_at',
                'value'=> function($data){
                    return  \Yii::$app->formatter->asDatetime($data->updated_at, "php:d-m-Y H:i:s");
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
