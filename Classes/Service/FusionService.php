<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Service;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Core\FusionConfiguration;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\FusionService as NeosFusionService;

/**
 */
class FusionService extends NeosFusionService
{

    #[Flow\Inject]
    protected SiteRepository $siteRepository;

    /**
     * Returns a merged fusion object tree in the context of the given site-package
     */
    public function getFusionConfigurationForSitePackage(string $siteResourcesPackageKey): FusionConfiguration
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($siteResourcesPackageKey);

        return $this->createFusionConfigurationFromSite($site);
    }
}
