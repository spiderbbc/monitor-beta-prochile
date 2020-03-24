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
	private $resourcesName = 'Paginas Webs';
	private $alertId;
	private $resourcesId;
	private $start_date;
	private $end_date;
	private $urls;
	private $data;

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
			$products   = $alert['products'];
			// set if search finish
			//$this->searchFinish();
			
			
			$this->urls = $this->_setUrls($alert['config']['urls']);

		}
		return false;
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
		$client = new GuzzleClient();
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
						$urls[$url]['domain'] = $domain;
						$urls[$url]['links'] = $all_links;
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
	
	function __construct(){
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourcesName);
		// call the parent __construct
		parent::__construct();
	}
}