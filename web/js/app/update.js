

/**
 * [description] event unselecting of select2 reourceId when is fire delete resource from alert and his results
 * @param  {[type]} e) [even when is click on delete resource]
 */
$('#social_resourcesId').on('select2:unselecting', function (e) {
    var resource = e.params.args.data;
    var id = $(this).attr('id');
    swal_modal_info_resource(resource,id);
});
/**
 * [description] event unselecting of select2 productsIds AKA terms to search, when is fire delete terms from alert and his results
 * @param  {[type]} e) [even when is click on delete resource]
 */
$('#productsIds').on('select2:unselecting', function (e) {
    var term = e.params.args.data;
    var id = $(this).attr('id');
    swal_modal_info_term(term,id);
});
/**
 * [description] event select of select2, when is fire add filter from alert and his results
 * @param  {[type]} e) [even when is click on delete resource]
 */
$('#social_dictionaryId,#free_words, #competitors').on('select2:select', function (e) {
    var id = $(this).attr('id');
    var data = $('#'+id).select2('data');
    var term = data[data.length -1].text;
    var dictionaryName = $(this).attr('resourceName');
    //console.log(term,id,dictionaryName);
    swal_modal_filter_add(term,id,dictionaryName);
});

$('#language').on('select2:select', function (e) {
    var id = $(this).attr('id');
    var lang = $('#'+id).select2('data');
    lang = lang[0];
    swal_modal_change_language(lang,id);
});

/**
 * [description] event unselecting of select2 , when is fire delete filter from alert and his results
 * @param  {[type]} e) [even when is click on delete resource]
 */
$('#social_dictionaryId,#free_words, #competitors').on('select2:unselecting', function (e) {
    var filter = e.params.args.data;
    var id = $(this).attr('id');

    var dictionaryName = ($(this).attr('resourceName') == "dictionaries") ? filter.text : $(this).attr('resourceName');
    swal_modal_filter_delete(filter,id,dictionaryName);
});






/**
 * [swal_modal_info informs the user of the days on which he should initiate an alert if a date is exceeded]
 * @param  {[type]} resource [resource name]
 * @param  {[type]} days     [total days]
 * @param  {[type]} days_ago [days ago]
 * @return {[type]}          [description]
 */
function swal_modal_info_resource(resource,id) {
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
				add_selector(id,resource.id);
			}
		}
		if('dismiss' in result){
			// if user click out box modal
			if(result.dismiss == "backdrop"){
				add_selector(id,resource.id);
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

/**
 * [swal_modal_info_term modal info to delete terms to search]
 * @param  {[type]} term [description]
 * @return {[type]}      [description]
 */
function swal_modal_info_term(term,id) {
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
				add_selector(id,term.id);	
			}

		}
		if('dismiss' in result){
			// if user click out box modal
			if (result.dismiss == "backdrop") {
				add_selector(id,term.id);
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


/**
 * [swal_modal_filter_delete modal info to delete filter to search]
 * @param  {[type]} term [description]
 * @return {[type]}      [description]
 */
function swal_modal_filter_delete(filter,id,dictionaryName) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: ` ¿ Desea eliminar <b>${filter.text}</b> ? <hr> Se procedera a <b>eliminar</b> los resultados recabados por este filtro`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: `No, Deseo conservar "${filter.text}"!`,
	  cancelButtonText: `Quitar "${filter.text}" como filtro!`
	}).then((result) => {
		// checks if a action user
		if ('value' in result) {
			// click button info 
			if(result.value){
				add_selector(id,filter.id);	
			}

		}
		if('dismiss' in result){
			// if user click out box modal
			if (result.dismiss == "backdrop") {
				add_selector(id,filter.id);
			}
			// delete filter button danger
			if(result.dismiss == "cancel"){
				// delete result
				var data = {alertId: alertId,dictionaryName:dictionaryName,filterName: filter.text};
				
				$.ajax({
			        url: origin + `/${appId}/web/monitor/alert/delete-filter-alert`,
			        data: data,
			        type: "GET",
			        dataType: "json",
			      }).done(function(data) {
			        if(data.status){
					    Swal.fire(
					      `Eliminado`,
					      `La Alerta elimino el filtro <b>${filter.text} </b>`,
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

/**
 * [swal_modal_filter_add modal info to add filter to search]
 * @param  {[type]} term [description]
 * @return {[type]}      [description]
 */
function swal_modal_filter_add(term,id,dictionaryName) {
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: ` ¿ Desea agregar <b>${term}</b> ? <hr> Se procedera a <b>agregar</b> los resultados recabados por este filtro`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: `Si, Deseo conservar "${term}"!`,
	  cancelButtonText: `Quitar "${term}" como filtro!`
	}).then((result) => {
		console.log(result);
		// checks if a action user
		if ('value' in result) {
			// click button info 
			if(result.value){

				var data = {alertId: alertId,dictionaryName:dictionaryName,filterName: term};
				console.log(data);
				
				$.ajax({
			        url: origin + `/${appId}/web/monitor/alert/add-filter-alert`,
			        data: data,
			        type: "GET",
			        dataType: "json",
			      }).done(function(data) {
			        if(data.status){
					    Swal.fire(
					      `Añadido`,
					      `La Alerta añadio el filtro <b>${term} </b>`,
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
		if('dismiss' in result){
			// if user click out box modal
			if (result.dismiss == "backdrop") {
				remove_value_selector(id,term);
			}
			// delete filter button danger
			if(result.dismiss == "cancel"){
				// delete result
				remove_value_selector(id,term);
			}
		}
		
	});
}


function swal_modal_change_language(lang,id){
	Swal.fire({
	  icon: 'warning',
	  title: 'Oops...',
	  html: ` ¿ Desea cambiar el Lenguaje a <b>${lang.text}</b> ? <hr> Se procedera a <b>reiniciar</b> las busquedas en base al cambio de idioma`,
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: `Si, Deseo conservar "${lang.text}"!`,
	  cancelButtonText: `Quitar "${lang.text}" como Lenguaje!`
	}).then((result) => {
		//console.log(result);
		// checks if a action user
		if ('value' in result) {
			// click button info 
			if(result.value){
				var data = {alertId: alertId,lang:lang.id};
				console.log(data);
				
				$.ajax({
			        url: origin + `/${appId}/web/monitor/alert/change-lang-alert`,
			        data: data,
			        type: "GET",
			        dataType: "json",
			      }).done(function(data) {
			        if(data.status == 'change'){
					    Swal.fire(
					      `Cambiado`,
					      `La Alerta cambio el lenguaje a <b>${lang.text} </b>`,
					      'success'
					    );
		            }
			    });
				
			}

		}
		if('dismiss' in result){
			// if user click out box modal
			if (result.dismiss == "backdrop") {
				restore_simple_select(id,lang.id);
			}
			// delete filter button danger
			if(result.dismiss == "cancel"){
				// delete result
				
				restore_simple_select(id,lang.id);
			}
		}
		
	});

}
/**
 * [add_selector add value to select2]
 * @param  {[type]} selectId [description]
 * @param  {[type]} termId   [description]
 * @return {[type]}          [description]
 */
function add_selector(selectId,termId) {
	
	var selectId = `#${selectId}`;
	var terms = $(selectId).select2('data');
	var value = [];
	for (var s = 0; s < terms.length; s++) {
		value.push(terms[s].id);
	}
	value.push(termId);
	$(selectId).val(value).trigger('change');
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

function restore_simple_select(id,value){
	value = (value != 1) ? 1:0;
	$('#'+id).val(value); // Select the option with a value of '1'
	$('#'+id).trigger('change'); // Notify any JS components that the value changed
}