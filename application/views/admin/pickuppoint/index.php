<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/leaflet/leaflet.css">
<script src="<?php echo base_url(); ?>backend/plugins/leaflet/leaflet.js"></script>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary" id="route">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('pickup_point_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('pickup_point', 'can_add')) {?>
                            <button type="button" onclick="add()" class="btn btn-primary btn-sm checkbox-toggle"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add') ?></button>
                            <?php }?>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                            <div class="pull-right">
                            </div>
                        </div>
                        <div class="mailbox-messages">
                            <div class="table-responsive">      

                                <table class="table table-striped table-bordered table-hover list" data-export-title="<?php echo $this->lang->line('pickup_point_list'); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="add" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" >&times;</button>
                <h4 class="modal-title" id="modal-title"></h4>
            </div>
            <form id="form1" name="employeeform" method="post" accept-charset="utf-8">
                <div class="modal-body pb0">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <!-- Search location -->
                    <div class="row">
                        <div class="col-md-12 mb10">
                            <div class="input-group">
                                <input type="text" id="map_search_input" class="form-control" placeholder="Search location, landmark or address on map...">
                                <span class="input-group-btn">
                                    <button type="button" id="btn_search_place" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    <button type="button" id="btn_current_loc" class="btn btn-default" title="Locate Current Position"><i class="fa fa-crosshairs"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Map -->
                    <div style="position: relative; margin-bottom: 10px;">
                        <div id="pickup_map_canvas" style="height: 380px; width: 100%; border-radius: 4px; border: 1px solid #d2d6de; z-index: 1;"></div>
                        <div id="map_click_hint" style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); padding: 5px 15px; border-radius: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); font-size: 12px; pointer-events: none; z-index: 999;">
                            <i class="fa fa-map-marker text-danger"></i> Click anywhere on the map to set pickup point
                        </div>
                        <div id="geocode_loading" style="display: none; position: absolute; bottom: 15px; left: 15px; background: rgba(0,0,0,0.75); color: #fff; padding: 5px 12px; border-radius: 4px; font-size: 12px; z-index: 999;">
                            <i class="fa fa-spinner fa-spin"></i> Getting address...
                        </div>
                    </div>

                    <!-- Pickup Point Address / Name field -->
                    <div class="form-group mb10">
                        <label for="name"><?php echo $this->lang->line('pickup_point'); ?> / <?php echo $this->lang->line('address'); ?></label> <small class="req"> *</small>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Pickup point address will appear here after clicking on the map">
                        <span class="text-danger" id="name_error"></span>
                    </div>

                    <div id="selected_loc_info" class="alert alert-success" style="display: none; padding: 6px 12px; margin-bottom: 5px; font-size: 12px;">
                        <i class="fa fa-check-circle"></i> <span id="selected_loc_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <div id="submit_container" style="display: none;">
                        <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('saving') ?>"><i class="fa fa-check"></i> <span id="submit_label"><?php echo $this->lang->line('save') ?></span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="map_modal" class="modal fade " role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
             <div class="modal-body pt0 minheight303 pr0 ps-0 pb0">
    </div>
       </div>
    </div>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';

    // Configure Leaflet default marker icons so they work offline/locally
    if (typeof L !== 'undefined' && L.Icon && L.Icon.Default) {
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: base_url + 'backend/plugins/leaflet/images/marker-icon-2x.png',
            iconUrl: base_url + 'backend/plugins/leaflet/images/marker-icon.png',
            shadowUrl: base_url + 'backend/plugins/leaflet/images/marker-shadow.png'
        });
    }

    var pickupMap = null;
    var pickupMarker = null;
    var pendingInitPoint = null;

    function parseCoordinate(coord, isLng) {
        if (!coord) return null;
        var str = String(coord).trim();
        if (str.indexOf(',') !== -1) {
            var parts = str.split(',');
            return parseFloat(isLng ? parts[1].trim() : parts[0].trim());
        }
        return parseFloat(str);
    }

    function initPickupModalMap(lat, lng, name) {
        var defaultLat = 8.524139;
        var defaultLng = 76.936638;
        var hasPoint = (lat !== undefined && lat !== null && !isNaN(lat) && lng !== undefined && lng !== null && !isNaN(lng));

        var centerLat = hasPoint ? parseFloat(lat) : defaultLat;
        var centerLng = hasPoint ? parseFloat(lng) : defaultLng;
        var zoomLevel = hasPoint ? 16 : 13;

        if (typeof L === 'undefined') {
            return;
        }

        if (!pickupMap) {
            pickupMap = L.map('pickup_map_canvas').setView([centerLat, centerLng], zoomLevel);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(pickupMap);

            pickupMap.on('click', function(e) {
                setPickupLocation(e.latlng.lat, e.latlng.lng, true);
            });
        } else {
            pickupMap.setView([centerLat, centerLng], zoomLevel);
            pickupMap.invalidateSize();
        }

        if (hasPoint) {
            setPickupLocation(centerLat, centerLng, false, name);
        }
    }

    function setPickupLocation(lat, lng, doGeocode, customName) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        $('#latitude').val(lat);
        $('#longitude').val(lng);

        if (!pickupMarker) {
            pickupMarker = L.marker([lat, lng], { draggable: true }).addTo(pickupMap);
            pickupMarker.on('dragend', function(e) {
                var coord = e.target.getLatLng();
                setPickupLocation(coord.lat, coord.lng, true);
            });
        } else {
            pickupMarker.setLatLng([lat, lng]);
            if (!pickupMap.hasLayer(pickupMarker)) {
                pickupMarker.addTo(pickupMap);
            }
        }

        if (pickupMap) {
            pickupMap.panTo([lat, lng]);
        }

        if (customName) {
            $('#name').val(customName);
            $('#selected_loc_text').text(customName);
            $('#selected_loc_info').show();
            $('#submit_container').show();
        } else if (doGeocode) {
            reverseGeocode(lat, lng);
        } else {
            $('#submit_container').show();
        }
    }

    function reverseGeocode(lat, lng) {
        $('#geocode_loading').show();
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
                $('#geocode_loading').hide();
                var addr = '';
                if (data && data.display_name) {
                    addr = data.display_name;
                } else {
                    addr = 'Pickup Point (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')';
                }
                $('#name').val(addr);
                $('#selected_loc_text').text(addr);
                $('#selected_loc_info').show();
                $('#submit_container').fadeIn();
            },
            error: function() {
                $('#geocode_loading').hide();
                var fallback = 'Pickup Point (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')';
                $('#name').val(fallback);
                $('#selected_loc_text').text(fallback);
                $('#selected_loc_info').show();
                $('#submit_container').fadeIn();
            }
        });
    }

    $('#btn_search_place').on('click', function(e) {
        e.preventDefault();
        var query = $('#map_search_input').val().trim();
        if (!query) return;
        $('#geocode_loading').show();
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            type: 'GET',
            data: { format: 'json', q: query, limit: 1 },
            dataType: 'json',
            timeout: 6000,
            success: function(data) {
                $('#geocode_loading').hide();
                if (data && data.length > 0) {
                    var item = data[0];
                    setPickupLocation(parseFloat(item.lat), parseFloat(item.lon), false, item.display_name);
                } else {
                    alert('Location not found. Please click directly on the map to set the point.');
                }
            },
            error: function() {
                $('#geocode_loading').hide();
                alert('Location search failed. Please click directly on the map to set the point.');
            }
        });
    });

    $('#map_search_input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn_search_place').click();
        }
    });

    $('#btn_current_loc').on('click', function(e) {
        e.preventDefault();
        if (navigator.geolocation) {
            $('#geocode_loading').show();
            navigator.geolocation.getCurrentPosition(function(pos) {
                setPickupLocation(pos.coords.latitude, pos.coords.longitude, true);
            }, function(err) {
                $('#geocode_loading').hide();
                alert('Unable to retrieve your current location.');
            });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    });

    $('#add').on('shown.bs.modal', function() {
        if (pendingInitPoint) {
            initPickupModalMap(pendingInitPoint.lat, pendingInitPoint.lng, pendingInitPoint.name);
            pendingInitPoint = null;
        } else {
            initPickupModalMap();
        }
        if (pickupMap) {
            setTimeout(function() {
                pickupMap.invalidateSize();
            }, 200);
        }
    });

    function add() {
        $('#modal-title').html('<?php echo $this->lang->line('add'); ?>');
        $('#submit_label').html('<?php echo $this->lang->line('save'); ?>');
        $('#id').val('');
        $('#name').val('');
        $('#latitude').val('');
        $('#longitude').val('');
        $('#map_search_input').val('');
        $('#selected_loc_info').hide();
        $('#submit_container').hide();
        if (pickupMarker && pickupMap) {
            pickupMap.removeLayer(pickupMarker);
            pickupMarker = null;
        }
        pendingInitPoint = null;
        $('#add').modal('show');
    }

    function edit(id) {
        $('#modal-title').html('<?php echo $this->lang->line('edit'); ?>');
        $('#submit_label').html('<?php echo $this->lang->line('save'); ?>');
        $('#id').val(id);
        $('#submit_container').show();
        $.ajax({
            url: '<?php echo base_url(); ?>admin/pickuppoint/get_pointdata',
            type: "POST",
            data: { point_id: id },
            dataType: 'json',
            success: function(res) {
                $('#name').val(res.name);
                $('#latitude').val(res.latitude);
                $('#longitude').val(res.longitude);

                var lat = parseCoordinate(res.latitude, false);
                var lng = parseCoordinate(res.longitude, true);

                pendingInitPoint = { lat: lat, lng: lng, name: res.name };
                $('#selected_loc_text').text(res.name);
                $('#selected_loc_info').show();
                $('#add').modal('show');
            },
            error: function(xhr) {
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            }
        });
    }

    function printDiv(elem) {
        Popup(jQuery(elem).html());
    }

    $("#form1").on('submit', (function (e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");
        $.ajax({
            url: base_url + "admin/pickuppoint/add_point",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (res) {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            },
            error: function (xhr) {
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    }));

    var datatableMap = null;
    var datatableMarker = null;

    $(document).on('click', '.pickup_map', function(e) {
        e.preventDefault();
        var $this = $(this);
        var pick_location = $this.data('pickLocation');

        $.ajax({
            url: base_url + "admin/pickuppoint/pointmap",
            type: "POST",
            data: { 'pick_location': pick_location },
            dataType: 'json',
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (res) {
                let location_data = res.page.location;
                $('#map_modal .modal-body').html(res.page.page);
                $('#map_modal').modal('show');
                loadMap(location_data.latitude, location_data.longitude, location_data.name);
            },
            error: function (xhr) {
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    });

    $('#map_modal').on('hidden.bs.modal', function() {
        if (datatableMap) {
            datatableMap.remove();
            datatableMap = null;
            datatableMarker = null;
        }
    });

    function loadMap(a, b, name) {
        var lat = parseCoordinate(a, false);
        var lng = parseCoordinate(b, true);
        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
            $('#sample').html('<div class="alert alert-warning" style="margin:20px;">Coordinates not set or invalid for this pickup point.</div>');
            return;
        }

        setTimeout(function() {
            if (!datatableMap) {
                datatableMap = L.map('sample').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(datatableMap);
            } else {
                datatableMap.setView([lat, lng], 16);
                datatableMap.invalidateSize();
            }

            if (datatableMarker) {
                datatableMarker.setLatLng([lat, lng]);
            } else {
                datatableMarker = L.marker([lat, lng]).addTo(datatableMap);
            }
            datatableMarker.bindPopup('<b>' + name + '</b>').openPopup();
        }, 300);
    }

    $(document).ready(function() {
        initDatatable('list', 'admin/pickuppoint/getpickpointlist', [], [], 50,
            [
                {
                    targets: [-1],
                    orderable: false,
                    className: 'dt-body-right dt-head-right'
                }
            ]
        );

        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'add' || window.location.hash === '#add') {
            setTimeout(function() {
                add();
            }, 300);
        }
    });
</script>