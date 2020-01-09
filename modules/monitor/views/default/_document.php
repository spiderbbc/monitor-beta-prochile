<?php 
use yii\helpers\Html;
use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=PT+Sans+Narrow:700&display=swap" rel="stylesheet">
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
         google.charts.load("current", {packages:['corechart']});
        google.charts.setOnLoadCallback(drawChart);
       
        function drawChart() {

          // Create the data table.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Topping');
            data.addColumn('number', 'Slices');
            data.addRows([
              ['Mushrooms', 3],
              ['Onions', 1],
              ['Olives', 1],
              ['Zucchini', 1],
              ['Pepperoni', 2]
            ]);

          // Set chart options
            var options = {'title':'How Much Pizza I Ate Last Night',
                           'width':400,
                           'height':300};

          var chart_div = document.getElementById('chart_div');
          var test = document.getElementById('test');
          test.innerHTML = '<h1>Hiiiii</h1>';
          var chart = new google.visualization.PieChart(chart_div);

          // Wait for the chart to finish drawing before calling the getImageURI() method.
          google.visualization.events.addListener(chart, 'ready', function () {
            chart_div.innerHTML = '<img src="' + chart.getImageURI() + '">';
            console.log(chart_div.innerHTML);
          });

          chart.draw(data, options);

      }
    </script>

</head>
<body>
    
    <div class="container">
        <!-- images portada -->
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <?= Html::img($url_logo_small) ?>
                    <br>
                    <?= Html::img($url_logo) ?>
                </div>
            </div>
        </div>
        <!-- end images portada -->
        <!-- leyend -->
        <div class="row">
            <div class="col-md-12">
                <h1 style="font-family: 'PT Sans Narrow', sans-serif;">Reporte Monitor</h1>
                <br>
            <p style="font-family: 'PT Sans Narrow', sans-serif;"><?=  Yii::$app->formatter->asDate('now', 'yyyy-MM-dd');  ?></p>
            <p>---</p>
            <p style="font-family: 'PT Sans Narrow', sans-serif; font-size: 16px; color: blue"><?= $model->user->username ?></p>
            <p style="font-family: 'PT Sans Narrow', sans-serif; font-size: 16px;">Social Media Trends</p>

            </div>
        </div>
        <!-- end  leyend -->
        <!-- break to another page -->
        <br><br><br><br><br><br><br><br>
        <!-- end break to another page -->
        <!-- top belt -->
        <div class="row">
            <div class="col-md-12">
                <div class="">
                        <?= Html::img($data) ?>
                </div>
            </div>
        </div>
        <!-- end top belt -->
        <div id="test"></div>
        <div id="chart_div"></div>



        
    </div>


    <script type="text/php">
        if ( isset($pdf) ) {
            $x = 520;
            $y = 15;
            $text = "{PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 6;
            $color = array(255,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>