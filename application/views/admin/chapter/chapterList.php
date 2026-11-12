<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_chapter'); ?></h3>
                    </div>
                    <form id="form1" action="<?php echo site_url('admin/chapter') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg');
                                $this->session->unset_userdata('msg'); ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>

                            <div class="form-group">
                                <label for="class_id"><?php echo $this->lang->line('class'); ?></label> <small class="req">*</small>
                                <select id="class_id" name="class_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($classlist as $class) { ?>
                                        <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id', $class['id']); ?>>
                                            <?php echo $class['class'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="section_id"><?php echo $this->lang->line('section'); ?></label>
                                <select id="section_id" name="section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="subject_id"><?php echo $this->lang->line('subject'); ?></label> <small class="req">*</small>
                                <select id="subject_id" name="subject_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($subjectlist as $subject) { ?>
                                        <option value="<?php echo $subject['id'] ?>" <?php echo set_select('subject_id', $subject['id']); ?>>
                                            <?php echo $subject['name'] . ($subject['code'] ? ' (' . $subject['code'] . ')' : ''); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('subject_id'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="chapter_no"><?php echo $this->lang->line('chapter_no'); ?></label>
                                <input id="chapter_no" name="chapter_no" placeholder="e.g. 1 or Unit 1" type="text" class="form-control" value="<?php echo set_value('chapter_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('chapter_no'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="chapter_name"><?php echo $this->lang->line('chapter_name'); ?></label> <small class="req">*</small>
                                <input id="chapter_name" name="chapter_name" placeholder="Enter chapter name" type="text" class="form-control" value="<?php echo set_value('chapter_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('chapter_name'); ?></span>
                            </div>

                            <div class="form-group">
                                <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Key topics or overview..."><?php echo set_value('description'); ?></textarea>
                                <span class="text-danger"></span>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="box box-primary" id="chapter_list_box">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('chapter_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class='d-flex gap-0-5 text-right float-right'>
                                <a class="btn btn-primary btn-xs pull-right" title="<?php echo $this->lang->line('print'); ?>" id="print" onclick="printDiv()"><i class="fa fa-print"></i></a>
                                <a class="btn btn-primary btn-xs pull-right" title="<?php echo $this->lang->line('export'); ?>" id="btnExport" onclick="fnExcelReport();"><i class="fa fa-file-excel-o"></i></a>
                            </div>

                            <table class="table table-striped table-hover" id="headerTable">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('subject'); ?></th>
                                        <th><?php echo $this->lang->line('chapter_no'); ?></th>
                                        <th><?php echo $this->lang->line('chapter_name'); ?></th>
                                        <th class="text-right no_print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($chapterList)) {
                                        foreach ($chapterList as $chapter) { ?>
                                            <tr>
                                                <td><?php echo $chapter->class_name . ($chapter->section_name ? ' (' . $chapter->section_name . ')' : ''); ?></td>
                                                <td><?php echo $chapter->subject_name; ?></td>
                                                <td><span class="label label-default"><?php echo !empty($chapter->chapter_no) ? $chapter->chapter_no : '-'; ?></span></td>
                                                <td>
                                                    <strong><?php echo $chapter->chapter_name; ?></strong>
                                                    <?php if (!empty($chapter->description)) { ?>
                                                        <br><small class="text-muted"><?php echo character_limiter($chapter->description, 50); ?></small>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right no_print">
                                                    <a href="<?php echo base_url(); ?>admin/chapter/edit/<?php echo $chapter->id; ?>" class="btn btn-primary btn-xs no_print displayinline" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo base_url(); ?>admin/chapter/delete/<?php echo $chapter->id; ?>" class="btn btn-primary btn-xs no_print displayinline" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                                        <i class="fa fa-remove"></i>
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
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var post_class_id = '<?php echo set_value('class_id', 0); ?>';
        var post_section_id = '<?php echo set_value('section_id', 0); ?>';

        if (post_class_id > 0) {
            getSectionByClass(post_class_id, post_section_id);
        }

        $(document).on('change', '#class_id', function (e) {
            var class_id = $(this).val();
            getSectionByClass(class_id, 0);
        });
    });

    function getSectionByClass(class_id, section_id) {
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        if (class_id !== "" && class_id !== 0) {
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
                    $('#section_id').html(options);
                }
            });
        }
    }

    function printDiv() {
        $(".no_print").css("display", "none");
        var divElements = document.getElementById('chapter_list_box').innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML = "<html><head><title></title></head><body>" + divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;
        location.reload(true);
    }

    function fnExcelReport() {
        var tab_text = "<table border='2px'><tr>";
        var tab = document.getElementById('headerTable');
        for (var j = 0; j < tab.rows.length; j++) {
            tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
        }
        tab_text = tab_text + "</table>";
        tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, "");
        var sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));
        return (sa);
    }
</script>
