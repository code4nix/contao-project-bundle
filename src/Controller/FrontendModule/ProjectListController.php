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

namespace Code4Nix\ContaoProjectBundle\Controller\FrontendModule;

use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Code4Nix\ContaoProjectBundle\Traits\FrontendModuleTrait;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'projects', template: 'mod_project_list')]
class ProjectListController extends AbstractFrontendModuleController
{
    use FrontendModuleTrait;

    public const TYPE = 'project_list';

    protected ?ProjectModel $project = null;
    private ScopeMatcher $scopeMatcher;
    private ?array $allowedProjectArchives = null;

    public function __construct(ScopeMatcher $scopeMatcher)
    {
        $this->scopeMatcher = $scopeMatcher;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        if ($this->scopeMatcher->isFrontendRequest($request)) {
            $this->allowedProjectArchives = $this->sortOutProtected(StringUtil::deserialize($model->allowed_project_archives));

            // Return if there are no archives
            if (empty($this->allowedProjectArchives) || !\is_array($this->allowedProjectArchives)) {
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
        $counter = 0;

        $objProjects = $this->fetchItems($this->allowedProjectArchives, $model, $blnFeatured, $limit, $offset);

        if (null !== $objProjects) {
            while ($objProjects->next()) {
                ++$counter;
                $project = $objProjects->current();
                $arrProjects[] = $this->getProjectDetails($project);
            }
        }

        $template->projects = $arrProjects;
        $template->countProjects = $counter;

        return $template->getResponse();
    }

    /**
     * Fetch the matching items.
     *
     * @param array $projectArchives
     * @param bool  $blnFeatured
     *
     * @return Collection|ProjectModel|null
     */
    protected function fetchItems($projectArchives, ModuleModel $model, ?bool $blnFeatured, int $limit = 0, int $offset = 0)
    {
        // Determine sorting
        $t = ProjectModel::getTable();
        $order = '';

        switch ($model->project_order) {
            case 'project_order_title_asc':
                $order .= "$t.title";
                break;

            case 'project_order_title_desc':
                $order .= "$t.title DESC";
                break;

            case 'project_order_sorting_asc':
                $order .= "$t.sorting ASC";
                break;

            case 'project_order_sorting_desc':
                $order .= "$t.sorting DESC";
                break;

            default:
                $order .= "$t.title";
        }

        return ProjectModel::findPublishedByPids($projectArchives, $blnFeatured, $limit, $offset, ['order' => $order]);
    }
}
