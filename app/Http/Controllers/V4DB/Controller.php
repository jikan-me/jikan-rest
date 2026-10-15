<?php

namespace App\Http\Controllers\V4DB;

use App\Contracts\Mediator;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * Class Controller
 */
class Controller extends BaseController
{
    /**
     * @OA\OpenApi(
     *
     *     @OA\Info(
     *         version="4.0.0",
     *         title="Jikan API",
     *         description=API_DESCRIPTION,
     *         termsOfService="https://jikan.moe/terms",
     *
     *         @OA\Contact(
     *             name="API Support (Discord)",
     *             url="http://discord.jikan.moe"
     *         ),
     *
     *         @OA\License(
     *             name="MIT",
     *             url="https://github.com/jikan-me/jikan-rest/blob/master/LICENSE"
     *         )
     *     ),
     *
     *     @OA\Server(
     *         description="Jikan REST API",
     *         url="https://api.jikan.moe/v4",
     *     ),
     *
     *     @OA\ExternalDocumentation(
     *         description="About",
     *         url="https://jikan.moe"
     *     ),
     * )
     */
    protected Mediator $mediator;

    /**
     * AnimeController constructor.
     */
    public function __construct(Mediator $mediator)
    {
        $this->mediator = $mediator;
    }
}
