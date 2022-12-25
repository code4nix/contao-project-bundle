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

use Code4Nix\ContaoProjectBundle\Controller\ContentElement\ProjectTeaserLinkController;
use Contao\DataContainer;

/**
 * Content element
 */
$GLOBALS['TL_DCA']['tl_content']['palettes'][ProjectTeaserLinkController::TYPE] = '{type_legend},type,project_teaser_link_headline,project_teaser_link_layout;{project_legend},project_archive,project;{project_teaser_link_image_legend},project_teaser_link_image;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['project_teaser_link_headline'] = [
    'exclude'   => true,
    'search'    => true,
    'sorting'   => true,
    'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'sql'       => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['project_teaser_link_layout'] = [
    'exclude'   => true,
    'search'    => true,
    'sorting'   => true,
    'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
    'inputType' => 'select',
    'options'   => ['one_third', 'two_third', 'three_third'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['projects'],
    'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
    'sql'       => "varchar(255) NOT NULL default 'one_third'",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['project_teaser_link_image'] = [
    'exclude'   => true,
    'inputType' => 'fileTree',
    'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr w50'],
    'sql'       => "binary(16) NULL",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['project_archive'] = [
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_project_archive.title',
    'eval'       => ['mandatory' => false, 'includeBlankOption' => true, 'submitOnChange' => true, 'multiple' => false, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default 0",
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['project'] = [
    'exclude'    => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_project.title',
    'eval'       => ['mandatory' => true, 'includeBlankOption' => false, 'multiple' => false, 'tl_class' => 'w50'],
    'sql'        => "int(10) unsigned NOT NULL default 0",
];
