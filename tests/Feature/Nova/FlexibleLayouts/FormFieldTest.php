<?php

declare(strict_types=1);

namespace Tests\Feature\Nova\FlexibleLayouts;

use App\Models\Activity;
use App\Models\FormLayout;
use App\Nova\Flexible\Layouts\FormField;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\RequiresNova;

class FormFieldTest extends TestCase
{
    use RequiresNova;
    use WithFaker;

    public function testConversion(): void
    {
        $key = Str::random(16);
        $help = $this->faker->paragraph;
        $label = $this->faker->words(3, true);

        $layout = new FormField(null, null, null, $key, [
            "help" => $help,
            "label" => $label,
            "required" => true,
        ]);

        $result = $layout->toFormField();

        $this->assertInstanceOf(FormLayout::class, $result);

        $this->assertEquals($key, $result->getName());
        $this->assertEquals('text', $result->getType());

        $this->assertEquals([
            'label' => $label,
            'rules' => [
                'required',
            ],
            'help_block' => [
                'text' => $help,
            ],
        ], $result->getOptions());
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testFormFieldConversion()
    {
        $this->markTestSkipped('Test is broken');

        // Make entry
        $activity = factory(Activity::class)->create([
            'enrollment_questions' => json_encode([
                [
                    "key" => "54fd249d9b4fc488",
                    "layout" => "text-field",
                    "attributes" => [
                        "help" => "This is a help text",
                        "label" => "Alpha Field",
                        "required" => true,
                    ],
                ],
                [
                    "key" => "9zVMHhtWNpTAnBGf",
                    "layout" => "text-field",
                    "attributes" => [
                        "help" => null,
                        "label" => "Bravo Field",
                        "required" => false,
                    ],
                ],
            ]),
        ]);

        // Re-load activity from scratch
        $activity = Activity::find($activity->id);
        $form = $activity->form;

        // Check
        $this->assertIsArray($form);
        $this->assertCount(2, $form);

        foreach ($form as $field) {
            assert($field instanceof FormLayout);
            $this->assertEquals('text', $field->getType());
        }
    }
}
