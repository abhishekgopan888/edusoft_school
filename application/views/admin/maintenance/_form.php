<?php
// $rec is NULL for add, or the maintenance row object for edit
$isEdit = !empty($rec);
$prefix = $isEdit ? 'edit' : 'add';

// Parse stored JSON
$parts_data  = ($isEdit && $rec->parts_list)  ? json_decode($rec->parts_list,  true) : [];
$labour_data = ($isEdit && $rec->labour_list)  ? json_decode($rec->labour_list, true) : [];
if (empty($parts_data))  $parts_data  = [['name'=>'','price'=>'']];
if (empty($labour_data)) $labour_data = [['desc'=>'','cost'=>'']];
?>
<?php if ($isEdit): ?>
<input type="hidden" name="id" value="<?php echo $rec->id; ?>">
<?php endif; ?>

<!-- ===== TOP FIELDS ===== -->
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('select_vehicle'); ?> <small class="req">*</small></label>
            <select name="vehicle_id" class="form-control" required>
                <option value=""><?php echo $this->lang->line('select'); ?></option>
                <?php foreach ($listVehicle as $v): ?>
                    <option value="<?php echo $v['id']; ?>" <?php echo ($isEdit && $rec->vehicle_id == $v['id']) ? 'selected' : ''; ?>>
                        <?php echo $v['vehicle_no'] . " (" . $v['vehicle_model'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('last_service_date'); ?></label>
            <input type="text" name="last_service_date" class="form-control date" readonly autocomplete="off"
                   value="<?php echo ($isEdit && $rec->last_service_date) ? date($this->customlib->getSchoolDateFormat(), strtotime($rec->last_service_date)) : ''; ?>" />
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('odometer_reading'); ?></label>
            <input type="text" name="odometer_reading" class="form-control" placeholder="12000"
                   value="<?php echo $isEdit ? $rec->odometer_reading : ''; ?>" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('service_type'); ?></label>
            <select name="service_type" class="form-control">
                <option value="">-- Select Service Type --</option>
                <option value="Oil Change" <?php echo ($isEdit && $rec->service_type=='Oil Change') ? 'selected':''; ?>>Oil Change</option>
                <option value="Tyre Replacement" <?php echo ($isEdit && $rec->service_type=='Tyre Replacement') ? 'selected':''; ?>>Tyre Replacement</option>
                <option value="Brake Service" <?php echo ($isEdit && $rec->service_type=='Brake Service') ? 'selected':''; ?>>Brake Service</option>
                <option value="Engine Repair" <?php echo ($isEdit && $rec->service_type=='Engine Repair') ? 'selected':''; ?>>Engine Repair</option>
                <option value="Electrical" <?php echo ($isEdit && $rec->service_type=='Electrical') ? 'selected':''; ?>>Electrical</option>
                <option value="AC Service" <?php echo ($isEdit && $rec->service_type=='AC Service') ? 'selected':''; ?>>AC Service</option>
                <option value="General Service" <?php echo ($isEdit && $rec->service_type=='General Service') ? 'selected':''; ?>>General Service</option>
                <option value="Other" <?php echo ($isEdit && $rec->service_type=='Other') ? 'selected':''; ?>>Other</option>
            </select>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('status'); ?> <small class="req">*</small></label>
            <select name="status" class="form-control" required>
                <option value="Active"      <?php echo ($isEdit && $rec->status=='Active')      ? 'selected':''; ?>>Active</option>
                <option value="Pending"     <?php echo ($isEdit && $rec->status=='Pending')     ? 'selected':''; ?>>Pending</option>
                <option value="In Progress" <?php echo ($isEdit && $rec->status=='In Progress') ? 'selected':''; ?>>In Progress</option>
                <option value="Completed"   <?php echo ($isEdit && $rec->status=='Completed')   ? 'selected':''; ?>>Completed</option>
                <option value="Inactive"    <?php echo ($isEdit && $rec->status=='Inactive')    ? 'selected':''; ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('insurance_expired_date'); ?></label>
            <input type="text" name="insurance_expired_date" class="form-control date" readonly autocomplete="off"
                   value="<?php echo ($isEdit && $rec->insurance_expired_date) ? date($this->customlib->getSchoolDateFormat(), strtotime($rec->insurance_expired_date)) : ''; ?>" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('next_service_date'); ?></label>
            <input type="text" name="next_service_date" class="form-control date" readonly autocomplete="off"
                   value="<?php echo ($isEdit && $rec->next_service_date) ? date($this->customlib->getSchoolDateFormat(), strtotime($rec->next_service_date)) : ''; ?>" />
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('tax_expired_date'); ?></label>
            <input type="text" name="tax_expired_date" class="form-control date" readonly autocomplete="off"
                   value="<?php echo ($isEdit && $rec->tax_expired_date) ? date($this->customlib->getSchoolDateFormat(), strtotime($rec->tax_expired_date)) : ''; ?>" />
        </div>
    </div>
</div>

<hr style="margin:10px 0;">

<!-- ===== PARTS + LABOUR SIDE BY SIDE ===== -->
<div class="row">
    <!-- PARTS DETAILS -->
    <div class="col-sm-6">
        <div class="maint-panel" id="<?php echo $prefix; ?>_parts_panel">
            <div class="maint-section-title">
                <i class="fa fa-circle-o" style="color:#2980b9"></i> Parts Details
                <span class="maint-total-badge blue" id="<?php echo $prefix; ?>_parts_total_badge">0.00</span>
            </div>
            <div class="form-group">
                <label>Vendor Name <small class="req">*</small></label>
                <input type="text" name="parts_vendor_name" id="<?php echo $prefix; ?>_parts_vendor_name" class="form-control" placeholder="Supplier Name"
                       value="<?php echo $isEdit ? htmlspecialchars($rec->parts_vendor_name ?? '') : ''; ?>" />
            </div>
            <div class="form-group">
                <label>Vendor Details</label>
                <textarea name="parts_vendor_details" id="<?php echo $prefix; ?>_parts_vendor_details" class="form-control" rows="2" placeholder="Address / Contact"><?php echo $isEdit ? htmlspecialchars($rec->parts_vendor_details ?? '') : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Parts List <small class="req">*</small></label>
                <div id="<?php echo $prefix; ?>_parts_body">
                    <?php foreach ($parts_data as $p): ?>
                    <div class="parts-row">
                        <input type="text"   name="part_name[]"  class="form-control part-name"  placeholder="Part Name"  value="<?php echo htmlspecialchars($p['name'] ?? ''); ?>" />
                        <input type="number" name="part_price[]" class="form-control part-price price-col" placeholder="Price" min="0" step="0.01" value="<?php echo $p['price'] ?? ''; ?>" />
                        <button type="button" class="remove-row-btn"><i class="fa fa-times"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-row-btn" data-target="<?php echo $prefix; ?>_parts_body" data-type="part">+ Add Part</button>
            </div>
        </div>
    </div>

    <!-- LABOUR DETAILS -->
    <div class="col-sm-6">
        <div class="maint-panel" id="<?php echo $prefix; ?>_labour_panel">
            <div class="maint-section-title">
                <i class="fa fa-wrench" style="color:#f39c12"></i> Labour Details
                <span class="labour-same-badge">
                    <span class="maint-total-badge" id="<?php echo $prefix; ?>_labour_total_badge">0.00</span>
                    <label style="margin-left:8px;font-size:12px;color:#27ae60;cursor:pointer;">
                        <input type="checkbox" name="same_as_parts" id="<?php echo $prefix; ?>_same_as_parts" value="1" <?php echo ($isEdit && $rec->same_as_parts) ? 'checked' : ''; ?> style="margin-right:3px;">
                        Same as Parts
                    </label>
                </span>
            </div>
            <div class="form-group">
                <label>Garage / Service Center <small class="req">*</small></label>
                <input type="text" name="labour_garage" id="<?php echo $prefix; ?>_labour_garage" class="form-control" placeholder="Garage Name"
                       value="<?php echo $isEdit ? htmlspecialchars($rec->labour_garage ?? '') : ''; ?>" />
            </div>
            <div class="form-group">
                <label>Vendor Details</label>
                <textarea name="labour_vendor_details" id="<?php echo $prefix; ?>_labour_vendor_details" class="form-control" rows="2" placeholder="Address / Contact"><?php echo $isEdit ? htmlspecialchars($rec->labour_vendor_details ?? '') : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Services List <small class="req">*</small></label>
                <div id="<?php echo $prefix; ?>_labour_body">
                    <?php foreach ($labour_data as $l): ?>
                    <div class="labour-row">
                        <input type="text"   name="service_desc[]" class="form-control service-desc"  placeholder="Service Desc." value="<?php echo htmlspecialchars($l['desc'] ?? ''); ?>" />
                        <input type="number" name="service_cost[]" class="form-control service-cost cost-col" placeholder="Cost" min="0" step="0.01" value="<?php echo $l['cost'] ?? ''; ?>" />
                        <button type="button" class="remove-row-btn"><i class="fa fa-times"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="<?php echo $prefix; ?>_labour_add_btn" class="add-row-btn" data-target="<?php echo $prefix; ?>_labour_body" data-type="labour">+ Add Service</button>
            </div>
        </div>
    </div>
</div>

<hr style="margin:10px 0;">

<!-- ===== BOTTOM SUMMARY ===== -->
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('total_cost'); ?></label>
            <input type="text" id="<?php echo $prefix; ?>_total_cost_display" class="form-control" readonly value="<?php echo $isEdit ? number_format((float)$rec->total_cost, 2) : '0.00'; ?>" />
            <input type="hidden" name="parts_total_hidden"  id="<?php echo $prefix; ?>_parts_total_hidden"  value="<?php echo $isEdit ? $rec->parts_total  : '0'; ?>" />
            <input type="hidden" name="labour_total_hidden" id="<?php echo $prefix; ?>_labour_total_hidden" value="<?php echo $isEdit ? $rec->labour_total : '0'; ?>" />
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('invoice_reference'); ?></label>
            <input type="text" name="invoice_reference" class="form-control" placeholder="INV-001"
                   value="<?php echo $isEdit ? htmlspecialchars($rec->invoice_reference ?? '') : ''; ?>" />
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('upload_invoice'); ?></label>
            <input type="file" name="invoice_file" class="form-control" />
            <?php if ($isEdit && $rec->invoice_file): ?>
                <small class="text-muted"><i class="fa fa-file"></i> <?php echo $this->media_storage->fileview($rec->invoice_file); ?></small>
                <a href="<?php echo site_url('admin/maintenance/download/' . $rec->id); ?>" class="btn btn-xs btn-default" title="Download"><i class="fa fa-download"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $this->lang->line('next_service_due'); ?></label>
            <input type="text" name="next_service_due" class="form-control date" readonly autocomplete="off"
                   value="<?php echo ($isEdit && $rec->next_service_due) ? date($this->customlib->getSchoolDateFormat(), strtotime($rec->next_service_due)) : ''; ?>" />
        </div>
    </div>
    <div class="col-sm-8">
        <div class="form-group">
            <label><?php echo $this->lang->line('description_notes'); ?></label>
            <textarea name="description_notes" class="form-control" rows="2" placeholder="Add notes..."><?php echo $isEdit ? htmlspecialchars($rec->description_notes ?? '') : ''; ?></textarea>
        </div>
    </div>
</div>


<script>
(function($){
    var prefix  = '<?php echo $prefix; ?>';
    // Unique event namespace per prefix – prevents stacking on AJAX reload
    var ns      = '.maint_' + prefix;
    // Container: use the nearest parent modal-body or fallback to document
    var $form   = $('#' + prefix + '_same_as_parts').closest('form, .modal-body, body');

    // ── Saved originals (restored when checkbox is unchecked) ──
    var _origGarage         = '';
    var _origLabourDetails  = '';
    var _origLabourRows     = [];   // array of {desc, cost}

    function saveOrigLabour() {
        _origGarage        = $('#' + prefix + '_labour_garage').val();
        _origLabourDetails = $('#' + prefix + '_labour_vendor_details').val();
        _origLabourRows    = [];
        $('#' + prefix + '_labour_body .labour-row').each(function() {
            _origLabourRows.push({
                desc: $(this).find('.service-desc').val(),
                cost: $(this).find('.service-cost').val()
            });
        });
    }

    // ── Build one labour row jQuery element ──
    function makeLabourRow(desc, cost) {
        var $row = $('<div class="labour-row"></div>');
        $('<input type="text" name="service_desc[]" class="form-control service-desc" placeholder="Service Desc.">')
            .val(desc || '')
            .appendTo($row);
        $('<input type="number" name="service_cost[]" class="form-control service-cost cost-col" placeholder="Cost" min="0" step="0.01">')
            .val(cost || '')
            .appendTo($row);
        $('<button type="button" class="remove-row-btn"><i class="fa fa-times"></i></button>')
            .appendTo($row);
        return $row;
    }

    // ── Build one parts row jQuery element ──
    function makePartRow(name, price) {
        var $row = $('<div class="parts-row"></div>');
        $('<input type="text" name="part_name[]" class="form-control part-name" placeholder="Part Name">')
            .val(name || '')
            .appendTo($row);
        $('<input type="number" name="part_price[]" class="form-control part-price price-col" placeholder="Price" min="0" step="0.01">')
            .val(price || '')
            .appendTo($row);
        $('<button type="button" class="remove-row-btn"><i class="fa fa-times"></i></button>')
            .appendTo($row);
        return $row;
    }

    // ── Mirror Parts → Labour ──
    function mirrorPartsToLabour() {
        // 1. Vendor Name → Garage / Service Center
        $('#' + prefix + '_labour_garage').val(
            $('#' + prefix + '_parts_vendor_name').val()
        );

        // 2. Vendor Details → Labour Vendor Details
        $('#' + prefix + '_labour_vendor_details').val(
            $('#' + prefix + '_parts_vendor_details').val()
        );

        // 3. Parts rows → Service rows (Part Name → Service Desc, Price → Cost)
        var $labourBody = $('#' + prefix + '_labour_body');
        $labourBody.empty();

        var $partRows = $('#' + prefix + '_parts_body .parts-row');
        if ($partRows.length === 0) {
            // No parts — add one empty service row
            $labourBody.append(makeLabourRow('', ''));
        } else {
            $partRows.each(function() {
                var name  = $(this).find('.part-name').val();
                var price = $(this).find('.part-price').val();
                $labourBody.append(makeLabourRow(name, price));
            });
        }
    }

    // ── Restore Labour to saved originals ──
    function restoreLabour() {
        $('#' + prefix + '_labour_garage').val(_origGarage);
        $('#' + prefix + '_labour_vendor_details').val(_origLabourDetails);

        var $labourBody = $('#' + prefix + '_labour_body');
        $labourBody.empty();
        if (_origLabourRows.length === 0) {
            $labourBody.append(makeLabourRow('', ''));
        } else {
            $.each(_origLabourRows, function(i, row) {
                $labourBody.append(makeLabourRow(row.desc, row.cost));
            });
        }
    }

    // ── Lock / unlock Labour fields ──
    function lockLabour(lock) {
        var $garage   = $('#' + prefix + '_labour_garage');
        var $details  = $('#' + prefix + '_labour_vendor_details');
        var $addBtn   = $('#' + prefix + '_labour_add_btn');

        $garage.prop('readonly', lock).css('background-color', lock ? '#f5f5f5' : '');
        $details.prop('readonly', lock).css('background-color', lock ? '#f5f5f5' : '');
        $('#' + prefix + '_labour_body input').prop('readonly', lock).css('background-color', lock ? '#f5f5f5' : '');
        $('#' + prefix + '_labour_body .remove-row-btn').prop('disabled', lock).css('opacity', lock ? 0.4 : 1);
        $addBtn.prop('disabled', lock).css('opacity', lock ? 0.4 : 1);
    }

    // ── Totals ──
    function calcTotals() {
        var pTotal = 0, lTotal = 0;
        $('#' + prefix + '_parts_body .part-price').each(function() {
            pTotal += parseFloat($(this).val()) || 0;
        });
        $('#' + prefix + '_labour_body .service-cost').each(function() {
            lTotal += parseFloat($(this).val()) || 0;
        });

        $('#' + prefix + '_parts_total_badge').text(pTotal.toFixed(2));
        $('#' + prefix + '_parts_total_hidden').val(pTotal.toFixed(2));

        var sameAsParts = $('#' + prefix + '_same_as_parts').is(':checked');
        var finalLabour = sameAsParts ? pTotal : lTotal;
        $('#' + prefix + '_labour_total_badge').text(finalLabour.toFixed(2));
        $('#' + prefix + '_labour_total_hidden').val(finalLabour.toFixed(2));
        $('#' + prefix + '_total_cost_display').val((pTotal + finalLabour).toFixed(2));
    }

    // ══ Unbind old namespaced events before binding (prevents stacking on AJAX reload) ══
    $form.off(ns);
    $(document).off(ns);

    // ── "Same as Parts" checkbox ──
    $form.on('change' + ns, '#' + prefix + '_same_as_parts', function() {
        if ($(this).is(':checked')) {
            saveOrigLabour();        // save current labour before overwriting
            mirrorPartsToLabour();   // copy parts → labour
            lockLabour(true);        // lock labour fields
        } else {
            restoreLabour();         // put back original labour data
            lockLabour(false);       // unlock labour fields
        }
        calcTotals();
    });

    // ── Live re-mirror: Parts Vendor Name / Details changed while checkbox ticked ──
    $form.on('input' + ns, '#' + prefix + '_parts_vendor_name, #' + prefix + '_parts_vendor_details', function() {
        if ($('#' + prefix + '_same_as_parts').is(':checked')) {
            mirrorPartsToLabour();
            lockLabour(true);
            calcTotals();
        }
    });

    // ── Live re-mirror: Parts rows changed while checkbox ticked ──
    $form.on('input' + ns, '#' + prefix + '_parts_body .part-name, #' + prefix + '_parts_body .part-price', function() {
        if ($('#' + prefix + '_same_as_parts').is(':checked')) {
            mirrorPartsToLabour();
            lockLabour(true);
        }
        calcTotals();
    });

    // ── Recalc on Labour cost input ──
    $form.on('input' + ns, '#' + prefix + '_labour_body .service-cost', calcTotals);

    // ── Remove row ──
    $form.on('click' + ns, '#' + prefix + '_parts_body .remove-row-btn', function() {
        if ($('#' + prefix + '_parts_body .parts-row').length > 1) {
            $(this).closest('.parts-row').remove();
            if ($('#' + prefix + '_same_as_parts').is(':checked')) {
                mirrorPartsToLabour();
                lockLabour(true);
            }
            calcTotals();
        }
    });

    $form.on('click' + ns, '#' + prefix + '_labour_body .remove-row-btn', function() {
        if (!$('#' + prefix + '_same_as_parts').is(':checked') &&
            $('#' + prefix + '_labour_body .labour-row').length > 1) {
            $(this).closest('.labour-row').remove();
            calcTotals();
        }
    });

    // ── Add Part row ──
    $('#' + prefix + '_parts_body').closest('.maint-panel')
        .find('.add-row-btn[data-type="part"]')
        .off('click' + ns)
        .on('click' + ns, function() {
            if ($(this).prop('disabled')) return;
            $('#' + prefix + '_parts_body').append(makePartRow('', ''));
        });

    // ── Add Service row ──
    $('#' + prefix + '_labour_add_btn')
        .off('click' + ns)
        .on('click' + ns, function() {
            if ($(this).prop('disabled')) return;
            $('#' + prefix + '_labour_body').append(makeLabourRow('', ''));
        });

    // ── On load: if checkbox already checked (edit mode with saved same_as_parts=1) ──
    if ($('#' + prefix + '_same_as_parts').is(':checked')) {
        lockLabour(true);
    }

    // ── Initial calc ──
    calcTotals();

})(jQuery);
</script>


