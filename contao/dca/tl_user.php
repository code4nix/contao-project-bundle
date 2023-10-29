<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend the default palettes
PaletteManipulator::create()
    ->addLegend('project_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['projects', 'projectp'], 'project_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user');

/**
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['projects'] = [
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_project_archive.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_user']['fields']['projectp'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL",
];
