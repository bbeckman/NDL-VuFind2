/*global VuFind*/
finna.searchTabsRecommendations = (function() {
    var initSearchTabsRecommendations = function() {
        var holder = $('#search-tabs-recommendations-holder');
        if (!holder[0]) {
            return;
        }
        var url = VuFind.getPath() + '/AJAX/JSON?method=getSearchTabsRecommendations';
        var searchHash = holder.data('search-hash');
        var limit = holder.data('limit');
        var jqxhr = $.getJSON(url, {searchHash: searchHash, limit: limit})
        .done(function(response) {
            var holder = $('#search-tabs-recommendations-holder');
            holder.html(response.data);
            finna.layout.initTruncate(holder);
            finna.openUrl.initLinks();
            finna.layout.initSaveRecordLinks(holder);
            finna.itemStatus.initItemStatuses(holder);
            finna.itemStatus.initDedupRecordSelection(holder);
            finna.layout.checkSaveStatuses(holder);
        })
        .fail(function(response, textStatus) {
            console.log(response, textStatus);
        });
    };

    var my = {
        init: function() {
            initSearchTabsRecommendations();
        }
    };

    return my;

})(finna);
