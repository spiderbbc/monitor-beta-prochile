let id = document.getElementById('alertId').value;
const origin = location.origin;
const baseUrlApi = `${origin}/monitor-beta/web/monitor/api/mentions/`;
const baseUrlDocument = `${origin}/monitor-beta/web/monitor/pdf/`;
const baseUrlView = `${origin}/monitor-beta/web/monitor/alert/`;
// 1000 = 1 seg
let refreshTime = 10000;
let refreshTimeTable = 40000;
let data_chart = new Object();
let controllerName = {
    "Twitter": "Twitter",
    "Live Chat": "Live-chat",
    "Live Chat Conversations": "Live-chat-conversations",
    "Facebook Comments": "Facebook-comments",
    "Facebook Comments": "Facebook-comments",
};
let tableConfig = {
    'scrollY': '400px',
    'scrollCollapse': true,
    "processing": true,
    "ajax": {
        'url': baseUrlApi + 'list-mentions?alertId=' + id,
        //"dataSrc": "mentions"
    },
    "fixedColumns": true,
    "columns": [{
        "data": "alertMention.resources.name",
        "width": "200px"
    }, {
        "data": "alertMention.term_searched",
        "width": "200px"
    }, {
        "data": "created_time",
        "type": "date",
        "render": function(data) {
            var a = new Date(data * 1000);
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            var year = a.getFullYear();
            var month = months[a.getMonth()];
            var date = a.getDate();
            var hour = a.getHours();
            var min = a.getMinutes();
            var sec = a.getSeconds();
            var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
            return time;
        },
        "width": "200px"
    }, {
        "data": "origin.name",
        "width": "200px"
    }, {
        "data": "origin.screen_name",
        "width": "200px"
    }, {
        "data": "subject",
        "width": "200px"
    }, {
        "data": "message_markup",
        "width": "40%"
    }, {
        "data": "url",
        "render": function(data) {
            var link = "-";
            if (data !== null && data !== '-') {
                var href = "<a href=";
                var url = data;
                var target = " target=" + '.$target.'
                var text = ">link</a>";
                link = href.concat(url, target, text);
                return link;
            }
            return '-'
        },
        "width": "10%"
    }]
};

let columnsName = ['col-md-12','col-md-6','col-md-4','col-md-3','col-md-5','col-md-2','col-sm-1 col-md-1 col-lg-1'];

let resourceIcons = {
    'Twitter': 'socicon-twitter',
    'Live Chat': 'socicon-googlegroups',
    'Live Chat Conversations': 'socicon-twitch',
    'Facebook Comments': 'socicon-facebook',
    'Instagram Comments': 'socicon-instagram',
    'Facebook Messages': 'socicon-messenger',
    'Excel Document': 'socicon-windows',
    'Web page': ''
}