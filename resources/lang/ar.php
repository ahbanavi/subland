<?php

return [
    'welcome' =>  <<< MYHEREDOC
🌺 مرحبا *%name*
مرحبا بك في Subland.
مع هذا البوت , يمكنك تحميل ترجمات الافلام و المسلسلات مباشرة  من subscene.

هذا البوت يعمل قي وضع Inline mode, مما يعني أنه يمكنك استخدامه في أي محادثات فقط عن طريق كتابة اسم فيلم أو المسلسل أمام اسم مستخدم البوت.

🌎  لتغيير لغة الترجمة ، استخدم هذا الامر /lang .

✍️ لطرح سؤال او مشكلة تواصل مع المطور [من هنا](https://t.me/yedoost).

ملاحظة:
🔍 يجب ان تكون للغة البحث باللغة * "English" بدون كتابة السنة*.

مثال:
```
@$_ENV[BOT_USER_NAME] venom
or
@$_ENV[BOT_USER_NAME] game of thrones
```
Use /settings for changing Robot language.
MYHEREDOC,
    'change_subtitle_language' => <<< MYHEREDOC
🌍 لغة الترجمة الحالية للبحث هي %lang
للتغيير ، اختر لغة ترجمة أخرى من الأسفل:
MYHEREDOC,
    'tldr' => <<< MYHEREDOC
💬     بختصار:

Inline Mode => للبحث عن ترجمة
/lang => لتغيير لغة *الترجمة*.
/settings  => لتغيير لغة *البوت*.
/help => لرؤية هذه الرسالة وما فوقها.
MYHEREDOC,
    'no_sub_found' => <<< MYHEREDOC
⚠️ مؤسف, %lang لم يتم إصدار ترجمات لهذا الفيلم/المسلسل بعد.
 يرجى المحاولة مرة أخرى في المستقبل.
🌍 يمكنك أيضًا تغيير لغة البحث عن الترجمة لمزيد من النتائج  باستخدام  هذا الامر  /lang .
MYHEREDOC,
    'key_try_here' => 'جرب البحث هنا',
    'key_try_else' => 'او في محادثة اخرى',
    'key_next' => 'الترجمة التالية',
    'key_previous' => 'الترجمة السابقة',
    'key_list' => 'عرض جميع الترجمات',
    'success_change_local_language' => "🗣 تم تغير اللغة الى العربية لمزيد من المعلومات استخدم هذا الامر /help.",
    'loading' => 'جار البحث...',
    'subtitle_loaded' => 'تحميل الترجمات!',
    'just_one' => ' لايوجد المزيد من الترجمات !',
    'just_one_callback' => "لا توجد ترجمات أخرى  حتى الآن!  حاول لاحقا:)",
    'dont_understand' => "لا افهم استخدم هذا الامر للمساعدة /help ",
    'sub_lang_answer' => 'تم تغيير لغة الترجمة بنجاح!',
    'local_lang_answer' => 'تم تغيير لغة البوت بنجاح!',
    'no_results_found' => 'لم يتم العثور على نتائج!',
    'try_again' => '🔍 حاول مجددا...'
];
