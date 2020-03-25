<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;


/**
 * class wrapper to web scraping
 */
class Scraping extends Model
{
	private $resourceName = 'Paginas Webs';
	private $alertId;
	private $resourcesId;
	private $terms;
	private $start_date;
	private $end_date;
	private $urls;
	private $data;

	const TYPE_MENTIONS = 'web';
	/**
     * [rules for scrapping a webpage]
     * @return [array] [title => rule xpath]
     */
    public function rules()
    {
        return [
            '//div'         => Yii::t('app','div'),
            '//title'       => Yii::t('app','document_title'),
            '//h1'          => Yii::t('app','cabezera_1'),
            '//h2'          => Yii::t('app','cabezera_2'),
            '//h3'          => Yii::t('app','cabezera_3'),
            '//h4'          => Yii::t('app','cabezera_4'),
            '//h5'          => Yii::t('app','cabezera_5'),
            '//strong'      => Yii::t('app','negrita'),
            '//a'           => Yii::t('app','link'),
            '//b'           => Yii::t('app','negrita'),
            '//span'        => Yii::t('app','contenedor'),
            '//li'          => Yii::t('app','ítem'),
            '//address'     => Yii::t('app','address'),
            '//div/article' => Yii::t('app','cabezera_2'),
            '//aside'       => Yii::t('app','aside'),
            '//hgroup'      => Yii::t('app','hgroup'),
            '//p'           => Yii::t('app','paragraph'),
            '//footer/div'  => Yii::t('app','footer'),
        ];
    }

	public function prepare($alert)
	{
		if(!empty($alert)){
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->terms   = $alert['products'];
			// set if search finish
			//$this->searchFinish();
			
			
			$this->urls = $this->_setUrls($alert['config']['urls']);

		}
	}
	/**
	 * [_setUrls get http request to url and extract all links with the same domain the url]
	 * @param  [string] $urls_string [string urls on string]
	 * @return [array]  $urls [set url when url + domain + links]
	 */
	private function _setUrls($urls_string)
	{
		$valid_urls = \app\helpers\StringHelper::validUrlFromString($urls_string);
		$urls = [];

		// Initialize the client with the handler option
		$client = new GuzzleClient();
		//$client = new GuzzleClient();
		// loop on urls
		foreach ($valid_urls as $url) {
			$domain = \app\helpers\StringHelper::getDomain($url);
			try {
				
				$request = new \GuzzleHttp\Psr7\Request('get', $url);
				$response = $client->send($request, ['timeout' => 10]);
				
				$code = $response->getStatusCode();
				$reason = $response->getReasonPhrase(); // OK
				
				if ($code == 200 && $reason == 'OK') {

					$body = $response->getBody()->getContents();
					// call crallwer
					$crawler = new Crawler($body,$url);
					$links_count = $crawler->filter('a')->count();
					if ($links_count > 0) {
						$links = $crawler->filter('a')->links();
						$all_links = [];
						foreach ($links as $link) {
						    $link_web = $link->getURI();
						    $link_same_domain = \app\helpers\StringHelper::getDomain($link_web);
						    if($domain == $link_same_domain){
						      $all_links[] = $link_web;  
						    }
						    
						} // for each links
						
						
						// put original url
						array_push($all_links, $url);
						$all_links = array_unique($all_links);
						// reorder array
						$links_order = array_values($all_links);
						$urls[$url]['domain'] = $domain;
						$urls[$url]['links'] = $links_order;
					}
				}
			} catch (\GuzzleHttp\Exception\RequestException $e) {
				// send email
				//echo "TooManyRedirectsException: ".$e->getMessage();
				continue;
			}
		}
		return $urls;
	}

