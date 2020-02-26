<?php

namespace App\Services;

use GuzzleHttp\Client;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder;


class Connpass
{    
  private const CONNPASS_SEARCH_API_URL = 'https://connpass.com/api/v1/event/';

    public function searchCpevents(string $word)
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
            
        //return json_decode($response->getBody()->getContents(), true);

        $json = json_decode($res->getBody(), true);
        return array(
          "owner_display_name" => $json['events'][0]["owner_display_name"], // 管理者の表示名
          "hash_tag" => $json['events'][0]["hash_tag"], // Twitterのハッシュタグ
          "title" => $json['events'][0]["title"], // タイトル
          "waiting" => $json['events'][0]["waiting"], // 補欠者数
          "limit" => $json['events'][0]["limit"], // 定員
          "accepted" => $json['events'][0]["accepted"], // 参加者数
          "catch" => $json['events'][0]["catch"], // キャッチ
          "place" => $json['events'][0]["place"], // 開催会場
          "address" => $json['events'][0]["address"], // 開催場所
          "started_at" => $json['events'][0]["started_at"], // イベント開催日時 (ISO-8601形式)
      );
    }
}