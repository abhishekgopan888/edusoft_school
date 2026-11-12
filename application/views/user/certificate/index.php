<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('certificate'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('certificate') . ' ' . $this->lang->line('list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('certificate') . ' ' . $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('background_image'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($certificateList)) { ?>
                                        <tr>
                                            <td colspan="3" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                    <?php } else {
                                        foreach ($certificateList as $certificate) { ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $certificate->certificate_name; ?></td>
                                                <td class="mailbox-name">
                                                    <?php if (!empty($certificate->background_image)) { ?>
                                                        <img src="<?php echo $this->media_storage->getImageURL('uploads/certificate/' . $certificate->background_image); ?>" width="40" height="40" class="img-thumbnail" />
                                                    <?php } else { ?>
                                                        <span class="label label-default"><?php echo $this->lang->line('none'); ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-default btn-xs print_certificate" data-toggle="tooltip" title="<?php echo $this->lang->line('print'); ?>" data-certificate_id="<?php echo $certificate->id; ?>">
                                                        <i class="fa fa-print"></i> <?php echo $this->lang->line('view') . ' & ' . $this->lang->line('print'); ?>
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

<div class="modal fade" id="certificateModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width:90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('view') . ' ' . $this->lang->line('certificate'); ?></h4>
            </div>
            <div class="modal-body" id="certificate_detail">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="printCertContent()"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('click', '.print_certificate', function () {
        var certificate_id = $(this).data('certificate_id');
        $.ajax({
            url: '<?php echo base_url(); ?>user/certificate/printcertificate',
            type: 'POST',
            data: {certificate_id: certificate_id},
            success: function (data) {
                $('#certificate_detail').html(data);
                $('#certificateModal').modal('show');
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
        frameDoc.document.write('<html><head><title></title>');
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
