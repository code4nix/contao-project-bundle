<?php

declare(strict_types=1);

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_project_archive'] = [
    // Config
    'config'      => [
        'dataContainer'    => DC_Table::class,
        'ctable'           => ['tl_project'],
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'markAsCopy'       => 'title',
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'        => DataContainer::MODE_SORTED,
            'fields'      => ['title'],
            'flag'        => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
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
            'edit'       => [
                'href' => 'table=tl_project',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ],
            'copy'       => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete'     => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => ['protected'],
        'default'      => '{title_legend},title,jumpToDetail,jumpToAdditional;{protected_legend:hide},protected',
    ],

    // Subpalettes
    'subpalettes' => [
        'protected' => 'groups',
    ],

    // Fields
    'fields'      => [
        'id'               => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'           => [
            'sql' => "int(10) unsigned NOT NULL default 0",
        ],
        'title'            => [
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'jumpToDetail'     => [
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'        => "int(10) unsigned NOT NULL default 0",
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'jumpToAdditional' => [
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'        => "int(10) unsigned NOT NULL default 0",
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'protected'        => [
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['mandatory' => true, 'multiple' => true],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
    ],
];
