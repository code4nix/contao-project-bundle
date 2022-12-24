<?php

declare(strict_types=1);

/*
 * This file is part of Contao Project Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/code4nix/contao-project-bundle
 */

namespace Code4Nix\ContaoProjectBundle\Security;

final class ContaoProjectPermissions
{
    public const USER_CAN_EDIT_ARCHIVE = 'contao_user.projects';
    public const USER_CAN_CREATE_ARCHIVES = 'contao_user.projectp.create';
    public const USER_CAN_DELETE_ARCHIVES = 'contao_user.projectp.delete';
}
