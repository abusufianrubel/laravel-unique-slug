# Laravel Unique Slug

A small unique-slug generator for Laravel Eloquent models.

---

## Installation

```sh
composer require sufianrubel/unique-slug
```

## Configuration

Laravel discovers the service provider and facade automatically. For applications
with package discovery disabled, register them manually:

```php
'providers' => [
    // ...
    Sufian\SlugHelper\SlugHelperServiceProvider::class,
    // ...
],
```

```php
'aliases' => Facade::defaultAliases()->merge([
    // ...
    'UniqueSlug' => Sufian\SlugHelper\Facades\SlugHelperFacade::class,
    // ...
])->toArray(),
```

## Usage

Import the facade:

```php
use Sufian\SlugHelper\Facades\SlugHelperFacade as UniqueSlug;
```

### Generate a unique post slug

Assume the `posts.slug` column has a unique index:

```php
use App\Models\Post;

// First time create post with title Simple Post
UniqueSlug::generate(Post::class, 'Simple Post', 'slug');
// Output: simple_post

// Second time create post with title Simple Post
UniqueSlug::generate(Post::class, 'Simple Post', 'slug');
// Output: simple_post_1

// Third time create post with title Simple Post
UniqueSlug::generate(Post::class, 'Simple Post', 'slug');
// Output: simple_post_2
```

### Pass a custom separator

Pass a custom separator. An empty or `null` separator uses the configured default
for backward compatibility.

```php
// First time create user.
UniqueSlug::generate(User::class, 'sufian', 'username', '-'); // sufian

// Second time create user.
UniqueSlug::generate(User::class, 'sufian', 'username', '-'); // sufian-1

// Third time create user.
UniqueSlug::generate(User::class, 'sufian', 'username', '-'); // sufian-2
```

### Example - Unique slug for category or any model easily
```php
public function create(array $data): ?Category
{
    if (empty($data['slug'])) {
        $data['slug'] = UniqueSlug::generate(Category::class, $data['name'], 'slug');
    }

    return Category::create($data);
}
```

## API Docs

### Generate method

```php
UniqueSlug::generate($model, $value, $field, $separator);
```

```php
/**
 * Generate a Unique Slug.
 *
 * @param class-string<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model $model
 * @param string $value
 * @param string $field
 * @param string|null $separator
 *
 * @return string
 * @throws \Exception
 */
public function generate(
    $model,
    $value,
    $field,
    $separator = null
): string

```

#### Publish configuration

```sh
php artisan vendor:publish --tag=slug-helper-config
```

#### Configurations

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Slug default separator.
    |--------------------------------------------------------------------------
    |
    | If no separator is passed, then this default separator will be used as slug.
    |
    */
    'separator' => '_',

    /*
    |--------------------------------------------------------------------------
    | Slug max count limit
    |--------------------------------------------------------------------------
    |
    | Default 100, slug will generated like
    | test_1, test_2, test_3 .... test_100
    |
    */
    'max_count' => 100,
];

```

When updating an existing record, pass the model instance to exclude that record:

```php
$post->slug = UniqueSlug::generate($post, $post->title, 'slug');
```

Always add a unique database index to the slug column. The generator checks the
current database state, while the index also protects against concurrent requests.

## Contribution

Pull requests are welcome.

## License

Laravel Unique Slug is open-sourced software licensed under the [MIT license](LICENSE).
