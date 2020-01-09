<?php 
\app\assets\AxiosAsset::register($this);

?>
<div class="monitor-default-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
    
    <div id="chart_div"></div>

    <input type="hidden" name="hidden_html" id="hidden_html" />
    <button type="button" name="create_pdf" id="create_pdf" class="btn btn-danger btn-xs">Make PDF</button>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	 // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
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

        // Instantiate and draw our chart, passing in some options.
        var chart_area = document.getElementById('chart_div');
        var hidden_html = document.getElementById('hidden_html');

        var chart = new google.visualization.PieChart(chart_area);

        google.visualization.events.addListener(chart, 'ready', function () {
          hidden_html.value = chart.getImageURI();

        });

        chart.draw(data, options);
      }


      var button = document.getElementById('create_pdf');
      button.addEventListener('click',function(e){
        var hidden_html = document.getElementById('hidden_html').value;
        axios.post('http://localhost/monitor-beta/web/monitor/', {
            hidden_html: hidden_html,
          })
          .then(function (response) {
            console.log(response.data.data);
            /*var link = document.createElement('a');
            link.href = "http://localhost" + response.data.data;
            link.download = 'file.pdf';
            link.dispatchEvent(new MouseEvent('click'));*/
            
          })
          .catch(function (error) {
            console.log(error);
          });
      });
      
      
</script>


