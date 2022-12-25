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
    // Config
    'config'      => [
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

    // List
    'list'        => [
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

    // Palettes
    'palettes'    => [
        '__selector__' => ['showAdditionalContent'],
        'default'      => '{title_legend},title,alias,featured;{detail_legend},multiSRC;{additional_content_legend},showAdditionalContent;{expert_legend:hide},cssClass;{publish_legend},published',
        //'default'    => '{title_legend},title,alias,category,featured;{detail_legend},multiSRC,sizeGal,fullsizeGal;{additional_content_legend},showAdditionalContent;{expert_legend:hide},cssClass;{publish_legend},published',
    ],

    // Subpalettes
    'subpalettes' => [
        //'showAdditionalContent' => 'headline,introText,projectText,singleSRCOne,singleSRCTwo,singleSRCThree,sizeImg,fullsizeImg',
        'showAdditionalContent' => 'headline,introText,projectText,singleSRCOne,singleSRCTwo,singleSRCThree',
    ],

    // Fields
    'fields'      => [
        'id'                    => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                   => [
            'foreignKey' => 'tl_project_archive.title',
            'sql'        => "int(10) unsigned NOT NULL default 0",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp'                => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'sorting'               => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'title'                 => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'alias'                 => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'alias', 'doNotCopy' => true, 'unique' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) BINARY NOT NULL default ''",
        ],
        'category'              => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'select',
            'options'   => ['cat_1', 'cat_2', 'cat_3', 'cat_4', 'cat_5'],
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'featured'              => [
            'exclude'   => true,
            'toggle'    => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],

        // Details
        'showAdditionalContent' => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'fullsizeGal'           => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'sizeGal'               => [
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static function () {
                return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'multiSRC'              => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['isGallery' => true, 'extensions' => System::getContainer()->getParameter('contao.image.valid_extensions'), 'multiple' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC', 'files' => true, 'mandatory' => true],
            'sql'       => "blob NULL",
        ],
        'orderSRC'              => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['sortOrder'],
            'sql'   => "blob NULL",
        ],

        // Additional content
        'headline'              => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'introText'                  => [
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr w50'],
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ],
        'projectText'                  => [
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr w50'],
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ],
        'singleSRCOne'          => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "binary(16) NULL",
        ],
        'singleSRCTwo'          => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "binary(16) NULL",
        ],
        'singleSRCThree'        => [
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "binary(16) NULL",
        ],
        'sizeImg'               => [
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'clr w50'],
            'options_callback' => static function () {
                return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fullsizeImg'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'cssClass'              => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'             => [
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
