<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;
use yii\db\Command;
/**
 * ExcelSearch represents the model behind search products, dictionaries and save`.
 */
class ExcelSearch {

	public $alertId;
    public $data = [];
    public $products;
    public $resourcesId;
    public $isDictionaries = false;
    public $isBoolean = false;

	/**
     * [load load in to local variables]
     * @param  [array] $params [product [tweets]]
     * @return [boolean]
     */
    public function load($params){
        if(empty($params)){
           return false;     
        }
        $this->alertId = ArrayHelper::getValue($params, 0);
        $this->isDictionaries = $this->_isDictionaries();
        $this->products = $this->getProducts();
        $this->resourcesId = $this->_setResourceId();
      
        // is boolean

        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                // loop in data file
                foreach($params[$p] as $data => $paginations){
                	foreach ($paginations as $pagination => $values){
                		for($v = 0; $v < sizeof($values); $v++){
                			$values[$v]['message_markup'] = $values[$v]['Post Snippet'];
                			$this->data[] = $values[$v];
                		}
                	}
                }
            } // end loop json
        }
        // looking products
        $this->searchProductsData();
        // save products found it
        $this->saveProductsMentionsAlerts();
        
        return true;
    }
    /**
     * search products in the data
     */
    private function searchProductsData(){
    	$data = [];
    	$data_count = count($this->data);

    	for($d = 0; $d < sizeof($this->data); $d++){
    		$title = $this->data[$d]['Title'];
    		$message_markup = $this->data[$d]['message_markup'];
    		for($p = 0; $p < sizeof($this->products); $p++){
    			// destrutura el product
				$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$p]);
    			if($data_count){

    				if(!is_null($title)){
    				$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($title,$product_data) : \app\helpers\StringHelper::containsAll($title,$product_data);

    				if($is_contains){
    					// if a not key
						if(!ArrayHelper::keyExists($this->products[$p], $data, false)){
							$data[$this->products[$p]] = [] ;

						}
						// if a not in array
						if(!in_array($this->data[$d],$data[$this->products[$p]])){
							$data[$this->products[$p]][] = $this->data[$d];
							$data_count --;
							break;

						}

    				}

    			}

    			if(!is_null($message_markup)){
    				$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($message_markup,$product_data) : \app\helpers\StringHelper::containsAll($message_markup,$product_data);

    				if($is_contains){
    					// if a not key
						if(!ArrayHelper::keyExists($this->products[$p], $data, false)){
							$data[$this->products[$p]] = [] ;

						}
						// if a not in array
						if(!in_array($this->data[$d],$data[$this->products[$p]])){
							$data[$this->products[$p]][] = $this->data[$d];
							$data_count --;
							break;

						}

    				}

    			}

    		}


    		}// end loop products
    	}// end loop data

    	$this->data = $data;

    }

    /**
     * get products in the alert
     */
    private function getProducts(){
    	$products_models_alerts = \app\models\ProductsModelsAlerts::findAll(['alertId' => $this->alertId]);
    	$alertsConfig = [];
        
        if(!empty($products_models_alerts)){
            $alertsConfig['products'] = [];
            foreach($products_models_alerts as $product){
                // models
                if(!in_array($product->productModel->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->name);
                }
                // products
                if(!in_array($product->productModel->product->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->product->name);
                }
                // category
                if(!in_array($product->productModel->product->category->name,$alertsConfig['products'])){
                    array_push($alertsConfig['products'], $product->productModel->product->category->name);
                }
               // array_push($alertsConfig[$c]['products'], $product->productModel->product->category->productsFamily->name);
            }
        }
        // order products by his  length
		array_multisort(array_map('strlen', $alertsConfig['products']), $alertsConfig['products']);
		
		return $alertsConfig['products'];
        
    }

    private function saveProductsMentionsAlerts(){
    	$products = array_keys($this->data);
    	$where = [
    		'alertId' => $this->alertId,
    		'resourcesId'  => $this->resourcesId,
    		'condition'    =>  'ACTIVE',
    		'type'         =>  'document',

    	];


    	for($p = 0; $p < sizeof($products); $p++){

    		$isProductExist = \app\models\AlertsMencions::find()->where(['alertId' => $this->alertId, 'resourcesId' => $this->resourcesId,'term_searched' => $products[$p]])->exists();
    		if(!$isProductExist){
    			$model = new \app\models\AlertsMencions();
    			$model->alertId = $this->alertId;
    			$model->resourcesId = $this->resourcesId;
    			$model->condition = 'ACTIVE';
    			$model->type = 'document';
    			$model->term_searched = $products[$p];
    			$model->save();
    		}

    	}
    }

    /**
     * methodh applied depends of type search
     *
     *
     * @return boolean status
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
             echo "no dictionaries .. \n";
            // save all data
            $mentions = $this->data;
            //$search = $this->saveMentions($mentions);
            return $search;
        }

        // if  dictionaries and  boolean
        if($this->isDictionaries && $this->isBoolean){
            // init search
            echo "boolean and dictionaries \n";
            // retur something
        }

        // if  dictionaries and  !boolean
        if($this->isDictionaries && !$this->isBoolean){
            // init search
            echo "only dictionaries \n";
            $mentions = $this->data;
            $data = $this->searchDataByDictionary($mentions);
            //$search = $this->saveMentions($data);
            //return $search;
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }


    private function searchDataByDictionary($mentions){
    	
    	$words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();

    	foreach ($mentions as $product => $mention){
    		for($m =  0; $m < sizeof($mention); $m++){
    			if(!is_null($mention[$m]['message_markup'])){
    				$wordsId = [];
                    for($w = 0; $w < sizeof($words); $w++){
                    	$sentence = $mentions[$product][$m]['message_markup'];
                        $containsCount = \app\helpers\StringHelper::containsCount($sentence, $words[$w]['name']);
                        if($containsCount){
                        	$wordsId[$words[$w]['id']] = $containsCount;
                            $word = $words[$w]['name'];
                            $mentions[$product][$m]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");

                        }// end if containsCount

                    }// end loop words
                    if(!empty($wordsId)){
                        $mentions[$product][$m]['wordsId'] = $wordsId;
                    }else{
                        unset($mentions[$product][$m]);
                    }
    			}// end if is_null

    		}// end loop mention 
    	}// end foreach mentions

    	/*var_dump($mentions);
    	die();*/
    }

    /**
     * [_isDictionaries is the alert hace dictionaries]
     * @return boolean [description]
     */
    private function _isDictionaries(){
        if(!is_null($this->alertId)){
            $keywords = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->exists();
            return $keywords;
        }
        return false;
    }

    /**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		
		$socialId = (new \yii\db\Query())
		    ->select('id')
		    ->from('type_resources')
		    ->where(['name' => 'Document'])
		    ->one();
		
		
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Excel Document','resourcesId' => $socialId['id']])
		    ->all();
		

		return ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

}