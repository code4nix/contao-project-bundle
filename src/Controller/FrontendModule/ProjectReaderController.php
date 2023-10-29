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
use Code4Nix\ContaoProjectBundle\Traits\ProjectDetailTrait;
use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Environment;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'projects', template: 'mod_project_reader')]
class ProjectReaderController extends AbstractFrontendModuleController
{
    use ProjectDetailTrait;

    public const TYPE = 'project_reader';

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
            // Set the item from the auto_item parameter
            if (!isset($_GET['items']) && isset($_GET['auto_item']) && Config::get('useAutoItem')) {
                Input::setGet('items', Input::get('auto_item'));
            }

            if (!Input::get('items')) {
                return new Response('', Response::HTTP_NO_CONTENT);
            }

            $this->allowedProjectArchives = $this->sortOutProtected(StringUtil::deserialize($model->allowed_project_archives));

            if (empty($this->allowedProjectArchives) || !\is_array($this->allowedProjectArchives)) {
                return new Response('', Response::HTTP_NO_CONTENT);
            }
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $template->hasReferer = false;

        if ($model->overviewPage) {
            $template->hasReferer = true;
            $template->referer = PageModel::findById($model->overviewPage)->getFrontendUrl();
            $template->back = $GLOBALS['TL_LANG']['MSC']['projectOverview'];
        }

        // Get the project item
        $objProject = ProjectModel::findPublishedByParentAndIdOrAlias(Input::get('items'), $this->allowedProjectArchives);

        // The project item does not exist
        if (null === $objProject) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        $arrProject = $this->getProjectDetails($objProject);

        $template->project = $arrProject;

        return $template->getResponse();
    }
}
