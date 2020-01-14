
let id                 = document.getElementById('alertId').value;
const origin           = location.origin;
const baseUrlApi       = `${origin}/monitor-beta/web/monitor/api/mentions/`;
const baseUrlDocument  = `${origin}/monitor-beta/web/monitor/pdf/`;
const baseUrlView      = `${origin}/monitor-beta/web/monitor/alert/`;

let refreshTime = 2000;
let refreshTimeTable = 40000;

let controllerName = {
	"Twitter": "Twitter",
	"Live Chat": "Live-chat",
	"Live Chat Conversations": "Live-chat-conversations",
	"Facebook Comments": "Facebook-comments",
	"Facebook Comments": "Facebook-comments",
};

let tableConfig = {
	'scrollY' : '400px',
	'scrollCollapse' : true,
	"processing": true,
    "ajax": {
    	'url': baseUrlApi +'list-mentions?alertId=' + id,
    	//"dataSrc": "mentions"
    },
    "fixedColumns": true,
    "columns": [
        { "data": "alertMention.resources.name" ,"width": "200px" },
        { "data": "alertMention.term_searched" ,"width": "200px" },
        { 
        	"data": "created_time" ,
        	"type":"date",
        	"render": function(data){
        		var a = new Date(data * 1000);
				var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
				var year = a.getFullYear();
				var month = months[a.getMonth()];
				var date = a.getDate();
				var hour = a.getHours();
				var min = a.getMinutes();
				var sec = a.getSeconds();
				var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
				return time;
        	},
        	"width": "200px" 
        },
        {"data": "origin.name","width": "200px" },
        {"data": "origin.screen_name","width": "200px" },
        { "data": "subject" ,"width": "200px" },
        { "data": "message_markup","width": "40%" },
        { 
        	"data": "url",
        	"render": function(data){
        		var link = "-";
        		if(data !== null && data !== '-'){
        			var href = "<a href=";
                    var url = data;
                    var target =   " target=" + '.$target.'
                    var text = ">link</a>";
                    link = href.concat(url,target ,text);
                    return link;
        		}
        		return '-'
               
        	},
        	"width": "10%" 

         }
    ]
};

let data_chart = new Object();;



const count_mentions = Vue.component('total-mentions',{
	props: ['count'],
	data: function () {
	    return {
	    }
	},
	template: '#view-total-mentions',
});


const count_resources = Vue.component('total-resources',{
	template: '#view-total-mentions-resources',
	data(){
		return {
			alertId:id,
			response:null,
			resourceId:null,
			loaded: false
		}
	},
	mounted(){
		setInterval(function () {
	      this.fetchResourceCount();
	    }.bind(this), refreshTime);
		
	},
	methods:{
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'count-sources-mentions' + '?alertId=' +this.alertId)
		      .then(response => {
		      	this.response = response.data.resources;
		      	if(typeof this.response === 'object'){
		      		this.loaded = true;
		      	}

		      })
		      .catch(error => console.log(error))
		},
		fetchResourceName(resourceName){
			axios
		      .get(baseUrlApi + 'get-resource-id' + '?resourceName=' +resourceName)
		      .then(response => (this.resourceId = response.data.resourceId))
		      .catch(error => console.log(error))
		    var link = `${baseUrlView}${controllerName[resourceName]}?resourceId=${this.resourceId}&alertId=${this.alertId}`;  
		    return link;
		}
	},	
	
});


/**
 * [Gráfico de número de interacciones por red social]
 */
