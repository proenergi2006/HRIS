<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

trait HasHashid
{
    public function getRouteKey(): mixed
    {
        return Hashids::encode($this->getKey());
    }

    public function resolveRouteBinding(mixed $value, $field = null): ?static
    {
        $decoded = Hashids::decode($value);

        if (empty($decoded)) {
            abort(404);
        }

        return $this->find($decoded[0]) ?? abort(404);
    }
}
