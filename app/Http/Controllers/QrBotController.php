<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;

use App\Services\Qiita;

class QrBotController extends Controller
{
    public function index()
    {
        return view('linebot.index');
    }

    public function qiitax(Request $request)
    {
        Log::debug($request->header());
        Log::debug($request->input());

        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $request->header('x-line-signature');

        if (!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid signature');
        }

        $events = $lineBot->parseEventRequest($request->getContent(), $signature);

        Log::debug($events);
        
        foreach ($events as $event) {
            if (!($event instanceof TextMessage)) {
                Log::debug('Non text message has come');
                continue;
            }
            
            // -- ここから追加
            $qiita = new Qiita();
            $qiitaResponse = $gurunavi->searchQiita($event->getText());

            if (array_key_exists('error', $qiitaResponse)) {
                $replyText = $qiitaResponse['error'][0]['message'];
                $replyToken = $event->getReplyToken();
                $lineBot->replyText($replyToken, $replyText);
                continue;
            }

            $replyText = '';
            foreach($qiitaResponse['rest'] as $respon) {
                $replyText .=
                    $respon['title'] . "\n" .
                    $respon['url'] . "\n" .
                    $respon['updated_at'] . "\n" .
                    "\n";
            }
            
            $replyToken = $event->getReplyToken();
            $lineBot->replyText($replyToken, $replyText);
        }        
    }
}