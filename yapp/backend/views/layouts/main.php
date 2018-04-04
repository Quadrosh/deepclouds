<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

$currentMenuItem = Yii::$app->params['currentMenuItem'];

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="/img/favicon/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/img/favicon/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/favicon/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/favicon/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="256x256" href="/img/favicon/apple-touch-icon-256x256.png">
</head>
<body >
<?php $this->beginBody() ?>

<div class="wrap fullheight">

    <div id="newHamburgerMenu" class="newHamburger hide_more768">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="container fullheight">


            <div class=" row  fullheight ">
                <div id="sideMenu" class="col-sm-3 hidde_less767 iconMenu ">
                    <?= Html::img('/att_logo.png',['class'=>'navbar_brand']) ?>
                    <h4 class="text-center navbar_brand_lead">B2B bot</h4>
                    <div >
                            <?= common\widgets\SidemenuWidget::widget([
                                'site'=>Yii::$app->params['site'],
                                'formfactor'=>'accordion',
                                'currentItem'=> $currentMenuItem
                            ]); ?>
                        <div id="sideMenuCloseButton" class="closeButton hide_more768">X</div>
                    </div>

                </div>
                <div class="col-sm-offset-3 col-sm-9 col-xs-12 nopadding">
                    <?php
                    NavBar::begin([
                        'options' => [
                            'class' => 'navbar-inverse ',
                        ],
                    ]);
                    $menuItems = [
                        Yii::$app->user->can('creatorPermission', [])
                            ? (
                        [
                            'label' => 'Управление',
                            'items' => [
                                ['label' => 'Администраторы', 'url' => ['/usermanage']],
                                ['label' => 'settings', 'url' => ['/bot-settings']],
                                ['label' => 'job counter', 'url' => ['/job-counter']],
                                ['label' => 'job counter status', 'url' => ['/job-counter-stat']],
                            ]
                        ]
                        )
                            : (['label' => false]),
                    ];
                    if (Yii::$app->user->isGuest) {
                        $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
                    } else {
                        $menuItems[] = '<li>'
                            . Html::beginForm(['/site/logout'], 'post')
                            . Html::submitButton(
                                'Выйти (' . Yii::$app->user->identity->username . ')',
                                ['class' => 'btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>';
                    }
                    echo Nav::widget([
                        'options' => ['class' => 'navbar-nav navbar-right'],
                        'items' => $menuItems,
                    ]);
                    NavBar::end();
                    ?>
                </div>
                <div class="col-sm-offset-3 col-sm-9 col-xs-12 workArea ">



                    <?= Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>

                    <?= Alert::widget() ?>
                    <?= $content ?>


<!--                    <footer class="footer">-->
<!--                        <div class="container">-->
<!--                            <p class="pull-left">&copy; Deepclouds --><?//= date('Y') ?><!--</p>-->
<!---->
<!---->
<!--                        </div>-->
<!--                    </footer>-->

                </div>
                <div id="push"></div>
            </div>

    </div>
</div>














<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
