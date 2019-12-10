<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DataTableAsset extends AssetBundle
{
    public $sourcePath = '@npm/datatables/media';

    public function init()
    {
        parent::init();
        $this->js[] = YII_ENV_DEV ? 'js/jquery.dataTables.js':'js/jquery.dataTables.min.js';
    }
    public $css = [
        'css/dataTables.jqueryui.css',
        'css/jquery.dataTables.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]; 
    //public $baseUrl = '@web';
    /*public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];*/
}
