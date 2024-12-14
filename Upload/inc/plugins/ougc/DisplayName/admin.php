<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/admin.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2024 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow users to use a display name to visually replace their unique username.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\DisplayName\Admin;

use DirectoryIterator;
use stdClass;

use function ougc\DisplayName\Core\loadLanguage;

use const ougc\DisplayName\ROOT;
use const PLUGINLIBRARY;

const TABLES_DATA = [
    'users' => [
        'ougcDisplayName' => [
            'type' => 'VARCHAR',
            'size' => 120,
            'default' => ''
        ],
        //'unique_key' => ['ougcDisplayName' => 'ougcDisplayName']
    ],
    'usergroups' => [
        'ougcDisplayNameCanChange' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ]
    ]
];

function pluginInfo(): array
{
    global $lang;

    loadLanguage();

    return [
        'name' => 'ougc Display Name',
        'description' => $lang->setting_group_ougcDisplayName_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.0',
        'versioncode' => 1800,
        'compatibility' => '18*',
        'codename' => 'ougcDisplayName',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function pluginActivate(): bool
{
    global $PL, $cache, $lang;

    loadLanguage();

    loadPluginLibrary();

    $settingsContents = file_get_contents(ROOT . '/settings.json');

    $settingsData = json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougcDisplayName_{$settingKey}"})) {
            continue;
        }

        if ($settingData['optionscode'] == 'select' || $settingData['optionscode'] == 'checkbox') {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougcDisplayName_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougcDisplayName_{$settingKey}"};

        $settingData['description'] = $lang->{"setting_ougcDisplayName_{$settingKey}_desc"};
    }

    $PL->settings(
        'ougcDisplayName',
        $lang->setting_group_ougcDisplayName,
        $lang->setting_group_ougcDisplayName_desc,
        $settingsData
    );

    $templates = [];

    if (file_exists($templateDirectory = ROOT . '/templates')) {
        $templatesDirIterator = new DirectoryIterator($templateDirectory);

        foreach ($templatesDirIterator as $template) {
            if (!$template->isFile()) {
                continue;
            }

            $pathName = $template->getPathname();

            $pathInfo = pathinfo($pathName);

            if ($pathInfo['extension'] === 'html') {
                $templates[$pathInfo['filename']] = file_get_contents($pathName);
            }
        }
    }

    if ($templates) {
        $PL->templates('ougcDisplayName', 'ougc Display Name', $templates);
    }

    $pluginInfo = pluginInfo();

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    if (!isset($plugins['DisplayName'])) {
        $plugins['DisplayName'] = $pluginInfo['versioncode'];
    }

    db_verify_tables();

    change_admin_permission('config', 'ougc_display_name');

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['DisplayName'] = $pluginInfo['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    return true;
}

function pluginDeactivate(): bool
{
    change_admin_permission('config', 'ougc_display_name', 0);

    return true;
}

function pluginInstall(): bool
{
    global $db;

    db_verify_tables();

    $db->update_query('users', ['ougcDisplayName' => 'username'], '', '', true);

    db_verify_indexes();

    return true;
}

function pluginIsInstalled(): bool
{
    static $isInstalled = null;

    if ($isInstalled === null) {
        global $db;

        $isInstalledEach = true;

        foreach (TABLES_DATA as $tableName => $tableColumns) {
            foreach ($tableColumns as $fieldName => $fieldData) {
                $isInstalledEach = $db->field_exists($fieldName, $tableName) && $isInstalledEach;
            }
        }

        $isInstalled = $isInstalledEach;
    }

    return $isInstalled;
}

function pluginUninstall(): bool
{
    global $db, $PL, $cache;

    loadPluginLibrary();

    foreach (TABLES_DATA as $tableName => $tableColumns) {
        foreach ($tableColumns as $fieldName => $fieldData) {
            if ($db->field_exists($fieldName, $tableName)) {
                $db->drop_column($tableName, $fieldName);
            }
        }
    }

    if ($db->index_exists('users', 'ougcDisplayName')) {
        $db->drop_index('users', 'ougcDisplayName');
    }

    $PL->settings_delete('ougcDisplayName');

    $PL->templates_delete('ougcDisplayName');

    change_admin_permission('config', 'ougc_display_name', -1);

    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['DisplayName'])) {
        unset($plugins['DisplayName']);
    }

    $cache->delete('ougcDisplayNameFilters');

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }

    return true;
}

