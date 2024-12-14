<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/admin/config.php)
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

use function ougc\DisplayName\Core\loadLanguage;
use function ougc\DisplayName\Core\urlHandlerBuild;
use function ougc\DisplayName\Core\urlHandlerGet;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

global $lang, $plugins, $mybb, $db;
global $page, $sub_tabs;
global $PL;

$PL || require_once MYBB_ROOT . 'inc/plugins/pluginlibrary.php';

loadLanguage();

$editObjects = [
    'portal.php' => [
        [
            'search' => ['$forumpermissions[$thread[\'fid\']] = forum_permissions($thread[\'fid\']);'],
            'before' => [
                '        $plugins->run_hooks("portal_discussion");',
            ],
        ],
    ],
    'inc/functions_forumlist.php' => [
        [
            'search' => ['if(!forum_password_validated($forum, true))'],
            'before' => [
                '			$hook_arguments = [',
                '				"lastpost_data" => &$lastpost_data',
                '			];',
                '			$hook_arguments = $plugins->run_hooks("build_forumbits_forum_intermediate", $hook_arguments);',
            ],
        ],
    ],
];

$url = urlHandlerGet();

$page->add_breadcrumb_item($lang->ougcDisplayNameMainTitle, $url);

$sub_tabs['ougc_display_name'] = [
    'title' => $lang->ougcDisplayNameMainTabTitle,
    'link' => $url,
    'description' => $lang->ougcDisplayNameMainTabTitleDescription
];

if (in_array($mybb->get_input('do'), ['apply', 'revert'])) {
    if (!verify_post_check($mybb->get_input('my_post_key'))) {
        flash_message($lang->invalid_post_verify_key2, 'error');

        admin_redirect($url);
    }

    $applyEdits = $mybb->get_input('do') === 'apply';

    if ($applyEdits) {
        $redirectText = $lang->ougcDisplayNamePageSuccessApply;
    } else {
        $redirectText = $lang->ougcDisplayNamePageSuccessRevert;
    }

    $file = $mybb->get_input('file');

    $redirectType = 'success';

    if (isset($editObjects[$file])) {
        if ($applyEdits) {
            if ($PL->edit_core('ougc_display_name', $file, $editObjects[$file], true) !== true) {
                $redirectType = 'error';

                $redirectText = $lang->ougcDisplayNamePageErrorApply;
            }
        } elseif ($PL->edit_core('ougc_display_name', $file, [], true) !== true) {
            $redirectType = 'error';

            $redirectText = $lang->ougcDisplayNamePageErrorRevert;
        }
    }

    flash_message($redirectText, $redirectType);

    admin_redirect($url);
} else {
    $page->output_header($lang->ougcDisplayNameMainTitle);

    $page->output_nav_tabs($sub_tabs, 'ougc_display_name');

    $table = new Table();

    $table->construct_header(
        $lang->ougcDisplayNameMainTableFile,
        ['width-' => '50%']
    );
    $table->construct_header(
        $lang->ougcDisplayNameMainTableStatus,
        ['width' => '40%', 'class' => 'align_center']
    );
    $table->construct_header(
        $lang->options,
        ['width' => '10%', 'class' => 'align_center']
    );

    $key = 0;

    foreach ($editObjects as $filePath => $fileEdits) {
        ++$key;

        $table->construct_cell(htmlspecialchars_uni($filePath));

        if ($PL->edit_core('ougc_display_name', $filePath, $fileEdits) === true) {
            $imageName = 'on';

            $imageText = $lang->alt_enabled;
        } else {
            $imageName = 'off';

            $imageText = $lang->alt_disabled;
        }

        $table->construct_cell(
            "<img src=\"styles/{$page->style}/images/icons/bullet_{$imageName}.png\" alt=\"({$imageText})\" title=\"{$imageText}\"  style=\"vertical-align: middle;\" /> ",
            ['class' => 'align_center']
        );

        $popup = new PopupMenu('filter_' . $key, $lang->options);

        $popup->add_item(
            $lang->ougcDisplayNameMainTableStatusOptionsApply,
            urlHandlerBuild([
                'do' => 'apply',
                'file' => $filePath,
                'my_post_key' => $mybb->post_code
            ])
        );

        $popup->add_item(
            $lang->ougcDisplayNameMainTableStatusOptionsRevert,
            urlHandlerBuild([
                'do' => 'revert',
                'file' => $filePath,
                'my_post_key' => $mybb->post_code
            ])
        );

        $table->construct_cell($popup->fetch(), ['class' => 'align_center']);

        $table->construct_row();
    }

    if (!$table->num_rows()) {
        $table->construct_cell(
            $lang->ougcDisplayNamePageTableEmpty,
            ['class' => 'align_center', 'colspan' => 4]
        );

        $table->construct_row();
    }

    $table->output($lang->ougcDisplayNameMainTableTitle);

    $page->output_footer();

    exit;
}
