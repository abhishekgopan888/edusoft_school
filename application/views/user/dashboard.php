<div class="content-wrapper">
    <section class="content pb0">
		<?php
        foreach ($unread_notifications as $notice_key => $notice_value) {
        ?>
            <div class="dashalert alert alert-success alert-dismissible" role="alert">
                <button type="button" class="alertclose close close_notice stualert" data-dismiss="alert" aria-label="Close" data-noticeid="<?php echo $notice_value->id; ?>"><span aria-hidden="true">&times;</span></button>
                <a href="<?php echo site_url('user/notification') ?>"><?php echo $notice_value->title; ?></a>
            </div>
        <?php
        }
        ?>
    	<div class="row">
    		<?php 

    		if ($this->customlib->get_user_dashboard_setting_status('welcome_student')) { ?>
    		<div class="col-lg-6 col-md-6 col-sm-12">
	    		<div class="box borderwhite shadow">
	                <div class="box-body direct-top-equal-scroll-22">
	                	<div class="row">
	                		<div class="col-lg-3 col-md-3 col-sm-3">
								<?php   
									if (!empty($student_data["image"])) {
                                        $file = base_url() . $student_data["image"].img_time();
                                    } else {                            
                                        if ($student_data['gender'] == 'Female') {
                                            $file = base_url() . "uploads/student_images/default_female.jpg".img_time();
                                        } else {
                                            $file = base_url() . "uploads/student_images/default_male.jpg".img_time();
                                        }
                                    }
								?>
							
	                			<img src="<?php echo $file.''.img_time(); ?>" class="img-rounded img-responsive img-h-150 mb-xs-1">											
											
	                		</div><!--./col-lg-3-->
	                		<div class="col-lg-9 col-md-9 col-sm-9">
								
								<?php 								
								if($attendence_percentage == '-1' ){ ?>
								
									<h4 class="mt0"><?php echo $this->lang->line('welcome'); ?>, <?php echo $studentsession_username; ?></h4>
									
								<?php } elseif($attendence_percentage > 0 && $attendence_percentage < $low_attendance_limit && $low_attendance_limit != '0.00' ){ ?>
								
									<h4 class="mt0"><?php echo $this->lang->line('welcome'); ?>, <?php echo $studentsession_username; ?>! <?php echo $this->lang->line('need_improvement'); ?>.</h4>
									<p class="text-danger"><?php echo $this->lang->line('your_current_attendance_is'); ?> <?php echo $attendence_percentage ; ?>% <?php echo $this->lang->line('which_is_lower_than');?> <?php echo $low_attendance_limit; ?>% <?php echo $this->lang->line('of_minimum_attendance_mark'); ?>. </p>				
								
								<?php } elseif($attendence_percentage > 0 && $attendence_percentage >= $low_attendance_limit && $low_attendance_limit != '0.00'){ ?>
								
									<h4 class="mt0"><?php echo $this->lang->line('welcome'); ?>, <?php echo $studentsession_username; ?>! <?php echo $this->lang->line('keep_going'); ?>.</h4>
									<p class="text-success"><?php echo $this->lang->line('your_current_attendance_is'); ?> <?php echo $attendence_percentage ; ?>% <?php echo $this->lang->line('which_is_above'); ?> <?php echo $low_attendance_limit; ?>% <?php echo $this->lang->line('of_minimum_attendance_mark'); ?>.</p>	
									
								<?php }else{ ?>	
									
									<h4 class="mt0"><?php echo $this->lang->line('welcome'); ?>, <?php echo $studentsession_username; ?></h4>
								<?php } ?>
								<div class="d-flex align-items-center" style="gap:10px; margin-top:10px; flex-wrap: wrap;">	
									<a class="text-aqua" href="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/' . $student_data['student_id'] . '.png'); ?>" target="_blank">
                                            <img class="h-50 qrcodeimg" src="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/' . $student_data['student_id'] . '.png'); ?>" width="auto" height="auto" /></a>
									<a class="text-aqua" href="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/barcodes/' . $student_data['student_id'] . '.png'); ?>" target="_blank">&nbsp;&nbsp;
                                            <img src="<?php echo base_url('uploads/student_id_card/barcodes/' . $student_data['student_id'] . '.png'); ?>" width="auto" height="auto" /></a>
									<a href="javascript:void(0);" class="btn btn-primary btn-sm btn_student_analysis" data-student_id="<?php echo $student_data['student_id']; ?>" style="border-radius: 4px; font-weight:600; margin-left: 10px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border:none;">
										<i class="fa fa-line-chart"></i> Child Performance Analysis & AI Insights
									</a>
	                		    </div>
	                		</div><!--./col-lg-10-->
	                	</div><!--./row-->
	                </div>
	            </div>   
	        </div><!--./col-lg-6-->

	    <?php } ?>
		
	    <?php if ($this->customlib->get_user_dashboard_setting_status('notice_board') && $this->module_lib->hasActive('communicate') && $this->studentmodule_lib->hasActive('notice_board')) { ?>
			<div class="col-lg-6 col-md-6 col-sm-12">	    		 
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('notice_board'); ?></h3>      
                    </div>
                    <div class="box-body pb0">
						<?php if(!empty($notificationlist)){ ?>
                    	<ul class="user-progress ps mb0">
                    		<?php for($i=0;$i<4;$i++){
                    			$notification = array();
                    			if(!empty($notificationlist[$i])){
	                    			$notification=$notificationlist[$i];
                                }
	                    	?>
	                    <?php if(!empty($notification)){ ?>
			                <li class="doc-file-type">			                   
				                <div class="set-flex">
					                <div class="media-title"><?php if(!empty($notification)){ ?>
									<a href="<?php echo base_url(); ?>user/notification" class="displayinline text-muted" target="_blank">
									
					                	<?php if ($notification['notification_id'] == "read") { ?>
                                            <img src="<?php echo base_url() ?>/backend/images/read_one.png">
                                        <?php } else { ?>
                                            <img src="<?php echo base_url() ?>backend/images/unread_two.png">
                                        <?php }?>
										
										&nbsp;<?php  echo $notification['title']; ?> (<?php if(!empty($notification)){ echo "<i class='fa fa-clock-o text-aqua'></i>". ' '. date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($notification['date']));} ?>)
					                </a><?php } ?>
									</div>                

			            		</div>   
				               
			                </li><!-- /.item -->
			            <?php } } ?>
			                
			            </ul>  
						<?php }else{ ?>
							<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
						<?php } ?>
                    </div>                   
                </div>	 
	        </div><!--./col-lg-6-->  
	    <?php } ?>
    	</div><!--./row-->	
		 
		<div class="row">
			<?php if ($this->customlib->get_user_dashboard_setting_status('subject_progress') && $this->module_lib->hasActive('lesson_plan') && $this->studentmodule_lib->hasActive('syllabus_status')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('subject_progress'); ?></h3>      
                    </div>
                    <div class="box-body direct-chat-messages">
                    	<div class="table-responsive">
							<?php   if (!empty($subjects_data)) {  ?>
                    		<table class="table table-striped table-hover">
                    			<tr class="">
                    				<th><?php echo $this->lang->line('subject'); ?></th>
                    				<th><?php echo $this->lang->line('progress'); ?></th>
                    				<!-- <th>Duration</th> -->
                    			</tr>
                    		<?php 
                                    foreach ($subjects_data as $key => $value) {
                            ?>
                    			<tr>
                    				<td><?php echo $value['lebel']; ?></td>
                    				<td><?php echo $value['complete']; ?>%
                    					<div class="progress progress-minibar">
										  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow=""
										  aria-valuemin="0" aria-valuemax="100" style="width:<?php if($value['complete'] !=0){ echo $value['complete'];} ?>%">
										  </div>
										</div>
                    				</td>
                    				<!-- <td>2 Months</td> -->
                    			</tr>
                    		<?php }  ?>
                    			
                    		</table>
							<?php }else{  ?>
								<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
							<?php } ?>
                    	</div>
                    </div>
                </div>
			</div><!--./col-lg-4-->
		<?php } ?>
		
		<?php if ($this->customlib->get_user_dashboard_setting_status('upcomming_class') && $this->module_lib->hasActive('academics') && $this->studentmodule_lib->hasActive('class_timetable')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('upcomming_class'); ?></h3>      
                    </div>
                    <div class="box-body direct-chat-messages">					 
					
						<?php if (!empty($timetable)) { ?>
                    	<ul class="user-progress">

                    	<?php 
						//===========================
						$status=0;
                    	foreach ($timetable as $tm_key => $tm_value) {
                    		if (!empty($tm_value)) {
                    			$status=1;
                    			break;
                    		}
                    	}
                    	if($status==0){ ?>
                    		<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
                    	<?php }
                    	//===========================

                    		foreach ($timetable as $tm_key => $tm_value) {

                    			if (!$timetable[$tm_key]) {
                    	 ?>
			            <?php }else{ 
                                for($i=0;$i<5;$i++){

	                                $timetablelist = array();
	                    			if(!empty($timetable[$tm_key][$i])){
	                    				
		                    			$timetablelist=$timetable[$tm_key][$i];
		                 
	                                }
 
			             if(!empty($timetablelist)){ ?>
			            	<li class="lecture-list">

			            		<?php $profile_pic = '';
			            		if($timetablelist->image !=''){
				            	    $profile_pic = 'uploads/staff_images/'.$timetablelist->image;
				            	}else{
				            	    if($timetablelist->gender == 'Male'){
	                                    $profile_pic = 'uploads/staff_images/default_male.jpg';
				            		 }else{
	                                    $profile_pic = 'uploads/staff_images/default_female.jpg';
				            		} 
			            		}?>
			                    <img src="<?php echo base_url(); ?><?php echo $profile_pic.img_time(); ?>" alt="" class="img-circle msr-3 object-fit-cover fit-image-40" width="40" height="40">

				                <div class="set-flex">
					                <div class="media-title bmedium"><?php echo $timetablelist->name.' '.$timetablelist->surname.' (' . $timetablelist->employee_id .')'; ?> 
					                </div>
					                <div class="text-muted mb8">
					                	<?php
					                	if(!empty($timetablelist)){
                                            echo $timetablelist->subject_name;
                                            if ($timetablelist->code != '') {
                                                echo " (" . $timetablelist->code . ")";
                                            }
                                        }
                                        ?>                          	
                                    </div>
			            		</div>    
				                 <div class="ms-auto">
					                <div class="bmedium"><?php echo $this->lang->line('room_no'); ?>:<?php echo $timetablelist->room_no; ?></div>
					                <div class="text-muted mb8"><?php echo $timetablelist->time_from ?>-<?php echo $timetablelist->time_to; ?></div>
				                 </div>
			                </li>
			           <?php } } } }  ?>
			               
			            </ul>
						<?php }else{  ?>
							<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
						<?php } ?>
                    </div>
                </div>
			</div><!--./col-lg-4-->
		<?php } ?>

		<?php if ($this->customlib->get_user_dashboard_setting_status('homework') && $this->module_lib->hasActive('homework') && $this->studentmodule_lib->hasActive('homework') ) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('homework'); ?></h3>      
                    </div>

                    <div class="box-body direct-chat-messages">
                    	
						<?php if(!empty($homeworklist)){ ?>
                    	<ul class="user-progress ps">
                    		<?php for($i=0;$i<5;$i++){
                    			$homework = array();
                    			if(!empty($homeworklist[$i])){
	                    			$homework=$homeworklist[$i];
                                }
	                    	?>
	                    <?php if(!empty($homework)){ ?>
			                <li class="doc-file-type">
				                <div class="set-flex">
					                <div class="media-title font-16"><?php if(!empty($homework)){ ?><a href="<?php echo base_url(); ?>user/homework" class="displayinline text-muted" target="_blank"><?php  echo $homework['subject_name']; ?> (<?php  echo $homework['subject_code']; ?>)									
									</a><?php } ?></div>
					                <div class="text-muted mb8"><?php if(!empty($homework)){ echo $this->lang->line('homework_date').': '. date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($homework['homework_date'])) .',';} ?> <?php if(!empty($homework)){ echo $this->lang->line('submission_date'). ': '. date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($homework['submit_date'])) .',';} ?> <?php if(!empty($homework)){

                                        if ($homework["status"] == 'submitted') {
                                            $status_class = "class= 'label label-warning'";
                                            $status_homework = $this->lang->line('submitted');
                                        }else{
                                            $status_class = "class= 'label label-danger'";
                                            $status_homework = $this->lang->line("pending");
                                        }
                      
                                        if ($homework["homework_evaluation_id"] != 0) {
                                           
                                            $status_class = "class= 'label label-success'";
                                            $status_homework = $this->lang->line("evaluated");
                                        }

                                        echo $this->lang->line('status').': ';
                                        ?>
                                        <label <?php echo $status_class; ?>><?php echo $status_homework; ?></label>
								    <?php	
								    }
									?>

					            </div>
			            		</div> 
			                </li><!-- /.item -->
			            <?php } } ?>			                
			            </ul> 
						<?php }else{ ?>
							<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
						<?php } ?>
                    </div>
                </div>
			</div><!--./col-lg-4-->	
		<?php } ?>
			
		
		</div><!--./row-->	

		<div class="row">
		<?php if ($this->customlib->get_user_dashboard_setting_status('teacher_list') && $this->module_lib->hasActive('human_resource')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('teacher_list'); ?></h3>      
                    </div>

                    <div class="box-body direct-chat-messages">                    	
						<?php  					 
						 
							if(!empty($teacherlist)){   
								
						?>
                    	<ul class="user-progress ps">
                    		<?php foreach ($teacherlist as $teacher) {							 
								
								$class_teacher = '';
							
								if ($teacher[0]->class_teacher == $teacher[0]->staff_id) {
									$class_teacher = '<span class="label label-success bolds">' . $this->lang->line('class_teacher') . '</span>' ;
								}
							?>
							<li class="lecture-list">

			            		<?php 
										$profile_pic = '';
										
										if($teacher[0]->image !=''){
											$profile_pic = 'uploads/staff_images/'.$teacher[0]->image;
										}else{
											if($teacher[0]->gender == 'Male'){
												$profile_pic = 'uploads/staff_images/default_male.jpg';
											}else{
												$profile_pic = 'uploads/staff_images/default_female.jpg';
											} 
										}
								?>
			                    <img src="<?php echo base_url(); ?><?php echo $profile_pic.img_time(); ?>" alt="" class="img-circle msr-3 object-fit-cover fit-image-40" width="40" height="40">

				                <div class="set-flex">
					                <div class="media-title bmedium"><?php echo $teacher[0]->name . " " . $teacher[0]->surname . "<br> (" . $teacher[0]->employee_id . ") " . $class_teacher ?>	 
					                </div>
					                 
			            		</div>  
			                </li>
							
			                <?php } ?>		            		                
			            </ul> 
						<?php  }else{ ?>
							<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
						<?php } ?>
                    </div>
                </div>
			</div><!--./col-lg-4-->	
		<?php } ?>	

		<?php if ($this->customlib->get_user_dashboard_setting_status('visitor_list') && $this->module_lib->hasActive('front_office') && $this->studentmodule_lib->hasActive('visitor_book')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('visitor_list'); ?></h3>      
                    </div>
                    <div class="box-body direct-chat-messages">
						<?php if(!empty($visitor_list)){ ?>
                    	<ul class="user-progress ps">                    		 
	                    <?php 
							foreach($visitor_list as $visitor_value){
							?>
			                <li class="doc-file-type">			                   
				                <div class="set-flex">
					                <div class="text-muted mb8">
										<b><?php echo $visitor_value['name']; ?></b><br> (<?php echo $this->lang->line('purpose'); ?>: <?php echo $visitor_value['purpose']; ?>)				
									</div>									
					                <div class="text-muted mb8"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($visitor_value['date'])); ?>
					               </div>
			            		</div> 			               
			                </li><!-- /.item -->
			            <?php } ?>
			                
			            </ul>
						<?php }else{ ?>
						
						<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
						
						<?php } ?>					
                    </div>                   
                </div>    
			</div><!--./col-lg-4-->
		<?php } ?>	
		
		<?php if ($this->customlib->get_user_dashboard_setting_status('library') && $this->module_lib->hasActive('library') && $this->studentmodule_lib->hasActive('library')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('library_book_issue_list');?></h3>      
                    </div>

                    <div class="box-body direct-chat-messages">
                    	<div class="table-responsive">
                    		<table class="table table-striped table-hover">
                    			<tr class="">
                    				<th><?php echo $this->lang->line('book_no')?></th>
                    				<th><?php echo $this->lang->line('book_title')?></th>
                    				<th><?php echo $this->lang->line('issue_date')?></th>                    				 
                    				<th><?php echo $this->lang->line('due_return')?></th>
                    			</tr>                    			
                                
                                <?php if (empty($bookList)) { ?>
									<tr>
										<td colspan="6">
											<img src="https://smart-school.in/ssappresource/images/addnewitem.svg"  width="150" class="center-block mt20">
										</td>
									</tr>
                                <?php
                                    } else {									 
									foreach($bookList as $key => $value){ 
									if($value['is_returned'] == 0){
                                ?>
                                    <tr>	
										<td class="mailbox-name"> <?php echo $value['book_no'] ?></td>
                                        <td class="mailbox-name"> <?php echo $value['book_title'] ?> <br> (<?php echo $value['author'] ?>)</td>                                                         
                                        <td class="mailbox-name"> 
                                            <?php
                                            if ($value['issue_date'] != '') {
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['issue_date']));
                                            }
                                            ?>
                                        </td> 
                                        <td >

                                            <?php
                                            if ($value['duereturn_date'] != '') {
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['duereturn_date']));
                                            }
                                            ?> 
											
                                        </td>                                   
                                    </tr>
									<?php } } } ?>                                 
                                
                            </table>                    			
                    	</div>
                    </div>
                </div>    
			</div><!--./col-lg-4-->
		<?php } ?>

		<?php if ($this->module_lib->hasActive('certificate') && $this->studentmodule_lib->hasActive('certificate')) { ?>
			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-newspaper-o text-aqua"></i> <?php echo $this->lang->line('certificate'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>user/certificate" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> <?php echo $this->lang->line('view_all'); ?></a>
                        </div>
                    </div>
                    <div class="box-body direct-chat-messages">
                        <?php if (!empty($certificateList)) { ?>
                            <ul class="user-progress ps mb0">
                                <?php foreach (array_slice($certificateList, 0, 5) as $cert) { ?>
                                    <li class="doc-file-type">
                                        <div class="set-flex">
                                            <div class="media-title font-16">
                                                <a href="<?php echo base_url(); ?>user/certificate" class="displayinline text-muted">
                                                    <i class="fa fa-certificate text-gold"></i> &nbsp;<?php echo $cert->certificate_name; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <img src="https://smart-school.in/ssappresource/images/addnewitem.svg" width="150" class="center-block mt20">
                        <?php } ?>
                    </div>
                </div>    
			</div><!--./col-lg-4-->
		<?php } ?>

			<div class="col-lg-4 col-md-4 col-sm-12">
				<div class="box borderwhite shadow">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-id-card-o text-aqua"></i> <?php echo $this->lang->line('student_id_card'); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-xs btn-primary print_user_idcard"><i class="fa fa-print"></i> <?php echo $this->lang->line('view') . ' & ' . $this->lang->line('print'); ?></button>
                        </div>
                    </div>
                    <div class="box-body direct-chat-messages">
                        <div class="text-center mb10">
                            <?php   
                                if (!empty($student_data["image"])) {
                                    $file = base_url() . $student_data["image"].img_time();
                                } else {                            
                                    if ($student_data['gender'] == 'Female') {
                                        $file = base_url() . "uploads/student_images/default_female.jpg".img_time();
                                    } else {
                                        $file = base_url() . "uploads/student_images/default_male.jpg".img_time();
                                    }
                                }
                            ?>
                            <img src="<?php echo $file; ?>" class="img-circle" width="60" height="60" style="border: 2px solid #00c0ef;">
                            <h4 class="mt5 mb0"><b><?php echo $studentsession_username; ?></b></h4>
                        </div>
                        <ul class="list-group list-group-unbordered mb0">
                            <?php if (!empty($student_detail['admission_no'])) { ?>
                                <li class="list-group-item"><b><?php echo $this->lang->line('admission_no'); ?>:</b> <span class="pull-right"><?php echo $student_detail['admission_no']; ?></span></li>
                            <?php } ?>
                            <?php if (!empty($student_detail['roll_no'])) { ?>
                                <li class="list-group-item"><b><?php echo $this->lang->line('roll_no'); ?>:</b> <span class="pull-right"><?php echo $student_detail['roll_no']; ?></span></li>
                            <?php } ?>
                            <?php if (!empty($student_detail['class'])) { ?>
                                <li class="list-group-item"><b><?php echo $this->lang->line('class'); ?>:</b> <span class="pull-right"><?php echo $student_detail['class'] . ' (' . $student_detail['section'] . ')'; ?></span></li>
                            <?php } ?>
                            <?php if (!empty($student_detail['guardian_name'])) { ?>
                                <li class="list-group-item"><b><?php echo $this->lang->line('guardian_name'); ?>:</b> <span class="pull-right"><?php echo $student_detail['guardian_name']; ?></span></li>
                            <?php } ?>
                            <?php if (!empty($student_detail['dob'])) { ?>
                                <li class="list-group-item"><b><?php echo $this->lang->line('date_of_birth'); ?>:</b> <span class="pull-right"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student_detail['dob'])); ?></span></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>    
			</div><!--./col-lg-4-->
		
		</div><!--./row--> 
	</section>
