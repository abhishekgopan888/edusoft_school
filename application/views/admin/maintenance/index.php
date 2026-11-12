<div class="content-wrapper">
    <section class="content">
        <?php if (!empty($maintenance_alerts)) { ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4><i class="fa fa-warning"></i> Maintenance Expiry Alerts</h4>
                <ul style="padding-left: 20px; margin-bottom: 0;">
                    <?php foreach ($maintenance_alerts as $alert) { 
                        $formatted_date = $this->customlib->dateformat($alert['date']);
                        if ($alert['days'] < 0) {
                            $days_text = "Overdue by " . abs($alert['days']) . " day(s) ($formatted_date)";
                        } elseif ($alert['days'] == 0) {
                            $days_text = "Expires today ($formatted_date)";
                        } else {
                            $days_text = "Expiring in " . $alert['days'] . " day(s) ($formatted_date)";
                        }
                        ?>
                        <li>
                            <strong><?php echo $alert['vehicle_name']; ?></strong>: 
                            <?php echo $alert['type']; ?> is 
                            <span class="text-danger"><strong><?php echo $days_text; ?></strong></span>.
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('maintenance_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('vehicle_maintenance', 'can_add')) { ?>
                            <button type="button" class="btn btn-sm btn-primary pull-right" data-toggle="modal" data-backdrop="static" data-target="#addMaintenanceModal">
                                <i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?>
                            </button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="download_label"><?php echo $this->lang->line('maintenance_list'); ?></div>
                        <table class="table table-hover table-striped table-bordered example" data-export-title="<?php echo $this->lang->line('maintenance_list'); ?>">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('vehicle'); ?></th>
                                    <th><?php echo $this->lang->line('service_type'); ?></th>
                                    <th><?php echo $this->lang->line('last_service_date'); ?></th>
                                    <th><?php echo $this->lang->line('next_service_date'); ?></th>
                                    <th><?php echo $this->lang->line('insurance_expired_date'); ?></th>
                                    <th><?php echo $this->lang->line('tax_expired_date'); ?></th>
                                    <th><?php echo $this->lang->line('total_cost'); ?></th>
                                    <th><?php echo $this->lang->line('status'); ?></th>
                                    <th class="text-right noExport" width="10%"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sn = 1; 
                                $grand_total = 0;
                                foreach ($listMaintenance as $row) { 
                                    $grand_total += (float)$row['total_cost'];
                                ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td><?php echo $row['vehicle_no'] . " (" . $row['vehicle_model'] . ")"; ?></td>
                                        <td><?php echo $row['service_type']; ?></td>
                                        <td><?php echo $row['last_service_date'] ? $this->customlib->dateformat($row['last_service_date']) : '-'; ?></td>
                                        <td><?php echo $row['next_service_date'] ? $this->customlib->dateformat($row['next_service_date']) : '-'; ?></td>
                                        <td><?php echo $row['insurance_expired_date'] ? $this->customlib->dateformat($row['insurance_expired_date']) : '-'; ?></td>
                                        <td><?php echo $row['tax_expired_date'] ? $this->customlib->dateformat($row['tax_expired_date']) : '-'; ?></td>
                                        <td><b><?php echo number_format((float)$row['total_cost'], 2); ?></b></td>
                                        <td>
                                            <span class="label label-<?php echo $row['status'] == 'Completed' ? 'success' : ($row['status'] == 'In Progress' ? 'warning' : ($row['status'] == 'Pending' ? 'default' : ($row['status'] == 'Inactive' ? 'danger' : 'primary'))); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td class="mailbox-date pull-right no-print white-space-nowrap">
                                            <a class="btn btn-success btn-xs viewmaintenance" data-id="<?php echo $row['id']; ?>" title="View"><i class="fa fa-eye"></i></a>
                                            <?php if ($this->rbac->hasPrivilege('vehicle_maintenance', 'can_edit')) { ?>
                                                <a class="btn btn-primary btn-xs editmaintenance" data-id="<?php echo $row['id']; ?>" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                            <?php } if ($this->rbac->hasPrivilege('vehicle_maintenance', 'can_delete')) { ?>
                                                <a href="<?php echo base_url(); ?>admin/maintenance/delete/<?php echo $row['id']; ?>" class="btn btn-danger btn-xs" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-trash-o"></i></a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <?php if (!empty($listMaintenance)) { ?>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-right"><b>Grand Total:</b></th>
                                    <th><b><?php echo number_format((float)$grand_total, 2); ?></b></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ===== ADD MODAL ===== -->
<div class="modal fade" id="addMaintenanceModal" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width:1000px;">
        <div class="modal-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('add_maintenance'); ?></h4>
            </div>
            <form id="addmaintenanceform" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php $this->load->view('admin/maintenance/_form', array('listVehicle' => $listVehicle, 'rec' => null)); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('please_wait'); ?>">
                        <?php echo $this->lang->line('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== EDIT MODAL ===== -->
<div class="modal fade" id="editMaintenanceModal" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width:1000px;">
        <div class="modal-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('edit_maintenance'); ?></h4>
            </div>
            <form id="editmaintenanceform" method="post" enctype="multipart/form-data">
                <div class="modal-body" id="editmaintenancedata"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('please_wait'); ?>">
                        <?php echo $this->lang->line('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== VIEW MODAL ===== -->
<div class="modal fade" id="viewMaintenanceModal" role="dialog" aria-labelledby="viewMaintenanceModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title">Maintenance Details</h4>
            </div>
            <div class="modal-body pt0 pb0">
                <div id="viewmaintenancedata"></div>
            </div>
        </div>
    </div>
</div>

<style>
.maint-section-title { font-weight: 600; color: #2980b9; font-size: 14px; margin-bottom:10px; }
.maint-section-title i { margin-right:6px; }
.maint-panel { border:1px solid #ddd; border-radius:6px; padding:15px; margin-bottom:15px; background:#fafbfc; }
.maint-total-badge { display:inline-block; background:#e74c3c; color:#fff; border-radius:20px; padding:3px 12px; font-weight:700; font-size:13px; float:right; }
.maint-total-badge.blue { background:#2980b9; }
.labour-same-badge { float:right; }
.parts-row, .labour-row { display:flex; gap:8px; margin-bottom:6px; align-items:center; }
.parts-row input, .labour-row input { flex:1; }
.parts-row .price-col, .labour-row .cost-col { flex:0 0 130px; }
.add-row-btn { width:100%; border:1px dashed #bbb; background:#f5f6fa; color:#555; padding:6px; border-radius:4px; cursor:pointer; text-align:center; margin-top:4px; }
.add-row-btn:hover { background:#eef; }
.remove-row-btn { background:none; border:none; color:#e74c3c; font-size:16px; cursor:pointer; padding:0 4px; }
</style>

<script>
(function($){
    // reset add form on modal close
    $('#addMaintenanceModal').on('hidden.bs.modal', function(){
        $(this).find('form')[0].reset();
        $('#add_parts_body').html(makePartRow());
        $('#add_labour_body').html(makeLabourRow());
        calcTotals('add');
    });

    // ---- Submit Add ----
    $('#addmaintenanceform').on('submit', function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type=submit]');
        $.ajax({
            url: '<?php echo site_url("admin/maintenance/add"); ?>',
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            contentType: false, cache: false, processData: false,
            beforeSend: function(){ $btn.button('loading'); },
            success: function(res){
                if(res.status=='fail'){ var m=''; $.each(res.error, function(i,v){ m+=v; }); errorMsg(m); }
                else { successMsg(res.message); window.location.reload(true); }
            },
            error: function(){ alert('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>'); },
            complete: function(){ $btn.button('reset'); }
        });
    });

    // ---- View trigger ----
    $(document).on('click', '.viewmaintenance', function(){
        var id = $(this).data('id');
        $('#viewmaintenancedata').html('');
        $('#viewMaintenanceModal').modal('show');
        $.ajax({
            url: '<?php echo site_url("admin/maintenance/getsinglemaintenanceview"); ?>',
            type: 'post', data: {id: id}, dataType: 'json',
            success: function(res){ $('#viewmaintenancedata').html(res.page); }
        });
    });

    // ---- Edit trigger ----
    $(document).on('click', '.editmaintenance', function(){
        var id = $(this).data('id');
        $('#editMaintenanceModal').modal({ backdrop:'static', keyboard:false });
        $.ajax({
            url: '<?php echo site_url("admin/maintenance/getsinglemaintenancedata"); ?>',
            type: 'post', data: {id: id}, dataType: 'json',
            success: function(res){ $('#editmaintenancedata').html(res.page); }
        });
    });

    // ---- Submit Edit ----
    $(document).on('submit', '#editmaintenanceform', function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type=submit]');
        $.ajax({
            url: '<?php echo site_url("admin/maintenance/edit"); ?>',
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            contentType: false, cache: false, processData: false,
            beforeSend: function(){ $btn.button('loading'); },
            success: function(res){
                if(res.status=='fail'){ var m=''; $.each(res.error, function(i,v){ m+=v; }); errorMsg(m); }
                else { successMsg(res.message); window.location.reload(true); }
            },
            error: function(){ alert('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>'); },
            complete: function(){ $btn.button('reset'); }
        });
    });
})(jQuery);
</script>
