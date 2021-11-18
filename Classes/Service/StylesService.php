<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Service;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;

/**
 * The User Command Controller
 *
 * @Flow\Scope("singleton")
 */
class StylesService
{
    /**
     * @var array
     */
    protected $stylesCache = [];

    /**
     * @param array $styleProperties
     * @param array $path
     * @return string
     */
    public function getHashForStyles(array $styleProperties, array $path = []): string
    {
        return substr(md5(serialize($styleProperties) . serialize($path)), 0, 10);
    }

    /**
     * @param array $styleProperties
     * @param array $path
     * @return string
     */
    public function renderStyles(array $styleProperties, array $path = []): string
    {
        $hash = $this->getHashForStyles($styleProperties, $path);
        if (array_key_exists($hash, $this->stylesCache)) {
            return $this->stylesCache[$hash];
        }

        $styles = $this->renderProperties($styleProperties, $path);

        $this->stylesCache[$hash] = $styles;
        return $styles;
    }

    /**
     * @param array $properties
     * @param array $path
     * @return string
     */
    protected function renderProperties(array $properties, array $path = []): string
    {
        // Construct full CSS selector
        $pathName = implode(' ', array_map(static function ($part) {
            return strpos($part, '@') === 0 ? $part . '{' : $part;
        }, $path));
        $pathName = str_replace('{ ', '{', $pathName);

        $subSelectors = [];
        $styleProps = [];
        foreach ($properties as $styleName => $styleValue) {
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
            $styles .= implode(';', $styleProps);

            // Finish block with correct amount of closing braces
            $mediaQueryCount = substr_count($pathName, '@');
            $styles .= str_repeat('}', $mediaQueryCount + 1);
        }

        return $styles . implode('', $subSelectors);
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
