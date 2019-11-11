<?php
namespace app\widgets;

use Yii;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 */
class AlertFacebook extends \yii\bootstrap\Widget
{
    public $resourceId  = 3;
    
    public $name_app = 'monitor-facebook';
    
    public $logout = true;

    public $logoutCallback;
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * @var array the options for rendering the close button tag.
     * Array will be passed to [[\yii\bootstrap\Alert::closeButton]].
     */
    public $closeButton = [];


    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // get userId
        $userId = Yii::$app->user->id;
        // if there register with facebook
        $user_facebook = \app\models\CredencialsApi::find()->where([
            'userId' => $userId,
            'resourceId' => $this->resourceId,
            'name_app' => $this->name_app
        ])->one();

        $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
        $link = \app\helpers\FacebookHelper::loginLink();
        $url_link = '<a href="' . $link . '">Log in with Facebook!</a>';

        
        if(!\Yii::$app->user->isGuest){
            if(!$user_facebook->status){
                // is expired
                $message = Yii::t('app','Por favor Inicie sesión con facebook: '.$url_link);
                echo \yii\bootstrap\Alert::widget([
                        'body' => $message,
                        'closeButton' => $this->closeButton,
                        'options' => array_merge($this->options, [
                            'id' => $this->getId(),
                            'class' => $this->alertTypes['warning'],
                        ]),
                ]);
            }

            $is_expired = \app\helpers\FacebookHelper::isExpired($userId);
            if($is_expired){
                $message = Yii::t('app','Su sesión de facebook ha caducado: '.$url_link);
                echo \yii\bootstrap\Alert::widget([
                        'body' => $message,
                        'closeButton' => $this->closeButton,
                        'options' => array_merge($this->options, [
                            'id' => $this->getId(),
                            'class' => $this->alertTypes['info'],
                        ]),
                ]);
            }

            // only test for logout
            if($user_facebook->status && !$is_expired){
                if($this->logout){
                    // is expired
                    $is_expired = \app\helpers\FacebookHelper::isExpired($userId);
                    if(!$is_expired){
                        $link = \yii\helpers\Url::to(['monitor/facebook/logout','userId' => $userId],true);
                        $linkHtml = \yii\helpers\Html::a('logout',$link);
                        $message = Yii::t('app','Logout facebook: '.$linkHtml);
                        echo \yii\bootstrap\Alert::widget([
                                'body' => $message,
                                'closeButton' => $this->closeButton,
                                'options' => array_merge($this->options, [
                                    'id' => $this->getId(),
                                    'class' => $this->alertTypes['success'],
                                ]),
                        ]);
                    }
                }
            }
        }

        
    }
}
