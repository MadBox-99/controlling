<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GlobalSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlobalSetting>
 */
final class GlobalSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'google_service_account' => null,
        ];
    }
}
