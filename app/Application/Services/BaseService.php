<?php

declare(strict_types=1);

namespace App\Application\Services;

/**
 * Base class for the application service layer.
 *
 * Services orchestrate use cases: they coordinate repositories, domain rules
 * and DTOs, and expose a thin, intention-revealing API to the HTTP layer. This
 * keeps controllers skinny (single responsibility) and business orchestration
 * testable in isolation from the framework. Concrete services are introduced
 * per business capability in later phases.
 */
abstract class BaseService {}
