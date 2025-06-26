<?php

declare(strict_types=1);

namespace Shel\CriticalCSS\Tests\Functional;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use GuzzleHttp\Psr7\ServerRequest;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\Core\FusionGlobals;
use Neos\Fusion\Core\FusionSourceCodeCollection;
use Neos\Fusion\Core\RuntimeFactory;
use Neos\Fusion\Tests\Functional\FusionObjects\AbstractFusionObjectTest;
use Neos\Fusion\Tests\Functional\FusionObjects\TestingViewForFusionRuntime;

/**
 * Abstract testcase for the style collector object
 */
abstract class AbstractFusionStylingTestCase extends AbstractFusionObjectTest
{
    protected function buildView(): TestingViewForFusionRuntime
    {
        $this->request = ActionRequest::fromHttpRequest(new ServerRequest('GET', 'http://localhost/'));

        $runtime = $this->objectManager->get(RuntimeFactory::class)->createFromSourceCode(
            FusionSourceCodeCollection::fromFilePath(__DIR__ . '/Fixtures/Fusion/Root.fusion'),
            FusionGlobals::fromArray(['request' => $this->request])
        );

        $view = new TestingViewForFusionRuntime($runtime);
        $view->assign('fixtureDirectory', __DIR__ . '/Fixtures/');
        return $view;
    }
}
