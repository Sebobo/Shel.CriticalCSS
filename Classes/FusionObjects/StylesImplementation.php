<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Exception as FusionException;
use Neos\Fusion\FusionObjects\JoinImplementation;
use Neos\Fusion\Service\HtmlAugmenter;

/**
 * Adds all attributes as css styles into a style tag with a css class and adds the class to the tag
 */
class StylesImplementation extends JoinImplementation
{

    /**
     * @var HtmlAugmenter
     * @Flow\Inject
     */
    protected $htmlAugmenter;

    /**
     * Properties that are ignored
     *
     * @var array
     */
    protected $ignoreProperties = ['__meta', 'tagContent', 'fallbackTagName', 'global'];

    /**
     * @return string
     * @throws FusionException
     */
    public function evaluate(): string
    {
        $content = $this->fusionValue('tagContent');
        $fallbackTagName = $this->fusionValue('fallbackTagName');
        $global = $this->fusionValue('global');
        $sortedChildFusionKeys = $this->sortNestedFusionKeys();

        $styles = '';
        foreach ($sortedChildFusionKeys as $key) {
            $fusionValue = $this->fusionValue($key);
            if ($fusionValue !== null && $fusionValue !== '') {
                $styles .= $key . ':' . $fusionValue . ';';
            }
        }

        $stylesHash = substr(md5($styles), 0, 10);
        $className = 'style--' . $stylesHash;

        if ($global) {
            return '<style data-inline>html{' . $styles . '}</style>' . $content;
        }

        return '<style data-inline>.' . $className . '{' . $styles . '}</style>' .
            $this->htmlAugmenter->addAttributes($content, ['class' => $className], $fallbackTagName);
    }
}
