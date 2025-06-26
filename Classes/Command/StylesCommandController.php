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
use Neos\Flow\Security\Exception as SecurityException;
use Neos\Neos\Domain\Repository\SiteRepository;
use Shel\CriticalCSS\Fusion\FusionView;

/**
 * The User Command Controller
 */
#[Flow\Scope('singleton')]
class StylesCommandController extends CommandController
{
    #[Flow\Inject]
    protected SiteRepository $siteRepository;

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
        $fusionView->assign('request', $this->createSimpleControllerRequest());
        $fusionView->setFusionPath('shelCriticalStyles');
        $fusionView->setPackageKey($site->getSiteResourcesPackageKey());
        $fusionView->assign('site', $site->getNodeName());

        $this->outputLine($fusionView->renderStyles($basePrototypeName));
    }

    /**
     * Create a simple controller context which can be used to instantiate a Fusion runtime, etc.
     */
    protected function createSimpleControllerRequest(): ActionRequest
    {
        $httpRequest = new ServerRequest('POST', 'http://localhost');
        return ActionRequest::fromHttpRequest($httpRequest);
    }
}
