<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MarkdownServiceContract;
use Illuminate\Support\HtmlString;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Inline\Element\Image;

final class MarkdownService implements MarkdownServiceContract
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        // get basic environment, not the GFM one (we don't want tables and such)
        $environment = Environment::createCommonMarkEnvironment();

        // Ensure safety, we're sending user input as mails in the end.
        $environment->mergeConfig([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 5,
        ]);

        // Add security observer
        $environment->addEventListener(
            DocumentParsedEvent::class,
            fn (DocumentParsedEvent $event) => $this->removeImages($event),
        );

        // Allow some extra features, but always keep end-user safety in mind!
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new ExternalLinkExtension());
        $environment->addExtension(new SmartPunctExtension());

        // Instantiate the converter engine
        $this->converter = new CommonMarkConverter([], $environment);
    }

    public function parse(string $body): string
    {
        return $this->converter->convertToHtml($body);
    }

    public function parseSafe(string $body): HtmlString
    {
        return new HtmlString($this->parse($body));
    }

    /**
     * Remove the images from the document.
     *
     * @return void
     */
    private function removeImages(DocumentParsedEvent $event)
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            // Only stop at Image nodes when we first encounter them
            if (! ($node instanceof Image) || ! $event->isEntering()) {
                continue;
            }

            // Drop the node
            $node->detach();
        }
    }
}
