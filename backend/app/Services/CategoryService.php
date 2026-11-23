<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Support\DefaultCategories;

class CategoryService
{
    public function seedDefaultsFor(User $user): void
    {
        foreach (DefaultCategories::all() as $row) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'emoji' => $row['emoji'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                    'is_default' => true,
                ],
            );
        }
    }
}