<?php

namespace SubLand\Utilities;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\Film;
use Symfony\Component\Console\Helper\Helper;

/**
 * Subscene-API-PHP
 * By NimaH79
 * http://NimaH79.ir.
 *
 * Modified by ahbanavi
 */
class Subscene
{
    private static $language = 'farsi_persian';
    private static $page = '';
    private static $cookies = 'cookies.txt';
    public const BASE_URL = 'https://subscene.com';


    public static function setLanguage($lang){
        self::$language = $lang;
    }

    public static function search(string $title, bool $exit_on_bad_request = false)
    {
        $title = Helpers::removeAccents($title);
        self::$page = self::curl_post(
            self::BASE_URL.'/subtitles/searchbytitle',
            ['query' => $title]
        );
        if (!self::isLoaded()) {
            if ($exit_on_bad_request) {
                return [];
            }
            return self::search($title);
        }

        $titles = self::xpathQuery("//ul/li/div[@class='title']/a/text()");
        $urls = self::xpathQuery("//ul/li/div[@class='title']/a/@href");
        $results = [];
        for ($i = 0; $i < count($titles); $i++) {
            $url = trim(strtolower($urls[$i]->nodeValue));
            $results[$url] ??= [
                'title' => trim($titles[$i]->nodeValue),
                'url'   => $url,
                'poster' => '',
                'imdb' => ''
            ];
        }

        return array_values($results);
    }

    public static function getSubtitles(array $data, bool $exit_on_bad_request = false): array
    {

        self::$page = self::curl_get_contents(self::BASE_URL.$data['url'].'/'.self::$language,true);

        if (!self::isLoaded() || !self::isSorted()) {
            return self::getSubtitles($data);
        }

        $result = [];
        foreach ([
            'title' => '//h2/text()',
            'year' => "//li[strong[contains(text(), 'Year')]]/text()[last()]",
            'poster' => "//img[@alt='Poster']/@src",
            'imdb' => "//a[@target='_blank' and @class='imdb']/@href",
        ] as $part => $query) {
            ${$part} = self::xpathQuery($query);
            if (count(${$part}) > 0) {
                $result[$part] = trim(${$part}[0]->nodeValue);
            }
        }
        $subtitle_nodes = self::xpathQuery("//tr[td[@class='a5']]");

        if (!$subtitle_nodes->length){
            if ($exit_on_bad_request) {
                return [];
            }
            return self::getSubtitles($data, true);
        }
        $subtitles = [];
        foreach ($subtitle_nodes as $subtitle_node) {

            $subtitle_node_html = $subtitle_node->ownerDocument->saveHTML($subtitle_node);
            $url = trim(self::xpathQuery('//tr/td[1]/a/@href', $subtitle_node_html)[0]->nodeValue);
            $info = [trim(self::xpathQuery('//td/a/span[2]/text()', $subtitle_node_html)[0]->nodeValue)];
            if(isset($subtitles[$url])) {
                $subtitles[$url]['info'][] = str_ireplace('.', ' ',$info[0]);
                continue;
            }
            $language = str_ireplace('/','_',strtolower(trim(self::xpathQuery('//td/a/span[1]/text()', $subtitle_node_html)[0]->nodeValue)));
            $author_node = self::xpathQuery("//td[@class='a5']/a/text()", $subtitle_node_html);
            $author_name = $author_node[0]->nodeValue ?? 'Anonymous';
            $author_name = trim($author_name);
            $author_node = self::xpathQuery("//td[@class='a5']/a/@href", $subtitle_node_html);
            $author_url = $author_node[0]->nodeValue ?? 'Anonymous';
            $author_url = trim($author_url);
            $film_id = $data['film_id'];
            $comment = Helpers::mbWordWrap(Helpers::cleanSpace(self::xpathQuery("//td[@class='a6']/div/text()", $subtitle_node_html)[0]->nodeValue));
            $subtitles[$url] = compact('info', 'language', 'author_name','author_url', 'comment', 'url','film_id');
        }

        foreach ($subtitles as $key => $subtitle){
            $subtitles[$key]['extra'] = self::getExtra($subtitle['comment'],$subtitle['info']);
            $subtitles[$key]['info'] = implode(PHP_EOL, self::getInfo($result['title'],$subtitle['info'],$subtitles[$key]['extra']));
            $subtitles[$key]['extra'] = implode('|', $subtitles[$key]['extra']);
        }

        $result['subtitles'] = array_reverse(array_values($subtitles));
        return $result;
    }

