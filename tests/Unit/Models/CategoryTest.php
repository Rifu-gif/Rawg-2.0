<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;

uses(RefreshDatabase::class);

test('category has expected fillable attributes', function () {
    $category = new Category();

    expect($category->getFillable())->toBe(['name']);
});

test('category posts relationship returns only related posts', function () {
    $user = User::factory()->create();
    $categoryA = Category::create(['name' => 'Action']);
    $categoryB = Category::create(['name' => 'Puzzle']);

    $postA = Post::create([
        'title' => 'Action post',
        'slug' => 'action-post',
        'content' => 'content',
        'category_id' => $categoryA->id,
        'user_id' => $user->id,
    ]);

    Post::create([
        'title' => 'Puzzle post',
        'slug' => 'puzzle-post',
        'content' => 'content',
        'category_id' => $categoryB->id,
        'user_id' => $user->id,
    ]);

    expect($categoryA->posts())->toBeInstanceOf(HasMany::class)
        ->and($categoryA->posts->pluck('id')->all())->toContain($postA->id)
        ->and($categoryA->posts)->toHaveCount(1);
});
