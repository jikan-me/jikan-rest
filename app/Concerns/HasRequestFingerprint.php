<?php

namespace App\Concerns;

use App\Http\HttpHelper;
use Illuminate\Http\Request;

/**
 * Helper trait for data transfer objects
 *
 * Ref: https://spatie.be/docs/laravel-data/v2/as-a-data-transfer-object/request-to-data-object#content-mapping-a-request-onto-a-data-object
 */
trait HasRequestFingerprint
{
    protected ?string $fingerprint;

    public static function fromRequest(Request $request): ?static
    {
        $result = new self();
        $result->fingerprint = HttpHelper::resolveRequestFingerprint($request);
        return $result;
    }

    public function getFingerPrint(): string
    {
        return $this->fingerprint;
    }
}
