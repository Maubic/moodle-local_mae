<?php
/**
 *
 * @package    local_mae
 * @copyright  2021 Maubic Consultoría Tecnológica SL
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero GPL v3 or later
 * 
 */

$capabilities = array(
    'mod/mae:impersonate' => array(
        'riskbitmask'  => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes'   => array(
            'student'        => CAP_DENY,
            'teacher'        => CAP_DENY,
            'editingteacher' => CAP_DENY,
            'manager'          => CAP_ALLOW
        )
    )
        );