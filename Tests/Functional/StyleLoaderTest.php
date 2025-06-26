<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;

/**
 * Testcase for the style loader object
 */
class StyleLoaderTest extends AbstractFusionObjectTest
{

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

    /**
     * @test
     */
    public function addStylesAsProcessWorks()
    {
        $view = $this->buildView();

        $view->setFusionPath('styleLoader/asProcess');
        $this->assertEquals(
            '<style data-inline>.foo {
    color: blue;
}
</style><div>foo</div>', $view->render());
    }
}