const count_resources_chat = Vue.component('total-resources-chart',{
	template: '#view-total-resources-chart',
	data: function () {
	    return {
	    	alertId:id,
	    	response: [
	    	  ['Red Social', 'Shares', 'Likes Post', 'Likes','Total Tweets','Retweets','Total'],	
	    	  ['Facebook Comment', 1000, 400, 199,50,700,80],
	          ['Facebook Messages', 1170, 460, 250,70,50,10],
	          ['Instagram Comment', 660, 1120, 300,25,70,15],
	          ['Twitter', 1030, 540, 350,20,15,10],
	          ['Live Chats', 1030, 540, 350,90,400,155],
	          ['Live Chats Tickets', 1030, 540, 350,40,100,90]


	    	],
	    	//loaded: false,
	    	loaded: true,
	    	//dataTable: ['Red Social', 'Shares', 'Likes Post', 'Likes','Total Tweets','Retweets','Total'],
	    	view:null,
	    	column: [0,
	    		1,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" 
                },
                2,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" 
                },
                3,
                { 
                	calc: "stringify",
                    sourceColumn: 3,
                    type: "string",
                    role: "annotation" 
                },
                4,
                { 
                	calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" 
                },
                5,
                { 
                	calc: "stringify",
                    sourceColumn: 5,
                    type: "string",
                    role: "annotation" 
                },
                6,
                { 
                	calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" 
                }
            ],
            

            options: {
	          chart: {
	            title: 'Company Performance',
	            subtitle: 'Sales, Expenses, and Profit: 2014-2017',
	          },
	          bars: 'vertical',
	          vAxis: {format: 'decimal'},
	       //   is3D:true,
	          height: 400,
	          //width: 930,
	          colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab','#b5d817'],
	          chartArea: {width: "70%"}
	        },
	    }
	},
	mounted(){
		//this.response = [this.dataTable];
		// Load the Visualization API and the corechart package.
     	google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(this.drawColumnChart);	

        /*this.fetchResourceCount();
		setInterval(function () {
		   google.charts.setOnLoadCallback(this.drawColumnChart);	
	      this.fetchResourceCount();
	    }.bind(this), refreshTime);*/
		
	},
	methods: {
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'count-sources-mentions' + '?alertId=' +this.alertId)
		      .then(response => {
		      	if(typeof this.response === 'object'){
		      		this.response.splice(1,response.data.data.length);
			      	for(let index in response.data.data){
			      		this.response.push(response.data.data[index]);
			      	}
		      		this.loaded = true;
		      	}

		      })
		      .catch(error => console.log(error))
		},
		drawColumnChart(){
			var data = google.visualization.arrayToDataTable(this.response);
			var view = new google.visualization.DataView(data);
			view.setColumns(this.column);
			var chart = new google.visualization.ColumnChart(document.getElementById("resources_chart_count"));

			google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart = { 'chart_bar_resources_count' : chart.getImageURI()};

	        });

			var container = document.getElementById("resources_chart_count");
			container.style.width = "100%";
			chart.draw(view, this.options);
			 
			/*var data = google.visualization.arrayToDataTable(this.response);
			var view = new google.visualization.DataView(data);
			view.setColumns(this.column);
			var chart = new google.visualization.ColumnChart(document.getElementById("resources_chart_count"));
			google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart = { 'chart_bar_resources_count' : chart.getImageURI()};

	        });

			chart.draw(view, this.options);
*/
		},

	}

});




const listMentions = Vue.component('list-mentions',{
	template: '#mentions-list',
	data: function () {
	    return {
	    	table:null
	    }
	},
	mounted(){
		var table = this.setDataTable();
		setInterval( function () {
		    table.ajax.reload();
		}, refreshTimeTable );
	},
	methods:{
		setDataTable(){
			return initSearchTable();
		}
	}
});

const cloudWords = Vue.component('cloud-words',{
    'template': '#cloud-words',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchWords();
	    }.bind(this), refreshTime);
		
		
	},
	methods:{
		fetchWords(){
			axios.get(baseUrlApi + 'list-words' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.wordsModel
		        if(this.response.length){
		        	this.loaded = true;
		        	var words = this.handlers(this.response);
		        	var some_words_with_same_weight =
					    $("#jqcloud").jQCloud(words, {
					      width: 1000,
      					  height: 350,
      					  delay: 50
					});
		        }
		        
		    })
		},
		reload(){
			var words = this.handlers(this.response);
			$('#jqcloud').jQCloud('update', words);
		},
		handlers(response){
			var words = response.map(function(r){
				r.handlers = {click: function() {
			      $("#list-mentions").DataTable().search(r.text).draw();
			    }};
			    r.html = {'class': 'pointer-jqcloud'};
			    return r;
			});
			return words;
		}

	},

});

const tableDate = Vue.component('resource-date-mentions',{
	template: '#resource-date-mentions',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchMentionsDate();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchMentionsDate(){
			axios.get(baseUrlApi + 'resource-on-date' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.resourceDateCount
		        if(typeof this.response === 'object'){
		      		this.loaded = true;
		      	}
		    })
		    .catch(error => console.log(error))

		},
		collapseValue(target="",index){
			return `${target}collapse${index + 1}`;
		}
	}
});

const listEmojis = Vue.component('list-emojis',{
    'template' : '#emojis-list',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchEmojis();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchEmojis(){
			axios.get(baseUrlApi + 'list-emojis' + '?alertId=' + id )
		      .then((response) => {
		        if(typeof response.data.data.length === 'undefined'){
		        	this.response = response.data.data;
		        	this.loaded = true;
		        }
		    })
		},
	},
});

const statusAlert = Vue.component('status-alert',{
	'template' : '#status-alert',
	'props': ['resourceids'],
	data: function () {
	    return {
	    	response:null,
	    	status: null,
	    	resourceId: this.resourceids,
	    	classColor:'status-indicator',
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchStatus();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchStatus(){
			axios.get(baseUrlApi + 'status-alert' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.data;
		    })
		},
	},
	computed:{
		colorClass(){
			var valueClass = 'status-indicator--yellow';
			if(this.response != undefined || this.response != null){
				var search_data_response = this.response.search_data;
				for(let propeties in search_data_response){
					var span = document.getElementById(search_data_response[propeties].resourceId);
					if(search_data_response[propeties].status == 'Finish'){
						span.className = 'status-indicator status-indicator--red';
					}else{
						span.className = 'status-indicator status-indicator--green';
					}
				}

			}
			
			return valueClass;
		}
	}

});

