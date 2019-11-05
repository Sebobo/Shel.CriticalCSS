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
        $stylesHash = substr(md5(serialize($sortedChildFusionKeys)), 0, 10);

        $styles = $this->renderProperties($global, $stylesHash, $sortedChildFusionKeys);
        $styleTag = '<style data-inline>' . $styles . '</style>';

        // Don't augment the content, just prepend the style tag
        if ($global) {
            return $styleTag . $content;
        }

        return $styleTag . $this->htmlAugmenter->addAttributes($content, ['class' => 'style--' . $stylesHash],
                $fallbackTagName);
    }

    /**
     * @param bool $global
     * @param string $hash
     * @param array $properties
     * @param string $parentName
     * @return string
     */
    protected function renderProperties(bool $global, string $hash, array $properties, string $parentName = ''): string
    {
        $styles = $global ? 'html' : '.style--' . $hash;
        if ($parentName) {
            $styles .= ' ' . $parentName;
        }
        $styles .= '{';
        $childrenStyles = [];

        foreach ($properties as $key => $value) {
            $styleName = $parentName ? $key : $value;
            $styleValue = $parentName ? $value : $this->fusionValue($styleName);
            if (is_iterable($styleValue)) {
                $childrenStyles[] = $this->renderProperties($global, $hash, $styleValue, $parentName . $styleName);
            } elseif ($styleValue !== null && $styleValue !== '') {
                $styles .= $styleName . ':' . $styleValue . ';';
            }
        }

        $styles .= '}';

        return $styles . join('', $childrenStyles);
    }
}
