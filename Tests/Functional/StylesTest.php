<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;

/**
 * Testcase for the styles object
 */
class StylesTest extends AbstractFusionObjectTest
{
    protected function buildView()
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
            '.style--013887df94{color:green;font-size:12px}' .
            '</style>' .
            '<div class="style--013887df94">foo</div>'
            , $view->render()
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
    public function nestedRenderingWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styles/nested');
        $this->assertEquals(
            '<style data-inline>' .
            '.style--7028ec0ef2{color:blue}' .
            '.style--7028ec0ef2 strong{font-weight:bold}' .
            '</style>' .
            '<div class="style--7028ec0ef2">foo <strong>bar</strong></div>'
            , $view->render()
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
            '<html><body>foo</body></html>'
            , $view->render()
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
            '.style--40a1017b31{color:blue}' .
            '@media all{.style--40a1017b31{color:green}}' .
            '</style>' .
            '<div class="style--40a1017b31">foo</div>'
            , $view->render()
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
            '.style--b77a4ff07a{color:blue}' .
            '@media all{.style--b77a4ff07a{color:green}}' .
            '@media all{.style--b77a4ff07a strong{color:yellow}}' .
            '</style>' .
            '<div class="style--b77a4ff07a">foo <strong>bar</strong></div>'
            , $view->render()
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
            '.style--8a3c0e177e strong{display:none}' .
            '}' .
            '@media print{@media (max-width:12cm){' .
            '.style--8a3c0e177e strong{display:block}' .
            '}}' .
            '</style>' .
            '<div class="style--8a3c0e177e">foo <strong>bar</strong></div>'
            , $view->render()
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
            '.style--955d31a3ff{' .
            'display:block' .
            '}' .
            '@supports (display:flex){' .
            '.style--955d31a3ff{' .
            'display:flex' .
            '}}' .
            '</style>' .
            '<div class="style--955d31a3ff">foo</div>'
            , $view->render()
        );
    }
}
