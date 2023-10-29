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

namespace Code4Nix\ContaoProjectBundle\DataContainer;

use Code4Nix\ContaoProjectBundle\Controller\ContentElement\ProjectTeaserLinkController;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Content
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    #[AsCallback(table: 'tl_content', target: 'config.onload', priority: 100)]
    public function setPalette(DataContainer $dc): void
    {
        if ($dc->id) {
            $projectArchiveId = $this->connection->fetchOne('SELECT project_archive FROM tl_content WHERE id = ?', [$dc->id]);

            if (!$projectArchiveId || !$this->connection->fetchOne('SELECT id FROM tl_project_archive WHERE id = ?', [$projectArchiveId])) {
                PaletteManipulator::create()
                    ->removeField('project', 'project_legend')
                    ->applyToPalette(ProjectTeaserLinkController::TYPE, 'tl_content')
                ;
            }
        }
    }

    /**
     * @throws Exception
     */
    #[AsCallback(table: 'tl_content', target: 'fields.project.options', priority: 100)]
    public function getProjects(?DataContainer $dc): array
    {
        $options = [];

        if (!$dc || !$dc->id || !$dc->activeRecord->project_archive) {
            return $options;
        }

        $result = $this->connection->executeQuery('SELECT id,title FROM tl_project WHERE pid = ?', [$dc->activeRecord->project_archive]);

        while (false !== ($row = $result->fetchAssociative())) {
            $options[$row['id']] = $row['title'];
        }

        return $options;
    }
}
