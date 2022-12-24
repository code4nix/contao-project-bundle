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

use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectSingleElementController;
use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectReaderController;
use Code4Nix\ContaoProjectBundle\Controller\FrontendModule\ProjectListController;

/**
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][ProjectSingleElementController::TYPE] = '{title_legend},name,headline,type;{config_legend},project_archive,project;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][ProjectReaderController::TYPE] = '{title_legend},name,headline,type;{config_legend},allowed_project_archives,overviewPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][ProjectListController::TYPE] = '{title_legend},name,headline,type;{config_legend},allowed_project_archives,project_featured;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['allowed_project_archives'] = [
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_project_archive.title',
    'eval'       => ['mandatory' => false, 'submitOnChange' => true, 'multiple' => true],
    'sql'        => "blob NULL",
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['project_archive'] = [
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_project_archive.title',
    'eval'       => ['mandatory' => false, 'includeBlankOption' => true, 'submitOnChange' => true, 'multiple' => false, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default 0",
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['project'] = [
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_project.title',
    'eval'       => ['mandatory' => true, 'includeBlankOption' => false, 'multiple' => false, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default 0",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['project_featured'] = [
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['all_items', 'featured', 'unfeatured'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['projects'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(16) COLLATE ascii_bin NOT NULL default 'all_items'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['project_order'] = [
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['project_order_title_asc', 'project_order_title_desc', 'project_order_sorting_asc', 'project_order_sorting_desc'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(32) COLLATE ascii_bin NOT NULL default 'order_date_desc'",
];
