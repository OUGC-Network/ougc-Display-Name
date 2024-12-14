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