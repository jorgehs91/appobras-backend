<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'jpg', 'png', 'docx', 'xlsx']);
        $fileName = fake()->word() . '.' . $extension;

        return [
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'name' => $fileName,
            'file_path' => 'documents/' . $fileName,
            'file_url' => '/storage/documents/' . $fileName,
            'mime_type' => fake()->mimeType(),
            'file_size' => fake()->numberBetween(10000, 5000000),
            'uploaded_by' => User::factory(),
        ];
    }
}
