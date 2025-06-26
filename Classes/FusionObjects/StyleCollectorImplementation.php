<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * Collects all style tags defined with the `Shel.CriticalCSS:Styles` object
 * and puts them in the HTML head or at the start of the object the collector is applied to.
 */
class StyleCollectorImplementation extends AbstractFusionObject
{
    /**
     * @return void|string
     */
    public function evaluate()
    {
        $content = $this->fusionValue('content');

        $occurrences = preg_match_all('/<style data-inline>(.*?)<\/style>/', $content, $matches);
        if (!$occurrences) {
            return $content;
        }

        $styles = array_unique($matches[1]);
        /** @var string $modifiedContent */
        $modifiedContent = preg_replace('/<style data-inline>.*?<\/style>/s', '', $content);

        if (!$modifiedContent) {
            return $content;
        }

        $styleTag = '<style>' . implode('', $styles) . '</style>';

        if (str_contains($modifiedContent, '</head>')) {
            /** @var string $modifiedContent */
            $modifiedContent = str_replace('</head>', $styleTag . '</head>', $modifiedContent);
            if ($modifiedContent) {
                return $modifiedContent;
            }
            return $content;
        }

        return $styleTag . $modifiedContent;
    }
}
