/*global finna, finnaCustomInit */
var finna = (function finnaModule() {

  var my = {
    init: function init() {
      // List of modules to be inited
      var modules = [
        'advSearch',
        'autocomplete',
        'contentFeed',
        'common',
        'changeHolds',
        'dateRangeVis',
        'feed',
        'feedback',
        'imagePopup',
        'itemStatus',
        'layout',
        'myList',
        'openUrl',
        'organisationList',
        'primoAdvSearch',
        'record',
        'searchTabsRecommendations',
        'StreetSearch',
        'finnaSurvey'
      ];

      $.each(modules, function initModule(ind, module) {
        if (typeof finna[module] !== 'undefined') {
          finna[module].init();
        }
      });
    }
  };

  return my;
})();

$(document).ready(function onReady() {
  finna.init();

  // init custom.js for custom theme
  if (typeof finnaCustomInit !== 'undefined') {
    finnaCustomInit();
  }
});
