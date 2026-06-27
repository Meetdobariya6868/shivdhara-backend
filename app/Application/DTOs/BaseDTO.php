<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Data Transfer Object base.
 *
 * DTOs carry validated, framework-agnostic data across layer boundaries
 * (HTTP -> Service -> Domain) so that inner layers never depend on HTTP request
 * objects. Concrete DTOs are introduced per use case in later phases.
 */
abstract class BaseDTO
{
    /**
     * Hydrate a DTO from an associative array.
     *
     * @param  array<string, mixed>  $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