function db_tables(): array
{
    $tables_data = [];

    foreach (TABLES_DATA as $table_name => $table_columns) {
        foreach ($table_columns as $fieldName => $fieldData) {
            if (!isset($fieldData['type'])) {
                continue;
            }

            $tables_data[$table_name][$fieldName] = db_build_field_definition($fieldData);
        }

        foreach ($table_columns as $fieldName => $fieldData) {
            if (isset($fieldData['primary_key'])) {
                $tables_data[$table_name]['primary_key'] = $fieldName;
            }

            if ($fieldName === 'unique_key') {
                $tables_data[$table_name]['unique_key'] = $fieldData;
            }
        }
    }

    return $tables_data;
}

function db_verify_tables(): bool
{
    global $db;

    $collation = $db->build_create_table_collation();

    foreach (db_tables() as $table_name => $table_columns) {
        if ($db->table_exists($table_name)) {
            foreach ($table_columns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key' || $fieldName == 'unique_key') {
                    continue;
                }

                if ($db->field_exists($fieldName, $table_name)) {
                    $db->modify_column($table_name, "`{$fieldName}`", $fieldData);
                } else {
                    $db->add_column($table_name, $fieldName, $fieldData);
                }
            }
        } else {
            $query_string = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$table_name}` (";

            foreach ($table_columns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key') {
                    $query_string .= "PRIMARY KEY (`{$fieldData}`)";
                } elseif ($fieldName != 'unique_key') {
                    $query_string .= "`{$fieldName}` {$fieldData},";
                }
            }

            $query_string .= ") ENGINE=MyISAM{$collation};";

            $db->write_query($query_string);
        }
    }

    db_verify_indexes();

    return true;
}

function db_verify_indexes(): bool
{
    global $db;

    foreach (db_tables() as $table_name => $table_columns) {
        if (!$db->table_exists($table_name)) {
            continue;
        }

        if (isset($table_columns['unique_key'])) {
            foreach ($table_columns['unique_key'] as $key_name => $key_value) {
                if ($db->index_exists($table_name, $key_name)) {
                    continue;
                }

                $db->write_query(
                    "ALTER TABLE {$db->table_prefix}{$table_name} ADD UNIQUE KEY {$key_name} ({$key_value})"
                );
            }
        }
    }

    return true;
}

function db_build_field_definition(array $fieldData): string
{
    $field_definition = '';

    $field_definition .= $fieldData['type'];

    if (isset($fieldData['size'])) {
        $field_definition .= "({$fieldData['size']})";
    }

    if (isset($fieldData['unsigned'])) {
        if ($fieldData['unsigned'] === true) {
            $field_definition .= ' UNSIGNED';
        } else {
            $field_definition .= ' SIGNED';
        }
    }

    if (!isset($fieldData['null'])) {
        $field_definition .= ' NOT';
    }

    $field_definition .= ' NULL';

    if (isset($fieldData['auto_increment'])) {
        $field_definition .= ' AUTO_INCREMENT';
    }

    if (isset($fieldData['default'])) {
        $field_definition .= " DEFAULT '{$fieldData['default']}'";
    }

    return $field_definition;
}

function pluginLibraryRequirements(): stdClass
{
    return (object)pluginInfo()['pl'];
}

function loadPluginLibrary(): bool
{
    global $PL, $lang;

    loadLanguage();

    $fileExists = file_exists(PLUGINLIBRARY);

    if ($fileExists && !($PL instanceof \PluginLibrary)) {
        require_once PLUGINLIBRARY;
    }

    if (!$fileExists || $PL->version < pluginLibraryRequirements()->version) {
        flash_message(
            $lang->sprintf(
                $lang->ougcDisplayNamePluginLibrary,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}