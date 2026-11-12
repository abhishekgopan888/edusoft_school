<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix">Attender List</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-backdrop="static" data-target="#addAttenderModal"><i class="fa fa-plus"></i> Add</button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div>
                            <div class="download_label">Attender List</div>
                            <table class="table table-hover table-striped table-bordered example" data-export-title="Attender List">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Attender Image</th>
                                        <th>Attender Name</th>
                                        <th>Age</th>
                                        <th>Address</th>
                                        <th>ID Proof</th>
                                        <th>Status</th>
                                        <th>Documents</th>
                                        <th class="text-right noExport" width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <?php $sn = 1; foreach ($listAttender as $row) { ?>
                                        <tr>
                                            <td><?php echo $sn++; ?></td>
                                            <td>
                                                <?php if (!empty($row['attender_image'])) {
                                                    $img_path = (!empty($row['is_staff'])) ? 'uploads/staff_images/'.$row['attender_image'] : 'uploads/attender_documents/'.$row['attender_image'];
                                                ?>
                                                    <img src="<?php echo $this->media_storage->getImageURL($img_path); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
                                                <?php } else { ?>
                                                    <i class="fa fa-user-circle-o fa-2x text-muted"></i>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php echo $row['attender_name']; ?>
                                                <?php if (!empty($row['is_staff'])) { ?>
                                                    <span class="label label-info" style="font-size: 10px; margin-left: 5px;">Staff Role</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo $row['age']; ?></td>
                                            <td><?php echo $row['address']; ?></td>
                                            <td>
                                                <?php if (!empty($row['id_proof'])) {
                                                    $ext = strtolower(pathinfo($row['id_proof'], PATHINFO_EXTENSION));
                                                    $icon = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'fa-image' : 'fa-file-pdf-o';
                                                ?>
                                                    <a href="<?php echo site_url('admin/attender/viewfile/'.$row['id'].'/id_proof'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa <?php echo $icon; ?>"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = !empty($row['status']) ? $row['status'] : 'Active';
                                                if ($status == 'Active' || $status == 'active') {
                                                    echo '<span class="label label-success">Active</span>';
                                                } elseif ($status == 'Inactive' || $status == 'inactive') {
                                                    echo '<span class="label label-danger">Inactive</span>';
                                                } elseif ($status == 'leave') {
                                                    echo '<span class="label label-warning">Leave</span>';
                                                } else {
                                                    echo '<span class="label label-default">Active</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['documents'])) {
                                                    $ext = strtolower(pathinfo($row['documents'], PATHINFO_EXTENSION));
                                                    $icon = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'fa-image' : 'fa-file-pdf-o';
                                                ?>
                                                    <a href="<?php echo site_url('admin/attender/viewfile/'.$row['id'].'/documents'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa <?php echo $icon; ?>"></i> View</a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print white-space-nowrap">
                                                <a class="btn btn-primary btn-xs attenderdetails" data-id="<?php echo $row['id']; ?>" data-toggle="tooltip" title="View"><i class="fa fa-reorder"></i></a>
                                                <?php if (empty($row['is_staff'])) { ?>
                                                    <a class="btn btn-primary btn-xs editattender" data-id="<?php echo $row['id']; ?>" data-toggle="tooltip" title="Edit"><i class="fa fa-pencil"></i></a>
                                                    <a href="<?php echo base_url(); ?>admin/attender/delete/<?php echo $row['id']; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fa fa-remove"></i></a>
                                                <?php } else { ?>
                                                    <a href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $row['staff_id']; ?>" class="btn btn-info btn-xs" data-toggle="tooltip" title="View Staff Profile"><i class="fa fa-user"></i> Staff Profile</a>
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

<!-- Add Attender Modal -->
<div class="modal fade" id="addAttenderModal" role="dialog" aria-labelledby="addAttenderModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Add Attender</h4>
            </div>
            <form id="addattenderform" method="post" enctype="multipart/form-data">
                <div class="modal-body pb0 ptt10">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Attender Name</label><small class="req"> *</small>
                                        <input autofocus="" id="attender_name" name="attender_name" type="text" class="form-control" value="<?php echo set_value('attender_name'); ?>" />
                                        <span class="text-danger"><?php echo form_error('attender_name'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Age</label>
                                        <input id="age" name="age" type="text" class="form-control" value="<?php echo set_value('age'); ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="leave">Leave</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input id="address" name="address" type="text" class="form-control" value="<?php echo set_value('address'); ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Attender Image</label>
                                        <input id="attender_image" name="attender_image" type="file" class="filestyle form-control" data-height="30" />
                                        <span class="text-danger"><?php echo form_error('attender_image'); ?></span>
                                    </div>
                                </div>
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
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
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

<!-- Edit Attender Modal -->
<div class="modal fade" id="editAttenderModal" role="dialog" aria-labelledby="editAttenderModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Edit Attender</h4>
            </div>
            <form id="editattenderform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0">
                    <div id="editattenderdata"></div>
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

<!-- View Attender Modal -->
<div class="modal fade" id="viewAttenderModal" role="dialog" aria-labelledby="viewAttenderModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Attender Details</h4>
            </div>
            <div class="modal-body pt0 pb0">
                <div id="viewattenderdata"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    $('#addAttenderModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    });
})(jQuery);

$("#addattenderform").on('submit', function (e) {
    e.preventDefault();
    var $this = $(this).find("button[type=submit]:focus");
    $.ajax({
        url: "<?php echo site_url('admin/attender/add'); ?>",
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

$('.editattender').click(function () {
    $('#editAttenderModal').modal({ backdrop: 'static', keyboard: false });
    var attenderid = $(this).attr('data-id');
    $.ajax({
        url: '<?php echo site_url("admin/attender/getsingleattender"); ?>',
        type: 'post',
        data: { attenderid: attenderid },
        dataType: 'json',
        success: function (response) { $('#editattenderdata').html(response.page); }
    });
});

$('.attenderdetails').click(function () {
    $('#viewAttenderModal').modal({ backdrop: 'static', keyboard: false });
    var attenderid = $(this).attr('data-id');
    $.ajax({
        url: '<?php echo site_url("admin/attender/attenderdetails"); ?>',
        type: 'post',
        data: { attenderid: attenderid },
        dataType: 'json',
        success: function (response) { $('#viewattenderdata').html(response.page); }
    });
});

$("#editattenderform").on('submit', function (e) {
    e.preventDefault();
    var $this = $(this).find("button[type=submit]:focus");
    $.ajax({
        url: "<?php echo site_url('admin/attender/edit'); ?>",
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
