<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Service;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Exception as FusionException;
use Neos\Neos\Domain\Exception as DomainException;
use Neos\Neos\Domain\Service\FusionService as NeosFusionService;

/**
 */
class FusionService extends NeosFusionService
{

    /**
     * @Flow\InjectConfiguration(path="fusion.autoInclude", package="Neos.Neos")
     * @var array
     */
    protected $autoIncludeConfiguration = [];

    /**
     * Returns a merged fusion object tree in the context of the given site-package
     *
     * @param string $siteResourcesPackageKey
     * @return array The merged object tree as of the given node
     * @throws DomainException
     * @throws FusionException
     */
    public function getMergedFusionObjectTreeForSitePackage($siteResourcesPackageKey)
    {
        $siteRootFusionPathAndFilename = sprintf($this->siteRootFusionPattern, $siteResourcesPackageKey);

        $mergedFusionCode = '';
        $mergedFusionCode .= $this->generateNodeTypeDefinitions();
        $mergedFusionCode .= $this->getFusionIncludes($this->prepareAutoIncludeFusion());
        $mergedFusionCode .= $this->getFusionIncludes($this->prependFusionIncludes);
        $mergedFusionCode .= $this->readExternalFusionFile($siteRootFusionPathAndFilename);
        $mergedFusionCode .= $this->getFusionIncludes($this->appendFusionIncludes);

        $fusionAst = $this->fusionParser->parse($mergedFusionCode, $siteRootFusionPathAndFilename);

        return $fusionAst;
    }
}
