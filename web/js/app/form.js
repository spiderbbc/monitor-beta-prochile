'use strict'
//var element = $("#productsIds");
const origin   = location.origin;
const baseUrl  = `${origin}/${appId}/web/monitor/alert/`;

var root = location.pathname.split("/")[1];
var appId = root != "web" ? `${root}/web` : "web";

let message_error_no_dates = "Debe de escojer fecha de Inicio y fecha Final";
let message_more_than_one_month= "Consultar las paginas web tiene que ser un rango menor de <b>1 mes</b>";

var product_description = document.getElementById('product_description');
var inputOptions = {};

$('#urls').on('select2:unselecting', function (e) {
    var urls = $(this).val();
    if (urls.length == 1) {
    	remove_value_selector('social_resourcesId','4');
    }
    
});



/**
 * [event when selecting select2 of urls: adding web page to select2 resourceID]
 */
$('#urls').on('select2:select', function (e) {
    var selectId = 'social_resourcesId';
    var data = {
        id: '4',
        text: 'Paginas Webs'
    };
    add_options_select(selectId,data);
    
});
/**
 * [event when unselecting select2 of resourceId: if resource is web page clean the select web urls]
 */
$('#social_resourcesId').on('select2:unselecting', function (e) {
    var resource = e.params.args.data;
    if(resource.text == "Paginas Webs"){
    	var urls = $('#urls')
    	urls.val(null).trigger('change'); // Select the option with a value of '1'
	}
	var resourceNameId = $(this).val();
	if(resourceNameId.indexOf("2") < 0 && resourceNameId.indexOf("7") < 0 && resourceNameId.indexOf("8") < 0){
		product_description.value = '';
	}
});

/**
 * [add_options_select adding data to select2]
 * @param  {[type]} string [id to select2]
 * @param  {[type]} obj [data to select2]
 */
function add_options_select(selectId,data) {
	var social = $('#'+selectId);
	var current_values = social.val();

    // Set the value, creating a new option if necessary
    if (social.find('option[value=' + data.id +']').length) {
        current_values.push(data.id);
        social.val(current_values).trigger('change');
    } else { 
        // Create a DOM Option and pre-select by default
        var newOption = new Option(data.text, data.id, true, true);
        // Append it to the select
        social.append(newOption).trigger('change');
    }
}

/**
 * [modalReosurces method that validates according to the time and the social network chosen the type of message to show the user]
 * @param  {[type]} event [event calendar]
 */
function modalReosurces(resourceName) {
	
	var format = 'DD/MM/YYYY';
	
	var start_date = $('#start_date')[0].value;
	var end_date = $('#end_date')[0].value;
	var social = $('#social_resourcesId').select2('data');

	
	var resource = resourceName;

	switch (resource){
		case "Web page":
		const days_web = 29;
		var start_date = $('#start_date')[0].value;
		var end_date = $('#end_date')[0].value;

		if(start_date.length && end_date.length){
			
			var days_ago = check_if_it_exceeds_the_limit(start_date,days_web,'days');
			if (days_ago) {
				swal_modal_info(resource,days_web,days_ago);
			}
			
		}else{
			swal_modal('error','Opps',message_error_no_dates);
			clean_select2(social,resource);
			
		}
		break;

		case "Twitter":
		const days_twitter = 7;
		if(start_date.length && end_date.length){

			var days_ago = check_if_it_exceeds_the_limit(start_date,days_twitter,'days');
			if (days_ago) {
				swal_modal_info(resource,days_twitter,days_ago);
			}
			
		}else{
			swal_modal('error','Opps',message_error_no_dates);
			clean_select2(social,resource);
		}
		break;
		case "Instagram Comments":
			if(product_description.value == ''){
				swal_modal_options_accounts(social,resource);
			}
			
		break;
		
		case "Facebook Comments":
			if(product_description.value == ''){
				swal_modal_options_accounts(social,resource);
			}
			
		break;

		case "Facebook Messages":
			if(product_description.value == ''){
				swal_modal_options_accounts(social,resource);
			}
			
		break;

		
	}
}

/**
 * [check_if_it_exceeds_the_limit if the date exceeds the limit returns the day in which it is within the range of the limit]
 * @param  {[type]} start_date [start date alert]
 * @param  {[type]} limit      [number of day or month]
 * @param  {[type]} period     [ej: 'days' or 'month']
 * @return {[type]}            [the optimal day to start the alert]
 */
