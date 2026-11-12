<?php
$rec = $viewmaintenance;

// Parse parts & labour JSON
$parts_data  = (!empty($rec->parts_list))  ? json_decode($rec->parts_list,  true) : [];
$labour_data = (!empty($rec->labour_list)) ? json_decode($rec->labour_list, true) : [];

// Status badge
$statusColors = [
    'Completed'   => 'success',
    'In Progress' => 'warning',
    'Pending'     => 'default',
    'Active'      => 'primary',
    'Inactive'    => 'danger',
];
$statusColor = isset($statusColors[$rec->status]) ? $statusColors[$rec->status] : 'default';
?>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12" style="padding:15px;">

        <!-- ── Row 1: Vehicle / Odometer / Service Type / Status ── -->
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Vehicle: </b>
                    <span><?php echo htmlspecialchars($rec->vehicle_no . ' (' . $rec->vehicle_model . ')'); ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Odometer Reading: </b>
                    <span><?php echo !empty($rec->odometer_reading) ? htmlspecialchars($rec->odometer_reading) . ' km' : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Service Type: </b>
                    <span><?php echo !empty($rec->service_type) ? htmlspecialchars($rec->service_type) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Status: </b>
                    <span class="label label-<?php echo $statusColor; ?>"><?php echo htmlspecialchars($rec->status); ?></span>
                </div>
            </div>
        </div>

        <!-- ── Row 2: Dates ── -->
        <div class="row" style="margin-top:10px;">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Last Service Date: </b>
                    <span><?php echo !empty($rec->last_service_date) ? $this->customlib->dateformat($rec->last_service_date) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Next Service Date: </b>
                    <span><?php echo !empty($rec->next_service_date) ? $this->customlib->dateformat($rec->next_service_date) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Insurance Expiry: </b>
                    <span><?php echo !empty($rec->insurance_expired_date) ? $this->customlib->dateformat($rec->insurance_expired_date) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Tax Expiry: </b>
                    <span><?php echo !empty($rec->tax_expired_date) ? $this->customlib->dateformat($rec->tax_expired_date) : '-'; ?></span>
                </div>
            </div>
        </div>

        <!-- ── Row 3: Invoice Ref / Next Service Due ── -->
        <div class="row" style="margin-top:10px;">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Invoice Reference: </b>
                    <span><?php echo !empty($rec->invoice_reference) ? htmlspecialchars($rec->invoice_reference) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="route-text"><b>Next Service Due: </b>
                    <span><?php echo !empty($rec->next_service_due) ? $this->customlib->dateformat($rec->next_service_due) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="route-text"><b>Total Cost: </b>
                    <span><strong><?php echo number_format((float)$rec->total_cost, 2); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- ── Parts Details ── -->
        <div class="row" style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
            <div class="col-lg-12">
                <b>Parts Details</b>
            </div>
        </div>
        <div class="row" style="margin-top:8px;">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="route-text"><b>Vendor Name: </b>
                    <span><?php echo !empty($rec->parts_vendor_name) ? htmlspecialchars($rec->parts_vendor_name) : '-'; ?></span>
                </div>
                <div class="route-text" style="margin-top:6px;"><b>Vendor Details: </b>
                    <span><?php echo !empty($rec->parts_vendor_details) ? nl2br(htmlspecialchars($rec->parts_vendor_details)) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <?php if (!empty($parts_data)): ?>
                <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Part Name</th>
                            <th class="text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pTotal = 0; foreach ($parts_data as $i => $p):
                            $price = (float)($p['price'] ?? 0); $pTotal += $price; ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($p['name'] ?? ''); ?></td>
                            <td class="text-right"><?php echo number_format($price, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Parts Total</th>
                            <th class="text-right"><?php echo number_format($pTotal, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <?php else: ?>
                    <span class="text-muted">No parts recorded.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Labour Details ── -->
        <div class="row" style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
            <div class="col-lg-12">
                <b>Labour Details</b>
                <?php if ($rec->same_as_parts): ?>
                    <span class="label label-info" style="margin-left:8px;">Same as Parts</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="row" style="margin-top:8px;">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="route-text"><b>Garage / Service Center: </b>
                    <span><?php echo !empty($rec->labour_garage) ? htmlspecialchars($rec->labour_garage) : '-'; ?></span>
                </div>
                <div class="route-text" style="margin-top:6px;"><b>Vendor Details: </b>
                    <span><?php echo !empty($rec->labour_vendor_details) ? nl2br(htmlspecialchars($rec->labour_vendor_details)) : '-'; ?></span>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <?php if (!empty($labour_data) && !$rec->same_as_parts): ?>
                <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service Description</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $lTotal = 0; foreach ($labour_data as $i => $l):
                            $cost = (float)($l['cost'] ?? 0); $lTotal += $cost; ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($l['desc'] ?? ''); ?></td>
                            <td class="text-right"><?php echo number_format($cost, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Labour Total</th>
                            <th class="text-right"><?php echo number_format($lTotal, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
                <?php elseif ($rec->same_as_parts): ?>
                    <span class="text-muted">Labour cost is same as parts cost.</span>
                <?php else: ?>
                    <span class="text-muted">No labour services recorded.</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Description / Notes ── -->
        <?php if (!empty($rec->description_notes)): ?>
        <div class="row" style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">
            <div class="col-lg-12">
                <div class="route-text pb10"><b>Description / Notes: </b>
                    <span><?php echo nl2br(htmlspecialchars($rec->description_notes)); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Invoice File ── -->
        <?php if (!empty($rec->invoice_file)): ?>
        <div class="row" style="margin-top:10px;">
            <div class="col-lg-12">
                <b>Invoice: </b>
                <a href="<?php echo site_url('admin/maintenance/download/' . $rec->id); ?>" class="btn btn-default btn-sm" title="Download Invoice">
                    <i class="fa fa-download"></i> <?php echo $this->media_storage->fileview($rec->invoice_file); ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
