<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix">Driver List</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-backdrop="static" data-target="#addDriverModal"><i class="fa fa-plus"></i> Add</button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div>
                            <div class="download_label">Driver List</div>
                            <table class="table table-hover table-striped table-bordered example" data-export-title="Driver List">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Driver Image</th>
                                        <th>Driver Name</th>
                                        <th>Age</th>
                                        <th>Licence</th>
                                        <th>Licence Front</th>
                                        <th>Licence Back</th>
                                        <th>ID Proof</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Documents</th>
                                        <th class="text-right noExport" width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sn = 1; foreach ($listDriver as $row) { ?>
                                        <tr>
                                            <td><?php echo $sn++; ?></td>
                                            <td>
                                                <?php if (!empty($row['driver_image'])) { ?>
                                                    <img src="<?php echo $this->media_storage->getImageURL('/uploads/driver_documents/'.$row['driver_image']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
                                                <?php } else { ?>
                                                    <i class="fa fa-user-circle-o fa-2x text-muted"></i>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo $row['driver_name']; ?></td>
                                            <td><?php echo $row['age']; ?></td>
                                            <td><?php echo $row['licence']; ?></td>
                                            <td>
                                                <?php if (!empty($row['licence_front'])) { ?>
                                                    <a href="<?php echo site_url('admin/driver/download/' . $row['id'] . '/licence_front'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['licence_back'])) { ?>
                                                    <a href="<?php echo site_url('admin/driver/download/' . $row['id'] . '/licence_back'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['id_proof'])) { ?>
                                                    <a href="<?php echo site_url('admin/driver/download/' . $row['id'] . '/id_proof'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file-image-o"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td><?php echo $row['contact']; ?></td>
                                            <td>
                                                <?php
                                                $status = !empty($row['status']) ? $row['status'] : 'Active';
                                                if ($status == 'Active') {
                                                    echo '<span class="label label-success">Active</span>';
                                                } else {
                                                    echo '<span class="label label-danger">Inactive</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['documents'])) { ?>
                                                    <a href="<?php echo site_url('admin/driver/download/' . $row['id'] . '/documents'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print white-space-nowrap">
                                                <a class="btn btn-primary btn-xs driverdetails" data-id="<?php echo $row['id']; ?>" data-toggle="tooltip" title="View"><i class="fa fa-reorder"></i></a>
                                                <a class="btn btn-primary btn-xs editdriver" data-id="<?php echo $row['id']; ?>" data-toggle="tooltip" title="Edit"><i class="fa fa-pencil"></i></a>
                                                <a href="<?php echo base_url(); ?>admin/driver/delete/<?php echo $row['id']; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fa fa-remove"></i></a>
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

<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal" role="dialog" aria-labelledby="addDriverModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Add Driver</h4>
            </div>
            <form id="adddriverform" method="post" enctype="multipart/form-data">
                <div class="modal-body pb0 ptt10">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Driver Name</label><small class="req"> *</small>
                                        <input autofocus="" id="driver_name" name="driver_name" type="text" class="form-control" value="<?php echo set_value('driver_name'); ?>" />
                                        <span class="text-danger"><?php echo form_error('driver_name'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Age</label><small class="req"> *</small>
                                        <input id="age" name="age" type="number" class="form-control" value="<?php echo set_value('age'); ?>" required />
                                        <span class="text-danger" id="age_error"></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Licence No</label><small class="req"> *</small>
                                        <input id="licence" name="licence" type="text" class="form-control" value="<?php echo set_value('licence'); ?>" required />
                                        <span class="text-danger" id="licence_error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Contact</label><small class="req"> *</small>
                                        <input id="contact" name="contact" type="text" class="form-control" value="<?php echo set_value('contact'); ?>" required />
                                        <span class="text-danger" id="contact_error"></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Status</label><small class="req"> *</small>
                                        <select class="form-control" name="status" required>
                                            <option value="">-- Select --</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                        <span class="text-danger" id="status_error"></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Address</label><small class="req"> *</small>
                                        <input id="address" name="address" type="text" class="form-control" value="<?php echo set_value('address'); ?>" required />
                                        <span class="text-danger" id="address_error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Licence Front Page</label><small class="req"> *</small>
                                        <input id="licence_front" name="licence_front" type="file" class="filestyle form-control" data-height="30" required />
                                        <span class="text-danger" id="licence_front_error"><?php echo form_error('licence_front'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Licence Back Page</label><small class="req"> *</small>
                                        <input id="licence_back" name="licence_back" type="file" class="filestyle form-control" data-height="30" required />
                                        <span class="text-danger" id="licence_back_error"><?php echo form_error('licence_back'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Driver Image</label><small class="req"> *</small>
                                        <input id="driver_image" name="driver_image" type="file" class="filestyle form-control" data-height="30" required />
                                        <span class="text-danger" id="driver_image_error"><?php echo form_error('driver_image'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>ID Proof Document</label>
                                        <input id="id_proof" name="id_proof" type="file" class="filestyle form-control" data-height="30" />
                                        <span class="text-danger"><?php echo form_error('id_proof'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Documents (Other)</label>
                                        <input id="documents" name="documents" type="file" class="filestyle form-control" data-height="30" />
                                        <span class="text-danger"><?php echo form_error('documents'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('note'); ?></label>
                                        <textarea class="form-control" id="note" name="note" rows="2"><?php echo set_value('note'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="paddA10">
                            <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Please wait..."><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Driver Modal -->
<div class="modal fade" id="editDriverModal" role="dialog" aria-labelledby="editDriverModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Edit Driver</h4>
            </div>
            <form id="editdriverform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0">
                    <div id="editdriverdata"></div>
                </div>
                <div class="box-footer">
                    <div class="paddA10">
                        <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Please wait..."><?php echo $this->lang->line('save'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Driver Modal -->
<div class="modal fade" id="viewDriverModal" role="dialog" aria-labelledby="viewDriverModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Driver Details</h4>
            </div>
            <div class="modal-body pt0 pb0">
                <div id="viewdriverdata"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    $('#addDriverModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    });
})(jQuery);

$("#adddriverform").on('submit', function (e) {
    e.preventDefault();
    var $this = $(this).find("button[type=submit]:focus");
    // Clear previous inline errors
    $('#adddriverform .text-danger[id$="_error"]').text('');
    $.ajax({
        url: "<?php echo site_url('admin/driver/add'); ?>",
        type: "POST",
        data: new FormData(this),
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () { $this.button('loading'); },
        success: function (res) {
            if (res.status == "fail") {
                var message = "";
                $.each(res.error, function (index, value) {
                    if (value) {
                        message += value + "<br>";
                        // Show inline below each field if span exists
                        if ($('#' + index + '_error').length) {
                            $('#' + index + '_error').text(value);
                        }
                    }
                });
                if (message) { errorMsg(message); }
            } else {
                successMsg(res.message);
                window.location.reload(true);
            }
        },
        error: function () { alert("An error occurred. Please try again."); $this.button('reset'); },
        complete: function () { $this.button('reset'); }
    });
});

$(document).on('click', '.editdriver', function () {
    $('#editdriverdata').html('<div class="text-center ptt10 pbt10"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $('#editDriverModal').modal({ backdrop: 'static', keyboard: false });
    var driverid = $(this).attr('data-id');
    $.ajax({
        url: '<?php echo site_url("admin/driver/getsingledriver"); ?>',
        type: 'post',
        data: { driverid: driverid },
        dataType: 'json',
        success: function (response) { $('#editdriverdata').html(response.page); }
    });
});

$(document).on('click', '.driverdetails', function () {
    $('#viewdriverdata').html('<div class="text-center ptt10 pbt10"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $('#viewDriverModal').modal({ backdrop: 'static', keyboard: false });
    var driverid = $(this).attr('data-id');
    $.ajax({
        url: '<?php echo site_url("admin/driver/driverdetails"); ?>',
        type: 'post',
        data: { driverid: driverid },
        dataType: 'json',
        success: function (response) { $('#viewdriverdata').html(response.page); }
    });
});

$("#editdriverform").on('submit', function (e) {
    e.preventDefault();
    var $this = $(this).find("button[type=submit]:focus");
    $.ajax({
        url: "<?php echo site_url('admin/driver/edit'); ?>",
        type: "POST",
        data: new FormData(this),
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () { $this.button('loading'); },
        success: function (res) {
            if (res.status == "fail") {
                var message = "";
                $.each(res.error, function (index, value) { message += value; });
                errorMsg(message);
            } else {
                successMsg(res.message);
                window.location.reload(true);
            }
        },
        error: function () { alert("An error occurred. Please try again."); $this.button('reset'); },
        complete: function () { $this.button('reset'); }
    });
});
</script>
