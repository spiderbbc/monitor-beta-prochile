const topicsView = Vue.component('cloud-view',{
	'template' : '#cloud-view',
	'data': function () {
	    return {
	    	resourcesObj: [],
	    	loaded: false
	    }
	},
	mounted(){
		this.fetchNumbersResources();

	},
	methods: {
		fetchNumbersResources(){
			axios.get(baseUrlApi + 'numbers-resources' + '?topicId=' + id)
		      .then(response => {
		      	if (response.status == 200) {
		      		this.setResourceObj(response.data.topic);
		      	}
		      })
		      .catch(error => console.log(error))
		},
		setResourceObj(topic){
			var sources = topic.sources;
			console.log(sources.length);
			if (sources.length) {
				for (var s = 0; s < sources.length; s++) {
					if (sources[s].mTopicsStadistics.length) {
						var obj = {
							'id': sources[s].id,
							'name': sources[s].name,
						}
						this.resourcesObj.push(obj);
					}
				}
				if (this.resourcesObj.length) {
					this.loaded = true;
				}
			}
		}
	}
});

const cloud = Vue.component('cloud-words',{
	'template' : '#cloud-words',
	'props': ['resourceId','name'],
	'data': function () {
	    return {
	    	loaded: false
	    }
	},
	mounted(){
		this.fetchWords();
	},
	methods:{
		fetchWords(){
			axios.get(baseUrlApi + 'cloud-word' + '?topicId=' + id + '&resourceId='+ this.resourceId)
		      .then(response => {
		      	if (response.status == 200) {
		      		this.loaded = true; 
		      		var some_words_with_same_weight =
					    $("#jqcloud"+this.resourceId).jQCloud(response.data.words, {
					      width: 1000,
      					  height: 350,
      					  delay: 50
					});

		      	}
		      })
		      .catch(error => console.log(error))

		}
	}
	
});

const dictionariesCloud = Vue.component('cloud-dictionaries',{
	'template' : '#cloud-dictionaries',
	//'props': ['resourceId','name'],
	'data': function () {
	    return {
	    	loaded: true
	    }
	},
	mounted(){
		this.fetchWordsDictionaries();
		
	},
	methods:{
		fetchWordsDictionaries(){
			axios.get(baseUrlApi + 'cloud-dictionaries' + '?topicId=' + id)
		      .then(response => {
		      	if (response.status == 200 && response.data.model.length) {
		      		 
		      		$("#jqcloud-words-dictionaries").jQCloud(response.data.model, {
					      width: 1000,
      					  height: 350,
      					  delay: 50
					});
					//this.loaded = true;    
					   
		      	}else{
		      		this.loaded = false; 	
		      	}
		      })
		      .catch(error => console.log(error))

		}
	}
	
});


const dateWordsHistoria = Vue.component('words-history',{
	'template' : '#words-history',
	data: function () {
	    return {
	    	loaded: true,
	    	data: false
	    }
	},
	mounted(){
		this.fetchWordsHistory();
		
		
	},
	methods: {
		fetchWordsHistory(){
			$(document).ready(function() {
				axios.get(baseUrlApi + 'stadistics-date' + '?topicId=' + id)
			      .then(response => {
			      	if (response.status == 200 && response.data.period.length && response.data.seriesWords.length) {
			      		console.log(response.data.seriesWords);
			      		$( document ).ready(function() {
				      		Highcharts.chart('history', {
							    chart: {
							        type: 'line'
							    },
							    title: {
							        text: 'Historial de Fechas por Terminos'
							    },
							    subtitle: {
							        text: ''
							    },
							    xAxis: {
							        categories: response.data.period
							    },
							    yAxis: {
							        title: {
							            text: 'Total'
							        }
							    },
							    plotOptions: {
							        line: {
							            dataLabels: {
							                enabled: false
							            },
							            enableMouseTracking: true
							        }
							    },
							    series: response.data.seriesWords
							});
			      	
				      	});	
			      	}
			      })
			      .catch(error => console.log(error))
			});
		},
		draw(data){
			console.log(this.data);
		}
	}
});

const vue = new Vue({
	'el' : '#topics-view',
	'components':{
		topicsView
	}
});

