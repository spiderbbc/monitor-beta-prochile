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


/**
 * [modalTwitter show modal alert when user click twitter in box resource indicating if range date is optimal]
 * @param  {[type]} event 
 */
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
				if (diff_end_date >= 7) {
					//swal_modal_error(days_ago);
					swal_modal_info(days_twitter,days_ago);
                    /*var current_values = social.val();
                    var index = current_values.indexOf("1");
                    social.val(index).trigger('change');*/

				}

				var diff_start_date = now.diff(beforeTime, "days");
				console.log(diff_start_date);
				if (diff_start_date >= 7) {
					swal_modal_info(days_twitter,days_ago);
				}

				/*if(diff_end_date < 0){
					swal_modal_info(days_twitter,days_ago);
				}*/

			}
		}
	}	
}

/**
 * [swal_modal_info show modal info indicating range date for twitter]
 * @param  {[type]} days_twitter [days twiiter api]
 * @param  {[type]} days_ago     [days ago for call twitter api]
 */
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
			$('#end_date').kvDatepicker('clearDates');
			
		}else{
			var social = $('#social_resourcesId');
            var current_values = social.val();
            current_values.splice( current_values.indexOf('1'), 1 );
            social.val(current_values).trigger('change');
		}
	});
}

/**
 * [swal_modal_error show error modal indicating to user not range date for twitter]
 * @param  {[type]} days_ago [days ago for call twitter api]
 */
function swal_modal_error(days_ago) {
	Swal.fire({
	  icon: 'error',
	  title: 'Opps',
	  html: "<b>Twitter API</b> no estara disponible para este rango de fechas <hr> realiza una búsqueda a partir de: <b>"+ days_ago +"</b>",
	})
}

/**
 * [validator_date change the end date based on the start date ]
 * @param  {[type]} event
 */
function validator_date(event) {
	
	$('#end_date').kvDatepicker('clearDates');
	$('#end_date').kvDatepicker('setStartDate',event.date);

}

