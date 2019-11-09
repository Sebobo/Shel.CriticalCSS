<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Exception as FusionException;
use Neos\Fusion\FusionObjects\DataStructureImplementation;
use Neos\Fusion\Service\HtmlAugmenter;

/**
 * Adds all attributes as css styles into a style tag with a css class and adds the class to the tag
 */
class StylesImplementation extends DataStructureImplementation
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
    protected $ignoreProperties = ['__meta'];

    /**
     * The content that will be applied the generated css class or
     * if it's multiple elements they will be wrapped with a new
     * tag and the generated class. See `fallbackTagName`.
     *
     * @return string
     */
    protected function getContent(): string
    {
        return $this->fusionValue('__meta/content') ?? '';
    }

    /**
     * The tag that will be used when content contains multiple tags
     * and needs to be wrapped for a class to be applied.
     * This behaves the same as with the HTML Augmenter.
     *
     * @return string
     */
    protected function getFallbackTagName(): string
    {
        return $this->fusionValue('__meta/fallbackTagName') ?? 'div';
    }

    /**
     * When this is set a selector is used instead of the generated class.
     *
     * @return bool|string
     */
    protected function getSelector()
    {
        return $this->fusionValue('__meta/selector') ?? false;
    }

    /**
     * @return string
     * @throws FusionException
     */
    public function evaluate(): string
    {
        $content = $this->getContent();
        $sortedChildFusionKeys = $this->sortNestedFusionKeys();

        $selector = $this->getSelector();

        $styles = $this->renderProperties($sortedChildFusionKeys, [$selector !== false ? $selector : '.style--#{$hash}']);
        $stylesHash = substr(md5(serialize($styles)), 0, 10);
        $styleTag = '<style data-inline>' . $styles . '</style>';

        // Don't augment the content, just prepend the style tag
        if ($this->getSelector() !== false) {
            return $styleTag . $content;
        } else {
            $styleTag = str_replace('#{$hash}', $stylesHash, $styleTag);
        }

        return
            $styleTag .
            $this->htmlAugmenter->addAttributes(
                $content,
                ['class' => 'style--' . $stylesHash],
                $this->getFallbackTagName()
            );
    }

    /**
     * @param array $properties
     * @param array $path
     * @return string
     */
    protected function renderProperties(array $properties, array $path = []): string
    {
        $level = count($path);

        // Construct full CSS selector
        $pathName = join(array_map(function ($part) {
            return strpos($part, '@') === 0 ? $part . '{' : $part;
        }, $path), ' ');
        $pathName = str_replace('{ ', '{', $pathName);

        $subSelectors = [];
        $styleProps = [];
        foreach ($properties as $key => $value) {
            $styleName = $level > 1 ? $key : $value;
            $styleValue = $level > 1 ? $value : $this->fusionValue($styleName);
            if (is_iterable($styleValue)) {
                $childPath = array_merge($path, [$styleName]);
                // Check for @media or @supports queries and reorder the selector
                if (strpos($styleName, '@') === 0) {
                    usort($childPath, 'self::compareSelectorParts');
                }
                $subSelectors[] = $this->renderProperties($styleValue, $childPath);
            } elseif ($styleValue !== null && $styleValue !== '') {
                $styleProps[]= $styleName . ':' . $styleValue;
            }
        }

        $styles = '';

        if ($styleProps) {
            // Create CSS style block
            $styles = $pathName . '{';

            // Insert simple props
            $styles .= join(';', $styleProps);

            // Finish block with correct amount of closing braces
            $mediaQueryCount = substr_count($pathName, '@');
            $styles .= str_repeat('}', $mediaQueryCount + 1);
        }

        return $styles . join('', $subSelectors);
    }

    /**
     * Sorts a CSS selector to keep media queries at the start of the given array
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    private static function compareSelectorParts(string $a, string $b): int
    {
        $aIsMediaQuery = strpos($a, '@') === 0;
        $bIsMediaQuery = strpos($b, '@') === 0;
        if ($aIsMediaQuery && !$bIsMediaQuery) {
            return -1;
        }
        if (!$aIsMediaQuery && $bIsMediaQuery) {
            return 1;
        }
        return 0;
    }
}
