<?php

/*
 * This file is part of Contao Project Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/code4nix/contao-project-bundle
 */

use Code4Nix\ContaoProjectBundle\Model\ProjectArchiveModel;
use Code4Nix\ContaoProjectBundle\Model\ProjectModel;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['projects'] = [
    'tables' => ['tl_project_archive', 'tl_project'],
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_project_archive'] = ProjectArchiveModel::class;
$GLOBALS['TL_MODELS']['tl_project'] = ProjectModel::class;

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'projects';
$GLOBALS['TL_PERMISSIONS'][] = 'projectp';
