<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_project'] = [
    'config'   => [
        'dataContainer'    => DC_Table::class,
        'ptable'           => 'tl_project_archive',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'markAsCopy'       => 'title',
        'sql'              => [
            'keys' => [
                'id'                     => 'primary',
                'alias'                  => 'index',
                'pid,published,featured' => 'index',
            ],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'         => DataContainer::MODE_PARENT,
            'fields'       => ['sorting'],
            'headerFields' => ['title', 'jumpTo', 'tstamp', 'protected'],
            'panelLayout'  => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'    => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy'    => [
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
            ],
            'cut'     => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
            ],
            'delete'  => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle'  => [
                'href'         => 'act=toggle&amp;field=published',
                'icon'         => 'visible.svg',
                'showInHeader' => true,
            ],
            'feature' => [
                'href' => 'act=toggle&amp;field=featured',
                'icon' => 'featured.svg',
            ],
            'show'    => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['addImage', 'addGallery'],
        'default'      => '
        {title_legend},title,alias,category,featured;
        {detail_legend},headlineOne,headlineTwo,text;
        {image_legend},addImage;
        {gallery_legend},addGallery;
        {expert_legend:hide},cssClassListing,cssClass;
        {publish_legend},published
        ',
    ],
    'subpalettes' => [
        'addImage'   => 'singleSRC,sizeSingleSRC,fullsizeSingleSRC',
        'addGallery' => 'multiSRC,sizeMultiSRC,fullsizeMultiSRC',
    ],
    'fields'      => [
        'id'                => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'               => [
            'foreignKey' => 'tl_project_archive.title',
            'sql'        => "int(10) unsigned NOT NULL default 0",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp'            => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'sorting'           => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'title'             => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'alias'             => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) BINARY NOT NULL default ''",
        ],
        'category'          => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'select',
            'options'   => ['cat_1', 'cat_2', 'cat_3', 'cat_4', 'cat_5'],
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'featured'          => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        // Details
        'headlineOne'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'headlineTwo'       => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'text'              => [
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => true, 'helpwizard' => true, 'tl_class' => 'clr w100'],
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ],
        // Single image
        'addImage'          => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'singleSRC'         => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "binary(16) NULL",
        ],
        'sizeSingleSRC'     => [
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'clr w50'],
            'options_callback' => static function () {
                return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fullsizeSingleSRC' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        // Gallery
        'addGallery'        => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'multiSRC'          => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['isGallery' => true, 'extensions' => System::getContainer()->getParameter('contao.image.valid_extensions'), 'multiple' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC', 'files' => true, 'mandatory' => true],
            'sql'       => "blob NULL",
        ],
        'sizeMultiSRC'      => [
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static function () {
                return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fullsizeMultiSRC'  => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'orderSRC'          => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['sortOrder'],
            'sql'   => "blob NULL",
        ],
        'cssClassListing'          => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'cssClass'          => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'         => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];
