<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('generate_certificate'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Comprehensive Student Information Panel -->
                <?php if (!empty($student)) {
                    $student_fullname = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
                    if (empty($student_fullname)) {
                        $student_fullname = $student->name ?? 'Student';
                    }

                    if (!empty($student->image)) {
                        $student_photo = $this->media_storage->getImageURL($student->image);
                    } else {
                        if (($student->gender ?? '') == 'Female') {
                            $student_photo = base_url() . "uploads/student_images/default_female.jpg";
                        } else {
                            $student_photo = base_url() . "uploads/student_images/default_male.jpg";
                        }
                    }
                    ?>
                    <div class="box box-primary box-solid"
                        style="border-radius: 6px; overflow: hidden; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        <div class="box-header with-border"
                            style="background: linear-gradient(135deg, #3c8dbc 0%, #1e5799 100%); padding: 12px 20px;">
                            <h3 class="box-title"
                                style="font-size: 16px; font-weight: 600; color: #fff; line-height: 28px;">
                                <i class="fa fa-vcard-o"></i>
                                <?php echo $this->lang->line('student') . ' ' . $this->lang->line('details'); ?>
                            </h3>
                            <button type="button" class="btn btn-warning btn-sm pull-right" data-toggle="modal"
                                data-target="#editStudentModal"
                                style="font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <i class="fa fa-pencil"></i>
                                <?php echo $this->lang->line('edit') . ' ' . $this->lang->line('student') . ' ' . $this->lang->line('details'); ?>
                            </button>
                        </div>
                        <div class="box-body" style="padding: 20px;">
                            <!-- Top Header Profile Info -->
                            <div class="row"
                                style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                                <div class="col-md-2 col-sm-3 text-center">
                                    <img src="<?php echo $student_photo; ?>" alt="<?php echo $student_fullname; ?>"
                                        class="img-thumbnail img-circle"
                                        style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #3c8dbc;" />
                                </div>
                                <div class="col-md-10 col-sm-9">
                                    <h3 style="margin-top: 5px; margin-bottom: 8px; font-weight: 700; color: #2c3e50;">
                                        <?php echo $student_fullname; ?>
                                    </h3>
                                    <div style="margin-bottom: 10px;">
                                        <span class="label label-primary"
                                            style="font-size: 12px; margin-right: 5px; padding: 5px 10px;">
                                            <i class="fa fa-graduation-cap"></i> <?php echo $this->lang->line('class'); ?>:
                                            <?php echo ($student->class ?? '-') . ' (' . ($student->section ?? '-') . ')'; ?>
                                        </span>
                                        <span class="label label-info"
                                            style="font-size: 12px; margin-right: 5px; padding: 5px 10px;">
                                            <i class="fa fa-id-badge"></i> <?php echo $this->lang->line('admission_no'); ?>:
                                            <?php echo $student->admission_no ?? '-'; ?>
                                        </span>
                                        <span class="label label-success"
                                            style="font-size: 12px; margin-right: 5px; padding: 5px 10px;">
                                            <i class="fa fa-list-ol"></i> <?php echo $this->lang->line('roll_no'); ?>:
                                            <?php echo $student->roll_no ?? '-'; ?>
                                        </span>
                                        <?php if (!empty($student->gender)) { ?>
                                            <span class="label label-default"
                                                style="font-size: 12px; margin-right: 5px; padding: 5px 10px;">
                                                <i class="fa fa-venus-mars"></i> <?php echo $student->gender; ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <div class="row text-muted" style="font-size: 13px;">
                                        <?php if (!empty($student->dob) && $student->dob != '0000-00-00') { ?>
                                            <div class="col-md-3 col-sm-6">
                                                <i class="fa fa-calendar text-aqua"></i>
                                                <strong><?php echo $this->lang->line('date_of_birth'); ?>:</strong>
                                                <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($student->dob)); ?>
                                            </div>
                                        <?php } ?>
                                        <?php if (!empty($student->admission_date) && $student->admission_date != '0000-00-00') { ?>
                                            <div class="col-md-3 col-sm-6">
                                                <i class="fa fa-calendar-check-o text-green"></i>
                                                <strong><?php echo $this->lang->line('admission_date'); ?>:</strong>
                                                <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($student->admission_date)); ?>
                                            </div>
                                        <?php } ?>
                                        <?php if (!empty($student->mobileno)) { ?>
                                            <div class="col-md-3 col-sm-6">
                                                <i class="fa fa-phone text-purple"></i>
                                                <strong><?php echo $this->lang->line('mobile_number'); ?>:</strong>
                                                <?php echo $student->mobileno; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if (!empty($student->email)) { ?>
                                            <div class="col-md-3 col-sm-6">
                                                <i class="fa fa-envelope text-red"></i>
                                                <strong><?php echo $this->lang->line('email'); ?>:</strong>
                                                <?php echo $student->email; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Information Grid -->
                            <div class="row">
                                <!-- Column 1: Personal & Identification Details -->
                                <div class="col-md-4 col-sm-6">
                                    <div class="panel panel-default"
                                        style="border-radius: 4px; border-left: 3px solid #3c8dbc;">
                                        <div class="panel-heading" style="background: #f8f9fa; font-weight: 600;">
                                            <i class="fa fa-user text-blue"></i>
                                            <?php echo $this->lang->line('personal') . ' ' . $this->lang->line('details'); ?>
                                        </div>
                                        <ul class="list-group list-group-unbordered"
                                            style="margin-bottom: 0; padding: 10px 15px;">
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('blood_group'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->blood_group) ? $student->blood_group : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('religion'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->religion) ? $student->religion : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('caste'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->cast) ? $student->cast : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('national_identification_no'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->adhar_no) ? $student->adhar_no : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item" style="border: none; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('house'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->house_name) ? $student->house_name : '-'; ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Column 2: Parent & Guardian Info -->
                                <div class="col-md-4 col-sm-6">
                                    <div class="panel panel-default"
                                        style="border-radius: 4px; border-left: 3px solid #00a65a;">
                                        <div class="panel-heading" style="background: #f8f9fa; font-weight: 600;">
                                            <i class="fa fa-users text-green"></i>
                                            <?php echo $this->lang->line('parent_guardian_detail'); ?>
                                        </div>
                                        <ul class="list-group list-group-unbordered"
                                            style="margin-bottom: 0; padding: 10px 15px;">
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('father_name'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->father_name) ? $student->father_name : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('father_phone'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->father_phone) ? $student->father_phone : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('mother_name'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->mother_name) ? $student->mother_name : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('guardian_name'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->guardian_name) ? $student->guardian_name : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item" style="border: none; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('guardian_phone'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->guardian_phone) ? $student->guardian_phone : '-'; ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Column 3: Address & Transport/Hostel -->
                                <div class="col-md-4 col-sm-12">
                                    <div class="panel panel-default"
                                        style="border-radius: 4px; border-left: 3px solid #f39c12;">
                                        <div class="panel-heading" style="background: #f8f9fa; font-weight: 600;">
                                            <i class="fa fa-map-marker text-orange"></i>
                                            <?php echo $this->lang->line('address') . ' & ' . $this->lang->line('facility'); ?>
                                        </div>
                                        <ul class="list-group list-group-unbordered"
                                            style="margin-bottom: 0; padding: 10px 15px;">
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('current_address'); ?>:</b>
                                                <span class="pull-right text-muted"
                                                    style="max-width: 60%; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                    title="<?php echo $student->current_address ?? ''; ?>">
                                                    <?php echo !empty($student->current_address) ? $student->current_address : '-'; ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('permanent_address'); ?>:</b>
                                                <span class="pull-right text-muted"
                                                    style="max-width: 60%; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                    title="<?php echo $student->permanent_address ?? ''; ?>">
                                                    <?php echo !empty($student->permanent_address) ? $student->permanent_address : '-'; ?>
                                                </span>
                                            </li>
                                            <li class="list-group-item"
                                                style="border: none; border-bottom: 1px dashed #eee; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('route_title'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->route_title) ? $student->route_title : '-'; ?></span>
                                            </li>
                                            <li class="list-group-item" style="border: none; padding: 6px 0;">
                                                <b><?php echo $this->lang->line('hostel_name'); ?>:</b> <span
                                                    class="pull-right text-muted"><?php echo !empty($student->hostel_name) ? $student->hostel_name . ' (' . ($student->room_no ?? '') . ')' : '-'; ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- Certificate Generation List Box -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix">
                            <i class="fa fa-certificate text-blue"></i>
                            <?php echo $this->lang->line('select') . ' ' . $this->lang->line('certificate'); ?>
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th><?php echo $this->lang->line('certificate') . ' ' . $this->lang->line('name'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('background_image'); ?></th>
                                        <th class="text-right" style="width: 200px;">
                                            <?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($certificateList)) { ?>
                                        <tr>
                                            <td colspan="4" class="text-danger text-center">
                                                <?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                    <?php } else {
                                        $count = 1;
                                        foreach ($certificateList as $certificate) { ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td class="mailbox-name">
                                                    <strong><?php echo $certificate->certificate_name; ?></strong></td>
                                                <td class="mailbox-name">
                                                    <?php if (!empty($certificate->background_image)) { ?>
                                                        <img src="<?php echo $this->media_storage->getImageURL('uploads/certificate/' . $certificate->background_image); ?>"
                                                            width="50" height="40" class="img-thumbnail" />
                                                    <?php } else { ?>
                                                        <span
                                                            class="label label-default"><?php echo $this->lang->line('none'); ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-primary btn-xs generate_cert_btn"
                                                        data-toggle="tooltip"
                                                        title="<?php echo $this->lang->line('generate_certificate'); ?>"
                                                        data-certificate_id="<?php echo $certificate->id; ?>">
                                                        <i class="fa fa-print"></i>
                                                        <?php echo $this->lang->line('generate') . ' & ' . $this->lang->line('print'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal for Editing Student Details -->
<div class="modal fade" id="editStudentModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editStudentForm" method="POST">
                <div class="modal-header bg-primary" style="background: #3c8dbc; color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i>
                        <?php echo $this->lang->line('edit') . ' ' . $this->lang->line('student') . ' ' . $this->lang->line('details'); ?>
                    </h4>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div id="formAlert"></div>

                    <!-- Basic Information -->
                    <h4 class="page-header text-info" style="font-size: 15px; margin-top: 0; font-weight: 600;">
                        <i class="fa fa-user"></i> Basic Information
                    </h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('first_name'); ?></label>
                                <input type="text" name="firstname" class="form-control"
                                    value="<?php echo htmlspecialchars($student->firstname ?? ''); ?>" required />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('middle_name'); ?></label>
                                <input type="text" name="middlename" class="form-control"
                                    value="<?php echo htmlspecialchars($student->middlename ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('last_name'); ?></label>
                                <input type="text" name="lastname" class="form-control"
                                    value="<?php echo htmlspecialchars($student->lastname ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('gender'); ?></label>
                                <select name="gender" class="form-control">
                                    <option value="Male" <?php echo ($student->gender ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($student->gender ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_birth'); ?></label>
                                <input type="text" name="dob" class="form-control date"
                                    value="<?php echo (!empty($student->dob) && $student->dob != '0000-00-00') ? date($this->customlib->getSchoolDateFormat(), strtotime($student->dob)) : ''; ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('blood_group'); ?></label>
                                <select name="blood_group" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg) { ?>
                                        <option value="<?php echo $bg; ?>" <?php echo ($student->blood_group ?? '') == $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mobile_number'); ?></label>
                                <input type="text" name="mobileno" class="form-control"
                                    value="<?php echo htmlspecialchars($student->mobileno ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('email'); ?></label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($student->email ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('religion'); ?></label>
                                <input type="text" name="religion" class="form-control"
                                    value="<?php echo htmlspecialchars($student->religion ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('national_identification_no'); ?></label>
                                <input type="text" name="adhar_no" class="form-control"
                                    value="<?php echo htmlspecialchars($student->adhar_no ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <!-- Parent & Guardian Information -->
                    <h4 class="page-header text-info" style="font-size: 15px; margin-top: 15px; font-weight: 600;">
                        <i class="fa fa-users"></i> Parent & Guardian Information
                    </h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_name'); ?></label>
                                <input type="text" name="father_name" class="form-control"
                                    value="<?php echo htmlspecialchars($student->father_name ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_phone'); ?></label>
                                <input type="text" name="father_phone" class="form-control"
                                    value="<?php echo htmlspecialchars($student->father_phone ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_name'); ?></label>
                                <input type="text" name="mother_name" class="form-control"
                                    value="<?php echo htmlspecialchars($student->mother_name ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_name'); ?></label>
                                <input type="text" name="guardian_name" class="form-control"
                                    value="<?php echo htmlspecialchars($student->guardian_name ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_phone'); ?></label>
                                <input type="text" name="guardian_phone" class="form-control"
                                    value="<?php echo htmlspecialchars($student->guardian_phone ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_relation'); ?></label>
                                <input type="text" name="guardian_relation" class="form-control"
                                    value="<?php echo htmlspecialchars($student->guardian_relation ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <h4 class="page-header text-info" style="font-size: 15px; margin-top: 15px; font-weight: 600;">
                        <i class="fa fa-map-marker"></i> Address Details
                    </h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('current_address'); ?></label>
                                <textarea name="current_address" class="form-control"
                                    rows="2"><?php echo htmlspecialchars($student->current_address ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('permanent_address'); ?></label>
                                <textarea name="permanent_address" class="form-control"
                                    rows="2"><?php echo htmlspecialchars($student->permanent_address ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" id="saveStudentBtn" class="btn btn-primary"><i class="fa fa-save"></i>
                        <?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for viewing and printing generated certificate -->
<div class="modal fade" id="certificateModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width:90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('generate_certificate'); ?></h4>
            </div>
            <div class="modal-body" id="certificate_detail">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="printCertContent()"><i class="fa fa-print"></i>
                    <?php echo $this->lang->line('print'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Submit Student Edit Form via AJAX
    $('#editStudentForm').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#saveStudentBtn');
        $btn.button('loading');

        $.ajax({
            url: '<?php echo base_url(); ?>user/certificate/update_student_details',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                $btn.button('reset');
                if (res.status === 'success') {
                    $('#formAlert').html('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-check-circle"></i> ' + res.message + '</div>');
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    $('#formAlert').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-exclamation-triangle"></i> ' + res.message + '</div>');
                }
            },
            error: function () {
                $btn.button('reset');
                $('#formAlert').html('<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>An error occurred while saving details.</div>');
            }
        });
    });

    $(document).on('click', '.generate_cert_btn', function () {
        var $btn = $(this);
        var certificate_id = $btn.data('certificate_id');
        $btn.button('loading');

        $.ajax({
            url: '<?php echo base_url(); ?>user/certificate/printcertificate',
            type: 'POST',
            data: { certificate_id: certificate_id },
            success: function (data) {
                $btn.button('reset');
                $('#certificate_detail').html(data);
                $('#certificateModal').modal('show');
            },
            error: function () {
                $btn.button('reset');
            }
        });
    });

    function printCertContent() {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({ "position": "absolute", "top": "-1000000px" });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write('<html><head><title>Print Certificate</title>');
        frameDoc.document.write('</head><body>');
        frameDoc.document.write($('#certificate_detail').html());
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
    }
</script>