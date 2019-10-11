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
        $link = \app\helpers\FacebookHelper::login();
        $ulr_link = '<a href="' . $link . '">Log in with Facebook!</a>';

        
        if(!\Yii::$app->user->isGuest){
            if($user_facebook){
                // is expired
                $is_expired = \app\helpers\FacebookHelper::isExpired($userId);
                if($is_expired){
                    $message = Yii::t('app','Su sesión de facebook ha caducado: '.$ulr_link);
                    echo \yii\bootstrap\Alert::widget([
                            'body' => $message,
                            'closeButton' => $this->closeButton,
                            'options' => array_merge($this->options, [
                                'id' => $this->getId(),
                                'class' => $this->alertTypes['info'],
                            ]),
                    ]);
                }
                
            }else{
                $message = Yii::t('app','Por favor Inicie sesión con facebook: '.$ulr_link);
                echo \yii\bootstrap\Alert::widget([
                        'body' => $message,
                        'closeButton' => $this->closeButton,
                        'options' => array_merge($this->options, [
                            'id' => $this->getId(),
                            'class' => $this->alertTypes['warning'],
                        ]),
                ]);
            }
        }

        // only test for logout
        /*if($this->logout && $user_facebook){
            // is expired
            $is_expired = \app\helpers\FacebookHelper::isExpired($userId);
            if(!$is_expired){
                $logout_link = yii\helpers\Html::a('logout',yii\helpers\Url::to([
                    'monitor/facebook/logout',
                    'credencials_api_id' => $user_facebook->id,
                    'next' => $this->logoutCallback
                ]));
                $message = Yii::t('app','Solo test puede salir de la session de facebook: '.$logout_link);
                echo \yii\bootstrap\Alert::widget([
                        'body' => $message,
                        'closeButton' => $this->closeButton,
                        'options' => array_merge($this->options, [
                            'id' => $this->getId(),
                            'class' => $this->alertTypes['success'],
                        ]),
                ]);
            }
        }*/
    }
}
