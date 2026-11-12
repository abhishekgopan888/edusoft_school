<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/zoom_addon.css">
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-gears"></i> <small class="pull-right">
                <a type="button" class="btn btn-primary btn-sm"><?php echo $this->lang->line('setting') ?></a>
            </small>
        </h1>
    </section>   
    <section class="content">
        <div class="row">
            <div class="col-md-12">             
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-envelope"></i> <?php echo $this->lang->line('setting') ?></h3>
                    </div>   
                    <form id="form1" action="<?php echo base_url() ?>admin/conference"   name="employeeform" class="form-horizontal form-label-left" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <div>
                                <?php 
                                 if(!$this->session->has_userdata('zoom_access_token')){
                                   ?>
                                   <div class="alert alert-info"><?php echo $this->lang->line('access_token_not_generated_please_authenticate_your_account'); ?></div>
                                   <?php
                                }                                
                                ?>
                            </div>
                            <div class="row">
<div class="col-lg-7 col-md-7 col-sm-6">
 

                            <?php
                              if ($this->session->flashdata('msg')) { 
                                ?>
                                <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                            <?php
                             } 
                            ?>   
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1">
                                    <?php echo $this->lang->line('zoom_api_key'); ?><small class="req"> *</small>
                                </label>
                                <div class="col-md-8 col-sm-8 col-xs-12">
                                    <input id="name" name="zoom_api_key" placeholder="" type="text" class="form-control col-md-7 col-xs-12" value="<?php echo set_value('zoom_api_key', $setting->zoom_api_key); ?>" />
                                    <span class="text-danger"><?php echo form_error('zoom_api_key'); ?></span>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1">
                                    <?php echo $this->lang->line('zoom_api_secret'); ?><small class="req"> *</small>
                                </label>
                                <div class="col-md-8 col-sm-8 col-xs-12">
                                    <input id="name" name="zoom_api_secret" placeholder="" type="text" class="form-control col-md-7 col-xs-12" value="<?php echo set_value('zoom_api_secret', $setting->zoom_api_secret); ?>" />
                                    <span class="text-danger"><?php echo form_error('zoom_api_secret'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1">
                               <?php echo $this->lang->line('teacher_api_credential'); ?><small class="req"> *</small>
                                </label>
                                <div class="col-md-8 col-sm-8 col-xs-12">
                                    <label class="radio-inline">
                                                    <input type="radio" name="use_teacher_api" value="0" <?php
                                                    if (!$setting->use_teacher_api) {
                                                        echo "checked";
                                                    }
                                                    ?> ><?php echo $this->lang->line('disabled'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="use_teacher_api" value="1" <?php
                                                    if ($setting->use_teacher_api) {
                                                        echo "checked";
                                                    }
                                                    ?>><?php echo $this->lang->line('enabled'); ?>
                                                </label>
                                    <span class="text-danger"><?php echo form_error('use_teacher_api'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1">
                                <?php echo $this->lang->line('use_zoom_client_for_staff'); ?><small class="req"> *</small>
                                </label>
                                 <div class="col-md-8 col-sm-8 col-xs-12">
                                    <label class="radio-inline">
                                                    <input type="radio" name="use_zoom_app" value="0" <?php
                                                    if (!$setting->use_zoom_app) {
                                                        echo "checked";
                                                    }
                                                    ?> ><?php echo $this->lang->line('web'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="use_zoom_app" value="1" <?php
                                                    if ($setting->use_zoom_app) {
                                                        echo "checked";
                                                    }
                                                    ?>><?php echo $this->lang->line('zoom_app'); ?>
                                                </label>

                                    <span class="text-danger"><?php echo form_error('use_zoom_app'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1"> <?php echo $this->lang->line('use_zoom_client_for_student'); ?>
                                 <small class="req"> *</small>
                                </label>
                                 <div class="col-md-8 col-sm-8 col-xs-12">
                                    <label class="radio-inline">
                                <input type="radio" name="use_zoom_app_user" value="0" <?php
                                                    if (!$setting->use_zoom_app_user) {
                                                        echo "checked";
                                                    }
                                                    ?> ><?php echo $this->lang->line('web'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="use_zoom_app_user" value="1" <?php
                                                    if ($setting->use_zoom_app_user) {
                                                        echo "checked";
                                                    }
                                                    ?>><?php echo $this->lang->line('zoom_app'); ?>
                                                </label>

                                    <span class="text-danger"><?php echo form_error('use_zoom_app_user'); ?></span>
                                </div>
                            </div>
                            
                            
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="exampleInputEmail1"><?php echo $this->lang->line('parent_live_class') ?><small class="req"> *</small>
                                </label>
                                 <div class="col-md-8 col-sm-8 col-xs-12">
                                    <label class="radio-inline">
                                                    <input type="radio" name="parent_live_class" value="0" <?php
                                                    if (!$setting->parent_live_class) {
                                                        echo "checked";
                                                    }
                                                    ?> ><?php echo $this->lang->line('disabled'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="parent_live_class" value="1" <?php
                                                    if ($setting->parent_live_class) {
                                                        echo "checked";
                                                    }
                                                    ?>><?php echo $this->lang->line('enabled'); ?>
                                                </label>

                                    <span class="text-danger"><?php echo form_error('parent_live_class'); ?></span>
                                </div>
                            </div>


</div>
<div class="col-lg-5 col-md-5 col-sm-6">
    <div class="ps-lg-3 pt-sm-3">
        <div class="mb10"><img src="<?php echo base_url(); ?>backend/images/zoom-icon.png" /></div>
        <p><?php echo $this->lang->line('to_set_zoom_api'); ?> <a class="display-inline" href="https://marketplace.zoom.us/"> <?php echo $this->lang->line('click_here'); ?></a></p>
        <p class="pb0 mb0"><?php echo $this->lang->line('set_zoom_redirect_url'); ?>:</p>
        <p  class="word-break-all"><?php echo  base_url().'admin/conference/generatetoken';?></p>
        <a href='<?php echo $oAuthURL;?>' class="btn btn-primary"> <?php echo $this->lang->line('get_access_token'); ?> </a>   
    </div>    
</div>
                            </div>                        
                           
                           
                        </div>
                        <div class="box-footer">
                            <div class="col-md-6 col-sm-6 col-xs-6 col-md-offset-3">
                                <?php
                                if ($this->rbac->hasPrivilege('setting', 'can_edit')) {
                                    ?>
                                    <button type="submit" class="btn btn-info pull-left"><?php echo $this->lang->line('save'); ?></button>
                                    <?php
                                }
                                ?>                           
                            </div> 
							<div class="pull-right"><?php echo $this->lang->line('version') . " " . $version; ?></div>
                        </div>
                    </form>
                </div>
            </div>           
        </div>
    </section>
</div>