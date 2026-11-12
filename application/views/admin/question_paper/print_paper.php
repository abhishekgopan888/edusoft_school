<?php
$school_name = is_object($setting) ? $setting->name : (is_array($setting) ? (isset($setting['name']) ? $setting['name'] : (isset($setting[0]['name']) ? $setting[0]['name'] : 'School')) : 'School');
$school_address = is_object($setting) ? $setting->address : (is_array($setting) ? (isset($setting['address']) ? $setting['address'] : (isset($setting[0]['address']) ? $setting[0]['address'] : '')) : '');
$school_phone = is_object($setting) ? $setting->phone : (is_array($setting) ? (isset($setting['phone']) ? $setting['phone'] : (isset($setting[0]['phone']) ? $setting[0]['phone'] : '')) : '');
$school_email = is_object($setting) ? $setting->email : (is_array($setting) ? (isset($setting['email']) ? $setting['email'] : (isset($setting[0]['email']) ? $setting[0]['email'] : '')) : '');
$admin_logo = $this->setting_model->getAdminlogo();
$onlineexamfooter = $this->setting_model->get_onlineexamfooter();
$header_image = !empty($onlineexamfooter['header_image']) ? $onlineexamfooter['header_image'] : '';
$general_header = $this->setting_model->get_general_purpose_header();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($paper->title); ?> - Question Paper</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #eef2f5;
            color: #111;
            font-family: 'Times New Roman', Times, Georgia, serif;
            font-size: 14.5px;
            line-height: 1.5;
            margin: 0;
            padding: 20px 10px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print-toolbar {
            max-width: 920px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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
            padding: 6px 13px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 4px;
            text-decoration: none !important;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
            white-space: nowrap;
        }

        .toolbar-btn:focus, .toolbar-btn:active {
            outline: none;
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

        .btn-scheme {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-scheme:hover {
            background: #0369a1;
            color: #ffffff;
        }

        .btn-scheme.active-scheme {
            background: #d97706 !important;
            color: #ffffff !important;
        }
        .btn-scheme.active-scheme:hover {
            background: #b45309 !important;
            color: #ffffff !important;
        }

        .btn-print-student {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-print-student:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-print-teacher {
            background: #16a34a;
            color: #ffffff;
        }
        .btn-print-teacher:hover {
            background: #15803d;
            color: #ffffff;
        }

        .paper-container {
            max-width: 920px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px 45px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #dcdfe6;
            border-radius: 4px;
        }

        .school-header-wrapper {
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .school-header-banner {
            text-align: center;
            margin-bottom: 10px;
        }

        .school-header-img {
            max-width: 100%;
            height: auto;
            max-height: 105px;
            object-fit: contain;
        }

        .school-branding-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 8px;
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
            font-size: 23px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
            color: #111;
        }

        .school-subtitle {
            font-size: 13px;
            color: #444;
            margin: 0;
            line-height: 1.4;
        }

        .exam-title-box {
            text-align: center;
            margin-top: 10px;
        }

        .exam-title-text {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 0.5px;
            color: #111;
        }

        .paper-meta-table {
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 1.5px solid #222;
            padding-bottom: 6px;
        }

        .paper-meta-table td {
            padding: 4px 2px;
            font-size: 14.5px;
            vertical-align: middle;
        }

        .instructions-box {
            background: #fafafa;
            border: 1px dashed #666;
            padding: 9px 14px;
            margin-bottom: 20px;
            font-size: 13.5px;
            line-height: 1.45;
            page-break-inside: avoid;
            border-radius: 3px;
        }

        .instructions-title {
            font-weight: bold;
            font-size: 13.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .question-item {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .question-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 15px;
            line-height: 1.5;
        }

        .question-num-text {
            flex-grow: 1;
        }

        .question-marks-badge {
            font-weight: bold;
            white-space: nowrap;
            margin-left: 15px;
            font-size: 14px;
            color: #222;
        }

        .mcq-options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px 30px;
            margin-top: 8px;
            padding-left: 24px;
            font-size: 14px;
        }

        .mcq-option-item {
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .mcq-opt-letter {
            font-weight: bold;
            min-width: 24px;
        }

        .teacher-scheme-section {
            margin-top: 10px;
            background: #f6fbf6;
            border: 1.5px dashed #2e7d32;
            padding: 10px 14px;
            font-size: 13.5px;
            border-radius: 4px;
            display: none;
            page-break-inside: avoid;
        }

        .scheme-badge {
            color: #2e7d32;
            font-weight: bold;
            font-size: 13.5px;
            margin-bottom: 4px;
        }

        .step-marking-box {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dotted #c8e6c9;
        }

        .step-marking-title {
            font-weight: bold;
            color: #1b5e20;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .step-marking-list {
            margin: 3px 0 0 20px;
            padding: 0;
        }

        .scheme-step-item {
            margin: 3px 0;
            line-height: 1.4;
        }

        .step-marks-tag {
            color: #2e7d32;
            font-weight: bold;
        }

        .explanation-box {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dotted #c8e6c9;
            color: #0d47a1;
            font-size: 13px;
        }

        .end-paper-tag {
            text-align: center;
            margin-top: 35px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-size: 13px;
            font-weight: bold;
            color: #555;
            letter-spacing: 1px;
            page-break-inside: avoid;
        }

        @media screen and (max-width: 768px) {
            .no-print-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .no-print-toolbar .toolbar-actions {
                justify-content: flex-start;
            }
            .paper-container {
                padding: 20px 15px;
            }
            .mcq-options-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm 12mm 15mm 12mm;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 13.5pt !important;
            }

            .paper-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .question-item, .paper-meta-table, .instructions-box, .school-header-wrapper, .end-paper-tag, .teacher-scheme-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            body.hide-marking-scheme .teacher-scheme-section {
                display: none !important;
                visibility: hidden !important;
            }

            body.show-marking-scheme .teacher-scheme-section {
                display: block !important;
                visibility: visible !important;
            }
        }
    </style>
</head>
<body class="hide-marking-scheme">

    <div class="no-print-toolbar">
        <div class="toolbar-left">
            <a href="<?php echo base_url('admin/question_paper'); ?>" class="toolbar-btn btn-back">
                <i class="fa fa-arrow-left"></i> Back to Question Papers
            </a>
        </div>
        <div class="toolbar-actions">
            <button id="btn_toggle_scheme" class="toolbar-btn btn-scheme" onclick="toggleScheme();">
                <i id="scheme_btn_icon" class="fa fa-key"></i> <span id="scheme_btn_text">Show Answer Key & Marking</span>
            </button>
            <button onclick="printStudentCopy();" class="toolbar-btn btn-print-student">
                <i class="fa fa-print"></i> Print (Student Copy)
            </button>
            <button onclick="printTeacherCopy();" class="toolbar-btn btn-print-teacher">
                <i class="fa fa-print"></i> Print (With Solutions)
            </button>
        </div>
    </div>

    <div class="paper-container">
        <!-- School Header -->
        <div class="school-header-wrapper">
            <?php if (!empty($header_image)) { ?>
                <div class="school-header-banner">
                    <img src="<?php echo $this->media_storage->getImageURL('uploads/print_headerfooter/online_exam/' . $header_image); ?>" class="school-header-img" alt="Header">
                </div>
            <?php } elseif (!empty($general_header)) { ?>
                <div class="school-header-banner">
                    <img src="<?php echo $this->media_storage->getImageURL('uploads/print_headerfooter/general_purpose/' . $general_header); ?>" class="school-header-img" alt="Header">
                </div>
            <?php } else { ?>
                <div class="school-branding-box">
                    <?php if (!empty($admin_logo)) { ?>
                        <img src="<?php echo $this->media_storage->getImageURL('uploads/school_content/admin_logo/' . $admin_logo); ?>" class="school-logo-img" alt="Logo">
                    <?php } ?>
                    <div class="school-info-center">
                        <h1 class="school-title"><?php echo htmlspecialchars(!empty($school_name) ? $school_name : 'DEMO PUBLIC SCHOOL'); ?></h1>
                        <p class="school-subtitle">
                            <?php echo htmlspecialchars($school_address); ?>
                            <?php echo !empty($school_phone) ? ' | Tel: ' . htmlspecialchars($school_phone) : ''; ?>
                            <?php echo !empty($school_email) ? ' | Email: ' . htmlspecialchars($school_email) : ''; ?>
                        </p>
                    </div>
                </div>
            <?php } ?>

            <div class="exam-title-box">
                <h2 class="exam-title-text"><?php echo htmlspecialchars($paper->title); ?></h2>
            </div>
        </div>

        <!-- Meta Table -->
        <table class="paper-meta-table">
            <tr>
                <td style="width: 35%;"><strong>Class:</strong> <?php echo htmlspecialchars($paper->class_name . (!empty($paper->section_name) ? ' (' . $paper->section_name . ')' : '')); ?></td>
                <td style="width: 35%; text-align: center;"><strong>Subject:</strong> <?php echo htmlspecialchars($paper->subject_name . (!empty($paper->subject_code) ? ' (' . $paper->subject_code . ')' : '')); ?></td>
                <td style="width: 30%; text-align: right;"><strong>Max Marks:</strong> <?php echo htmlspecialchars($paper->total_marks); ?></td>
            </tr>
            <tr>
                <td><strong>Time Allowed:</strong> <?php echo htmlspecialchars($paper->duration_minutes); ?> Minutes</td>
                <td style="text-align: center;"><strong>Date:</strong> ___________________</td>
                <td style="text-align: right;"><strong>Roll No:</strong> ______________</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Student Name:</strong> __________________________________________________</td>
                <td style="text-align: right;"><strong>Invigilator's Sign:</strong> ______________</td>
            </tr>
        </table>

        <!-- Instructions -->
        <?php if (!empty($paper->instructions)) { ?>
            <div class="instructions-box">
                <div class="instructions-title"><i class="fa fa-info-circle"></i> General Instructions:</div>
                <div><?php echo nl2br(htmlspecialchars($paper->instructions)); ?></div>
            </div>
        <?php } ?>

        <!-- Questions List -->
        <div class="questions-list">
            <?php if (!empty($paper->questions)) {
                $qCount = 1;
                foreach ($paper->questions as $q) {
                    $qNo = !empty($q->question_no) ? $q->question_no : $qCount++;
            ?>
                    <div class="question-item">
                        <div class="question-header-flex">
                            <div class="question-num-text">
                                <strong>Q<?php echo $qNo; ?>.</strong> <?php echo nl2br(htmlspecialchars($q->question_text)); ?>
                            </div>
                            <div class="question-marks-badge">[<?php echo floatval($q->marks); ?> Mark<?php echo (floatval($q->marks) > 1 ? 's' : ''); ?>]</div>
                        </div>

                        <?php if (!empty($q->options_json)) {
                            $options = is_string($q->options_json) ? json_decode($q->options_json, true) : $q->options_json;
                            if (!empty($options) && is_array($options)) { ?>
                                <div class="mcq-options-grid">
                                    <?php foreach ($options as $oIdx => $opt) { ?>
                                        <div class="mcq-option-item">
                                            <span class="mcq-opt-letter">(<?php echo chr(65 + $oIdx); ?>)</span>
                                            <span class="mcq-opt-text"><?php echo htmlspecialchars($opt); ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                        <?php }
                        } ?>

                        <!-- TEACHER MARKING SCHEME & SOLUTION (TOGGLEABLE) -->
                        <div class="teacher-scheme-section">
                            <div class="scheme-badge"><i class="fa fa-check-circle"></i> <strong>Answer Key / Correct Answer:</strong> <?php echo htmlspecialchars(!empty($q->answer_key) ? $q->answer_key : 'As specified'); ?></div>

                            <?php if (!empty($q->step_marking_json)) {
                                $steps = is_string($q->step_marking_json) ? json_decode($q->step_marking_json, true) : $q->step_marking_json;
                                if (!empty($steps) && is_array($steps)) { ?>
                                    <div class="step-marking-box">
                                        <div class="step-marking-title"><i class="fa fa-list-ol"></i> Step-by-Step Marking Breakdown:</div>
                                        <ul class="step-marking-list">
                                            <?php foreach ($steps as $st) {
                                                $sText = isset($st['step']) ? $st['step'] : (is_string($st) ? $st : '');
                                                $sMarks = isset($st['marks']) ? ' [' . floatval($st['marks']) . ' Mark' . (floatval($st['marks']) > 1 ? 's' : '') . ']' : '';
                                                if (!empty($sText)) {
                                                    echo '<li class="scheme-step-item"><span>' . htmlspecialchars($sText) . '</span> <span class="step-marks-tag">' . htmlspecialchars($sMarks) . '</span></li>';
                                                }
                                            } ?>
                                        </ul>
                                    </div>
                            <?php }
                            } ?>

                            <?php if (!empty($q->explanation)) { ?>
                                <div class="explanation-box">
                                    <strong><i class="fa fa-lightbulb-o"></i> Explanation / Solution:</strong> <?php echo nl2br(htmlspecialchars($q->explanation)); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>

        <div class="end-paper-tag">
            *** END OF QUESTION PAPER ***
        </div>
    </div>

    <script>
        var isSchemeVisible = false;

        function toggleScheme() {
            isSchemeVisible = !isSchemeVisible;
            applySchemeVisibility(isSchemeVisible);
        }

        function applySchemeVisibility(visible) {
            var els = document.querySelectorAll('.teacher-scheme-section');
            els.forEach(function(el) {
                el.style.display = visible ? 'block' : 'none';
            });
            
            var btn = document.getElementById('btn_toggle_scheme');
            var btnText = document.getElementById('scheme_btn_text');
            var btnIcon = document.getElementById('scheme_btn_icon');
            
            if (visible) {
                document.body.classList.remove('hide-marking-scheme');
                document.body.classList.add('show-marking-scheme');
                if (btnText) btnText.textContent = 'Hide Answer Key & Marking';
                if (btnIcon) btnIcon.className = 'fa fa-eye-slash';
                if (btn) btn.classList.add('active-scheme');
            } else {
                document.body.classList.remove('show-marking-scheme');
                document.body.classList.add('hide-marking-scheme');
                if (btnText) btnText.textContent = 'Show Answer Key & Marking';
                if (btnIcon) btnIcon.className = 'fa fa-key';
                if (btn) btn.classList.remove('active-scheme');
            }
        }

        function printStudentCopy() {
            var previousState = isSchemeVisible;
            
            // Switch to student copy (hide marking scheme)
            applySchemeVisibility(false);
            
            // Trigger browser print
            setTimeout(function() {
                window.print();
                
                // If the user previously had solutions visible on screen, restore it
                if (previousState) {
                    setTimeout(function() {
                        applySchemeVisibility(true);
                    }, 500);
                }
            }, 100);
        }

        function printTeacherCopy() {
            var previousState = isSchemeVisible;
            
            // Switch to teacher copy (show marking scheme)
            applySchemeVisibility(true);
            
            // Trigger browser print
            setTimeout(function() {
                window.print();
                
                // If the user previously had solutions hidden on screen, restore it
                if (!previousState) {
                    setTimeout(function() {
                        applySchemeVisibility(false);
                    }, 500);
                }
            }, 100);
        }
    </script>
</body>
</html>

