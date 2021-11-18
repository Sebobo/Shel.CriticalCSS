<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Command;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use GuzzleHttp\Psr7\ServerRequest;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Security\Exception as SecurityException;
use Neos\Neos\Domain\Repository\SiteRepository;
use Shel\CriticalCSS\Fusion\FusionView;
use Shel\CriticalCSS\Service\FusionService;

/**
 * The User Command Controller
 *
 * @Flow\Scope("singleton")
 */
class StylesCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * This command returns all styles for a given site node and prototype name.
     *
     * @param string $siteNodeName name of the site node to create the context from for rendering Fusion
     * @param string $basePrototypeName name of the base prototype that should be used to export styles from
     * @throws SecurityException
     */
    public function exportCommand(string $siteNodeName, string $basePrototypeName = 'Shel.CriticalCSS:Styles'): void
    {
        $site = $this->siteRepository->findOneByNodeName($siteNodeName);

        if (!$site) {
            $this->output('Could not find site %s', [$siteNodeName]);
            return;
        }

        $fusionView = new FusionView();
        $fusionView->setControllerContext($this->createSimpleControllerContext());
        $fusionView->setFusionPath('shelCriticalStyles');
        $fusionView->setPackageKey($site->getSiteResourcesPackageKey());
        $fusionView->assign('site', $site->getNodeName());

        $this->outputLine($fusionView->renderStyles($basePrototypeName));
    }

    /**
     * Create a simple controller context which can be used to instantiate a Fusion runtime etc.
     */
    protected function createSimpleControllerContext(): ControllerContext
    {
        $httpRequest = new ServerRequest('POST', 'http://localhost');
        $request = ActionRequest::fromHttpRequest($httpRequest);
        $response = new ActionResponse();
        $arguments = new Arguments([]);
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);

        return new ControllerContext($request, $response, $arguments, $uriBuilder);
    }
}
