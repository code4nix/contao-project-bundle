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

use Code4Nix\ContaoProjectBundle\Security\ContaoProjectPermissions;
use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Security\Core\Security;

class ProjectArchive extends Backend
{
    private ContaoFramework $framework;
    private Security $security;
    private RequestStack $requestStack;
    private Adapter $stringUtil;
    private Adapter $input;
    private Adapter $image;
    private Adapter $pageModel;

    public function __construct(ContaoFramework $framework, Security $security, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->security = $security;
        $this->requestStack = $requestStack;

        // Adapters
        $this->stringUtil = $this->framework->getAdapter(StringUtil::class);
        $this->input = $this->framework->getAdapter(Input::class);
        $this->image = $this->framework->getAdapter(Image::class);
        $this->pageModel = $this->framework->getAdapter(PageModel::class);

        parent::__construct();
    }

    /**
     * Check permissions to edit table tl_project_archive.
     *
     * @throws AccessDeniedException
     */
    #[AsCallback(table: 'tl_project_archive', target: 'config.onload', priority: 100)]
    public function checkPermission(): void
    {
        $user = $this->security->getUser();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($user->projects) || !\is_array($user->projects)) {
            $root = [0];
        } else {
            $root = $user->projects;
        }

        $GLOBALS['TL_DCA']['tl_project_archive']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$this->security->isGranted(ContaoProjectPermissions::USER_CAN_CREATE_ARCHIVES)) {
            $GLOBALS['TL_DCA']['tl_project_archive']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_project_archive']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_project_archive']['config']['notCopyable'] = true;
        }

        // Check permissions to delete calendars
        if (!$this->security->isGranted(ContaoProjectPermissions::USER_CAN_DELETE_ARCHIVES)) {
            $GLOBALS['TL_DCA']['tl_project_archive']['config']['notDeletable'] = true;
        }

        $objSession = $this->requestStack->getCurrentRequest()->getSession();

        // Check current action
        switch ($this->input->get('act')) {
            case 'select':
                // Allow
                break;

            case 'create':
                if (!$this->security->isGranted(ContaoProjectPermissions::USER_CAN_CREATE_ARCHIVES)) {
                    throw new AccessDeniedException('Not enough permissions to create projects archives.');
                }
                break;

            case 'edit':
            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array($this->input->get('id'), $root, true) || ('delete' === $this->input->get('act') && !$this->security->isGranted(ContaoProjectPermissions::USER_CAN_DELETE_ARCHIVES))) {
                    throw new AccessDeniedException('Not enough permissions to '.$this->input->get('act').' project archive ID '.$this->input->get('id').'.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'copyAll':
                $session = $objSession->all();

                if ('deleteAll' === $this->input->get('act') && !$this->security->isGranted(ContaoProjectPermissions::USER_CAN_DELETE_ARCHIVES)) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if ($this->input->get('act')) {
                    throw new AccessDeniedException('Not enough permissions to '.$this->input->get('act').' projects archives.');
                }
                break;
        }
    }

    /**
     * Add the new archive to the permissions.
     *
     * @param $insertId
     */
    #[AsCallback(table: 'tl_project_archive', target: 'config.oncreate', priority: 100)]
    public function adjustPermissions($insertId): void
    {
        // The oncreate_callback passes $insertId as second argument
        if (4 === \func_num_args()) {
            $insertId = func_get_arg(1);
        }

        $user = $this->security->getUser();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($user->projects) || !\is_array($user->projects)) {
            $root = [0];
        } else {
            $root = $user->projects;
        }

        // The archive is enabled already
        if (\in_array($insertId, $root, true)) {
            return;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = $this->requestStack->getCurrentRequest()->getSession()->getBag('contao_backend');

        $arrNew = $objSessionBag->get('new_records');

        if (\is_array($arrNew['tl_project_archive']) && \in_array($insertId, $arrNew['tl_project_archive'], true)) {
            // Add the permissions on group level
            if ('custom' !== $user->inherit) {
                $objGroup = $this->Database->execute('SELECT id, projects, projectp FROM tl_user_group WHERE id IN('.implode(',', array_map('\intval', $user->groups)).')');

                while ($objGroup->next()) {
                    $arrProjectp = $this->stringUtil->deserialize($objGroup->projectp);

                    if (\is_array($arrProjectp) && \in_array('create', $arrProjectp, true)) {
                        $arrProjects = $this->stringUtil->deserialize($objGroup->projects, true);
                        $arrProjects[] = $insertId;

                        $this->Database->prepare('UPDATE tl_user_group SET projects=? WHERE id=?')
                            ->execute(serialize($arrProjects), $objGroup->id)
                        ;
                    }
                }
            }

            // Add the permissions on user level
            if ('group' !== $user->inherit) {
                $objUser = $this->Database->prepare('SELECT projects, projectp FROM tl_user WHERE id=?')
                    ->limit(1)
                    ->execute($user->id)
                ;

                $arrProjectp = $this->stringUtil->deserialize($objUser->projectp);

                if (\is_array($arrProjectp) && \in_array('create', $arrProjectp, true)) {
                    $arrProjects = $this->stringUtil->deserialize($objUser->projects, true);
                    $arrProjects[] = $insertId;

                    $this->Database->prepare('UPDATE tl_user SET projects=? WHERE id=?')
                        ->execute(serialize($arrProjects), $user->id)
                    ;
                }
            }

            // Add the new element to the user object
            $root[] = $insertId;
            $user->projects = $root;
        }
    }

    /**
     * Return the edit header button.
     */
    #[AsCallback(table: 'tl_project_archive', target: 'list.operations.editheader.button', priority: 100)]
    public function editHeader(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_project_archive') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.$this->stringUtil->specialchars($title).'"'.$attributes.'>'.$this->image->getHtml($icon, $label).'</a> ' : $this->image->getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the copy archive button.
     */
    #[AsCallback(table: 'tl_project_archive', target: 'list.operations.copy.button', priority: 100)]
    public function copyArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->security->isGranted(ContaoProjectPermissions::USER_CAN_CREATE_ARCHIVES) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.$this->stringUtil->specialchars($title).'"'.$attributes.'>'.$this->image->getHtml($icon, $label).'</a> ' : $this->image->getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete archive button.
     */
    #[AsCallback(table: 'tl_project_archive', target: 'list.operations.delete.button', priority: 100)]
    public function deleteArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->security->isGranted(ContaoProjectPermissions::USER_CAN_DELETE_ARCHIVES) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.$this->stringUtil->specialchars($title).'"'.$attributes.'>'.$this->image->getHtml($icon, $label).'</a> ' : $this->image->getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    #[AsCallback(table: 'tl_project_archive', target: 'config.oninvalidate_cache_tags', priority: 100)]
    public function addSitemapCacheInvalidationTag(DataContainer $dc, array $tags): array
    {
        $pageModel = $this->pageModel->findWithDetails($dc->activeRecord->jumpToDetail);

        if (null !== $pageModel) {
            $tags = array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
        }

        $pageModel = $this->pageModel->findWithDetails($dc->activeRecord->jumpToAdditional);

        if (null !== $pageModel) {
            $tags = array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
        }

        return $tags;
    }
}
