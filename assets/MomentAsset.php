<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MomentAsset extends AssetBundle
{
    public $sourcePath = '@bower/moment/min';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'moment.min.js':'moment.min.js';
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 

    public $publishOptions = [
      //  'forceCopy' => true,
         //you can also make it work only in debug mode: 'forceCopy' => YII_DEBUG
    ];
}