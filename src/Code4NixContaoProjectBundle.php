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

namespace Code4Nix\ContaoProjectBundle;

use Code4Nix\ContaoProjectBundle\DependencyInjection\Code4NixContaoProjectExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Code4NixContaoProjectBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): Code4NixContaoProjectExtension
    {
        return new Code4NixContaoProjectExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
