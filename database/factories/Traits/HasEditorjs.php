<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

trait HasEditorjs
{
    protected function getEditorBlocks(): array
    {
        // prep array
        $result = [
            'time' => $this->faker->dateTime()->getTimestamp(),
            'blocks' => [],
            'version' => '2.15.0',
        ];

        // determine count
        $count = $this->faker->numberBetween(2, 15);

        // make blocks
        for ($i = 0; $i < $count; $i++) {
            $result['blocks'][] = $this->getEditorBlock($this->faker);
        }

        // return
        return $result;
    }

    /**
     * @intenal
     */
    private function getEditorBlock(): array
    {
        switch ($this->faker->randomDigit) {
            case 1:
                return [
                    'type' => 'header',
                    'data' => [
                        'text' => $this->faker->sentence,
                        'level' => $this->faker->numberBetween(1, 5),
                    ],
                ];
            case 2:
                return [
                    'type' => 'list',
                    'data' => [
                        'style' => $this->faker->randomElement(['ordered', 'unordered']),
                        'items' => $this->faker->sentences($this->faker->numberBetween(1, 5)),
                    ],
                ];
            case 3:
                return [
                    'type' => 'delimiter',
                    'data' => [],
                ];
            case 3:
                return [
                    'type' => 'image',
                    'data' => [
                        'file' => [
                            'url' => 'https://picsum.photos/200/300',
                        ],
                        'caption' => $this->faker->optional(0.8)->sentence,
                        'withBorder' => $this->faker->boolean,
                        'stretched' => $this->faker->boolean,
                        'withBackground' => $this->faker->boolean,
                    ],
                ];
            default:
                return [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => $this->faker->sentences($this->faker->numberBetween(1, 8), true),
                    ],
                ];
        }
    }
}
