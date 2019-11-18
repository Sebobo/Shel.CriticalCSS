<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Command;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response;
use Neos\Flow\Http\Uri;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Exception as MvcException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Flow\Security\Exception as SecurityException;
use Neos\Neos\Domain\Model\Site;
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
     * @param string $siteNodeName
     * @throws SecurityException
     * @throws MvcException
     */
    public function exportCommand(string $siteNodeName): void
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
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

        $this->outputLine($fusionView->renderStyles());
    }

    /**
     * Create a simple controller context which can be used to instantiate a Fusion runtime etc.
     *
     * @return ControllerContext
     */
    protected function createSimpleControllerContext()
    {
        $httpRequest = Request::create(new Uri('http://localhost'));
        $request = new ActionRequest($httpRequest);
        $response = new Response();
        $arguments = new Arguments([]);
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);

        return new ControllerContext($request, $response, $arguments, $uriBuilder);
    }
}
