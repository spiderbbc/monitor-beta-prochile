
let id            = document.getElementById('alertId').value;
const baseUrlApi  = 'http://localhost/monitor-beta/web/monitor/api/mentions/';
const baseUrlView = 'http://localhost/monitor-beta/web/monitor/alert/';

let refreshTime = 10000;
let refreshTimeTable = 30000;



const count_mentions = Vue.component('total-mentions',{
	props: ['count'],
	data: function () {
	    return {
	    }
	},
	template: '#view-total-mentions',
});

const count_resources = Vue.component('total-resources',{
	props: ['value','resource','resourcescount'],
	template: '#view-total-mentions-resources',
	data(){
		return {
			columns:0,
			resourceId:''
		}
	},
	created(){
		this.colpropeties();
	},
	methods:{
		colpropeties(){
			var total_resources = Object.keys(this.resourcescount).length;
			var total = Math.round(12 / total_resources);
			this.columns =  'col-md-' + total + ' well';
		}
	},	
	computed:{
		fetchResourceName(){
			axios
		      .get(baseUrlApi + 'get-resource-id' + '?resourceName=' +this.resource)
		      .then(response => (this.resourceId = response.data.resourceId))
		      .catch(error => console.log(error))
		    var link = `${baseUrlView}resource?resourceId=${this.resourceId}&alertId=${id}`;  
		    return link;
		}
	}	
		
	
});

const listMentions = Vue.component('list-mentions',{
	template: '#list-mentions',
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
			//var table = initTable();
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
      					  height: 350
					});
		        }
		        
		    })
		},
		handlers(response){
			var words = response.map(function(r){
				r.handlers = {click: function() {
			      $("#list-mentions").DataTable().search(r.text).draw();
			    }};
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
	    	resourceName:null,
	    	model: [],
	    	date: null,
	    	count: 0
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
		    })

		}
	}
});

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

		setInterval(function () {
	      this.fetchIsData();
	      this.fetchResourceCount();
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
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'count-sources-mentions' + '?alertId=' +this.alertId)
		      .then(response => (this.resourcescount = response.data.resources))
		      .catch(error => console.log(error))
		}


	},
	components:{
		count_mentions,
		count_resources,
		listMentions,
		cloudWords,
		tableDate
	}	
});


function initSearchTable(){
	// Setup - add a text input to each footer cell
    $('#list-mentions thead th').each( function () {
        var title = $('#list-mentions tfoot th').eq( $(this).index() ).text();
        $(this).html(  '<input type="text" size="10" placeholder="Search '+title+'"  />'  );
    } );

    var table = $('#list-mentions').DataTable( {
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
	} );

	// Apply the search
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

