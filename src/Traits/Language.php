<?php


namespace SubLand\Traits;


use SubLand\Utilities\Subscene;

trait Language
{
    private function getLanguageMessage(): string
    {
        $current_lang = Subscene::LANGUAGES[$this->user->language];
        return <<< MYHEREDOC
🌍 زبان جستجو شما در حال حاضر بر روی $current_lang[title] $current_lang[flag] تنظیم شده است.
برای تغییر، لطفا زبان مورد نظر خود را انتخاب کنید:
MYHEREDOC;
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
