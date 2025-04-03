<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Fusion;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Exception as SecurityException;
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
     */
    protected function loadFusion(): void
    {
        $fusionAst = [];
        try {
            $fusionAst = $this->fusionService->getFusionConfigurationForSitePackage(
                $this->getOption('packageKey')
            );
        } catch (\Exception) {
        }
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
        if (!$this->parsedFusion) {
            $this->loadFusion();
        }
        $fusionAst = $this->parsedFusion;
        $prototypes = $fusionAst['__prototypes'];

        $arrayIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fusionAst));
        $outputArray = [];
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($arrayIterator as $sub) {
            $subArray = $arrayIterator->getSubIterator();
            /** @noinspection PhpParamsInspection */
            if (!$subArray || !array_key_exists('__objectType', $subArray)) {
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
            $fusionRuntime = new FusionRuntime($fusionAst, $fusionGlobals);
            $fusionRuntime->pushContextArray($this->variables);
            $output .= $fusionRuntime->render($this->styleRenderPath);
            $fusionRuntime->popContext();
        }

        return $output;
    }
}
