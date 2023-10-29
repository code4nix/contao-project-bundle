<?php

declare(strict_types=1);

/*
 * This file is part of Contao Project Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/code4nix/contao-project-bundle
 */

use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectSingleElementController;
use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectReaderController;
use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectListController;

/**
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['content'] = 'Inhalte';
$GLOBALS['TL_LANG']['MOD']['projects'] = ['Projekte', 'Projekte'];

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['projects'] = 'Projekte';
$GLOBALS['TL_LANG']['FMD'][ProjectListController::TYPE] = ['Projektliste', 'Fügen Sie Ihrem Layout ein Projektlisten Modul hinzu.'];
