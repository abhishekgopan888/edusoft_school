<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-credit-card"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('assign_vehicle', 'can_add')) { ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('assign_vehicle_on_route'); ?></h3>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo base_url() ?>admin/vehroute"  method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg'); ?>
                                <?php } ?>
                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('route'); ?></label> <small class="req"> *</small>

                                    <select autofocus="" id="route_id" name="route_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($routelist as $route) {
                                            ?>
                                            <option value="<?php echo $route['id'] ?>"<?php
                                            if (set_value('route_id') == $route['id']) {
                                                echo "selected =selected";
                                            }
                                            ?>><?php echo $route['route_title'] ?></option>

                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('route_id'); ?></span>
                                </div>

                                <div class="form-group">
                                    <label for="vehicle_id"><?php echo $this->lang->line('vehicle'); ?></label> <small class="req"> *</small>
                                    <select id="vehicle_id" name="vehicle[]" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($vehiclelist as $vehicle) { ?>
                                            <option value="<?php echo $vehicle['id']; ?>" <?php if (set_value('vehicle[]') == $vehicle['id']) echo 'selected'; ?>>
                                                <?php echo $vehicle['vehicle_no']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('vehicle[]'); ?></span>
                                </div>

                                <div class="form-group">
                                    <label>Driver Name</label> <small class="req"> *</small>
                                    <select name="driver_id" id="driver_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($driverlist as $driver) { ?>
                                            <option value="<?php echo $driver['id']; ?>" <?php if (set_value('driver_id') == $driver['id']) echo 'selected'; ?>>
                                                <?php echo $driver['driver_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('driver_id'); ?></span>
                                </div>

                                <div class="form-group">
                                    <label>Attender Name</label> <small class="req"> *</small>
                                    <select name="attender_id" id="attender_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($attenderlist as $attender) { ?>
                                            <option value="<?php echo $attender['id']; ?>" <?php if (set_value('attender_id') == $attender['id']) echo 'selected'; ?>>
                                                <?php echo $attender['attender_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('attender_id'); ?></span>
                                </div>

                            </div><!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>

                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('assign_vehicle', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('vehicle_route_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('vehicle_route_list'); ?></div>
                            <div class="table-responsive overflow-visible">  
                                <table class="table table-striped table-bordered table-hover example" data-export-title="<?php echo $this->lang->line('vehicle_route_list');?>">
                                    <thead>
                                        <tr>

                                            <th><?php echo $this->lang->line('route'); ?>
                                            </th>
                                            <th><?php echo $this->lang->line('vehicle'); ?>
                                            </th>
                                            <th>Driver Name</th>
                                            <th>Attender Name</th>

                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($vehroutelist)) {
                                            ?>

                                            <?php
                                        } else {
                                            foreach ($vehroutelist as $vehroute) {

                                                ?>
                                                <tr>
                                                    <td class="mailbox-name">
                                                        <?php

                                                         echo $vehroute['route_title']; ?>

                                                    </td>


                                                    <td>
                                                        <?php
                                                        $vehicles = $vehroute['vehicles'];
                                                        if (!empty($vehicles)) {
                                                            foreach ($vehicles as $key => $value) {
                                                                echo "<div><b>" . $value->vehicle_no . "</b></div>";
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($vehroute['driver_id'])) {
                                                            // find driver name from driverlist
                                                            foreach ($driverlist as $drv) {
                                                                if ($drv['id'] == $vehroute['driver_id']) {
                                                                    echo '<span class="label label-info">' . htmlspecialchars($drv['driver_name']) . '</span>';
                                                                    break;
                                                                }
                                                            }
                                                        } else {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($vehroute['attender_id'])) {
                                                            foreach ($attenderlist as $att) {
                                                                if ($att['id'] == $vehroute['attender_id']) {
                                                                    echo '<span class="label label-success">' . htmlspecialchars($att['attender_name']) . '</span>';
                                                                    break;
                                                                }
                                                            }
                                                        } else {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="mailbox-date pull-right">
                                                        <?php if ($this->rbac->hasPrivilege('assign_vehicle', 'can_edit')) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/vehroute/edit/<?php echo $vehroute['id']; ?>" class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                        <?php } if ($this->rbac->hasPrivilege('assign_vehicle', 'can_delete')) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/vehroute/delete/<?php echo $vehroute['id']; ?>"class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>

                                    </tbody>
                                </table><!-- /.table -->
                            </div>


                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
        <div class="row">
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">

            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