const sweetAlert = Vue.component('modal-alert',{
	'template' : '#modal-alert',
	data: function () {
	    return {
	    	response:null,
	    	isShowModal:false
	    }
	},
	async mounted(){

	    while(!this.isShowModal){
	    	await sleep(2000);
	    	this.fetchStatus();
	    }
		
	    if(this.isShowModal){
	    	this.modal();
	    }

	   
	},
	methods:{
		fetchStatus(){
			axios.get(baseUrlApi + 'status-alert' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.data;
		        if(this.response != undefined || this.response != null){
		        	this.setStatus();
		        }
		        
		    })
		},
		setStatus(){
			if(this.response != undefined || this.response != null){
				var resources_count = Object.keys(this.response.search_data).length;
				var search_data = this.response.search_data;
				var statuses = Object.keys(search_data).filter(function(key) {
				   return search_data[key].status <= "Finish";
				});

				if(statuses.length == resources_count){
					this.isShowModal = true;
				}else{
					this.isShowModal = false;
				}


			}
		},
		modal(){
			const swalWithBootstrapButtons = Swal.mixin({
		      customClass: {
		        confirmButton: 'btn btn-info',
		        cancelButton: 'btn btn-success'
		      },
		      buttonsStyling: true
		    })

		    swalWithBootstrapButtons.fire({
		      title: '<strong>Alerta Finalizada</strong>',
		      icon: 'info',
		      html:
		        'Usted puede pulsar en <b>continuar</b>, para mantenerse en esta vista <hr> Puede pulsar en <b> Generar Informe </b> para recibir el documento pdf <hr> Puede pulsar en <b>actualizar la alerta</b> para buscar bajo otros parametros',
		      showCancelButton: true,
		      confirmButtonText: 'Generar Informe!',
		      cancelButtonText: 'Continuar!',
		      reverseButtons: true,
		      footer: '<a class="btn btn-dark" href= '+ baseUrlView + 'update?id='+ id + '&fresh=true' +'>update the alert?</a>'
		    }).then(function(result){
		        if(result.value){
		        	axios.post(baseUrlDocument + 'document' , {
			            chart_bar_resources_count: data_chart['chart_bar_resources_count'],
			            alertId: id,
			        })
			        .then(function (response) {
			          var link = document.createElement('a');
			          link.href = origin + response.data.data;
			          link.download = response.data.filename;
			          link.dispatchEvent(new MouseEvent('click'));
			          
			            
			        })
			        .catch(function (error) {
			           console.log(error);
			        });
		        }
		       // console.log(result)
		    });

		}
	},

});

function pdfModal(){
	let timerInterval
	Swal.fire({
	  title: 'Generando el Pdf!',
	  html: 'I will close in <b></b> milliseconds.',
	  timer: 8000,
	  timerProgressBar: true,
	  onBeforeOpen: () => {
	    Swal.showLoading()
	    timerInterval = setInterval(() => {
	      Swal.getContent().querySelector('b')
	        .textContent = Swal.getTimerLeft()
	    }, 100)
	  },
	  onClose: () => {
	    clearInterval(timerInterval)
	  }
	}).then((result) => {
	  if (
	    /* Read more about handling dismissals below */
	    result.dismiss === Swal.DismissReason.timer
	  ) {
	  	return true
	    console.log('I was closed by the timer') // eslint-disable-line
	  }
	})
	
}


// vue here
var vm = new Vue({
	el: '#alerts-view',
	data: {
		alertId:id,
		isData: false,
		count: 0,
		resourcescount:[],
	},
	mounted(){
		//this.init();
		setInterval(function () {
	      this.fetchIsData();
	    }.bind(this), refreshTime);
	    

	},
	methods: {
		fetchIsData(){
			axios
		      .get(baseUrlApi + 'count-mentions' + '?alertId=' +this.alertId)
		      .then(response => (this.count = response.data.count))
		      .catch(error => console.log(error))
		    if(this.count > 0){
		    	this.isData = true; 
		    } 
		},
		init(){
			axios
		      .get(baseUrlApi)
		      .then(response => (console.log("calling cronb tab")))
		      .catch(error => console.log(error))
		}
	},
	components:{
		count_mentions,
		count_resources_chat,
		//count_resources,
		/*listMentions,
		cloudWords,
		tableDate,
		listEmojis,
		sweetAlert,*/

	}	
});


function initSearchTable(){
	// Setup - add a text input to each footer cell
    $('#list-mentions thead th').each( function () {
        var title = $('#list-mentions tfoot th').eq( $(this).index() ).text();
        $(this).html(  '<input type="text" size="10" placeholder="Search '+title+'"  />'  );
    } );

    var table = $('#list-mentions').DataTable(tableConfig);
    // Apply the search
    table.columns().eq( 0 ).each( function ( colIdx ) {
        $( 'input', table.column( colIdx ).header() ).on( 'keyup change', function () {
            table
                .column( colIdx )
                .search( this.value )
                .draw();
        } );
    } );

    return table;
}


function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
