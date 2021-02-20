<?php


namespace SubLand\Models;
use Illuminate\Database\Eloquent\Model;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Traits\Escapable;
use SubLand\Traits\HasSubtitle;
use SubLand\Utilities\Subscene;

class Subtitle extends Model
{
    use Escapable;
    use HasSubtitle;

    protected $table = 'subtitles';
    protected $primaryKey = 'subtitle_id';

    protected $guarded = [];

    public function film()
    {
        return $this->hasOne('SubLand\Models\Film','film_id','film_id');
    }

    public static function addSubtitle($subtitle, $prev)
    {
        return self::firstOrCreate(['url' => $subtitle['url']],$subtitle + ['prev' => $prev])->subtitle_id;
    }

    public function deleteSubtitle(): Subtitle
    {
        $next_subtitle = $this->nextSubtitle();
        if ($next_subtitle === false){
            throw new SubNotFoundException();
        }
        $next_subtitle->prev = $this->prev;
        $next_subtitle->update();
        $this->delete();

        return $next_subtitle;
    }

    public function checkDownload(string $title): Subtitle
    {
        if (is_null($this->download_url)){
            try {
                $find = Subscene::getSubtitleInfo(['title' => $title] + $this->toArray());
            } catch (SubNotFoundException $exception){
                $next_subtitle = $this->deleteSubtitle();
                return $next_subtitle->checkDownload($title);
            }
            $this->preview = $find['preview'];
            $this->details = $find['details'];
            $this->download_url = $find['download_url'];
            $this->release_at = $find['release_at'];
            $this->update();
        }

        return $this;
    }

    /**
     * Find Next Subtitle on the list
     *
     * @return false|Subtitle
     */
    public function nextSubtitle()
    {
        return self::firstWhere('prev',$this->subtitle_id) ?? false;
    }
}
