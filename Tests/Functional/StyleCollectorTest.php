<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;
use Neos\Fusion\View\FusionView;

/**
 * Testcase for the style collector object
 */
class StyleCollectorTest extends AbstractFusionObjectTest
{
    /**
     * @inheritDoc
     */
    protected function buildView(): FusionView
    {
        $view = parent::buildView();
        $view->setPackageKey('Shel.CriticalCSS');
        $view->setFusionPathPattern(__DIR__ . '/Fixtures/Fusion');
        return $view;
    }

    /**
     * @test
     */
    public function collectMultipleStylesWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styleCollector/collectMultipleStyles');
        $this->assertEquals(
            '
        <html>
            <head>
                <title>Test</title>
            <style>.foo{color:blue}.bar{color:green}</style></head>
            <body>
                
                <div class="foo">foo</div>
                
                <div class="bar">bar</div>
            </body>
        </html>', $view->render());
    }

    /**
     * @test
     */
    public function removeDuplicatesStylesWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styleCollector/removeDuplicatesStyles');
        $this->assertEquals(
            '
        <html>
            <head>
                <title>Test</title>
            <style>.foo{color:blue}</style></head>
            <body>
                
                <div class="foo">foo</div>
                
                <div class="foo">foo</div>
            </body>
        </html>', $view->render());
    }
}
