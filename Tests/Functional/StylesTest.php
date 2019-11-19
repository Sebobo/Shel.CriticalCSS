<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;
use Neos\Fusion\View\FusionView;

/**
 * Testcase for the styles object
 */
class StylesTest extends AbstractFusionObjectTest
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
    public function basicRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/basic');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--1d5b3fc659{color:green;font-size:12px}' .
            '</style>' .
            '<div class="style--1d5b3fc659">foo</div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function hashDependsOnValues()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/hashCase1');
        $case1 = $view->render();

        $view->setFusionPath('styles/hashCase2');
        $case2 = $view->render();

        $this->assertNotEquals($case1, $case2);
    }

    /**
     * @test
     */
    public function eelExpressionRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/eelExpressions');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--a01f4ebd21{font-size:7px}' .
            '</style>' .
            '<div class="style--a01f4ebd21">foo</div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function nestedRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/nested');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--0e1e7d5620{color:blue}' .
            '.style--0e1e7d5620 strong{font-weight:bold}' .
            '</style>' .
            '<div class="style--0e1e7d5620">foo <strong>bar</strong></div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function nestedRenderingWithoutDataStructuresWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/nestedWithoutDataStructures');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--e2384db9d9{color:blue}' .
            '.style--e2384db9d9 strong{font-weight:bold}' .
            '.style--e2384db9d9 strong em{color:pink}' .
            '</style>' .
            '<div class="style--e2384db9d9">foo <strong>bar <em>pony</em></strong></div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function nestedEvaluationRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/nestedEvaluation');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--385286f4a7{color:blue}' .
            '.style--385286f4a7 strong{font-weight:bold}' .
            '.style--385286f4a7 strong em{margin-top:50px}' .
            '</style>' .
            '<div class="style--385286f4a7">foo <strong>bar <em>pony</em></strong></div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function selectorRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/selector');
        $this->assertEquals(
            '<style data-inline>' .
            'body{font-family:Comic Sans}' .
            '</style>' .
            '<html><body>foo</body></html>', $view->render()
        );
    }

    /**
     * @test
     */
    public function mediaQueryRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/mediaQuery');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--3e741db00a{color:blue}' .
            '@media all{.style--3e741db00a{color:green}}' .
            '</style>' .
            '<div class="style--3e741db00a">foo</div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function mediaQueryWithNestingRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/mediaQueryWithNesting');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--751ebabd93{color:blue}' .
            '@media all{.style--751ebabd93{color:green}}' .
            '@media all{.style--751ebabd93 strong{color:yellow}}' .
            '</style>' .
            '<div class="style--751ebabd93">foo <strong>bar</strong></div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function nestedMediaQueriesRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/nestedMediaQueries');
        $this->assertEquals(
            '<style data-inline>' .
            '@media print{' .
            '.style--2d1e5ab0be strong{display:none}' .
            '}' .
            '@media print{@media (max-width:12cm){' .
            '.style--2d1e5ab0be strong{display:block}' .
            '}}' .
            '</style>' .
            '<div class="style--2d1e5ab0be">foo <strong>bar</strong></div>', $view->render()
        );
    }

    /**
     * @test
     */
    public function supportsQueryRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/supportsQuery');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--c07e794268{' .
            'display:block' .
            '}' .
            '@supports (display:flex){' .
            '.style--c07e794268{' .
            'display:flex' .
            '}}' .
            '</style>' .
            '<div class="style--c07e794268">foo</div>', $view->render()
        );
    }
}
