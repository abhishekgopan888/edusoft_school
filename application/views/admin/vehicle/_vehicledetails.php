<div class="nav-tabs-custom" style="box-shadow:none; margin-bottom:0;">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_vehicle_info" data-toggle="tab"><i class="fa fa-info-circle"></i> Vehicle Info & Assigned Students</a></li>
        <li><a href="#tab_vehicle_maintenance" data-toggle="tab"><i class="fa fa-wrench"></i> Maintenance History <span class="label label-warning"><?php echo count($maintenance_list); ?></span></a></li>
    </ul>
    <div class="tab-content" style="padding:15px 0 0 0;">
        <!-- TAB 1: VEHICLE INFO & ASSIGNED STUDENTS -->
        <div class="tab-pane active" id="tab_vehicle_info">
            <div class="row">
                <input type="hidden" name="id" value="<?php echo set_value('id', $editvehicle->id); ?>">
                <div class="col-sm-3 col-md-2 col-lg-2">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('documents'); ?></label>
                        <?php if (!empty($editvehicle->documents)) { ?>
                            <p><a href="<?php echo site_url('admin/vehicle/download/' . $editvehicle->id); ?>"
                                    class="btn btn-default btn-sm"><i class="fa fa-download"></i> View Document</a></p>
                        <?php } else { ?>
                            <div class="route-bus-icon"><i class="fa fa-bus"></i></div>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-lg-10 col-md-10 col-sm-9">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('vehicle_number'); ?>: </b><span><?php echo $editvehicle->vehicle_no; ?></span></div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('vehicle_model'); ?>: </b><span><?php echo $editvehicle->vehicle_model; ?></span></div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('year_made'); ?>: </b><span><?php echo $editvehicle->manufacture_year; ?></span></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('registration_number'); ?>: </b><span><?php echo $editvehicle->registration_number; ?></span></div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('chasis_number'); ?>: </b><span><?php echo $editvehicle->chasis_number; ?></span></div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b><?php echo $this->lang->line('max_seating_capacity'); ?>: </b><span><?php echo $editvehicle->max_seating_capacity; ?></span></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b>Status: </b><span>
                                    <?php
                                    $status = !empty($editvehicle->status) ? $editvehicle->status : 'Active';
                                    if ($status == 'Active') {
                                        echo '<span class="label label-success">Active</span>';
                                    } elseif ($status == 'Inactive') {
                                        echo '<span class="label label-danger">Inactive</span>';
                                    } elseif ($status == 'maintanance') {
                                        echo '<span class="label label-warning">Maintenance</span>';
                                    } elseif ($status == 'Break down') {
                                        echo '<span class="label label-danger">Break Down</span>';
                                    } else {
                                        echo '<span class="label label-default">Active</span>';
                                    }
                                    ?>
                                </span></div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4">
                            <div class="route-text"><b>Occupancy: </b><span>
                                    <?php
                                    $occupied = isset($editvehicle->occupied_seats) ? (int) $editvehicle->occupied_seats : 0;
                                    $capacity = (int) $editvehicle->max_seating_capacity;
                                    if ($capacity > 0 && $occupied >= $capacity) {
                                        echo '<span class="label label-danger">' . $occupied . ' / ' . $capacity . ' (Full)</span>';
                                    } else {
                                        echo '<span class="label label-info">' . $occupied . ' / ' . ($capacity > 0 ? $capacity : '-') . '</span>';
                                    }
                                    ?>
                                </span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row" style="margin-top:10px;">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="route-text pb10"><b><?php echo $this->lang->line('note'); ?>: </b><span><?php echo $editvehicle->note; ?></span></div>
                </div>
            </div>

            <div class="row" style="margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h4 class="box-title" style="font-size:16px; margin-bottom:15px;">
                        <i class="fa fa-users"></i> <?php echo $this->lang->line('assigned_student'); ?> / Students Selected in Bus 
                        <span class="label label-primary pull-right"><?php echo count($student_list); ?> Student(s)</span>
                    </h4>
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th>Route</th>
                                    <th>Pickup Point</th>
                                    <th><?php echo $this->lang->line('mobile_number'); ?> / Guardian Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($student_list)) { 
                                    $count = 1;
                                    foreach ($student_list as $student) {
                                        $student_name = array_filter([$student['firstname'], $student['middlename'], $student['lastname']]);
                                        $full_name = implode(' ', $student_name);
                                        $phone = !empty($student['mobileno']) ? $student['mobileno'] : $student['guardian_phone'];
                                ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo $student['admission_no']; ?></td>
                                        <td>
                                            <a href="<?php echo site_url('student/view/' . $student['id']); ?>" target="_blank">
                                                <b><?php echo $full_name; ?></b>
                                            </a>
                                        </td>
                                        <td><?php echo $student['class'] . ' (' . $student['section'] . ')'; ?></td>
                                        <td><?php echo !empty($student['route_title']) ? $student['route_title'] : '-'; ?></td>
                                        <td><?php echo !empty($student['pickup_point_name']) ? $student['pickup_point_name'] : '-'; ?></td>
                                        <td><?php echo !empty($phone) ? $phone : '-'; ?></td>
                                    </tr>
                                <?php } } else { ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No students currently assigned to this vehicle.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: MAINTENANCE HISTORY -->
        <div class="tab-pane" id="tab_vehicle_maintenance">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h4 class="box-title" style="font-size:16px; margin-bottom:15px;">
                        <i class="fa fa-wrench"></i> Vehicle Maintenance History & Details
                        <a href="<?php echo site_url('admin/maintenance'); ?>" target="_blank" class="btn btn-xs btn-primary pull-right">
                            <i class="fa fa-external-link"></i> Manage Maintenance
                        </a>
                    </h4>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Service Type</th>
                                    <th>Odometer</th>
                                    <th>Last Service</th>
                                    <th>Next Service</th>
                                    <th>Insurance Expired</th>
                                    <th>Tax Expired</th>
                                    <th>Total Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($maintenance_list)) {
                                    $m_count = 1;
                                    $grand_total = 0;
                                    foreach ($maintenance_list as $m_row) {
                                        $grand_total += (float)$m_row['total_cost'];
                                ?>
                                    <tr>
                                        <td><?php echo $m_count++; ?></td>
                                        <td><b><?php echo !empty($m_row['service_type']) ? $m_row['service_type'] : '-'; ?></b></td>
                                        <td><?php echo !empty($m_row['odometer_reading']) ? number_format((float)$m_row['odometer_reading']) . ' km' : '-'; ?></td>
                                        <td><?php echo !empty($m_row['last_service_date']) ? $this->customlib->dateformat($m_row['last_service_date']) : '-'; ?></td>
                                        <td>
                                            <?php if (!empty($m_row['next_service_date'])) {
                                                $days = (strtotime($m_row['next_service_date']) - strtotime(date('Y-m-d'))) / 86400;
                                                $badge_class = ($days <= 0) ? 'label-danger' : (($days <= 10) ? 'label-warning' : 'label-success');
                                                echo $this->customlib->dateformat($m_row['next_service_date']) . ' <span class="label ' . $badge_class . '">' . (int)round($days) . 'd</span>';
                                            } else {
                                                echo '-';
                                            } ?>
                                        </td>
                                        <td><?php echo !empty($m_row['insurance_expired_date']) ? $this->customlib->dateformat($m_row['insurance_expired_date']) : '-'; ?></td>
                                        <td><?php echo !empty($m_row['tax_expired_date']) ? $this->customlib->dateformat($m_row['tax_expired_date']) : '-'; ?></td>
                                        <td><b><?php echo number_format((float)$m_row['total_cost'], 2); ?></b></td>
                                        <td>
                                            <?php 
                                            $m_status = !empty($m_row['status']) ? $m_row['status'] : 'Pending';
                                            $st_class = ($m_status == 'Completed') ? 'success' : (($m_status == 'In Progress') ? 'warning' : 'default');
                                            echo '<span class="label label-' . $st_class . '">' . $m_status . '</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php } } else { ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No maintenance records found for this vehicle.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <?php if (!empty($maintenance_list)) { ?>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-right"><b>Grand Total:</b></th>
                                    <th><b><?php echo number_format((float)$grand_total, 2); ?></b></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>