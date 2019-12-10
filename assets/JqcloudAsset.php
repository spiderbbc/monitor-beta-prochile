<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JqcloudAsset extends AssetBundle
{
    public $sourcePath = '@bower/jqcloud2/dist';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'jqcloud.js':'jqcloud.min.js';
        $this->css[] = YII_ENV_DEV ? 'jqcloud.css':'jqcloud.min.css';
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
}
