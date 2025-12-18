<?php
defined('MOODLE_INTERNAL') || die();

// This file keeps track of upgrades to the local_mae plugin.
//
// Sometimes, changes between versions involve alterations to database structures
// and other major things that may break installations. The upgrade function in
// this file will attempt to perform all the necessary actions to upgrade your
// site to the current version.

/**
 * Upgrade code for the local_mae plugin.
 *
 * @package   local_mae
 * @copyright 2021 Maubic Consultoría Tecnológica SL
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero GPL v3 or later
 */
function xmldb_local_mae_upgrade(int $oldversion): bool {
    global $DB;

    // Migrate the legacy mod/mae:impersonate capability to the local component
    // so existing role permissions continue to work after the capability rename.
    if ($oldversion < 2023040403) {
        $transaction = $DB->start_delegated_transaction();

        // Update capability definitions stored in the capabilities table.
        $DB->set_field('capabilities', 'name', 'local/mae:impersonate', ['name' => 'mod/mae:impersonate']);

        // Update role assignments that point to the legacy capability name.
        $DB->set_field('role_capabilities', 'capability', 'local/mae:impersonate', ['capability' => 'mod/mae:impersonate']);

        $transaction->allow_commit();
        upgrade_plugin_savepoint(true, 2023040403, 'local', 'mae');
    }

    return true;
}
