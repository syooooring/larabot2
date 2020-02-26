<?php

namespace App\Services;

use GuzzleHttp\Client;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder;


class Connpass
{    
  private const CONNPASS_SEARCH_API_URL = 'https://connpass.com/api/v1/event/';

    public function searchCpevents(string $word): array
    {
        $client = new Client();
        $response = $client
            ->get(self::CONNPASS_SEARCH_API_URL, [
                'query' => [
                    // 'event_id' => $event_id,
                    'keyword' => str_replace(' ', ',', $word),
                ],
              'http_errors' => false,
            ]);
            
        return json_decode($response->getBody()->getContents(), true);
    }
}