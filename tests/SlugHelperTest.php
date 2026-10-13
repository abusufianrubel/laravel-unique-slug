<?php

namespace Sufian\SlugHelper\Tests;

use Exception;
use InvalidArgumentException;
use Sufian\SlugHelper\Facades\SlugHelperFacade as UniqueSlug;
use Sufian\SlugHelper\Tests\Fixtures\Post;

class SlugHelperTest extends TestCase
{
    public function test_it_generates_a_normalized_slug(): void
    {
        $this->assertSame('hello_world', UniqueSlug::generate(Post::class, '  Héllo, World!  ', 'slug'));
    }

    public function test_it_fills_the_first_available_duplicate_suffix(): void
    {
        Post::query()->create(['slug' => 'simple_post']);
        Post::query()->create(['slug' => 'simple_post_1']);
        Post::query()->create(['slug' => 'simple_post_3']);

        $this->assertSame('simple_post_2', UniqueSlug::generate(Post::class, 'Simple Post', 'slug'));
    }

    public function test_it_supports_a_custom_separator(): void
    {
        Post::query()->create(['slug' => 'simple-post']);

        $this->assertSame('simple-post-1', UniqueSlug::generate(Post::class, 'Simple Post', 'slug', '-'));
    }

    public function test_it_handles_existing_slugs_case_insensitively(): void
    {
        Post::query()->create(['slug' => 'Simple_Post']);

        $this->assertSame('simple_post_1', UniqueSlug::generate(Post::class, 'Simple Post', 'slug'));
    }

    public function test_it_excludes_an_existing_model_when_updating(): void
    {
        $post = Post::query()->create(['slug' => 'simple_post']);

        $this->assertSame('simple_post', UniqueSlug::generate($post, 'Simple Post', 'slug'));
    }

    public function test_it_rejects_a_value_that_cannot_form_a_slug(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UniqueSlug::generate(Post::class, '?!#', 'slug');
    }

    public function test_it_honors_the_configured_maximum(): void
    {
        config()->set('slug-helper.max_count', 1);
        Post::query()->create(['slug' => 'simple_post']);
        Post::query()->create(['slug' => 'simple_post_1']);

        $this->expectException(Exception::class);

        UniqueSlug::generate(Post::class, 'Simple Post', 'slug');
    }
}
