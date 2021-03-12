<?php


namespace SubLand\Traits;


use Carbon\Carbon;
use Illuminate\Support\Str;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use SubLand\Models\Film;
use SubLand\Models\Subtitle;
use SubLand\Utilities\Helpers;
use SubLand\Utilities\Subscene;

trait HasSubtitle
{

    public static function getFirstSubtitle(Film $film): Subtitle
    {
        $subtitle = Subtitle::first();

        if (count($subtitle) && $film->updated_at->addSeconds($_ENV['SUBTITLE_CACHE_TIME'])->gt(Carbon::now())){
            $subtitle->checkDownload($film->title);
            return $subtitle;
        }

        $subtitles = Subscene::getSubtitles(['url' => $film->url,'film_id' => $film->film_id])['subtitles'];

        Subtitle::upsert($subtitles, ['url']);


//        $prev = 0;
//        foreach ($subtitles['subtitles'] as $item){
//            $prev = Subtitle::addSubtitle($item,$prev);
//            if ($prev === False){
//                break;
//            }
//        }
//        $subtitle = Subtitle::where('film_id',$film->film_id)->where('prev',0)->first();
        $subtitle = Subtitle::first();

        $film->touch();
        $subtitle->checkDownload($film->title);
        return $subtitle;
    }

    private static function getSubtitleText(Subtitle $subtitle,Film $film): string
    {
        $download_url = $_ENV['UPLOAD_REDIRECT'] . '/' . $subtitle->download_url;

        return <<< MYHEREDOC
<b>{$film->title}</b> {$subtitle->extra}<a href='$download_url'>⁪</a>
<b>Comment:</b>
<pre>{$subtitle->comment}</pre>
<b>Info:</b>
<pre>{$subtitle->info}</pre>
<b>Details:</b>
<pre>{$subtitle->details}</pre>
MYHEREDOC;

    }

    private function getSubtitleKeyboard(Subtitle $subtitle, string $inline_message_id): InlineKeyboard
    {
        $control_key = [];
        $next_sub = $subtitle->next();
        file_put_contents('t1', json_encode($next_sub));
        if (!$next_sub ){
            $control_key[] =[
                'text' => 'بعدی',
                'callback_data' => json_encode([
                    'subtitle_id' => $next_sub->subtitle_id
                ])
            ];
        }

//        if (!$subtitle->previous()){
//            $control_key[] = [
//                'text' => 'قبلی',
//                'callback_data' => json_encode([
//                    'subtitle_id' => $subtitle->previous()->subtitle_id
//                ])
//            ];
//        }

        $list_key = [[
            'text' => 'نمایش بصورت لیست',
            'switch_inline_query_current_chat' => 'list:' . $subtitle->film_id . '-' . $inline_message_id
        ]];

       return new InlineKeyboard($control_key,$list_key);
    }
}
