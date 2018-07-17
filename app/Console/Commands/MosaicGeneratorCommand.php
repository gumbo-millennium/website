<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use MischiefCollective\ColorJizz\Formats\Hex;
use MischiefCollective\ColorJizz\Formats\HSV;
use Symfony\Component\Process\Process;
use DOMDocument;

class MosaicGeneratorCommand extends Command
{
    /**
     * @var string xlink:href DTD. Don't change.
     */
    const DTD_XLINK = 'http://www.w3.org/1999/xlink';

    /**
     * @var int[] Lightness deviation from the base color. 100 = 1%
     */
    const LIGHTNESS_DEVIATION = [-500, 500];

    /**
     * @var int[] Lightness deviation from the base color. 100 = 1%
     */
    const CONTRAST_DEVIATION = [-500, 500];

    /**
     * @var int[] Opacity range, in percentage. Used in mt_rand.
     */
    const OPACITY_RANGE = [40, 80];

    /**
     * @var int[] Image dimensions. Will be fileld completely
     */
    const IMAGE_SIZE = [1024, 512];

    /**
     * @var int Size of the diamonds
     */
    const DIAMOND_SIZE = 32;

    /**
     * @var string Base color
     */
    const DIAMOND_COLOUR = '#007d00';

    /**
     * @var string XML template
     */
    const XML_BASE = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg
    xmlns="http://www.w3.org/2000/svg"
    version="1.1"
    viewBox="%3$s">
    <defs>
        <polygon id="diamond" points="%1$d,0 %2$d,%1$d %1$d,%2$d 0,%1$d" />
    </defs>
</svg>
XML;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:mosaic
                            {seed? : Random generator seed to use}
                            {--f|force : Overwrite svg without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds an SVG file containing a mosaic, used in sliders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Prepare seed
        $randomSeed = $this->argument('seed', '');
        if (empty($randomSeed) || !preg_match('/^\d{1,16}$/', $randomSeed)) {
            $randomSeed = $this->buildSeed();
        }

        // Report seed
        $this->line(sprintf('Generating mosaic using seed <info>%d</>.', $randomSeed));

        // Apply seed
        mt_srand($randomSeed);

        // Build data
        $diamonds = $this->buildDiamonds();
        $diamonds = $this->addDiamondProperties($diamonds);
        $document = $this->buildXmlDocument($diamonds);

        // Path to SVG file
        $svgPath = resource_path('assets/svg/mosaic.svg');

        // Check if file eixsts, and if we're not forcing confirm it.
        if (file_exists($svgPath) && !$this->option('force')) {
            if (!$this->confirm('Mosaic already exists. Overwrite?', false)) {
                return;
            }
        }

        // Report action
        $this->line(sprintf(
            'Saved mosaic with <info>%d</> diamonds as <comment>%s/</><info>%s</>.',
            $diamonds->count(),
            dirname($svgPath),
            basename($svgPath)
        ));

        // Write the XML file
        file_put_contents($svgPath, $document->saveXML());

        // Export PNGs based on SVG file, using inkscape
        $this->exportPngImages($svgPath, 'assets/images/mosaic.png');
    }

    /**
     * Builds a seed, which is just based on the time
     *
     * @return int
     */
    protected function buildSeed() : int
    {
        list($usec, $sec) = explode(' ', microtime());
        return (int) round($sec / 3949 + $usec * 30000);
    }

    /**
     * Builds a list of diamonds, with X and Y coordinates. Overlaps outside of the screen.
     *
     * @return Collection
     */
    protected function buildDiamonds() : Collection
    {
        // Get dimensions
        list($width, $height) = self::IMAGE_SIZE;
        $diamondSize = self::DIAMOND_SIZE;

        // Determine counts
        $horizontalDiamondCount = ceil($width / $diamondSize + 1);
        $verticalDiamondCount = ceil($height / $diamondSize + 1);
        $diamondCount = $horizontalDiamondCount * $verticalDiamondCount;

        // Result
        $result = collect();

        // Build coords for each diamond
        for ($i = 0; $i < $diamondCount; $i++) {
            $result->push([
                'x' => $diamondSize * -0.5 + (($i % $horizontalDiamondCount) * $diamondSize),
                'y' => $diamondSize * -0.5 + (floor($i / $horizontalDiamondCount) * $diamondSize)
            ]);
        }

        // Return diamond
        return $result;
    }

