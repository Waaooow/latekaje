<?php

return [
    'model_label' => 'Student',
    'model_plural' => 'Students',

    // Form
    'nis_label' => 'NIS',
    'nis_placeholder' => 'e.g.: 1001 (may be empty)',
    'full_name_label' => 'Full Name',
    'name_label' => 'Name',
    'class_label' => 'Class',
    'class_placeholder' => 'e.g.: X TJKT 1',

    // Table
    'active_label' => 'Active',
    'loans_label' => 'Loans',

    // Actions & notifications
    'create_action' => 'Add Student',
    'create_account' => 'Create Account',
    'account_created_title' => 'Login account created',
    'account_created_body' => 'NIS: :nis | Initial password: their NIS. Ask the student to change it in Profile.',

    // Importer columns
    'import_col_nis' => 'NIS',
    'import_col_name' => 'Name',
    'import_col_class' => 'Class',
    'import_col_password' => 'Password',
    'import_completed' => 'Student import finished: :success rows succeeded',
    'import_failed_suffix' => ', :failed rows failed',

    // Abilities
    'ability_view_any' => 'View Students',
    'ability_create' => 'Add student',
];
