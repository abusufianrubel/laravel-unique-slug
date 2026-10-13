<?php

namespace Sufian\SlugHelper;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SlugHelper
{
    /**
     * Generate a unique slug for an Eloquent model.
     *
     * A model instance may be supplied when updating a record so that record is
     * excluded from the uniqueness check. Passing a model class remains fully
     * supported for create operations.
     *
     * @param  class-string<Model>|Model  $model
     */
    public function generate($model, $value, $field, $separator = null): string
    {
        $separator = $separator ?: config('slug-helper.separator', '_');

        if (!is_string($separator) || $separator === '') {
            throw new InvalidArgumentException('The slug separator must be a non-empty string.');
        }

        if (!is_string($field) || $field === '') {
            throw new InvalidArgumentException('The slug field must be a non-empty string.');
        }

        $modelInstance = $this->resolveModel($model);
        $slug = Str::slug((string) $value, $separator);

        if ($slug === '') {
            throw new InvalidArgumentException('The value must contain at least one slug-compatible character.');
        }

        $allSlugs = $this->getRelatedSlugs($slug, $modelInstance, $field);

        // If we haven't used it before then we are all good.
        if (!$this->containsSlug($allSlugs, $field, $slug)) {
            return $slug;
        }

        $maxCount = (int) config('slug-helper.max_count', 100);

        if ($maxCount < 1) {
            throw new InvalidArgumentException('The slug max_count must be at least 1.');
        }

        for ($i = 1; $i <= $maxCount; $i++) {
            $newSlug = $slug . $separator . $i;
            if (!$this->containsSlug($allSlugs, $field, $newSlug)) {
                return $newSlug;
            }
        }

        throw new Exception('Unable to create a unique slug within the configured max_count.');
    }

    /**
     * @param  class-string<Model>|Model  $model
     */
    private function resolveModel($model): Model
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && is_subclass_of($model, Model::class)) {
            return new $model();
        }

        throw new InvalidArgumentException('The model must be an Eloquent model instance or class name.');
    }

    private function getRelatedSlugs(string $slug, Model $model, string $field): Collection
    {
        $query = $model->newQuery()->select($field);
        $wrappedField = $query->getQuery()->getGrammar()->wrap($field);

        // Compare case-insensitively so the result is safe on databases whose
        // unique indexes use a case-insensitive collation.
        $query->whereRaw("LOWER({$wrappedField}) LIKE ?", [Str::lower($slug) . '%']);

        if ($model->exists && $model->getKey() !== null) {
            $query->where($model->getQualifiedKeyName(), '<>', $model->getKey());
        }

        return $query
            ->get();
    }

    private function containsSlug(Collection $models, string $field, string $slug): bool
    {
        return $models->contains(
            static fn ($model): bool => Str::lower((string) $model->getAttribute($field)) === Str::lower($slug)
        );
    }
}
