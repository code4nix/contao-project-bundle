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

use Code4Nix\ContaoProjectBundle\Model\ProjectArchiveModel;
use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class Project extends Backend
{
    private ContaoFramework $framework;
    private Security $security;
    private RequestStack $requestStack;
    private Adapter $input;
    private Adapter $pageModel;
    private Adapter $projectArchiveModel;

    public function __construct(ContaoFramework $framework, Security $security, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->security = $security;
        $this->requestStack = $requestStack;

        // Adapters
        $this->input = $this->framework->getAdapter(Input::class);
        $this->pageModel = $this->framework->getAdapter(PageModel::class);
        $this->projectArchiveModel = $this->framework->getAdapter(ProjectArchiveModel::class);

        parent::__construct();
    }

    /**
     * Check permissions to edit table tl_project.
     *
     * @throws AccessDeniedException
     */
    #[AsCallback(table: 'tl_project', target: 'config.onload', priority: 100)]
    public function checkPermission(DataContainer $dc): void
    {
        $user = $this->security->getUser();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (empty($user->projects) || !\is_array($user->projects)) {
            $root = [0];
        } else {
            $root = $user->projects;
        }

        $id = \strlen($this->input->get('id')) ? $this->input->get('id') : $dc->currentPid;

        // Check current action
        switch ($this->input->get('act')) {
            case 'paste':
            case 'select':
                // Check CURRENT_ID here (see #247)
                if (!\in_array($dc->currentPid, $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to access project archive ID '.$id.'.');
                }
                break;

            case 'create':
                if (!$this->input->get('pid') || !\in_array($this->input->get('pid'), $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to create project items in project archive ID '.$this->input->get('pid').'.');
                }
                break;

            case 'cut':
            case 'copy':
                if ('cut' === $this->input->get('act') && 1 === (int) $this->input->get('mode')) {
                    $objArchive = $this->Database->prepare('SELECT pid FROM tl_project WHERE id=?')
                        ->limit(1)
                        ->execute($this->input->get('pid'))
                    ;

                    if ($objArchive->numRows < 1) {
                        throw new AccessDeniedException('Invalid project item ID '.$this->input->get('pid').'.');
                    }

                    $pid = $objArchive->pid;

                } else {
                    $pid = $this->input->get('pid');
                }

                if (!\in_array($pid, $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to '.$this->input->get('act').' project item ID '.$id.' to project archive ID '.$pid.'.');
                }
            // no break

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
                $objArchive = $this->Database->prepare('SELECT pid FROM tl_project WHERE id=?')
                    ->limit(1)
                    ->execute($id)
                ;

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid project item ID '.$id.'.');
                }

                if (!\in_array($objArchive->pid, $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to '.$this->input->get('act').' project item ID '.$id.' of project archive ID '.$objArchive->pid.'.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to access project archive ID '.$id.'.');
                }

                $objArchive = $this->Database->prepare('SELECT id FROM tl_project WHERE pid=?')
                    ->execute($id)
                ;

                $objSession = $this->requestStack->getCurrentRequest()->getSession();

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if ($this->input->get('act')) {
                    throw new AccessDeniedException('Invalid command "'.$this->input->get('act').'".');
                }

                if (!\in_array($id, $root, false)) {
                    throw new AccessDeniedException('Not enough permissions to access project archive ID '.$id.'.');
                }
                break;
        }
    }

    /**
     * Auto-generate the project alias if it has not been set yet.
     *
     * @throws \Exception
     */
    #[AsCallback(table: 'tl_project', target: 'fields.alias.save', priority: 100)]
    public function generateAlias(mixed $varValue, DataContainer $dc): string
    {
        $aliasExists = fn (string $alias): bool => $this->Database->prepare('SELECT id FROM tl_project WHERE alias=? AND id!=?')->execute($alias, $dc->id)->numRows > 0;

        // Generate alias if there is none
        if (!$varValue) {
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, $this->projectArchiveModel->findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $varValue)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        } elseif ($aliasExists($varValue)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

    #[AsCallback(table: 'tl_project', target: 'config.oninvalidate_cache_tags', priority: 101)]
    public function addSitemapCacheInvalidationTag(DataContainer $dc, array $tags): array
    {
        $archiveModel = $this->projectArchiveModel->findByPk($dc->activeRecord->pid);

        if (null === $archiveModel) {
            return $tags;
        }

        $pageModel = $this->pageModel->findWithDetails($archiveModel->jumpTo);

        if (null === $pageModel) {
            return $tags;
        }

        return array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
    }
}
