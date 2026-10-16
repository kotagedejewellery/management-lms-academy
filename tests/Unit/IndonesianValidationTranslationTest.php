<?php

namespace Tests\Unit;

use Tests\TestCase;

class IndonesianValidationTranslationTest extends TestCase
{
    public function test_validation_messages_are_available_in_indonesian_without_fallback(): void
    {
        config(['app.locale' => 'id', 'app.fallback_locale' => 'id']);
        app()->setLocale('id');

        $this->assertSame('Email sudah digunakan.', trans('validation.unique', ['attribute' => 'Email']));
        $this->assertSame('Email wajib diisi.', trans('validation.required', ['attribute' => 'Email']));
    }
}
