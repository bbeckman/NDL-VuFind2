/*global VuFind, finna */
finna.changeHolds = (function finnaChangeHolds() {
  function setupChangeHolds() {
    var errorOccured = $('<div></div>').attr('class', 'alert alert-danger hold-change-error').text(VuFind.translate('error_occurred'));

    var changeHolds = $('.changeHolds');
    changeHolds.click(function onClickChangeHolds() {
      var hold = $(this);
      $('.hold-change-success').remove();
      $('.hold-change-error').remove();
      var pickupLocations = $(this).find('.pickup-locations');
      if (!pickupLocations.data('populated')) {
        pickupLocations.data('populated', 1);
        var spinnerLoad = $(this).find('.pickup-location-load-indicator');
        spinnerLoad.removeClass('hidden');
        var recordId = $(this).data('record-id');
        var requestId = $(this).data('request-id');
        var params = {
          method: 'getRequestGroupPickupLocations',
          id: recordId,
          requestGroupId: '0'
        };
        $.ajax({
          data: params,
          dataType: 'json',
          cache: false,
          url: VuFind.path + '/AJAX/JSON'
        })
          .done(function onPickupLocationsDone(response) {
            $.each(response.data.locations, function addPickupLocation() {
              var item = $('<li class="pickupLocationItem" role="menuitem"></li>')
                .data('locationId', this.locationID).data('locationDisplay', this.locationDisplay).data('requestId', requestId).data('hold', hold).click(pickupSubmitHandler);
              var text = $('<a></a>').text(this.locationDisplay);
              item.append(text);
              pickupLocations.append(item);
            });
            spinnerLoad.addClass('hidden');
          })
          .fail(function onPickupLocationsDone() {
            spinnerLoad.addClass('hidden');
            changeHolds.append(errorOccured);
            pickupLocations.data('populated', 0);
          });
      }
    });

    $('.hold-status-freeze').click(function onClickHoldFreeze() {
      var container = $(this).closest('.change-hold-status');
      var requestId = container.data('request-id');
      changeHoldStatus(container, requestId, 1);
      return false;
    });

    $('.hold-status-release').click(function onClickHoldRelease() {
      var container = $(this).closest('.change-hold-status');
      var requestId = container.data('request-id');
      changeHoldStatus(container, requestId, 0);
      return false;
    });

    function pickupSubmitHandler() {
      $().dropdown('toggle');
      var selected = $(this);
      var requestId = selected.data('requestId');
      var locationId = selected.data('locationId');
      var locationDisplay = selected.data('locationDisplay');
      var hold = selected.data('hold');

      var spinnerChange = hold.find('.pickup-change-load-indicator');
      spinnerChange.removeClass('hidden');

      var pickupLocationsSelected = hold.find('.pickupLocationSelected');
      pickupLocationsSelected.text(locationDisplay);

      var params = {
        method: 'changePickupLocation',
        requestId: requestId,
        pickupLocationId: locationId
      };
      $.ajax({
        data: params,
        dataType: 'json',
        cache: false,
        url: VuFind.path + '/AJAX/JSON'
      })
        .done(function onChangePickupLocationDone(response) {
          spinnerChange.addClass('hidden');
          if (response.data.success) {
            var success = $('<div></div>').attr('class', 'alert alert-success hold-change-success').text(VuFind.translate('change_hold_success'));
            hold.closest('.pickup-location-container').append(success);
          } else {
            hold.closest('.pickup-location-container').append(errorOccured);
          }
        })
        .fail(function onChangePickupLocationFail() {
          spinnerChange.addClass('hidden');
          hold.append(errorOccured);
        });
    }

    function changeHoldStatus(container, requestId, frozen) {
      var spinnerChange = container.find('.status-change-load-indicator');

      $('.hold-change-success').remove();
      $('.hold-change-error').remove();
      spinnerChange.removeClass('hidden');

      var params = {
        method: 'changeRequestStatus',
        requestId: requestId,
        frozen: frozen
      };
      $.ajax({
        data: params,
        dataType: 'json',
        cache: false,
        url: VuFind.path + '/AJAX/JSON'
      })
        .done(function onChangeRequestStatusDone(response) {
          spinnerChange.addClass('hidden');
          if (response.data.success) {
            if (frozen) {
              container.find('.hold-status-active').addClass('hidden');
              container.find('.hold-status-frozen').removeClass('hidden');
            } else {
              container.find('.hold-status-active').removeClass('hidden');
              container.find('.hold-status-frozen').addClass('hidden');
            }
          } else {
            container.append(errorOccured);
          }
        })
        .fail(function onChangeRequestStatusFail() {
          spinnerChange.addClass('hidden');
          container.append(errorOccured);
        });
    }
  }

  var my = {
    init: function init() {
      setupChangeHolds();
    }
  };

  return my;

})();