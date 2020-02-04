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
		
		var format = 'DD/MM/YYYY';
		var days_twitter = 7;

		var start_date = $('#start_date')[0].value;
		var end_date = $('#end_date')[0].value;

		var social = $('#social_resourcesId');

	

		if(start_date.length && end_date.length){
			var now = moment();

			var beforeTime = moment(start_date, format);
	  		var afterTime = moment(end_date, format);
			
			
			var days_ago = moment().subtract(7, 'days').format(format);


			if(moment().isBetween(beforeTime, afterTime)){
				var days = now.diff(beforeTime, "days");
				if(Math.sign(days) && days > 7){
					swal_modal_info(days_twitter,days_ago);
				}
			}else{
				var diff_end_date = now.diff(afterTime, "days");
				if (diff_end_date > 7) {
					
					swal_modal_error(days_ago);
					
                    var current_values = social.val();
                    var index = current_values.indexOf("1");
                   
                    social.val(index).trigger('change');

				}else{
					swal_modal_info(days_twitter,days_ago);
				}

			}
		}
	}	
}


function swal_modal_info(days_twitter,days_ago) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: "<b>Twitter API</b> realiza una búsqueda en una muestra de Tweets recientes publicados en los últimos "+ days_twitter +" días.  como parte del conjunto 'público' de API. <hr> La alerta comenzara a recabar data a partir " + days_ago + " para Twitter",
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Si, Deseo cambiar la fecha!',
	  cancelButtonText: 'Quitar Twitter de los recursos!'
	}).then((result) => {
		if(result.value){
			//if yes
			$('#start_date').kvDatepicker.defaults.format = 'dd/mm/yyyy';
			$('#start_date').kvDatepicker('update', days_ago);
			
		}else{
			var social = $('#social_resourcesId');
            var current_values = social.val();
            console.log(current_values);
            current_values.splice( current_values.indexOf('1'), 1 );
            social.val(current_values).trigger('change');
		}
	});
}

function swal_modal_error(days_ago) {
	Swal.fire({
	  icon: 'error',
	  title: 'Opps',
	  html: "<b>Twitter API</b> no estara disponible para este rango de fechas <hr> realiza una búsqueda a partir de: <b>"+ days_ago +"</b>",
	})
}


