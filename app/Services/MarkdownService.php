<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MarkdownServiceContract;
use Illuminate\Support\HtmlString;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownService implements MarkdownServiceContract
{
    private readonly ConverterInterface $converter;

    public function __construct()
    {
        // Ensure safety, we're sending user input as mails in the end.
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 5,
        ]);

        // prep basic environment, not the GFM one (we don't want tables and such)
        $environment->addExtension(new CommonMarkCoreExtension());

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
        $this->converter = new MarkdownConverter($environment);
    }

    public function parse(string $body): string
    {
        return $this->converter->convert($body)->getContent();
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
