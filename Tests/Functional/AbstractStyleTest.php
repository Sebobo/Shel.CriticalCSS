<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;
use Neos\Fusion\View\FusionView;

abstract class AbstractStyleTest extends AbstractFusionObjectTest
{
    /**
     * @inheritDoc
     */
    protected function buildView(): FusionView
    {
        $view = parent::buildView();
        $view->setPackageKey('Shel.CriticalCSS');
        $view->setFusionPathPattern(__DIR__ . '/Fixtures/Fusion');
        $view->assign('fixtureDirectory', __DIR__ . '/Fixtures/');
        return $view;
    }
}
