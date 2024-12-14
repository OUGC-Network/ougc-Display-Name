<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/languages/english/admin/ougcDisplayName.lang.php)
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

$l = [
    'setting_group_ougcDisplayName' => 'ougc Display Name',
    'setting_group_ougcDisplayName_desc' => 'Allow users to use a display name to visually replace their unique username.',

    'setting_ougcDisplayName_minimumLength' => 'Minimum Display Name Length',
    'setting_ougcDisplayName_minimumLength_desc' => 'The minimum number of characters a display name can be.',
    'setting_ougcDisplayName_maximumLength' => 'Maximum Display Name Length',
    'setting_ougcDisplayName_maximumLength_desc' => 'The maximum number of characters a display name can be.',
    'setting_ougcDisplayName_clearProfile' => 'Clear Profile',
    'setting_ougcDisplayName_clearProfile_desc' => 'Reset display names for users when clearing profiles.',
    'setting_ougcDisplayName_regularExpressions' => 'Clearance Rules',
    'setting_ougcDisplayName_regularExpressions_desc' => 'Line break separated list of regular expressions to match invalid usernames.',
    'setting_ougcDisplayName_pageAction' => 'Page Action',
    'setting_ougcDisplayName_pageAction_desc' => 'Select the custom page action to use for this plugin.',

    'ougcDisplayNameModuleMenu' => 'Display Name',

    'ougcDisplayNameGroupPermissionsCanChange' => 'Can change display name?',

    'ougcDisplayName' => 'Display Name',
    'ougcDisplayNamePermission' => 'Can view display name configuration?',

    'ougcDisplayNameMainTitle' => 'Display Name',
    'ougcDisplayNameMainTabTitle' => 'File Edits',
    'ougcDisplayNameMainTabTitleDescription' => 'This section allows you to apply or revert file edits.',

    'ougcDisplayNameMainTableTitle' => 'File Edits',
    'ougcDisplayNameMainTableFile' => 'File',
    'ougcDisplayNameMainTableStatus' => 'Status',
    'ougcDisplayNameMainTableStatusOptionsApply' => 'Apply File Edits',
    'ougcDisplayNameMainTableStatusOptionsRevert' => 'Revert File Edits',

    'ougcDisplayNamePageSuccessApply' => 'The selected file edit was applied successfully.',
    'ougcDisplayNamePageSuccessRevert' => 'The selected file edit was reverted successfully.',

    'ougcDisplayNamePageErrorApply' => 'There was an error applying the selected file edits.',
    'ougcDisplayNamePageErrorRevert' => 'There was an error reverting the selected file edits.',

    'ougcDisplayNamePluginLibrary' => 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.',
];