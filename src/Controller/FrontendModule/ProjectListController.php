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

namespace Code4Nix\ContaoProjectBundle\Controller\FrontendModule;

use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Code4Nix\ContaoProjectBundle\Traits\ProjectDetailTrait;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'projects', template: 'mod_project_list')]
class ProjectListController extends AbstractFrontendModuleController
{
    use ProjectDetailTrait;

    public const TYPE = 'project_list';

    protected ProjectModel|null $project = null;
    protected ScopeMatcher $scopeMatcher;
    protected array|null $allowedProjectArchives = null;
    protected PageModel|null $page = null;

    public function __construct(ScopeMatcher $scopeMatcher)
    {
        $this->scopeMatcher = $scopeMatcher;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        if ($this->scopeMatcher->isFrontendRequest($request)) {
            if ($this->isAjaxRequest() && $request->get('project_id') && $request->get('module_uuid')) {
                $page->noSearch = 1;
            }

            $this->allowedProjectArchives = $this->sortOutProtected(StringUtil::deserialize($model->allowed_project_archives, true));

            // Return if there are no archives
            if (empty($this->allowedProjectArchives)) {
                return new Response('', Response::HTTP_NO_CONTENT);
            }

            // Tag the project archives (see #2137)
            if (System::getContainer()->has('fos_http_cache.http.symfony_response_tagger')) {
                $responseTagger = System::getContainer()->get('fos_http_cache.http.symfony_response_tagger');
                $responseTagger->addTags(array_map(static fn ($id) => 'contao.db.tl_project_archive.'.$id, $this->allowedProjectArchives));
            }
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if ($this->isAjaxRequest() && $request->get('project_id') && $request->get('module_uuid')) {
            $json = [];
            $project = ProjectModel::findByPk($request->get('project_id'));

            if (null === $project) {
                $json['success'] = 'false';
                $response = new JsonResponse($json);

                throw new ResponseException($response);
            }

            $template = new FrontendTemplate('_project_detail');
            $template->project = $this->getProjectDetails($project);
            $template->module = $model;
            $template->moduleUuid = $request->get('module_uuid');

            $json['data'] = $template->getResponse()->getContent();
            $json['project_id'] = $project->id;
            $json['success'] = 'true';

            $response = new JsonResponse($json);

            throw new ResponseException($response);
        }

        // Handle featured projects
        if ('featured' === $model->project_featured) {
            $blnFeatured = true;
        } elseif ('unfeatured' === $model->project_featured) {
            $blnFeatured = false;
        } else {
            $blnFeatured = null;
        }

        $template->empty = $GLOBALS['TL_LANG']['MSC']['emptyProjectList'];

        $limit = 0;
        $offset = 0;

        $arrProjects = [];

        $objProjects = $this->fetchItems($this->allowedProjectArchives, $model, $blnFeatured, $limit, $offset);
        $arrProjectIds = [];

        if (null !== $objProjects) {
            while ($objProjects->next()) {
                $project = $objProjects->current();
                $arrProjects[] = $this->getProjectDetails($project);
                $arrProjectIds[] = $project->id;
            }
        }

        if (!empty($arrProjects)) {
            $template->projectIds = $arrProjectIds;
            $template->projects = $arrProjects;
        }

        $template->module = $model;
        $template->moduleUuid = md5(Uuid::uuid4()->toString());

        return $template->getResponse();
    }

    /**
     * Fetch the matching items.
     */
    protected function fetchItems(array $projectArchives, ModuleModel $model, bool|null $blnFeatured, int $limit = 0, int $offset = 0): Collection|ProjectModel|null
    {
        // Determine sorting
        $t = ProjectModel::getTable();

        $order = match ($model->project_order) {
            'project_order_title_desc' => "$t.pid, $t.title DESC",
            'project_order_sorting_asc' => "$t.pid, $t.sorting ASC",
            'project_order_sorting_desc' => "$t.pid, $t.sorting DESC",
            default => "$t.pid, $t.title",
        };

        return ProjectModel::findPublishedByPids($projectArchives, $blnFeatured, $limit, $offset, ['order' => $order]);
    }

    /**
     * Checks whether the request is an AJAX request for this module.
     */
    protected function isAjaxRequest(): bool
    {
        return Environment::get('isAjaxRequest');
    }
}
