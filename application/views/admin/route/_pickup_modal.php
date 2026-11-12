<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/leaflet/leaflet.css">
<script src="<?php echo base_url(); ?>backend/plugins/leaflet/leaflet.js"></script>

<!-- Add Pickup Point Map Modal -->
<div id="add_pickup_modal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('add') . ' ' . $this->lang->line('pickup_point'); ?></h4>
            </div>
            <form id="pickup_point_form" method="post" accept-charset="utf-8">
                <div class="modal-body pb0">
                    <input type="hidden" name="latitude" id="route_modal_lat">
                    <input type="hidden" name="longitude" id="route_modal_lng">

                    <!-- Search location -->
                    <div class="row">
                        <div class="col-md-12 mb10">
                            <div class="input-group">
                                <input type="text" id="route_map_search" class="form-control" placeholder="Search location, landmark or address on map...">
                                <span class="input-group-btn">
                                    <button type="button" id="route_btn_search" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    <button type="button" id="route_btn_current_loc" class="btn btn-default" title="Locate Current Position"><i class="fa fa-crosshairs"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Map -->
                    <div style="position: relative; margin-bottom: 10px;">
                        <div id="route_map_canvas" style="height: 380px; width: 100%; border-radius: 4px; border: 1px solid #d2d6de; z-index: 1;"></div>
                        <div id="route_map_hint" style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); padding: 5px 15px; border-radius: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); font-size: 12px; pointer-events: none; z-index: 999;">
                            <i class="fa fa-map-marker text-danger"></i> Click anywhere on the map to set pickup point
                        </div>
                        <div id="route_geocode_loading" style="display: none; position: absolute; bottom: 15px; left: 15px; background: rgba(0,0,0,0.75); color: #fff; padding: 5px 12px; border-radius: 4px; font-size: 12px; z-index: 999;">
                            <i class="fa fa-spinner fa-spin"></i> Getting address...
                        </div>
                    </div>

                    <!-- Pickup Point Address / Name field -->
                    <div class="form-group mb10">
                        <label for="route_pickup_point_name"><?php echo $this->lang->line('pickup_point'); ?> / <?php echo $this->lang->line('address'); ?></label> <small class="req"> *</small>
                        <input type="text" name="name" id="route_pickup_point_name" class="form-control" placeholder="Pickup point address will appear here after clicking on the map">
                    </div>

                    <div id="route_selected_info" class="alert alert-success" style="display: none; padding: 6px 12px; margin-bottom: 5px; font-size: 12px;">
                        <i class="fa fa-check-circle"></i> <span id="route_selected_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <div id="route_submit_container" style="display: none;">
                        <button type="submit" class="btn btn-info pull-right" id="route_modal_submit" data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('saving'); ?>"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';

    // Configure Leaflet default marker icons
    if (typeof L !== 'undefined' && L.Icon && L.Icon.Default) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: base_url + 'backend/plugins/leaflet/images/marker-icon-2x.png',
            iconUrl: base_url + 'backend/plugins/leaflet/images/marker-icon.png',
            shadowUrl: base_url + 'backend/plugins/leaflet/images/marker-shadow.png'
        });
    }

    var routeModalMap = null;
    var routeModalMarker = null;
    var currentTargetSelect = null;

    function initRouteModalMap(lat, lng, name) {
        var defaultLat = 8.524139;
        var defaultLng = 76.936638;
        var hasPoint = (lat !== undefined && lat !== null && !isNaN(lat) && lng !== undefined && lng !== null && !isNaN(lng));

        var centerLat = hasPoint ? parseFloat(lat) : defaultLat;
        var centerLng = hasPoint ? parseFloat(lng) : defaultLng;
        var zoomLevel = hasPoint ? 16 : 13;

        if (typeof L === 'undefined') return;

        if (!routeModalMap) {
            routeModalMap = L.map('route_map_canvas').setView([centerLat, centerLng], zoomLevel);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>'
            }).addTo(routeModalMap);

            routeModalMap.on('click', function(e) {
                setRouteMapLocation(e.latlng.lat, e.latlng.lng, true);
            });
        } else {
            routeModalMap.setView([centerLat, centerLng], zoomLevel);
            routeModalMap.invalidateSize();
        }

        if (hasPoint) {
            setRouteMapLocation(centerLat, centerLng, false, name);
        }
    }

    function setRouteMapLocation(lat, lng, doGeocode, customName) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        $('#route_modal_lat').val(lat);
        $('#route_modal_lng').val(lng);

        if (!routeModalMarker) {
            routeModalMarker = L.marker([lat, lng], { draggable: true }).addTo(routeModalMap);
            routeModalMarker.on('dragend', function(e) {
                var coord = e.target.getLatLng();
                setRouteMapLocation(coord.lat, coord.lng, true);
            });
        } else {
            routeModalMarker.setLatLng([lat, lng]);
            if (!routeModalMap.hasLayer(routeModalMarker)) {
                routeModalMarker.addTo(routeModalMap);
            }
        }

        if (routeModalMap) {
            routeModalMap.panTo([lat, lng]);
        }

        if (customName) {
            $('#route_pickup_point_name').val(customName);
            $('#route_selected_text').text(customName);
            $('#route_selected_info').show();
            $('#route_submit_container').show();
        } else if (doGeocode) {
            reverseGeocodeRoute(lat, lng);
        } else {
            $('#route_submit_container').show();
        }
    }

    function reverseGeocodeRoute(lat, lng) {
        $('#route_geocode_loading').show();
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/reverse',
            type: 'GET',
            data: {
                format: 'json',
                lat: lat,
                lon: lng,
                zoom: 18,
                addressdetails: 1
            },
            dataType: 'json',
            timeout: 6000,
            success: function(data) {
                $('#route_geocode_loading').hide();
                var addr = '';
                if (data && data.display_name) {
                    addr = data.display_name;
                } else {
                    addr = 'Pickup Point (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')';
                }
                $('#route_pickup_point_name').val(addr);
                $('#route_selected_text').text(addr);
                $('#route_selected_info').show();
                $('#route_submit_container').fadeIn();
            },
            error: function() {
                $('#route_geocode_loading').hide();
                var fallback = 'Pickup Point (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')';
                $('#route_pickup_point_name').val(fallback);
                $('#route_selected_text').text(fallback);
                $('#route_selected_info').show();
                $('#route_submit_container').fadeIn();
            }
        });
    }

    // Search on map
    $('#route_btn_search').on('click', function(e) {
        e.preventDefault();
        var query = $('#route_map_search').val().trim();
        if (!query) return;
        $('#route_geocode_loading').show();
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            type: 'GET',
            data: { format: 'json', q: query, limit: 1 },
            dataType: 'json',
            timeout: 6000,
            success: function(data) {
                $('#route_geocode_loading').hide();
                if (data && data.length > 0) {
                    var item = data[0];
                    setRouteMapLocation(parseFloat(item.lat), parseFloat(item.lon), false, item.display_name);
                } else {
                    alert('Location not found. Please click directly on the map to set the point.');
                }
            },
            error: function() {
                $('#route_geocode_loading').hide();
                alert('Location search failed. Please click directly on the map to set the point.');
            }
        });
    });

    $('#route_map_search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#route_btn_search').click();
        }
    });

    // Locate current position
    $('#route_btn_current_loc').on('click', function(e) {
        e.preventDefault();
        if (navigator.geolocation) {
            $('#route_geocode_loading').show();
            navigator.geolocation.getCurrentPosition(function(pos) {
                setRouteMapLocation(pos.coords.latitude, pos.coords.longitude, true);
            }, function(err) {
                $('#route_geocode_loading').hide();
                alert('Unable to retrieve your current location.');
            });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    });

    // Open map modal when "+ Add" button is clicked
    $(document).on('click', '.btn-open-pickup-map', function(e) {
        e.preventDefault();
        currentTargetSelect = $(this).closest('.input-group').find('.pickup_point_select');
        $('#route_pickup_point_name').val('');
        $('#route_modal_lat').val('');
        $('#route_modal_lng').val('');
        $('#route_map_search').val('');
        $('#route_selected_info').hide();
        $('#route_submit_container').hide();
        if (routeModalMarker && routeModalMap) {
            routeModalMap.removeLayer(routeModalMarker);
            routeModalMarker = null;
        }
        $('#add_pickup_modal').modal('show');
    });

    $('#add_pickup_modal').on('shown.bs.modal', function() {
        initRouteModalMap();
        if (routeModalMap) {
            setTimeout(function() {
                routeModalMap.invalidateSize();
            }, 200);
        }
    });

    // Save pickup point via AJAX
    $('#pickup_point_form').on('submit', function(e) {
        e.preventDefault();
        var $submitBtn = $('#route_modal_submit');
        $.ajax({
            url: base_url + 'admin/pickuppoint/add_point',
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $submitBtn.button('loading');
            },
            success: function(res) {
                $submitBtn.button('reset');
                if (res.status == 'fail') {
                    var message = '';
                    $.each(res.error, function(index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    var newId = res.id;
                    var newName = res.name || $('#route_pickup_point_name').val();

                    // Add to all pickup point select dropdowns on the page
                    $('.pickup_point_select').each(function() {
                        var $sel = $(this);
                        var exists = false;
                        $sel.find('option').each(function() {
                            if ($(this).val() == newId) {
                                exists = true;
                            }
                        });
                        if (!exists) {
                            $sel.append($('<option>', {
                                value: newId,
                                text: newName
                            }));
                        }
                    });

                    // Select the newly added point in the trigger row
                    if (currentTargetSelect && currentTargetSelect.length) {
                        currentTargetSelect.val(newId);
                    }

                    $('#add_pickup_modal').modal('hide');
                    successMsg(res.message);
                }
            },
            error: function() {
                $submitBtn.button('reset');
                alert('An error occurred while saving the pickup point.');
            }
        });
    });

    // Add More Pickup Point rows
    $('#btn_add_more_pickup').on('click', function(e) {
        e.preventDefault();
        var firstRow = $('#pickup_point_wrapper .pickup-point-row:first');
        var newRow = firstRow.clone();
        newRow.find('.pickup_point_select').val('');
        newRow.find('.btn-remove-pickup-row').show();
        $('#pickup_point_wrapper').append(newRow);
        updatePickupRemoveButtons();
    });

    $(document).on('click', '.btn-remove-pickup-row', function(e) {
        e.preventDefault();
        $(this).closest('.pickup-point-row').remove();
        updatePickupRemoveButtons();
    });

    function updatePickupRemoveButtons() {
        var rows = $('#pickup_point_wrapper .pickup-point-row');
        if (rows.length <= 1) {
            rows.find('.btn-remove-pickup-row').hide();
        } else {
            rows.find('.btn-remove-pickup-row').show();
        }
    }
</script>

