<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougcDisplayName.php)
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

use function ougc\DisplayName\Admin\pluginInfo;
use function ougc\DisplayName\Admin\pluginActivate;
use function ougc\DisplayName\Admin\pluginDeactivate;
use function ougc\DisplayName\Admin\pluginInstall;
use function ougc\DisplayName\Admin\pluginIsInstalled;
use function ougc\DisplayName\Admin\pluginUninstall;
use function ougc\DisplayName\Core\addHooks;

use const ougc\DisplayName\ROOT;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

// You can uncomment the lines below to avoid storing some settings in the DB
define('ougc\DisplayName\Core\SETTINGS', [
    //'key' => '',
]);

define('ougc\DisplayName\Core\DEBUG', false);

define('ougc\DisplayName\ROOT', constant('MYBB_ROOT') . 'inc/plugins/ougc/DisplayName');

require_once ROOT . '/core.php';

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', constant('MYBB_ROOT') . 'inc/plugins/pluginlibrary.php');

if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    addHooks('ougc\DisplayName\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    addHooks('ougc\DisplayName\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

addHooks('ougc\DisplayName\Hooks\Shared');

function ougcDisplayName_info(): array
{
    return pluginInfo();
}

function ougcDisplayName_activate(): bool
{
    return pluginActivate();
}

function ougcDisplayName_deactivate(): bool
{
    return pluginDeactivate();
}

function ougcDisplayName_install(): bool
{
    return pluginInstall();
}

function ougcDisplayName_is_installed(): bool
{
    return pluginIsInstalled();
}

function ougcDisplayName_uninstall(): bool
{
    return pluginUninstall();
}

function ougcDisplayNameGet(
    int $userID = 0,
    bool $formatName = true,
    bool $profileLink = true,
    bool $getByUsername = false,
    string $userName = ''
): string {
    if ($getByUsername) {
        $userData = get_user_by_username(
            $userName,
            ['fields' => 'username', 'ougcDisplayName', 'usergroup', 'displaygroup']
        );
    } else {
        $userData = get_user($userID);
    }

    if (empty($userData['uid'])) {
        return (string)$userName;
    }

    $displayName = htmlspecialchars_uni($userData['ougcDisplayName']);

    if ($profileLink) {
        $displayName = build_profile_link($displayName, $userData['uid']);
    }

    if ($formatName) {
        global $ougcDisplayNameSkip;

        $ougcDisplayNameSkip = true;

        $displayName = format_name($displayName, $userData['usergroup'], $userData['displaygroup']);

        $ougcDisplayNameSkip = false;
    }

    return $displayName;
}

function ougcDisplayNameGetByUsername(
    string $userName,
    bool $formatName = true,
    bool $profileLink = true
): string {
    return ougcDisplayNameGet(0, $formatName, $profileLink, true, $userName);
}

function _dump()
{
    global $mybb;

    if (!((int)$mybb->user['uid'] === 1)) {
        return false;
    }

    $args = func_get_args();

    echo '<pre>';
    var_dump($args);
    echo '</pre>';
    exit;
}