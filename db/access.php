<?php
/**
 *
 * @package    local_mae
 * @copyright  2021 Maubic Consultoría Tecnológica SL
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero GPL v3 or later
 * 
 */

$capabilities = array(
    'local/mae:impersonate' => array(
        'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
        'captype'      => 'read',
        // The web service impersonation works at site scope and is checked with context_system,
        // so the capability must also live at the system context level for consistency.
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            // Explicitly prohibit the capability for teaching and student roles.
            'student'        => CAP_PROHIBIT,
            'teacher'        => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'manager'        => CAP_ALLOW
        )
    )
);
