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
use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'projects', template: 'mod_project_single_element')]
class ProjectSingleElementController extends AbstractFrontendModuleController
{
    use FrontendModuleTrait;

    public const TYPE = 'project_single_element';

    protected ?ProjectModel $project = null;
    protected ScopeMatcher $scopeMatcher;

    public function __construct(ScopeMatcher $scopeMatcher)
    {
        $this->scopeMatcher = $scopeMatcher;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        if ($this->scopeMatcher->isFrontendRequest($request)) {
            if (null === ($this->project = ProjectModel::findByPk($model->project))) {
                return new Response('', Response::HTTP_NO_CONTENT);
            }

            $allowedProjectArchives = $this->sortOutProtected([$model->project_archive]);
            if(!in_array($this->project->pid, $allowedProjectArchives, false))
            {
                return new Response('', Response::HTTP_NO_CONTENT);
            }

        }

        return parent::__invoke($request, $model, $section, $classes);


        
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $arrProject = $this->getProjectDetails($this->project);

        $template->project = $arrProject;

        return $template->getResponse();
    }
}
