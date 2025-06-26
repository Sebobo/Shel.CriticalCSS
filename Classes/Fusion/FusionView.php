<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Fusion;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception;
use Neos\Flow\Security\Exception as SecurityException;
use Neos\Fusion\Core\FusionConfiguration;
use Neos\Fusion\Core\FusionGlobals;
use Neos\Fusion\View\FusionView as BaseFusionView;
use Neos\Fusion\Core\Runtime as FusionRuntime;
use Shel\CriticalCSS\Service\FusionService;

/**
 * A specialized fusion view that
 */
class FusionView extends BaseFusionView
{
    protected string $styleRenderPath = 'shelCriticalStyles';

    #[Flow\Inject]
    protected FusionService $fusionService;

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function loadFusion(): void
    {
        $fusionAst = $this->fusionService->getFusionConfigurationForSitePackage(
            $this->getOption('packageKey')
        );
        $this->parsedFusion = $fusionAst;
    }

    /**
     * Iterates through the Fusion AST and renders all instantiated
     * objects of the given prototype and returns the concatenated results as string.
     *
     * @throws SecurityException
     */
    public function renderStyles(string $stylePrototypeName): string
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        /** @phpstan-ignore booleanNot.alwaysFalse */
        if (!$this->parsedFusion) {
            try {
                $this->loadFusion();
            } catch (Exception) {
                return '';
            }
        }
        $fusionAst = $this->parsedFusion->toArray();
        $prototypes = $fusionAst['__prototypes'] ?? [];

        if (!$prototypes) {
            return '';
        }

        $arrayIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fusionAst));
        $outputArray = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($arrayIterator as $sub) {
            $subArray = iterator_to_array($arrayIterator->getSubIterator());
            if (!array_key_exists('__objectType', $subArray)) {
                continue;
            }
            $prototypeName = $subArray['__objectType'];
            if ($prototypeName === $stylePrototypeName ||
                ($prototypeName
                    && array_key_exists($prototypeName, $prototypes)
                    && array_key_exists('__prototypeChain', $prototypes[$prototypeName])
                    && in_array($stylePrototypeName, $prototypes[$prototypeName]['__prototypeChain'], true))) {
                $props = iterator_to_array($subArray);
                $props['__meta']['stylesOnly'] = true;
                $outputArray[] = $props;
            }
        }

        $output = '';

        // Render each found instantiated prototype
        foreach ($outputArray as $props) {
            $fusionAst[$this->styleRenderPath] = $props;
            $fusionGlobals = FusionGlobals::fromArray(array_filter([
                'request' => $this->assignedActionRequest,
            ]));
            $fusionRuntime = new FusionRuntime(FusionConfiguration::fromArray($fusionAst), $fusionGlobals);
            $fusionRuntime->pushContextArray($this->variables);
            $output .= $fusionRuntime->render($this->styleRenderPath);
            $fusionRuntime->popContext();
        }

        return $output;
    }
}
