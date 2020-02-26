<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;

use App\Services\Connpass;

class QrBotController extends Controller
{
    public function index()
    {
        return view('linebot.index');
    }

    public function cpserch(Request $request)
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
            $connpass = new Connpass();
            $connpassResponse = $connpass->searchCpevents($event->getText());

            if (array_key_exists('error', $connpassResponse)) {
                $replyText = $connpassResponse['error'][0]['message'];
                $replyToken = $event->getReplyToken();
                $lineBot->replyText($replyToken, $replyText);
                continue;
            }

            $replyText = '';
            foreach($connpassResponse['rest'] as $respon) {
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