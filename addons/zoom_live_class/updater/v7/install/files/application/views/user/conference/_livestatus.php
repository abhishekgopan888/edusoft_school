<?php

$name = ($live->create_for_surname == "") ? $live->create_for_name : $live->create_for_name . " " . $live->create_for_surname . " (" . $live->for_create_empid .")";
$st_label="label label-success";
?>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="modal-header zoommodal-title">
            <h4 class="modal-title"><?php echo $live->title; ?></h4>
        </div>
    </div>

    <div class="col-lg-4 col-md-4">
        <label>
            <span class="labalblock"> <?php echo $this->lang->line('host'); ?></span> <span class="text-dark robo-normal"><span class="text-dark"><?php echo $name; ?></span>
        </label>
    </div>

    <div class="col-lg-4 col-md-4">
        <label>
            <span class="labalblock"> <?php echo $this->lang->line('date_time'); ?></span> <span class="text-dark robo-normal"><span class="text-dark"> <?php echo $this->customlib->dateyyyymmddToDateTimeformat($live->date); ?></span>
        </label>
    </div>

    <div class="col-lg-4 col-md-4">
        <label>
            <span class="labalblock"> <?php echo $this->lang->line('class_duration_minutes'); ?></span> <span class="text-dark robo-normal"><span class="text-dark"><?php echo $live->duration; ?></span>
        </label>
    </div>
</div>
  <div class="zoommodal-border">   
 
<?php

       if ($conference_setting->use_zoom_app_user) {
         ?>
        
        
   <a data-placement="left" href="<?php echo $live_url->join_url; ?>" class="btn btn-outline-success btn-sm pull-right join-btn" data-id="<?php echo $live->id; ?>" target="_blank">
      <i class="fa fa-video-camera"></i> <?php echo $this->lang->line('join') . ' ' . $this->lang->line('now'); ?>
      </a>
     
  <?php 
}else{
 ?> 
      <a data-placement="left" href="<?php echo site_url('user/conference/join/'.$live->id); ?>" class="btn btn-outline-success btn-sm pull-right">
            <i class="fa fa-video-camera"></i> <?php echo $this->lang->line('join') . ' ' . $this->lang->line('now'); ?>
      </a>
 
  <?php 
}
 

?>
</div>