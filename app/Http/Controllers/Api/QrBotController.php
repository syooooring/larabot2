<?php

namespace App\Http\Controllers\Api;

use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use App\Http\Controllers\Controller;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Exception;

class QrBotController extends Controller
{
    public function qrbot(Request $request) {

        $channel_secret = env('LINE_CHANNEL_SECRET', "");
        $access_token = env('LINE_ACCESS_TOKEN', "");
        $request_body = $request->getContent();
        $hash = hash_hmac('sha256', $request_body, $channel_secret, true);
        $signature = base64_encode($hash);

        if($signature === $request->header('X-Line-Signature')) {   // LINEからの送信を検証

            $client = new CurlHTTPClient($access_token);
            $bot = new LINEBot($client, ['channelSecret' => $channel_secret]);

            try {

                $events = $bophpphpt->parseEventRequest($request_body, $signature);

                foreach ($events as $event) {

                    if($event instanceof MessageEvent && $event instanceof TextMessage) {   // テキストメッセージの場合

                        $text = $event->getText();              // LINEで送信されたテキスト
                        $reply_token = $event->getReplyToken(); // 返信用トークン

                        // QRコード作成
                        $filename = Str::random() .'.png';
                        $path = public_path('qr_code/'. $filename);
                        $qrCode = new QrCode($text);
                        $qrCode->writeFile($path);

                        // 画像メッセージで返信
                        $url = url('qr_code/'. $filename);
                        $replying_message = new TextMessageBuilder(
                            $url,
                            $url
                        );
                        $bot->replyMessage($reply_token, $replying_message);

                    }

                }

            } catch (\Exception $e) {}

        }

    }
}