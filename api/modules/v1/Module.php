<?php
// Check this namespace:
namespace app\api\modules\v1;
 
class Module extends \yii\base\Module
{
	/**
     * {@inheritdoc}
     */
    #public $controllerNamespace = 'app\api\v1\controllers';

    public function init()
    {
        parent::init();
 
        // ...  other initialization code ...
    }
}