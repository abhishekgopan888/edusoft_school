<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('vehicle_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('vehicle', 'can_add')) { ?>
                                <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal"
                                    data-backdrop="static" data-target="#myModal"><i class="fa fa-plus"></i>
                                    <?php echo $this->lang->line('add'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div>
                            <div class="download_label"><?php echo $this->lang->line('vehicle_list'); ?></div>
                            <table class="table table-hover table-striped table-bordered example"
                                data-export-title="<?php echo $this->lang->line('vehicle_list'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('vehicle_number'); ?></th>
                                        <th><?php echo $this->lang->line('vehicle_model'); ?></th>
                                        <th><?php echo $this->lang->line('year_made'); ?></th>
                                        <th><?php echo $this->lang->line('registration_number'); ?></th>
                                        <th><?php echo $this->lang->line('chasis_number'); ?></th>
                                        <th><?php echo $this->lang->line('max_seating_capacity'); ?></th>
                                        <th>Occupancy</th>
                                        <th>Status</th>
                                        <th><?php echo $this->lang->line('documents'); ?></th>
                                        <th class="text-right noExport" width="10%">
                                            <?php echo $this->lang->line('action'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($listVehicle as $key => $data) { ?>
                                        <tr>
                                            <td class="mailbox-name">
                                                <a href="#" data-toggle="popover"
                                                    class="detail_popover"><?php echo $data['vehicle_no'] ?></a>
                                            </td>
                                            <td class="mailbox-name"> <?php echo $data['vehicle_model'] ?></td>
                                            <td class="mailbox-name"> <?php echo $data['manufacture_year'] ?></td>
                                            <td class="mailbox-name"> <?php echo $data['registration_number'] ?></td>
                                            <td class="mailbox-name"> <?php echo $data['chasis_number'] ?></td>
                                            <td class="mailbox-name"> <?php echo $data['max_seating_capacity'] ?></td>
                                            <td class="mailbox-name">
                                                <?php
                                                $occupied = isset($data['occupied_seats']) ? (int) $data['occupied_seats'] : 0;
                                                $capacity = (int) $data['max_seating_capacity'];
                                                if ($capacity > 0 && $occupied >= $capacity) {
                                                    echo '<span class="label label-danger" data-toggle="tooltip" title="Vehicle Full">' . $occupied . ' / ' . $capacity . ' (Full)</span>';
                                                } else {
                                                    echo '<span class="label label-info" data-toggle="tooltip" title="' . $occupied . ' seats occupied">' . $occupied . ' / ' . ($capacity > 0 ? $capacity : '-') . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = !empty($data['status']) ? $data['status'] : 'Active';
                                                if ($status == 'Active') {
                                                    echo '<span class="label label-success">Active</span>';
                                                } elseif ($status == 'Inactive') {
                                                    echo '<span class="label label-danger">Inactive</span>';
                                                } elseif ($status == 'maintanance') {
                                                    echo '<span class="label label-warning">Maintanance</span>';
                                                } elseif ($status == 'Break down') {
                                                    echo '<span class="label label-danger">Break Down</span>';
                                                } else {
                                                    echo '<span class="label label-default">Active</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="mailbox-name">
                                                <?php if (!empty($data['documents'])) { ?>
                                                    <a href="<?php echo site_url('admin/vehicle/download/' . $data['id']); ?>"
                                                        class="btn btn-xs btn-default" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>"><i
                                                            class="fa fa-download"></i> View</a>
                                                <?php } else {
                                                    echo '-';
                                                } ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print white-space-nowrap">

                                                <a class="btn btn-primary btn-xs vehicledetails"
                                                    data-id="<?php echo $data['id'] ?>" data-toggle="tooltip"
                                                    title="<?php echo $this->lang->line('view'); ?>"><i
                                                        class="fa fa-reorder"></i>
                                                </a>

                                                <?php if ($this->rbac->hasPrivilege('vehicle', 'can_edit')) { ?>
                                                    <a class="btn btn-primary btn-xs transfervehicle"
                                                        data-id="<?php echo $data['id'] ?>" data-toggle="tooltip"
                                                        title="Transfer Vehicle"><i class="fa fa-exchange"></i>
                                                    </a>
                                                    <a class="btn btn-primary btn-xs editvehicle"
                                                        data-id="<?php echo $data['id'] ?>" data-toggle="tooltip"
                                                        title="<?php echo $this->lang->line('edit'); ?>"><i
                                                            class="fa fa-pencil"></i>
                                                    </a>
                                                <?php }
                                                if ($this->rbac->hasPrivilege('vehicle', 'can_delete')) { ?>
                                                    <a href="<?php echo base_url(); ?>admin/vehicle/delete/<?php echo $data['id'] ?>"
                                                        class="btn btn-primary btn-xs" data-toggle="tooltip"
                                                        title="<?php echo $this->lang->line('delete'); ?>"
                                                        onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')"><i
                                                            class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('add_vehicle'); ?></h4>
            </div>

            <form id="addvehicleform" method="post" enctype="multipart/form-data">
                <div class="modal-body pb0 ptt10">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('vehicle_number'); ?></label><small
                                            class="req"> *</small>
                                        <input autofocus="" id="vehicle_no" name="vehicle_no" placeholder="" type="text"
                                            class="form-control" value="<?php echo set_value('vehicle_no'); ?>" />
                                        <span class="text-danger"><?php echo form_error('vehicle_no'); ?></span>
                                    </div>

                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('vehicle_model'); ?></label>
                                        <input id="vehicle_model" name="vehicle_model" placeholder="" type="text"
                                            class="form-control" value="<?php echo set_value('vehicle_model'); ?>" />
                                        <span class="text-danger"><?php echo form_error('vehicle_model'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('year_made'); ?> </label>
                                        <input id="manufacture_year" name="manufacture_year" placeholder="" type="text"
                                            class="form-control" value="<?php echo set_value('manufacture_year'); ?>" />
                                        <span class="text-danger"><?php echo form_error('manufacture_year'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('registration_number'); ?> </label>
                                        <input id="registration_number" name="registration_number" placeholder=""
                                            type="text" class="form-control"
                                            value="<?php echo set_value('registration_number'); ?>" />
                                        <span
                                            class="text-danger"><?php echo form_error('registration_number'); ?></span>
                                    </div>

                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('chasis_number'); ?> </label>
                                        <input id="chasis_number" name="chasis_number" placeholder="" type="text"
                                            class="form-control" value="<?php echo set_value('chasis_number'); ?>" />
                                        <span class="text-danger"><?php echo form_error('chasis_number'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('max_seating_capacity'); ?> </label>
                                        <input id="max_seating_capacity" name="max_seating_capacity" placeholder=""
                                            type="text" class="form-control"
                                            value="<?php echo set_value('max_seating_capacity'); ?>" />
                                        <span
                                            class="text-danger"><?php echo form_error('max_seating_capacity'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="maintanance">maintanance</option>
                                            <option value="Break down">Break down</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('documents'); ?></label>
                                        <input id="documents" name="documents" placeholder="" type="file"
                                            class="filestyle form-control" data-height="30" />
                                        <span class="text-danger"><?php echo form_error('documents'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('note'); ?></label>
                                        <textarea class="form-control" id="note" name="note" placeholder=""
                                            rows="2"><?php echo set_value('note'); ?></textarea>
                                        <span class="text-danger"><?php echo form_error('note'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <div class="paddA10">
                        <button type="submit" class="btn btn-info pull-right"
                            data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                    </div>
                </div>

        </div>

        </form>
    </div>
</div>
</div>

<div class="modal fade" id="editvehiclemodal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('edit_vehicle'); ?></h4>
            </div>

            <form id="editvehicleform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0 ">
                    <div id="editvehicledata"></div>
                </div>
                <div class="box-footer">

                    <div class="paddA10">
                        <button type="submit" class="btn btn-info pull-right"
                            data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="vehicledetails" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('vehicle_details'); ?></h4>
            </div>

            <form id="viewvehicleform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0 ">
                    <div id="viewvehicledata"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="transfervehiclemodal" role="dialog" aria-labelledby="transferVehicleLabel">
    <div class="modal-dialog" role="document" style="max-width: 540px;">
        <div class="modal-content"
            style="border-radius: 14px; border: none; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #635BFF 0%, #5c54ec 100%); padding: 18px 24px; border-bottom: none; display: flex; align-items: center; justify-content: space-between;">
                <h4 class="modal-title" style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">Transfer
                    Vehicle</h4>
                <button type="button" class="close" data-dismiss="modal"
                    style="font-size: 22px; color: #ffffff; opacity: 0.9; text-shadow: none; margin-top: -2px;">&times;</button>
            </div>

            <form id="transfervehicleform" method="post">
                <input type="hidden" name="vehicle_id" id="transfer_vehicle_id" value="" />
                <div class="modal-body" style="padding: 24px; background-color: #ffffff;">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-sm-6">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label
                                    style="font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 8px; display: block;">Current
                                    Branch</label>
                                <input id="current_branch" type="text" class="form-control" readonly
                                    style="background-color: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; height: 44px; padding: 10px 14px; font-weight: 500; color: #1f2937; font-size: 14px;" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label
                                    style="font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 8px; display: block;">Current
                                    Driver</label>
                                <input id="current_driver" type="text" class="form-control" readonly
                                    style="background-color: #f3f4f6; border-radius: 8px; border: 1px solid #e5e7eb; height: 44px; padding: 10px 14px; font-weight: 500; color: #1f2937; font-size: 14px;" />
                            </div>
                        </div>
                    </div>

                    <div style="height: 1px; background-color: #e5e7eb; margin: 0 0 20px 0;"></div>

                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-sm-12">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label
                                    style="font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 8px; display: block;">Transfer
                                    To Branch <small class="req" style="color: #ef4444;">*</small></label>
                                <select id="transfer_branch" name="transfer_branch" class="form-control"
                                    style="border-radius: 8px; border: 1px solid #d1d5db; height: 44px; font-size: 14px; color: #1f2937;">
                                    <option value="">Select Branch</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label
                                    style="font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 8px; display: block;">Assign
                                    Driver <small class="req" style="color: #ef4444;">*</small></label>
                                <select id="assign_driver_id" name="assign_driver_id" class="form-control"
                                    style="border-radius: 8px; border: 1px solid #d1d5db; height: 44px; font-size: 14px; color: #1f2937;">
                                    <option value="">Select Driver</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer"
                    style="border-top: none; padding: 0 24px 24px 24px; text-align: right; background-color: #ffffff;">
                    <button type="button" class="btn" data-dismiss="modal"
                        style="background-color: #17b978; color: #ffffff; border-radius: 8px; padding: 10px 24px; font-size: 14px; font-weight: 600; border: none; margin-right: 10px;">Cancel</button>
                    <button type="submit" class="btn"
                        style="background-color: #0071c5; color: #ffffff; border-radius: 8px; padding: 10px 26px; font-size: 14px; font-weight: 600; border: none;"
                        data-loading-text="<i class='fa fa-spinner fa-spin '></i> Transferring...">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    (function ($) {
        $('#myModal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
        })
    })(jQuery);


    $("#addvehicleform").on('submit', (function (e) {

        e.preventDefault();

        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("admin/vehicle/add") ?>",
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

    $('.editvehicle').click(function () {
        $('#editvehiclemodal').modal({
            backdrop: 'static',
            keyboard: false
        });
        var vehicleid = $(this).attr('data-id');

        $.ajax({
            url: '<?php echo site_url("admin/vehicle/getsinglevehicledata"); ?>',
            type: 'post',
            data: { vehicleid: vehicleid },
            dataType: 'json',
            success: function (response) {
                $('#editvehicledata').html(response.page);
            }
        });
    })

    $('.vehicledetails').click(function () {
        $('#vehicledetails').modal({
            backdrop: 'static',
            keyboard: false
        });
        var vehicleid = $(this).attr('data-id');

        $.ajax({
            url: '<?php echo site_url("admin/vehicle/vehicledetails"); ?>',
            type: 'post',
            data: { vehicleid: vehicleid },
            dataType: 'json',
            success: function (response) {
                $('#viewvehicledata').html(response.page);
            }
        });
    })

    $("#editvehicleform").on('submit', (function (e) {
        e.preventDefault();

        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("admin/vehicle/edit") ?>",
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

    $('.transfervehicle').click(function () {
        var vehicleid = $(this).attr('data-id');
        $('#transfer_vehicle_id').val(vehicleid);

        $.ajax({
            url: '<?php echo site_url("admin/vehicle/gettransferdetails"); ?>',
            type: 'post',
            data: { vehicleid: vehicleid },
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var veh = response.vehicle;
                    var currentBranchVal = veh.branch ? veh.branch : (veh.route_name ? veh.route_name : 'Ajman');
                    var currentDriverVal = veh.driver_name ? veh.driver_name : 'None';

                    $('#current_branch').val(currentBranchVal);
                    $('#current_driver').val(currentDriverVal);

                    var branch_options = '<option value="">Select Branch</option>';
                    if (response.routes && response.routes.length > 0) {
                        $.each(response.routes, function (i, r) {
                            branch_options += '<option value="' + r.route_title + '">' + r.route_title + '</option>';
                        });
                    }
                    if (response.branches && response.branches.length > 0) {
                        $.each(response.branches, function (i, b) {
                            var isDup = false;
                            if (response.routes) {
                                $.each(response.routes, function (j, r) {
                                    if (r.route_title == b.branch_name) isDup = true;
                                });
                            }
                            if (!isDup) {
                                branch_options += '<option value="' + b.branch_name + '">' + b.branch_name + '</option>';
                            }
                        });
                    }
                    $('#transfer_branch').html(branch_options);

                    var driver_options = '<option value="">Select Driver</option>';
                    if (response.drivers && response.drivers.length > 0) {
                        $.each(response.drivers, function (i, d) {
                            driver_options += '<option value="' + d.id + '">' + d.driver_name + '</option>';
                        });
                    }
                    $('#assign_driver_id').html(driver_options);

                    $('#transfervehiclemodal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            }
        });
    });

    $("#transfervehicleform").on('submit', (function (e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]");

        $.ajax({
            url: "<?php echo site_url("admin/vehicle/transfer") ?>",
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
                        message += value + " ";
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
</script>