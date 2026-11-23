<?php

namespace App\Support;

final class DefaultCategories
{
    /**
     * @return list<array{name: string, slug: string, emoji: string, sort_order: int}>
     */
    public static function all(): array
    {
        return [
            ['name' => 'Еда и продукты',   'slug' => 'food',           'emoji' => '🍎', 'sort_order' => 10],
            ['name' => 'Кафе и рестораны', 'slug' => 'dining',         'emoji' => '🍽️', 'sort_order' => 20],
            ['name' => 'Транспорт',        'slug' => 'transport',      'emoji' => '🚌', 'sort_order' => 30],
            ['name' => 'Жильё',            'slug' => 'housing',        'emoji' => '🏠', 'sort_order' => 40],
            ['name' => 'Подписки',         'slug' => 'subscriptions',  'emoji' => '💳', 'sort_order' => 50],
            ['name' => 'Развлечения',      'slug' => 'entertainment',  'emoji' => '🎬', 'sort_order' => 60],
            ['name' => 'Здоровье',         'slug' => 'health',         'emoji' => '💊', 'sort_order' => 70],
            ['name' => 'Одежда',           'slug' => 'clothing',       'emoji' => '👕', 'sort_order' => 80],
            ['name' => 'Путешествия',      'slug' => 'travel',         'emoji' => '✈️', 'sort_order' => 90],
            ['name' => 'Связь и интернет', 'slug' => 'connectivity',   'emoji' => '📱', 'sort_order' => 100],
            ['name' => 'Прочее',           'slug' => 'other',          'emoji' => '💰', 'sort_order' => 999],
        ];
    }
}