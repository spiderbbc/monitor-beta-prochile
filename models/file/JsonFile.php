<?php
namespace app\models\file;

use yii;
use \Filebase\Database;

class JsonFile {

	public $filebase;
	public $documentId;
	private $_data;

	public function isDocumentExist(){
		if (!$this->filebase->has($this->documentId)){ return false;}
		return true;
	}

	public function thereIsDataInDocument(){
		return (!empty($this->filebase->get($this->documentId)->field('data'))) ? true : false;
	}

	public function getSinceIdByProducts($products = []){
		
		$products_sinceId = [];
		for($p = 0; $p < sizeOf($products); $p++){
			$ids = $this->filebase->get($this->documentId)->filter('data',$products[$p],function($tweet, $product) {
			    if($tweet['product_name'] == $product){
			    	return $tweet['id'];
			    }
			});
			// if empty($ids)
			if(empty($ids)){$products_sinceId[$products[$p]] = ''; }
			if(!empty($ids)){$products_sinceId[$products[$p]] = max($ids); }
			
		}
		return $products_sinceId;
	}

	public function load($data){
		$this->_data[] =  $data;
	}

	public function save(){
		if(!empty($this->_data)){
			$file = $this->filebase->get($this->documentId);
			foreach ($this->_data as $key => $value) {
	            $file->$key = $value;
	        }
	        $file->save();
		}
	}


	function __construct($folderpath =[],$read_only = false,$validate = [])
	{
		
		// path to folder flat archives
		$s = DIRECTORY_SEPARATOR;
		$folder = $folderpath['resource'];
		$this->documentId = $folderpath['documentId'];
		

		$this->filebase = new Database([
            'dir' => \Yii::getAlias('@data')."{$s}{$this->documentId}{$s}{$folder}",
            'cache'          => true,
    		'cache_expires'  => 1800,
    		'pretty'         => true,
	    	'safe_filename'  => true,
	    	'read_only'      => $read_only,
        ]);
	}

}

?>