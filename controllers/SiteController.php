<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\form\LoginForm;
use app\models\form\ContactForm;

//use yii2tech\spreadsheet\Spreadsheet;
use app\models\file\JsonFile;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        $hash = Yii::$app->security->generatePasswordHash("");
        return $this->render('about');
    }


    public function actionExcel(){
      
      $documento = new Spreadsheet();
      $documento
          ->getProperties()
          ->setCreator("Monitor App")
          ->setLastModifiedBy('Monitor App User') // última vez modificado por
          ->setTitle('Pequeños Desordenados PhpSpreadSheet')
          ->setSubject('Concurso')
          ->setDescription('Este documento fue generado por Monitor App')
          ->setKeywords('PequeñosDesordenados')
          ->setCategory('concurso');
       
      $hoja = $documento->getActiveSheet();
      $hoja->setTitle("Concurso PequeñosDesordenados");
      
      $hoja->setCellValueByColumnAndRow(1, 1, "id");
      $hoja->setCellValueByColumnAndRow(2, 1, "comentario");
      $hoja->setCellValueByColumnAndRow(3, 1, "Link de la foto");
    
      $jsonfile = new JsonFile(1,'Facebook Comments');

      $model = $jsonfile->findAll(); 
      $model = $model[0][0];
      $data = $model[0]['comments']; 

      $index = 2;
      for($d=0; $d < sizeOf($data); $d++){
          $hoja->setCellValue("A{$index}", $data[$d]['id']);
          $hoja->setCellValue("B{$index}", $data[$d]['message']);
          
          $hoja->setCellValue("C{$index}", "Link foto");
          $hoja->getCell("C{$index}")->getHyperlink()->setUrl($data[$d]['src']);
          $index++;

      }     

       
      $writer = new Xlsx($documento);
       
      # Le pasamos la ruta de guardado
      $writer->save('PequeñosDesordenados.xlsx');  

        /*$jsonfile = new JsonFile(1,'Facebook Comments');

        $model = $jsonfile->findAll(); 
        $model = $model[0][0];

        $spreadsheet = new Spreadsheet();*/

        // FIXED HEADER
        /*$spreadsheet->getActiveSheet();
        
        $data = $model[0]['comments'];

        $spreadsheet->setCellValueByColumnAndRow(1, 1, "id");
        $spreadsheet->setCellValueByColumnAndRow(2, 1, "message");
        $spreadsheet->setCellValueByColumnAndRow(3, 1, "Link de la foto");*/

        /*for($d=0; $d < sizeOf($data); $d++){
          $id = $data[$d]['id'];
          $message = $data[$d]['message'];
          $src = $data[$d]['src'];

        }*/
       // Create Excel file and sve in your directory
       

       /*$writer = new Xlsx($spreadsheet);
       $writer->save(\Yii::getAlias('@data').'mysheet.xlsx');*/



    }
}
