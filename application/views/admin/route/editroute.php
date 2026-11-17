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
                                    <label for="start_pickup_point_id"><i class="fa fa-map-marker text-success"></i> <?php echo $this->lang->line('pickup_point'); ?> (Start Location)</label><small class="req"> *</small>
                                    <div class="input-group">
                                        <select id="start_pickup_point_id" name="start_pickup_point_id" class="form-control pickup_point_select">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($pickup_point_list)) {
                                                foreach ($pickup_point_list as $pickup_point) {
                                                    $sel = (set_value('start_pickup_point_id', $selected_start_pickup) == $pickup_point['id']) ? 'selected' : '';
                                            ?>
                                                    <option value="<?php echo $pickup_point['id']; ?>" <?php echo $sel; ?>>
                                                        <?php echo $pickup_point['name']; ?>
                                                    </option>
                                            <?php } } ?>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-success btn-open-pickup-map" data-title="Add / Pick Pickup Point (Start Location)" title="Pick on Map">
                                                <i class="fa fa-map-marker"></i> Pick on Map
                                            </button>
                                        </span>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('start_pickup_point_id'); ?></span>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-road text-primary"></i> Between Stops (Intermediate Stops)</label>
                                    <div id="between_stops_wrapper">
                                        <?php if (!empty($selected_between_stops)) {
                                            $b_seq = 1;
                                            foreach ($selected_between_stops as $bs_id) {
                                        ?>
                                            <div class="between-stop-row" style="margin-bottom: 8px;">
                                                <div class="input-group">
                                                    <span class="input-group-addon stop-seq-badge"><?php echo $b_seq++; ?></span>
                                                    <select name="between_stops[]" class="form-control pickup_point_select">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php if (!empty($pickup_point_list)) {
                                                            foreach ($pickup_point_list as $pickup_point) {
                                                                $sel = ($bs_id == $pickup_point['id']) ? 'selected' : '';
                                                        ?>
                                                                <option value="<?php echo $pickup_point['id']; ?>" <?php echo $sel; ?>>
                                                                    <?php echo $pickup_point['name']; ?>
                                                                </option>
                                                        <?php } } ?>
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-info btn-open-pickup-map" data-title="Add / Select Intermediate Stop" title="Pick on Map">
                                                            <i class="fa fa-map-marker"></i> Map
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-remove-between-row" title="Remove">
                                                            <i class="fa fa-remove"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php
                                            }
                                        } ?>
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <button type="button" id="btn_add_more_between" class="btn btn-default btn-xs">
                                            <i class="fa fa-plus"></i> Add Between Stop
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="end_drop_point_id"><i class="fa fa-flag-checkered text-danger"></i> Drop Point (Destination / End Location)</label>
                                    <div class="input-group">
                                        <select id="end_drop_point_id" name="end_drop_point_id" class="form-control pickup_point_select">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($pickup_point_list)) {
                                                foreach ($pickup_point_list as $pickup_point) {
                                                    $sel = (set_value('end_drop_point_id', $selected_end_drop) == $pickup_point['id']) ? 'selected' : '';
                                            ?>
                                                    <option value="<?php echo $pickup_point['id']; ?>" <?php echo $sel; ?>>
                                                        <?php echo $pickup_point['name']; ?>
                                                    </option>
                                            <?php } } ?>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-danger btn-open-pickup-map" data-title="Add / Pick Drop Point (Destination)" title="Pick on Map">
                                                <i class="fa fa-map-marker"></i> Pick on Map
                                            </button>
                                        </span>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('end_drop_point_id'); ?></span>
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
                                            <th><i class="fa fa-map-marker text-success"></i> Pickup Point (Start)</th>
                                            <th><i class="fa fa-flag-checkered text-danger"></i> Drop Point (End)</th>
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
        $start_display = !empty($data['start_point_name']) ? $data['start_point_name'] : (!empty($data['pickup_point_names']) ? explode(',', $data['pickup_point_names'])[0] : '-');
        $drop_display  = !empty($data['drop_point_name']) ? $data['drop_point_name'] : '-';
        ?>
                                                <tr>
                                                    <td class="mailbox-name"> <?php echo $data['route_title'] ?></td>
                                                    <td class="mailbox-name"><span class="label label-success"><i class="fa fa-map-marker"></i></span> <?php echo $start_display; ?></td>
                                                    <td class="mailbox-name"><span class="label label-danger"><i class="fa fa-flag-checkered"></i></span> <?php echo $drop_display; ?></td>
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