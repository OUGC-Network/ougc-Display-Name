<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/hooks/forum.php)
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

namespace ougc\DisplayName\Hooks\Forum;

use UserDataHandler;

use function ougc\DisplayName\Core\control_db;
use function ougc\DisplayName\Core\fetchUserDisplayName;
use function ougc\DisplayName\Core\getSetting;
use function ougc\DisplayName\Core\getTemplate;
use function ougc\DisplayName\Core\loadLanguage;
use function ougc\DisplayName\Core\urlHandlerBuild;
use function ougc\DisplayName\Core\urlHandlerSet;

function global_start(): bool
{
    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    if (THIS_SCRIPT == 'usercp.php') {
        $templatelist .= ', ougcDisplayName_controlPanelNavigation, ougcDisplayName_controlPanelPage';
    }

    return true;
}

function global_intermediate09(): bool
{
    global $mybb;

    loadLanguage();

    if (!empty($mybb->user['ougcDisplayName'])) {
        global $lang;

        $mybb->user['username'] = htmlspecialchars_uni($mybb->user['ougcDisplayName']);

        $lang->welcome_back = $lang->sprintf(
            $lang->welcome_back,
            build_profile_link(htmlspecialchars_uni($mybb->user['username']), $mybb->user['uid'])
        );
    }

    control_db(
        '
    function query($string, $hide_errors = 0, $write_query = 0)
    {
        if (my_strpos($string, "fu.username AS fromusername") !== false) {
            $string = str_replace("fu.username AS fromusername", "fu.ougcDisplayName AS fromusername", $string);
        }
        
        if (my_strpos($string, "tu.username as tousername") !== false) {
            $string = str_replace("tu.username as tousername", "tu.ougcDisplayName as tousername", $string);
        }

        if (my_strpos($string, "t.username AS threadusername") !== false) {
            $string = str_replace(
                ["t.username AS threadusername", "u.username,", "u.username AS username"],
                ["u.ougcDisplayName AS threadusername", "u.ougcDisplayName as username,", "u.ougcDisplayName AS username"],
            $string);

            if (my_strpos($string, "ORDER BY t.sticky") !== false || my_strpos($string, "ORDER BY t.lastpost") !== false) {
                $string = str_replace(
                    [", u.username"],
                    [", u.ougcDisplayName AS username"],
                $string);
            }
        }

        return parent::query($string, $hide_errors, $write_query);
    }'
    );

    return true;
}

function private_read09(): bool
{
    control_db(
        '
    function simple_select($table, $fields="*", $conditions="", $options=array())
    {
        if ($table === "users" && my_strpos($fields, "uid, username") !== false && my_strpos($conditions, "uid IN (") !== false) {
            $fields = "uid, ougcDisplayName AS username";
        }

        return parent::simple_select($table, $fields, $conditions, $options);
    }'
    );

    return true;
}

function usercp_menu_built(): bool
{
    global $usercpnav;

    if (my_strpos($usercpnav, '<!--OUGC_DISPLAY_NAME-->') === false) {
        return false;
    }

    global $mybb, $lang;

    loadLanguage();

    urlHandlerSet('usercp.php');

    $pageUrl = urlHandlerBuild([
        'action' => getSetting('pageAction'),
    ]);

    $usercpnav = str_replace(
        '<!--OUGC_DISPLAY_NAME-->',
        eval(getTemplate('controlPanelNavigation')),
        $usercpnav
    );

    return true;
}

