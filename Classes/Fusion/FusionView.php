<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Fusion;


use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Fusion\View\FusionView as BaseFusionView;
use Neos\Fusion\Core\Runtime as FusionRuntime;
use Shel\CriticalCSS\Service\FusionService;

/**
 * A specialized fusion view that
 */
class FusionView extends BaseFusionView
{
    /**
     * @Flow\Inject
     * @var FusionService
     */
    protected $fusionService;

    /**
     * @Flow\Inject
     * @var TemplateView
     */
    protected $fallbackView;

    /**
     * Load Fusion from the directories specified by $this->getOption('fusionPathPatterns')
     *
     * @return void
     */
    protected function loadFusion()
    {
        $fusionAst = [];
        try {
            $fusionAst = $this->fusionService->getMergedFusionObjectTreeForSitePackage($this->getOption('packageKey'));
        } catch (Exception $e) {
        } catch (\Neos\Fusion\Exception $e) {
        } catch (\Neos\Neos\Domain\Exception $e) {
        }
        $this->parsedFusion = $fusionAst;
    }

    /**
     * Special method to render a specific prototype
     *
     * @return string
     * @throws \Neos\Flow\Security\Exception
     */
    public function renderStyles()
    {
        if (!$this->parsedFusion) {
            $this->loadFusion();
        }
        $fusionAst = $this->parsedFusion;
        $prototypes = $fusionAst['__prototypes'];
        $stylePrototypeName = 'Shel.CriticalCSS:Styles';

        $arrayIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fusionAst));
        $outputArray = [];
        foreach ($arrayIterator as $sub) {
            $subArray = $arrayIterator->getSubIterator();
            /** @noinspection PhpParamsInspection */
            if (!array_key_exists('__objectType', $subArray)) {
                continue;
            }
            $prototypeName = $subArray['__objectType'];
            if ($prototypeName === $stylePrototypeName ||
                ($prototypeName
                && array_key_exists($prototypeName, $prototypes)
                && array_key_exists('__prototypeChain', $prototypes[$prototypeName])
                && in_array($stylePrototypeName, $prototypes[$prototypeName]['__prototypeChain']))) {
                $props = iterator_to_array($subArray);
                $props['__meta']['stylesOnly'] = true;
                $outputArray[] = $props;
            }
        }

        $output = '';

        foreach($outputArray as $props) {
            $fusionAst['shelCriticalStyles'] = $props;
            $fusionRuntime = new FusionRuntime($fusionAst, $this->controllerContext);
            $fusionRuntime->pushContextArray($this->variables);
            $output .= $fusionRuntime->render('shelCriticalStyles');
            $fusionRuntime->popContext();
        }

        return $output;
    }
}
