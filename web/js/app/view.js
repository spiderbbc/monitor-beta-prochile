
let id          = document.getElementById('alertId').value;
const baseUrl   = 'http://localhost/monitor-beta/web/monitor/api/mentions/';
let refreshTime = 5000;

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
	    	mentions:[]
	    }
	},
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