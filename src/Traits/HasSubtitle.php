<?php


namespace SubLand\Traits;


use Carbon\Carbon;
use Longman\TelegramBot\Entities\InlineKeyboard;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\Film;
use SubLand\Models\Subtitle;
use SubLand\Utilities\Subscene;

trait HasSubtitle
{

    public function getFirstSubtitle(Film $film, $force = false): Subtitle
    {
        /** @var Subtitle $subtitle */
        $subtitle = $film->subtitles()->language($this->user->language)->first();

        if ($force || ($subtitle && $subtitle->updated_at->addSeconds($_ENV['SUBTITLE_CACHE_TIME'])->gt(Carbon::now()))){
            if ($force){
                $subtitle->touch();
            }
            $subtitle->checkDownload($film->title);
            return $subtitle;
        }

        $subtitles = Subscene::getSubtitles($film->url)['subtitles'];
        if ($subtitles == []){
            throw new SubNotFoundException();
        }
        data_fill($subtitles, '*.film_id', $film->film_id);
        Subtitle::upsert($subtitles, ['url']);
        return $this->getFirstSubtitle($film, true);
    }

    private static function getSubtitleText(Subtitle $subtitle,Film $film): string
    {
        $download_url = $_ENV['UPLOAD_REDIRECT'] . '/' . $subtitle->download_url;

        return <<< MYHEREDOC
<b>{$film->title} $film->year</b> {$subtitle->extra}<a href='$download_url'>⁪</a>
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
        $prev_sub = $subtitle->previous();
        if ($prev_sub){
            $control_key[] = [
                'text' => trans('key_previous'),
                'callback_data' => json_encode([
                    'subtitle_id' => $prev_sub->subtitle_id
                ])
            ];
        }

        $next_sub = $subtitle->next();
        if ($next_sub){
            $control_key[] =[
                'text' => trans('key_next'),
                'callback_data' => json_encode([
                    'subtitle_id' => $next_sub->subtitle_id
                ])
            ];
        }

        if (!$control_key){
            return new InlineKeyboard([[
                'text' => trans('just_one'),
                'callback_data' => json_encode([
                    'just_one' => ''
                ])
            ]]);
        }

        global $local_lang;
        if (in_array($local_lang, ['fa', 'ar'])){
            $control_key = array_reverse($control_key);
        }
        $list_key = [[
            'text' => trans('key_list'),
            'switch_inline_query_current_chat' => 'list:' . $subtitle->film_id . '-' . $subtitle->language . '-' . $inline_message_id
        ]];

       return new InlineKeyboard($control_key,$list_key);
    }
}
