<?php
$school_name = is_object($setting) ? $setting->name : (is_array($setting) ? (isset($setting['name']) ? $setting['name'] : (isset($setting[0]['name']) ? $setting[0]['name'] : 'School')) : 'School');
$school_address = is_object($setting) ? $setting->address : (is_array($setting) ? (isset($setting['address']) ? $setting['address'] : (isset($setting[0]['address']) ? $setting[0]['address'] : '')) : '');
$school_phone = is_object($setting) ? $setting->phone : (is_array($setting) ? (isset($setting['phone']) ? $setting['phone'] : (isset($setting[0]['phone']) ? $setting[0]['phone'] : '')) : '');
$school_email = is_object($setting) ? $setting->email : (is_array($setting) ? (isset($setting['email']) ? $setting['email'] : (isset($setting[0]['email']) ? $setting[0]['email'] : '')) : '');
$admin_logo = $this->setting_model->getAdminlogo();
$general_header = $this->setting_model->get_general_purpose_header();
$student_name = $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname);
$photo_src = !empty($student["image"]) ? $this->media_storage->getImageURL($student["image"]) : ($student['gender'] == 'Female' ? $this->media_storage->getImageURL("uploads/student_images/default_female.jpg") : $this->media_storage->getImageURL("uploads/student_images/default_male.jpg"));
$d = !empty($analysis) ? $analysis : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student_name); ?> - Comprehensive Student Analysis Report</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #eef2f5;
            color: #212529;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px 10px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print-toolbar {
            max-width: 960px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #dcdfe6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .no-print-toolbar .toolbar-left {
            display: flex;
            align-items: center;
        }

        .no-print-toolbar .toolbar-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 4px;
            text-decoration: none !important;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
        }

        .btn-back {
            background: #f4f6f8;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-print-report {
            background: #1a237e;
            color: #ffffff;
        }

        .btn-print-report:hover {
            background: #0d1642;
            color: #ffffff;
        }

        .report-card-container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px 40px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
            border-radius: 6px;
            border: 1px solid #dcdfe6;
        }

        .school-header-wrapper {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .school-header-banner {
            text-align: center;
            margin-bottom: 8px;
        }

        .school-header-img {
            max-width: 100%;
            height: auto;
            max-height: 100px;
            object-fit: contain;
        }

        .school-branding-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .school-logo-img {
            max-height: 65px;
            max-width: 120px;
            object-fit: contain;
        }

        .school-info-center {
            text-align: center;
            flex-grow: 1;
        }

        .school-title {
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
            color: #1a237e;
        }

        .school-subtitle {
            font-size: 12px;
            color: #555;
            margin: 0;
            line-height: 1.4;
        }

        .report-title-badge-container {
            text-align: center;
            margin-top: 8px;
        }

        .report-title-badge {
            display: inline-block;
            background: #e8eaf6;
            color: #1a237e;
            font-weight: 700;
            padding: 4px 18px;
            border-radius: 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border: 1px solid #c5cae9;
        }

        .student-hero {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 18px;
            gap: 18px;
        }

        .student-avatar {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3f51b5;
            flex-shrink: 0;
        }

        .student-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px 14px;
            flex-grow: 1;
            font-size: 12.5px;
        }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px 10px;
            text-align: center;
            border-top: 3.5px solid #3f51b5;
        }

        .kpi-card.kpi-purple {
            border-top-color: #6a1b9a;
        }

        .kpi-card.kpi-blue {
            border-top-color: #1565c0;
        }

        .kpi-card.kpi-green {
            border-top-color: #2e7d32;
        }

        .kpi-card.kpi-orange {
            border-top-color: #ef6c00;
        }

        .kpi-val {
            font-size: 20px;
            font-weight: 700;
            margin: 3px 0;
        }

        .kpi-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .section-header {
            font-size: 13.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1a237e;
            border-bottom: 1.5px solid #c5cae9;
            padding-bottom: 4px;
            margin: 18px 0 10px 0;
            display: flex;
            align-items: center;
        }

        .section-header i {
            margin-right: 7px;
        }

        .analysis-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .analysis-table th,
        .analysis-table td {
            border: 1px solid #dcdfe6;
            padding: 6px 9px;
            font-size: 12px;
        }

        .analysis-table th {
            background: #f1f3f8;
            font-weight: 700;
            color: #333;
        }

        .insight-box {
            background: #f0f7ff;
            border-left: 4px solid #1976d2;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 14px;
            font-size: 12.5px;
            line-height: 1.45;
        }

        .insight-box.strengths {
            background: #f1f8e9;
            border-left-color: #43a047;
        }

        .insight-box.focus {
            background: #fff8e1;
            border-left-color: #ffa000;
        }

        .insight-box.recommendation {
            background: #f3e5f5;
            border-left-color: #8e24aa;
        }

        .signatures-area {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 15px;
            page-break-inside: avoid;
        }

        .sign-block {
            text-align: center;
            width: 200px;
            border-top: 1px dashed #555;
            padding-top: 6px;
            font-size: 11.5px;
            font-weight: 600;
            color: #333;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .report-card-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .section-header,
            .kpi-row,
            .insight-box,
            .analysis-table,
            .signatures-area,
            .student-hero {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print-toolbar">
        <div class="toolbar-left">
            <a href="<?php echo base_url('student/view/' . $student['id']); ?>" class="toolbar-btn btn-back">
                <i class="fa fa-arrow-left"></i> Back to Student Profile
            </a>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print();" class="toolbar-btn btn-print-report">
                <i class="fa fa-print"></i> Print Analysis Report
            </button>
        </div>
    </div>

    <div class="report-card-container">
        <!-- School Header -->
        <div class="school-header-wrapper">
            <?php if (!empty($general_header)) { ?>
                <div class="school-header-banner">
                    <img src="<?php echo $this->media_storage->getImageURL('uploads/print_headerfooter/general_purpose/' . $general_header); ?>"
                        class="school-header-img" alt="Header">
                </div>
            <?php } else { ?>
                <div class="school-branding-flex">
                    <?php if (!empty($admin_logo)) { ?>
                        <img src="<?php echo $this->media_storage->getImageURL('uploads/school_content/admin_logo/' . $admin_logo); ?>"
                            class="school-logo-img" alt="Logo">
                    <?php } ?>
                    <div class="school-info-center">
                        <h1 class="school-title">
                            <?php echo htmlspecialchars(!empty($school_name) ? $school_name : 'DEMO PUBLIC SCHOOL'); ?></h1>
                        <p class="school-subtitle">
                            <?php echo htmlspecialchars($school_address); ?>
                            <?php echo !empty($school_phone) ? ' | Tel: ' . htmlspecialchars($school_phone) : ''; ?>
                            <?php echo !empty($school_email) ? ' | Email: ' . htmlspecialchars($school_email) : ''; ?>
                        </p>
                    </div>
                </div>
            <?php } ?>

            <div class="report-title-badge-container">
                <div class="report-title-badge"><i class="fa fa-line-chart"></i> Comprehensive Student Analytics &
                    Performance Report</div>
            </div>
        </div>

        <!-- Student Profile Hero -->
        <div class="student-hero">
            <img src="<?php echo $photo_src; ?>" class="student-avatar" alt="Photo">
            <div class="student-meta-grid">
                <div><strong>Student Name:</strong> <?php echo htmlspecialchars($student_name); ?></div>
                <div><strong>Admission No:</strong> <?php echo htmlspecialchars($student['admission_no']); ?></div>
                <div><strong>Class & Section:</strong>
                    <?php echo htmlspecialchars($student['class'] . ' - ' . $student['section']); ?></div>
                <div><strong>Roll Number:</strong>
                    <?php echo !empty($student['roll_no']) ? htmlspecialchars($student['roll_no']) : 'N/A'; ?></div>
                <div><strong>Date of Birth:</strong>
                    <?php echo (!empty($student['dob']) && $student['dob'] != '0000-00-00') ? date($sch_setting->date_format, strtotime($student['dob'])) : 'N/A'; ?>
                </div>
                <div><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></div>
                <div><strong>Guardian Name:</strong>
                    <?php echo !empty($student['guardian_name']) ? htmlspecialchars($student['guardian_name']) : (!empty($student['father_name']) ? htmlspecialchars($student['father_name']) : 'N/A'); ?>
                </div>
                <div><strong>Contact Phone:</strong>
                    <?php echo !empty($student['guardian_phone']) ? htmlspecialchars($student['guardian_phone']) : (!empty($student['mobileno']) ? htmlspecialchars($student['mobileno']) : 'N/A'); ?>
                </div>
                <div><strong>Report Date:</strong> <?php echo date($sch_setting->date_format); ?></div>
            </div>
        </div>

        <div id="analytics_dynamic_content">
            <?php if (!empty($d)) { ?>
                <!-- 1. KPI Cards -->
                <div class="kpi-row">
                    <div class="kpi-card kpi-purple">
                        <div class="kpi-label">Performance Score</div>
                        <div class="kpi-val" style="color:#6a1b9a;">
                            <?php echo $d['overall_score']['composite_score']; ?>/100</div>
                        <small class="text-muted"><?php echo $d['overall_score']['performance_label']; ?></small>
                    </div>
                    <div class="kpi-card kpi-blue">
                        <div class="kpi-label">Academic Score</div>
                        <div class="kpi-val" style="color:#1565c0;">
                            <?php echo $d['overall_score']['academic_percentage']; ?>%</div>
                        <small class="text-muted">Grade <?php echo $d['overall_score']['academic_grade']; ?></small>
                    </div>
                    <div class="kpi-card kpi-green">
                        <div class="kpi-label">Attendance Rate</div>
                        <div class="kpi-val" style="color:#2e7d32;"><?php echo $d['attendance']['percentage']; ?>%</div>
                        <small class="text-muted"><?php echo $d['attendance']['health_status']; ?></small>
                    </div>
                    <div class="kpi-card kpi-orange">
                        <div class="kpi-label">Fee Clearance</div>
                        <div class="kpi-val" style="font-size:16px; color:#ef6c00;"><?php echo $d['fees']['status']; ?>
                        </div>
                        <small class="text-muted">Due:
                            <?php echo ($d['fees']['balance_amount'] > 0) ? amountFormat($d['fees']['balance_amount']) : 'Nil'; ?></small>
                    </div>
                </div>

                <!-- 2. Executive Insights -->
                <div class="insight-box">
                    <h4 style="margin:0 0 5px 0; font-size:13.5px; font-weight:bold; color:#0d47a1;">
                        <i class="fa fa-info-circle"></i> Executive Performance Summary & AI Insights
                        <?php if (isset($d['insights']['source']) && $d['insights']['source'] == 'ai_endpoint') { ?>
                            <span
                                style="float:right; font-size:10px; background:#ede9fe; color:#6d28d9; padding:2px 6px; border-radius:3px; border:1px solid #ddd6fe;">AI
                                Powered</span>
                        <?php } ?>
                    </h4>
                    <div><?php echo $d['insights']['summary']; ?></div>
                </div>

                <!-- Strengths & Focus Areas -->
                <div class="row">
                    <div class="col-xs-6">
                        <div class="insight-box strengths" style="min-height:95px;">
                            <h4 style="margin:0 0 5px 0; font-size:13px; font-weight:bold; color:#2e7d32;"><i
                                    class="fa fa-check-circle"></i> Key Strengths</h4>
                            <ul style="margin:0 0 0 16px; padding:0;">
                                <?php foreach ($d['insights']['strengths'] as $s) { ?>
                                    <li><?php echo $s; ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="insight-box focus" style="min-height:95px;">
                            <h4 style="margin:0 0 5px 0; font-size:13px; font-weight:bold; color:#e65100;"><i
                                    class="fa fa-bullseye"></i> Focus / Improvement Areas</h4>
                            <ul style="margin:0 0 0 16px; padding:0;">
                                <?php foreach ($d['insights']['focus_areas'] as $f) { ?>
                                    <li><?php echo $f; ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 3. Examination Breakdown -->
                <div class="section-header"><i class="fa fa-graduation-cap"></i> Academic & Examination Evaluation</div>
                <?php if (!empty($d['examinations']['subject_summary'])) { ?>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th class="text-center">Marks Scored / Max</th>
                                <th class="text-center">Average Score %</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Proficiency Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($d['examinations']['subject_summary'] as $sub) {
                                $badgeColor = ($sub['avg_percentage'] >= 80) ? '#2e7d32' : (($sub['avg_percentage'] >= 60) ? '#1565c0' : (($sub['avg_percentage'] >= 40) ? '#ef6c00' : '#c62828'));
                                $profText = ($sub['avg_percentage'] >= 85) ? 'Advanced Mastery' : (($sub['avg_percentage'] >= 70) ? 'Proficient' : (($sub['avg_percentage'] >= 50) ? 'Developing' : 'Requires Focus'));
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sub['subject']); ?></strong></td>
                                    <td class="text-center"><?php echo $sub['total_obtained'] . ' / ' . $sub['total_max']; ?></td>
                                    <td class="text-center" style="font-weight:bold; color:<?php echo $badgeColor; ?>;">
                                        <?php echo $sub['avg_percentage']; ?>%</td>
                                    <td class="text-center"><span
                                            style="display:inline-block; padding:2px 8px; border-radius:3px; background:#f0f0f0; font-weight:bold;"><?php echo $sub['grade']; ?></span>
                                    </td>
                                    <td class="text-center"><span
                                            style="color:<?php echo $badgeColor; ?>; font-weight:600;"><?php echo $profText; ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="text-muted" style="font-style:italic; margin-bottom:15px;">No formal examination results recorded
                        for this session yet.</p>
                <?php } ?>

                <!-- 4. Attendance Breakdown -->
                <div class="section-header"><i class="fa fa-calendar-check-o"></i> Attendance Consistency & Trends</div>
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-xs-3"><strong>Total Working Days:</strong>
                        <?php echo $d['attendance']['total_working_days']; ?></div>
                    <div class="col-xs-3"><strong style="color:#2e7d32;">Days Present:</strong>
                        <?php echo $d['attendance']['present']; ?></div>
                    <div class="col-xs-3"><strong style="color:#c62828;">Days Absent:</strong>
                        <?php echo $d['attendance']['absent']; ?></div>
                    <div class="col-xs-3"><strong style="color:#ef6c00;">Late / Half-Day:</strong>
                        <?php echo ($d['attendance']['late'] + $d['attendance']['half_day']); ?></div>
                </div>

                <?php if (!empty($d['attendance']['monthly_trend'])) { ?>
                    <table class="analysis-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Working Days</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($d['attendance']['monthly_trend'] as $mt) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mt['month'] . ' ' . $mt['year']); ?></td>
                                    <td class="text-center"><?php echo $mt['total_days']; ?></td>
                                    <td class="text-center text-success font-weight-bold"><?php echo $mt['present']; ?></td>
                                    <td class="text-center text-danger"><?php echo $mt['absent']; ?></td>
                                    <td class="text-center text-warning"><?php echo $mt['late']; ?></td>
                                    <td class="text-center font-weight-bold"><?php echo $mt['percentage']; ?>%</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>

                <!-- 5. Official Recommendations -->
                <div class="section-header"><i class="fa fa-mortar-board"></i> Faculty & Academic Recommendations</div>
                <div class="insight-box recommendation">
                    <div><?php echo $d['insights']['recommendation']; ?></div>
                </div>
            <?php } else { ?>
                <div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading
                    Analysis...</div>
            <?php } ?>
        </div>

        <!-- Official Signatures Block -->
        <div class="signatures-area">
            <div class="sign-block">Class Teacher Signature</div>
            <div class="sign-block">Academic Counselor / Coordinator</div>
            <div class="sign-block">Principal / Head of School</div>
        </div>
    </div>

    <script src="<?php echo base_url(); ?>backend/plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script>
        $(document).ready(function () {
            var hasData = <?php echo !empty($d) ? 'true' : 'false'; ?>;
            if (!hasData) {
                var studentId = "<?php echo $student['id']; ?>";
                $.ajax({
                    url: "<?php echo base_url('student/get_student_analysis/'); ?>" + studentId,
                    type: "GET",
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'success' && res.data) {
                            location.reload();
                        } else {
                            $('#analytics_dynamic_content').html('<div class="alert alert-danger">Unable to load student analysis.</div>');
                        }
                    },
                    error: function () {
                        $('#analytics_dynamic_content').html('<div class="alert alert-danger">Error connecting to server.</div>');
                    }
                });
            }
        });
    </script>
</body>

</html>