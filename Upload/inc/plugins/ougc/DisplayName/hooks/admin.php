<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/hooks/admin.php)
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

namespace ougc\DisplayName\Hooks\Admin;

use MyBB;

use function ougc\DisplayName\Core\loadLanguage;
use function ougc\DisplayName\Core\urlHandlerGet;

use const ougc\DisplayName\ROOT;

function admin_tabs09(array &$modules): array
{
    global $mybb;

    if (!empty($mybb->user['ougcDisplayName'])) {
        $mybb->user['username'] = htmlspecialchars_uni($mybb->user['ougcDisplayName']);
    }

    return $modules;
}

function admin_config_plugins_deactivate()
{
    global $mybb, $page;

    if (
        $mybb->get_input('action') != 'deactivate' ||
        $mybb->get_input('plugin') != 'ougcDisplayName' ||
        !$mybb->get_input('uninstall', MyBB::INPUT_INT)
    ) {
        return;
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougcDisplayName'
        );
    }

    if ($mybb->get_input('no')) {
        admin_redirect('index.php?module=config-plugins');
    }
}

function admin_user_groups_begin(): bool
{
    global $usergroup_permissions;

    $usergroup_permissions['ougcDisplayNameCanChange'] = 1;

    return true;
}

function admin_formcontainer_output_row(array &$hookArguments): array
{
    global $page;

    if (
        $page->active_action !== 'groups' ||
        $page->active_module !== 'user'
    ) {
        return $hookArguments;
    }

    global $mybb, $lang;
    global $form;

    if (
        $mybb->get_input('action') === 'edit' &&
        isset($lang->account_management) &&
        $hookArguments['title'] === $lang->account_management
    ) {
        loadLanguage();

        $hookArguments['content'] .= '<div class="group_settings_bit">' . $form->generate_check_box(
                'ougcDisplayNameCanChange',
                1,
                $lang->ougcDisplayNameGroupPermissionsCanChange,
                ['checked' => $mybb->get_input('ougcDisplayNameCanChange', MyBB::INPUT_INT)]
            ) . '</div>';
    }

    return $hookArguments;
}

function admin_user_groups_edit_commit(): bool
{
    global $mybb;
    global $updated_group;

    $updated_group['ougcDisplayNameCanChange'] = $mybb->get_input('ougcDisplayNameCanChange', MyBB::INPUT_INT);

    return true;
}

function admin_user_users_begin(): bool
{
    global $lang;
    global $user_view_fields, $sort_options;

    loadLanguage();

    $user_view_fields['ougcDisplayName'] = [
        'title' => $lang->ougcDisplayName,
        'width' => '',
        'align' => ''
    ];

    $sort_options['ougcDisplayName'] = $lang->ougcDisplayName;

    return true;
}

function admin_config_action_handler(array &$actions): array
{
    global $lang;

    $actions['ougc_display_name'] = [
        'active' => 'ougc_display_name',
        'file' => 'config.php'
    ];

    return $actions;
}

function admin_config_menu(array &$sub_menu): array
{
    global $lang;

    loadLanguage();

    $sub_menu[] = [
        'id' => 'ougc_display_name',
        'title' => $lang->ougcDisplayNameModuleMenu,
        'link' => urlHandlerGet()
    ];

    return $sub_menu;
}

function admin_load(): bool
{
    global $run_module, $page;

    if ($run_module !== 'config' || $page->active_action !== 'ougc_display_name') {
        return false;
    }

    require ROOT . '/admin/config.php';

    return true;
}

function admin_config_permissions(array &$hookArguments): array
{
    global $lang;

    loadLanguage();

    $hookArguments['ougc_display_name'] = $lang->ougcDisplayNamePermission;

    return $hookArguments;
}

function admin_formcontainer_output_row10(array &$hookArguments): array
{
    global $page;

    if (
        $page->active_action !== 'users' ||
        $page->active_module !== 'user'
    ) {
        return $hookArguments;
    }

    global $mybb, $lang;
    global $form;

    if (
        $mybb->get_input('action') === 'edit' &&
        isset($lang->new_password) &&
        $hookArguments['title'] === $lang->new_password
    ) {
        loadLanguage();

        $hookArguments['this']->output_row(
            $lang->ougcDisplayName,
            '',
            $form->generate_text_box(
                'ougcDisplayName',
                $mybb->get_input('ougcDisplayName'),
                ['id' => 'ougcDisplayName']
            ),
            'ougcDisplayName'
        );
    }

    return $hookArguments;
}