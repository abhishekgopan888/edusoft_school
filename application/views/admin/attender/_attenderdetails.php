<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 15px;">
        <div class="row">
            <div class="col-lg-2 col-md-2 col-sm-3">
                <?php if (!empty($editattender->attender_image)) {
                    $img_path = (!empty($editattender->is_staff)) ? 'uploads/staff_images/'.$editattender->attender_image : 'uploads/attender_documents/'.$editattender->attender_image;
                ?>
                    <img src="<?php echo $this->media_storage->getImageURL($img_path); ?>" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;" />
                <?php } else { ?>
                    <i class="fa fa-user-circle-o fa-5x text-muted"></i>
                <?php } ?>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-9">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class="route-text"><b>Attender Name: </b><span><?php echo $editattender->attender_name; ?></span></div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class="route-text"><b>Age: </b><span><?php echo $editattender->age; ?></span></div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class="route-text"><b>Status: </b><span>
                            <?php
                            $status = !empty($editattender->status) ? $editattender->status : 'Active';
                            if ($status == 'Active' || $status == 'active') {
                                echo '<span class="label label-success">Active</span>';
                            } elseif ($status == 'Inactive' || $status == 'inactive') {
                                echo '<span class="label label-danger">Inactive</span>';
                            } elseif ($status == 'leave') {
                                echo '<span class="label label-warning">Leave</span>';
                            } else {
                                echo '<span class="label label-default">Active</span>';
                            }
                            ?>
                        </span></div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="route-text"><b>Address: </b><span><?php echo $editattender->address; ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
            <div class="col-sm-6">
                <b>ID Proof Document:</b><br/>
                <?php if (!empty($editattender->id_proof)) {
                    $ext = strtolower(pathinfo($editattender->id_proof, PATHINFO_EXTENSION));
                    $view_url = site_url('admin/attender/viewfile/'.$editattender->id.'/id_proof');
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
                        <a href="<?php echo $view_url; ?>" target="_blank">
                            <img src="<?php echo $view_url; ?>" class="img-thumbnail" style="max-height: 150px;" />
                        </a>
                    <?php } else { ?>
                        <a href="<?php echo $view_url; ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-file-pdf-o"></i> View ID Proof (<?php echo strtoupper($ext); ?>)</a>
                    <?php }
                } else { echo 'Not Uploaded'; } ?>
            </div>
            <div class="col-sm-6">
                <b>Documents (Other):</b><br/>
                <?php if (!empty($editattender->documents)) {
                    $ext = strtolower(pathinfo($editattender->documents, PATHINFO_EXTENSION));
                    $view_url = site_url('admin/attender/viewfile/'.$editattender->id.'/documents');
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
                        <a href="<?php echo $view_url; ?>" target="_blank">
                            <img src="<?php echo $view_url; ?>" class="img-thumbnail" style="max-height: 150px;" />
                        </a>
                    <?php } else { ?>
                        <a href="<?php echo $view_url; ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-file-pdf-o"></i> View Document (<?php echo strtoupper($ext); ?>)</a>
                    <?php }
                } else { echo 'Not Uploaded'; } ?>
            </div>
        </div>

        <?php if (!empty($editattender->note)) { ?>
        <div class="row" style="margin-top:15px; border-top: 1px solid #eee; padding-top: 10px;">
            <div class="col-lg-12">
                <div class="route-text pb10"><b>Note: </b><span><?php echo $editattender->note; ?></span></div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
