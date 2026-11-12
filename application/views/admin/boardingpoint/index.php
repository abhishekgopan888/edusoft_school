<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bus"></i> Boarding Point Tracking
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Boarding Record</h3>
                    </div>
                    <form action="<?php echo base_url(); ?>admin/boardingpoint/add" method="post" id="boardingform">
                        <div class="box-body">
                            <?php echo $this->session->flashdata('msg'); ?>
                            
                            <div class="form-group">
                                <label for="studentid">Student <small class="req">*</small></label>
                                <select id="studentid" name="studentid" class="form-control" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student) { ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo $student['firstname'] . ' ' . $student['lastname'] . ' (' . $student['admission_no'] . ')'; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('studentid'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="busid">Bus / Vehicle <small class="req">*</small></label>
                                <select id="busid" name="busid" class="form-control" required>
                                    <option value="">Select Bus</option>
                                    <?php foreach ($vehicles as $vehicle) { ?>
                                        <option value="<?php echo $vehicle['id']; ?>">
                                            <?php echo $vehicle['vehicle_no'] . ($vehicle['vehicle_model'] ? ' (' . $vehicle['vehicle_model'] . ')' : ''); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('busid'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="attenderid">Attender <small class="req">*</small></label>
                                <select id="attenderid" name="attenderid" class="form-control" required>
                                    <option value="">Select Attender</option>
                                    <?php foreach ($attenders as $attender) { ?>
                                        <option value="<?php echo $attender['id']; ?>">
                                            <?php echo $attender['attender_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('attenderid'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="boarded">Boarded</option>
                                    <option value="deboarded">Deboarded</option>
                                </select>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix">Boarding Point Records</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Bus / Vehicle</th>
                                        <th>Attender</th>
                                        <th>Status</th>
                                        <th>Timestamp</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if (!empty($boarding_list)) {
                                        foreach ($boarding_list as $row) {
                                            ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td><?php echo (!empty($row['firstname']) ? $row['firstname'] . ' ' . $row['lastname'] : 'Student ID #' . $row['studentid']); ?></td>
                                                <td><?php echo (!empty($row['vehicle_no']) ? $row['vehicle_no'] : 'Bus ID #' . $row['busid']); ?></td>
                                                <td><?php echo (!empty($row['attender_name']) ? $row['attender_name'] : 'Attender ID #' . $row['attenderid']); ?></td>
                                                <td>
                                                    <span class="label <?php echo ($row['status'] == 'boarded' ? 'label-success' : 'label-warning'); ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $row['timestamp']; ?></td>
                                                <td class="text-right">
                                                    <a href="<?php echo base_url(); ?>admin/boardingpoint/delete/<?php echo $row['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
