'use strict'
//var element = $("#productsIds");
const baseUrl = 'http://localhost/monitor-beta/web/monitor/alert/';

//reset select2 values if previously selected 
/*
setInterval(function () {
    element.val(null).trigger('change');
    console.log(1);
}.bind(this), 3000);

//get plugin options
let dataSelect = eval(element.data('krajee-select2'));

//get kartik-select2 options
let krajeeOptions = element.data('s2-options');
console.log(krajeeOptions);

//add your options
dataSelect.multiple=true;

//apply select2 options and load select2 again
$.when(element.select2(dataSelect)).done(initS2Loading("productsIds", krajeeOptions));*/

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