<?php 
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>


<!-- template que muestra los componentes-->
<script type="text/x-template" id="detail">
  <div>
  <hr> 
  <div v-if="!loading && count" class="col-md-12">
    
    <box-detail 
    :alertid="alertid" 
    :resourceid="resourceid" 
    :term="term" 
    :socialId="socialId"
    :isChange="isChange"
    ></box-detail>
    
    <graph-count-domains-detail
    :alertid="alertid" 
    :resourceid="resourceid" 
    :term="term" 
    :socialId="socialId"
    :isChange="isChange"/>
    
    <grid-detail
    :alertid="alertid" 
    :resourceid="resourceid" 
    :term="term" 
    :socialId="socialId"
    :isChange="isChange" 
    ></grid-detail>
  
  </div>
  <div v-else-if="loading">
      <div class="loader">
        <div class="spinner" style="height: 15vh;width:  15vh;"></div>
      </div>
  </div>
  <div v-else-if="!loading && count === 0">
    <div class="col-md-12">
      <div class="alert alert-info">
        <div v-html="msg"></div>
      </div>
    </div>
  </div>
  </div>
</script>

<!-- box sources -->
<script type="text/x-template" id="box-info-detail">
  <div  class="row">
    <div v-for="box_property in box_properties" :key="box_property.id" @click="filter(box_property.method,box_property.attribute)" :class="calcColumns">
      <div  class="info-box">
        <span :class="box_property.background_color"><i :class="box_property.icon"></i></span>

        <div class="info-box-content">
          <span class="info-box-text"><small>{{box_property.title}}</small></span>
          <span class="info-box-number">
            <small>{{box_property.total | formatNumber }}</small>
          </span>
        </div>
        <!-- /.info-box-content -->
      </div>
      <!-- /.info-box -->
    </div>
  </div> 
</script>

<!-- box common words mentions -->
<script type="text/x-template" id="box-common-words-detail">
  <div  class="row">
    <div class="col-md-12">
      <div v-if="words.length" class="well">
        <h4 class="card-title">Palabras más utilizadas</h4>
        <p>
          <small>In Caption</small>
        </p>
        <span v-for="word in words" 
              :key="word.name" 
              v-if="word.name !=''" 
              style="margin-left: 5px;" 
              data-toggle="tooltip" data-placement="top" :title="word.total"
              class="badge">{{word.name}}
        </span>
      </div>
      <div v-else class="well">
        <h4 class="card-title">No hay Palabras utilizadas</h4>
      </div>
    </div>
  </div> 
</script>

<!-- graph common words mentions -->
<script type="text/x-template" id="graph-common-words-detail">
  <div  class="row">
    <div class="col-md-12">
      <div v-show="words.length">
        <div id="graph-common-words"></div>
      </div>
      <div v-show="!words.length" class="well">
        <h4 class="card-title">No hay Palabras utilizadas</h4>
      </div>
    </div>
  </div> 
</script>



<!-- graph common words mentions -->
<script type="text/x-template" id="graph-count-domains-detail">
<div  class="row">
    <div class="col-md-12">
      <div v-show="domains.length">
        <div id="view-count-domains-chart"></div>
      </div>
    </div>
  </div> 
</script>

<!-- grid mentions -->
<script type="text/x-template" id="grid-mention-detail">
  <div  class="row">
    <div class="col-md-12">
      <?php Pjax::begin(['id' => 'mentions-detail', 'timeout' => 10000, 'enablePushState' => false]) ?>
      <?=   $this->render('/alert/_search-word', ['model' => $searchModel,'view' => $view]); ?>
          <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'autoXlFormat'=>true,
            'krajeeDialogSettings' => ['overrideYiiConfirm' => false],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'export'=>[
                'showConfirmAlert'=>false,
                'target'=> GridView::TARGET_BLANK
            ],
            'exportConfig' => [
              GridView::TEXT => ['label' => 'Guardar como Texto'],
              GridView::EXCEL => ['label' => 'Guardar como Excel'],
              GridView::PDF => ['label' => 'Guardar como Pdf'],
              GridView::JSON => ['label' => 'Guardar como JSON'],
          ],
            'columns' => \app\helpers\DetailHelper::setGridMentionsColumnsOnDetailView($resource->name,$searchModel),
            'class' => 'yii\grid\Column',
            'pjax'=>false,
            'pjaxSettings'=>[
              'options'=>[
                'id'=> 'mentions'
              ]
            ],
            'showPageSummary'=>true,
            'panel'=>[
                'type'=>'primary',
                'heading'=>'Menciones'
            ],
          ]); ?>
      <?php Pjax::end() ?>
    </div>
  </div> 
</script>