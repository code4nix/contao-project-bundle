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

namespace Code4Nix\ContaoProjectBundle\EventListener\ContaoHooks\ReplaceInsertTags;

use Code4Nix\ContaoProjectBundle\Model\ProjectModel;
use Code4Nix\ContaoProjectBundle\Traits\ProjectDetailTrait;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\StringUtil;

/**
 * Use Contao insert tags to publish project data:.
 *
 * {{project::#project_alias##::headline}}
 * {{project::#project_alias##::title}}
 * {{project::#project_alias##::detailLink}}
 */
#[AsHook(ReplaceProjectListener::HOOK, priority: 100)]
class ReplaceProjectListener
{
    use ProjectDetailTrait;

    public const HOOK = 'replaceInsertTags';
    private InsertTagParser $insertTagParser;

    public function __construct(InsertTagParser $insertTagParser)
    {
        $this->insertTagParser = $insertTagParser;
    }

    public function __invoke(string $insertTag, bool $useCache, string $cachedValue, array $flags, array $tags, array $cache, int $_rit, int $_cnt): array|bool
    {
        if (0 === strpos($insertTag, 'project')) {
            $parts = StringUtil::trimsplit('::', $insertTag);

            if (1 === \count($parts)) {
                return false;
            }

            $id = $parts[1] ?? null;
            $strField = $parts[2] ?? null;

            if (!empty($id) && !empty($strField)) {
                if (null === ($model = ProjectModel::findByIdOrAlias($id))) {
                    return false;
                }

                $arrProject = $this->getProjectDetails($model);

                if (isset($arrProject[$strField])) {
                    // {{project::#project_alias##::headline}}
                    // {{project::#project_alias##::title}}
                    // {{project::#project_alias##::detailLink}}
                    return $arrProject[$strField];
                }
            }
        }

        return false;
    }
}
