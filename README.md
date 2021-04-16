

<p align="center"><img src="https://user-images.githubusercontent.com/39927312/115019142-00df0700-9ece-11eb-9559-d1af23d6b93f.png" title="Telegram Subscene Robot" alt="Telegram Subscene Robot"></p

#  Telegram Subscene Robot [@SubLandBot][sublandbot-url]
With [this robot][sublandbot-url], you can get subtitles of movies and TV shows directly from subscene in [telegram](https://telegram.org/).

## :sparkles: Features
- Search subtitles for movies. *(inline mode)*
- Search subtitles for TV series and anime. *(inline mode)*
- See subscene home page. *(empty search query in inline mode)*
- Cashing subtitles for better performance. [*(See Caching)*](#caching)
- Search **subtitles** in 6 languages: *(/lang command)*
	- Persian :iran:
	- English :gb:
	- German :de:
	- Arabic :united_arab_emirates:
	- French :fr:
	- Russian :ru:
- Support English :gb: and Persian :iran: language for **interface**. *(/settings command)*


## :information_source: Usage

- *Inline Mode* => for **searching subtitles**.
- */lang* => for changing **Subtitle language**.
- */settings*  => for changing **Robot language**.
- */help* => see **help** messages.

## :gear: Caching
Caching configs can be change in `.env` file.  
Currently cache settings for [@SubLandBot][sublandbot-url] are:
| Cache Key | Time | Description
|--|--|--
|`SEARCH_CACHE_TIME`|10 days| For caching search results
|`HOME_CACHE_TIME`|6 hours| For caching subscene home results
|`SUBTITLE_CACHE_TIME`|1 hour| For caching subtitle results


## :black_nib: Credits

- [Amirhossein Banavi](https://github.com/ahbanavi)
- [Nima HeydariNasab](https://github.com/nimah79)
	- for [Subscene.php](src/Utilities/Subscene.php) file; that's a modified version of [this project](https://github.com/nimah79/Subscene-API-PHP).

## :email: Contact

If you want to contact me you can reach me at <yedoost@att.net>.

## :balance_scale: License

This project uses the following license: [GNU General Public License v3.0](LICENSE).

## Links

- [Subland Telegram Robot][sublandbot-url]

[sublandbot-url]: https://t.me/SubLandBot