function usercp_start(): bool
{
    global $mybb;

    $pageAction = getSetting('pageAction');

    if ($mybb->get_input('action') !== $pageAction) {
        return false;
    }

    if (empty($mybb->usergroup['ougcDisplayNameCanChange'])) {
        error_no_permission();
    }

    global $lang, $templates;
    global $header, $footer, $headerinclude, $theme;
    global $usercpnav;

    loadLanguage();

    $pageUrl = urlHandlerBuild([
        'action' => getSetting('pageAction'),
    ]);

    add_breadcrumb($lang->nav_usercp, 'usercp.php');

    add_breadcrumb($lang->ougcDisplayNameUserControlPanelBreadcrumb, $pageUrl);

    if ($mybb->request_method == 'post') {
        verify_post_check($mybb->get_input('my_post_key'));

        require_once MYBB_ROOT . 'inc/datahandlers/user.php';

        $dataHandler = new UserDataHandler('update');

        $user = [
            'uid' => $mybb->user['uid'],
            'ougcDisplayName' => $mybb->get_input('ougcDisplayName')
        ];

        $dataHandler->set_data($user);

        if (!$dataHandler->validate_user()) {
            $errors = $dataHandler->get_friendly_errors();
        } else {
            $dataHandler->update_user();

            redirect($pageUrl, $lang->ougcDisplayNameUserControlPanelRedirect);
        }

        $displayName = htmlspecialchars_uni($mybb->get_input('ougcDisplayName'));
    } else {
        $displayName = htmlspecialchars_uni($mybb->user['ougcDisplayName']);
    }

    if (!empty($errors)) {
        $errors = inline_error($errors);
    } else {
        $errors = '';
    }

    $maximumLength = getSetting('maximumLength');

    output_page(eval(getTemplate('controlPanelPage')));

    return true;
}

function usercp_thread_subscriptions_thread09(): bool
{
    global $thread;

    return true;
}

function forumdisplay_announcement09(): bool
{
    global $announcement;

    if (!empty($announcement['username'])) {
        fetchUserDisplayName((int)$announcement['uid'], $announcement['username']);
    }

    if (!empty($announcement['username'])) {
        $announcement['username'] = htmlspecialchars_uni($announcement['username']);

        $announcement['profilelink'] = build_profile_link($announcement['username'], $announcement['uid']);
    }

    return true;
}

function forumdisplay_thread09(): bool
{
    global $thread;

    if (!empty($thread['username'])) {
        fetchUserDisplayName((int)$thread['uid'], $thread['username']);
    }


    if (!empty($thread['lastposter'])) {
        fetchUserDisplayName((int)$thread['lastposteruid'], $thread['lastposter']);
    }

    return true;
}

function newreply_threadreview_post09(): bool
{
    global $post;

    if (!empty($post['username'])) {
        fetchUserDisplayName((int)$post['uid'], $post['username']);
    }

    if ($post['username']) {
        $post['username'] = htmlspecialchars_uni($post['username']);

        $post['profilelink'] = build_profile_link($post['username'], $post['uid']);
    }

    return true;
}

function portal_start09(): bool
{
    global $mybb;

    if (!empty($mybb->user['ougcDisplayName'])) {
        global $lang;

        $mybb->user['username'] = htmlspecialchars_uni($mybb->user['ougcDisplayName']);

        $lang->welcome = $lang->sprintf($lang->welcome, $mybb->user['username']);
    }

    return true;
}

function portal_announcement09(): bool
{
    global $announcement, $profilelink;

    if (!empty($announcement['username'])) {
        fetchUserDisplayName((int)$announcement['uid'], $announcement['username']);
    }

    if (!empty($announcement['username'])) {
        $announcement['username'] = htmlspecialchars_uni($announcement['username']);

        $announcement['threadusername'] = $announcement['username'];

        if (empty($announcement['uid'])) {
            $profilelink = $announcement['threadusername'];
        } else {
            $profilelink = build_profile_link($announcement['username'], $announcement['uid']);
        }
    }

    return true;
}

function portal_discussion09(): bool
{
    global $thread;

    if (!empty($thread['username'])) {
        fetchUserDisplayName((int)$thread['uid'], $thread['username']);
    }

    if (!empty($thread['lastposter'])) {
        fetchUserDisplayName((int)$thread['lastposteruid'], $thread['lastposter']);
    }

    return true;
}

function search_results_thread09(): bool
{
    global $thread;

    if (!empty($thread['username'])) {
        fetchUserDisplayName((int)$thread['uid'], $thread['username']);
    }

    if ($thread['username']) {
        $thread['username'] = htmlspecialchars_uni($thread['username']);

        $thread['profilelink'] = build_profile_link($thread['username'], $thread['uid']);
    }

    if (!empty($thread['lastposter'])) {
        fetchUserDisplayName((int)$thread['lastposteruid'], $thread['lastposter']);
    }

    if ($thread['lastposter']) {
        global $lastposter, $lastposterlink;

        $lastposter = htmlspecialchars_uni($thread['lastposter']);

        $lastposterlink = build_profile_link($lastposter, $thread['lastposteruid']);
    }

    return true;
}

