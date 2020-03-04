'use strict'
//var element = $("#productsIds");
const origin           = location.origin;
const baseUrl  = `${origin}/${appId}/web/monitor/alert/`;

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
			    	$("#productsIds").trigger('change');
			    	location.reload();
			    	this.msg = 'Reload';
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


let message_error_no_dates = "Debe de escojer fecha de Inicio y fecha Final";
let message_more_than_one_month= "Consultar las paginas web tiene que ser un rango menor de <b>1 mes</b>";




function modalReosurces(event) {
	
	var format = 'DD/MM/YYYY';
	
	var start_date = $('#start_date')[0].value;
	var end_date = $('#end_date')[0].value;
	var social = $('#social_resourcesId');

	
	var resource = event.params.data.text;
	console.log(resource);

	switch (resource){
		case "Web page":
		const days_web = 29;
		var start_date = $('#start_date')[0].value;
		var end_date = $('#end_date')[0].value;
		var afterTime = moment(start_date, format);

	

		if(start_date.length && end_date.length){
			var now = moment();
			var diff_start_date = now.diff(afterTime, "days");
			if (diff_start_date >= days_web) {
				var days_ago = moment().subtract(days_web, 'days').format(format);
				
				swal_modal_info(resource,days_web,days_ago);
				
			}
			
		}else{
			swal_modal('error','Opps',message_error_no_dates);
			clean_select2(social);
			
		}
		break;
	}
	
	
}




/**
 * [swal_modal_error fire up a simple swal modal]
 * @param  {[type]} icon    [succes,error,warning]
 * @param  {[type]} title   [title to content]
 * @param  {[type]} message [message to content]
 */
function swal_modal(icon,title,message) {
	Swal.fire({
	  icon: icon,
	  title: title,
	  html: message ,
	});
}


/**
 * [validator_date change the end date based on the start date ]
 * @param  {[type]} event
 */
function validator_date(event) {
	
	var start_date = $('#start_date').val().split("/").reverse().join("-");
	var end_date = $('#end_date').val().split("/").reverse().join("-");


	if (end_date != '') {
		if (moment(start_date).isAfter(end_date)) {
			Swal.fire({
			  icon: 'error',
			  title: 'Opps',
			  html: "Fecha Final no puede ser menor que Fecha de Inicio",
			});
			var date = $('#start_date').val();
			$('#end_date').kvDatepicker('update', date);
		}
	}
	$('#end_date').kvDatepicker('setStartDate',event.date);

	/*$('#end_date').kvDatepicker('clearDates');
	$('#end_date').kvDatepicker('setStartDate',event.date);*/

}


function clean_select2(social) {
	var current_values = social.val();
    current_values.splice( current_values.indexOf('1'), 1 );
    social.val(current_values).trigger('change');
}

function swal_modal_info(resource,days,days_ago) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: `<b>${resource}</b> realiza una búsqueda en una muestra de registros recientes publicados en los últimos ${days} días.<hr> La alerta comenzara a recabar data a partir ${days_ago} para ${resource}`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Si, Deseo cambiar la fecha!',
	  cancelButtonText: `Quitar ${resource} de los recursos!`
	}).then((result) => {
		if(result.value){
			//if yes
			$('#start_date').kvDatepicker.defaults.format = 'dd/mm/yyyy';
			$('#start_date').kvDatepicker('update', days_ago);
			$('#end_date').kvDatepicker('clearDates');
			
		}else{
			var social = $('#social_resourcesId');
            clean_select2(social);
		}
	});
}