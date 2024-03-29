<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * Collects all style tags defined with the `Shel.CriticalCSS:Styles` object
 * and puts them in the html head or at the start of the object the collector is applied to.
 */
class StyleCollectorImplementation extends AbstractFusionObject
{
    /**
     * @return void|string
     */
    public function evaluate()
    {
        $content = $this->fusionValue('content');

        preg_match_all('/<style data-inline>(.*?)<\/style>/', $content, $matches);

        if (!$matches) {
            return $content;
        }

        $styles = array_unique($matches[1]);
        $content = preg_replace('/<style data-inline>.*?<\/style>/', '', $content);

        $styleTag = '<style>' . implode('', $styles) . '</style>';

        if (strpos($content, '</head>') !== false) {
            return str_replace('</head>', $styleTag . '</head>', $content);
        }

        return $styleTag . $content;
    }
}
