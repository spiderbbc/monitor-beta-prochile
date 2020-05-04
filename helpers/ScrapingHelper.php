<?php
namespace app\helpers;

use yii;

/**
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * ScrapingHelper wrapper for scraping function.
 *
 */
class ScrapingHelper{

	/**
     * [rules for scrapping a webpage]
     * @return [array] [title => rule xpath]
     */
    public static function rules()
    {
        return [
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
            '//ul//li'      => Yii::t('app','ítem'),
            '//address'     => Yii::t('app','address'),
            '//aside'       => Yii::t('app','aside'),
            '//hgroup'      => Yii::t('app','hgroup'),
            '//p'           => Yii::t('app','paragraph'),
        ];
    }

	/**
	 * [getLinksInUrlsWebPage get all sub links on same domain]
	 * @param  array  $valid_urls [urls valids]
	 * @return [array]             [all the sublinks by each url]
	 * $urls = [
			'https://www.forbes.com'=>[
				'domain' => 'chron.com',
				'links'  => [
					'https://www.chron.com'
				],
			]
		];
	 */
	public static function getLinksInUrlsWebPage($valid_urls = [])
	{
		$urls = [];
		// Initialize the client with the handler option
		$client = new \GuzzleHttp\Client();
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
					$crawler = new \Symfony\Component\DomCrawler\Crawler($body,$url);
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
						if (!in_array($url, $all_links)) {
							array_push($all_links, $url);
						}
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
	 * [getContent loop on each link to filter for his crawler]
	 * @param  [array] $crawlers [url and his links with each craw]
	 * @return [array] $contents [each link with content by rules]
	 */
	public static function getContent($crawlers)
	{
		$contents = [];
        
        foreach ($crawlers as $url => $links) {
        	foreach ($links as $link => $crawler) {
        		for ($c =0; $c  < sizeof($crawler) ; $c ++) { 
        			foreach (self::rules() as $rule => $title){
        				$contents[$url][$link][] = $crawler[$c]->filterXpath($rule)->each(function ($node,$i) use ($rule)
	                    {
	                    	$text = $node->text();
	                    	if (!\app\helpers\StringHelper::isEmpty($text)) {
	                    		$text_without_spaces = \app\helpers\StringHelper::collapseWhitespace($text);
	                    		//echo $rule."\n";
	                    		return [
		                           // 'id' => $node->extract(['id'])[0],
		                            '_text' => trim($text_without_spaces),
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
	public static function setContent($contents)
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
}


?>