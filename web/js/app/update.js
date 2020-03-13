

/**
 * [description] event unselecting of select2 reourceId when is fire delete resource from alert and his results
 * @param  {[type]} e) [even when is click on delete resource]
 */
$('#social_resourcesId').on('select2:unselecting', function (e) {
    var resource = e.params.args.data;
    swal_modal_info_resource(resource);
});

$('#productsIds').on('select2:unselecting', function (e) {
    var term = e.params.args.data;
    swal_modal_info_term(term);
});


$('#social_dictionaryId, #free_words, #competitors').on('select2:unselecting', function (e) {
    var resource = e.params.args.data;
    console.log(resource);
});


$('#social_dictionaryId, #free_words, #competitors').on('select2:select', function (e) {
    var resource = e.params.data;
    console.log(resource);
});

/**
 * [swal_modal_info informs the user of the days on which he should initiate an alert if a date is exceeded]
 * @param  {[type]} resource [resource name]
 * @param  {[type]} days     [total days]
 * @param  {[type]} days_ago [days ago]
 * @return {[type]}          [description]
 */
function swal_modal_info_resource(resource) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: ` ¿ Desea eliminar <b>${resource.text}</b> ? <hr> Se procedera a <b>eliminar</b> los resultados recabados por este recurso social`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: `No, Deseo conservar ${resource.text}!`,
	  cancelButtonText: `Quitar ${resource.text} de los recursos!`
	}).then((result) => {
		if ('value' in result) {
			if(result.value){
				reset_selector('#social_resourcesId',resource.id);
			}
		}
		if('dismiss' in result){
			// if user click out box modal
			if(result.dismiss == "backdrop"){
				reset_selector('#social_resourcesId',resource.id);
			}
			// delete term button danger
			if (result.dismiss == "cancel") {
				// delete result
				var data = {alertId: alertId,resourceId: resource.id};
				$.ajax({
			        url: origin + `/${appId}/web/monitor/alert/delete-resource-alert`,
			        data: data,
			        type: "GET",
			        dataType: "json",
			      }).done(function(data) {
			        if(data.status){
					    Swal.fire(
					      `${resource.text} Eliminado`,
					      'La Alerta cambio sus Redes Sociales',
					      'success'
					    );
		            }else{
		            	Swal.fire(
					      'Opss',
					      'No se pudo realizar la operacion',
					      'error'
					    );
					   // $($this).val(value).trigger('change');
		              
		            }
			    });
			}
		}
	});
}


function swal_modal_info_term(term) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: ` ¿ Desea eliminar <b>${term.text}</b> ? <hr> Se procedera a <b>eliminar</b> los resultados recabados por este termino`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: `No, Deseo conservar ${term.text}!`,
	  cancelButtonText: `Quitar ${term.text} de los terminos!`
	}).then((result) => {
		// checks if a action user
		if ('value' in result) {
			// click button info 
			if(result.value){
				reset_selector('#productsIds',term.id);	
			}

		}
		if('dismiss' in result){
			// if user click out box modal
			if (result.dismiss == "backdrop") {
				reset_selector('#productsIds',term.id);
			}
			// delete term button danger
			if(result.dismiss == "cancel"){
				// delete result
				var data = {alertId: alertId,termName: term.text};
				$.ajax({
			        url: origin + `/${appId}/web/monitor/alert/delete-term-alert`,
			        data: data,
			        type: "GET",
			        dataType: "json",
			      }).done(function(data) {
			        if(data.status){
					    Swal.fire(
					      `${term.text} Eliminado`,
					      'La Alerta cambio los terminos a buscar',
					      'success'
					    );
		            }else{
		            	Swal.fire(
					      'Opss',
					      'No se pudo realizar la operacion',
					      'error'
					    );
		              
		            }
			    });
			}
		}
		
	});
}


function reset_selector(selectId,termId) {
	
	var terms = $(selectId).select2('data');
	var value = [];
	for (var s = 0; s < terms.length; s++) {
		value.push(terms[s].id);
	}
	value.push(termId);

	$(selectId).val(value).trigger('change');
}