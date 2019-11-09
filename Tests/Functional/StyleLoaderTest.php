<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;
use Neos\Fusion\View\FusionView;

/**
 * Testcase for the style loader object
 */
class StyleLoaderTest extends AbstractFusionObjectTest
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

    /**
     * @test
     */
    public function insertingStylesWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styleLoader/insertStyles');
        $this->assertEquals(
            '<style data-inline>.foo {
    color: blue;
}
</style>', $view->render());
    }

    /**
     * @test
     */
    public function prependingStylesWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styleLoader/prependStyles');
        $this->assertEquals(
            '<style data-inline>.foo {
    color: blue;
}
</style><div>foo</div>', $view->render());
    }
}
