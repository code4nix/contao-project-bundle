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

namespace Code4Nix\ContaoProjectBundle\Controller\ContentElement;

use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Code4Nix\ContaoProjectBundle\Traits\ProjectDetailTrait;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\File;
use Contao\FilesModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'projects', template: 'ce_project_teaser_link')]
class ProjectTeaserLinkController extends AbstractContentElementController
{
    use ProjectDetailTrait;

    public const TYPE = 'project_teaser_link';

    protected ScopeMatcher $scopeMatcher;
    protected string $projectDir;
    protected ?ProjectModel $project = null;

    public function __construct(ScopeMatcher $scopeMatcher, string $projectDir)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->projectDir = $projectDir;
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        if (null === ($this->project = ProjectModel::findByPk($model->project))) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $allowedProjectArchives = $this->sortOutProtected([$model->project_archive]);

        if (!\in_array($this->project->pid, $allowedProjectArchives, false)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        // Show additional content in the template if contao scope is "backend"
        $template->scope = $this->scopeMatcher->isBackendRequest($request) ? 'backend' : 'frontend';

        $arrProject = $this->getProjectDetails($this->project);

        // Add project data as well
        $template->project = $arrProject;

        // Headline
        $template->headline = $model->project_teaser_link_headline;

        // Layout
        System::loadLanguageFile('tl_content');
        $template->layout = $model->project_teaser_link_layout;
        $template->layout_translation = $GLOBALS['TL_LANG']['tl_content']['projects'][$model->project_teaser_link_layout];

        // Link
        if ($this->project->showAdditionalContent && $arrProject['hasAdditionalContentLink']) {
            $template->link = $arrProject['additionalContentLink'];
        } else {
            $template->link = $arrProject['hasDetailLink'] ? $arrProject['detailLink'] : '';
        }

        // Image
        $template->image_uuid = false;

        $objFile = FilesModel::findByUuid($model->project_teaser_link_image);

        if (null !== $objFile) {
            if (is_file($this->projectDir.'/'.$objFile->path)) {
                $file = new File($objFile->path);

                if ($file && $file->isGdImage) {
                    $template->image_uuid = StringUtil::binToUuid($model->project_teaser_link_image);
                }
            }
        }

        return $template->getResponse();
    }
}
