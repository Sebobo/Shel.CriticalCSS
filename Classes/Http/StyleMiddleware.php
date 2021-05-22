<?php
declare(strict_types=1);

namespace Shel\CriticalCSS\Http;

/*
 * This file is part of the Shel.CriticalCSS package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\ContentStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The HTTP component to collect all inline styles and merge them into the html head
 */
class StyleMiddleware implements MiddlewareInterface
{

    /**
     * @var boolean
     * @Flow\InjectConfiguration(path="mergeStyles.enabled")
     */
    protected $enabled;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->enabled) {
            return $response;
        }

        if (strpos($request->getUri()->getPath(), '/neos/') === 0) {
            return $response;
        }

        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();

        // Retrieve all inline style tags
        preg_match_all('/<style data-inline>(.*?)<\/style>/', $content, $matches);

        if (!$matches) {
            return $response;
        }

        // Remove duplicates
        $styles = array_unique($matches[1]);

        if (count($styles) === 0) {
            return $response;
        }

        // Remove inline style tags from content
        $content = preg_replace('/<style data-inline>.*?<\/style>/', '', $content);

        // Add merged styles into one new style tag to head
        $styleTag = '<style data-merged>' . implode('', $styles) . '</style>';
        $content = str_replace('</head>', $styleTag . '</head>', $content);

        return $response->withBody(ContentStream::fromContents($content));
    }
}
