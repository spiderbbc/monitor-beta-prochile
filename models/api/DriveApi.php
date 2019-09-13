<?php 
namespace app\models\api;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\models\ProductsSeries;
use app\models\Products;
use app\models\ProductsModels;
use app\models\ProductsFamily;
use app\models\ProductCategories;
use app\models\CategoriesDictionary;

require_once Yii::getAlias('@vendor') . '/autoload.php'; // call google client

class DriveApi extends Model{

	private $_data;
    private $_productsFamily = ['HA', 'HE', 'MC', 'Monitores y proyectores'];

    public $driveTitle; // used in form alert
	

	public function getDictionaries(){
	    // Get the API client and construct the service object.
	    $service = $this->_getServices();
        $spreadsheetId = Yii::$app->params['drive']['monitor-beta-lg'];
	    $response      = $service->spreadsheets->get($spreadsheetId);
	    $sheetName     = [];
	    for ($i = 0; $i < sizeof($response->sheets); $i++) {
	    	$its_title_dictionary = $response->sheets[$i]->properties->title;
	    	if($its_title_dictionary[0] == '_'){
	    		$sheetName[$its_title_dictionary] = $its_title_dictionary;
                
	    	}
	    }
	    /*if (count($sheetName)) {
	        $this->saveCategoriesDictionary($sheetName);
	    }*/
	    return (count($sheetName)) ? $sheetName : null;

	}

	public function getContentDictionaryByTitle($sheetNames = [])
    {
        $service = $this->_getServices();

        $spreadsheetId = Yii::$app->params['drive']['monitor-beta-lg'];
        $response      = $service->spreadsheets->get($spreadsheetId);

        $values = [];
        foreach ($sheetNames as $sheetName) {
            $response           = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
            $values[$sheetName] = $response->getValues();
        }
        if (count($values)) {
            foreach ($values as $key => $words) {
                for ($i = 0; $i < sizeof($words); $i++) {
                    $values[$key][$i] = trim($words[$i][0]);
                }
            }
        }

        return (count($values)) ? $values : null;

    }

    public function getContentDocument()
    {
        $service = $this->_getServices();

        $sheetNames = $this->_productsFamily;
        $spreadsheetId = Yii::$app->params['drive']['Drive Diccionario Listening'];

        $values = [];

        foreach ($sheetNames as $id => $sheetName) {
            $response = $service->spreadsheets_values->get($spreadsheetId, $sheetName);
            $values[] = $response->getValues();
        }

        try {
            $this->SaveToDatabase($values);
        }catch (ErrorException $e){
            throw new \yii\web\NotFoundHttpException(Yii::t('app','houston we have a problem, problem in the drive document ლ(ಠ_ಠლ)   '));
        }

    }

    /**
     * [SaveToDatabase calls distins function to save in database]
     * @param [array] $values [all products from dictionaries]
     */
    public function SaveToDatabase($values)
    {
        for ($i = 0; $i < sizeof($values); $i++) {
            for ($j = 1; $j < sizeof($values[$i]); $j++) {
                $id         = $this->saveSeries(trim($values[$i][$j][0]));
                $familyId   = $this->saveFamily($id, trim($values[$i][$j][1]));
                $categoryId = $this->saveProductCategory($familyId, trim($values[$i][$j][2]));
                $productId  = $this->saveProduct($categoryId, trim($values[$i][$j][3]));
                $modelId    = $this->saveProductsModel($productId, trim($values[$i][$j][4]));
            }
        }

    }

    public function saveSeries($value)
    {
        $value = $this->delete_quotation_marks($value);
        $params = [
            'abbreviation_name' => $value,
        ];
        $model = ProductsSeries::findOne($params);
        if (is_null($model)) {
            $model                    = new ProductsSeries;
            $model->name              = $value;
            $model->abbreviation_name = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveFamily($serieId, $value)
    {
        $value = $this->delete_quotation_marks($value);

        $params = [
            'seriesId' => $serieId,
            'name'     => $value,
        ];
        $model = ProductsFamily::findOne($params);
        if (is_null($model)) {
            $model           = new ProductsFamily;
            $model->seriesId = $serieId;
            $model->name     = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveProductCategory($familyId, $value)
    {
        $value = $this->delete_quotation_marks($value);

        $params = [
            'products_familyId' => $familyId,
            'name'     => $value,
        ];
        $model = ProductCategories::find()->where($params)->one();
        if (is_null($model)) {
            $model                    = new ProductCategories;
            $model->products_familyId = $familyId;
            $model->name              = $value;
            $model->save();
        }
        return $model->id;
    }

    public function saveProduct($categoryId, $value)
    {   
        $value = $this->delete_quotation_marks($value);

        $params = [
            'categoryId' => $categoryId,
            'name'       => $value,
        ];
        $model = Products::findOne($params);
        if (is_null($model)) {
            $model             = new Products;
            $model->categoryId = $categoryId;
            $model->name       = $value;
            $model->save();
        }
        return $model->id;

    }

    public function saveProductsModel($productId, $value)
    {   
        $value = $this->delete_quotation_marks($value);

        $params = [
            'productId'    => $productId,
            'name' => $value,
        ];
        $model = ProductsModels::findOne($params);
        if (is_null($model)) {
            $model            = new ProductsModels;
            $model->productId = $productId;
            $model->name      = $value;
            $model->save();
            $modelId = $model->id;
        }
        return $model->id;
    }

    public function saveCategoriesDictionary($categoryTitles)
    {
        foreach ($categoryTitles as $category) {
            $model = CategoriesDictionary::findOne(['name' => $category]);
            if (is_null($model)) {
               $model = new CategoriesDictionary();
               $model->name = $category;
               $model->save();
            }
        }
    }

	public function getParamsDrive(){
		$params = Yii::$app->params['drive'];

		return array_flip($params);
	}

	private function _getClient()
    {
        $client = new \Google_Client();
        $http   = new \GuzzleHttp\Client([
            //'verify' => 'c:\cert\cacert.pem'
        ]);
        $client->setHttpClient($http);
        $client->setAuthConfig(Yii::getAlias('@credencials'));
        $client->setApplicationName('monitor-app');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS_READONLY);

        return $client;

    }

    private function _getServices()
    {
        // Get the API client and construct the service object.
        $client  = $this->_getClient();
        $service = new \Google_Service_Sheets($client);
        return $service;
    }

    public function delete_quotation_marks($string) { 
        $result = str_replace(array('\'', '"'), '', $string);
        return $result; 
    } 
	


}

?>