    /**
     * Adds random color values and opacity to each diamond
     *
     * @param Collection $diamonds
     * @return Collection
     */
    public function addDiamondProperties(Collection $diamonds) : Collection
    {
        // Get colour
        $baseColor = Hex::fromString(self::DIAMOND_COLOUR);

        foreach ($diamonds as $key => $value) {
            $lightness = call_user_func_array('mt_rand', self::LIGHTNESS_DEVIATION) / 100;
            $saturation = call_user_func_array('mt_rand', self::CONTRAST_DEVIATION) / 100;
            $opacity = call_user_func_array('mt_rand', self::OPACITY_RANGE) / 100;

            $percentageX = min(1, max(0.1, $value['x'] / self::IMAGE_SIZE[0]));
            $percentageY = min(1, max(0.1, $value['y'] / self::IMAGE_SIZE[1]));

            $color = clone $baseColor;

            // Change saturation
            $color = $color->toHSV();
            $color->saturation = min(max($color->saturation + $saturation, 0), 100);

            // Change lightness
            $color = $color->toCIELab();
            $color->lightness = min(max($color->lightness + $lightness, 0), 100);

            // Build color name
            $colorName = sprintf('#%s', (string) $color->toHex());
            $value['color'] = $colorName;

            // Build opacity
            $opacity = $opacity * (-.1 + .9 * $percentageX) * (-.05 + 1 * $percentageY);

            $value['opacity'] = sprintf('%.2f', max(0, min(1, $opacity)));

            // Replace item
            $diamonds->put($key, $value);
        }

        return $diamonds;
    }

    /**
     * Converts a list of diamond positions, colors and opacties to an SVG document
     *
     * @param Collection $diamonds
     * @return DOMDocument
     */
    protected function buildXmlDocument(Collection $diamonds) : DOMDocument
    {
        // Get dimensions
        list($width, $height) = self::IMAGE_SIZE;
        $diamondSize = self::DIAMOND_SIZE;

        // Complete XML template
        $xml = vsprintf(self::XML_BASE, [
            $diamondSize,
            $diamondSize * 2,
            "0 0 {$width} {$height}"
        ]);

        // Construct DOMDocument and load template
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXml($xml);

        // Find svg root
        $target = $doc->getElementsByTagName('svg')->item(0);

        // Append diamonds
        foreach ($diamonds as $diamondId => $diamond) {
            // Parameters
            $atts = [
                'x' => (string)$diamond['x'],
                'y' => (string)$diamond['y'],
                'fill' => (string)$diamond['color'],
                'opacity' => $diamond['opacity'],
            ];

            // Create use element and add xlink
            $node = $doc->createElement('use');
            $linkAttr = $doc->createAttributeNS(self::DTD_XLINK, 'xlink:href');
            $linkAttr->nodeValue = '#diamond';
            $node->appendChild($linkAttr);

            // Add all attributes
            foreach ($atts as $name => $value) {
                $attribute = $doc->createAttribute($name);
                $attribute->nodeValue = $value;
                $node->appendChild($attribute);
            }

            // Add node to svg root
            $target->appendChild($node);
        }

        // Return document
        return $doc;
    }

    /**
     * Converts SVG file to PNG, in normal, high and ultra-high DPI
     *
     * @param string $svgPath
     * @param string $pngTemplate
     * @return void
     */
    protected function exportPngImages(string $svgPath, string $pngTemplate) : void
    {
        $regularDpiPath = resource_path($pngTemplate);
        $highDpiPath = resource_path(preg_replace('/\.png$/', '@2x.png', $pngTemplate));
        $superHighDpiPath = resource_path(preg_replace('/\.png$/', '@4x.png', $pngTemplate));

        $command = [
            'inkscape',
            '--without-gui',
            '--export-background-opacity=0',
            sprintf('%s', $svgPath)
        ];

        $queue = [
            [96 * 1, $regularDpiPath],
            [96 * 2, $highDpiPath],
            [96 * 4, $superHighDpiPath]
        ];

        // Convert files to png
        $helper = $this->getHelper('process');

        // Run the queue
        foreach ($queue as list($dpi, $file)) {
            // Report task
            $this->line(sprintf(
                'Exporting at <info>%d</> DPI to <comment>%s/</><info>%s</>',
                $dpi,
                dirname($file),
                basename($file)
            ));

            // Run task
            $helper->run($this->getOutput(), array_merge($command, [
                sprintf('--export-png=%s', $file),
                sprintf('--export-dpi=%d', $dpi)
            ]), "PNG conversion for {$dpi} DPI failed failed");

            // Report result.
            $this->line(sprintf(
                'Exported <info>%.1f KB</>.',
                filesize($file) / 1024
            ));
        }
    }
}