function search_results_post09(): bool
{
    global $post;

    if (!empty($post['username'])) {
        fetchUserDisplayName((int)$post['uid'], $post['username']);
    }

    if ($post['username']) {
        $post['username'] = htmlspecialchars_uni($post['username']);

        $post['profilelink'] = build_profile_link($post['username'], $post['uid']);
    }

    return true;
}

function index_start09(): bool
{
    return stats_start09();
}

function stats_start09(): bool
{
    global $mybb;

    if (!empty($mybb->cache->cache['statistics']['top_poster']) && !empty($mybb->cache->cache['statistics']['top_poster']['username'])) {
        fetchUserDisplayName(
            (int)$mybb->cache->cache['statistics']['top_poster']['uid'],
            $mybb->cache->cache['statistics']['top_poster']['username']
        );
    }

    if (!empty($mybb->cache->cache['stats']['lastuid']) && !empty($mybb->cache->cache['stats']['lastusername'])) {
        fetchUserDisplayName(
            (int)$mybb->cache->cache['stats']['lastuid'],
            $mybb->cache->cache['stats']['lastusername']
        );
    }

    return true;
}

function usercp_latest_threads_thread09(): bool
{
    global $thread;

    if (!empty($thread['username'])) {
        fetchUserDisplayName((int)$thread['uid'], $thread['username']);
    }

    if ($thread['username']) {
        $thread['username'] = htmlspecialchars_uni($thread['username']);

        $thread['profilelink'] = build_profile_link($thread['username'], $thread['uid']);
    }

    if (!empty($thread['lastposter'])) {
        fetchUserDisplayName((int)$thread['lastposteruid'], $thread['lastposter']);
    }

    if ($thread['lastposter']) {
        global $lastposteruid, $lastposter, $lastposterlink;

        $thread['lastposter'] = $lastposter = htmlspecialchars_uni($thread['lastposter']);

        if (!$lastposteruid) {
            $lastposterlink = $lastposter;
        } else {
            $lastposterlink = build_profile_link($lastposter, $lastposteruid);
        }
    }

    return true;
}

function archive_start09(): bool
{
    global $announcement;

    if (!empty($announcement['username'])) {
        fetchUserDisplayName((int)$announcement['uid'], $announcement['username']);
    }

    return true;
}

function archive_thread_post09(): bool
{
    global $post;

    if (!empty($post['username'])) {
        fetchUserDisplayName((int)$post['uid'], $post['username']);
    }

    if ($post['username']) {
        $post['username'] = htmlspecialchars_uni($post['username']);

        $post['profilelink'] = build_profile_link($post['username'], $post['uid']);
    }

    return true;
}

function build_forumbits_forum09(array &$forum): array
{
    if (empty($forum['lastposteruid'])) {
        return $forum;
    }

    if (!empty($forum['lastposter'])) {
        fetchUserDisplayName((int)$forum['lastposteruid'], $forum['lastposter']);
    }

    return $forum;
}

function build_forumbits_forum_intermediate09(array &$hookArguments): array
{
    if (empty($hookArguments['lastpost_data']['lastposteruid'])) {
        return $hookArguments;
    }

    if (!empty($hookArguments['lastpost_data']['lastposter'])) {
        fetchUserDisplayName(
            (int)$hookArguments['lastpost_data']['lastposteruid'],
            $hookArguments['lastpost_data']['lastposter']
        );
    }

    return $hookArguments;
}

function xmlhttp_edit_post_end09(): bool
{
    global $mybb, $templates;

    $templates->cache['postbit_editedby'] = str_replace(
        "{\$post['editedprofilelink']}",
        build_profile_link($mybb->user['ougcDisplayName'], $mybb->user['uid']),
        $templates->cache['postbit_editedby']
    );

    return true;
}

function showthread_start09(): bool
{
    control_db(
        '
    function query($string, $hide_errors = 0, $write_query = 0)
    {
        if (my_strpos($string, "eu.username AS editusername") !== false) {
            $string = str_replace("eu.username AS editusername", "eu.ougcDisplayName AS editusername", $string);
        }

        return parent::query($string, $hide_errors, $write_query);
    }'
    );

    return true;
}

function newreply_do_newreply_end09(): bool
{
    return showthread_start09();
}