function check_if_it_exceeds_the_limit(start_date,limit,period){

	var now = moment();
	var format = 'DD/MM/YYYY';
	var days_ago = null;

	var afterTime = moment(start_date, format);
	var diff_start_date = now.diff(afterTime, period);

	if (diff_start_date > limit) {
		days_ago = moment().subtract(limit, period).format(format);
	}

	return days_ago;
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


function swal_modal_options_accounts(social,resource){
	// var inputOptions = {
	// 	'101330848134001': 'Prochile USA',
	// 	'169441517247': 'Mundo LG',
	// };
	
	if(Object.entries(inputOptions).length === 0){
		var data = {userId: userId};
		$.ajax({
			url: origin + `/${appId}/monitor/alert/get-credentials`,
			data: data,
			type: "GET",
			dataType: "json",
		  }).done(function(data) {
			if(data.credential){
				if(data.credential.hasOwnProperty('access_secret_token')){
					var accessToken = data.credential.access_secret_token;
					var accounts = makeRequestFacebook("me?fields=accounts{id,name}",accessToken);
					accounts.then(function(result){
						result.accounts.data.forEach(function(element){
							inputOptions[element.id] = element.name;
						});
						swalmodalAccounts(inputOptions,social,resource);
					},function(e){
						swal_modal("error","Error","bueno, esto es embarazoso tenemos un problema con facebook");
						clean_select2(social,resource);
					});
					 
				}else{
					Swal.fire(
						'Opss',
						'No se pudo realizar la operacion',
						'error'
					);	
				}
			}else{
				Swal.fire(
				  'Opss',
				  'No se pudo realizar la operacion',
				  'error'
				);
			  
			}
		});
	}else{
		swalmodalAccounts(inputOptions,social,resource);
	}



	
}

function swalmodalAccounts(inputOptions,social,resource){
	Swal.fire({
		icon: "warning",
		title: 'Select Account',
		input: 'select',
		inputOptions: inputOptions,
		inputPlaceholder: 'Select Account',
		showCancelButton: true,
		inputValidator: function (value) {
		  return new Promise(function (resolve, reject) {
			if (value) {
			  resolve()
			} else {
			  //reject('You need to select one Account :)')
			  //clean_select2(social,resource);
			}
		  })
		}
	  }).then(function (result) {
		//console.log(result);    
		if(result.isDismissed){
			
			clean_select2(social,resource);
		}else{
			
			product_description.value = result.value;
			Swal.fire({
				icon: 'success',
				html: 'You selected: ' + inputOptions[result.value],
			  })
		} 
	  })

}

/**
 * [validator_date change the end date based on the start date ]
 * @param  {[type]} event
 */
async function validator_date(event) {
	
	var start_date = $('#start_date').val().split("/").reverse().join("-");
	var end_date = $('#end_date').val().split("/").reverse().join("-");

	// check if have resource clicked
	var resources = $('#social_resourcesId').select2('data');
	for (var i = 0; i < resources.length; i++) {
		var resourceName = resources[i].text;
		modalReosurces(resourceName);
		await new Promise(r => setTimeout(r, 4000));
	}


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

}

/**
 * [clean_select2 clean select2 select option]
 * @param  {[type]} social [element select2]
 */
function clean_select2(social,resource_to_delete = null) {

	if(Array.isArray(social)){
		var new_values = [];
		for (var i = 0; i < social.length; i++) {
			if (resource_to_delete != social[i].text) {
				new_values.push(social[i].id);
			}
		}
		var social = $('#social_resourcesId');
		social.val(new_values).trigger('change');
	}

	/*var current_values = social.val();
    console.log(current_values);
    current_values.splice( current_values.indexOf('1'), 1 );
    console.log(current_values);
    social.val(current_values).trigger('change');*/
}
/**
 * [swal_modal_info informs the user of the days on which he should initiate an alert if a date is exceeded]
 * @param  {[type]} resource [resource name]
 * @param  {[type]} days     [total days]
 * @param  {[type]} days_ago [days ago]
 * @return {[type]}          [description]
 */
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
			//$('#end_date').kvDatepicker('clearDates');
			
		}else{
			var social = $('#social_resourcesId').select2('data');
			//var social = $('#social_resourcesId');
            clean_select2(social,resource);
		}
	});
}

/**
 * [remove_value_selector delete value to select2]
 * @param  {[type]} selectId [description]
 * @param  {[type]} termId   [description]
 * @return {[type]}          [description]
 */
function remove_value_selector(selectId,termId) {
	var selectId = `#${selectId}`;
	var terms = $(selectId).select2('data');
	var value = [];
	for (var s = 0; s < terms.length; s++) {
		if(terms[s].id != termId){
			value.push(terms[s].id);
		}
	}
	$(selectId).val(value).trigger('change');
}

function get(edge,accessToken) {
	
	// var urlEndPoint = "https://graph.facebook.com";
	// var apiVersion = "v9.0";
    // var url = urlEndPoint + "/" + apiVersion + "/" + edge;
    
	//var response = UrlFetchApp.fetch(encodeURI("" + url)).getContentText();
	// var response_data = null;
	// var response =  fetch(encodeURI("" + url), {
	// method: "GET",
	// headers: { Authorization: 'Bearer ' + accessToken}
	// })
	// .then(response => response.json()) 
	// .then(function(data){
	// 	response_data = data;
	// }) 
	// .catch(err => console.log(err));

	
	// console.log(response_data);
    //return JSON.parse(response);
}



async function makeRequestFacebook(edge,accessToken) {

	var urlEndPoint = "https://graph.facebook.com";
	var apiVersion = "v9.0";
	var url = urlEndPoint + "/" + apiVersion + "/" + edge;
	
    const config = {
        method: 'get',
        url: url,
        headers: { Authorization: 'Bearer ' + accessToken}
    }

    let res = await axios(config)

    return res.data;
}