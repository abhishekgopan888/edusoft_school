<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg');
                    $this->session->unset_userdata('msg'); ?>
                <?php } ?>

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_generate" data-toggle="tab"><i class="fa fa-magic text-aqua"></i> <?php echo $this->lang->line('question_paper_generator'); ?></a></li>
                        <li><a href="#tab_list" data-toggle="tab"><i class="fa fa-list text-purple"></i> <?php echo $this->lang->line('question_paper_list'); ?> (<?php echo count($question_papers); ?>)</a></li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: GENERATE -->
                        <div class="tab-pane active" id="tab_generate">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box box-primary box-solid">
                                        <div class="box-header with-border">
                                            <h3 class="box-title"><i class="fa fa-sliders"></i> <?php echo $this->lang->line('select_criteria'); ?> & Parameters</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('class'); ?> <small class="req">*</small></label>
                                                <select id="gen_class_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($classlist as $class) { ?>
                                                        <option value="<?php echo $class['id']; ?>"><?php echo $class['class']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('section'); ?></label>
                                                <select id="gen_section_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('subject'); ?> <small class="req">*</small></label>
                                                <select id="gen_subject_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($subjectlist as $subject) { ?>
                                                        <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name'] . ($subject['code'] ? ' (' . $subject['code'] . ')' : ''); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="d-flex justify-content-between">
                                                    <span><?php echo $this->lang->line('select_chapters'); ?> <small class="req">*</small></span>
                                                    <a href="javascript:void(0);" id="btn_toggle_all_chapters" style="font-size:11px; float:right;">Select All</a>
                                                </label>
                                                <div id="chapters_container" style="max-height: 160px; overflow-y: auto; border: 1px solid #d2d6de; padding: 8px; border-radius: 4px; background: #fafafa;">
                                                    <p class="text-muted" style="margin:0; font-size:12px;">Please select Class & Subject first...</p>
                                                </div>
                                            </div>

                                            <hr style="margin: 10px 0;">

                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label><?php echo $this->lang->line('question_count'); ?></label>
                                                        <input type="number" id="gen_count" class="form-control" value="10" min="1" max="50">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <div class="form-group">
                                                        <label><?php echo $this->lang->line('difficulty_level'); ?></label>
                                                        <select id="gen_difficulty" class="form-control">
                                                            <option value="Mixed">Mixed</option>
                                                            <option value="Easy">Easy</option>
                                                            <option value="Medium" selected>Medium</option>
                                                            <option value="Hard">Hard</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('ai_suggestions'); ?></label>
                                                <textarea id="gen_suggestions" class="form-control" rows="2" placeholder="e.g. Include 5 MCQs and 2 numerical problems..."></textarea>
                                            </div>

                                            <button type="button" id="btn_generate_questions" class="btn btn-block btn-primary btn-lg" style="margin-top: 15px;">
                                                <i class="fa fa-magic"></i> <?php echo $this->lang->line('generate_questions'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- PREVIEW & SAVE PANEL -->
                                <div class="col-md-8">
                                    <div class="box box-info box-solid" id="preview_box">
                                        <div class="box-header with-border">
                                            <h3 class="box-title"><i class="fa fa-eye"></i> Generated Question Paper Live Preview</h3>
                                            <div class="box-tools pull-right" style="display:flex; align-items:center; gap:8px;">
                                                <button type="button" id="btn_regenerate_top" class="btn btn-sm btn-default" style="display:none; font-weight:600; color:#0056b3;">
                                                    <i class="fa fa-refresh"></i> Regenerate All Questions
                                                </button>
                                                <span id="badge_question_count" class="label label-warning" style="display:none; font-size:12px;">0 Questions</span>
                                            </div>
                                        </div>
                                        <div class="box-body" style="min-height: 400px;">
                                            <div id="loading_spinner" style="display:none; text-align:center; padding: 60px 20px;">
                                                <i class="fa fa-spinner fa-spin fa-3x text-aqua"></i>
                                                <h4 style="margin-top:15px; color:#555;">AI is analyzing chapters & synthesizing questions...</h4>
                                                <p class="text-muted">Please wait a moment.</p>
                                            </div>

                                            <div id="empty_placeholder" style="text-align:center; padding: 80px 20px; color: #999;">
                                                <i class="fa fa-file-text-o fa-4x" style="opacity: 0.3;"></i>
                                                <h4>No Questions Generated Yet</h4>
                                                <p>Select your Class, Subject, and Chapters on the left, then click <strong>"Generate Questions"</strong>.</p>
                                            </div>

                                            <div id="questions_preview_content" style="display:none;">
                                                <!-- Questions List Container -->
                                                <div id="questions_rendered_list" style="margin-bottom: 25px;"></div>

                                                <!-- Save Form Block -->
                                                <div class="panel panel-default" style="background: #fdfdfd; border: 1px solid #e0e0e0; padding: 15px; border-radius: 4px;">
                                                    <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                                                        <i class="fa fa-save text-green"></i> Save & Publish Question Paper
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label><?php echo $this->lang->line('question_title'); ?> <small class="req">*</small></label>
                                                                <input type="text" id="save_title" class="form-control" placeholder="e.g. Mid-Term Examination 2026">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label><?php echo $this->lang->line('total_marks'); ?></label>
                                                                <input type="number" id="save_total_marks" class="form-control" value="50">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label><?php echo $this->lang->line('duration_minutes'); ?></label>
                                                                <input type="number" id="save_duration" class="form-control" value="60">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><?php echo $this->lang->line('general_instructions'); ?></label>
                                                        <textarea id="save_instructions" class="form-control" rows="2" placeholder="1. All questions are compulsory.&#10;2. Write neatly."></textarea>
                                                    </div>
                                                    <div class="text-right">
                                                        <button type="button" id="btn_regenerate" class="btn btn-default"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('regenerate_questions'); ?></button>
                                                        <button type="button" id="btn_save_paper" class="btn btn-success"><i class="fa fa-check"></i> <?php echo $this->lang->line('save_question_paper'); ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: SAVED LIST -->
                        <div class="tab-pane" id="tab_list">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover dataTable" id="savedPapersTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo $this->lang->line('question_title'); ?></th>
                                            <th><?php echo $this->lang->line('class'); ?></th>
                                            <th><?php echo $this->lang->line('subject'); ?></th>
                                            <th><?php echo $this->lang->line('total_marks'); ?></th>
                                            <th><?php echo $this->lang->line('duration_minutes'); ?></th>
                                            <th>Created Date</th>
                                            <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($question_papers)) {
                                            $count = 1;
                                            foreach ($question_papers as $qp) { ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><strong><?php echo $qp->title; ?></strong></td>
                                                    <td><?php echo $qp->class_name . ($qp->section_name ? ' (' . $qp->section_name . ')' : ''); ?></td>
                                                    <td><?php echo $qp->subject_name; ?></td>
                                                    <td><span class="badge bg-blue"><?php echo $qp->total_marks; ?> Marks</span></td>
                                                    <td><?php echo $qp->duration_minutes; ?> Mins</td>
                                                    <td><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($qp->created_at)); ?></td>
                                                    <td class="text-right">
                                                        <button type="button" class="btn btn-info btn-xs btn_regen_from_saved" data-id="<?php echo $qp->id; ?>" data-toggle="tooltip" title="Regenerate & Modify this Paper">
                                                            <i class="fa fa-refresh"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-default btn-xs btn_view_modal" data-id="<?php echo $qp->id; ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('view_question_paper'); ?>">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                        <a href="<?php echo base_url('admin/question_paper/print_paper/' . $qp->id); ?>" target="_blank" class="btn btn-primary btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('print_question_paper'); ?>">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                        <a href="<?php echo base_url('admin/question_paper/delete/' . $qp->id); ?>" class="btn btn-danger btn-xs" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
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
        </div>
    </section>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="modal_view_paper" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal_paper_title">Question Paper</h4>
            </div>
            <div class="modal-body" id="modal_paper_body" style="max-height: 500px; overflow-y: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="modal_regen_from_view_btn"><i class="fa fa-refresh"></i> Regenerate as New Paper</button>
                <a href="#" id="modal_print_link" target="_blank" class="btn btn-primary"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';
    var current_generated_questions = [];
    var current_viewed_paper_id = null;

    $(document).ready(function () {
        // Class change -> load sections and chapters
        $('#gen_class_id').on('change', function () {
            var class_id = $(this).val();
            loadSections(class_id, 0);
            loadChapters();
        });

        // Section & Subject change -> load chapters
        $('#gen_section_id, #gen_subject_id').on('change', function () {
            loadChapters();
        });

        // Toggle select all chapters
        $('#btn_toggle_all_chapters').on('click', function () {
            var checkboxes = $('#chapters_container input[type="checkbox"]');
            var allChecked = true;
            checkboxes.each(function () {
                if (!$(this).prop('checked')) allChecked = false;
            });
            checkboxes.prop('checked', !allChecked);
        });

        // Generate / Regenerate All Questions button click
        $('#btn_generate_questions, #btn_regenerate, #btn_regenerate_top').on('click', function () {
            var class_id = $('#gen_class_id').val();
            var section_id = $('#gen_section_id').val();
            var subject_id = $('#gen_subject_id').val();
            var count = $('#gen_count').val();
            var difficulty = $('#gen_difficulty').val();
            var suggestions = $('#gen_suggestions').val();

            var chapter_ids = [];
            $('#chapters_container input[type="checkbox"]:checked').each(function () {
                chapter_ids.push($(this).val());
            });

            if (!class_id || !subject_id) {
                alert('Please select Class and Subject first!');
                return;
            }

            if (chapter_ids.length === 0) {
                alert('Please select at least one Chapter!');
                return;
            }

            $('#empty_placeholder').hide();
            $('#questions_preview_content').hide();
            $('#loading_spinner').show();
            $('#badge_question_count').hide();
            $('#btn_regenerate_top').hide();

            $.ajax({
                url: base_url + 'admin/question_paper/generate',
                type: 'POST',
                data: {
                    class_id: class_id,
                    section_id: section_id,
                    subject_id: subject_id,
                    chapter_ids: chapter_ids,
                    question_count: count,
                    difficulty: difficulty,
                    suggestions: suggestions
                },
                dataType: 'json',
                success: function (res) {
                    $('#loading_spinner').hide();
                    if (res.status === 'success' && res.questions && res.questions.length > 0) {
                        current_generated_questions = res.questions;
                        renderQuestionsPreview(res.questions);
                        $('#questions_preview_content').show();
                        $('#badge_question_count').text(res.questions.length + ' Questions').show();
                        $('#btn_regenerate_top').show();

                        // Auto-fill title
                        var sub_text = $('#gen_subject_id option:selected').text();
                        var class_text = $('#gen_class_id option:selected').text();
                        $('#save_title').val(class_text + ' ' + sub_text + ' Assessment Paper');
                    } else {
                        $('#empty_placeholder').show();
                        alert('Could not generate questions. Please verify parameters.');
                    }
                },
                error: function () {
                    $('#loading_spinner').hide();
                    $('#empty_placeholder').show();
                    alert('Error connecting to server. Please try again.');
                }
            });
        });

        // Single Question Regeneration (Swap question)
        $(document).on('click', '.btn_regen_single', function () {
            var btn = $(this);
            var qIndex = parseInt(btn.data('index'));
            var targetQ = current_generated_questions[qIndex];
            if (!targetQ) return;

            var class_id = $('#gen_class_id').val();
            var subject_id = $('#gen_subject_id').val();
            var difficulty = $('#gen_difficulty').val();

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Regenerating...');

            $.ajax({
                url: base_url + 'admin/question_paper/generate_single',
                type: 'POST',
                data: {
                    class_id: class_id,
                    subject_id: subject_id,
                    chapter: targetQ.chapter || '',
                    question_type: targetQ.question_type || 'MCQ',
                    difficulty: difficulty || targetQ.difficulty || 'Medium',
                    question_no: targetQ.question_no
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success' && res.question) {
                        current_generated_questions[qIndex] = res.question;
                        renderQuestionsPreview(current_generated_questions);
                    } else {
                        btn.prop('disabled', false).html('<i class="fa fa-refresh text-aqua"></i> Swap / Regenerate');
                        alert(res.message || 'Could not regenerate this question.');
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html('<i class="fa fa-refresh text-aqua"></i> Swap / Regenerate');
                    alert('Error regenerating question.');
                }
            });
        });

        // Single Question Removal
        $(document).on('click', '.btn_remove_single', function () {
            var qIndex = parseInt($(this).data('index'));
            if (confirm('Are you sure you want to remove Question ' + (qIndex + 1) + '?')) {
                current_generated_questions.splice(qIndex, 1);
                // Re-index question numbers
                $.each(current_generated_questions, function (idx, q) {
                    q.question_no = idx + 1;
                });
                renderQuestionsPreview(current_generated_questions);
                $('#badge_question_count').text(current_generated_questions.length + ' Questions');
            }
        });

        // Regenerate from Saved Paper (in Table or Modal)
        $(document).on('click', '.btn_regen_from_saved', function () {
            var paperId = $(this).data('id');
            loadAndRegenerateSavedPaper(paperId);
        });

        $('#modal_regen_from_view_btn').on('click', function () {
            if (current_viewed_paper_id) {
                $('#modal_view_paper').modal('hide');
                loadAndRegenerateSavedPaper(current_viewed_paper_id);
            }
        });

        function loadAndRegenerateSavedPaper(paperId) {
            // Switch to generate tab
            $('.nav-tabs a[href="#tab_generate"]').tab('show');
            $('#loading_spinner').show();
            $('#empty_placeholder').hide();
            $('#questions_preview_content').hide();

            $.ajax({
                url: base_url + 'admin/question_paper/view_modal/' + paperId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success' && res.data) {
                        var p = res.data;
                        $('#gen_class_id').val(p.class_id);
                        loadSections(p.class_id, p.section_id || 0);

                        $('#gen_subject_id').val(p.subject_id);
                        if (p.duration_minutes) $('#save_duration').val(p.duration_minutes);
                        if (p.total_marks) $('#save_total_marks').val(p.total_marks);
                        if (p.instructions) $('#save_instructions').val(p.instructions);
                        if (p.ai_suggestions) $('#gen_suggestions').val(p.ai_suggestions);

                        var selectedChapIds = p.chapter_ids ? p.chapter_ids.split(',') : [];

                        // Load chapters and select them
                        $.ajax({
                            type: "POST",
                            url: base_url + "admin/chapter/getChaptersBySubject",
                            data: {
                                'class_id': p.class_id,
                                'section_id': p.section_id || 0,
                                'subject_id': p.subject_id
                            },
                            dataType: "json",
                            success: function (data) {
                                if (data && data.length > 0) {
                                    var html = '';
                                    $.each(data, function (i, obj) {
                                        var isChecked = (selectedChapIds.length === 0 || selectedChapIds.indexOf(String(obj.id)) !== -1) ? 'checked' : '';
                                        var label = (obj.chapter_no ? '<strong>' + obj.chapter_no + ':</strong> ' : '') + obj.chapter_name;
                                        html += '<div class="checkbox" style="margin-top:4px; margin-bottom:4px;">';
                                        html += '<label><input type="checkbox" name="chapters[]" value="' + obj.id + '" ' + isChecked + '> ' + label + '</label>';
                                        html += '</div>';
                                    });
                                    $('#chapters_container').html(html);

                                    // Trigger question generation immediately
                                    setTimeout(function () {
                                        $('#btn_generate_questions').trigger('click');
                                    }, 200);
                                } else {
                                    $('#loading_spinner').hide();
                                    $('#empty_placeholder').show();
                                    alert('No chapters found for this subject.');
                                }
                            }
                        });
                    }
                },
                error: function () {
                    $('#loading_spinner').hide();
                    $('#empty_placeholder').show();
                    alert('Error loading saved paper details.');
                }
            });
        }

        // Save Question Paper click
        $('#btn_save_paper').on('click', function () {
            var title = $('#save_title').val();
            var total_marks = $('#save_total_marks').val();
            var duration = $('#save_duration').val();
            var instructions = $('#save_instructions').val();

            var class_id = $('#gen_class_id').val();
            var section_id = $('#gen_section_id').val();
            var subject_id = $('#gen_subject_id').val();
            var suggestions = $('#gen_suggestions').val();

            var chapter_ids = [];
            $('#chapters_container input[type="checkbox"]:checked').each(function () {
                chapter_ids.push($(this).val());
            });

            if (!title) {
                alert('Please enter a Question Paper Title!');
                return;
            }

            if (current_generated_questions.length === 0) {
                alert('No questions to save!');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: base_url + 'admin/question_paper/save',
                type: 'POST',
                data: {
                    title: title,
                    class_id: class_id,
                    section_id: section_id,
                    subject_id: subject_id,
                    chapter_ids: chapter_ids,
                    total_marks: total_marks,
                    duration_minutes: duration,
                    instructions: instructions,
                    suggestions: suggestions,
                    questions_json: JSON.stringify(current_generated_questions)
                },
                dataType: 'json',
                success: function (res) {
                    btn.prop('disabled', false).html('<i class="fa fa-check"></i> <?php echo $this->lang->line('save_question_paper'); ?>');
                    if (res.status === 'success') {
                        alert('Question Paper saved successfully!');
                        window.location.reload();
                    } else {
                        alert(res.message || 'Error saving question paper.');
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html('<i class="fa fa-check"></i> <?php echo $this->lang->line('save_question_paper'); ?>');
                    alert('Error saving question paper.');
                }
            });
        });

        // View Modal click
        $(document).on('click', '.btn_view_modal', function () {
            var id = $(this).data('id');
            current_viewed_paper_id = id;
            $('#modal_paper_title').text('Loading...');
            $('#modal_paper_body').html('<div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
            $('#modal_print_link').attr('href', base_url + 'admin/question_paper/print_paper/' + id);
            $('#modal_view_paper').modal('show');

            $.ajax({
                url: base_url + 'admin/question_paper/view_modal/' + id,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success' && res.data) {
                        var p = res.data;
                        $('#modal_paper_title').text(p.title);
                        $('#modal_print_link').attr('href', base_url + 'admin/question_paper/print_paper/' + p.id);

                        var html = '<div style="margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">';
                        html += '<strong>Class:</strong> ' + p.class_name + (p.section_name ? ' (' + p.section_name + ')' : '') + ' &nbsp;|&nbsp; ';
                        html += '<strong>Subject:</strong> ' + p.subject_name + ' &nbsp;|&nbsp; ';
                        html += '<strong>Max Marks:</strong> ' + p.total_marks + ' &nbsp;|&nbsp; ';
                        html += '<strong>Time:</strong> ' + p.duration_minutes + ' Mins';
                        if (p.instructions) {
                            html += '<br><small class="text-muted"><strong>Instructions:</strong> ' + p.instructions + '</small>';
                        }
                        html += '</div>';

                        if (p.questions && p.questions.length > 0) {
                            html += '<div class="list-group">';
                            $.each(p.questions, function (idx, q) {
                                var typeClass = 'label-info';
                                if (q.question_type === 'MCQ') typeClass = 'label-primary';
                                else if (q.question_type === 'Numerical') typeClass = 'label-warning';
                                else if (q.question_type === 'Long') typeClass = 'label-danger';

                                html += '<div class="list-group-item" style="margin-bottom:10px; border-radius:4px; border-left:4px solid #3c8dbc;">';
                                html += '<div style="display:flex; justify-content:space-between; align-items:flex-start;">';
                                html += '<div><strong style="font-size:14.5px;">Q' + q.question_no + '.</strong> <span style="font-size:14px;">' + q.question_text + '</span></div>';
                                html += '<div style="white-space:nowrap; margin-left:10px;">';
                                html += '<span class="label ' + typeClass + '" style="margin-right:4px;">' + q.question_type + '</span>';
                                html += '<span class="badge bg-blue">' + q.marks + ' Marks</span>';
                                html += '</div>';
                                html += '</div>';

                                if (q.chapter_name || q.chapter) {
                                    var chap = q.chapter_name || q.chapter;
                                    html += '<div style="margin-top:4px;"><small class="text-muted"><i class="fa fa-bookmark text-purple"></i> ' + chap + '</small></div>';
                                }

                                if (q.options_json) {
                                    var opts = [];
                                    try { opts = JSON.parse(q.options_json); } catch (e) { }
                                    if (opts && opts.length > 0) {
                                        html += '<div style="margin-top:8px; padding-left:15px; display:flex; flex-wrap:wrap; gap:8px;">';
                                        $.each(opts, function (oIdx, opt) {
                                            var optLabel = String.fromCharCode(65 + oIdx);
                                            html += '<div style="flex:1 1 45%; font-size:13px;"><strong>(' + optLabel + ')</strong> ' + opt + '</div>';
                                        });
                                        html += '</div>';
                                    }
                                }

                                if (q.answer_key) {
                                    html += '<div style="margin-top:8px; font-size:12.5px; color:#1b5e20; background:#e8f5e9; padding:5px 10px; border-radius:3px; border-left:3px solid #4caf50;"><strong><i class="fa fa-check-circle"></i> Correct Answer:</strong> ' + q.answer_key + '</div>';
                                }

                                if (q.step_marking_json) {
                                    var steps = [];
                                    try { steps = JSON.parse(q.step_marking_json); } catch (e) { }
                                    if (steps && steps.length > 0) {
                                        html += '<div style="margin-top:8px; font-size:12px; background:#fffde7; padding:6px 10px; border-radius:3px; border-left:3px solid #fbc02d;">';
                                        html += '<strong><i class="fa fa-list-ol text-orange"></i> Step Marking:</strong><ul style="margin:4px 0 0 18px; padding:0;">';
                                        $.each(steps, function (sIdx, st) {
                                            var sText = st.step || st;
                                            var sMark = st.marks ? ' <span class="badge bg-yellow">[' + st.marks + ' M]</span>' : '';
                                            html += '<li>' + sText + sMark + '</li>';
                                        });
                                        html += '</ul></div>';
                                    }
                                }

                                if (q.explanation) {
                                    html += '<div style="margin-top:8px; font-size:12px; color:#0d47a1; background:#e3f2fd; padding:6px 10px; border-radius:3px; border-left:3px solid #2196f3;"><strong><i class="fa fa-lightbulb-o text-yellow"></i> Explanation:</strong> ' + q.explanation + '</div>';
                                }

                                html += '</div>';
                            });
                            html += '</div>';
                        }
                        $('#modal_paper_body').html(html);
                    }
                }
            });
        });
    });

    function loadSections(class_id, section_id) {
        $('#gen_section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        if (class_id) {
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    var options = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                    $.each(data, function (i, obj) {
                        var sel = (section_id == obj.id) ? "selected" : "";
                        options += "<option value='" + obj.id + "' " + sel + ">" + obj.section + "</option>";
                    });
                    $('#gen_section_id').html(options);
                }
            });
        }
    }

    function loadChapters() {
        var class_id = $('#gen_class_id').val();
        var section_id = $('#gen_section_id').val();
        var subject_id = $('#gen_subject_id').val();

        if (!class_id || !subject_id) {
            $('#chapters_container').html('<p class="text-muted" style="margin:0; font-size:12px;">Please select Class & Subject first...</p>');
            return;
        }

        $('#chapters_container').html('<p class="text-muted" style="margin:0; font-size:12px;"><i class="fa fa-spinner fa-spin"></i> Loading chapters...</p>');

        $.ajax({
            type: "POST",
            url: base_url + "admin/chapter/getChaptersBySubject",
            data: {
                'class_id': class_id,
                'section_id': section_id,
                'subject_id': subject_id
            },
            dataType: "json",
            success: function (data) {
                if (data && data.length > 0) {
                    var html = '';
                    $.each(data, function (i, obj) {
                        var label = (obj.chapter_no ? '<strong>' + obj.chapter_no + ':</strong> ' : '') + obj.chapter_name;
                        html += '<div class="checkbox" style="margin-top:4px; margin-bottom:4px;">';
                        html += '<label><input type="checkbox" name="chapters[]" value="' + obj.id + '" checked> ' + label + '</label>';
                        html += '</div>';
                    });
                    $('#chapters_container').html(html);
                } else {
                    $('#chapters_container').html('<p class="text-danger" style="margin:0; font-size:12px;">No chapters found for this selection. You can add chapters under Academics > Chapters.</p>');
                }
            }
        });
    }

    function renderQuestionsPreview(questions) {
        var html = '';
        var total_marks_calc = 0;

        $.each(questions, function (idx, q) {
            var marks = parseFloat(q.marks) || 1;
            total_marks_calc += marks;

            var typeClass = 'label-info';
            if (q.question_type === 'MCQ') typeClass = 'label-primary';
            else if (q.question_type === 'Numerical') typeClass = 'label-warning';
            else if (q.question_type === 'Long') typeClass = 'label-danger';

            html += '<div class="box box-default" style="margin-bottom:15px; border-left: 4px solid #00c0ef; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">';
            html += '<div class="box-body" style="padding:12px 18px;">';

            // Top Header: Question No, Badges, and Per-Question Action Buttons
            html += '<div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">';
            html += '<div style="font-size:14.5px; line-height: 1.4; flex-grow:1; padding-right:10px;"><strong>Q' + q.question_no + '.</strong> ' + q.question_text + '</div>';
            html += '<div style="white-space:nowrap; display:flex; align-items:center; gap:5px;">';
            html += '<span class="label ' + typeClass + '" style="font-size:11px;">' + q.question_type + '</span>';
            if (q.difficulty) {
                html += '<span class="label label-default" style="font-size:10px;">' + q.difficulty + '</span>';
            }
            html += '<span class="badge bg-blue" style="font-size:11px;">' + marks + ' Marks</span>';
            html += '<button type="button" class="btn btn-default btn-xs btn_regen_single" data-index="' + idx + '" title="Regenerate / Swap this specific question" style="margin-left:6px; font-weight:600;"><i class="fa fa-refresh text-aqua"></i> Swap</button>';
            html += '<button type="button" class="btn btn-default btn-xs btn_remove_single" data-index="' + idx + '" title="Remove question"><i class="fa fa-trash text-danger"></i></button>';
            html += '</div>';
            html += '</div>';

            // Chapter tag if available
            if (q.chapter) {
                html += '<div style="margin-top:4px;"><small class="text-muted"><i class="fa fa-bookmark text-purple"></i> ' + q.chapter + '</small></div>';
            }

            // Options grid
            if (q.options && q.options.length > 0) {
                html += '<div style="margin-top:10px; padding-left:15px; display:flex; flex-wrap:wrap; gap:10px;">';
                $.each(q.options, function (oIdx, opt) {
                    var optLabel = String.fromCharCode(65 + oIdx);
                    html += '<div style="flex: 1 1 45%; font-size:13.5px; background:#f9f9f9; padding:5px 10px; border-radius:4px; border:1px solid #eee;"><strong>(' + optLabel + ')</strong> ' + opt + '</div>';
                });
                html += '</div>';
            }

            // Answer Key
            if (q.answer_key) {
                html += '<div style="margin-top:10px; font-size:12.5px; color:#1b5e20; background:#e8f5e9; padding:6px 12px; border-radius:3px; border-left:3px solid #4caf50;">';
                html += '<strong><i class="fa fa-check-circle"></i> Correct Answer:</strong> ' + q.answer_key;
                html += '</div>';
            }

            // Step Marking Scheme if present
            if (q.step_marking && q.step_marking.length > 0) {
                html += '<div style="margin-top:8px; font-size:12px; background:#fffde7; padding:8px 12px; border-radius:3px; border-left:3px solid #fbc02d;">';
                html += '<strong><i class="fa fa-list-ol text-orange"></i> Step-by-Step Marking Scheme:</strong>';
                html += '<ul style="margin:4px 0 0 18px; padding:0;">';
                $.each(q.step_marking, function (sIdx, st) {
                    var sText = st.step || st;
                    var sMark = st.marks ? ' <span class="badge bg-yellow">[' + st.marks + ' Marks]</span>' : '';
                    html += '<li>' + sText + sMark + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }

            // Explanation / Solution if present
            if (q.explanation) {
                html += '<div style="margin-top:8px; font-size:12.5px; color:#0d47a1; background:#e3f2fd; padding:6px 12px; border-radius:3px; border-left:3px solid #2196f3;">';
                html += '<strong><i class="fa fa-lightbulb-o text-yellow"></i> Explanation / Solution:</strong> ' + q.explanation;
                html += '</div>';
            }

            html += '</div></div>';
        });

        $('#questions_rendered_list').html(html);
        $('#save_total_marks').val(total_marks_calc);
    }
</script>
