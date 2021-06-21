<?php

namespace App\Http\Controllers\V4DB;


use Dotenv\Dotenv;
use Illuminate\Http\Request;
use Patreon\OAuth;

class PatreonController extends Controller
{
    public function login(Request $request)
    {
        $code = $request->get('code');

        if (!empty($code)) {
            $oauthClient = new OAuth(env('PATREON_CLIENT_ID'), env('PATREON_CLIENT_SECRET'));
            $tokens = $oauthClient->get_tokens($code, env('APP_URL').'/v4/patreon/oauth');

            $accessToken = $tokens['access_token'] ?? '';
            $refreshToken = $tokens['refresh_token'] ?? '';
        }

        if (empty($accessToken)) {
            return response()->json([
                'error' => 'Invalid access token'
            ], 400);
        }

        if (env('PATREON') && env('CACHING') && env('CACHE_DRIVER') == 'redis') {
            app('redis')->set('patreon:access_token', $accessToken);
            app('redis')->set('patreon:refresh_token', $refreshToken);
        }
    }

    public function url(Request  $request) {
        $href = (new \Patreon\AuthUrl(env('PATREON_CLIENT_ID')))
            ->withRedirectUri(env('APP_URL').'/v4/patreon/oauth')
            ->withScopes([
                'campaigns', 'campaigns.members', 'identity.memberships'
            ]);

        return response()->json([
            'url' => $href->buildUrl()
        ]);
    }

    /**
     * @throws \Patreon\Exceptions\CurlException
     * @throws \Patreon\Exceptions\APIException
     * @throws \SodiumException
     */
    public function campaigns(Request $request) {
        if (env('PATREON') && env('CACHING') && env('CACHE_DRIVER') == 'redis') {
            $patreon = new \Patreon\API(app('redis')->get('patreon:access_token'));
            $campaigns = $patreon->fetch_campaigns();

            return response()->json($campaigns);
        }
    }

    public function pledges(Request $request) {
        $campaignId = $request->get('campaign_id');

        if (env('PATREON') && env('CACHING') && env('CACHE_DRIVER') == 'redis') {
            $patreon = new \Patreon\API(app('redis')->get('patreon:access_token'));
            $pledges = $patreon->fetch_page_of_members_from_campaign($campaignId, 100, 0);

            // @todo Patreon doesnt return list of all active backers for some reason!
            // Need to look into this, might have to scrape or try out webhooks
            $pledges = $patreon->get_data(
                'campaigns/'.$campaignId.'/members',
                [
                    'page' => [
                        'count' => 50
                    ],
                    'include' => 'currently_entitled_tiers',
                    'fields' => [
                        'member' => implode(',', [
                            'full_name',
                            'patron_status',
                            'lifetime_support_cents'
                        ]),
                        'tier' => implode(',', [
                            'patron_count',
                            'title'
                        ])
                    ]
                ]
            );


            $backers = [];
            foreach ($pledges['data'] as $pledge) {
                if ($pledge['attributes']['patron_status'] !== 'active_patron') {
                    continue;
                }

                $backers[] = [
                    'full_name' => $pledge['attributes']['full_name'],
                    'lifetime_support_cents' => $pledge['attributes']['lifetime_support_cents'],
                ];

            }

            usort($backers, function ($item1, $item2) {
                return $item2['lifetime_support_cents'] <=> $item1['lifetime_support_cents'];
            });

            foreach ($backers as &$backer) {
                $backer = trim($backer['full_name']);
            }

            return response()->json([
                'backers' => implode(", ", $backers)
            ]);
        }
    }
}