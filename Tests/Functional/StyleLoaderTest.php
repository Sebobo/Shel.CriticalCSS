<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

/**
 * Testcase for the style loader object
 */
class StyleLoaderTest extends AbstractFusionStylingTestCase
{

    /**
     * @test
     */
    public function insertingStylesWorks(): void
    {
        $this->assertFusionPath(
            '<style data-inline>.foo {
    color: blue;
}
</style>', 'styleLoader/insertStyles');
    }

    /**
     * @test
     */
    public function prependingStylesWorks(): void
    {
        $this->assertFusionPath(
            '<style data-inline>.foo {
    color: blue;
}
</style><div>foo</div>', 'styleLoader/prependStyles');
    }

    /**
     * @test
     */
    public function addStylesAsProcessWorks(): void
    {
        $this->assertFusionPath(
            '<style data-inline>.foo {
    color: blue;
}
</style><div>foo</div>', 'styleLoader/asProcess');
    }
}
