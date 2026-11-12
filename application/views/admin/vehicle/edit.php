<div class="row">
    <input type="hidden" name="id" value="<?php echo set_value('id', $editvehicle->id); ?>">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('vehicle_number'); ?></label><small class="req"> *</small>
                    <input id="vehicle_no" name="vehicle_no" placeholder="" type="text" class="form-control"
                        value="<?php echo set_value('vehicle_no', $editvehicle->vehicle_no); ?>" />
                    <span class="text-danger"><?php echo form_error('vehicle_no'); ?></span>
                </div>

            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('vehicle_model'); ?></label>
                    <input id="vehicle_model" name="vehicle_model" placeholder="" type="text" class="form-control"
                        value="<?php echo set_value('vehicle_model', $editvehicle->vehicle_model); ?>" />
                    <span class="text-danger"><?php echo form_error('vehicle_model'); ?></span>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('year_made'); ?> </label>
                    <input id="manufacture_year" name="manufacture_year" placeholder="" type="text" class="form-control"
                        value="<?php echo set_value('manufacture_year', $editvehicle->manufacture_year); ?>" />
                    <span class="text-danger"><?php echo form_error('manufacture_year'); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('registration_number'); ?> </label>
                    <input id="registration_number" name="registration_number" placeholder="" type="text"
                        class="form-control"
                        value="<?php echo set_value('registration_number', $editvehicle->registration_number); ?>" />
                    <span class="text-danger"><?php echo form_error('registration_number'); ?></span>
                </div>

            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('chasis_number'); ?> </label>
                    <input id="chasis_number" name="chasis_number" placeholder="" type="text" class="form-control"
                        value="<?php echo set_value('chasis_number', $editvehicle->chasis_number); ?>" />
                    <span class="text-danger"><?php echo form_error('chasis_number'); ?></span>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('max_seating_capacity'); ?> </label>
                    <input id="max_seating_capacity" name="max_seating_capacity" placeholder="" type="text"
                        class="form-control"
                        value="<?php echo set_value('max_seating_capacity', $editvehicle->max_seating_capacity); ?>" />
                    <span class="text-danger"><?php echo form_error('max_seating_capacity'); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="Active" <?php if ($editvehicle->status == 'Active')
                            echo 'selected'; ?>>Active
                        </option>
                        <option value="Inactive" <?php if ($editvehicle->status == 'Inactive')
                            echo 'selected'; ?>>
                            Inactive</option>
                        <option value="maintanance" <?php if ($editvehicle->status == 'maintanance')
                            echo 'selected'; ?>>
                            maintanance</option>
                        <option value="Break down" <?php if ($editvehicle->status == 'Break down')
                            echo 'selected'; ?>>
                            Break down</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('documents'); ?></label>
                    <?php if (!empty($editvehicle->documents)) { ?>
                        <p><a href="<?php echo site_url('admin/vehicle/download/' . $editvehicle->id); ?>"
                                class="btn btn-xs btn-default"><i class="fa fa-download"></i> View Current</a>
                        </p>
                    <?php } ?>
                    <input id="documents" name="documents" placeholder="" type="file" class="filestyle form-control"
                        data-height="30" />
                    <span class="text-danger"><?php echo form_error('documents'); ?></span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label><?php echo $this->lang->line('note'); ?></label>
                    <textarea class="form-control" id="note" name="note" placeholder=""
                        rows="2"><?php echo set_value('note', $editvehicle->note); ?></textarea>
                    <span class="text-danger"><?php echo form_error('note'); ?></span>
                </div>
            </div>
        </div>
    </div><!--./row-->
</div><!--./col-md-12-->
<script type="text/javascript">
    $(document).ready(function () {
        // Basic
        $('.filestyle').dropify();
    });
</script>