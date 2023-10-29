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

namespace Code4Nix\ContaoProjectBundle\Traits;

use Code4Nix\ContaoProjectBundle\Model\ProjectArchiveModel;
use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Contao\ArrayUtil;
use Contao\Config;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\File;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;

trait ProjectDetailTrait
{
    protected function getProjectDetails(ProjectModel $project): array
    {
        $arrProject = $project->row();

        $arrProject['singleSRC'] = !empty($arrProject['singleSRC']) ? StringUtil::binToUuid($arrProject['singleSRC']) : '';

        // Gallery
        $arrGallery = $this->getGallery($project);
        $arrProject['addGallery'] = !empty($arrGallery);
        $arrProject['gallery'] = !empty($arrGallery) ? $arrGallery : null;

        return $arrProject;
    }

    /**
     * @throws \Exception
     */
    protected function getJumpToPage(string $jumpToType, ProjectModel $projectModel): Collection|ProjectArchiveModel|null
    {
        if ('jumpToDetail' !== $jumpToType) {
            throw new \Exception('Parameter "$jumpToType" has to be one of this: "jumpToDetail".');
        }

        $projectArchiveModel = ProjectArchiveModel::findByPk($projectModel->pid);

        if (null === $projectArchiveModel) {
            return null;
        }

        if (($objTarget = $projectArchiveModel->getRelated($jumpToType)) instanceof PageModel) {
            return $objTarget;
        }

        return null;
    }

    protected function getDetailLink(ProjectModel $projectModel): string|null
    {
        if (null !== ($targetPage = $this->getJumpToPage('jumpToDetail', $projectModel))) {
            $params = (Config::get('useAutoItem') ? '/' : '/items/').($projectModel->alias ?: $projectModel->id);

            return StringUtil::ampersand($targetPage->getFrontendUrl($params));
        }

        return null;
    }

    protected function getGallery(ProjectModel $project): array
    {
        $multiSRC = StringUtil::deserialize($project->multiSRC);

        // Return if there are no files
        if (empty($multiSRC) || !\is_array($multiSRC)) {
            return [];
        }

        // Get the file entries from the database
        $objFiles = FilesModel::findMultipleByUuids($multiSRC);

        if (null === $objFiles) {
            return [];
        }

        $images = [];

        // Get all images
        while ($objFiles->next()) {
            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(System::getContainer()->getParameter('kernel.project_dir').'/'.$objFiles->path)) {
                continue;
            }

            // Single files
            if ('file' === $objFiles->type) {
                $objFile = new File($objFiles->path);

                if (!$objFile->isImage) {
                    continue;
                }

                // Add the image
                $images[$objFiles->path] = $objFiles->current();
            }

            // Folders
            else {
                $objSubfiles = FilesModel::findByPid($objFiles->uuid, ['order' => 'name']);

                if (null === $objSubfiles) {
                    continue;
                }

                while ($objSubfiles->next()) {
                    // Skip subfolders
                    if ('folder' === $objSubfiles->type) {
                        continue;
                    }

                    $objFile = new File($objSubfiles->path);

                    if (!$objFile->isImage) {
                        continue;
                    }

                    // Add the image
                    $images[$objSubfiles->path] = $objSubfiles->current();
                }
            }
        }

        $images = ArrayUtil::sortByOrderField($images, $project->orderSRC);
        $images = array_values($images);

        $arrGallery = [];

        foreach ($images as $file) {
            $arrFile = $file->row();
            $arrFile['uuid'] = StringUtil::binToUuid($file->uuid);
            $arrFile['fullsize'] = $project->fullsizeMultiSRC;
            $arrGallery[] = $arrFile;
        }

        return $arrGallery;
    }

    /**
     * Sort out protected archives.
     */
    protected function sortOutProtected(array $arrArchives): array
    {
        if (empty($arrArchives)) {
            return $arrArchives;
        }

        $objArchive = ProjectArchiveModel::findMultipleByIds($arrArchives);
        $arrArchives = [];

        if (null !== $objArchive) {
            $security = System::getContainer()->get('security.helper');

            while ($objArchive->next()) {
                if ($objArchive->protected && !$security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, StringUtil::deserialize($objArchive->groups, true))) {
                    continue;
                }

                $arrArchives[] = $objArchive->id;
            }
        }

        return $arrArchives;
    }
}
