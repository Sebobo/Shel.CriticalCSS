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
use Shel\CriticalCSS\Service\StylesService;

/**
 * Adds all attributes as css styles into a style tag with a css class and adds the class to the tag
 */
class StylesImplementation extends DataStructureImplementation
{

    /**
     * @Flow\Inject
     * @var HtmlAugmenter
     */
    protected $htmlAugmenter;

    /**
     * @Flow\Inject
     * @var StylesService
     */
    protected $stylesService;

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
     * When this is true, only the rendered CSS is returned and nothing else.
     *
     * @return bool
     */
    protected function getStylesOnly()
    {
        return $this->fusionValue('__meta/stylesOnly') ?? false;
    }

    /**
     * @return string
     */
    protected function getClassPrefix()
    {
        return $this->fusionValue('__meta/classPrefix') ?? 'style--';
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
        $classPrefix = $this->getClassPrefix();

        $styleProperties = [];
        foreach ($sortedChildFusionKeys as $key) {
            $value = $this->fusionValue($key);

            // When using simple nesting with `{` instead of using Neos.Fusion:DataStructure
            // we have to retrieve the value from the properties as the value is null.
            if ($value === null && array_key_exists($key, $this->properties)) {
                $value = $this->properties[$key];
                if (is_array($value)) {
                    $this->evaluateNestedProps($key, $value);
                }
            }

            $styleProperties[$key] = $value;
        }

        $path = [$selector !== false ? $selector : '.' . $classPrefix . '#{$hash}'];
        $stylesHash = $this->stylesService->getHashForStyles($styleProperties, $path);
        $styles = $this->stylesService->renderStyles($styleProperties, $path);
        $styles = str_replace('#{$hash}', $stylesHash, $styles);

        if ($this->getStylesOnly()) {
            return $styles;
        }

        $styleTag = '<style data-inline>' . $styles . '</style>';

        // Don't augment the content, just prepend the style tag
        if ($this->getSelector() !== false) {
            return $styleTag . $content;
        }

        return
            $styleTag .
            $this->htmlAugmenter->addAttributes(
                $content,
                ['class' => $classPrefix . $stylesHash],
                $this->getFallbackTagName()
            );
    }

    /**
     * @param string $path
     * @param array $props
     */
    protected function evaluateNestedProps(string $path, array &$props): void
    {
        if (array_key_exists('__objectType', $props)) {
            $props = $this->fusionValue($path);
        } else {
            array_walk($props, function (&$prop, $propName, $path) {
                if (is_array($prop)) {
                    $this->evaluateNestedProps($path . '/' . $propName, $prop);
                }
            }, $path);
        }
    }
}
