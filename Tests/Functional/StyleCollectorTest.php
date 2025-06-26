<?php

declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

/**
 * Testcase for the style collector object
 */
class StyleCollectorTest extends AbstractFusionStylingTestCase
{
    /**
     * @test
     */
    public function collectMultipleStylesWorks(): void
    {
        $this->assertFusionPath(
            '
        <html>
            <head>
                <title>Test</title>
            <style>.foo{color:blue}.bar{color:green}</style></head>
            <body>
                <div class="foo">foo</div>
                <div class="bar">bar</div>
            </body>
        </html>',
            'styleCollector/collectMultipleStyles'
        );
    }

    /**
     * @test
     */
    public function removeDuplicatesStylesWorks(): void
    {
        $this->assertFusionPath(
            '
        <html>
            <head>
                <title>Test</title>
            <style>.foo{color:blue}</style></head>
            <body>
                <div class="foo">foo</div>
                <div class="foo">foo</div>
            </body>
        </html>',
            'styleCollector/removeDuplicatesStyles'
        );
    }
}
