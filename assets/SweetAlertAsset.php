<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SweetAlertAsset extends AssetBundle
{
    public $sourcePath = '@npm/sweetalert2/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        YII_ENV_DEV ? 'sweetalert2.js' : 'sweetalert2.all.min.js',
    ];

    public $css = [
       YII_ENV_DEV ? 'sweetalert2.css' : 'sweetalert2.min.css',
       
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ]; 
}