</div>	

<div class="modal fade" id="user_idcardModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('student_id_card'); ?></h4>
            </div>
            <div class="modal-body" id="user_idcard_detail">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="printIDCardContent()"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('click', '.print_user_idcard', function () {
        $.ajax({
            url: '<?php echo base_url(); ?>user/user/print_idcard',
            type: 'POST',
            success: function (data) {
                $('#user_idcard_detail').html(data);
                $('#user_idcardModal').modal('show');
            }
        });
    });

    function printIDCardContent() {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({ "position": "absolute", "top": "-1000000px" });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write('<html><head><title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/idcard.css">');
        frameDoc.document.write('</head><body>');
        frameDoc.document.write($('#user_idcard_detail').html());
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.close_notice', function() {
            var data = $(this).data();

            $.ajax({
                type: "POST",
                url: "<?php echo site_url("user/notification/read") ?>",   
                data: {
                    'notice': data.noticeid
                },
                dataType: "json",
                success: function(data) {
                    if (data.status == "fail") {

                        errorMsg(data.msg);
                    } else {
                        successMsg(data.msg);
                    }
                }
            });
        });
    });	
</script>

<!--====================== STUDENT ANALYSIS MODAL ============================-->
<div class="modal fade" id="modal_student_analysis" tabindex="-1" role="dialog" aria-labelledby="studentAnalysisModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1050px;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 15px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.9; font-size: 24px;">&times;</button>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-right: 20px;">
                    <h4 class="modal-title" style="font-weight: 600; font-size: 18px;"><i class="fa fa-line-chart"></i> Comprehensive Student Analysis & Performance Insights</h4>
                    <a href="#" id="btn_modal_export_top" target="_blank" class="btn btn-warning btn-sm" style="font-weight: 600; border-radius: 4px;"><i class="fa fa-print"></i> Export / Print Report</a>
                </div>
            </div>

            <div class="modal-body" style="padding: 20px; background: #f8fafc; max-height: 80vh; overflow-y: auto;">
                <div id="analysis_loading" style="text-align: center; padding: 60px 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary" style="color: #2a5298;"></i>
                    <h4 style="margin-top: 15px; color: #444;">Synthesizing Student Analytics & Exam Trends...</h4>
                    <p class="text-muted">Analyzing attendance, examination marks, and performance records.</p>
                </div>

                <div id="analysis_content" style="display:none;">
                    <!-- HERO STUDENT BANNER -->
                    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: flex; align-items: center;">
                            <img id="ana_student_img" src="" alt="Avatar" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid #2a5298; margin-right: 18px;">
                            <div>
                                <h3 id="ana_student_name" style="margin: 0 0 4px 0; font-weight: bold; color: #1e293b; font-size: 20px;"></h3>
                                <div style="color: #64748b; font-size: 13px;">
                                    <span><strong>Adm No:</strong> <span id="ana_adm_no"></span></span> &nbsp;|&nbsp;
                                    <span><strong>Roll No:</strong> <span id="ana_roll_no"></span></span> &nbsp;|&nbsp;
                                    <span><strong>Class:</strong> <span id="ana_class_sec"></span></span>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; margin-top: 10px;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold;">Overall Performance Rating</div>
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; margin-top: 2px;">
                                <span id="ana_composite_score" style="font-size: 24px; font-weight: bold; color: #1e3c72;"></span>
                                <span id="ana_perf_badge" class="label" style="font-size: 12px; padding: 4px 10px;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL TABS -->
                    <div class="nav-tabs-custom" style="box-shadow: none; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <ul class="nav nav-tabs" style="border-bottom: 2px solid #e2e8f0; background: #fff; border-radius: 8px 8px 0 0;">
                            <li class="active"><a href="#ana_tab_overview" data-toggle="tab"><i class="fa fa-dashboard text-blue"></i> Executive Overview</a></li>
                            <li><a href="#ana_tab_exams" data-toggle="tab"><i class="fa fa-graduation-cap text-purple"></i> Examination & Subjects</a></li>
                            <li><a href="#ana_tab_attendance" data-toggle="tab"><i class="fa fa-calendar-check-o text-green"></i> Attendance Trends</a></li>
                            <li><a href="#ana_tab_finance" data-toggle="tab"><i class="fa fa-credit-card text-orange"></i> Fees & Discipline</a></li>
                        </ul>

                        <div class="tab-content" style="padding: 20px; background: #fff; border-radius: 0 0 8px 8px;">
                            <!-- TAB 1: OVERVIEW -->
                            <div class="tab-pane active" id="ana_tab_overview">
                                <!-- 4 KPI CARDS -->
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-3 col-sm-6">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 3px solid #6366f1; border-radius: 6px; padding: 12px; text-align: center;">
                                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Composite Score</div>
                                            <div id="kpi_composite" style="font-size: 22px; font-weight: bold; color: #4338ca; margin: 4px 0;">0/100</div>
                                            <small id="kpi_composite_sub" class="text-muted"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 3px solid #3b82f6; border-radius: 6px; padding: 12px; text-align: center;">
                                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Academic Score</div>
                                            <div id="kpi_academic" style="font-size: 22px; font-weight: bold; color: #1d4ed8; margin: 4px 0;">0%</div>
                                            <small id="kpi_academic_grade" class="text-muted"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 3px solid #10b981; border-radius: 6px; padding: 12px; text-align: center;">
                                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Attendance Rate</div>
                                            <div id="kpi_attendance" style="font-size: 22px; font-weight: bold; color: #047857; margin: 4px 0;">0%</div>
                                            <small id="kpi_attendance_health" class="text-muted"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: 3px solid #f59e0b; border-radius: 6px; padding: 12px; text-align: center;">
                                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Fee Clearance</div>
                                            <div id="kpi_fee" style="font-size: 17px; font-weight: bold; color: #b45309; margin: 6px 0;">-</div>
                                            <small id="kpi_fee_sub" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- EXECUTIVE SUMMARY -->
                                <div style="background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <h4 style="margin: 0; font-size: 14px; font-weight: bold; color: #1e40af;"><i class="fa fa-info-circle"></i> Executive Performance Summary & AI Insights</h4>
                                        <span id="ana_ai_badge" class="badge" style="background:#8b5cf6; font-weight: 600; font-size: 11px; padding: 4px 8px; display: none;"><i class="fa fa-magic"></i> AI Insights</span>
                                    </div>
                                    <div id="ana_summary_text" style="color: #1e293b; font-size: 13.5px; line-height: 1.5;"></div>
                                </div>

                                <!-- STRENGTHS & FOCUS AREAS -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #22c55e; border-radius: 6px; padding: 14px; min-height: 140px;">
                                            <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #15803d;"><i class="fa fa-check-circle"></i> Key Strengths & Accomplishments</h4>
                                            <ul id="ana_strengths_list" style="margin: 0 0 0 16px; padding: 0; font-size: 13px; color: #166534; line-height: 1.6;"></ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 14px; min-height: 140px;">
                                            <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #b45309;"><i class="fa fa-bullseye"></i> Focus & Improvement Areas</h4>
                                            <ul id="ana_focus_list" style="margin: 0 0 0 16px; padding: 0; font-size: 13px; color: #92400e; line-height: 1.6;"></ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- RECOMMENDATION -->
                                <div style="margin-top: 20px; background: #faf5ff; border: 1px solid #e9d5ff; border-left: 4px solid #a855f7; border-radius: 6px; padding: 14px;">
                                    <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: bold; color: #6b21a8;"><i class="fa fa-mortar-board"></i> Faculty & Advisory Recommendations</h4>
                                    <div id="ana_recommendation_text" style="color: #581c87; font-size: 13px; line-height: 1.5;"></div>
                                </div>
                            </div>

                            <!-- TAB 2: EXAMS & SUBJECTS -->
                            <div class="tab-pane" id="ana_tab_exams">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                                    <h4 style="margin:0; font-weight:bold; color:#1e293b;"><i class="fa fa-book text-purple"></i> Subject-Wise Performance Breakdown</h4>
                                    <div id="ana_exam_overall_badge"></div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="table_subject_summary">
                                        <thead>
                                            <tr style="background:#f1f5f9;">
                                                <th>Subject Name</th>
                                                <th class="text-center" style="width: 140px;">Marks Scored / Max</th>
                                                <th style="width: 220px;">Average Score %</th>
                                                <th class="text-center" style="width: 90px;">Grade</th>
                                                <th class="text-center" style="width: 150px;">Proficiency Level</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_subject_summary"></tbody>
                                    </table>
                                </div>

                                <h4 style="margin: 25px 0 12px 0; font-weight:bold; color:#1e293b;"><i class="fa fa-file-text-o text-blue"></i> Examination History & Records</h4>
                                <div id="ana_exams_history_list"></div>
                            </div>

                            <!-- TAB 3: ATTENDANCE -->
                            <div class="tab-pane" id="ana_tab_attendance">
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-3">
                                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:15px; text-align:center;">
                                            <div style="font-size:12px; color:#64748b;">Total Working Days</div>
                                            <div id="att_box_total" style="font-size:24px; font-weight:bold; color:#1e293b; margin:4px 0;">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:15px; text-align:center;">
                                            <div style="font-size:12px; color:#15803d;">Days Present</div>
                                            <div id="att_box_present" style="font-size:24px; font-weight:bold; color:#166534; margin:4px 0;">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:15px; text-align:center;">
                                            <div style="font-size:12px; color:#b91c1c;">Days Absent</div>
                                            <div id="att_box_absent" style="font-size:24px; font-weight:bold; color:#991b1b; margin:4px 0;">0</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:15px; text-align:center;">
                                            <div style="font-size:12px; color:#b45309;">Late / Half-Day</div>
                                            <div id="att_box_late" style="font-size:24px; font-weight:bold; color:#92400e; margin:4px 0;">0</div>
                                        </div>
                                    </div>
                                </div>

                                <h4 style="margin:0 0 12px 0; font-weight:bold; color:#1e293b;"><i class="fa fa-calendar text-green"></i> Month-by-Month Attendance Breakdown</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr style="background:#f1f5f9;">
                                                <th>Month & Year</th>
                                                <th class="text-center">Recorded Days</th>
                                                <th class="text-center text-success">Present</th>
                                                <th class="text-center text-danger">Absent</th>
                                                <th class="text-center text-warning">Late</th>
                                                <th class="text-center">Attendance %</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_monthly_attendance"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 4: FEES & BEHAVIOR -->
                            <div class="tab-pane" id="ana_tab_finance">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="box box-solid box-default" style="border:1px solid #e2e8f0;">
                                            <div class="box-header with-border" style="background:#f8fafc;">
                                                <h3 class="box-title" style="font-size:14px; font-weight:bold;"><i class="fa fa-money text-green"></i> Fee & Payment Compliance</h3>
                                            </div>
                                            <div class="box-body" style="padding:15px;">
                                                <table class="table table-striped" style="margin:0;">
                                                    <tr><td><strong>Total Assigned Fee:</strong></td><td id="fee_row_total" class="text-right font-weight-bold"></td></tr>
                                                    <tr><td><strong>Total Paid Amount:</strong></td><td id="fee_row_paid" class="text-right text-success font-weight-bold"></td></tr>
                                                    <tr><td><strong>Total Concession / Discount:</strong></td><td id="fee_row_discount" class="text-right"></td></tr>
                                                    <tr><td><strong>Total Fine:</strong></td><td id="fee_row_fine" class="text-right text-danger"></td></tr>
                                                    <tr style="font-size:14px; background:#f1f5f9;"><td><strong>Outstanding Balance:</strong></td><td id="fee_row_balance" class="text-right font-weight-bold text-danger"></td></tr>
                                                    <tr><td><strong>Compliance Status:</strong></td><td id="fee_row_status" class="text-right"></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="box box-solid box-default" style="border:1px solid #e2e8f0;">
                                            <div class="box-header with-border" style="background:#f8fafc;">
                                                <h3 class="box-title" style="font-size:14px; font-weight:bold;"><i class="fa fa-shield text-purple"></i> Behavioral & Incident Metrics</h3>
                                            </div>
                                            <div class="box-body" style="padding:15px;">
                                                <table class="table table-striped" style="margin:0;">
                                                    <tr><td><strong>Behavior Incidents Recorded:</strong></td><td id="beh_row_incidents" class="text-right">0</td></tr>
                                                    <tr><td><strong>Cumulative Discipline Points:</strong></td><td id="beh_row_points" class="text-right font-weight-bold text-success">0</td></tr>
                                                    <tr><td><strong>Timeline Logged Activities:</strong></td><td id="beh_row_timeline" class="text-right">0</td></tr>
                                                    <tr><td><strong>Classroom Conduct Status:</strong></td><td id="beh_row_status" class="text-right text-success"><strong>Exemplary</strong></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                <a href="#" id="btn_modal_export_bottom" target="_blank" class="btn btn-primary"><i class="fa fa-print"></i> Export / Print Full Analysis</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.btn_student_analysis', function(e) {
        e.preventDefault();
        var studentId = $(this).data('student_id');

        $('#modal_student_analysis').modal('show');
        $('#analysis_loading').show();
        $('#analysis_content').hide();

        var exportUrl = "<?php echo base_url('user/user/export_analysis/'); ?>" + (studentId ? studentId : '');
        $('#btn_modal_export_top, #btn_modal_export_bottom').attr('href', exportUrl);

        $.ajax({
            url: "<?php echo base_url('user/user/get_student_analysis/'); ?>" + (studentId ? studentId : ''),
            type: "GET",
            dataType: "json",
            success: function(res) {
                $('#analysis_loading').hide();
                if (res.status === 'success' && res.data) {
                    var d = res.data;

                    // 1. Hero Student Info
                    $('#ana_student_img').attr('src', d.student.image);
                    $('#ana_student_name').text(d.student.name);
                    $('#ana_adm_no').text(d.student.admission_no);
                    $('#ana_roll_no').text(d.student.roll_no || 'N/A');
                    $('#ana_class_sec').text(d.student.class + ' - ' + d.student.section);

                    $('#ana_composite_score').text(d.overall_score.composite_score + '/100');
                    var badgeClass = (d.overall_score.composite_score >= 85) ? 'label-success' : ((d.overall_score.composite_score >= 70) ? 'label-primary' : ((d.overall_score.composite_score >= 50) ? 'label-warning' : 'label-danger'));
                    $('#ana_perf_badge').attr('class', 'label ' + badgeClass).text(d.overall_score.performance_label);

                    // 2. Overview Tab KPIs
                    $('#kpi_composite').text(d.overall_score.composite_score + '/100');
                    $('#kpi_composite_sub').text(d.overall_score.performance_label);

                    $('#kpi_academic').text(d.overall_score.academic_percentage + '%');
                    $('#kpi_academic_grade').text('Grade: ' + d.overall_score.academic_grade);

                    $('#kpi_attendance').text(d.attendance.percentage + '%');
                    $('#kpi_attendance_health').text(d.attendance.health_status);

                    $('#kpi_fee').text(d.fees.status);
                    $('#kpi_fee_sub').text(d.fees.balance_amount > 0 ? 'Due: ' + d.fees.balance_amount : 'No Due Balance');

                    // Summary & Insights
                    $('#ana_summary_text').html(d.insights.summary);

                    if (d.insights.source === 'ai_endpoint') {
                        $('#ana_ai_badge').html('<i class="fa fa-magic"></i> AI Powered Insights').css({'background': '#8b5cf6', 'color': '#ffffff'}).show();
                    } else {
                        $('#ana_ai_badge').html('<i class="fa fa-cogs"></i> Local Analytics Engine').css({'background': '#3b82f6', 'color': '#ffffff'}).show();
                    }

                    var strengthsHtml = '';
                    if (d.insights.strengths && d.insights.strengths.length > 0) {
                        $.each(d.insights.strengths, function(i, str) { strengthsHtml += '<li>' + str + '</li>'; });
                    } else {
                        strengthsHtml = '<li>Active participant in school activities.</li>';
                    }
                    $('#ana_strengths_list').html(strengthsHtml);

                    var focusHtml = '';
                    if (d.insights.focus_areas && d.insights.focus_areas.length > 0) {
                        $.each(d.insights.focus_areas, function(i, foc) { focusHtml += '<li>' + foc + '</li>'; });
                    } else {
                        focusHtml = '<li>Maintain current learning and attendance consistency.</li>';
                    }
                    $('#ana_focus_list').html(focusHtml);

                    $('#ana_recommendation_text').html(d.insights.recommendation);

                    // 3. Exams Tab
                    $('#ana_exam_overall_badge').html('<span class="label label-primary" style="font-size:12px; padding:4px 8px;">Overall: ' + d.examinations.overall_percentage + '% (' + d.examinations.overall_grade + ')</span>');

                    var subHtml = '';
                    if (d.examinations.subject_summary && d.examinations.subject_summary.length > 0) {
                        $.each(d.examinations.subject_summary, function(i, sub) {
                            var barColor = (sub.avg_percentage >= 80) ? '#22c55e' : ((sub.avg_percentage >= 60) ? '#3b82f6' : ((sub.avg_percentage >= 40) ? '#f59e0b' : '#ef4444'));
                            var profText = (sub.avg_percentage >= 85) ? 'Advanced Mastery' : ((sub.avg_percentage >= 70) ? 'Proficient' : ((sub.avg_percentage >= 50) ? 'Developing' : 'Requires Focus'));

                            subHtml += '<tr>';
                            subHtml += '<td><strong>' + sub.subject + '</strong></td>';
                            subHtml += '<td class="text-center">' + sub.total_obtained + ' / ' + sub.total_max + '</td>';
                            subHtml += '<td><div style="display:flex; align-items:center;"><div class="progress" style="height:8px; flex-grow:1; margin-bottom:0; background:#e2e8f0; border-radius:4px;"><div class="progress-bar" style="width:' + sub.avg_percentage + '%; background:' + barColor + ';"></div></div><span style="margin-left:10px; font-weight:bold; font-size:12px; width:45px; text-align:right;">' + sub.avg_percentage + '%</span></div></td>';
                            subHtml += '<td class="text-center"><span class="badge" style="background:#f1f5f9; color:#1e293b; font-weight:bold;">' + sub.grade + '</span></td>';
                            subHtml += '<td class="text-center"><span style="color:' + barColor + '; font-weight:600;">' + profText + '</span></td>';
                            subHtml += '</tr>';
                        });
                    } else {
                        subHtml = '<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">No examination marks recorded for this student yet.</td></tr>';
                    }
                    $('#tbody_subject_summary').html(subHtml);

                    var exListHtml = '';
                    if (d.examinations.exams_list && d.examinations.exams_list.length > 0) {
                        $.each(d.examinations.exams_list, function(i, ex) {
                            exListHtml += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px 15px; margin-bottom:12px;">';
                            exListHtml += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">';
                            exListHtml += '<h5 style="margin:0; font-weight:bold; color:#1e293b;">' + ex.exam_name + '</h5>';
                            exListHtml += '<div><span class="label label-info" style="margin-right:5px;">' + ex.marks_obtained + ' / ' + ex.max_marks + ' (' + ex.percentage + '%)</span><span class="label ' + (ex.status === 'Pass' ? 'label-success' : 'label-danger') + '">' + ex.status + ' (' + ex.grade + ')</span></div>';
                            exListHtml += '</div>';

                            if (ex.subjects && ex.subjects.length > 0) {
                                exListHtml += '<div style="display:flex; flex-wrap:wrap; gap:8px;">';
                                $.each(ex.subjects, function(si, sub) {
                                    exListHtml += '<span style="background:#fff; border:1px solid #cbd5e1; border-radius:4px; padding:4px 8px; font-size:12px;">' + sub.subject + ': <strong>' + sub.marks_obtained + '/' + sub.max_marks + '</strong> (' + sub.grade + ')</span>';
                                });
                                exListHtml += '</div>';
                            }
                            exListHtml += '</div>';
                        });
                    } else {
                        exListHtml = '<p class="text-muted" style="font-style:italic;">No examination history available.</p>';
                    }
                    $('#ana_exams_history_list').html(exListHtml);

                    // 4. Attendance Tab
                    $('#att_box_total').text(d.attendance.total_working_days);
                    $('#att_box_present').text(d.attendance.present);
                    $('#att_box_absent').text(d.attendance.absent);
                    $('#att_box_late').text(d.attendance.late + d.attendance.half_day);

                    var attHtml = '';
                    if (d.attendance.monthly_trend && d.attendance.monthly_trend.length > 0) {
                        $.each(d.attendance.monthly_trend, function(i, m) {
                            attHtml += '<tr>';
                            attHtml += '<td><strong>' + m.month + ' ' + m.year + '</strong></td>';
                            attHtml += '<td class="text-center">' + m.total_days + '</td>';
                            attHtml += '<td class="text-center text-success font-weight-bold">' + m.present + '</td>';
                            attHtml += '<td class="text-center text-danger font-weight-bold">' + m.absent + '</td>';
                            attHtml += '<td class="text-center text-warning font-weight-bold">' + m.late + '</td>';
                            attHtml += '<td class="text-center"><strong>' + m.percentage + '%</strong></td>';
                            attHtml += '</tr>';
                        });
                    } else {
                        attHtml = '<tr><td colspan="6" class="text-center text-muted" style="padding:20px;">No monthly attendance records found.</td></tr>';
                    }
                    $('#tbody_monthly_attendance').html(attHtml);

                    // 5. Fees & Behavior Tab
                    $('#fee_row_total').text(d.fees.total_amount);
                    $('#fee_row_paid').text(d.fees.paid_amount);
                    $('#fee_row_discount').text(d.fees.discount_amount);
                    $('#fee_row_fine').text(d.fees.fine_amount);
                    $('#fee_row_balance').text(d.fees.balance_amount);
                    $('#fee_row_status').html('<span class="label label-' + d.fees.status_class + '">' + d.fees.status + '</span>');

                    $('#beh_row_incidents').text(d.behavior.incident_count);
                    $('#beh_row_points').text(d.behavior.total_points);
                    $('#beh_row_timeline').text(d.behavior.timeline_count);
                    $('#beh_row_status').html(d.behavior.incident_count === 0 ? '<span class="text-success"><strong>Exemplary Conduct</strong></span>' : '<span class="text-warning"><strong>Review Required</strong></span>');

                    $('#analysis_content').fadeIn(200);
                } else {
                    $('#analysis_loading').html('<div class="alert alert-danger" style="margin:20px;">Unable to load student analytics data. ' + (res.message || '') + '</div>');
                }
            },
            error: function() {
                $('#analysis_loading').html('<div class="alert alert-danger" style="margin:20px;">Connection failed while loading student analysis. Please try again.</div>');
            }
        });
    });
});
</script>	