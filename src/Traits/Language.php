<?php


namespace SubLand\Traits;


use SubLand\Utilities\Subscene;

trait Language
{
    private function getLanguageMessage(): string
    {
        $current_lang = Subscene::LANGUAGES[$this->user->language];
        return trans('change_subtitle_language', ['%lang' => $current_lang['title'] . ' ' . $current_lang['flag']]);
    }

    private function getLanguageKeys(): array
    {
        $keys = [];
        foreach (Subscene::LANGUAGES as $language => $value){
            if ($language == $this->user->language){
                continue;
            }
            $keys[] = [['text' => $value['title'] . ' ' . $value['flag'], 'callback_data' => json_encode(['language' => $language])]];
        }

        return $keys;
    }
}
