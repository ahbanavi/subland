<?php

return [
    'welcome' =>  <<< MYHEREDOC
🌺 Hey *%name*
Welcome to Subland.
With this robot, you can get subtitles of movies and tv shows directly from subscene.

This bot works in Inline mode, wich means you can use it in any chats just by typing the name of a movie or show in front of the bot user name.

🌎 Also for changing subtitle language, use /settings command.

✍️ Feel free to drop any questions or issues [here](https://t.me/yedoost).

🔍 Keep in mind that search query must be in *English without the year*.

Examples:
```
@$_ENV[BOT_USER_NAME] venom
or
@$_ENV[BOT_USER_NAME] game of thrones
```
Use /lang command for changing bot language
برای تغییر زبان ربات از دستور /lang استفاده کن.
MYHEREDOC,
    'change_subtitle_language' => <<< MYHEREDOC
🌍 Your current subtitle language for searching is %lang
For changing, chose another language from below:
MYHEREDOC,
    'key_try_here' => 'Try it in here',
    'key_try_else' => 'Or in another chat',
    'key_next' => 'Next',
    'key_previous' => 'Previous',
    'key_list' => 'List view',
    'success_change_local_language' => "🗣 Hooray! Now I speak English.\nfor more information use /start.",
    'loading' => 'Loading...',
    'subtitle_loaded' => 'Subtitle loaded!'
];
