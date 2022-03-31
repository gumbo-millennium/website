<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Helpers\Arr;
use Tests\TestCase;

class JoinControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function test_show_index()
    {
        $response = $this->get(route('join.form'));

        $response->assertOk();
        $response->assertSee(sprintf('action="%s"', route('join.submit')), false);
    }

    /**
     * @dataProvider formSubmissionFields
     */
    public function test_submit_index(array $data, array $errors): void
    {
        $response = $this->post(route('join.submit'), $data);

        // Next section is fail-state
        if (! empty($errors)) {
            $response->assertSessionHasErrors($errors);

            return;
        }

        // Next section is success-state
        $response
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('join.complete'));
    }

    public function formSubmissionFields(): array
    {
        $validFields = [
            'first-name' => 'Sam',
            'last-name' => 'Smith',
            'email' => 'sam.smith@example.com',
            'phone' => '038 845 0100',
            'date-of-birth' => '2000-01-01',
            'gender' => '-',
            'street' => 'Dorpsweg',
            'number' => '1',
            'postal-code' => '1234AS',
            'city' => 'Zwolle',
            'accept-terms' => 1,
        ];

        // Firstly test happy path
        $output = [
            'valid' => [
                'data' => $validFields,
                'errors' => [],
            ],
        ];

        // Then skip each field separately
        foreach (array_keys($validFields) as $field) {
            $invalidFields = Arr::except($validFields, $field);
            $output["missing {$field}"] = [
                'data' => $invalidFields,
                'errors' => [$field],
            ];
        }

        return $output;
    }
}