	/**
	 * [getRequest get url and send request to transfrom in crawler instance]
	 * @return [array] [crawlers instaces]
	 */
	public function getRequest()
	{
		$client = new Client();
		$crawler = [];
 		
 		if (!empty($this->urls)) {
			foreach ($this->urls as $url => $values) {
				if (!empty($values['links'])) {
					for ($l=0; $l < sizeof($values['links']) ; $l++) { 
						$link = $values['links'][$l];
						$response = $client->request('GET',$link);
						$status_code = $client->getResponse()->getStatus();
						if ($status_code == 200) {
							$domain = $values['domain'];
							if($domain){
								$content_type = $client->getResponse()->getHeader('Content-Type');
				                if (strpos($content_type, 'text/html') !== false) {
				                    $crawler[$url][$link][] = $response;
				                }
							}// if domain
						}// end if status code 
					}// end loop for links
				}// end if empty
			}// end loop foreach
		}// end if empty
		return $crawler;
	}
	/**
	 * [getContent loop on each link to filter for his crawler]
	 * @param  [array] $crawlers [url and his links with each craw]
	 * @return [array] $contents [each link with content by rules]
	 */
	public function getContent($crawlers)
	{
		$contents = [];
        
        foreach ($crawlers as $url => $links) {
        	foreach ($links as $link => $crawler) {
        		for ($c =0; $c  < sizeof($crawler) ; $c ++) { 
        			foreach ($this->rules() as $rule => $title){
        				$contents[$url][$link][] = $crawler[$c]->filterXpath($rule)->each(function ($node,$i)
	                    {
	                    	$text = $node->text();
	                    	if (!\app\helpers\StringHelper::isEmpty($text)) {
	                    		$text = \app\helpers\StringHelper::collapseWhitespace($text);
	                    		return [
		                            'id' => $node->extract(['id'])[0],
		                            '_text' => trim($node->text()),
		                        ];
	                    	}
	                    	//return null;
	                    });
        			}// end loop rules
        		}// end loop crawler
        	}// end loop links
        }// end loop crawlers
      	return $contents;
	}
	/**
	 * [setContent reoder array on each data for url]
	 * @param [array] $contents [content for each url]
	 */
	public function setContent($contents)
	{
		$data = [];
		foreach ($contents as $url => $values) {
			$data[$url] = [];
			foreach ($values as $link => $nodes) {
				$data[$url][$link] = [];
				for ($n=0; $n < sizeof($nodes); $n++) { 
					if (!empty($nodes[$n])) {
						for ($s=0; $s < sizeof($nodes[$n]) ; $s++) { 
							if (!is_null($nodes[$n][$s])) {
								$text = $nodes[$n][$s]['_text'];
								if (!in_array($text, $data[$url][$link])) {
									$data[$url][$link][] = $text;
								}
							}
						}
					}
				}
			}// end loop values
		}// end loop contents
		return $data;
	}
	/**
	 * [searchTermsInContent search terms in the content extract in the web pages]
	 * @param  [array] $data  [array data with his links and content]
	 * @return [array] $model [array with the sentences order by terms]
	 */
	public function searchTermsInContent($data)
	{
		$model = [];
		$terms = $this->terms;
		/*var_dump($data);
		die();*/

		if (!empty($data)) {
			foreach ($data as $url => $values) {
				//echo $url."\n";
				foreach ($values as $link => $nodes) {
					//echo $link."\n";
					for ($n=0; $n < sizeof($nodes) ; $n++) { 
						//echo $nodes[$n]."\n";
						$sentence = $nodes[$n];
						for ($t=0; $t <sizeof($terms) ; $t++) { 

							$isContains = \app\helpers\StringHelper::containsCountIncaseSensitive($sentence,$terms[$t]);
							if ($isContains) {
								if (!ArrayHelper::keyExists($terms[$t], $model, false)) {
									$model[$terms[$t]] = [];
								}

								$register = [
									'source' => [
										'name' => \app\helpers\StringHelper::getDomain($link)
									],
									'url' => $link,
									'content' => $sentence,
									'message_markup' => $sentence
								];
								//$model[$terms[$t]][] = $register;
								if (!in_array($register, $model[$terms[$t]])) {
									$model[$terms[$t]][] = $register;
								}
							}
						}
					}// end loop nodes
				}// end loop values
			}// end loop end data
		}// end if emty data
		return $model;
	}

	public function saveTermsMentions($model)
	{
		$terms = array_keys($model);
		$properties = [
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'date_searched' => \app\helpers\DateHelper::getToday(),
			'type'          => self::TYPE_MENTIONS,
		];
		if (!empty($terms)) {
			for ($t=0; $t < sizeof($terms) ; $t++) { 
				$properties['term_searched'] = $terms[$t];
				$this->_saveAlertsMencions($properties);
			}
		}
		$this->data = $model;
	}

	/**
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model =  \app\models\AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => self::TYPE_MENTIONS,
			'term_searched' => $properties['term_searched']
		])
		->one();

		if(is_null($model)){
			$model = new \app\models\AlertsMencions();
			$model->alertId = $this->alertId;
			$model->resourcesId = $this->resourcesId;
		}
		foreach($properties as $property => $values){
    		$model->$property = $values;
    	}
    	if(!$model->save()){
    		var_dump($model->errors);
    	}

	}

	/**
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile(){

		if(!empty($this->data)){
			$jsonfile = new \app\models\file\JsonFile($this->alertId,$this->resourceName);
			$jsonfile->load($this->data);
			$jsonfile->save();
		}

	}
	
	function __construct(){
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourceName);
		// call the parent __construct
		parent::__construct();
	}
}