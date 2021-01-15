<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

use app\widgets\AlertFacebook;
$condition = false;
if(isset(Yii::$app->user->identity->username)){
    $usernames = ['admin','mauro'];
    $condition = (in_array(Yii::$app->user->identity->username,$usernames))? true : false;
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to(['/favicon.ico'])]); ?> 
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-inverse fixed-top',
        ],
    ]);
    $menuItems = [];
    if(Yii::$app->user->isGuest){
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    }else{
        $menuItems = [
            ['label' => 'Logs', 'url' => ['/user/logs'],'visible' => $condition], 
            //  ['label' => 'Menciones', 'url' => ['/topic/']],
            [
                'label' => 'Monitor',
                'items' => [
                    ['label' => '<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Alertas', 'url' => ['/monitor/alert/index']],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Configuraciones</li>',
                        ['label' => '<span class="glyphicon glyphicon-book" aria-hidden="true"></span> Crear Diccionarios', 'url' => ['/wordlists/']],
                ],
            ],
            [
                'label' => 'User',
                'items' => [
                    ['label' => '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> Perfil', 'url' => ['/user/default/edit']],
                    [
                        'label' => '<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout (' . Yii::$app->user->identity->username . ')',
                        'url' => ['/site/logout'],
                        'linkOptions' => ['data-method' => 'post']
                    ]
                ],
            ]
            
        ];
    }
    echo Nav::widget([
        'encodeLabels' => false,
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'homeLink' => false
        ]) ?>
        <?= Alert::widget() ?>
        
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Social Media Trends <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
