<?php
namespace app\helpers;

use yii;
use yii\db\Expression;

/**
 * AlertMentionsHelper wrapper for table db function.
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */
class AlertMentionsHelper
{
    /**
     * [saveAlertsMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveAlertsMencions($where = [], $properties = []){

        $is_model = \app\models\AlertsMencions::find()->where($where)->exists();
        // if there a record 
        if($is_model){
            $model = \app\models\AlertsMencions::find()->where($where)->one();
            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // if not there a record
        //if(is_null($is_model)){
        if(!$is_model){
            $model = new  \app\models\AlertsMencions();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }
        return ($model->save()) ? $model : false;

    }
    /**
     * [getAlersMentions get the alerts previus mentions call]
     * @return [obj / null] [the objects db query]
     */
    public static function getAlersMentions($properties = []){
        $alertsMencions = \app\models\AlertsMencions::find()->where($properties)->asArray()->all();

        return (!empty($alertsMencions)) ? $alertsMencions : null;
    }

    /**
     * [getResourceNameById get name of resource by id]
     * @param  [int] $resourceName [Ej:1]
     * @return [string]               [nae Twiiter]
     */
    public static function getResourceNameById($resourceId)
    {
        $resourceName = (new \yii\db\Query())
            ->select('name')
            ->from('resources')
            ->where(['id' => $resourceId])
            ->one();
        return $resourceName['name'];
    }

    /**
     * [isAlertsMencionsExists if a mention alert exits]
     * @param  [type]  $publication_id [description]
     * @return boolean                 [description]
     */
    public static function isAlertsMencionsExists($publication_id,$alertId){
        if(\app\models\AlertsMencions::find()->where(['alertId' => $alertId,'publication_id' => $publication_id])->exists()){
            return true;
        }
        return false;
    }


    /**
     * [isAlertsMencionsExists if a mention alert exits by property]
     * @param  [type]  $publication_id [description]
     * @return boolean                 [description]
     */
    public static function isAlertsMencionsExistsByProperties($where){
        if(\app\models\AlertsMencions::find()->where($where)->exists()){
            return true;
        }
        return false;
    }


