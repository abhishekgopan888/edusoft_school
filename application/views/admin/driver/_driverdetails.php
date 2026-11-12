<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 15px;">
        <div class="row">
            <div class="col-lg-2 col-md-2 col-sm-3">
                <?php if (!empty($editdriver->driver_image)) { ?>
                    <img src="<?php echo $this->media_storage->getImageURL('/uploads/driver_documents/'.$editdriver->driver_image); ?>" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" />
                <?php } else { ?>
                    <i class="fa fa-user-circle-o fa-5x text-muted"></i>
                <?php } ?>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-9">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <div class="route-text"><b>Driver Name: </b><span><?php echo isset($editdriver->driver_name) ? $editdriver->driver_name : ''; ?></span></div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <div class="route-text"><b>Age: </b><span><?php echo isset($editdriver->age) ? $editdriver->age : ''; ?></span></div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <div class="route-text"><b>Licence No: </b><span><?php echo isset($editdriver->licence) ? $editdriver->licence : ''; ?></span></div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3">
                        <div class="route-text"><b>Status: </b><span>
                            <?php
                            $status = !empty($editdriver->status) ? $editdriver->status : 'Active';
                            if ($status == 'Active') {
                                echo '<span class="label label-success">Active</span>';
                            } else {
                                echo '<span class="label label-danger">Inactive</span>';
                            }
                            ?>
                        </span></div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class="route-text"><b>Contact: </b><span><?php echo isset($editdriver->contact) ? $editdriver->contact : ''; ?></span></div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-8">
                        <div class="route-text"><b>Address: </b><span><?php echo isset($editdriver->address) ? $editdriver->address : ''; ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
            <div class="col-sm-4">
                <b>Licence Front Page:</b><br/>
                <?php if (!empty($editdriver->licence_front)) { ?>
                    <a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/licence_front'); ?>" target="_blank">
                        <img src="<?php echo $this->media_storage->getImageURL('/uploads/driver_documents/'.$editdriver->licence_front); ?>" class="img-thumbnail" style="max-height: 150px;" />
                    </a>
                <?php } else { echo 'Not Uploaded'; } ?>
            </div>
            <div class="col-sm-4">
                <b>Licence Back Page:</b><br/>
                <?php if (!empty($editdriver->licence_back)) { ?>
                    <a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/licence_back'); ?>" target="_blank">
                        <img src="<?php echo $this->media_storage->getImageURL('/uploads/driver_documents/'.$editdriver->licence_back); ?>" class="img-thumbnail" style="max-height: 150px;" />
                    </a>
                <?php } else { echo 'Not Uploaded'; } ?>
            </div>
            <div class="col-sm-4">
                <b>ID Proof Document:</b><br/>
                <?php if (!empty($editdriver->id_proof)) { ?>
                    <a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/id_proof'); ?>" target="_blank">
                        <img src="<?php echo $this->media_storage->getImageURL('/uploads/driver_documents/'.$editdriver->id_proof); ?>" class="img-thumbnail" style="max-height: 150px;" />
                    </a>
                <?php } else { echo 'Not Uploaded'; } ?>
            </div>
        </div>

        <?php if (!empty($editdriver->note)) { ?>
        <div class="row" style="margin-top:15px; border-top: 1px solid #eee; padding-top: 10px;">
            <div class="col-lg-12">
                <div class="route-text pb10"><b>Note: </b><span><?php echo $editdriver->note; ?></span></div>
            </div>
        </div>
        <?php } ?>
        
        <?php if (!empty($editdriver->documents)) { ?>
        <div class="row" style="margin-top:10px;">
            <div class="col-lg-12">
                <b>Other Documents: </b>
                <a href="<?php echo site_url('admin/driver/download/'.$editdriver->id.'/documents'); ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-file"></i> View Document</a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
