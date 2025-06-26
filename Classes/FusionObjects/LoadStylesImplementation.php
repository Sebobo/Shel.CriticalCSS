<?php

declare(strict_types=1);

namespace Shel\CriticalCSS\FusionObjects;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\FluidAdaptor\Core\ViewHelper\Exception\InvalidVariableException;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

/**
 * Adds styles from the specified file and inserts them inline
 */
class LoadStylesImplementation extends AbstractFusionObject
{
    /**
     * HTML content that should be processed (optional)
     */
    protected function getContent(): ?string
    {
        return $this->fusionValue('content');
    }

    /**
     * Path to a CSS resource
     */
    protected function getPath(): string
    {
        return $this->fusionValue('path');
    }

    public function evaluate(): string
    {
        try {
            $styles = $this->loadResourceContent($this->getPath());
        } catch (InvalidVariableException) {
            throw new \InvalidArgumentException('Resource ' . $this->getPath() . ' cannot be loaded!', 1573314641);
        }

        return '<style data-inline>' . $styles . '</style>' . ($this->getContent() ?? '');
    }

    /**
     * Loads the content of a resource and returns it as string
     * @throws InvalidVariableException
     */
    public function loadResourceContent(?string $path): string
    {
        if ($path === null) {
            throw new InvalidVariableException('You have to define a path.', 1573317165);
        }
        return file_get_contents($path) ?: '';
    }
}
