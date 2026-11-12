<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?> <small><?php //echo $this->lang->line('student_fees1'); ?></small> </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('routes', 'can_add') || $this->rbac->hasPrivilege('routes', 'can_edit')) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('edit_route'); ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/route/edit/' . $id) ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php
if (isset($error_message)) {
        echo "<div class='alert alert-danger'>" . $error_message . "</div>";
    }
    ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('route_title'); ?></label><small class="req"> *</small>
                                    <input type="hidden" name="id" value="<?php echo set_value('id', $editroute['id']); ?>" >
                                    <input autofocus="" id="route_title" name="route_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('route_title', $editroute['route_title']); ?>" />
                                    <span class="text-danger"><?php echo form_error('route_title'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="vehicle"><?php echo $this->lang->line('vehicle'); ?></label>
                                    <select id="vehicle" name="vehicle[]" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php if (!empty($vehiclelist)) {
                                            foreach ($vehiclelist as $vehicle) {
                                                $selected = (!empty($selected_vehicles) && in_array($vehicle['id'], $selected_vehicles)) ? 'selected' : '';
                                        ?>
                                                <option value="<?php echo $vehicle['id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo $vehicle['vehicle_no']; ?>
                                                </option>
                                        <?php } } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('vehicle[]'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="pickup_point"><?php echo $this->lang->line('pickup_point'); ?></label>
                                    <div id="pickup_point_wrapper">
                                        <?php if (!empty($selected_pickup_points)) {
                                            $is_first = true;
                                            $total_pts = count($selected_pickup_points);
                                            foreach ($selected_pickup_points as $s_point_id) {
                                        ?>
                                            <div class="pickup-point-row" style="margin-bottom: 8px;">
                                                <div class="input-group">
                                                    <select name="pickup_point[]" class="form-control pickup_point_select">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php if (!empty($pickup_point_list)) {
                                                            foreach ($pickup_point_list as $pickup_point) { ?>
                                                                <option value="<?php echo $pickup_point['id']; ?>" <?php if ($s_point_id == $pickup_point['id']) echo 'selected'; ?>>
                                                                    <?php echo $pickup_point['name']; ?>
                                                                </option>
                                                        <?php } } ?>
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-primary btn-open-pickup-map" title="<?php echo $this->lang->line('add') . ' ' . $this->lang->line('pickup_point'); ?>">
                                                            <i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-remove-pickup-row" <?php if ($is_first && $total_pts == 1) echo 'style="display:none;"'; ?> title="<?php echo $this->lang->line('remove'); ?>">
                                                            <i class="fa fa-remove"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php
                                            $is_first = false;
                                            }
                                        } else { ?>
                                            <div class="pickup-point-row" style="margin-bottom: 8px;">
                                                <div class="input-group">
                                                    <select name="pickup_point[]" class="form-control pickup_point_select">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php if (!empty($pickup_point_list)) {
                                                            foreach ($pickup_point_list as $pickup_point) { ?>
                                                                <option value="<?php echo $pickup_point['id']; ?>">
                                                                    <?php echo $pickup_point['name']; ?>
                                                                </option>
                                                        <?php } } ?>
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-primary btn-open-pickup-map" title="<?php echo $this->lang->line('add') . ' ' . $this->lang->line('pickup_point'); ?>">
                                                            <i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-remove-pickup-row" style="display:none;" title="<?php echo $this->lang->line('remove'); ?>">
                                                            <i class="fa fa-remove"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <button type="button" id="btn_add_more_pickup" class="btn btn-default btn-xs">
                                            <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_more'); ?>
                                        </button>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('pickup_point[]'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('routes', 'can_add') || $this->rbac->hasPrivilege('routes', 'can_edit')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <div class="box box-primary" id="route">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('route_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                            <div class="pull-right">
                            </div>
                        </div>
                        <div class="mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('route_list'); ?></div>
                            <div class="table-responsive overflow-visible">
                                <table class="table table-striped table-bordered table-hover example" data-export-title="<?php echo $this->lang->line('route_list');?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('route_title'); ?></th>
                                            <th><?php echo $this->lang->line('pickup_point'); ?></th>
                                            <th><?php echo $this->lang->line('vehicle'); ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($listroute)) {
    ?>

                                            <?php
} else {
    $count = 1;
    foreach ($listroute as $data) {
        ?>
                                                <tr>
                                                    <td class="mailbox-name"> <?php echo $data['route_title'] ?></td>
                                                    <td class="mailbox-name"> <?php echo !empty($data['pickup_point_names']) ? $data['pickup_point_names'] : ''; ?></td>
                                                    <td class="mailbox-name"> <?php echo !empty($data['vehicles']) ? $data['vehicles'] : ''; ?></td>
                                                    <td class="mailbox-date pull-right no-print">
                                                        <?php if ($this->rbac->hasPrivilege('routes', 'can_edit')) {?>
                                                            <a href="<?php echo base_url(); ?>admin/route/edit/<?php echo $data['id'] ?>" class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                        <?php }if ($this->rbac->hasPrivilege('routes', 'can_delete')) {?>
                                                            <a href="<?php echo base_url(); ?>admin/route/delete/<?php echo $data['id'] ?>"class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php }?>
                                                    </td>
                                                </tr>
                                                <?php
}
    $count++;
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });
    });
</script>

<?php $this->load->view('admin/route/_pickup_modal'); ?>