<?php
namespace app\helpers;

use yii;
use yii\helpers\Url;

use app\models\CredencialsApi;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FacebookHelper wrapper for file function facebook.
 *
 */
class FacebookHelper
{
	private static $_resource_id = 3;
	public static $_app_id = '';
	private static $_name_app = 'monitor-facebook';
	private static $_app_secret = '';


	/**
     * return facebook object.
     * @return facebook object
     */
	public static function getFacebook(){
		$fb = new \Facebook\Facebook([
		  'app_id' => self::$_app_id,
		  'app_secret' => self::$_app_secret,
		  'default_graph_version' => 'v3.2',
		  // 'default_access_token' => '' // https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens
		]);

		return $fb;
	}
	/**
     * return login link for facebook.
     * @return string link
     */
	public static function loginLink(){
		$fb = self::getFacebook();
		$helper = $fb->getRedirectLoginHelper();
		// Optional permissions
		$permissions = ['manage_pages','pages_show_list','read_page_mailboxes','ads_management','pages_messaging','instagram_basic']; 
		// crea una URL absoluta: http://www.example.com/index.php?r=post/index
		//$url = Url::to('monitor/facebook/validate-fb');
		$url = 'http://localhost/monitor-beta/web/monitor/facebook/validate-fb';

		$loginUrl = $helper->getLoginUrl($url, $permissions);

		return htmlspecialchars($loginUrl);
	}
	/**
     * return logout link.
     * @param string $access_secret_token
     * @param string $next
     * @return string link
     */
	public static function logoutLink($user_facebook){
		
		$fb = self::getFacebook();
		$helper = $fb->getRedirectLoginHelper();

		// crea una URL absoluta: http://www.example.com/index.php?r=post/index
		//$next = Url::to(['default/site/index'], true);
		$next = 'http://localhost/monitor-beta/web/site/index';

		$logoutUrl = $helper->getLogoutUrl($user_facebook->access_secret_token, $next);
		return htmlspecialchars($logoutUrl);
	}
	/**
     * save access token.
     * @param int $userId
     * @param string $access_secret_token
     * @return boolean 
     */
	public static function saveAccessToken($userId,$access_secret_token){

		$record_exists = CredencialsApi::find() ->where( [ 
						'userId' => $userId, 
						'resourceId' => self::$_resource_id,
						'name_app' => self::$_name_app,
						 ] )->exists(); 

		if($record_exists){
			$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => self::$_name_app,
			] )->one();
			
			$userCredential->userId = $userId;
			$userCredential->resourceId = self::$_resource_id;
			$userCredential->access_secret_token = $access_secret_token;
		}

		if(!$record_exists){
			$userCredential = new CredencialsApi();
			$userCredential->name_app = self::$_name_app;
			$userCredential->userId = $userId;
			$userCredential->resourceId = self::$_resource_id;
			$userCredential->access_secret_token = $access_secret_token;
		}
		
		
		return ($userCredential->save());
	}
	/**
     * save access token.
     * @param int $userId
     * @param string $expiresAt
     * @return boolean 
     */
	public static function saveExpiresAt($userId,$expiresAt){
		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => CredencialsApi::FACEBOOK,
				'name_app' => CredencialsApi::NAME_APP_FACEBOOK,
			] )->one();

		$userCredential->expiration_date = $expiresAt;
		$userCredential->status = 1;
		
		return ($userCredential->save());
	}
	/**
     * if long live acces secret .
     * @param int $userId
     * @return boolean 
     */
	public static function isLongLived($userId){
		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => self::$_name_app,
			] )->one();
		if (isset($userCredential->expiration_date)) {
            return $userCredential->expiration_date > time() + (60 * 60 * 2);
        }
        return null;
	}
	/**
     * if is Expired acces secret .
     * @param int $userId
     * @return boolean 
     */
	public static function isExpired($userId){

		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => self::$_name_app,
			] )->one();
		if (isset($userCredential->expiration_date)) {
            return $userCredential->expiration_date < time();
        }
        return null;
	}


	public static function getCredencials($userId){

		$userCredential = CredencialsApi::find() ->where( [ 
				'userId' => $userId, 
				'resourceId' => self::$_resource_id,
				'name_app' => self::$_name_app,
			] )->one();

		return ($userCredential) ? $userCredential : null;
			

	}
}