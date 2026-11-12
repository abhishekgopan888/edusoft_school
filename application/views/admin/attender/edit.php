<div class="row">
<input type="hidden" name="id" value="<?php echo $editattender->id; ?>">
<div class="col-lg-12 col-md-12 col-sm-12">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Attender Name</label><small class="req"> *</small>
                <input id="attender_name" name="attender_name" type="text" class="form-control" value="<?php echo $editattender->attender_name; ?>" />
                <span class="text-danger"><?php echo form_error('attender_name'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Age</label>
                <input id="age" name="age" type="text" class="form-control" value="<?php echo $editattender->age; ?>" />
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="Active" <?php if ($editattender->status == 'Active' || $editattender->status == 'active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if ($editattender->status == 'Inactive' || $editattender->status == 'inactive') echo 'selected'; ?>>Inactive</option>
                    <option value="leave" <?php if ($editattender->status == 'leave') echo 'selected'; ?>>Leave</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label>Address</label>
                <input id="address" name="address" type="text" class="form-control" value="<?php echo $editattender->address; ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label>Attender Image</label>
                <?php if (!empty($editattender->attender_image)) { ?>
                    <p><a href="<?php echo site_url('admin/attender/viewfile/'.$editattender->id.'/attender_image'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-image"></i> View Current</a></p>
                <?php } ?>
                <input id="attender_image" name="attender_image" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('attender_image'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>ID Proof Document</label>
                <?php if (!empty($editattender->id_proof)) { ?>
                    <p><a href="<?php echo site_url('admin/attender/viewfile/'.$editattender->id.'/id_proof'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file-image-o"></i> View Current</a></p>
                <?php } ?>
                <input id="id_proof" name="id_proof" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('id_proof'); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Documents (Other)</label>
                <?php if (!empty($editattender->documents)) { ?>
                    <p><a href="<?php echo site_url('admin/attender/viewfile/'.$editattender->id.'/documents'); ?>" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-file"></i> View Current</a></p>
                <?php } ?>
                <input id="documents" name="documents" type="file" class="filestyle form-control" data-height="30" />
                <span class="text-danger"><?php echo form_error('documents'); ?></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label><?php echo $this->lang->line('note'); ?></label>
                <textarea class="form-control" id="note" name="note" rows="2"><?php echo $editattender->note; ?></textarea>
            </div>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.filestyle').dropify();
    });
</script>
