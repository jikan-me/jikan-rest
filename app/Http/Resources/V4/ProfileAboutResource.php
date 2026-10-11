<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Profile */
class ProfileAboutResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="user_about",
     *     type="object",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="about",
     *                  type="string",
     *                  description="User About. NOTE: About information is customizable by users through BBCode on MyAnimeList. This means users can add multimedia content, different text sizes, etc. Due to this freeform, Jikan returns parsed HTML. Validate on your end!",
     *                  nullable=true
     *              ),
     *          ),
     *      ),
     *  ),
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'about' => $this->about,
        ];
    }
}
