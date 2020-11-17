

const baseDetailApi = `${origin}/${appId}/monitor/api/detail/`;

const apiClientView = axios.create({
  baseURL: baseUrlApi,
  withCredentials: false, // This is the default
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  timeout: 20000,
});

const apiClientDetail = axios.create({
  baseURL: baseDetailApi,
  withCredentials: false, // This is the default
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  timeout: 20000,
});

/**
 * Vue Component: vm
 *  call api to return number mentions
 * @param {Number} id
 */
function getCountMentions(id) {
  return apiClientView.get("/count-mentions?alertId=" + id);
}
/**
 * Vue Component: vm
 *  call api to return property box resources
 * @param {Number} id
 */
function getPropertySourceBox(id) {
  return apiClientView.get("/properties-source-box?alertId=" + id);
}

/**
 * Vue Component: modal-alert
 *  call api to return status for resources mentions
 * @param {Number} id
 */
function getStatusMentionsResources(id) {
  return apiClientView.get("/status-alert?alertId=" + id);
}

/**
 * Vue Component: list-emojis
 *  call api to return emojis count
 * @param {Number} id
 */
function getEmojis(id) {
  return apiClientView.get("/list-emojis?alertId=" + id);
}

/**
 * Vue Component: cloud-words
 *  call api to return cloud-words dictionaries mentions
 * @param {Number} id
 */
function getWords(id) {
  return apiClientView.get("/list-words?alertId=" + id);
}

/**
 * Vue Component: count-date-resources-chart
 *  call api to return date count
 * @param {Number} id
 */
function getCountDateResources(id) {
  return apiClientView.get("/mention-on-date?alertId=" + id);
}

/**
 * Vue Component: products-interations-chart
 *  call api to return product interations
 * @param {Number} id
 */
function getProductInterations(id) {
  return apiClientView.get("/product-interation?alertId=" + id);
}

/**
 * Vue Component: post-interation-chart
 *  call api to return post interations
 * @param {Number} id
 */
function getPostInterations(id) {
  return apiClientView.get("/top-post-interation?alertId=" + id);
}

/**
 * Vue Component: post-interation-chart
 *  call api to return post interations
 * @param {Number} id
 */
function getPostInterations(id) {
  return apiClientView.get("/top-post-interation?alertId=" + id);
}

/**
 * Vue Component: total-resources-chart
 *  call api to return total resource
 * @param {Number} id
 */
function getTotalResource(id) {
  return apiClientView.get("/count-sources-mentions?alertId=" + id);
}

/**
 * View: Detail
 * Vue Component: detail
 *  call api to return total by resource, id and terms
 * @param {Number} id
 * @param {Number} resourceId
 * @param {String} term
 */
function getCountMentionsDetail(alertid, resourceid, term) {
  return apiClientDetail.get(
    `count?alertId=${alertid}&resourceId=${resourceid}&term=${term}`
  );
}

/**
 * View: Detail
 * Vue Component: detail
 *  call api to return data for depend select2
 * @param {Number} id
 * @param {Number} resourceId
 * @param {String} term
 */
function getDataSelectDetail(alertid, resourceid, term) {
  return apiClientDetail.get(
    `select-depend?alertId=${alertid}&resourceId=${resourceid}&term=${term}`
  );
}

/**
 * View: Detail
 * Vue Component: box-detail
 *  call api to return box inf properties
 * @param {Number} id
 * @param {Number} resourceId
 * @param {String} term
 * @param {String} socialId
 */
function getBoxInfoDetail(alertid, resourceid, term, socialId) {
  return apiClientDetail.get(
    `box-info?alertId=${alertid}&resourceId=${resourceid}&term=${term}&socialId=${socialId}`
  );
}

/**
 * View: Detail
 * Vue Component: common-words-detail
 *  call api to return comon words
 * @param {Number} id
 * @param {Number} resourceId
 * @param {String} term
 * @param {String} socialId
 */
function getBoxCommonWordsDetail(alertid, resourceid, term, socialId) {
  return apiClientDetail.get(
    `common-words?alertId=${alertid}&resourceId=${resourceid}&term=${term}&socialId=${socialId}`
  );
}

/**
 * View: Detail
 * Vue Component: map-user
 *  call api to regions count on users
 * @param {Number} id
 * @param {Number} resourceId
 * @param {String} term
 * @param {String} socialId
 */
function getRegionLiveChatDetail(alertid, resourceid, term, socialId) {
  return apiClientDetail.get(
    `get-region-live-chat?alertId=${alertid}&resourceId=${resourceid}&term=${term}&socialId=${socialId}`
  );
}

/**
 * View: Detail
 * Vue Component: map-user
 *  call api to city count on users
 * @param {Number} id
 * @param {Number} resourceId
 * @param {Array} options
 */
function getCityLiveChatDetail(alertid, resourceid, options, socialId) {
  var options_string = JSON.stringify(options);
  return apiClientDetail.get(
    `get-city-live-chat?alertId=${alertid}&resourceId=${resourceid}&options=${options_string}&socialId=${socialId}`
  );
}
