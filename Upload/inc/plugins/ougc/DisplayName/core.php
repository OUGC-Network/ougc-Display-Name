<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/core.php)
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

namespace ougc\DisplayName\Core;

use AbstractPdoDbDriver;
use DB_SQLite;
use PluginLibrary;
use ReflectionProperty;

use const ougc\DisplayName\ROOT;

const VALIDATION_RESULT_SUCCESS = 1;

const VALIDATION_RESULT_MISSING = 2;

const VALIDATION_RESULT_BANNED = 3;

const VALIDATION_RESULT_BAD_CHARACTERS = 4;

const VALIDATION_RESULT_INVALID_LENGTH = 5;

const VALIDATION_RESULT_INVALID_CHARACTERS = 6;

const URL = 'index.php?module=config-ougc_display_name';

function addHooks(string $namespace)
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }
}

function urlHandler(string $newUrl = ''): string
{
    static $setUrl = URL;

    if (($newUrl = trim($newUrl))) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlHandlerSet(string $newUrl)
{
    urlHandler($newUrl);
}

function urlHandlerGet(): string
{
    return urlHandler();
}

function urlHandlerBuild(array $urlAppend = [], bool $fetchImportUrl = false, bool $encode = true): string
{
    global $PL;

    if (!($PL instanceof PluginLibrary)) {
        $PL || require_once PLUGINLIBRARY;
    }

    if ($fetchImportUrl === false) {
        if ($urlAppend && !is_array($urlAppend)) {
            $urlAppend = explode('=', $urlAppend);
            $urlAppend = [$urlAppend[0] => $urlAppend[1]];
        }
    }

    return $PL->url_append(urlHandlerGet(), $urlAppend, '&amp;', $encode);
}

function loadLanguage(): bool
{
    global $lang;

    if (!isset($lang->ougcDisplayName)) {
        $lang->load('ougcDisplayName');
    }

    return true;
}

function getTemplateName(string $templateName = ''): string
{
    $templatePrefix = '';

    if ($templateName) {
        $templatePrefix = '_';
    }

    return "ougcDisplayName{$templatePrefix}{$templateName}";
}

function getTemplate(string $templateName = '', bool $enableHTMLComments = true): string
{
    global $templates;

    if (DEBUG) {
        $filePath = ROOT . "/templates/{$templateName}.html";

        $templateContents = file_get_contents($filePath);

        $templates->cache[getTemplateName($templateName)] = $templateContents;
    } elseif (my_strpos($templateName, '/') !== false) {
        $templateName = substr($templateName, strpos($templateName, '/') + 1);
    }

    return $templates->render(getTemplateName($templateName), true, $enableHTMLComments);
}

function getSetting(string $settingKey = '')
{
    global $mybb;

    return SETTINGS[$settingKey] ?? (
        $mybb->settings['ougcDisplayName_' . $settingKey] ?? false
    );
}

function verifyDisplayName(string $displayName): int
{
    loadLanguage();

    require_once MYBB_ROOT . 'inc/functions_user.php';

    $displayName = trim_blank_chrs($displayName);

    $displayName = str_replace([
        unichr(160),
        unichr(173),
        unichr(0xCA),
        dec_to_utf8(8238),
        dec_to_utf8(8237),
        dec_to_utf8(8203)
    ],
        [' ', '-', '', '', '', ''],
        $displayName
    );

    $displayName = preg_replace('#\s{2,}#', ' ', $displayName);

    if ($displayName === '') {
        return VALIDATION_RESULT_MISSING;
    }

    if (isBannedDisplayName($displayName)) {
        return VALIDATION_RESULT_BANNED;
    }

    if (
        my_strpos($displayName, '<') !== false ||
        my_strpos($displayName, '>') !== false ||
        my_strpos($displayName, '&') !== false ||
        my_strpos($displayName, "\\") !== false ||
        my_strpos($displayName, ';') !== false ||
        my_strpos($displayName, ',') !== false ||
        !validate_utf8_string($displayName, false, false)
    ) {
        return VALIDATION_RESULT_BAD_CHARACTERS;
    }

    $minimumLength = (int)getSetting('minimumLength');

    $maximumLength = (int)getSetting('maximumLength');

    if (
        ($maximumLength && my_strlen($displayName) > $maximumLength) ||
        ($minimumLength && my_strlen($displayName) < $minimumLength)
    ) {
        return VALIDATION_RESULT_INVALID_LENGTH;
    }

    if (getSetting('regularExpressions')) {
        $regularExpressions = explode("\n", getSetting('regularExpressions'));

        $clearedUsername = preg_replace($regularExpressions, '', $displayName);

        if ($displayName !== $clearedUsername) {
            return VALIDATION_RESULT_INVALID_CHARACTERS;
        }
        // https://stackoverflow.com/questions/23130740/determining-and-removing-invisible-characters-from-a-string-in-php-e2808e
    }

    return VALIDATION_RESULT_SUCCESS;
}

// todo: implement this function similar to the repeated username feature
function displayNameIsProtected(string $displayName): bool
{
    return false;
}

function isBannedDisplayName(string $displayName): bool
{
    require_once MYBB_ROOT . 'inc/functions_user.php';

    return is_banned_username($displayName);
}

function clearProfile(): bool
{
    return (bool)getSetting('clearProfile');
}

function getUserByDisplayName(string $displayName, array $queryOptions = [])
{
    global $db;

    $displayName = $db->escape_string(my_strtolower($displayName));

    switch ($db->type) {
        case 'mysql':
        case 'mysqli':
            $field = 'ougcDisplayName';
            break;
        default:
            $field = 'LOWER(ougcDisplayName)';
            break;
    }

    $fields = ['uid'];

    if (isset($queryOptions['fields'])) {
        $fields = array_merge((array)$queryOptions['fields'], $fields);
    }

    $query = $db->simple_select(
        'users',
        implode(',', array_unique($fields)),
        "{$field}='{$displayName}'",
        ['limit' => 1]
    );

    if (!$db->num_rows($query)) {
        return [];
    }

    return (array)$db->fetch_array($query);
}

function fetchUserDisplayName(int $userID, string &$userName): string
{
    static $usersCache = [];

    if (!isset($usersCache[$userID])) {
        $userData = get_user($userID);

        if (!empty($userData['ougcDisplayName'])) {
            $userName = $userData['ougcDisplayName'];
        }
    }

    return $userName;
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com )
function control_object(&$obj, $code)
{
    static $cnt = 0;
    $newname = '_objcont_ougc_contract_system_' . (++$cnt);
    $objserial = serialize($obj);
    $classname = get_class($obj);
    $checkstr = 'O:' . strlen($classname) . ':"' . $classname . '":';
    $checkstr_len = strlen($checkstr);
    if (substr($objserial, 0, $checkstr_len) == $checkstr) {
        $vars = [];
        // grab resources/object etc, stripping scope info from keys
        foreach ((array)$obj as $k => $v) {
            if ($p = strrpos($k, "\0")) {
                $k = substr($k, $p + 1);
            }
            $vars[$k] = $v;
        }
        if (!empty($vars)) {
            $code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
        }
        eval('class ' . $newname . ' extends ' . $classname . ' {' . $code . '}');
        $obj = unserialize('O:' . strlen($newname) . ':"' . $newname . '":' . substr($objserial, $checkstr_len));
        if (!empty($vars)) {
            $obj->___setvars($vars);
        }
    }
    // else not a valid object or PHP serialize has changed
}

// explicit workaround for PDO, as trying to serialize it causes a fatal error (even though PHP doesn't complain over serializing other resources)
if ($GLOBALS['db'] instanceof AbstractPdoDbDriver) {
    $GLOBALS['AbstractPdoDbDriver_lastResult_prop'] = new ReflectionProperty('AbstractPdoDbDriver', 'lastResult');
    $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setAccessible(true);
    function control_db($code)
    {
        global $db;
        $linkvars = [
            'read_link' => $db->read_link,
            'write_link' => $db->write_link,
            'current_link' => $db->current_link,
        ];
        unset($db->read_link, $db->write_link, $db->current_link);
        $lastResult = $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->getValue($db);
        $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setValue($db, null); // don't let this block serialization
        control_object($db, $code);
        foreach ($linkvars as $k => $v) {
            $db->$k = $v;
        }
        $GLOBALS['AbstractPdoDbDriver_lastResult_prop']->setValue($db, $lastResult);
    }
} elseif ($GLOBALS['db'] instanceof DB_SQLite) {
    function control_db($code)
    {
        global $db;
        $oldLink = $db->db;
        unset($db->db);
        control_object($db, $code);
        $db->db = $oldLink;
    }
} else {
    function control_db($code)
    {
        control_object($GLOBALS['db'], $code);
    }
}