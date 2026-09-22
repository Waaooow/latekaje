<?php

return [
    // Page labels
    'nav_label' => 'Settings',
    'title' => 'Notifications',

    // Notifications
    'saved' => 'Settings saved',
    'gowa_connected' => 'Connected! (HTTP :status)',
    'gowa_failed' => 'Failed: :body',
    'gowa_ok_title' => 'GOWA connected',
    'gowa_fail_title' => 'GOWA failed',
    'fill_test_number' => 'Fill in the test number first.',
    'test_wa_body' => 'LATEKAJE test OK — :time. Reply to this message if received.',
    'test_sent_to' => 'Test message sent to :target! (HTTP :status)',
    'test_send_fail' => 'Failed to send to :target: :body',
    'test_sent_title' => 'Test message sent',
    'test_failed_title' => 'Test message failed',
    'fill_webhook' => 'Fill in the webhook URL first.',
    'webhook_test_message' => 'LATEKAJE webhook connection test',
    'webhook_sent' => 'Sent! (HTTP :status)',
    'webhook_failed' => 'Failed (HTTP :status): :body',
    'maintenance_off' => 'Maintenance mode OFF — app is live',
    'maintenance_on' => 'Maintenance mode ON — only superadmin can access',
    'token_name_required' => 'Fill in the token name first',
    'token_created' => 'Token created — copy now, shown only once',
    'token_revoked' => 'Token revoked',

    // Blade: App section
    'app_heading' => 'Application',
    'app_desc' => 'Status: :status.',
    'status_maintenance' => 'MAINTENANCE MODE (closed to public)',
    'status_live' => 'Live normally',
    'maintenance_enable' => 'Enable Maintenance Mode',
    'maintenance_disable' => 'Disable Maintenance Mode',

    // Blade: API section
    'api_heading' => 'API (for external integrations)',
    'api_desc' => 'Your account tokens. Include as Authorization: Bearer <token> header.',
    'new_token_name' => 'New token name',
    'new_token_placeholder' => 'e.g.: hp-kiosk-1',
    'create_token' => 'Create Token',
    'th_name' => 'Name',
    'th_created' => 'Created',
    'th_last_used' => 'Last used',
    'revoke' => 'Revoke',
    'empty_tokens' => 'No tokens yet.',

    // Blade: Schedule section
    'schedule_heading' => 'Schedule',
    'auto_daily' => 'Send automatically every day',
    'send_time' => 'Send time (WIB)',

    // Blade: GOWA section
    'gowa_heading' => 'WhatsApp via GOWA',
    'gowa_desc' => 'Top priority. Fill in GOWA base URL + target number/group, then Test Connection.',
    'gowa_enable' => 'Enable sending via GOWA',
    'gowa_base' => 'GOWA Base URL',
    'optional' => '(optional)',
    'basic_user' => 'Basic Auth User',
    'basic_pass' => 'Basic Auth Password',
    'target_label' => 'Target (628.. number / group JID ....@g.us)',
    'relay_url' => 'Relay URL',
    'relay_url_hint' => '(optional — if GOWA is not directly reachable from the server)',
    'relay_secret' => 'Relay Secret',
    'test_conn' => 'Test GOWA Connection',
    'test_target_placeholder' => 'Test number (empty = use Target)',
    'send_test' => 'Send Test Message',

    // Blade: Webhook section
    'webhook_heading' => 'General Webhook',
    'webhook_desc' => 'POST JSON {event, generated_at, total, items} to any URL (n8n, your own bot, etc).',
    'webhook_enable' => 'Enable webhook',
    'webhook_url' => 'Webhook URL',
    'secret_how' => 'How to send secret',
    'opt_header' => 'Header X-Webhook-Secret',
    'opt_bearer' => 'Bearer token',
    'opt_basic' => 'Basic auth (user + password)',
    'opt_query' => 'Query param ?secret=',
    'opt_none' => 'No secret',
    'secret_token' => 'Secret / Token',
    'basic_user_cond' => 'Basic User',
    'basic_cond_hint' => '(if basic mode)',
    'basic_pass_plain' => 'Basic Password',
    'test_webhook' => 'Test Webhook',

    // Blade: save + history
    'save_all' => 'Save All Settings',
    'history_heading' => 'Delivery History (last 10)',
    'th_time' => 'Time',
    'th_channel' => 'Channel',
    'th_target' => 'Target',
    'th_units' => 'Units',
    'th_status' => 'Status',
    'th_response' => 'Response',
    'empty_logs' => 'No deliveries yet.',
];
