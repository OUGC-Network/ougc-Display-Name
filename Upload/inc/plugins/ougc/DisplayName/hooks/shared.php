<?php

/***************************************************************************
 *
 *    ougc Display Name plugin (/inc/plugins/ougc/DisplayName/hooks/shared.php)
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

namespace ougc\DisplayName\Hooks\Shared;

use userDataHandler;

use function ougc\DisplayName\Core\clearProfile;
use function ougc\DisplayName\Core\loadLanguage;
use function ougc\DisplayName\Core\verifyDisplayName;

use const ougc\DisplayName\Core\VALIDATION_RESULT_BAD_CHARACTERS;
use const ougc\DisplayName\Core\VALIDATION_RESULT_BANNED;
use const ougc\DisplayName\Core\VALIDATION_RESULT_INVALID_CHARACTERS;
use const ougc\DisplayName\Core\VALIDATION_RESULT_INVALID_LENGTH;
use const ougc\DisplayName\Core\VALIDATION_RESULT_MISSING;
use const ougc\DisplayName\Core\VALIDATION_RESULT_SUCCESS;

function format_name90(array &$hookArguments): array
{
    $userData = get_user_by_username($hookArguments['username'], ['fields' => 'ougcDisplayName']);

    if (!empty($userData['ougcDisplayName'])) {
        $hookArguments['format'] = str_replace(
            '{username}',
            htmlspecialchars_uni($userData['ougcDisplayName']),
            $hookArguments['format']
        );
    }

    return $hookArguments;
}

function datahandler_user_validate(userDataHandler &$dataHandler): userDataHandler
{
    global $mybb;

    if (array_key_exists('ougcDisplayName', $mybb->input)) {
        global $lang;

        loadLanguage();

        $verifyDisplayName = verifyDisplayName($mybb->get_input('ougcDisplayName'));

        if ($verifyDisplayName !== VALIDATION_RESULT_SUCCESS) {
            switch ($verifyDisplayName) {
                case VALIDATION_RESULT_MISSING:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorMissing);
                    break;
                case VALIDATION_RESULT_BANNED:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorBanned);
                    break;
                case VALIDATION_RESULT_BAD_CHARACTERS:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorBadCharacters);
                    break;
                case VALIDATION_RESULT_INVALID_CHARACTERS:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorInvalidCharacters);
                    break;
                case VALIDATION_RESULT_INVALID_LENGTH:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorInvalidLength);
                    break;
                default:
                    $dataHandler->set_error($lang->ougcDisplayNameUserDataHandlerErrorIsProtected);
                    break;
            }
        }
    }

    return $dataHandler;
}

function datahandler_user_insert(userDataHandler &$dataHandler): userDataHandler
{
    if ($dataHandler->method === 'insert' || array_key_exists('username', $dataHandler->user_insert_data)) {
        $dataHandler->user_insert_data['ougcDisplayName'] = $dataHandler->user_insert_data['username'];
    }

    return $dataHandler;
}

function datahandler_user_update(userDataHandler &$dataHandler): userDataHandler
{
    global $mybb;

    $userData = &$dataHandler->data;

    if (!array_key_exists('ougcDisplayName', $userData) && array_key_exists('ougcDisplayName', $mybb->input)) {
        $userData['ougcDisplayName'] = $mybb->get_input('ougcDisplayName');
    }

    if (array_key_exists('ougcDisplayName', $userData)) {
        global $db;

        $dataHandler->user_update_data['ougcDisplayName'] = $db->escape_string($userData['ougcDisplayName']);
    }

    return $dataHandler;
}

function datahandler_user_clear_profile(userDataHandler &$dataHandler): userDataHandler
{
    if (clearProfile() === true) {
        global $db;

        $db->update_query('users', ['ougcDisplayName' => 'username'], "uid IN({$dataHandler->delete_uids})", '', true);
    }

    return $dataHandler;
}

function amnesia_personal_account_data_fields(array &$personalAccountDataFields): array
{
    $personalAccountDataFields['ougcDisplayName'] = true;

    return $personalAccountDataFields;
}