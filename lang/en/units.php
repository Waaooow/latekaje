<?php

return [
    'model_label' => 'Unit Data',
    'model_plural' => 'Unit Data',

    // Form
    'asset_label' => 'Asset',
    'serial_label' => 'Serial Number / QR',
    'qty_created_label' => 'Units Created',
    'qty_created_helper' => 'Fill > 1 to create many units at once. Remaining codes are auto-generated.',
    'sn_manual_label' => 'Manual SN (optional)',
    'sn_manual_placeholder' => "One SN per line, e.g.:\nSN-PC-001\nSN-PC-002",
    'sn_manual_helper' => 'Leave empty if units have no SN — the system auto-generates codes.',
    'status_label' => 'Status',
    'status_available' => 'Available',
    'status_borrowed' => 'Borrowed',
    'condition_label' => 'Condition',
    'condition_good' => 'Good',
    'condition_damaged' => 'Damaged',
    'condition_total_loss' => 'Totally Damaged',
    'placement_label' => 'Placement Location',

    // Table
    'group_asset' => 'Asset',
    'name_label' => 'Tool Name',
    'code_label' => 'Asset Code',
    'borrowed_by_label' => 'Borrowed By',
    'location_label' => 'Location',

    // Header / record / bulk actions
    'download_excel' => 'Download Excel',
    'print_all_qr' => 'Print All QR',
    'print_qr' => 'Print QR',
    'print_selected_qr' => 'Print Selected QR',
    'move_room' => 'Move Room',
    'target_location_label' => 'Target Location',
    'actions_label' => 'Actions',

    // Pages & notifications
    'create_action' => 'Add Unit',
    'created_many_title' => ':count units successfully created',
    'created_many_body' => 'Codes: :first to :last',

    // Importer columns
    'import_col_tool_name' => 'Tool Name',
    'import_col_asset_code' => 'Asset Code',
    'import_col_type' => 'Type',
    'import_col_spec' => 'Specification',
    'import_col_usage' => 'Usage',
    'import_col_serial' => 'Serial Number / QR',
    'import_col_qty' => 'Quantity',
    'import_col_condition' => 'Condition',
    'import_col_location' => 'Location',
    'import_col_status' => 'Status',
    'import_completed' => 'Unit import finished: :success rows succeeded',
    'import_failed_suffix' => ', :failed rows failed',

    // Exporter
    'export_completed' => 'Unit export finished: :success rows succeeded',
    'export_failed_suffix' => ', :failed rows failed',

    // Validation (AssetItemService)
    'sn_manual_too_many' => 'Manual SN count (:count) exceeds unit quantity (:qty).',
    'sn_manual_duplicate' => "SN ':sn' is already registered in the system.",

    // Abilities
    'ability_view_any' => 'View Unit Data',
    'ability_create' => 'Add unit',
    'ability_update' => 'Update unit',
    'ability_delete' => 'Delete unit',
];
