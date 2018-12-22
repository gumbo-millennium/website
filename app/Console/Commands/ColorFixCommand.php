<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Color;

class ColorFixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'util:fix-color {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds and replaces colours in the given files with functions';

    const COLOR_LIST = [
        'gray' => [
            '100' => 0xf8f9fa,
            '200' => 0xe9ecef,
            '300' => 0xdee2e6,
            '400' => 0xced4da,
            '500' => 0xadb5bd,
            '600' => 0x6c757d,
            '700' => 0x495057,
            '800' => 0x343a40,
            '900' => 0x212529
        ],
        'color' => [
            'blue' => 0x007bff,
            'indigo' => 0x6610f2,
            'purple' => 0x6f42c1,
            'pink' => 0xe83e8c,
            'red' => 0xdc3545,
            'orange' => 0xfd7e14,
            'yellow' => 0xffc107,
            'green' => 0x28a745,
            'teal' => 0x20c997,
            'cyan' => 0x17a2b8,
            'white' => 0xffffff,
            'gray' => 0x6c757d,
            'gray-dark' => 0x343a40
        ],
        'theme' => [
            'primary@-10' => 0xcce3f7,
            'primary@-9' => 0xb8d8f4,
            'primary@-8' => 0xa4cdf1,
            'primary@-7' => 0x90c2ee,
            'primary@-6' => 0x7bb7eb,
            'primary@-5' => 0x67ace8,
            'primary@-4' => 0x53a1e4,
            'primary@-3' => 0x3f96e1,
            'primary@-2' => 0x2a8bde,
            'primary@-1' => 0x1680db,
            'primary' => 0x0275d8,
            'primary@1' => 0x026cc7,
            'primary@2' => 0x0262b5,
            'primary@3' => 0x0259a4,
            'primary@4' => 0x015093,
            'primary@5' => 0x014682,
            'primary@6' => 0x013d70,
            'primary@7' => 0x01335f,
            'primary@8' => 0x012a4e,
            'primary@9' => 0x01213c,
            'secondary@-10' => 0xe1e3e4,
            'secondary@-9' => 0xd5d7d9,
            'secondary@-8' => 0xc9ccce,
            'secondary@-7' => 0xbdc1c3,
            'secondary@-6' => 0xb2b5b8,
            'secondary@-5' => 0xa6aaad,
            'secondary@-4' => 0x9a9ea3,
            'secondary@-3' => 0x8e9398,
            'secondary@-2' => 0x82888d,
            'secondary@-1' => 0x767c82,
            'secondary' => 0x6a7177,
            'secondary@1' => 0x62686d,
            'secondary@2' => 0x595f64,
            'secondary@3' => 0x51565a,
            'secondary@4' => 0x484d51,
            'secondary@5' => 0x404447,
            'secondary@6' => 0x373b3e,
            'secondary@7' => 0x2f3234,
            'secondary@8' => 0x26292b,
            'secondary@9' => 0x1e2021,
            'success@-10' => 0xdef1de,
            'success@-9' => 0xd1ebd1,
            'success@-8' => 0xc4e5c4,
            'success@-7' => 0xb7e0b7,
            'success@-6' => 0xaadaaa,
            'success@-5' => 0x9dd49d,
            'success@-4' => 0x90cf90,
            'success@-3' => 0x83c983,
            'success@-2' => 0x76c376,
            'success@-1' => 0x69be69,
            'success' => 0x5cb85c,
            'success@1' => 0x55a955,
            'success@2' => 0x4d9b4d,
            'success@3' => 0x468c46,
            'success@4' => 0x3f7d3f,
            'success@5' => 0x376e37,
            'success@6' => 0x306030,
            'success@7' => 0x285128,
            'success@8' => 0x214221,
            'success@9' => 0x1a341a,
            'info@-10' => 0xdef2f8,
            'info@-9' => 0xd1edf6,
            'info@-8' => 0xc4e8f3,
            'info@-7' => 0xb7e3f0,
            'info@-6' => 0xaadeee,
            'info@-5' => 0x9dd9eb,
            'info@-4' => 0x8fd4e9,
            'info@-3' => 0x82cfe6,
            'info@-2' => 0x75cae3,
            'info@-1' => 0x68c5e1,
            'info' => 0x5bc0de,
            'info@1' => 0x54b1cc,
            'info@2' => 0x4ca1ba,
            'info@3' => 0x4592a9,
            'info@4' => 0x3e8397,
            'info@5' => 0x377385,
            'info@6' => 0x2f6473,
            'info@7' => 0x285462,
            'info@8' => 0x214550,
            'info@9' => 0x19363e,
            'warning@-10' => 0xfcefdc,
            'warning@-9' => 0xfbe8cd,
            'warning@-8' => 0xfae1bf,
            'warning@-7' => 0xf8dbb1,
            'warning@-6' => 0xf7d4a3,
            'warning@-5' => 0xf6ce95,
            'warning@-4' => 0xf5c787,
            'warning@-3' => 0xf4c178,
            'warning@-2' => 0xf2ba6a,
            'warning@-1' => 0xf1b45c,
            'warning' => 0xf0ad4e,
            'warning@1' => 0xdd9f48,
            'warning@2' => 0xca9142,
            'warning@3' => 0xb6833b,
            'warning@4' => 0xa37635,
            'warning@5' => 0x90682f,
            'warning@6' => 0x7d5a29,
            'warning@7' => 0x6a4c22,
            'warning@8' => 0x563e1c,
            'warning@9' => 0x433016,
            'danger@-10' => 0xf7dddc,
            'danger@-9' => 0xf4cfce,
            'danger@-8' => 0xf1c1c0,
            'danger@-7' => 0xeeb3b2,
            'danger@-6' => 0xeba6a3,
            'danger@-5' => 0xe89895,
            'danger@-4' => 0xe58a87,
            'danger@-3' => 0xe27c79,
            'danger@-2' => 0xdf6f6b,
            'danger@-1' => 0xdc615d,
            'danger' => 0xd9534f,
            'danger@1' => 0xc84c49,
            'danger@2' => 0xb64642,
            'danger@3' => 0xa53f3c,
            'danger@4' => 0x943836,
            'danger@5' => 0x82322f,
            'danger@6' => 0x712b29,
            'danger@7' => 0x5f2523,
            'danger@8' => 0x4e1e1c,
            'danger@9' => 0x3d1716,
            'light@-10' => 0xeeeeee,
            'light@-9' => 0xe7e7e7,
            'light@-8' => 0xe0e0e0,
            'light@-7' => 0xd9d9d9,
            'light@-6' => 0xd2d2d2,
            'light@-5' => 0xcbcbcb,
            'light@-4' => 0xc5c5c5,
            'light@-3' => 0xbebebe,
            'light@-2' => 0xb7b7b7,
            'light@-1' => 0xb0b0b0,
            'light' => 0xa9a9a9,
            'light@1' => 0x9b9b9b,
            'light@2' => 0x8e8e8e,
            'light@3' => 0x808080,
            'light@4' => 0x737373,
            'light@5' => 0x656565,
            'light@6' => 0x585858,
            'light@7' => 0x4a4a4a,
            'light@8' => 0x3d3d3d,
            'light@9' => 0x2f2f2f,
            'dark@-10' => 0xd3d3d3,
            'dark@-9' => 0xc1c1c1,
            'dark@-8' => 0xafafaf,
            'dark@-7' => 0x9e9e9e,
            'dark@-6' => 0x8c8c8c,
            'dark@-5' => 0x7a7a7a,
            'dark@-4' => 0x696969,
            'dark@-3' => 0x575757,
            'dark@-2' => 0x454545,
            'dark@-1' => 0x343434,
            'dark' => 0x222222,
            'dark@1' => 0x1f1f1f,
            'dark@2' => 0x1d1d1d,
            'dark@3' => 0x1a1a1a,
            'dark@4' => 0x171717,
            'dark@5' => 0x141414,
            'dark@6' => 0x121212,
            'dark@7' => 0x0f0f0f,
            'dark@8' => 0x0c0c0c,
            'dark@9' => 0x0a0a0a
        ]
    ];

    protected $allColors;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->allColors = array_map(function ($value) {
            return new Color($value);
        }, array_flatten(self::COLOR_LIST));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = $this->argument('file');
        if (empty($file) || !is_string($file)) {
            throw new \InvalidArgumentException("{$file} is invalid.");
        }

        $path = $file[0] == '/' ? realpath($file) : resource_path($file);
        if ($path === false) {
            throw new \InvalidArgumentException("Cannot find path for {$file}.");
        }
        if (!file_exists($path) || !is_readable($path) || !is_writable($path)) {
            throw new \InvalidArgumentException("$file at $path is unreadable.");
        }

        // Make backup
        if (!file_exists("{$path}~")) {
            $this->line("File backed up as <info>{$path}~</>");
            copy($path, "{$path}~") || die('Failed to backup file');
        }

        $contents = file_get_contents($path);

        $this->line(sprintf('Read <info>%d</> bytes from <comment>%s</>', strlen($contents), $path));

        $count = 0;
        $content2 = preg_replace_callback(
            '/\#((?:[a-f0-9]{3}){1,2})/i',
            \Closure::fromCallable([$this, 'replaceColor']),
            $contents,
            -1,
            $count
        );

        $this->line(sprintf('Replaced <info>%d</> colors', $count));

        file_put_contents($path, $content2);
    }

    public function replaceColor(array $matches) : string
    {
        $color = new Color();
        $color->fromRgbString(strtolower($matches[1]));

        $res = $this->findClosestColor($color);
        $this->line(sprintf('Replacing <comment>%s</> with <info>%s</>', $matches[0], $res));

        return $res;
    }

    public function findClosestColor(Color $color) : string
    {
        $closestIndex = $color->getClosestMatch($this->allColors);
        $closest = $this->allColors[$closestIndex];

        $this->line(sprintf('Found %s as closest color to %s', $closest->toRgbString(), $color->toRgbString()));
        $closestHex = $closest->toInt();

        // Find in grays
        foreach (self::COLOR_LIST['gray'] as $index => $color) {
            if ($color === $closestHex) {
                return sprintf('#{gray(\'%s\')}', $index);
            }
        }

        // Find in colors
        foreach (self::COLOR_LIST['color'] as $index => $color) {
            if ($color === $closestHex) {
                return sprintf('#{color(\'%s\')}', $index);
            }
        }

        // Find in theme colors
        foreach (self::COLOR_LIST['theme'] as $index => $color) {
            if ($color !== $closestHex) {
                continue;
            }

            $bits = explode('@', $index, 2);
            if (count($bits) == 2) {
                return sprintf('#{theme-color-level(\'%s\', %s)}', $bits[0], $bits[1]);
            } else {
                return sprintf('#{theme-color(\'%s\')}', $index);
            }
        }

        $this->error('Cannot find proper color for this!');
    }
}
