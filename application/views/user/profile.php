<style type="text/css">
    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }
    }

    .option_grade {
        display: none;
    }
</style>

<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content">
        

        <div class="row">
             
			<div class="col-lg-3 col-md-4 col-sm-12">
                <div class="box box-primary">
                    <div class="box box-widget widget-user-2 mb0">
                        <div class="widget-user-header bg-gray-light overflow-hidden">
                            <div class="widget-user-image">
                                <?php

                                if ($sch_setting->student_photo) {
                                ?>
                                    <img class="user-img-grid center-block img-rounded" src="       
       <?php
                                    if (!empty($student["image"])) {
                                        echo base_url() . $student["image"] . img_time();
                                    } else {

                                        if ($student['gender'] == 'Female') {
                                            echo base_url() . "uploads/student_images/default_female.jpg" . img_time();
                                        } else {
                                            echo base_url() . "uploads/student_images/default_male.jpg" . img_time();
                                        }
                                    }
        ?>" alt="User profile picture">
                                <?php } ?>
                            </div>

                            <h3 class="widget-user-username"><?php echo $student_name=$this->customlib->getFullname($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></h3>
                            <h5 class="widget-user-desc mb5"><?php echo $this->lang->line('admission_no'); ?> <span class="text-aqua"><?php echo $student['admission_no']; ?></span></h5>
                            <?php if ($sch_setting->roll_no) { ?>
                                <h5 class="widget-user-desc"><?php echo $this->lang->line('roll_number'); ?> <span class="text-aqua"><?php echo $student['roll_no']; ?></span></h5>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="box-body box-profile pt0">
                        <ul class="list-group list-group-unbordered">

                            <li class="list-group-item border0">
                                <b><?php echo $this->lang->line('class'); ?></b> <a class="pull-right text-aqua"><?php echo $student['class'] . ' (' . $student['session'] . ')'; ?></a>
                            </li>
                            <li class="list-group-item">
                                <b><?php echo $this->lang->line('section'); ?></b> <a class="pull-right text-aqua"><?php echo $student['section']; ?></a>
                            </li>
							<li class="list-group-item">
                                <b><?php echo $this->lang->line('gender'); ?></b> <a class="pull-right text-aqua"> <?php echo $this->lang->line(strtolower($student['gender'])); ?></a>
                            </li>
                            <?php if ($sch_setting->rte) { ?>
                                <li class="list-group-item">
                                    <b><?php echo $this->lang->line('rte'); ?></b> <a class="pull-right text-aqua"> <?php 
                                    if(isset($student['rte'])){ 
                                        echo $this->lang->line(strtolower($student['rte']));
                                    }
                                    ?></a>
                                </li>
                            <?php } ?>                           

                            <?php if ($sch_setting->student_barcode == 1) { ?>
                                <li class="list-group-item listnoback">
                                    <b><?php echo $this->lang->line('barcode'); ?></b>
                                    <?php if (file_exists("./uploads/student_id_card/barcodes/" . $student['id'] . ".png")) { ?>
                                        <a class="pull-right text-aqua" href="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/barcodes/' . $student['id'] . '.png'); ?>" target="_blank">
                                            <img src="<?php echo base_url('uploads/student_id_card/barcodes/' . $student['id'] . '.png'); ?>" width="auto" height="auto" /></a>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                            <?php if ($sch_setting->student_barcode == 1) { ?>
                                <li class="list-group-item listnoback">
                                    <b><?php echo $this->lang->line('qrcode'); ?></b>
                                    <?php if (file_exists("./uploads/student_id_card/qrcode/" . $student['id'] . ".png")) { ?>
                                        <a class="pull-right text-aqua" href="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/' . $student['id'] . '.png'); ?>" target="_blank">
                                            <img class="h-50 qrcodeimg" src="<?php echo $this->media_storage->getImageURL('uploads/student_id_card/qrcode/' . $student['id'] . '.png'); ?>" width="auto" height="auto" /></a>
                                    <?php } ?>
                                </li>
                            <?php } ?>

                            <!------- Behaviour Report Start-------->
                            <?php

                            if ($this->module_lib->hasModule('behaviour_records') && $this->module_lib->hasActive('behaviour_records') && $this->studentmodule_lib->hasActive('behaviour_records')) {
                            ?>
                                <li class="list-group-item listnoback">
                                    <b><?php echo $this->lang->line('behaviour_score'); ?></b> <a class="pull-right text-aqua"><?php echo $student['total_points']; ?></a>
                                </li>
                            <?php

                            }
                            ?>
                            <!------- Behaviour Report End--------->

                            <!-- download student resume/cv  -->
                            <?php  if ($sch_setting->student_resume_download) { ?>
                            <li class="list-group-item listnoback">
                                <b><?php echo $this->lang->line('download_resume'); ?></b> <a class="pull-right text-aqua">
                                
                            <button type="button" class="btn btn-default btn-xs download_pdf" data-action="download" data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('download'); ?>" data-student_id="<?php echo $student['id'] ?>" data-student_name="<?php echo $student_name; ?>" data-admission_no="<?php echo $student['admission_no']; ?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>" autocomplete="off"><i class="fa fa-download"></i></button>
                                </a>
                            </li>
                        <?php } ?>
                            <!-- download student resume/cv  -->

                            <li class="list-group-item listnoback" style="text-align:center; padding: 10px 0;">
                                <a href="javascript:void(0);" class="btn btn-primary btn-sm btn_student_analysis" data-student_id="<?php echo $student['id']; ?>" style="width:100%; border-radius: 4px; font-weight:600; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border:none; padding: 6px 12px;">
                                    <i class="fa fa-line-chart"></i> Performance Analysis & AI Insights
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 col-md-8 col-sm-12">
                <div class="nav-tabs-custom theme-shadow">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#activity" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('profile'); ?></a></li>
                        <?php if ($this->module_lib->hasActive('fees_collection') && $this->studentmodule_lib->hasActive('fees')) { ?>
                            <li class=""><a href="#fee" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('fees'); ?></a></li>
                        <?php } ?>

                        <?php if ($this->module_lib->hasActive('examination') && $this->studentmodule_lib->hasActive('examinations')) { ?>
                            <li><a href="#exam" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('exam'); ?></a></li>
                        <?php } ?>


                        <!------- CBSE Exam Start-------->
                        <?php
                        if ($this->module_lib->hasModule('cbseexam') && $this->module_lib->hasActive('cbseexam') && $this->studentmodule_lib->hasActive('cbseexam')) {  ?>
                            <li class=""><a href="#cbseexam" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('cbse_exam'); ?></a></li>
                        <?php
                        }
                        ?>
                        <!------- CBSE Exam End--------->


                        <?php
                        if ($this->module_lib->hasActive('student_attendance') && $this->studentmodule_lib->hasActive('attendance')) {
                            if (!$sch_setting->attendence_type) {
                        ?>
                                <li class="">
                                    <a href="#attendance" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('attendance'); ?></a>
                                </li>

                        <?php
                            }
                        }
                        ?>
                        <?php if ($sch_setting->upload_documents) { ?>
                            <li class=""><a href="#documents" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('documents'); ?></a></li>
                        <?php } ?>

                        <?php
                        if ($this->studentmodule_lib->hasActive('student_timeline')) {
                            if ($this->studentmodule_lib->hasActive('student_timeline', 'can_view')) {
                        ?>
                                <li class=""><a href="#timeline" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('timeline'); ?></a></li>
                        <?php }
                        } ?>

                        <!------- Behaviour Report Start-------->
                        <?php
                        if ($this->module_lib->hasModule('behaviour_records') && $this->module_lib->hasActive('behaviour_records') && $this->studentmodule_lib->hasActive('behaviour_records')) {  ?>
                            <li class=""><a href="#incident" data-toggle="tab" aria-expanded="true"><?php echo $this->lang->line('student_behaviour'); ?></a></li>
                        <?php
                        }
                        ?>
                        <!------- Behaviour Report End--------->

                        
							
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active relative" id="activity">
                            <div class="stprintedit-position pb5">
                                <ul class="stlist">
                                    <li>
                                        <button type="button" class="btn btn-default btn-xs print_student_details" data-student_id="<?php echo $student['id'] ?>" data-student_name="<?php echo $student_name; ?>" data-admission_no="<?php echo $student['admission_no']; ?>" data-action="download" data-toggle="tooltip" data-original-title="<?php echo $this->lang->line('print') ?>" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>" autocomplete="off"><i class="fa fa-print"></i></button>
                                    </li>
                                <?php
                                    if ($sch_setting->student_profile_edit) {
                                    ?>
                                        <li>
                                            <a href="<?php echo site_url('user/user/edit') ?>" class="btn btn-default btn-xs" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo $this->lang->line('edit') ?>"><i class="fa fa-pencil"></i>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ul> 
                            </div>    
                            <div class="tshadow mb25 bozero">
                                <div class="table-responsive around10 pt0">
                                    <table class="table3 table-striped table-hover">
                                        <tbody>
                                            <?php if ($sch_setting->admission_date) {
                                            ?>
                                                <tr class="bordertop">
                                                    <td width="35%"><?php echo $this->lang->line('admission_date'); ?></td>
                                                    <td class="col-md-5">
                                                        <?php
                                                        if (!empty($student['admission_date']) && $student['admission_date'] != '0000-00-00') {
                                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['admission_date']));
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td><?php echo $this->lang->line('date_of_birth'); ?></td>
                                                <td><?php
                                                    if (!empty($student['dob'])) {
                                                        echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob']));
                                                    }
                                                    ?></td>
                                            </tr>
                                            <?php if ($sch_setting->category) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('category'); ?></td>
                                                    <td>
                                                        <?php
                                                        foreach ($category_list as $value) {
                                                            if ($student['category_id'] == $value['id']) {
                                                                echo $value['category'];
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->mobile_no) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('mobile_number'); ?></td>
                                                    <td><?php echo $student['mobileno']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->cast) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('caste'); ?></td>
                                                    <td><?php echo $student['cast']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->religion) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('religion'); ?></td>
                                                    <td><?php echo $student['religion']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->student_email) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('email'); ?></td>
                                                    <td><?php echo $student['email']; ?></td>
                                                </tr>
                                            <?php } ?>
                                            <?php
                                            $cutom_fields_data = get_custom_table_values($student['id'], 'students');
                                            if (!empty($cutom_fields_data)) {
                                                foreach ($cutom_fields_data as $field_key => $field_value) {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $field_value->name; ?></td>
                                                        <td>
                                                            <?php
                                                            if (is_string($field_value->field_value) && is_array(json_decode($field_value->field_value, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                                                                $field_array = json_decode($field_value->field_value);
                                                                echo "<ul class='student_custom_field'>";
                                                                foreach ($field_array as $each_key => $each_value) {
                                                                    echo "<li>" . $each_value . "</li>";
                                                                }
                                                                echo "</ul>";
                                                            } else {
                                                                $display_field = $field_value->field_value;

                                                                if ($field_value->type == "link") {
                                                                    $display_field = "<a href=" . $field_value->field_value . " target='_blank'>" . $field_value->field_value . "</a>";
                                                                }
                                                                echo $display_field;
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $this->lang->line('note'); ?></td>
                                                <td><?php echo $student['note']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tshadow mb25 bozero">
                                <h3 class="pagetitleh2"><?php echo $this->lang->line('address_details'); ?></h3>
                                <div class="table-responsive around10 pt0">
                                    <table class="table3 table-striped table-hover">
                                        <tbody>
                                            <?php if ($sch_setting->current_address) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('current_address'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['current_address']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->permanent_address) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('permanent_address'); ?></td>
                                                    <td><?php echo $student['permanent_address']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic) || ($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic) || ($sch_setting->guardian_name) || ($sch_setting->guardian_occupation) || ($sch_setting->guardian_relation) || ($sch_setting->guardian_phone) || ($sch_setting->guardian_email) || ($sch_setting->guardian_pic) || ($sch_setting->guardian_address)) {
                            ?>
                                <div class="tshadow mb25 bozero">
                                    <h3 class="pagetitleh2"><?php echo $this->lang->line('parent_guardian_detail'); ?> </h3>
                                    <div class="table-responsive around10 pt0">
                                        <table class="table3 table-striped table-hover">
                                            <?php if ($sch_setting->father_name) {
                                            ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('father_name'); ?></td>
                                                    <td><?php echo $student['father_name']; ?></td>
                                                    <?php if ($sch_setting->father_pic) {
                                                    ?>
                                                        <td rowspan="3" width="100"><img class="profile-user-img img-responsive img-rounded border0" src="             
            <?php
                                                        if (!empty($student["father_pic"])) {
                                                            echo base_url() . $student["father_pic"] . img_time();
                                                        } else {
                                                            echo base_url() . "uploads/student_images/no_image.png" . img_time();
                                                        }
            ?>
        "></td>
                                                    <?php } ?>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->father_phone) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('father_phone'); ?></td>
                                                    <td><?php echo $student['father_phone']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->father_occupation) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('father_occupation'); ?></td>
                                                    <td><?php echo $student['father_occupation']; ?></td>
                                                </tr>
                                            <?php }
                                            ?>
                                            <tr class="bordertop">
                                                <td><?php if ($sch_setting->mother_name) {
                                                        echo $this->lang->line('mother_name');
                                                    } ?></td>
                                                <td><?php if ($sch_setting->mother_name) {
                                                        echo $student['mother_name'];
                                                    } ?></td>

                                                <td rowspan="3" width="100"> <?php if ($sch_setting->mother_pic) {
                                                                                ?><img class="profile-user-img img-responsive img-rounded border0" src="
        <?php
                                                                                    if (!empty($student["mother_pic"])) {
                                                                                        echo base_url() . $student["mother_pic"] . img_time();
                                                                                    } else {
                                                                                        echo base_url() . "uploads/student_images/no_image.png" . img_time();
                                                                                    }
        ?>
        
        "> <?php } ?></td>

                                            </tr>
                                            <?php if ($sch_setting->mother_phone) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('mother_phone'); ?></td>
                                                    <td><?php echo $student['mother_phone']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->mother_occupation) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('mother_occupation'); ?></td>
                                                    <td><?php echo $student['mother_occupation']; ?></td>
                                                </tr>
                                            <?php } ?>
                                            <tr class="bordertop">
                                                <td><?php if ($sch_setting->guardian_name) {
                                                        echo $this->lang->line('guardian_name');
                                                    } ?></td>
                                                <td><?php if ($sch_setting->guardian_name) {
                                                        echo $student['guardian_name'];
                                                    } ?></td>
                                                <td rowspan="3"><?php if ($sch_setting->guardian_pic) {
                                                                ?><img class="profile-user-img img-responsive img-rounded border0" src="<?php
                                                                                if (!empty($student["guardian_pic"])) {
                                                                                    echo base_url() . $student["guardian_pic"] . img_time();
                                                                                } else {
                                                                                    echo base_url() . "uploads/student_images/no_image.png" . img_time();
                                                                                }
                                                                                ?>
        
        
        "> <?php } ?></td>

                                            </tr>
                                            <?php if ($sch_setting->guardian_email) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('guardian_email'); ?></td>
                                                    <td><?php echo $student['guardian_email']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->guardian_relation) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('guardian_relation'); ?></td>
                                                    <td><?php echo $student['guardian_relation']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->guardian_phone) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('guardian_phone'); ?></td>
                                                    <td><?php echo $student['guardian_phone']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->guardian_occupation) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('guardian_occupation'); ?></td>
                                                    <td><?php echo $student['guardian_occupation']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->guardian_address) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('guardian_address'); ?></td>
                                                    <td><?php echo $student['guardian_address']; ?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php }
                            if ($sch_setting->route_list) {
                            ?>
                                <?php if ($student['vehroute_id'] != 0) { ?>
                                    <div class="tshadow mb25  bozero">
                                        <h3 class="pagetitleh2"><?php echo $this->lang->line('transport_details'); ?></h3>
                                        <div class="table-responsive around10 pt0">
                                            <table class="table3 table-striped table-hover tmb0">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%"><?php echo $this->lang->line('pick_up_point'); ?></td>
                                                        <td class="col-md-5"><?php echo $student['pickup_point_name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="35%"><?php echo $this->lang->line('route'); ?></td>
                                                        <td class="col-md-5"><?php echo $student['route_title']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $this->lang->line('vehicle_number'); ?></td>
                                                        <td><?php echo $student['vehicle_no']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $this->lang->line('driver_name'); ?></td>
                                                        <td><?php echo $student['driver_name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $this->lang->line('driver_contact'); ?></td>
                                                        <td><?php echo $student['driver_contact']; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                            <?php if ($sch_setting->hostel_id) {
                            ?>
                                <?php
                                if ($student['hostel_room_id'] != 0) {
                                ?>
                                    <div class="tshadow mb25  bozero">
                                        <h3 class="pagetitleh2"><?php echo $this->lang->line('hostel_details') ?></h3>
                                        <div class="table-responsive around10 pt0">
                                            <table class="table3 table-striped tmb0 table-hover">
                                                <tbody>
                                                    <tr>
                                                        <td width="35%"><?php echo $this->lang->line('hostel'); ?></td>
                                                        <td class="col-md-5"><?php echo $student['hostel_name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $this->lang->line('room_no'); ?></td>
                                                        <td><?php echo $student['room_no']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $this->lang->line('room_type'); ?></td>
                                                        <td><?php echo $student['room_type']; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                            <div class="tshadow mb25  bozero">
                                <h3 class="pagetitleh2"><?php echo $this->lang->line('miscellaneous_details'); ?></h3>
                                <div class="table-responsive around10 pt0">
                                    <table class="table3 table-striped table-hover">
                                        <tbody>
                                            <?php if ($sch_setting->is_blood_group) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('blood_group'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['blood_group']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->is_student_house) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('house'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['house_name']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->student_height) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('height'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['height']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->student_weight) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('weight'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['weight']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->measurement_date) {
                                            ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('measurement_date'); ?></td>
                                                    <td class="col-md-5"><?php
                                                                            if (!empty($student['measurement_date']) && $student['measurement_date'] != '0000-00-00') {
                                                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['measurement_date']));
                                                                            }
                                                                            ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->previous_school_details) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('previous_school_details'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['previous_school']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->national_identification_no) { ?>
                                                <tr>
                                                    <td width="35%"><?php echo $this->lang->line('national_identification_number'); ?></td>
                                                    <td class="col-md-5"><?php echo $student['adhar_no']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->local_identification_no) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('local_identification_number'); ?></td>
                                                    <td><?php echo $student['samagra_id']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->bank_account_no) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('bank_account_number'); ?></td>
                                                    <td><?php echo $student['bank_account_no']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->bank_name) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('bank_name'); ?></td>
                                                    <td><?php echo $student['bank_name']; ?></td>
                                                </tr>
                                            <?php }
                                            if ($sch_setting->ifsc_code) { ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line('ifsc_code'); ?></td>
                                                    <td><?php echo $student['ifsc_code']; ?></td>
                                                </tr>

                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                   
                        </div>

                        <?php if ($this->studentmodule_lib->hasActive('fees')) { ?>
                            <div class="tab-pane" id="fee">
                                <?php
                         
                                if (empty($student_due_fee) && empty($student_discount_fee) && empty($transport_fees)) {
                                ?>
                                    <div class="alert alert-danger">
                                        <?php echo $this->lang->line('no_record_found'); ?>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="feetable">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('fees'); ?></th>
                                                    <th class="text text-left"><?php echo $this->lang->line('due_date'); ?></th>
                                                    <th class="text text-left"><?php echo $this->lang->line('status'); ?></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                    <th class="text text-left"><?php echo $this->lang->line('payment_id'); ?></th>
                                                    <th class="text text-left"><?php echo $this->lang->line('mode'); ?></th>
                                                    <th class="text text-left"><?php echo $this->lang->line('date'); ?></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('paid'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                    <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                </tr>
                                            </thead>
                                            <?php
                                            if (empty($student_due_fee) && empty($student_discount_fee) && empty($transport_fees)) {
                                            ?>
                                            <?php
                                            } else {
                                            ?>
                                                <tbody>
                                                    <?php
                                                    $total_amount           = "0";
                                                    $total_deposite_amount  = "0";
                                                    $total_fine_amount      = "0";
                                                    $total_discount_amount  = "0";
                                                    $total_balance_amount   = "0";
                                                    $alot_fee_discount      = 0;
                                                    $total_fees_fine_amount = 0;
													 $fees_fine_amount=0;
                                                    foreach ($student_due_fee as $key => $fee) {

                                                        foreach ($fee->fees as $fee_key => $fee_value) {
                                                            $fee_paid     = 0;
                                                            $fee_discount = 0;
                                                            $fee_fine     = 0;

                                                            if (!empty($fee_value->amount_detail)) {
                                                                $fee_deposits = json_decode(($fee_value->amount_detail));

                                                                foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                                    $fee_paid     = $fee_paid + $fee_deposits_value->amount;
                                                                    $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                                    $fee_fine     = $fee_fine + $fee_deposits_value->amount_fine;
                                                                }
                                                            }
                                                            $total_amount          = $total_amount + $fee_value->amount;
                                                            $total_discount_amount = $total_discount_amount + $fee_discount;
                                                            $total_deposite_amount = $total_deposite_amount + $fee_paid;
                                                            $total_fine_amount     = $total_fine_amount + $fee_fine;
                                                            $feetype_balance       = $fee_value->amount - ($fee_paid + $fee_discount);
															if($feetype_balance<0){
																$feetype_balance=0;
															}
                                                            $total_balance_amount  = $total_balance_amount + $feetype_balance;
                                                            if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != null) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d')))) {
                                                            }

                                                    ?>
                                                            <?php
                                                            if ($feetype_balance > 0 && ($fee_value->due_date != "0000-00-00" && $fee_value->due_date != null) && strtotime($fee_value->due_date) < strtotime(date('Y-m-d'))) {
                                                            ?>
                                                                <tr class="danger">
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <tr class="dark-gray">
                                                                <?php
                                                            }
                                                                ?>

                                                                <td>
                                                                    <?php
                                                                    if ($fee_value->is_system) {
                                                                        echo $this->lang->line($fee_value->type) . " (" . $this->lang->line($fee_value->code) . ")";
                                                                    } else {
                                                                        echo $fee_value->type . " (" . $fee_value->code . ")";
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="text text-left">
                                                                    <?php
                                                                    if ($fee_value->due_date) {
                                                                        echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_value->due_date));
                                                                    } else {
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td class="text text-left">
                                                                    <?php
                                                                    if ($feetype_balance == 0) {
                                                                    ?><span class="label label-success"><?php echo $this->lang->line('paid'); ?></span><?php
                                                                                                        } else if (!empty($fee_value->amount_detail)) {
                                                                                                            ?><span class="label label-warning"><?php echo $this->lang->line('partial'); ?></span><?php
                                                                                                            } else {
                                                                                                                ?><span class="label label-danger"><?php echo $this->lang->line('unpaid'); ?></span><?php
                                                                                                            }
                                                                                                            ?>
                                                                </td>
                                                                <td class="text text-right">

                                                                    <?php echo amountFormat($fee_value->amount);
                                                                    if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != null) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d')))) {
																		
																		
                                                                         // get cumulative fine amount as delay days 
                                                                        if($fee_value->fine_type=='cumulative'){
                                                                            $date1=date_create("$fee_value->due_date");
                                                                            $date2=date_create(date('Y-m-d'));
                                                                            $diff=date_diff($date1,$date2);
                                                                            $due_days= $diff->format("%a");;
                                                                            
                                                                            if($this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days)){
                                                                                $due_fine_amount=$this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days);
                                                                            }else{
                                                                                $due_fine_amount=0;
                                                                            }
                                                                            $fees_fine_amount       = $due_fine_amount;
                                                                            $total_fees_fine_amount = $total_fees_fine_amount + $due_fine_amount;
                                                            
                                                                        }else if($fee_value->fine_type=='fix' || $fee_value->fine_type=='percentage'){
                                                                            $fees_fine_amount       = $fee_value->fine_amount;
                                                                            $total_fees_fine_amount = $total_fees_fine_amount + $fee_value->fine_amount;
                                                                        }
                                                                        // get cumulative fine amount as delay days
                                                                    ?>
                                                                        <span class="text text-danger"><?php echo " + " . amountFormat($fees_fine_amount); ?></span>
                                                                    <?php

                                                                    }

                                                                    ?>
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text text-right"><?php
                                                                                            echo (amountFormat($fee_discount));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            echo (amountFormat($fee_fine));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            echo (amountFormat($fee_paid));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            $display_none = "ss-none";
                                                                                            if ($feetype_balance > 0) {
                                                                                                $display_none = "";

                                                                                                echo (amountFormat($feetype_balance));
                                                                                            }
                                                                                            ?>
                                                                </td>
                                                                </tr>
                                                                <?php
                                                                if (!empty($fee_value->amount_detail)) {

                                                                    $fee_deposits = json_decode(($fee_value->amount_detail));

                                                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                                ?>
                                                                        <tr class="white-td">
                                                                            <!-- <td></td> -->
                                                                            <td></td>
                                                                            <td></td>
                                                                            <td></td>
                                                                            <td class="text-right"><img src="<?php echo base_url(); ?>backend/images/table-arrow.png" alt="" /></td>
                                                                            <td class="text text-center">
                                                                                <a href="#" data-toggle="popover" class="detail_popover"> <?php echo $fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?></a>
                                                                                <div class="fee_detail_popover" style="display: none">
                                                                                    <?php
                                                                                    if ($fee_deposits_value->description == "") {
                                                                                    ?>
                                                                                        <p class="text text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                                                    <?php
                                                                                    } else {
                                                                                    ?>
                                                                                        <p class="text text-info"><?php echo $fee_deposits_value->description; ?></p>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            <td class="text text-center"><?php echo $this->lang->line(strtolower($fee_deposits_value->payment_mode)); ?></td>
                                                                            <td class="text text-center">
                                                                                <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                            </td>
                                                                            <td class="text text-right"><?php echo (amountFormat($fee_deposits_value->amount_discount)); ?></td>
                                                                            <td class="text text-right"><?php echo (amountFormat($fee_deposits_value->amount_fine)); ?></td>
                                                                            <td class="text text-right"><?php echo (amountFormat($fee_deposits_value->amount)); ?></td>
                                                                            <td></td>
                                                                        </tr>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                        <?php
                                                        }
                                                    }
                                                        ?>
                                                        <?php

                                                        if (!empty($transport_fees)) {
                                                            foreach ($transport_fees as $transport_fee_key => $transport_fee_value) {

                                                                $fee_paid         = 0;
                                                                $fee_discount     = 0;
                                                                $fee_fine         = 0;
                                                                $fees_fine_amount = 0;
                                                                $feetype_balance  = 0;

                                                                if (!empty($transport_fee_value->amount_detail)) {
                                                                    $fee_deposits = json_decode(($transport_fee_value->amount_detail));

                                                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                                        $fee_paid     = $fee_paid + $fee_deposits_value->amount;
                                                                        $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                                                        $fee_fine     = $fee_fine + $fee_deposits_value->amount_fine;
                                                                    }
                                                                }

                                                                $feetype_balance       = $transport_fee_value->fees - ($fee_paid + $fee_discount);


                                                                if (($transport_fee_value->due_date != "0000-00-00" && $transport_fee_value->due_date != null) && (strtotime($transport_fee_value->due_date) < strtotime(date('Y-m-d')))) {
                                                                    $fees_fine_amount  =  is_null($transport_fee_value->fine_percentage) ? $transport_fee_value->fine_amount : percentageAmount($transport_fee_value->fees, $transport_fee_value->fine_percentage);
                                                                    $total_fees_fine_amount = $total_fees_fine_amount + $fees_fine_amount;
                                                                }

                                                                $total_amount          +=  $transport_fee_value->fees;
                                                                $total_discount_amount +=  $fee_discount;
                                                                $total_deposite_amount += $fee_paid;
                                                                $total_fine_amount     += $fee_fine;
                                                                $total_balance_amount  +=  $feetype_balance;

                                                                if (strtotime($transport_fee_value->due_date) < strtotime(date('Y-m-d'))) {
                                                        ?>
                                                                    <tr class="danger font12">
                                                                    <?php
                                                                } else {
                                                                    ?>
                                                                    <tr class="dark-gray">
                                                                    <?php
                                                                }
                                                                    ?>

                                                                    <td align="left"><?php echo $this->lang->line('transport_fees')." (".$transport_fee_value->month.")"; ?></td>
                                                                    
                                                                    <td align="left" class="text text-left">
                                                                        <?php echo $this->customlib->dateformat($transport_fee_value->due_date); ?> </td>
                                                                    <td align="left" class="text text-left width85">
                                                                        <?php
                                                                        if ($feetype_balance == 0) {
                                                                        ?><span class="label label-success"><?php echo $this->lang->line('paid'); ?></span><?php
                                                                                            } else if (!empty($transport_fee_value->amount_detail)) {
                                                                                                ?><span class="label label-warning"><?php echo $this->lang->line('partial'); ?></span><?php
                                                                                                } else {
                                                                                                    ?><span class="label label-danger"><?php echo $this->lang->line('unpaid'); ?></span><?php
                                                                                                }
                                                                                                ?>

                                                                    </td>
                                                                    <td class="text text-right"><?php echo amountFormat($transport_fee_value->fees);

                                                                                                if (($transport_fee_value->due_date != "0000-00-00" && $transport_fee_value->due_date != null) && (strtotime($transport_fee_value->due_date) < strtotime(date('Y-m-d')))) {
                                                                                                    $tr_fine_amount = $transport_fee_value->fine_amount;
                                                                                                    if ($transport_fee_value->fine_type != "" && $transport_fee_value->fine_type == "percentage") {

                                                                                                        $tr_fine_amount = percentageAmount($transport_fee_value->fees, $transport_fee_value->fine_percentage);
                                                                                                    }
                                                                                                ?>

                                                                            <span data-toggle="popover" class="text text-danger detail_popover"><?php echo " + " . (amountFormat($tr_fine_amount)); ?></span>

                                                                            <div class="fee_detail_popover" style="display: none">
                                                                                <?php
                                                                                                    if ($tr_fine_amount != "") {
                                                                                ?>
                                                                                    <p class="text text-danger"><?php echo $this->lang->line('fine'); ?></p>
                                                                                <?php
                                                                                                    }
                                                                                ?>

                                                                            <?php
                                                                                                }
                                                                            ?>
                                                                    </td>

                                                                    <td class="text text-left"></td>
                                                                    <td class="text text-left"></td>
                                                                    <td class="text text-left"></td>
                                                                    <td class="text text-right">
                                                                        <?php echo amountFormat($fee_discount); ?>
                                                                    </td>
                                                                    <td class="text text-right">
                                                                        <?php echo amountFormat($fee_fine); ?>
                                                                    </td>
                                                                    <td class="text text-right">
                                                                        <?php echo amountFormat($fee_paid); ?>

                                                                    </td>
                                                                    <td class="text text-right">
                                                                        <?php
                                                                        $display_none = "ss-none";
                                                                        if ($feetype_balance > 0) {
                                                                            $display_none = "";
                                                                            echo amountFormat($feetype_balance);
                                                                        }
                                                                        ?>
                                                                    </td>

                                                                    </tr>
                                                                    <?php
                                                                    if (!empty($transport_fee_value->amount_detail)) {

                                                                        $fee_deposits = json_decode(($transport_fee_value->amount_detail));

                                                                        foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                                                    ?>
                                                                            <tr class="white-td">
                                                                                <!-- <td align="left"></td> -->
                                                                                <!-- <td align="left"></td> -->
                                                                                <td align="left"></td>
                                                                                <td align="left"></td>
                                                                                <td align="left"></td>
                                                                                <td class="text-right"><img src="<?php echo base_url(); ?>backend/images/table-arrow.png" alt="" /></td>
                                                                                <td class="text text-left">


                                                                                    <a href="#" data-toggle="popover" class="detail_popover"> <?php echo $transport_fee_value->student_fees_deposite_id . "/" . $fee_deposits_value->inv_no; ?></a>
                                                                                    <div class="fee_detail_popover" style="display: none">
                                                                                        <?php
                                                                                        if ($fee_deposits_value->description == "") {
                                                                                        ?>
                                                                                            <p class="text text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                                                        <?php
                                                                                        } else {
                                                                                        ?>
                                                                                            <p class="text text-info"><?php echo $fee_deposits_value->description; ?></p>
                                                                                        <?php
                                                                                        }
                                                                                        ?>
                                                                                    </div>


                                                                                </td>
                                                                                <td class="text text-center"><?php echo $this->lang->line(strtolower($fee_deposits_value->payment_mode)); ?></td>
                                                                                <td class="text text-center">

                                                                                    <?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_deposits_value->date)); ?>
                                                                                </td>
                                                                                <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_discount); ?></td>
                                                                                <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount_fine); ?></td>
                                                                                <td class="text text-right"><?php echo amountFormat($fee_deposits_value->amount); ?></td>
                                                                                <td></td>

                                                                            </tr>
                                                                    <?php
                                                                        }
                                                                    }
                                                                    ?>

                                                            <?php
                                                            }
                                                        }
                                                        ?>
                                                          
                                                        <tr class="box box-solid total-bg">
                                                                <!-- <td></td> -->
                                                                <td></td>
                                                                <td class="text text-right" colspan="2"><?php echo $this->lang->line('grand_total'); ?></td>
                                                                <td class="text text-right">
                                                                    <?php
                                                                    echo $currency_symbol . amountFormat($total_amount) . "<span class='text text-danger'>+" . amountFormat($total_fees_fine_amount) . "</span>";
                                                                    ?></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td class="text text-right"><?php
                                                                                            echo ($currency_symbol . amountFormat($total_discount_amount + $alot_fee_discount));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            echo ($currency_symbol . amountFormat($total_fine_amount));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            echo ($currency_symbol . amountFormat($total_deposite_amount));
                                                                                            ?></td>
                                                                <td class="text text-right"><?php
                                                                                            echo ($currency_symbol . amountFormat($total_balance_amount - $alot_fee_discount));
                                                                                            ?></td>
																							
                                                        </tr>
                                                        <?php
                                                    }
                                                        ?>
                                                </tbody>
                                        </table>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        <?php } ?>


                        <div class="tab-pane" id="timeline">
                            <div class="relative z-index-1">
                                <?php if ($student_timeline == 'enabled') { ?>
                                    <button type="button" id="myTimelineButton" class="btn btn-sm btn-primary pull-right"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add') ?></button>
                                <?php } ?>
                            </div>

                            <div class="timeline-header no-border">
                                <div id="timeline_list" class="mt10">
                                    <?php if (empty($timeline_list)) { ?>
                                        
										<br><br>
                                        <div class="alert alert-info"><?php echo $this->lang->line("no_record_found") ?></div>
										
                                    <?php } else { ?>
                                        <ul class="timeline timeline-inverse">
                                            <?php foreach ($timeline_list as $key => $value) { ?>
                                                <li class="time-label">
                                                    <span class="bg-blue">
                                                        <?php if ($value['timeline_date'] != '0000-00-00') {
                                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['timeline_date']));
                                                        } ?>

                                                    </span>
                                                </li>
                                                <li>
                                                    <i class="fa fa-list-alt bg-blue"></i>
                                                    <div class="timeline-item">

                                                        <?php if ($value["created_student_id"] == $student_id) {
                                                            if ($student_timeline == 'enabled') { ?>
                                                                <span class="time"><a class="defaults-c text-right" data-toggle="tooltip" onclick="delete_timeline('<?php echo $value['id']; ?>')" data-original-title="<?php echo $this->lang->line('delete'); ?>"><i class="fa fa-trash"></i></a> </span>
                                                                <span class="time"><a data-toggle="tooltip" class="pull-right edit_timeline defaults-c text-right" data-id="<?php echo $value["id"]; ?>" data-original-title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a></span>
                                                        <?php }
                                                        } ?>

                                                        <?php if (!empty($value["document"])) { ?>
                                                            <span class="time"><a class="defaults-c text-right" style="color:#0084B4" data-toggle="tooltip" href="<?php echo base_url() . "user/timeline/download/" . $value["id"] ?>" data-original-title="<?php echo $this->lang->line('download'); ?>"><i class="fa fa-download"></i></a></span>
                                                        <?php } ?>
                                                        <h3 class="timeline-header text-aqua text-break"> <?php echo $value['title']; ?> </h3>
                                                        <div class="timeline-body text-break">
                                                            <?php echo $value['description']; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php } ?>
                                            <li><i class="fa fa-clock-o bg-blue"></i></li>
                                        <?php } ?>
                                        </ul>
                                </div>
                            </div>
                        </div>

                        <?php

                        if (!$sch_setting->attendence_type) {
                        ?>
                            <div class="tab-pane" id="attendance">
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-sm-6 col20per">
                                        <div class="staffprofile">
                                            <h5><?php echo $this->lang->line('total_present'); ?></h5>
                                            <h4><?php
                                                if (!empty($countAttendance[1])) {
                                                    echo $countAttendance[1];
                                                } else {
                                                    echo "0";
                                                }
                                                ?></h4>
                                            <div class="icon">
                                                <i class="fa fa-check-square-o"></i>
                                            </div>
                                        </div>
                                    </div><!--./col-md-3-->
                                    <div class="col-lg-3 col-md-4 col-sm-6 col20per">
                                        <div class="staffprofile">

                                            <h5><?php echo $this->lang->line('total_late'); ?></h5>
                                            <h4><?php
                                                if (!empty($countAttendance[3])) {
                                                    echo $countAttendance[3];
                                                } else {
                                                    echo "0";
                                                }
                                                ?></h4>
                                            <div class="icon">
                                                <i class="fa  fa-check-square-o"></i>
                                            </div>
                                        </div>
                                    </div><!--./col-md-3-->
                                    <div class="col-lg-3 col-md-4 col-sm-6 col20per">
                                        <div class="staffprofile">
                                            <h5><?php echo $this->lang->line('total_absent'); ?></h5>
                                            <h4><?php
                                                if (!empty($countAttendance[4])) {
                                                    echo $countAttendance[4];
                                                } else {
                                                    echo "0";
                                                }
                                                ?></h4>
                                            <div class="icon">
                                                <i class="fa  fa-check-square-o"></i>
                                            </div>
                                        </div>
                                    </div><!--./col-md-3-->
                                    <div class="col-lg-3 col-md-4 col-sm-6 col20per">
                                        <div class="staffprofile">
                                            <h5><?php echo $this->lang->line('total_half_day'); ?></h5>
                                            <h4><?php
                                                if (!empty($countAttendance[6])) {
                                                    echo $countAttendance[6];
                                                } else {
                                                    echo "0";
                                                }
                                                ?></h4>
                                            <div class="icon">
                                                <i class="fa  fa-check-square-o"></i>
                                            </div>
                                        </div>
                                    </div><!--./col-md-3-->
                                    <div class="col-lg-3 col-md-4 col-sm-6 col20per">
                                        <div class="staffprofile">
                                            <h5><?php echo $this->lang->line('total_holiday'); ?></h5>
                                            <h4><?php
                                                if (!empty($countAttendance[5])) {
                                                    echo $countAttendance[5];
                                                } else {
                                                    echo "0";
                                                }
                                                ?></h4>
                                            <div class="icon">
                                                <i class="fa  fa-check-square-o"></i>
                                            </div>
                                        </div>
                                    </div><!--./col-md-3-->
                                </div>
                                <div class="row">

                                    <div class="col-md-12 col-sm-12">
                                        <div class="halfday pull-right">
                                            <?php

                                            foreach ($attendencetypeslist as $key_type => $value_type) {
                                            ?>
                                                <b>
                                                    <?php
                                                    $att_type = str_replace(" ", "_", strtolower($value_type['type']));
                                                    echo $this->lang->line($att_type) . ": " . $value_type['key_value'] . "";
                                                    ?>
                                                </b>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="download_label"><?php echo $student["firstname"] . " " . $student["lastname"] . ' (' . $student["admission_no"] . ')'; ?></div>
                                    <div id="ajaxattendance" class="table-responsive">

                                        <table class="table table-bordered table-hover example" data-export-title="<?php echo $student["firstname"] . " " . $student["lastname"] . ' (' . $student["admission_no"] . ')'; ?>" >
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <?php

                                                        ?>
                                                        <?php echo $this->lang->line('date_month'); ?>
                                                    </th>
                                                    <?php foreach ($monthlist as $monthkey => $monthvalue) {

                                                    ?>
                                                        <th><?php echo $monthvalue; ?></th>
                                                    <?php }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $j = 1;
                                                for ($i = 1; $i <= 31; $i++) {


                                                ?>
                                                    <tr>
                                                        <td><?php echo $i; ?></td>
                                                        <?php
                                                        $st = $start_year;
                                                        foreach ($monthlist as $monthkey => $monthvalue) {

                                                            $datemonth       = $monthkey;
                                                            if ($datemonth < $sch_setting->start_month) {
                                                                $st = $Next_year;
                                                            }

                                                        ?>
                                                            <td>
                                                                <?php

                                                                $date_att = $st . "-" . $monthkey . "-" . sprintf("%02d", $i);



                                                                if (array_key_exists($date_att, $resultlist) && !empty($resultlist[$date_att])) {

                                                                    echo ($resultlist[$date_att]['key']);
                                                                }



                                                                ?>


                                                            </td>
                                                        <?php

                                                        }
                                                        ?> 
                                                    </tr>
                                                <?php

                                                    $j++;
                                                }

                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        <?php
                        }
                        ?>


                        <div class="tab-pane" id="documents">
                            <!-- <div class="download_label"><?php echo "Uploaded Documents" ?></div> -->
                            <div class="timeline-header no-border">
                                <div class="pull-right mb5">
									 <button type="button" data-student-session-id="<?php echo $student['student_session_id'] ?>" class="btn btn-xs btn-primary me-5 ms-rtl-5 mb5 myTransportFeeBtn"> <i class="fa fa-upload"></i> <?php echo $this->lang->line('upload_documents'); ?></button>
									 
                                  <?php  
                                $userdata = $this->customlib->getUserData(); 
                                $user_type = $this->session->userdata['student']['role'];

                                if(($googledrivepicketsetting['is_enable']=="enabled" && $googledrivepicketsetting['is_student']=="enabled" &&  $user_type=="student")) { ?>
                                    <button id="google_drive" onclick="opengoogledrivemodel()" class="btn btn-xs btn-primary me-5 ms-rtl-5 mb5"> <i class="fa fa-upload"></i> <?php echo $this->lang->line('upload_through_google_drive'); ?></button>
                                 <?php } 

                                if(($googledrivepicketsetting['is_enable']=="enabled" && $googledrivepicketsetting['is_parent']=="enabled" &&  $user_type=="parent")) { ?>
                                    <button id="google_drive" onclick="opengoogledrivemodel()" class="btn btn-xs btn-primary me-5 ms-rtl-5 mb5"> <i class="fa fa-upload"></i> <?php echo $this->lang->line('upload_through_google_drive'); ?></button>
                                 <?php } ?> 
                                
								   
                                </div>
                                <div class="table-responsive" style="clear: both;">
                                    <?php
                                    if (!empty($student_doc)) {
                                    ?>
                                        <table class="table table-striped table-bordered ">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <?php echo $this->lang->line('title'); ?>
                                                    </th>
                                                    <th>
                                                        <?php echo $this->lang->line('file_name'); ?>
                                                    </th>
                                                    <th class="mailbox-date text-right">
                                                        <?php echo $this->lang->line('action'); ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($student_doc as $value) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $value['title']; ?></td>
                                                        <td><?php echo $this->media_storage->fileview($value['doc']); ?></td>
                                                        <td class="mailbox-date text-right">
                                                            <a href="<?php echo base_url(); ?>user/user/download/<?php echo $value['student_id'] . "/" . $value['id']; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
													
													
													
                                                <?php } ?>
													<tr class="laoder hide">
                                                        <td colspan="3"><center><img src="<?php echo base_url() . '/backend/images/loading.gif' ?>"></center></td>
                                                    </tr>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <div class="alert alert-danger">
                                            <?php echo $this->lang->line('no_record_found'); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!------- Behaviour Report Start-------->
                        <?php
                        if ($this->module_lib->hasModule('behaviour_records') && $this->studentmodule_lib->hasActive('behaviour_records')) {  ?>
                            <div class="tab-pane" id="incident">
                                <div class="timeline-header no-border">
                                    <div class="mailbox-messages table-responsive overflow-visible-lg">
                                        <div class="download_label"><?php echo $student["firstname"] . " " . $student["lastname"] . ' (' . $student["admission_no"] . ')'; ?></div>
                                        <table class="table table-striped table-bordered table-hover example" data-export-title="<?php echo $student["firstname"] . " " . $student["lastname"] . ' (' . $student["admission_no"] . ')'; ?>" >
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('title'); ?>
                                                    </th>
                                                    <th><?php echo $this->lang->line('point'); ?>
                                                    </th>
                                                    <th><?php echo $this->lang->line('date'); ?></th>
                                                    <th><?php echo $this->lang->line('description'); ?></th>
                                                    <th><?php echo $this->lang->line('assign_by'); ?></th>
                                                    <th style="width: 70px;" class="noExport"><?php echo $this->lang->line('action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($assignstudent)) {
                                                ?>
                                                  
                                                    <?php
                                                } else {

                                                    foreach ($assignstudent as $assignstudent_value) {
                                                        $staff_id = '';
                                                        if ($assignstudent_value['staff_employee_id'] != "") {
                                                            $staff_id = ' (' . $assignstudent_value['staff_employee_id'] . ')';
                                                        }

                                                        $pointclass = '';
                                                        if ($assignstudent_value['point'] < 0) {
                                                            $pointclass = 'danger';
                                                        }
                                                    ?>
                                                        <tr class="<?php echo $pointclass; ?>">
                                                            <td class="mailbox-name"> <?php echo $assignstudent_value['title'] ?></td>
                                                            <td class="mailbox-name"> <?php echo $assignstudent_value['point'] ?></td>
                                                            <td class="mailbox-name"> <?php echo $this->customlib->dateformat($assignstudent_value['created_at']) ?></td>
                                                            <td class="mailbox-name" style="width: 380px;"> <?php echo $assignstudent_value['description'] ?></td>
                                                            <td class="mailbox-name">
                                                                <?php
                                                                if ($superadmin_restriction == 'disabled' && $assignstudent_value['role_id'] == 7) {
                                                                    echo  '';
                                                                } else {
                                                                    echo $assignstudent_value['staff_name'] . ' ' . $assignstudent_value['staff_surname'] . $staff_id;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="mailbox-name">
                                                                <a class="btn btn-primary btn-xs comments relative overflow-visible" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->lang->line('comment'); ?>" data-record-id="<?php echo $assignstudent_value['id'] ?>">
                                                                    <?php if ($assignstudent_value['totalcomments']['totalcomments'] != '0') { ?>
                                                                        <span class="comment-badges"><?php echo $assignstudent_value['totalcomments']['totalcomments']; ?></span><?php } ?>
                                                                    <i class="fa fa-comment"></i>

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
                        <?php
                        }
                        ?>
                        <!------- Behaviour Report End--------->
                        
                        
                        <!------- CBSE Exam Start-------->
                        <?php
                        if ($this->module_lib->hasModule('cbseexam') && $this->studentmodule_lib->hasActive('cbseexam')) {  ?>
                            <div class="tab-pane" id="cbseexam">
                                <div class="dt-buttons btn-group pull-right">
                                    <a class="btn btn-default btn-xs dt-button no_print" title="<?php echo $this->lang->line('print'); ?>" id="print" onclick="printDiv('cbseexam_print')"><i class="fa fa-print"></i></a>
                                </div>              
                               
                                        
                                        <div class="row" id="cbseexam_print">
                                            <div class="col-md-12">
                
                                                <?php
                                                if (!empty($exams)) {
                                                    foreach ($exams as $exam_key => $exam_value) {
                
                                                        $total_marks = 0;
                                                        $total_max_marks = 0;
                                                ?>
                
                                                        <div class="shadow-sm mb30">
                                                            <h3 class="pagetitleh2 mt10 border-b-none pl-5"><?php echo  $exam_value->name; ?></h3>
                
                                                            <div class="table-responsive">
                
                                                                <?php
                                                                if (!empty($exam_value->subjects)) {
                                                                ?>
                                                                    <table class="table table-bordered table-b mb0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="bolds">
                                                                                    <?php echo $this->lang->line('subject') ;?>
                                                                                </td>
                                                                                <?php
                
                                                                                foreach ($exam_value->exam_assessments as $exam_assessment_key => $exam_assessment_value) {
                                                                                ?>
                                                                                    <td class="text-center bolds">
                                                                                    <?php $assessment_code= ($exam_assessment_value->code == "") ? "": " (" . $exam_assessment_value->code . ")"; ?>
                                                                        <?php echo $exam_assessment_value->name . $assessment_code; ?>
                                                                                        <br />
                                                                                        (<?php echo $this->lang->line('max'); ?> <?php echo $exam_assessment_value->maximum_marks; ?>)
                                                                                    </td>
                                                                                <?php
                                                                                }
                
                                                                                ?>
                                                                                <td class="bolds">
                                                                                    <?php echo $this->lang->line('total') ;?>
                                                                                </td>
                                                                            </tr>
                
                
                                                                            <?php
                                                                            foreach ($exam_value->subjects as $subject_key => $subject_value) {
                                                                                $subject_total = 0;
                                                                            ?>
                                                                                <tr>
                
                                                                                    <td>

                                                                            <?php $subject_code= ($subject_value->subject_code == "") ? "": " (" . $subject_value->subject_code . ")"; ?>


                                                                                 <?php echo $subject_value->subject_name . $subject_code; ?>

                                                                                    </td>
                                                                                    <?php
                                                                                    foreach ($exam_value->exam_assessments as $exam_assessment_key => $exam_assessment_value) {
                                                                                        
                                                                                    ?>
                                                                                        <td class="text-center">
                                                                                            <?php
                                                                                    
                                                                                            
                                                                                        $assessment_exists=  find_subject_assessment_exists($exam_value->exam_subject_assessments, $subject_value->id, $exam_assessment_value->id);
                
                                                                                        if($assessment_exists){
                                                                                            $assessment_array = findAssessmentValue($subject_value->subject_id, $exam_assessment_value->id, $exam_value);
                                                                                            echo ($assessment_array['is_absent']) ? $this->lang->line('abs') : $assessment_array['marks'];
                                                                                            if ($assessment_array['marks'] == "N/A") {
                                                                                                $assessment_array['marks'] = 0;
                                                                                            }
                                                                                            $subject_total += $assessment_array['marks'];
                                                                                            $total_max_marks += $assessment_array['maximum_marks'];
                                                                                            $total_marks += $assessment_array['marks'];
                                                                                        }else{
                                                                                            echo "<b>xx</b>";
                                                                                        }
                
                                                                            
                                                                                            ?>
                                                                                        </td>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                    <td class="bolds">
                                                                                        <?php echo  two_digit_float($subject_total); ?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php
                                                                            }
                                                                            ?>
                
                
                                                                        </tbody>
                
                
                                                                    </table>
                                                                <?php
                
                                                                }
                                                                if($total_max_marks > 0){
                                                                $exam_percentage = getPercent($total_max_marks, $total_marks);
                                                                }else{
                                                                $exam_percentage =0;   
                                                                }
                                                                ?>            
                                                            
                                                            <table class="table table-bordered table-b mb0 bg-gray-light">
                                                                <tr>
                                                                    <td class="bolds"><?php echo $this->lang->line('total_marks'); ?> : <?php echo $total_marks . "/" . $total_max_marks; ?></td>
                                                                    <td class="bolds"><?php echo $this->lang->line('percentage'); ?> (%) : <?php echo $exam_percentage; ?></td>
                                                                    <td class="bolds"><?php echo $this->lang->line('grade'); ?> : <?php echo getGrade($exam_value->grades, $exam_percentage); ?></td>
                                                                    <td class="bolds"><?php echo $this->lang->line('rank'); ?> : <?php echo $exam_value->rank; ?></td>
                                                                </tr>
                                                            </table>
                                                        </div>  
                
                                                            
                                                        </div>
                
                                                    <?php
                
                
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="alert alert-info">
                                                        <?php echo $this->lang->line('no_exam_assigned'); ?>
                                                    </div>
                                                <?php
                                                }
                
                                                ?>
                
                                            </div>
                
                                        </div>
                                    
                            </div>
                        <?php
                        }
                        ?>
                        <!------- CBSE Exam End--------->
                        

                        <div class="tab-pane" id="exam">

                            <?php
                            if (empty($exam_result)) {
                            ?>
                                <div class="alert alert-danger">
                                    <?php echo $this->lang->line('no_record_found'); ?>
                                </div>
                            <?php
                            }
                            if (!empty($exam_result)) {
                            ?>
                                <div class="btn-group pull-right miusDM45">
                                    <a class="btn btn-default btn-xs no_print" id="print" title="<?php echo $this->lang->line('print'); ?>" onclick="printDiv('exam')"><i class="fa fa-print"></i></a>
                                </div>
                                <?php
                                foreach ($exam_result as $exam_key => $exam_value) {
                                ?>
                                    <div class="tshadow mb25">
                                        <h4 class="pagetitleh">
                                            <?php
                                            echo $exam_value->exam;
                                            ?>
                                        </h4>
                                        <?php
                                        if (!empty($exam_value->exam_result)) {
                                            if ($exam_value->exam_result['exam_connection'] == 0) {
                                                if (!empty($exam_value->exam_result['result'])) {
                                                    $exam_quality_points = 0;
                                                    $exam_total_points   = 0;
                                                    $exam_credit_hour    = 0;
                                                    $exam_grand_total    = 0;
                                                    $exam_get_total      = 0;
                                                    $exam_pass_status    = 1;
                                                    $exam_absent_status  = 0;
                                                    $total_exams         = 0;
                                        ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped ptt10" id="headerTable">
                                                            <thead>
                                                                <th><?php echo $this->lang->line('subject'); ?></th>
                                                                <?php
                                                                if ($exam_value->exam_type == "gpa") {
                                                                ?>
                                                                    <th><?php echo $this->lang->line('grade_point'); ?></th>
                                                                    <th><?php echo $this->lang->line('credit_hours'); ?></th>
                                                                    <th><?php echo $this->lang->line('quality_points'); ?></th>
                                                                <?php
                                                                }
                                                                ?>
                                                                <?php
                                                                if ($exam_value->exam_type != "gpa") {
                                                                ?>
                                                                    <th><?php echo $this->lang->line('max_marks'); ?></th>
                                                                    <?php
                                                                    if ($exam_value->exam_type != "average_passing") {

                                                                    ?>
                                                                        <th><?php echo $this->lang->line('min_marks'); ?></th>
                                                                    <?php
                                                                    }
                                                                    ?>

                                                                    <th><?php echo $this->lang->line('marks_obtained'); ?></th>
                                                                <?php
                                                                }
                                                                ?>
                                                                <?php
                                                                if ($exam_value->exam_type == "coll_grade_system" || $exam_value->exam_type == "school_grade_system") {
                                                                ?>
                                                                    <th><?php echo $this->lang->line('grade'); ?> </th>
                                                                <?php
                                                                }

                                                                if ($exam_value->exam_type == "basic_system") {
                                                                ?>
                                                                    <th>
                                                                        <?php echo $this->lang->line('result'); ?>
                                                                    </th>
                                                                <?php
                                                                }
                                                                ?>
                                                                <th><?php echo $this->lang->line('note'); ?></th>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                if (!empty($exam_value->exam_result['result'])) {
                                                                    $total_exams = 1;
                                                                    foreach ($exam_value->exam_result['result'] as $exam_result_key => $exam_result_value) {
                                                                        $exam_grand_total = $exam_grand_total + $exam_result_value->max_marks;
                                                                        $exam_get_total   = $exam_get_total + $exam_result_value->get_marks;
                                                                        $percentage_grade = ($exam_result_value->get_marks * 100) / $exam_result_value->max_marks;

                                                                        if ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                                                            $exam_pass_status = 0;
                                                                        }
                                                                ?>
                                                                        <tr>
                                                                            <td><?php echo ($exam_result_value->name); ?> <?php if ($exam_result_value->code) {
                                                                                                                                echo ' (' . $exam_result_value->code . ')';
                                                                                                                            } ?></td>
                                                                            <?php
                                                                            if ($exam_value->exam_type != "gpa") {
                                                                            ?>
                                                                                <td><?php echo ($exam_result_value->max_marks); ?></td>
                                                                                <?php
                                                                                if ($exam_value->exam_type != "average_passing") {
                                                                                ?>

                                                                                    <td><?php echo ($exam_result_value->min_marks); ?></td>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                                <td>
                                                                                    <?php
                                                                                    echo $exam_result_value->get_marks;

                                                                                    if ($exam_result_value->attendence == "absent") {
                                                                                        $exam_absent_status = 1;
                                                                                        echo "&nbsp;" . $this->lang->line('abs');
                                                                                    }
                                                                                    ?>
                                                                                </td>
                                                                            <?php
                                                                            } elseif ($exam_value->exam_type == "gpa") {
                                                                            ?>
                                                                                <td>
                                                                                    <?php

                                                                                    $percentage_grade  = ($exam_result_value->get_marks * 100) / $exam_result_value->max_marks;
                                                                                    $point             = findGradePoints($exam_grade, $exam_value->exam_type, $percentage_grade);
                                                                                    $exam_total_points = $exam_total_points + $point;
                                                                                    echo two_digit_float($point);
                                                                                    ?>
                                                                                </td>
                                                                                <td> <?php
                                                                                        echo $exam_result_value->credit_hours;
                                                                                        $exam_credit_hour = $exam_credit_hour + $exam_result_value->credit_hours;
                                                                                        ?></td>
                                                                                <td><?php
                                                                                    echo two_digit_float($exam_result_value->credit_hours * $point);
                                                                                    $exam_quality_points = $exam_quality_points + ($exam_result_value->credit_hours * $point);
                                                                                    ?></td>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                            <?php
                                                                            if ($exam_value->exam_type == "coll_grade_system" || $exam_value->exam_type == "school_grade_system") {
                                                                            ?>
                                                                                <td><?php echo findExamGrade($exam_grade, $exam_value->exam_type, $percentage_grade); ?></td>
                                                                            <?php
                                                                            }
                                                                            if ($exam_value->exam_type == "basic_system") {
                                                                            ?>
                                                                                <td>
                                                                                    <?php
                                                                                    if ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                                                                    ?>
                                                                                        <label class="label label-danger"><?php echo $this->lang->line('fail') ?></label>
                                                                                    <?php
                                                                                    } else {
                                                                                    ?>
                                                                                        <label class="label label-success"><?php echo $this->lang->line('pass') ?></label>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </td>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                            <td><?php echo ($exam_result_value->note); ?></td>
                                                                        </tr>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <?php ?>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="bgtgray">
                                                                <?php
                                                                if ($exam_value->exam_type != "gpa") {
                                                                ?>
                                                                    <div class="col-sm-3 col-lg-2 col-md-3">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('percentage') ?> : <span class="description-text"><?php

                                                                                                                                                                                            $exam_percentage = ($exam_get_total * 100) / $exam_grand_total;
                                                                                                                                                                                            echo two_digit_float($exam_percentage);
                                                                                                                                                                                            ?></span> </h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-2 col-md-3 pull ">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('rank') ?> : <span class="description-text">
                                                                                    <?php echo $exam_value->rank; ?>

                                                                                </span>
                                                                            </h5>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4 col-lg-3 col-md-4 border-right">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('result') ?> :<span class="description-text">
                                                                                    <?php
                                                                                    if ($total_exams) {
                                                                                        if ($exam_value->exam_type == "average_passing") {
                                                                                            if ($exam_value->passing_percentage <= $exam_percentage) {

                                                                                    ?>
                                                                                                <span class='label bg-green ml-rtl' style="margin-right: 5px;">
                                                                                                    <?php
                                                                                                    echo $this->lang->line('pass');
                                                                                                    ?>
                                                                                                </span> <?php
                                                                                                    } else {
                                                                                                        ?>
                                                                                                <span class='label label-danger'>
                                                                                                    <?php
                                                                                                        echo $this->lang->line('fail');
                                                                                                    ?>
                                                                                                </span>
                                                                                </span><?php
                                                                                                    }

                                                                                                    echo $this->lang->line('division') . " : " . findExamDivision($marks_division, $exam_percentage);
                                                                                                } else {
                                                                                                    if ($exam_absent_status) {
                                                                                        ?>
                                                                                <span class='label label-danger'>
                                                                                    <?php
                                                                                                        echo $this->lang->line('fail');
                                                                                    ?>
                                                                                </span>
                                                                                <?php
                                                                                                    } else {

                                                                                                        if ($exam_pass_status) {
                                                                                ?>
                                                                                    <span class='label bg-green ml-rtl' style="margin-right: 5px;">
                                                                                        <?php
                                                                                                            echo $this->lang->line('pass');
                                                                                        ?>
                                                                                    </span> <?php
                                                                                                        } else {
                                                                                            ?>
                                                                                    <span class='label label-danger'>
                                                                                        <?php
                                                                                                            echo $this->lang->line('fail');
                                                                                        ?>
                                                                                    </span>
                                                                                    </span>
                                                                    <?php
                                                                                                        }

                                                                                                        if ($exam_pass_status) {

                                                                                                            echo $this->lang->line('division') . " : " . findExamDivision($marks_division, $exam_percentage);
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                    ?>
                                                                            </h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-2 col-lg-2 col-md-2 border-right">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('grand_total'); ?> : <span class="description-text"><?php echo $exam_grand_total; ?></span></h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-3 col-lg-3 col-md-3 border-right">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('total_obtain_marks'); ?> : <span class="description-text"><?php echo $exam_get_total; ?></span></h5>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                } elseif ($exam_value->exam_type == "gpa") {
                                                                ?>

                                                                    <div class="col-lg-3 col-md-3">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('credit_hours'); ?> : <span class="description-text"><?php echo $exam_credit_hour; ?></span></h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-3 col-md-3 pull ">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('rank') ?> : <span class="description-text">
                                                                                    <?php echo $exam_value->rank; ?>

                                                                                </span>
                                                                            </h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-5 col-md-3">
                                                                        <div class="description-block">
                                                                            <h5 class="description-header"><?php echo $this->lang->line('quality_points'); ?> : <span class="description-text">
                                                                                    <?php
                                                                                    if ($exam_credit_hour <= 0) {
                                                                                        echo "--";
                                                                                    } else {
                                                                                        $exam_grade_percentage = ($exam_get_total * 100) / $exam_grand_total;
                                                                                        echo $exam_quality_points . "/" . $exam_credit_hour . '=' . two_digit_float($exam_quality_points / $exam_credit_hour) . " [" . findExamGrade($exam_grade, $exam_value->exam_type, $exam_grade_percentage) . "]";
                                                                                    }

                                                                                    ?>
                                                                                </span>

                                                                                <?php
                                                                                ?>
                                                                            </h5>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                    </div>
                                <?php
                                                }
                                            } elseif ($exam_value->exam_result['exam_connection'] == 1) {

                                                $exam_connected_exam = ($exam_value->exam_result['exam_result']['exam_result_' . $exam_value->exam_group_class_batch_exam_id]);

                                                if (!empty($exam_connected_exam)) {
                                                    $exam_quality_points = 0;
                                                    $exam_total_points   = 0;
                                                    $exam_credit_hour    = 0;
                                                    $exam_grand_total    = 0;
                                                    $exam_get_total      = 0;
                                                    $exam_pass_status    = 1;
                                                    $exam_absent_status  = 0;
                                                    $total_exams         = 0;
                                ?>
                                    <table class="table table-striped ">
                                        <thead>
                                            <th><?php echo $this->lang->line('subject') ?></th>
                                            <?php
                                                    if ($exam_value->exam_type == "gpa") {
                                            ?>
                                                <th><?php echo $this->lang->line('grade_point'); ?> </th>
                                                <th><?php echo $this->lang->line('credit_hours'); ?></th>
                                                <th><?php echo $this->lang->line('quality_points'); ?></th>
                                            <?php
                                                    }
                                            ?>
                                            <?php
                                                    if ($exam_value->exam_type != "gpa") {
                                            ?>
                                                <th><?php echo $this->lang->line('max_marks'); ?></th>
                                                <?php
                                                        if ($exam_value->exam_type != "average_passing") {
                                                ?>

                                                    <th><?php echo $this->lang->line('min_marks') ?></th>
                                                <?php
                                                        }
                                                ?>
                                                <th><?php echo $this->lang->line('marks_obtained') ?> </th>
                                            <?php
                                                    }
                                            ?>
                                            <?php
                                                    if ($exam_value->exam_type == "coll_grade_system" || $exam_value->exam_type == "school_grade_system") {
                                            ?>
                                                <th><?php echo $this->lang->line('grade'); ?></th>
                                            <?php
                                                    }

                                                    if ($exam_value->exam_type == "basic_system") {
                                            ?>
                                                <th>
                                                    <?php echo $this->lang->line('result'); ?>
                                                </th>
                                            <?php
                                                    }
                                            ?>
                                            <th><?php echo $this->lang->line('note') ?></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                                    if (!empty($exam_connected_exam)) {
                                                        $total_exams = 1;
                                                        foreach ($exam_connected_exam as $exam_result_key => $exam_result_value) {
                                                            $exam_grand_total = $exam_grand_total + $exam_result_value->max_marks;
                                                            $exam_get_total   = $exam_get_total + $exam_result_value->get_marks;
                                                            $percentage_grade = ($exam_result_value->get_marks * 100) / $exam_result_value->max_marks;
                                                            if ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                                                $exam_pass_status = 0;
                                                            }
                                            ?>
                                                    <tr>
                                                        <td><?php echo ($exam_result_value->name); ?> <?php if ($exam_result_value->code) {
                                                                                                            echo ' (' . $exam_result_value->code . ')';
                                                                                                        } ?></td>
                                                        <?php
                                                            if ($exam_value->exam_type != "gpa") {
                                                        ?>
                                                            <td><?php echo ($exam_result_value->max_marks); ?></td>
                                                            <?php

                                                                if ($exam_value->exam_type != "average_passing") {
                                                            ?>
                                                                <td><?php echo ($exam_result_value->min_marks); ?></td>
                                                            <?php
                                                                }
                                                            ?>

                                                            <td>
                                                                <?php
                                                                echo $exam_result_value->get_marks;

                                                                if ($exam_result_value->attendence == "absent") {
                                                                    $exam_absent_status = 1;
                                                                    echo "&nbsp; " . $this->lang->line('abs');
                                                                }
                                                                ?>
                                                            </td>
                                                        <?php
                                                            } elseif ($exam_value->exam_type == "gpa") {
                                                        ?>
                                                            <td style="">
                                                                <?php
                                                                $percentage_grade  = ($exam_result_value->get_marks * 100) / $exam_result_value->max_marks;
                                                                $point             = findGradePoints($exam_grade, $exam_value->exam_type, $percentage_grade);
                                                                $exam_total_points = $exam_total_points + $point;
                                                                echo two_digit_float($point);
                                                                ?>
                                                            </td>
                                                            <td> <?php
                                                                    echo $exam_result_value->credit_hours;
                                                                    $exam_credit_hour = $exam_credit_hour + $exam_result_value->credit_hours;
                                                                    ?></td>
                                                            <td><?php
                                                                echo two_digit_float($exam_result_value->credit_hours * $point);
                                                                $exam_quality_points = $exam_quality_points + ($exam_result_value->credit_hours * $point);
                                                                ?></td>
                                                        <?php
                                                            }
                                                        ?>
                                                        <?php
                                                            if ($exam_value->exam_type == "coll_grade_system" || $exam_value->exam_type == "school_grade_system") {
                                                        ?>
                                                            <td><?php echo findExamGrade($exam_grade, $exam_value->exam_type, $percentage_grade); ?></td>
                                                        <?php
                                                            }
                                                            if ($exam_value->exam_type == "basic_system") {
                                                        ?>
                                                            <td>
                                                                <?php
                                                                if ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                                                ?>
                                                                    <label class="label label-danger">
                                                                        <?php echo $this->lang->line('fail') ?>
                                                                        <label>
                                                                        <?php
                                                                    } else {
                                                                        ?>
                                                                            <label class="label label-success"><?php echo $this->lang->line('pass') ?>
                                                                                <label>
                                                                                <?php
                                                                            }
                                                                                ?>
                                                            </td>
                                                        <?php
                                                            }
                                                        ?>
                                                        <td><?php echo ($exam_result_value->note); ?></td>
                                                    </tr>
                                            <?php
                                                        }
                                                    }
                                            ?>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="bgtgray">
                                                <?php
                                                    if ($exam_value->exam_type != "gpa") {
                                                ?>
                                                    <div class="col-sm-3 col-lg-2 col-md-3 pull no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header"> <?php echo $this->lang->line('percentage') ?> : <span class="description-text">
                                                                    <?php
                                                                    $exam_percentage = ($exam_get_total * 100) / $exam_grand_total;
                                                                    echo two_digit_float($exam_percentage);
                                                                    ?>
                                                                </span> </h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 col-md-3 pull ">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?php echo $this->lang->line('rank') ?> : <span class="description-text">
                                                                    <?php echo $exam_value->rank; ?>

                                                                </span>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 col-lg-3 col-md-3 pull border-right no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?php echo $this->lang->line('result'); ?> :<span class="description-text">
                                                                    <?php
                                                                    if ($total_exams) {

                                                                        if ($exam_value->exam_type == "average_passing") {
                                                                            if ($exam_value->passing_percentage <= $exam_percentage) {

                                                                    ?>
                                                                                <span class='label bg-green ml-rtl' style="margin-right: 5px;">
                                                                                    <?php
                                                                                    echo $this->lang->line('pass');
                                                                                    ?>
                                                                                </span> <?php
                                                                                    } else {
                                                                                        ?>
                                                                                <span class='label label-danger'>
                                                                                    <?php
                                                                                        echo $this->lang->line('fail');
                                                                                    ?>
                                                                                </span>

                                                                            <?php
                                                                                    }
                                                                                } else {
                                                                                    if ($exam_absent_status) {
                                                                            ?>
                                                                                <span class='label label-danger ml-rtl' style="margin-right: 5px;">
                                                                                    <?php
                                                                                        echo $this->lang->line('fail');
                                                                                    ?>
                                                                                </span>
                                                                                <?php
                                                                                    } else {

                                                                                        if ($exam_pass_status) {

                                                                                ?>
                                                                                    <span class='label bg-green ml-rtl' style="margin-right: 5px;">
                                                                                        <?php
                                                                                            echo $this->lang->line('pass');
                                                                                        ?>
                                                                                    </span>
                                                                                <?php
                                                                                        } else {
                                                                                ?>
                                                                                    <span class='label label-danger ml-rtl' style="margin-right: 5px;">
                                                                                        <?php
                                                                                            echo $this->lang->line('fail');
                                                                                        ?>
                                                                                    </span>
                                                                    <?php
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                    ?>
                                                                    <?php
                                                                    if ($total_exams) {

                                                                        if ($exam_pass_status) {
                                                                            echo $this->lang->line('division') . " : " . findExamDivision($marks_division, $exam_percentage);
                                                                        }
                                                                    }
                                                                    ?>
                                                                </span></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2 col-lg-2 col-md-2 border-right no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?php echo $this->lang->line('grand_total'); ?> : <span class="description-text"><?php echo $exam_grand_total; ?></span></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 border-right no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?php echo $this->lang->line('total_obtain_marks'); ?> : <span class="description-text"><?php echo $exam_get_total; ?></span></h5>
                                                        </div>
                                                    </div>
                                                <?php
                                                    } elseif ($exam_value->exam_type == "gpa") {
                                                ?>
                                                    <div class="col-sm-3 col-lg-3 col-md-3 pull no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header">
                                                                <?php echo $this->lang->line('credit_hours'); ?> :
                                                                <span class="description-text"><?php echo $exam_credit_hour; ?>
                                                                </span>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 pull ">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?php echo $this->lang->line('rank') ?> : <span class="description-text">
                                                                    <?php echo $exam_value->rank; ?>

                                                                </span>
                                                            </h5>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-5 col-md-3 pull no-print">
                                                        <div class="description-block">
                                                            <h5 class="description-header">
                                                                <?php echo $this->lang->line('quality_points'); ?> :<span class="description-text"><?php
                                                                                                                                                    if ($exam_credit_hour <= 0) {
                                                                                                                                                        echo "--";
                                                                                                                                                    } else {
                                                                                                                                                        $exam_grade_percentage = ($exam_get_total * 100) / $exam_grand_total;
                                                                                                                                                        echo $exam_quality_points . "/" . $exam_credit_hour . '=' . two_digit_float($exam_quality_points / $exam_credit_hour) . " [" . findExamGrade($exam_grade, $exam_value->exam_type, $exam_grade_percentage) . "]";
                                                                                                                                                    }
                                                                                                                                                    ?>
                                                                </span>
                                                            </h5>
                                                        </div>
                                                    </div>
                                            <?php
                                                    }
                                                }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                        </div>
                        <div class="tshadow mb25">
                            <h4 class="pagetitleh">
                                <?php echo $this->lang->line('consolidated_result'); ?>
                            </h4>
                            <?php
                                                $consolidate_exam_result            = false;
                                                $consolidate_exam_result_percentage = false;

                                                if ($exam_value->exam_type == "coll_grade_system" || $exam_value->exam_type == "school_grade_system") {
                            ?>
                                <table class="table table-striped ">
                                    <thead>
                                        <th><?php echo $this->lang->line('exam') ?></th>
                                        <?php
                                                    foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {
                                        ?>
                                            <th>
                                                <?php echo $each_exam_value->exam; ?>
                                            </th>
                                        <?php
                                                    }
                                        ?>
                                        <th><?php echo $this->lang->line('consolidate') ?></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $this->lang->line('marks_obtained'); ?></td>
                                            <?php
                                                    $consolidate_get_total = 0;
                                                    $consolidate_max_total = 0;
                                                    $consolidate_get_total_percentage = 0;
                                                    if (!empty($exam_value->exam_result['exams'])) {
                                                        $consolidate_exam_result = "pass";
                                                        foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {
                                            ?>
                                                    <td>
                                                        <?php
                                                            $consolidate_each = getCalculatedExam($exam_value->exam_result['exam_result'], $each_exam_value->id);

                                                            $exam_get_percentage = ($consolidate_each->get_marks * 100) / $consolidate_each->max_marks;

                                                            $consolidate_get_percentage_mark = getConsolidateRatio($exam_value->exam_result['exam_connection_list'], $each_exam_value->id, $consolidate_each->get_marks, $exam_get_percentage);
                                                            if ($consolidate_each->exam_status == "fail") {
                                                                $consolidate_exam_result = "fail";
                                                            }

                                                            echo $consolidate_get_percentage_mark['exam_consolidate_marks'] . " (" . $consolidate_get_percentage_mark['exam_weightage'] . "%)";

                                                            $consolidate_get_total_percentage += ($consolidate_get_percentage_mark['exam_consolidate_percentage']);

                                                            $consolidate_get_total = $consolidate_get_total + ($consolidate_get_percentage_mark['exam_consolidate_marks']);
                                                            $consolidate_max_total = $consolidate_max_total + ($consolidate_each->max_marks);
                                                        ?>
                                                    </td>
                                            <?php
                                                        }
                                                    }
                                            ?>
                                            <td>
                                                <?php
                                                    if ($consolidate_max_total > 0) {

                                                        $consolidate_percentage_grade = ($consolidate_get_total * 100) / $consolidate_max_total;
                                                    } else {
                                                        $consolidate_percentage_grade = 0;
                                                    }

                                                    echo two_digit_float($consolidate_get_total_percentage)  . " [" . findExamGrade($exam_grade, $exam_value->exam_type, $consolidate_get_total_percentage) . "]";
                                                    $consolidate_exam_result_percentage = $consolidate_get_total_percentage;
                                                ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php
                                                } elseif ($exam_value->exam_type == "basic_system" || $exam_value->exam_type == "average_passing") {
                            ?>
                                <table class="table table-striped ">
                                    <thead>
                                        <th><?php echo $this->lang->line('exam'); ?></th>
                                        <?php
                                                    foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {
                                        ?>
                                            <th>
                                                <?php echo $each_exam_value->exam; ?>
                                            </th>
                                        <?php
                                                    }
                                        ?>
                                        <th><?php echo $this->lang->line('consolidate'); ?></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $this->lang->line('marks_obtained') ?></td>
                                            <?php
                                                    $consolidate_get_total = 0;
                                                    $consolidate_get_total_percentage = 0;
                                                    $consolidate_max_total = 0;
                                                    if (!empty($exam_value->exam_result['exams'])) {
                                                        $consolidate_exam_result = "pass";
                                                        foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {

                                            ?>
                                                    <td>
                                                        <?php
                                                            $consolidate_each                = getCalculatedExam($exam_value->exam_result['exam_result'], $each_exam_value->id);

                                                            if ($consolidate_each->max_marks > 0) {
                                                                $exam_get_percentage = ($consolidate_each->get_marks * 100) / $consolidate_each->max_marks;
                                                            } else {
                                                                $exam_get_percentage = 0;
                                                            }

                                                            $consolidate_get_percentage_mark = getConsolidateRatio($exam_value->exam_result['exam_connection_list'], $each_exam_value->id, $consolidate_each->get_marks, $exam_get_percentage);
                                                            if ($exam_value->exam_type == "average_passing") {
                                                                if ($each_exam_value->passing_percentage > $exam_get_percentage) {
                                                                    $consolidate_exam_result = "fail";
                                                                }
                                                            } elseif ($consolidate_each->exam_status == "fail") {
                                                                $consolidate_exam_result = "fail";
                                                            }
                                                            echo $consolidate_get_percentage_mark['exam_consolidate_marks'] . " (" . $consolidate_get_percentage_mark['exam_weightage'] . "%)";
                                                            $consolidate_get_total += ($consolidate_get_percentage_mark['exam_consolidate_marks']);
                                                            $consolidate_get_total_percentage += ($consolidate_get_percentage_mark['exam_consolidate_percentage']);
                                                            $consolidate_max_total = $consolidate_max_total + ($consolidate_each->max_marks);
                                                        ?>
                                                    </td>
                                            <?php
                                                        }
                                                    }
                                            ?>
                                            <td><?php
                                                    if ($consolidate_max_total > 0) {
                                                        $consolidate_percentage_grade = ($consolidate_get_total * 100) / $consolidate_max_total;
                                                    } else {
                                                        $consolidate_percentage_grade = 0;
                                                    }

                                                    echo two_digit_float($consolidate_get_total_percentage) . " [" . findExamGrade($exam_grade, $exam_value->exam_type, $consolidate_get_total_percentage) . "]";
                                                    $consolidate_exam_result_percentage = $consolidate_get_total_percentage;
                                                ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php
                                                } elseif ($exam_value->exam_type == "gpa") {
                            ?>
                                <table class="table table-striped ">
                                    <thead>
                                        <th><?php echo $this->lang->line('exam') ?></th>
                                        <?php
                                                    foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {
                                        ?>
                                            <th>
                                                <?php echo $each_exam_value->exam; ?>
                                            </th>
                                        <?php
                                                    }
                                        ?>
                                        <th><?php echo $this->lang->line('consolidate'); ?></th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $this->lang->line('marks_obtained'); ?></td>
                                            <?php
                                                    $consolidate_get_total      = 0;
                                                    $consolidate_subjects_total = 0;

                                                    foreach ($exam_value->exam_result['exams'] as $each_exam_key => $each_exam_value) {

                                            ?>
                                                <td>
                                                    <?php
                                                        $consolidate_each        = getCalculatedExamGradePoints($exam_value->exam_result['exam_result'], $each_exam_value->id, $exam_grade, $exam_value->exam_type);

                                                        $consolidate_exam_result = ($consolidate_each->total_points / $consolidate_each->total_exams);
                                                        $consolidate_each->total_points . "/" . $consolidate_each->total_exams . "=" . two_digit_float($consolidate_exam_result);

                                                        $exam_get_percentage = ($consolidate_each->total_get_marks * 100) / $consolidate_each->total_max_marks;


                                                        $consolidate_get_percentage_mark = getConsolidateRatio($exam_value->exam_result['exam_connection_list'], $each_exam_value->id, $consolidate_exam_result, 100);
                                                        echo two_digit_float($consolidate_get_percentage_mark['exam_consolidate_marks']) . " (" . $consolidate_get_percentage_mark['exam_weightage'] . "%)";
                                                        $consolidate_get_total           = $consolidate_get_total + ($consolidate_get_percentage_mark['exam_consolidate_marks']);
                                                        $consolidate_subjects_total      = $consolidate_subjects_total + $consolidate_each->total_exams;
                                                    ?>
                                                </td>
                                            <?php
                                                    }
                                            ?>
                                            <td>

                                                <?php

                                                    $consolidate_percentage_grade = ($consolidate_get_total * 100) / 10;
                                                    $consolidate_exam_result_percentage = $consolidate_percentage_grade;
                                                    echo (two_digit_float($consolidate_get_total)) . " [" . findExamGrade($exam_grade, $exam_value->exam_type, $consolidate_percentage_grade) . "]";
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php
                                                }

                                                if ($consolidate_exam_result) {
                            ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="bgtgray">

                                            <?php

                                                    if ($exam_value->exam_type != "gpa") {
                                            ?>

                                                <div class="col-sm-3 pull no-print">
                                                    <div class="description-block">
                                                        <h5 class="description-header"><?php echo $this->lang->line('result') ?> :
                                                            <span class="description-text">
                                                                <?php

                                                                if ($consolidate_exam_result == "pass") {
                                                                ?>
                                                                    <span class='label label-success ml-rtl' style="margin-right: 5px;">
                                                                        <?php
                                                                        echo $this->lang->line('pass');
                                                                        ?>
                                                                    </span>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <span class='label label-danger ml-rtl' style="margin-right: 5px;">
                                                                        <?php
                                                                        echo $this->lang->line('fail');
                                                                        ?>
                                                                    </span>
                                                                <?php
                                                                }
                                                                ?>
                                                            </span>
                                                        </h5>
                                                    </div>
                                                </div>
                                            <?php

                                                    }
                                            ?>
                                            <?php
                                                    if ($consolidate_exam_result_percentage) {
                                            ?>
                                                <div class="col-sm-3 border-right no-print">
                                                    <div class="description-block">
                                                        <h5 class="description-header"><?php echo $this->lang->line('division'); ?> :<span class="description-text">
                                                                <?php
                                                                echo findExamDivision($marks_division, $consolidate_exam_result_percentage);

                                                                ?>
                                                            </span></h5>
                                                    </div>
                                                </div>
                                        <?php
                                                    }
                                                }
                                        ?>
                                        </div>
                                    </div>
                                </div>
                        </div>
            <?php
                                            }
                                        }
                                    }
                                } else {
            ?>
        <?php
                                }

        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="myTransportFeesModal" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title text-center transport_fees_title"></h4>
            </div>
            <div class="modal-body pb0">
                <form id="form1" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                    <input type="hidden" class="form-control" id="transport_student_session_id" value="0" readonly="readonly" />
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div id='upload_documents_hide_show'>
                        <input type="hidden" name="student_id" value="<?php echo $student_doc_id; ?>" id="student_id">
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('title'); ?> <small class="req">*</small></label>
                            <input id="first_title" name="first_title" placeholder="" type="text" class="form-control" value="<?php echo set_value('first_title'); ?>" />
                            <span class="text-danger"><?php echo form_error('first_title'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('documents'); ?> <small class="req">*</small></label>
                            <div class="">
                                <input name="first_doc[]" placeholder="" type="file" class="form-control filestyle" data-height="40" value="<?php echo set_value('first_doc'); ?>" multiple/>
                                <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer" style="clear:both">
                <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>

            </div>
            </form>
        </div>
    </div>
</div>


<!-- google drive modal -->
 <div class="modal fade" id="google_drive_model" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title text-center"><?php echo $this->lang->line('upload_through_google_drive'); ?></h4>
            </div>
            <div class="">
                <div class="">
                    <div class="">
                        <form role="form" class="validateTitle" method="post" action="<?php echo base_url('user/user/searchvalidationtitle');?>">
                            <div class="modal-body">
                                <div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('title'); ?><small class="req"> *</small></label>
                                        <input id="image_first_title" name="image_first_title" placeholder="" type="text" class="form-control" value="<?php echo set_value('image_first_title'); ?>" />
                                        <span class="text-danger error_image_first_title"><?php echo form_error('image_first_title'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer" style="clear:both">
                                <button type="submit" id="authorize_button"  class="btn btn-xs btn-primary "> <i class="fa fa-upload"></i> <?php 
                            echo $this->lang->line('upload_documents'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<!-- google drive modal -->




<div class="modal fade" id="myTimelineModal" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title timeline_title"></h4>
            </div>
            <div class="">
                <div class="">
                    <form id="timelineform" name="timelineform" method="post" enctype="multipart/form-data">
                        <div class="modal-body pb0">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div id='timeline_hide_show' class="row">
                                <input type="hidden" name="student_id" value="<?php echo $student["id"] ?>" id="student_id">

                                <div class=" col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                                        <input id="timeline_title" name="timeline_title" placeholder="" type="text" class="form-control" />
                                        <span class="text-danger"><?php echo form_error('timeline_title'); ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('date'); ?></label><small class="req"> *</small>
                                        <input id="timeline_date" value="<?php echo set_value('timeline_date', date($this->customlib->getSchoolDateFormat())); ?>" name="timeline_date" placeholder="" type="text" class="form-control date" />
                                        <span class="text-danger"><?php echo form_error('timeline_date'); ?></span>
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                        <textarea id="timeline_desc" name="timeline_desc" placeholder="" class="form-control"></textarea>
                                        <span class="text-danger"><?php echo form_error('description'); ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('attach_document'); ?></label>
                                        <div class=""><input id="timeline_doc_id" name="timeline_doc" placeholder="" type="file" class="filestyle form-control" data-height="40" value="<?php echo set_value('timeline_doc'); ?>" />
                                            <span class="text-danger"><?php echo form_error('timeline_doc'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>

                            <button type="reset" id="reset" style="display: none" class="btn btn-info pull-right"><?php echo $this->lang->line('reset'); ?></button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edittimelineModal" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title"><?php echo $this->lang->line('edit_timeline'); ?></h4>
            </div>
            <form id="edittimelineform" name="timelineform" method="post" action="<?php echo base_url() . "admin/timeline/add_staff_timeline" ?>" enctype="multipart/form-data">
                <div class="modal-body pb0">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div id="edittimelinedata"></div>
                </div>
                <div class="modal-footer" style="clear:both">
                    <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                    <button type="reset" id="reset" style="display: none" class="btn btn-info pull-right"><?php echo $this->lang->line('reset'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!------- Behaviour Report Comments Modal--------->
<div class="modal fade" id="commentmodel" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header">
                <div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="box-title"><?php echo $this->lang->line('comments'); ?></h4>
                </div>
            </div>

            <div class="">
                <div class="modal-body pt0 pb0 relative bg-e6">
                    <form id="formadd" method="post" class="ptt10 mb10 place-italic" enctype="multipart/form-data">
					<?php

                            if ($this->module_lib->hasModule('behaviour_records') && $this->module_lib->hasActive('behaviour_records') && $this->studentmodule_lib->hasActive('behaviour_records')) {
                            ?>
							
							
                        <?php
                        $comment_option    = json_decode($behavioursetting['comment_option']);
                        if (isset($comment_option)) {
                            if (in_array($role, $comment_option)) {   ?>
                                <input type="hidden" name="student_incident_id" id="student_incident_id">
                                <div class="clearfix">
                                    <div class="d-flex justify-content-between gap-1">
                                        <textarea name="comment" cols="10" rows="2" placeholder="<?php echo $this->lang->line('type_your_comment'); ?>" class="form-control resize-auto border-radius-1 max-height-40"></textarea>

                                        <button type="submit" class="btn btn-primary overflow-inherit max-height-40" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('send') ?></button>
                                    </div>

                                </div>
                        <?php }
                        } ?>
						
							<?php } ?>
                    </form>

                    <div class="scroll-area-inside">
                        <ul class="user-progress">
                            <div id="messagedetails"></div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function findGradePoints($exam_grade, $exam_type, $percentage)
{
    foreach ($exam_grade as $exam_grade_key => $exam_grade_value) {
        if ($exam_grade_value['exam_key'] == $exam_type) {

            if (!empty($exam_grade_value['exam_grade_values'])) {
                foreach ($exam_grade_value['exam_grade_values'] as $grade_key => $grade_value) {
                    if ($grade_value->mark_from >= $percentage && $grade_value->mark_upto <= $percentage) {
                        return $grade_value->point;
                    }
                }
            }
        }
    }
    return 0;
}

function findExamGrade($exam_grade, $exam_type, $percentage)
{
    foreach ($exam_grade as $exam_grade_key => $exam_grade_value) {
        if ($exam_grade_value['exam_key'] == $exam_type) {

            if (!empty($exam_grade_value['exam_grade_values'])) {
                foreach ($exam_grade_value['exam_grade_values'] as $grade_key => $grade_value) {
                    if ($grade_value->mark_from >= $percentage && $grade_value->mark_upto <= $percentage) {
                        return $grade_value->name;
                    }
                }
            }
        }
    }
    return "";
}

function findExamDivision($marks_division, $percentage)
{
    if (!empty($marks_division)) {
        foreach ($marks_division as $division_key => $division_value) {
            if ($division_value->percentage_from >= $percentage && $division_value->percentage_to <= $percentage) {
                return $division_value->name;
            }
        }
    }

    return "";
}


function getConsolidateRatio($exam_connection_list, $examid, $get_marks, $exam_get_percentage)
{
    if (!empty($exam_connection_list)) {
        foreach ($exam_connection_list as $exam_connection_key => $exam_connection_value) {

            if ($exam_connection_value->exam_group_class_batch_exams_id == $examid) {
                return [
                    'exam_weightage' => $exam_connection_value->exam_weightage,
                    'exam_consolidate_marks' => ($get_marks * $exam_connection_value->exam_weightage) / 100,
                    'exam_consolidate_percentage' => ($exam_get_percentage * $exam_connection_value->exam_weightage) / 100
                ];
            }
        }
    }
    return 0;
}

function getCalculatedExamGradePoints($array, $exam_id, $exam_grade, $exam_type)
{
    $object              = new stdClass();
    $return_total_points = 0;
    $return_total_exams  = 0;
    $return_max_marks  = 0;
    $return_quality_point  = 0;
    $return_get_marks  = 0;
    $return_credit_hours  = 0;
    if (!empty($array)) {

        if (!empty($array['exam_result_' . $exam_id])) {

            foreach ($array['exam_result_' . $exam_id] as $exam_key => $exam_value) {
                $return_total_exams++;                 
                $percentage_grade    = ($exam_value->get_marks * 100) / $exam_value->max_marks;
                $point               = findGradePoints($exam_grade, $exam_type, $percentage_grade);
                $return_total_points = $return_total_points + $point;
                $return_quality_point += ($point * $exam_value->credit_hours);
                $return_credit_hours += $exam_value->credit_hours;
                $return_max_marks += $exam_value->max_marks;
                $return_get_marks += $exam_value->get_marks;
            }
        }
    }

    $object->total_max_marks = $return_max_marks;
    $object->total_get_marks = $return_get_marks;
    $object->total_points = $return_total_points;
    $object->total_exams  = $return_total_exams;
    $object->return_quality_point  = $return_quality_point;
    $object->return_credit_hours  = $return_credit_hours;

    return $object;
}

function getCalculatedExam($array, $exam_id)
{
    $object              = new stdClass();
    $return_max_marks    = 0;
    $return_get_marks    = 0;
    $return_credit_hours = 0;
    $return_exam_status  = false;
    if (!empty($array)) {
        $return_exam_status = 'pass';
        if (!empty($array['exam_result_' . $exam_id])) {
            foreach ($array['exam_result_' . $exam_id] as $exam_key => $exam_value) {

                if ($exam_value->get_marks < $exam_value->min_marks || $exam_value->attendence != "present") {
                    $return_exam_status = "fail";
                }

                $return_max_marks    = $return_max_marks + ($exam_value->max_marks);
                $return_get_marks    = $return_get_marks + ($exam_value->get_marks);
                $return_credit_hours = $return_credit_hours + ($exam_value->credit_hours);
            }
        }
    }
    $object->credit_hours = $return_credit_hours;
    $object->get_marks    = $return_get_marks;
    $object->max_marks    = $return_max_marks;
    $object->exam_status  = $return_exam_status;
    return $object;
}
?>
<script>
    $('.comments').click(function() {
        var student_incident_id = $(this).attr('data-record-id');
        $('#student_incident_id').val(student_incident_id);

        $('#commentmodel').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        getmessage(student_incident_id);
    })

    $("#formadd").on('submit', (function(e) {
        e.preventDefault();

        var student_incident_id = $('#student_incident_id').val();

        var $this = $(this).find("button[type=submit]:focus");
        $.ajax({
            url: "<?php echo site_url("user/studentincidentcomments/addmessage"); ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');

            },
            success: function(res) {
                if (res.status == "fail") {

                    var message = "";
                    $.each(res.error, function(index, value) {

                        message += value;
                    });
                    errorMsg(message);

                } else {
                    successMsg(res.message);
                    $('#formadd')[0].reset();
                    getmessage(student_incident_id);
                }
            },
            error: function(xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function() {
                $this.button('reset');
            }

        });
    }));

    function getmessage(student_incident_id) {
        $('#messagedetails').html('');
        $.ajax({
            url: "<?php echo site_url("user/studentincidentcomments/getmessage"); ?>",
            type: "POST",
            data: {
                student_incident_id: student_incident_id
            },
            dataType: 'json',
            success: function(res) {
                if (res.status == "success") {
                    $('#messagedetails').html(res.page);
                } else {
                    $('#messagedetails').html('');
                }
            }
        });
    }

    function delete_comment(id, student_incident_id) {
        if (confirm("<?php echo $this->lang->line('delete_confirm'); ?>") == true) {
            $.ajax({
                url: "<?php echo site_url("user/studentincidentcomments/delete_comment"); ?>",
                type: "POST",
                data: {
                    id: id
                },
                success: function(res) {
                    getmessage(student_incident_id);
                }
            });
        }
    }
</script>

<script type="text/javascript">
    /*--dropify--*/
    $(document).ready(function() {
        // Basic
        $('.filestyle').dropify();
    });
    /*--end dropify--*/
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.detail_popover').popover({
            placement: 'right',
            title: '',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function() {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });
    });

    $(document).ready(function() {
        $('table.display').DataTable();
    });
</script>
<script type="text/javascript">
    $(".myTransportFeeBtn").click(function() {
        $("span[id$='_error']").html("");
        $('#transport_amount').val("");
        $('#transport_amount_discount').val("0");
        $('#transport_amount_fine').val("0");
        var student_session_id = $(this).data("student-session-id");
        $('.transport_fees_title').html("<b><?php echo $this->lang->line('upload_documents'); ?></b>");
        $('#transport_student_session_id').val(student_session_id);
        $('#myTransportFeesModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });

    function printDiv(divid) {

        $('.bg-green').removeClass('label');
        $('.label-danger').removeClass('label');
        $('.label-success').removeClass('label');
        var divElements = document.getElementById(divid).innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
            "<html><head><title></title></head><body>" +
            divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;

        location.reload(true);
    }

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

    $("#myTimelineButton").click(function() {
        $('.timeline_title').html("<b><?php echo $this->lang->line('add_timeline'); ?></b>");
        $('#myTimelineModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    });

    $("#timelineform").on('submit', (function(e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("user/timeline/add") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');

            },
            success: function(data) {

                if (data.status == "fail") {

                    var message = "";
                    $.each(data.error, function(index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {

                    successMsg(data.message);
                    window.location.reload(true);
                }

            },
            error: function(e) {
                alert("<?php echo $this->lang->line('fail'); ?>");
                console.log(e);
            },
            complete: function() {
                $this.button('reset');
            }
        });
    }));

    $('.edit_timeline').click(function() {
        $('#edittimelineModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });

        var id = $(this).attr('data-id');
        $.ajax({
            url: "<?php echo site_url("user/timeline/getstudentsingletimeline") ?>",
            type: "POST",
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                $('#edittimelinedata').html(response.page);
            }

        });
    })

    $("#edittimelineform").on('submit', (function(e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("user/timeline/edit") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');
            },
            success: function(data) {

                if (data.status == "fail") {

                    var message = "";
                    $.each(data.error, function(index, value) {

                        message += value;
                    });
                    errorMsg(message);
                } else {

                    successMsg(data.message);
                    window.location.reload(true);
                }

            },
            error: function(e) {
                alert("<?php echo $this->lang->line('fail'); ?>");
            },
            complete: function() {
                $this.button('reset');
            }
        });

    }));

    function delete_timeline(id) {

        if (confirm('<?php echo $this->lang->line("delete_confirm") ?>')) {

            $.ajax({
                url: '<?php echo base_url(); ?>user/timeline/delete_timeline/',
                type: 'post',
                data: {
                    id: id
                },
                dataType: 'JSON',
                success: function(res) {
                    if (res.status == 'success') {
                        successMsg(res.message);
                        window.location.reload(true);
                    }

                },
                error: function() {
                    alert("<?php echo $this->lang->line('fail'); ?>");
                }
            });
        }
    }
</script>
<script>
    $("#form1").on('submit', (function(e) {
        e.preventDefault();

        var $this = $(this).find("button[type=submit]:focus");

        document.getElementById('submit').disabled = true;

        $.ajax({
            url: "<?php echo site_url("user/user/create_doc") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');

            },
            success: function(res) {

                if (res.status == "fail") {

                    var message = "";
                    $.each(res.error, function(index, value) {

                        message += value;
                    });
                    errorMsg(message);

                } else {

                    successMsg(res.message);

                    window.location.reload(true);
                }
            },
            error: function(xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function() {
                $this.button('reset');
                document.getElementById('submit').disabled = false;
            }

        });
    }));
</script>

<script type="text/javascript">

$(document).on('click','.download_pdf',function(){
  var admission_no = $(this).attr('data-admission_no');
  var student_name = $(this).attr('data-student_name');
  let $button_     = $(this);  
     $.ajax({
        type: 'POST',
        url: baseurl+'user/user/printpdfresume',
        data: {
        },         
        beforeSend: function() { 
           $button_.button('loading');    
        },
         xhr: function () {// Seems like the only way to get access to the xhr object
            var xhr = new XMLHttpRequest();
            xhr.responseType = 'blob'           
            return xhr;
        },
       success: function (data, jqXHR, response) {    
               var blob = new Blob([data], {type: 'application/pdf'});
               var link = document.createElement('a');
               link.href = window.URL.createObjectURL(blob);
               link.download =  student_name+'_'+admission_no;
               document.body.appendChild(link);
               link.click();
               document.body.removeChild(link);
               $button_.button('reset');
        },
        error: function(xhr) { // if error occured
            $button_.button('reset');
        },
        complete: function() {             
            $button_.button('reset');
        }
    });
});

</script>
<script type="text/javascript">
	$(document).on('click', '.print_student_details', function() {
    let $button_ = $(this);
    var admission_no = $(this).attr('data-admission_no');
	var student_name = $(this).attr('data-student_name');
    $.ajax({
        type: 'POST',
        url: baseurl + 'user/user/printStudentDetails',  // Assuming baseurl is defined elsewhere
        data: {},  // Add any data you need to send here
        beforeSend: function() {
            $button_.button('loading');  // Change button state to loading
        },
        xhr: function() {
            var xhr = new XMLHttpRequest();  // Fixed the typo here
            xhr.responseType = 'blob';  // Set response type to blob
            return xhr;
        },
        success: function(data, jqXHR, response) {
            // Create a Blob with the response data (PDF)
            var blob = new Blob([data], {type: 'application/pdf'});

            // Create an anchor element to trigger the file download
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = student_name + '_' + admission_no + '.pdf';  // Assumes student_name and admission_no are defined
            document.body.appendChild(link);  // Append to body to trigger download
            link.click();
            document.body.removeChild(link);  // Clean up by removing the link

            $button_.button('reset');  // Reset the button to its original state
        },
        error: function(xhr, status, error) {
            // If an error occurs, reset the button
            console.error("Error occurred:", status, error);  // You can log errors for debugging
            $button_.button('reset');
        },
        complete: function() {
            // Reset the button regardless of success or failure
            $button_.button('reset');
        }
    });
});
</script>



<?php
//-----------CBSE Exam start----------------------

function find_subject_assessment_exists($subject_assessments, $cbse_exam_timetable_id, $cbse_exam_assessment_type_id)
{

    if (!empty($subject_assessments)) {
        foreach ($subject_assessments as $key => $value) {
            if ($value->id == $cbse_exam_timetable_id) {
                if (!empty($value->subject_assessments)) {
                    foreach ($value->subject_assessments as $askey => $asvalue) {
                        if ($asvalue->cbse_exam_timetable_id == $cbse_exam_timetable_id  && $asvalue->cbse_exam_assessment_type_id == $cbse_exam_assessment_type_id) {
                            return true;
                            break;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function getGrade($grade_array, $Percentage)
{

    if (!empty($grade_array)) {
        foreach ($grade_array as $grade_key => $grade_value) {

            if ($grade_value->minimum_percentage <= $Percentage) {
                return $grade_value->name;
                break;
            } elseif (($grade_value->minimum_percentage >= $Percentage && $grade_value->maximum_percentage <= $Percentage)) {

                return $grade_value->name;
                break;
            }
        }
    }
    return "-";
}

function findAssessmentValue($find_subject_id, $find_cbse_exam_assessment_type_id, $student_value)
{

 
    $return_array = [
        'maximum_marks' => "",
        'marks' => "",
        'note' => "",
        'is_absent' => "",
    ];


    if (property_exists($student_value,'subjects')) {

        if (array_key_exists($find_subject_id, $student_value->exam_data['subjects'])) {

            $result_array = ($student_value->exam_data['subjects'][$find_subject_id]['exam_assessments'][$find_cbse_exam_assessment_type_id]);


            $return_array = [
                'maximum_marks' => $result_array['maximum_marks'],
                'marks' => is_null($result_array['marks']) ? "N/A" : $result_array['marks'],
                'note' => $result_array['note'],
                'is_absent' => $result_array['is_absent'],
            ];
        }
    }


    return $return_array;
}


//-----------CBSE Exam End----------------------
?>

<script>
   $(document).on('submit', '.validateTitle', function(e) {
           e.preventDefault(); // avoid to execute the actual submit of the form.
            var $this = $("button[type=submit][clicked=true]");
            var form = $(this);
            var url = form.attr('action');
            var form_data = form.serializeArray();

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'JSON',
                data: form_data, 
                success: function(response) { // your success handler
                    if (!response.status) {
                        $.each(response.error, function(key, value) {
                            $('.error_' + key).html(value);
                        });
                    } else {
                       handleAuthClick();
                    }
                },
                error: function() { // your error handler
                },
                complete: function() {
                }
            });
        });
</script>

<!--======================ADD GOOGLE DRIVE============================-->
<script type="text/javascript">
  /* exported gapiLoaded */
  /* exported gisLoaded */
  /* exported handleAuthClick */
  /* exported handleSignoutClick */

  // Authorization scopes required by the API; multiple scopes can be
  // included, separated by spaces.
  //const SCOPES = 'https://www.googleapis.com/auth/drive.metadata.readonly';
  const SCOPES = 'https://www.googleapis.com/auth/drive.file';

  // TODO(developer): Replace with your client ID and API key from https://console.cloud.google.com/.
  //webfeb credentials testing
   const CLIENT_ID = '<?php echo $googledrivepicketsetting['client_id']; ?>';
   const API_KEY = '<?php echo $googledrivepicketsetting['api_key']; ?>';

  // TODO(developer): Replace with your project number from https://console.cloud.google.com/.
  const APP_ID = '<?php echo $googledrivepicketsetting['project_number']; ?>';

  let tokenClient;
  let accessToken = null;
  let pickerInited = false;
  let gisInited = false;

  document.getElementById('authorize_button').style.visibility = 'hidden';

  /**
   * Callback after api.js is loaded.
   */
  function gapiLoaded() {
    gapi.load('client:picker', initializePicker);
  }

  /**
   * Callback after the API client is loaded. Loads the
   * discovery doc to initialize the API.
   */
  async function initializePicker() {
    await gapi.client.load('https://www.googleapis.com/discovery/v1/apis/drive/v3/rest');
    pickerInited = true;
    maybeEnableButtons();
  }

  /**
   * Callback after Google Identity Services are loaded.
   */
  function gisLoaded() {
    tokenClient = google.accounts.oauth2.initTokenClient({
      client_id: CLIENT_ID,
      scope: SCOPES,
      callback: '', // defined later
    });
    gisInited = true;
    maybeEnableButtons();
  }

  /**
   * Enables user interaction after all libraries are loaded.
   */
  function maybeEnableButtons() {
    if (pickerInited && gisInited) {
      document.getElementById('authorize_button').style.visibility = 'visible';
    }
  }

  /**
   *  Sign in the user upon button click.
   */
  function handleAuthClick() {
    $("#google_drive_model").modal("hide");//added by webfeb

    tokenClient.callback = async (response) => {
      if (response.error !== undefined) {
        throw (response);
      }
      accessToken = response.access_token;
      document.getElementById('authorize_button').innerText = 'Refresh';
      await createPicker();
    };

    if (accessToken === null) {
      // Prompt the user to select a Google Account and ask for consent to share their data
      // when establishing a new session.
      tokenClient.requestAccessToken({prompt: 'consent'});
    } else {
      // Skip display of account chooser and consent dialog for an existing session.
      tokenClient.requestAccessToken({prompt: ''});
    }
    
    console.log(tokenClient);
    
  }

  /**
   *  Create and render a Google Picker object for searching images.
   */
  function createPicker() {
    const view = new google.picker.View(google.picker.ViewId.DOCS);
    view.setMimeTypes('image/png,image/jpeg,image/jpg');
    const picker = new google.picker.PickerBuilder()
        .enableFeature(google.picker.Feature.NAV_HIDDEN)
        .enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
        .setDeveloperKey(API_KEY)
        .setAppId(APP_ID)
        .setOAuthToken(accessToken)
        .addView(view)
        .addView(new google.picker.DocsUploadView())
        .setCallback(pickerCallback)
        .build();
    picker.setVisible(true);

     console.log("accessToken-> "+accessToken);
  }

  /**
   * Displays the file details of the user's selection.
   * @param {object} data - Contains the user selection from the Google Picker.
   */
  async function pickerCallback(data) {

    if (data.action === google.picker.Action.PICKED) {
      let text = `Google Picker response: \n${JSON.stringify(data, null, 2)}\n`;
      const document = data[google.picker.Response.DOCUMENTS][0];
      const fileId = document[google.picker.Document.ID];
      //console.log(fileId);
      const res = await gapi.client.drive.files.get({
        'fileId': fileId,
        'fields': '*',
      });
      text += `Drive API response for first document: \n${JSON.stringify(res.result, null, 2)}\n`;
      //window.document.getElementById('content').innerText = text;//comment by webfeb
      //================store image=================
        storefile(data);
      //================store image=================
    }
  }
</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>

<script>
function opengoogledrivemodel(){
    $("#image_first_title").val("");  
    $("#google_drive_model").modal("show");
}

function storefile(data){
    var first_title=$("#image_first_title").val();
    $(".laoder").removeClass("hide");
    $.ajax({
        url: '<?php echo base_url(); ?>'+'user/user/save_image',
        // async:true,
        type: 'POST',
		dataType: "JSON",
        data:{data:data,accessToken:accessToken,first_title:first_title},
        beforeSend: function () {
            $(".laoder").removeClass("hide");   
        },

    
        success: function (res) {

            if (res.status == "fail") {

                let message = "";
                $.each(res.error, function (i, val) {
                    message += val;
                });

                errorMsg(message);

            } else {
                successMsg(res.message);
                location.reload();
            }
        },

 
        error: function (xhr, status, error) {
            errorMsg("Something went wrong. Please try again.");
        },

       
        complete: function () {
            $(".laoder").addClass("hide");   
        }
    });
}
</script>

<!--======================ADD GOOGLE DRIVE============================-->

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