    public static function getSubtitleInfo(array $data, bool $exit_on_bad_request = false): array
    {
        self::$page = self::curl_get_contents(self::BASE_URL.$data['url']);
        if (!self::isLoaded()) {
            if ($exit_on_bad_request) {
                return [];
            }

            return self::getSubtitleInfo($data);
        }

        $result = [];

        $download_url = self::xpathQuery("//a[@id='downloadButton']/@href");
        if ($download_url->length < 1)
            return self::getSubtitleInfo($data);

        $preview = self::xpathQuery("//div[@id='preview']/p");
        $result['preview'] =  Helpers::cleanSpace($preview[0]->ownerDocument->saveHTML($preview[0]));

        $details = self::xpathQuery("//div[@id='details']/ul/li");
        if ($details->length  > 0) {
            $details_text = '';
            $details_count = count($details);
            for ($i = 0; $i < $details_count; $i++) {
                $details_text .= Helpers::mbWordWrap(Helpers::cleanSpace($details[$i]->nodeValue));
                if ($i != $details_count - 1) {
                    $details_text .= PHP_EOL;
                }
            }
            $result['details'] = $details_text;
        }
        $result['details'] = preg_replace("/^.*(?:-).*$(?:\r\n|\n)?/m",'',$result['details']);
        preg_match('/(\d{1,2}\/\d{1,2}\/\d{4} \d{1,2}:\d{1,2} [A|P]M)/',$result['details'],$date);
        $result['release_at'] = \Carbon\Carbon::parse($date[0])->toDateTime();

        $download_url = self::BASE_URL.$download_url[0]->nodeValue;
        $file_name = Helpers::normalChars($data['title']) . " - $data[language]" . $data['extra']  . " [@$_ENV[BOT_USER_NAME]";
        $lang = self::$language == 'farsi_persian' ? '🇮🇷 Persian Subtitle' : '🇬🇧 English Subtitle';

        $caption = "<b>Author:</b> <code>" . str_ireplace('.','',$data['author_name']) . "</code>\n" . $lang . $data['extra'];

        $upload = Helpers::uploadDocument([
            'chat_id' => '@' . $_ENV['UPLOAD_CHANNEL'],
            'file_name' => $file_name,
            'caption' => $caption,
            'url' => $download_url
        ]);

        if ($upload) {
            $messageID = $upload->result->message_id;
            $urlCode = helpers::base64UrlEncode(base64_encode(sprintf('%010d', $messageID)));
            $result['download_url'] = $urlCode;
        } else {
            $result['download_url'] = $download_url;
        }

        return $result;
    }

    public static function getHome($exit_on_bad_request = false): array
    {
        self::$page = self::curl_get_contents(self::BASE_URL);
        if (!self::isLoaded()) {
            if ($exit_on_bad_request) {
                return [];
            }

            return self::getHome(true);
        }

        $result = [];
        // Popular subtitles
        for ($box = 1; $box <= 2; $box++){
            $titles = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][$box]//div[@class='title']/a[1]/text()");
            $posters = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][$box]//div[@class='poster']/img/@src");
            $imdbs = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][$box]//div[@class='title']/a[2]/@href");
            $urls = self::xpathQuery("//div[@class='popular-films']/div[@class='box'][$box]//div[@class='title']/a[1]/@href");

            for ($i = 0; $i < count($titles); $i++) {
                $item = [
                    'title'  => trim($titles[$i]->nodeValue),
                    'poster' => $posters[$i]->nodeValue,
                    'url'    => $urls[$i]->nodeValue,
                    'imdb'   => $imdbs[$i]->nodeValue ?? null
                ];

                $result[] = $item;
            }
        }

        return $result;
    }

    public static function getDownload(string $url, string $filename): void
    {
        $data = self::curl_get_contents($url);
        $file_name = $filename;
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=$file_name");
        header('Content-Length: '.strlen($data));
        echo $data;
    }

    private static function curl_get_contents(string $url,$set_cookie = false): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($set_cookie){
            curl_setopt($ch, CURLOPT_COOKIE, "SortSubtitlesByDate=true");
        }
//        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookies);
//        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookies);
        $response = curl_exec($ch);
        $http_header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_header == 404){
            throw new SubNotFoundException('متاسفانه زیرنویس مورد نظر از سایت مبدا حذف شده است.');
        }

        return $response;
    }

    private static function curl_post(string $url, ?array $parameters = null): string
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookies);
//        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookies);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private static function xpathQuery(string $query, ?string $html = null): DOMNodeList
    {
        $libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (empty($query) || empty($html ?? self::$page)) {
            return new DOMNodeList();
        }
        $xpath = self::htmlToDomXPath($html ?? self::$page);
        $results = $xpath->query($query);
        libxml_use_internal_errors($libxml_use_internal_errors);

        return $results;
    }

    private static function xpathEvaluate(string $expression, string $html)
    {
        $libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (empty($expression) || empty($html)) {
            return false;
        }
        $xpath = self::htmlToDomXPath($html);
        $result = $xpath->evaluate($expression);
        libxml_use_internal_errors($libxml_use_internal_errors);

        return $result;
    }

    private static function htmlToDomXPath(string $html): DomXPath
    {
        $dom = new DomDocument();
        $dom->loadHTML("<?xml encoding='utf-8'?>$html");
        $xpath = new DomXPath($dom);

        return $xpath;
    }

    private static function isLoaded(): bool
    {
        return !Helpers::iContains(self::$page,'best of luck');
    }

    private static function isSorted(): bool
    {
        return (bool) self::xpathQuery("//input[@id='SortByDate']/@checked")->length;
    }

    private static function getExtra(string $comment, array $info = []): array
    {
        $haystack = implode('',$info) . $comment;
        $haystack .= str_ireplace(['.',' '],'',$haystack);
        $extra = [];
        if (Helpers::iContains($haystack, 'trailer')) {
            $extra[] = ' Trailer';
        }
        if (Helpers::iContains($haystack, 'allepisode')) {
            $extra[] = ' All Episodes';
        } elseif (preg_match('/S\d{1,2}E\d{1,2}/mix', $haystack,$match)){
                $extra[] = ' ' . $match[0];
        }

        return $extra;
    }

    private static function getInfo(string $title, array $info, array $extra): array
    {
        $title = Str::beforeLast($title,'-');

        $results = [];
        foreach ($info as $info_item){
            $info_item = str_ireplace('.',' ',$info_item);
            $info_item = str_ireplace(trim($title), '',$info_item);
            foreach ($extra as $extra_item){
                $info_item = str_ireplace(trim($extra_item), '',$info_item);
            }
            $info_item = preg_replace('/(19|20)\d{2}/m','',$info_item);
            $results[] = Helpers::mbWordWrap(Helpers::cleanSpace($info_item));
        }

        return $results;
    }
}
