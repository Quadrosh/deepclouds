<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

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
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Deepclouds',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        Yii::$app->user->can('creatorPermission', [])
            ? (['label' => 'Admins', 'url' => ['/usermanage']])
            : (['label' => false]),



        [
            'label' => 'БОТ Development',
            'items' => [
                ['label' => 'b2bDealer', 'url' => ['/b2b-dealer']],
                ['label' => 'b2bBotUser', 'url' => ['/b2b-bot-user']],
                ['label' => 'b2bBotRequest', 'url' => ['/b2b-bot-request']],
            ],
        ],
//        [
//            'label' => 'Библиотека',
//            'items' => [
//                ['label' => 'Article page', 'url' => ['/article/index']],
//                ['label' => 'Article section', 'url' => ['/articlesection/index']],
//                ['label' => 'Happiness page', 'url' => ['/happypage/index']],
//                ['label' => 'Happiness section', 'url' => ['/happysection/index']],
//                ['label' => 'Index Pages', 'url' => ['/page/index']],
//                ['label' => 'Категории', 'url' => ['/category/index']],
//                ['label' => 'Quotepad', 'url' => ['/quotepad/index']],
//                ['label' => 'Quotepad Images', 'url' => ['/quotepadimg/index']],
//                ['label' => 'Картинки', 'url' => ['/imagefile/index']],
//                ['label' => 'Заявки', 'url' => ['/feedback/index']],
//
//            ],
//        ],


    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
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

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Deepclouds <?= date('Y') ?></p>

<!--        <p class="pull-right">Круто же)</p>-->
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
