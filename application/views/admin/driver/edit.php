<div class="row">
<input type="hidden" name="id" value="<?php echo isset($editdriver->id) ? $editdriver->id : ''; ?>">
<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Driver Name</label><small class="req"> *</small>
                <input id="driver_name" name="driver_name" type="text" class="form-control" value="<?php echo isset($editdriver->driver_name) ? $editdriver->driver_name : ''; ?>" />
                <span class="text-danger"><?php echo form_error('driver_name'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Age</label>
                <input id="age" name="age" type="text" class="form-control" value="<?php echo isset($editdriver->age) ? $editdriver->age : ''; ?>" />
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Licence No</label>
                <input id="licence" name="licence" type="text" class="form-control" value="<?php echo isset($editdriver->licence) ? $editdriver->licence : ''; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Contact</label>
                <input id="contact" name="contact" type="text" class="form-control" value="<?php echo isset($editdriver->contact) ? $editdriver->contact : ''; ?>" />
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="Active" <?php if (isset($editdriver->status) && $editdriver->status == 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if (isset($editdriver->status) && $editdriver->status == 'Inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Address</label>
                <input id="address" name="address" type="text" class="form-control" value="<?php echo isset($editdriver->address) ? $editdriver->address : ''; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Licence Front Page</label>
                <?php if (!empty($editdriver->licence_front)) { ?>
                    <p><a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/licence_front'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View Current</a></p>
                <?php } ?>
                <input id="licence_front" name="licence_front" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('licence_front'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Licence Back Page</label>
                <?php if (!empty($editdriver->licence_back)) { ?>
                    <p><a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/licence_back'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View Current</a></p>
                <?php } ?>
                <input id="licence_back" name="licence_back" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('licence_back'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Driver Image</label>
                <?php if (!empty($editdriver->driver_image)) { ?>
                    <p><a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/driver_image'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View Current</a></p>
                <?php } ?>
                <input id="driver_image" name="driver_image" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('driver_image'); ?></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>ID Proof Document</label>
                <?php if (!empty($editdriver->id_proof)) { ?>
                    <p><a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/id_proof'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file-image-o"></i> View Current</a></p>
                <?php } ?>
                <input id="id_proof" name="id_proof" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('id_proof'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Documents (Other)</label>
                <?php if (!empty($editdriver->documents)) { ?>
                    <p><a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/documents'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file"></i> View Current</a></p>
                <?php } ?>
                <input id="documents" name="documents" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('documents'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label><?php echo $this->lang->line('note'); ?></label>
                <textarea class="form-control" id="note" name="note" rows="2"><?php echo isset($editdriver->note) ? $editdriver->note : ''; ?></textarea>
            </div>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        if ($.fn.dropify) {
            $('.filestyle').dropify();
        }
    });
</script>
