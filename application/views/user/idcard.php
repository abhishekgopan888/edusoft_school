<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-id-card-o"></i> <?php echo $this->lang->line('student_id_card'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('student_id_card'); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-primary btn-sm" onclick="printIDCard()"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
                        </div>
                    </div>
                    <div class="box-body" id="idcard_print_area">
                        <?php if (isset($students) && !empty($students)) { 
                            $this->load->view('admin/certificate/generatemultiple', array('students' => $students, 'id_card' => $id_card, 'sch_settingdata' => $sch_settingdata, 'sch_setting' => $sch_setting));
                        } else { ?>
                            <div class="alert alert-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function printIDCard() {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({ "position": "absolute", "top": "-1000000px" });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        frameDoc.document.write('<html><head><title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/idcard.css">');
        frameDoc.document.write('</head><body>');
        frameDoc.document.write($('#idcard_print_area').html());
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);
    }
</script>
