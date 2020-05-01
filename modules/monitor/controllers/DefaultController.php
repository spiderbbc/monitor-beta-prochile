<?php

namespace app\modules\monitor\controllers;

use yii\web\Controller;



/**
 * Default controller for the `monitor` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        $client = new \GuzzleHttp\Client();
        $text = "The richest people on Earth are not immune to the coronavirus. As the pandemic tightened its grip on Europe and America, global equity markets imploded, tanking many fortunes. As of March 18, when we finalized this list, Forbes counted 2,095 billionaires, 58 fewer than a year ago and 226 fewer than just 12 days earlier, when we initially calculated these net worths. Of the billionaires who remain, 51% are poorer than they were last year. In raw terms, the world’s billionaires are worth $8 trillion, down $700 billion from 2019.

Jeff Bezos is the world’s wealthiest person for the third year in a row, despite giving $36 billion worth of his Amazon stock to his ex-wife MacKenzie Bezos as part of their divorce settlement last summer. He’s worth $113 billion, buoyed by a 15% rise in Amazon’s shares since our 2019 list. The e-commerce giant he runs has been in the spotlight amid the pandemic; it’s hiring 100,000 full- and part-time workers to help meet increased demand from consumers staying home and shopping online.

    While traumatic, the teapot incident wouldn’t affect Baldwin as profoundly as the gang violence that permeated daily life in her neighborhood. By 16, she had lost seven friends to shootings.

Baldwin sought change, a fresh start. So she moved to the family’s Houston home to finish high school. 

She remembers the day she decided to join the military. It was during morning homeroom, and her teacher wheeled in a TV. He turned on the broadcast and said, “America is under attack.” The students watched in horror as 9/11 unfolded.

The next day, recruiters from all the armed services came to school, and Baldwin spoke with them. Baldwin, then 17, was curious and started a conversation. In the coming weeks, she’d go into the Army recruiting office and continue to talk about life in the military. Eventually, she’d find herself in downtown Houston taking a test called the ASVAB, which shows potential recruits the military jobs most suited to them. 

As she thought about enlisting, 9/11 remained forefront in her mind.

“Just seeing the agony, the pain and the crying of people who had lost people in the twin towers—I remembered my friends that I had to bury. I said, ‘I want to serve. I want to be part of this mission.’”

Baldwin’s mother supported her decision and allowed her to enlist early with the 451th Civil Affairs Battalion, an Army Reserve unit mother near her home. Early enlistment is available through the Army’s Split Option program, which allows high school students to join the Army Reserve or National Guard and enter basic combat training in the summer after their junior year. Enrollees then resume school to finish their senior year and graduate.

Four months after graduating,mother Baldwin transferred to Active Duty and got a job as an armorer—the occupation responsible for maintaining and managing mother a unit’s weapons—and she went to war. Her first deployment came in 2003, when she shipped off to Baghdad, Iraq.";
        $response = $client->request('POST', 'http://textalyser.net/index.php?lang=en#analysis', [
            'multipart' => [
                [
                    'name'     => 'text_main',
                    'contents' => $text
                ],
                [
                    'name'     => 'site_to_analyze',
                    'contents' => 'http://'
                ],
                [
                    'name'     => 'file_to_analyze',
                    'contents' => '(binary)'
                ],
                [
                    'name'     => 'min_char',
                    'contents' => 3
                ],
                [
                    'name'     => 'special_word',
                    'contents' => ''
                ],
                [
                    'name'     => 'words_toanalyse',
                    'contents' => 10
                ],
                [
                    'name'     => 'count_numbers',
                    'contents' => 1
                ],
                [
                    'name'     => 'is_log',
                    'contents' => 1
                ],
                [
                    'name'     => 'stoplist_lang',
                    'contents' => 1
                ],
                [
                    'name'     => 'stoplist_perso',
                    'contents' => 'worth year',
                ],
            ]
        ]);
        $body = $response->getBody()->getContents();
        $crawler = new \Symfony\Component\DomCrawler\Crawler($body);
        //$count = $crawler->filter('table')->count(); 
        //$elements = $crawler->filter('table')->eq(11)->text();

        $table = $crawler->filter('table')->eq(11);

        $tds = array();
        foreach ($table as $node => $content) {
            // create crawler instance for result
            $crawler = new \Symfony\Component\DomCrawler\Crawler($content);
            //iterate again
            $index = 0;
            $rows= [];
            foreach ($crawler->filter('td') as $node) {
                $rows[] = $node->nodeValue;
                if (sizeof($rows) % 4 == 0) {
                    $tds[] = $rows;
                    $rows =[];
                }
            }

            /*foreach ($crawler->filter('td') as $i => $node) {
               // extract the value
                $tds[] = $node->nodeValue;

            }*/
            //$rows[] = $tds;

        }
        echo "<pre>";
        var_dump($tds);
        
        die();
        
        return $this->render('index');
    }
}
