<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

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

        $styleTag = '<style>' . join('', $styles) . '</style>';

        if (strpos($content, '</head>') !== false) {
            return str_replace('</head>', $styleTag . '</head>', $content);
        } else {
            return $styleTag . $content;
        }
    }
}
