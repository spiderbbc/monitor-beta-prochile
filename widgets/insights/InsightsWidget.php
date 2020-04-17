<?php
namespace app\widgets\insights;

use Yii;
use yii\helpers\Html;

/**
 * InsightsWidget widget renders a list Insights Page - Post - Strorys (Facebook - Instagram)
 *
 * ```php
 *  echo app\widgets\insights\InsightsWidget::widget();
 *  or 
 *  <?= app\widgets\insights\InsightsWidget::widget() ?>
 * ```
 *
 */
class InsightsWidget extends \yii\bootstrap\Widget
{
	
    public $userId;
    public $userCredencial = [];


    public function init()
    {
        parent::init();
        $this->userId = Yii::$app->user->id;
        $this->userCredencial = \app\helpers\FacebookHelper::getCredencials($this->userId);
        
    }

	public function run()
	{
        $link = \app\helpers\FacebookHelper::loginLink();
        $url_link = "<a href='{$link}'>Log in with Facebook!</a>";

        if (!\Yii::$app->user->isGuest) {
            if (is_null($this->userCredencial)) {
                $message = Yii::t('app','Por favor Inicie sesión con facebook: '.$url_link);
                $class   = 'alert-info';
                return $this->render('alert',['message' => $message,'class' => $class]);
            }else{

                if (!$this->userCredencial->status) {
                    $message = Yii::t('app','Parece que estas deslogueado: '.$url_link);
                    $class   = 'alert-warning';
                    return $this->render('alert',['message' => $message,'class' => $class]);
                }

                $is_expired = \app\helpers\FacebookHelper::isExpired($this->userId);
                if ($is_expired) {
                    $message = Yii::t('app','Su sesión de facebook ha caducado: '.$url_link);
                    $class   = 'alert-warning';
                    return $this->render('alert',['message' => $message,'class' => $class]);

                }
            }
        }
        


        return $this->render('dashboard');
	}
}