    /**
     * [getSocialNetworkInteractions return array of social with interation]
     * @param  [type] $resource_name [description]
     * @param  [type] $resource_id   [description]
     * @param  [type] $alertId       [description]
     * @return [type]                [description]
     */
    public static function getSocialNetworkInteractions($resource_name,$resource_id,$alertId)
    {
        
        $model = new \app\models\AlertsMencions();
        $model->alertId = $alertId;
        $model->resourcesId = $resource_id;

        switch ($resource_name) {
            
            case 'Facebook Comments':

                return [$resource_name,$model->shareFaceBookPost,$model->likesFacebookComments,$model->total];
                break;

            case 'Facebook Messages':
                
                $count = self::getCountAlertMentionsByResourceId($model->alertId,$model->resourcesId);
                return [$resource_name,'0','0',$count];
                break;    

            case 'Instagram Comments':
                
                return [$resource_name,'0',$model->likesInstagramPost,$model->total];
                break;
            case 'Twitter':
                $totalProperty = $model->twitterCountProperty;
                array_push($totalProperty,$model->twitterTotal);
                return $totalProperty;
                break;
                
            case 'Live Chat':
                $models = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'resourcesId' => $resource_id])->all();
                $expression = new Expression("`mention_data`->'$.id' AS ticketId");
                $total = 0;

                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('ticketId')
                      ->count();
                    $total += intval($rows);  
                }


                return [$resource_name,'0','0',$total];

                break;

            case 'Live Chat Conversations':
                $models = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'resourcesId' => $resource_id])->all();
                $expression = new \yii\db\Expression("`mention_data`->'$.event_id' AS event_id");
                $total = 0;
                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('event_id')
                      ->count();
                    $total += intval($rows);  
                }

                return [$resource_name,'0','0',$total];

                break;  
            case 'Excel Document':
                return [$resource_name,'0','0',$model->twitterTotal];
                break; 
            case 'Paginas Webs':
                return [$resource_name,'0','0',$model->total];                      
                break;
            case 'Noticias Webs':
                return [$resource_name,'0','0',$model->total];                      
                break;    
            
            default:
                # code...
                return  null;
                break;
        }
    }
	/**
     * [getPostInteractions return post interations by social]
     * @param  [type] $resource_name [description]
     * @param  [type] $resource_id   [description]
     * @param  [type] $alertId       [description]
     * @return [type]                [description]
     */
    public static function getPostInteractions($resource_name,$resource_id,$alertId)
    {
        $model = new \app\models\AlertsMencions();
        $model->alertId = $alertId;
        $model->resourcesId = $resource_id;

        switch ($resource_name) {
            case 'Facebook Comments':
                return $model->topPostFacebookInterations;
                break;
            case 'Instagram Comments':
                return $model->topPostInstagramInterations;
                break;    
            
            default:
                # code...
                break;
        }
    }

    /**
     * [getProductInterations get interations from products]
     * @param  [type] $resourceName       [description]
     * @param  [type] $alerts_mention_ids [description]
     * @param  [type] $alertId            [description]
     * @return [type]                     [description]
     */
    public static function getProductInterations($resourceName,$alerts_mention_ids,$alertId)
    {
        $data = [];
        ini_set('memory_limit', '4G');
        $models = \app\models\AlertsMencions::find()->where(['id' => $alerts_mention_ids,'alertId' => $alertId])->all();
        
        switch ($resourceName) {
            case 'Facebook Comments':
                // contadores
                $shares = 0;
                $likes = 0;
                $total = 0;
                foreach ($models as $model) {
                    $shares += $model->mention_data['shares'];
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        foreach ($model->mentions as $mention) {
                            $likes += $mention->mention_data['like_count'];
                        }
                    }
                }
                // shares
                $data['shares'] = $shares;
                //likes
                $data['likes'] = $likes;
                // total
                $data['total'] = $total;
                return $data;                
                break;
            
            case 'Facebook Messages':
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total ++;
                    }
                }
                // total
                $data['total'] = $total;
                return $data;
                break;
            case 'Instagram Comments':
                $like_post = 0;
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        $like_post += $model->mention_data['like_count'];
                    }
                }
                // like post
                $data['like_post'] = $like_post;
                // total
                $data['total'] = $total;
                return $data; 
            case 'Twitter':
                $likes = 0;
                $retweets = 0;
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        foreach ($model->mentions as $mention) {
                            $likes += $mention->mention_data['favorite_count'];
                            $retweets += $mention->mention_data['retweet_count'];
                        }

                    }
                }
                // count values in document
                $alertsMencions = new \app\models\AlertsMencions();
                $alertMentionsDocuments = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'type' => 'document'])->all();
                foreach ($alertMentionsDocuments as $alertMentionsDocument) {
                    if($alertMentionsDocument->mentionsCount){
                        $total += $alertsMencions->getCountDocumentByResource('TWITTER',$alertMentionsDocument->id);
                    }
                }
                // set
                $data['total'] = $total;
                $data['likes_twitter'] = $likes;
                $data['retweets'] = $retweets;
                return $data;
                break;
            case 'Live Chat':
                $total = 0;
                $expression = new Expression("`mention_data`->'$.id' AS ticketId");
                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('ticketId')
                      ->count();
                    $total += intval($rows);  
                    
                }
                // set
                $data['total'] = $total;
                return $data; 
                break;

            case 'Live Chat Conversations':
                $total = 0;
                //$expression = new \yii\db\Expression("`mention_data`->'$.event_id' AS event_id");
                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      //->select($expression)
                      ->select('social_id')
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('social_id')
                      ->count();
                    $total += intval($rows);  
                }
                // set
                $data['total'] = $total;
                return $data; 

                break;
            case 'Excel Document':
                $total = 0;
                foreach ($models as $model) {
                    if ($model->mentionsCount) {
                        $total += $model->mentionsCount;
                    }
                }
                // set
                $data['total'] = $total;
                return $data; 
                break; 
            case 'Paginas Webs':
                $total = 0;
                foreach ($models as $model) {
                    if ($model->mentionsCount) {
                        $total += $model->mentionsCount;
                    }
                }
                // set
                $data['total'] = $total;
                return $data; 
                break;            
            case 'Noticias Webs':
                $total = 0;
                foreach ($models as $model) {
                    if ($model->mentionsCount) {
                        $total += $model->mentionsCount;
                    }
                }
                // set
                $data['total'] = $total;
                return $data; 
                break;     
            default:
                # code...
                return '1';
                break;
        }
    }

    /**
     * [getProductByTermSearch return model product or nul if not exits]
     * @param  [type] $term_searched [description]
     * @return [obj/ null]                [description]
     */
    public static function getProductByTermSearch($term_searched)
    {
        $model = [];
        $is_family = \app\models\ProductsFamily::find()->where(['name' => $term_searched])->exists();

        if ($is_family) {
            $model = \app\models\ProductsFamily::findOne(['name' => $term_searched]);

        }

        $is_category = \app\models\ProductCategories::find()->where(['name' => $term_searched])->exists();

        if ($is_category) {
            $model = \app\models\ProductCategories::findOne(['name' => $term_searched]);
        }

        $is_product = \app\models\Products::find()->where(['name' => $term_searched])->exists();

        if ($is_product) {
            $model = \app\models\Products::findOne(['name' => $term_searched]);
        }

        $is_model = \app\models\ProductsModels::find()->where(['name' => $term_searched])->exists();

        if($is_model){
            $product_model = \app\models\ProductsModels::findOne(['name' => $term_searched]);
            $model = \app\models\Products::findOne($product_model->productId);
        }

        return $model;

    }
    /**
     * [checkStatusAndFinishAlerts change status in alert if his products is Inactive]
     * @param  [type] $alerts [all alerts running]
     * @return [null]         [description]
     */
    public static function checkStatusAndFinishAlerts($alerts)
    {
        //$models = \yii\helpers\ArrayHelper::map($alerts,'id','config.configSources');
        $models = $alerts;

        foreach ($models as $alertId => $resourceNames) {
           $alert = \app\models\Alerts::findOne($alertId);
            $historySearch = \app\models\HistorySearch::findOne(['alertId' => $alertId]);

            if (!is_null($historySearch)) {
                if (count($resourceNames) == count($historySearch->search_data)) {
                    $status = false;
                    foreach ($historySearch->search_data as $name => $values) {
                        if ($values['status'] == 'Pending') {
                            $status = true;
                            
                        }
                    }
                    if (!$status) {
                        //SELECT COUNT(*) FROM `alerts_mencions` WHERE `condition` != 'INACTIVE' AND `alertId`=1
                        $alertsMencions = \app\models\AlertsMencions::find()
                            ->where(['alertId' => $alertId])
                            ->andWhere(['!=','condition','INACTIVE'])
                            ->count();
                       
                        if (!intval($alertsMencions)) {
                            $alert->status = 0;
                            $alert->save();
                        }   

                    }
                }
            }
        }

    }
    /**
     * [checksSourcesCall check if the alert have resource like facebook if his last call is older than param sleep then call to api]
     * @param  [array] $alerts [all runnig alerts]
     * @return [array] $alerts [all runnig alerts]
     */
    public static function checksSourcesCall($alerts)
    {
        $now = new \DateTime('NOW');
        $minutes_to_call = \Yii::$app->params['facebook']['time_min_sleep']; 
        $minutes_scraping = \Yii::$app->params['scraping']['time_min_sleep']; 
        $hour_news_api   = \Yii::$app->params['newsApi']['time_hours_sleep'];; 


        $sourcesTargest = ['Instagram Comments','Facebook Comments','Facebook Messages','Noticias Webs','Paginas Webs'];
        // loop alerts config
        for ($a=0; $a < sizeof($alerts) ; $a++) { 
            foreach ($alerts[$a]['config']['configSources'] as $resourceName) {
                $index = null;
                if(in_array($resourceName, $sourcesTargest)){
                    $resouces_model = \app\models\Resources::findOne(['name' => $resourceName]);

                    $is_mentions = \app\helpers\AlertMentionsHelper::isAlertsMencionsExistsByProperties([
                        'alertId' => $alerts[$a]['id'],
                        'resourcesId' => $resouces_model->id
                    ]);
                    if ($is_mentions) {
                        $alertMention = \app\models\AlertsMencions::find()->where([
                            'alertId' => $alerts[$a]['id'],
                            'resourcesId' => $resouces_model->id
                        ])->orderBy([
                            'updatedAt' => SORT_DESC
                        ])
                        ->one();
                        // dates logic
                        $fecha = new \DateTime();
                        $updatedAt_diff = $now->diff($fecha->setTimestamp($alertMention->updatedAt));
                       
                        switch ($resourceName) {
                            case 'Noticias Webs':
                                // diff between 8 hours
                                if($updatedAt_diff->h <= $hour_news_api){
                                    $index = array_search($resourceName,$alerts[$a]['config']['configSources']);
                                }
                                break;
                            case 'Paginas Webs':
                                // diff between 10 minutes
                                if($updatedAt_diff->i <= $minutes_scraping){
                                    $index = array_search($resourceName,$alerts[$a]['config']['configSources']);
                                }
                                break;
                            default:
                                if($updatedAt_diff->i <= $minutes_to_call){
                                    $index = array_search($resourceName,$alerts[$a]['config']['configSources']);
                                } // end if diff
                                break;
                        }
                        
                    }// end if mentions

                    // if finish on history search table unset for array
                    $alertId = $alerts[$a]['id'];
                    if(\app\helpers\HistorySearchHelper::checkResourceByStatus($alertId,$resourceName,'Finish')){
                        $index = array_search($resourceName,$alerts[$a]['config']['configSources']);
                    }

                } // end !in_array
                if (!is_null($index)) {
                    unset($alerts[$a]['config']['configSources'][$index]);
                    $alerts[$a]['config']['configSources'] = array_values($alerts[$a]['config']['configSources']);
                }// end if !is_null
            }// end foreach config  config.sources
        } // end loop alerts
        return $alerts;
    }
    /**
     * [orderConfigSources reorder alerts on config resource data]
     * @param  [array] $alerts [all runnig alerts]
     * @return [alertsConfig] $alerts [all runnig alerts]
     */
    public static function orderConfigSources($alerts)
    {
        $alertsConfig = [];
        // loop searching alert with mentions relation and config relation
        for($a = 0; $a < sizeOf($alerts); $a++){
            if((!empty($alerts[$a]['config']))){
                // reduce configSources.alertResource
                for($s = 0; $s < sizeOf($alerts[$a]['config']['configSources']); $s ++){
                    $alertResource = \yii\helpers\ArrayHelper::getValue($alerts[$a]['config']['configSources'][$s], 'alertResource.name');
                    $alerts[$a]['config']['configSources'][$s] = $alertResource;
                } // end for $alerts[$a]['config']['configSources']
                array_push($alertsConfig, $alerts[$a]);
            } // end if not empty
        } // end loop alerts config
        return $alertsConfig;
    }
    /**
     * [setProductsSearch include terms to search]
     * @param  [array] $alertsConfig [all runnig alerts]
     * @return [alertsConfig] $alertsConfig [all runnig alerts]
     */
    public static function setTermsSearch($alertsConfig)
    {
        for($c = 0; $c < sizeOf($alertsConfig); $c++) {
            $terms_search = \app\models\TermsSearch::findAll(['alertId' => $alertsConfig[$c]['id']]);
            if(!empty($terms_search)){
                $alertsConfig[$c]['products'] = [];
                foreach ($terms_search as $term) {
                    array_push($alertsConfig[$c]['products'], $term->name);
                }
            }
        }
        return $alertsConfig;
    }
    /**
     * [getResourceIdByName get id of resource by name]
     * @param  [string] $resourceName [Ej:Twiiter]
     * @return [int]               [id resource]
     */
    public static function getResourceIdByName($resourceName)
    {
        $resourcesId = (new \yii\db\Query())
            ->select('id')
            ->cache(5000)
            ->from('resources')
            ->where(['name' => $resourceName])
            ->all();
        return \yii\helpers\ArrayHelper::getColumn($resourcesId,'id')[0];
    }

    /**
     * [isAlertHaveDictionaries get id of resource by name]
     * @param  [alertID]           [id for alert]
     * @return [boolean] 
     */
    public static function isAlertHaveDictionaries($alertId)
    {
        $is_dictionaries = \app\modules\wordlists\models\AlertsKeywords::find()->where(['alertId' => $alertId])->exists();
        return $is_dictionaries;
    }

    /**
     * [getDictionariesWords get words of the dictionaries]
     * @param  [alertID]           [id for alert]
     * @return [array] 
     */
    public static function getDictionariesWords($alertId){
        $model = \app\models\Alerts::findOne($alertId);
        $words = $model->getKeywords()->select(['name','id'])->asArray()->all();
        return $words;
    }
    /**
     * [getAlertsMentionsIdsByAlertIdAndResourcesIds get alertmentiobns by alertId and his properties]
     * @param  [alertID]           [id for alert]
     * @return [array] 
     */
    public static function getAlertsMentionsIdsByAlertIdAndResourcesIds($alertId,$resourceSocialIds = [])
    {
        $db = \Yii::$app->db;
        $alerMentionsIds = $db->cache(function ($db) use($alertId,$resourceSocialIds){
            $alerMentions = \app\models\AlertsMencions::find()->select('id')->where(['alertId' => $alertId,'resourcesId' => $resourceSocialIds])->asArray()->all();
            $alerMentionsIds = [];
            if(!empty($alerMentions)){
                for ($a=0; $a < sizeOf($alerMentions) ; $a++) { 
                    $alerMentionsIds[] = $alerMentions[$a]['id'];
                }
            }
            return $alerMentionsIds;
        },60);
        return $alerMentionsIds;
    }
    
    /**
     * [setMentionData get and order values json on array]
     * @param  [array] 
     * @return [array] 
     */
    public static function setMentionData($mention_data_array){
        $model= [];
        if(!empty($mention_data_array)){
            for($m=0;$m < sizeOf($mention_data_array); $m++){
                if(!empty($mention_data_array[$m])){
                    for ($d=0; $d < sizeOf($mention_data_array[$m]) ; $d++) { 
                        $tmp = json_decode($mention_data_array[$m][$d]['mention_data'],true);
                        foreach($tmp as $property => $value){
                            if(isset($model[$property])){
                                $model[$property] += $value; 
                            }else{
                                $model[$property] = $value;
                            }
                        }
                    }
                }
            }
        }
        return $model;
    }
    /**
     * [isAlertsMencionsExists if a mention alert exits by resource]
     * @param  int  $alertId       [id of alert]
     * @param  int  $resourceId       [id of resource]
     * @return int                 [total count Alert Mentions Table]
     */
    public static function getCountAlertMentionsByResourceId($alertId,$resourceId){
        $db = \Yii::$app->db;
        $count = $db->cache(function ($db) use($alertId,$resourceId){
            return (new \yii\db\Query())
                ->from('alerts_mencions')
                ->where(['alertId' => $alertId,'resourcesId' => $resourceId])
                ->count();
        },60);
        return $count;
    }

    public static function getCountMentionsByresourceId($alertId,$resourceId){
        $db = \Yii::$app->db;
        $models = $db->cache(function ($db) use($alertId,$resourceId){
            return \app\models\AlertsMencions::find()->with('mentions')->where(['alertId' => $alertId,'resourcesId' => $resourceId])->all();
        },60);
        $count = 0;
        foreach($models as $model){
            if(count($model->mentions)){
                $count++;
            }
        }
        return $count;
    }

    /**	 
	* [getAttributesForDetailView compose detailView array if there url on topic]
	* @param  [obj] $model [topic  model]
	* @return [array]              [arraty detailView]
	*/
	public static function getAttributesForDetailView($model)
	{
		$url_detail_arr = [];
		if ($model->config->urls != '') {
            $urls = explode(",",$model->config->urls);
			$url_detail_arr = [
				'label' => Yii::t('app','Scraping Paginas Web Urls'),
                'format'    => 'raw',
                //'attribute' => 'resourceId',
                'value' => function() use($urls) {
                    $html = '';
                    foreach ($urls as $index => $url) {
                        $html .= " <span class='label label-success'><a style='color: white;' href='{$url}' target='_blank'>{$url}</a></span>";
                    }
                    return $html;
                }

			];
		}

		$detail_attributes = [
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            /*[
                'label' => Yii::t('app','Usuario'),
                'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return $model->user->username;
                }
            ],*/
            [
                'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                  return $model->name;
                }
            ],
            
            [
                'label' => Yii::t('app','Recursos Sociales'),
                'format'    => 'raw',
                'attribute' => 'alertResourceId',
                'value' => function($model) {
                    $html = '';
                    foreach ($model->config->configSources as $alert) {
                        $url = \yii\helpers\Url::to(['/monitor/detail','id' => $model->id,'resourceId' => $alert->alertResource->id]);
                        $span = "<span class='label label-info'>{$alert->alertResource->name}<status-alert id={$alert->alertResource->id} :resourceids={$alert->alertResource->id}></status-alert></span>";
                        $hiperLink =  \yii\helpers\Html::a($span,$url,['target'=>'_blank', 'data-pjax'=>"0",'id' => $alert->alertResource->name]);
                        $html .= $hiperLink;
                    }
                    return $html;
                },

            ],
            [
                'label' => Yii::t('app','Terminos a Buscar'),
                'format'    => 'raw',
                //'attribute' => 'alertResourceId',
                'value' => \kartik\select2\Select2::widget([
                    'name' => 'products',
                    'size' => \kartik\select2\Select2::SMALL,
                    'hideSearch' => false,
                    'data' => $model->products,
                    'options' => ['placeholder' => 'Terminos...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),

            ],
            
            'config.start_date:datetime',
            'config.end_date:datetime',
        ];

        if (!empty($url_detail_arr)) {
        	array_push($detail_attributes, $url_detail_arr);
        }

        return $detail_attributes;
    }

    public static function print_mem(){
        /* Currently used memory */
        $mem_usage = memory_get_usage();
        
        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();

        echo 'The script is now using: ' . round($mem_usage / 1024) . "KB of memory.\n";
        echo 'Peak usage: ' . round($mem_peak / 1024) . "KB of memory.\n";
    }

}