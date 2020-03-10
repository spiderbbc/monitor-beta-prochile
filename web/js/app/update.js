

//const origin   = location.origin;
//const baseUrl  = `${origin}/${appId}/web/monitor/alert/`;


$('#social_resourcesId').on('select2:unselecting', function (e) {

    var resource = e.params.args.data;
    swal_modal_info(resource);
});


/**
 * [swal_modal_info informs the user of the days on which he should initiate an alert if a date is exceeded]
 * @param  {[type]} resource [resource name]
 * @param  {[type]} days     [total days]
 * @param  {[type]} days_ago [days ago]
 * @return {[type]}          [description]
 */
function swal_modal_info(resource) {
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
		if(!result.value){
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
		}else{
			var social = $('#social_resourcesId').select2('data');
			var value = [];
			for (var s = 0; s < social.length; s++) {
				value.push(social[s].id);
			}
			value.push(resource.id);

			$('#social_resourcesId').val(value).trigger('change');
		}
	});
}