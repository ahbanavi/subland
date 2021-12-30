<?php

return [
    'welcome' =>  <<< MYHEREDOC
🌺 Hey *%name*
Welcome to Subland.
With this robot, you can get subtitles of movies and tv shows directly from subscene.

This bot works in Inline mode, wich means you can use it in any chats just by typing the name of a movie or show in front of the bot user name.

🌎 Also for changing subtitle language, use /lang command.

✍️ Feel free to drop any questions or issues [here](https://t.me/yedoost).

🔍 Keep in mind that search query must be in *English without the year*.

Examples:
```
@$_ENV[BOT_USER_NAME] venom
or
@$_ENV[BOT_USER_NAME] game of thrones
```
برای تغییر زبان ربات از دستور /settings استفاده کن.
لتغيير لغة البوت استخدم هذا الامر /settings.
MYHEREDOC,
    'change_subtitle_language' => <<< MYHEREDOC
🌍 Your current subtitle language for searching is %lang
For changing, choose another subtitle language from below:
MYHEREDOC,
    'tldr' => <<< MYHEREDOC
💬     TL;DR

Inline Mode => for searching subtitles.
/lang => for changing *Subtitle* language.
/settings  => for changing *Robot* language.
/help => for see this and above message.
MYHEREDOC,
    'no_sub_found' => <<< MYHEREDOC
⚠️ Unfortunately, %lang subtitles for this movie/series hasn't been released yet.
Please try again in the future.
🌍 You can also change the subtitle search language with the /lang command.
MYHEREDOC,
    'key_try_here' => 'Try it in here',
    'key_try_else' => 'Or in another chat',
    'key_next' => 'Next',
    'key_previous' => 'Previous',
    'key_list' => 'List view',
    'success_change_local_language' => "🗣 Hooray! Now I speak English.\nfor more information use /help.",
    'loading' => 'Loading...',
    'subtitle_loaded' => 'Subtitle loaded!',
    'just_one' => 'It was just one subtitle!',
    'just_one_callback' => "No other subtitles released yet! Try later :)",
    'dont_understand' => "I don't understand!\nPlease try /help command.",
    'sub_lang_answer' => 'Subtitle language changed successfully!',
    'local_lang_answer' => 'Robot language changed successfully!',
    'no_results_found' => 'No results found!',
    'try_again' => '🔍 Try again...'
];
