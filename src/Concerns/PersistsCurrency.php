<?php

declare(strict_types=1);

namespace ArchTech\Money\Concerns;

use Closure;

trait PersistsCurrency
{
    protected Closure $resolveCurrentUsing;
    protected Closure $storeCurrentUsing;

    protected function resolveCurrent(): string|null
    {
        return isset($this->resolveCurrentUsing)
            ? ($this->resolveCurrentUsing)()
            : null;
    }

    /** Set the handler for resolving the current currency. */
    public function resolveCurrentUsing(Closure $callback): static
    {
        $this->resolveCurrentUsing = $callback;

        return $this;
    }

    protected function storeCurrent(string $currency): static
    {
        if (isset($this->storeCurrentUsing)) {
            ($this->storeCurrentUsing)($currency);
        }

        return $this;
    }

    /** Set the handler for storing the current currency. */
    public function storeCurrentUsing(Closure $callback): static
    {
        $this->storeCurrentUsing = $callback;

        return $this;
    }
}
