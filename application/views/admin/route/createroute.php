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
                                    <label for="start_pickup_point_id"><i class="fa fa-map-marker text-success"></i> <?php echo $this->lang->line('pickup_point'); ?> (Start Location)</label><small class="req"> *</small>
                                    <div class="input-group">
                                        <select id="start_pickup_point_id" name="start_pickup_point_id" class="form-control pickup_point_select">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($pickup_point_list)) {
                                                foreach ($pickup_point_list as $pickup_point) { ?>
                                                    <option value="<?php echo $pickup_point['id']; ?>" <?php if (set_value('start_pickup_point_id') == $pickup_point['id']) echo 'selected'; ?>>
                                                        <?php echo $pickup_point['name']; ?>
                                                    </option>
                                            <?php } } ?>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-success btn-open-pickup-map" data-title="Add / Pick Pickup Point (Start Location)" title="Pick on Map">
                                                <i class="fa fa-map-marker"></i> Pick on Map
                                            </button>
                                        </span>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('start_pickup_point_id'); ?></span>
                                </div>

                                <div class="form-group">
                                    <label><i class="fa fa-road text-primary"></i> Between Stops (Intermediate Stops)</label>
                                    <div id="between_stops_wrapper">
                                        <!-- Dynamic between stop rows -->
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <button type="button" id="btn_add_more_between" class="btn btn-default btn-xs">
                                            <i class="fa fa-plus"></i> Add Between Stop
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="end_drop_point_id"><i class="fa fa-flag-checkered text-danger"></i> Drop Point (Destination / End Location)</label>
                                    <div class="input-group">
                                        <select id="end_drop_point_id" name="end_drop_point_id" class="form-control pickup_point_select">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php if (!empty($pickup_point_list)) {
                                                foreach ($pickup_point_list as $pickup_point) { ?>
                                                    <option value="<?php echo $pickup_point['id']; ?>" <?php if (set_value('end_drop_point_id') == $pickup_point['id']) echo 'selected'; ?>>
                                                        <?php echo $pickup_point['name']; ?>
                                                    </option>
                                            <?php } } ?>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-danger btn-open-pickup-map" data-title="Add / Pick Drop Point (Destination)" title="Pick on Map">
                                                <i class="fa fa-map-marker"></i> Pick on Map
                                            </button>
                                        </span>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('end_drop_point_id'); ?></span>
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
                                            <th><i class="fa fa-map-marker text-success"></i> Pickup Point (Start)</th>
                                            <th><i class="fa fa-road text-primary"></i> Between Stops</th>
                                            <th><i class="fa fa-flag-checkered text-danger"></i> Drop Point (End)</th>
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
        $start_display = !empty($data['start_point_name']) ? $data['start_point_name'] : (!empty($data['pickup_point_names']) ? explode(',', $data['pickup_point_names'])[0] : '-');
        $drop_display  = !empty($data['drop_point_name']) ? $data['drop_point_name'] : '-';
        ?>
                                                <tr>
                                                    <td class="mailbox-name"> <?php echo $data['route_title'] ?></td>
                                                    <td class="mailbox-name">
                                                        <?php if (!empty($start_display) && $start_display !== '-') { ?>
                                                            <span class="label label-success"><i class="fa fa-map-marker"></i></span> <?php echo htmlspecialchars($start_display); ?>
                                                        <?php } else { ?>
                                                            <span class="text-muted">-</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="mailbox-name">
                                                        <?php
                                                        if (!empty($data['between_stops_array'])) {
                                                            foreach ($data['between_stops_array'] as $b_idx => $b_name) {
                                                                echo '<span class="label label-info" style="display:inline-block; margin:2px 2px; font-weight:normal; font-size:11px;"><i class="fa fa-map-pin"></i> ' . ($b_idx + 1) . '. ' . htmlspecialchars($b_name) . '</span> ';
                                                            }
                                                        } elseif (!empty($data['between_stops_names'])) {
                                                            echo htmlspecialchars($data['between_stops_names']);
                                                        } else {
                                                            echo '<span class="text-muted">-</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="mailbox-name">
                                                        <?php if (!empty($drop_display) && $drop_display !== '-') { ?>
                                                            <span class="label label-danger"><i class="fa fa-flag-checkered"></i></span> <?php echo htmlspecialchars($drop_display); ?>
                                                        <?php } else { ?>
                                                            <span class="text-muted">-</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="mailbox-name">
                                                        <?php if (!empty($data['vehicles'])) { ?>
                                                            <span class="label label-default" style="font-weight:normal; font-size:11px;"><i class="fa fa-bus"></i> <?php echo htmlspecialchars($data['vehicles']); ?></span>
                                                        <?php } else { ?>
                                                            <span class="text-muted">-</span>
                                                        <?php } ?>
                                                    </td>
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