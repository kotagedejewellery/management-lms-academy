<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActionableHttpErrorTest extends TestCase
{
    public function test_inertia_http_422_with_a_business_message_returns_an_actionable_form_error(): void
    {
        Route::middleware('web')->post('/__test/actionable-error', function (): never {
            abort(422, 'Materi hanya dapat diubah saat course masih draft.');
        });

        $response = $this->from('/courses')
            ->withHeader('X-Inertia', 'true')
            ->post('/__test/actionable-error');

        $response
            ->assertRedirect('/courses')
            ->assertSessionHasErrors('action');

        $this->assertSame(
            'Materi hanya dapat diubah saat course masih draft.',
            $response->getSession()->get('errors')->getBag('default')->first('action'),
        );
    }
}
