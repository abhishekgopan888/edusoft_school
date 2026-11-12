<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('routes', 'can_add')) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary" >
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('create_route'); ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/route/create') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php
if (isset($error_message)) {
        echo "<div class='alert alert-danger'>" . $error_message . "</div>";
    }
    ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('route_title'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="route_title" name="route_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('route_title'); ?>" />
                                    <span class="text-danger"><?php echo form_error('route_title'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="vehicle"><?php echo $this->lang->line('vehicle'); ?></label>
                                    <select id="vehicle" name="vehicle[]" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php if (!empty($vehiclelist)) {
                                            foreach ($vehiclelist as $vehicle) { ?>
                                                <option value="<?php echo $vehicle['id']; ?>" <?php if (set_value('vehicle[]') == $vehicle['id']) echo 'selected'; ?>>
                                                    <?php echo $vehicle['vehicle_no']; ?>
                                                </option>
                                        <?php } } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('vehicle[]'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="pickup_point"><?php echo $this->lang->line('pickup_point'); ?></label>
                                    <div id="pickup_point_wrapper">
                                        <div class="pickup-point-row" style="margin-bottom: 8px;">
                                            <div class="input-group">
                                                <select name="pickup_point[]" class="form-control pickup_point_select">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php if (!empty($pickup_point_list)) {
                                                        foreach ($pickup_point_list as $pickup_point) { ?>
                                                            <option value="<?php echo $pickup_point['id']; ?>" <?php if (set_value('pickup_point[]') == $pickup_point['id']) echo 'selected'; ?>>
                                                                <?php echo $pickup_point['name']; ?>
                                                            </option>
                                                    <?php } } ?>
                                                </select>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-primary btn-open-pickup-map" title="<?php echo $this->lang->line('add') . ' ' . $this->lang->line('pickup_point'); ?>">
                                                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-remove-pickup-row" style="display:none;" title="<?php echo $this->lang->line('remove'); ?>">
                                                        <i class="fa fa-remove"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <button type="button" id="btn_add_more_pickup" class="btn btn-default btn-xs">
                                            <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_more'); ?>
                                        </button>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('pickup_point[]'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('routes', 'can_add')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <div class="box box-primary" id="route">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('route_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                            <div class="pull-right">
                            </div>
                        </div>
                        <div class="mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('route_list'); ?></div>
                            <div class="table-responsive overflow-visible">
                                <table class="table table-striped table-bordered table-hover example" data-export-title="<?php echo $this->lang->line('route_list');?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('route_title'); ?></th>
                                            <th><?php echo $this->lang->line('pickup_point'); ?></th>
                                            <th><?php echo $this->lang->line('vehicle'); ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($listroute)) {
    ?>

                                            <?php
} else {
    $count = 1;
    foreach ($listroute as $data) {
        ?>
                                                <tr>
                                                    <td class="mailbox-name"> <?php echo $data['route_title'] ?></td>
                                                    <td class="mailbox-name"> <?php echo !empty($data['pickup_point_names']) ? $data['pickup_point_names'] : ''; ?></td>
                                                    <td class="mailbox-name"> <?php echo !empty($data['vehicles']) ? $data['vehicles'] : ''; ?></td>
                                                    <td class="mailbox-date pull-right no-print">
                                                        <?php if ($this->rbac->hasPrivilege('routes', 'can_edit')) {?>
                                                            <a href="<?php echo base_url(); ?>admin/route/edit/<?php echo $data['id'] ?>" class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                        <?php }if ($this->rbac->hasPrivilege('routes', 'can_delete')) {?>
                                                            <a href="<?php echo base_url(); ?>admin/route/delete/<?php echo $data['id'] ?>"class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php }?>
                                                    </td>
                                                </tr>
                                                <?php
}
    $count++;
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });
    });
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function printDiv(elem) {
        Popup(jQuery(elem).html());
    }

    function Popup(data)
    {
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);

        return true;
    }
</script>

<?php $this->load->view('admin/route/_pickup_modal'); ?>