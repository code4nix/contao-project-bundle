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

namespace Code4Nix\ContaoProjectBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Module
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    #[AsCallback(table: 'tl_module', target: 'fields.project.options', priority: 100)]
    public function getProjects(DataContainer|null $dc): array
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
