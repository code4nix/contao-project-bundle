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

namespace Code4Nix\ContaoProjectBundle\Traits;

use Code4Nix\ContaoProjectBundle\Model\ProjectArchiveModel;
use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Contao\ArrayUtil;
use Contao\Config;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\File;
use Contao\FilesModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;

trait ProjectDetailTrait
{
    protected function getProjectDetails(ProjectModel $project): array
    {
        $arrProject = $project->row();

        $detailHref = $this->getDetailLink($project);
        $arrProject['hasDetailLink'] = (bool) $detailHref;
        $arrProject['detailLink'] = $detailHref;

        $additionalHref = $this->getAdditionalLink($project);
        $arrProject['hasAdditionalContentLink'] = (bool) $additionalHref;
        $arrProject['additionalContentLink'] = $additionalHref;

        $arrProject['singleSRCOne'] = !empty($arrProject['singleSRCOne']) ? StringUtil::binToUuid($arrProject['singleSRCOne']) : false;
        $arrProject['singleSRCTwo'] = !empty($arrProject['singleSRCOne']) ? StringUtil::binToUuid($arrProject['singleSRCTwo']) : false;
        $arrProject['singleSRCThree'] = !empty($arrProject['singleSRCOne']) ? StringUtil::binToUuid($arrProject['singleSRCThree']) : false;

        // Gallery
        $arrGallery = $this->getGallery($project);
        $arrProject['hasGallery'] = !empty($arrGallery);
        $arrProject['multiSRC'] = !empty($arrGallery) ? $arrGallery : false;

        return $arrProject;
    }

    /**
     * @throws \Exception
     */
    protected function getJumpToPage(string $jumpToType, ProjectModel $projectModel): ?PageModel
    {
        if ('jumpToDetail' !== $jumpToType && 'jumpToAdditional' !== $jumpToType) {
            throw new \Exception('Parameter "$jumpToType" has to be either "jumpToDetail" or "jumpToAdditional".');
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

    protected function getDetailLink(ProjectModel $projectModel): ?string
    {
        if (null !== ($targetPage = $this->getJumpToPage('jumpToDetail', $projectModel))) {
            $params = (Config::get('useAutoItem') ? '/' : '/items/').($projectModel->alias ?: $projectModel->id);

            return StringUtil::ampersand($targetPage->getFrontendUrl($params));
        }

        return null;
    }

    protected function getAdditionalLink(ProjectModel $projectModel): ?string
    {
        if (null !== ($targetPage = $this->getJumpToPage('jumpToAdditional', $projectModel))) {
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
        $auxDate = [];

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
                $auxDate[] = $objFile->mtime;
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
                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        $images = ArrayUtil::sortByOrderField($images, $project->orderSRC);
        $images = array_values($images);

        $arrGallery = [];

        foreach ($images as $file) {
            $arrFile = $file->row();
            $arrFile['uuid'] = StringUtil::binToUuid($file->uuid);
            $arrGallery[] = $arrFile;
        }

        return $arrGallery;
    }

    /**
     * Sort out protected archives.
     *
     * @param array $arrArchives
     *
     * @return array
     */
    protected function sortOutProtected($arrArchives)
    {
        if (empty($arrArchives) || !\is_array($arrArchives)) {
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
