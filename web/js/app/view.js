
let id          = document.getElementById('alertId').value;
const baseUrl   = 'http://localhost/monitor-beta/web/monitor/api/mentions/';
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
		      .get(baseUrl + 'get-resource-id' + '?resourceName=' +this.resource)
		      .then(response => (this.resourceId = response.data.resourceId))
		      .catch(error => console.log(error))
		    var link = `http://localhost/monitor-beta/web/monitor/alert/resource?resourceId=${this.resourceId}&alertId=${id}`;  
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
			console.log(1);
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
		      .get(baseUrl + 'count-mentions' + '?alertId=' +this.alertId)
		      .then(response => (this.count = response.data.count))
		      .catch(error => console.log(error))
		    if(this.count > 0){
		    	this.isData = true; 
		    } 
		},
		fetchResourceCount(){
			axios
		      .get(baseUrl + 'count-sources-mentions' + '?alertId=' +this.alertId)
		      .then(response => (this.resourcescount = response.data.resources))
		      .catch(error => console.log(error))
		}


	},
	components:{
		count_mentions,
		count_resources,
		listMentions
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
	    	'url': 'http://localhost/monitor-beta/web/monitor/api/mentions/list-mentions?alertId=' + id,
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

/*function initTable(){
	return $('#list-mentions').DataTable( {
		"initComplete": function () {
	        var api = this.api();
	        api.$('td').click( function () {
	            api.search( this.innerHTML ).draw();
	        } );
	    },
		"processing": true,
	    "ajax": {
	    	'url': 'http://localhost/monitor-beta/web/monitor/api/mentions/list-mentions?alertId=' + id,
	    	//"dataSrc": "mentions"
	    },
	    "columns": [
	        { "data": "alertMention.resources.name" },
	        { "data": "alertMention.term_searched" },
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
	        	}
	        },
	        {"data": "origin.name"},
	        {"data": "origin.screen_name"},
	        { "data": "subject" },
	        { "data": "message_markup" },
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
	               
	        	}

	         }
	    ]
	} );
}
*/
// datatable here
/*var table = $('#list-mentions').DataTable( {
	"initComplete": function () {
        var api = this.api();
        api.$('td').click( function () {
            api.search( this.innerHTML ).draw();
        } );
    },
	"processing": true,
    "ajax": {
    	'url': 'http://localhost/monitor-beta/web/monitor/api/mentions/list-mentions?alertId=' + id,
    	//"dataSrc": "mentions"
    },
    "columns": [
        { "data": "alertMention.resources.name" },
        { "data": "alertMention.term_searched" },
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
        	}
        },
        {"data": "origin.name"},
        {"data": "origin.screen_name"},
        { "data": "subject" },
        { "data": "message_markup" },
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
               
        	}

         }
    ]
} );

setInterval( function () {
	console.log(1);
    table.ajax.reload();
}, 200000 );*/
