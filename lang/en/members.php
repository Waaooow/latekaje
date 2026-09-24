<?php

return [
    'model_label' => 'Member',
    'model_plural' => 'Members',

    // Form
    'code_label' => 'Member ID',
    'code_placeholder' => 'e.g.: 1001 (may be empty)',
    'full_name_label' => 'Full Name',
    'name_label' => 'Name',
    'group_label' => 'Group',
    'group_placeholder' => 'e.g.: X TJKT 1',

    // Table
    'active_label' => 'Active',
    'loans_label' => 'Loans',

    // Actions & notifications
    'create_action' => 'Add Member',
    'create_account' => 'Create Account',
    'account_created_title' => 'Login account created',
    'account_created_body' => 'ID: :code | Initial password: their ID. Ask the member to change it in Profile.',

    // Importer columns
    'import_col_code' => 'Member ID',
    'import_col_name' => 'Name',
    'import_col_group' => 'Group',
    'import_col_password' => 'Password',
    'import_completed' => 'Member import finished: :success rows succeeded',
    'import_failed_suffix' => ', :failed rows failed',

    // Abilities
    'ability_view_any' => 'View Members',
    'ability_create' => 'Add member',
];
