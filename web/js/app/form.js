'use strict'
//var element = $("#productsIds");
const origin           = location.origin;
const baseUrl  = `${origin}/monitor-beta/web/monitor/alert/`;


const reloadButton = Vue.component('sync-product',{
	template: '#sync-product-id',
	data(){
		return {
			msg: 'Reload'
		}
	},
	methods: {
		reload(){
			this.msg = 'Loading';
			fetch(`${baseUrl}reload-products`)
			  .then(response => {
			    return response.json()
			  })
			  .then(data => {
			    // Work with JSON data here
			    if(data.status){
			    	$("#alerts-productsids").trigger('change');
			    	location.reload();
			    }
			  })
			  .catch(err => {
			    // Do something for an error here
			})
		}
	}
});


// vue here
var vm = new Vue({
	el: '#views-alert',
	data: {
		loaded: true
	},
	mounted(){
	    

	},
	components:{
		reloadButton
	}	
});


// alertResourceId select2 with alert when user click in twitter
function modalTwitter(event) {
	var resource = event.params.data.text;

	if(resource == "Twitter"){
		var start_date = $('#start_date')[0].value;
	

	if(start_date.length){
		var now = moment();

		var moment_start_date = moment(start_date,'DD/MM/YYYY');
		
		var days = now.diff(moment_start_date, "days");
		var days_ago = now.subtract(7, 'days').calendar();

		if(Math.sign(days) && days > 7){
			Swal.fire({
				  icon: 'warning',
				  title: 'Oops...',
				  html: "<b>Twitter API</b> realiza una búsqueda en una muestra de Tweets recientes publicados en los últimos 7 días.  como parte del conjunto 'público' de API. <hr> La alerta comenzara a recabar data a partir " + days_ago + " para Twitter",
				  footer: ''
				})
			}

		}
	}
}




