<?php


namespace SubLand\Models;
use Illuminate\Database\Eloquent\Model;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Traits\Escapable;
use SubLand\Traits\HasSubtitle;
use SubLand\Utilities\Subscene;
use Illuminate\Database\Eloquent\Builder;

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
        $sub = self::firstOrCreate(['url' => $subtitle['url']],$subtitle + ['prev' => $prev]);
        if ($prev != 0 && $sub->prev == 0){
            $sub->prev = $prev;
            $sub->update();
            return False;
        }
        return $sub->subtitle_id;
    }

    public function deleteSubtitle(): Subtitle
    {
        $next_subtitle = $this->next();
        if ($next_subtitle === false){
            throw new SubNotFoundException();
        }
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


    public function previous(){
        // get next user
        return self::where('film_id', $this->film_id)->where('subtitle_id', '>', $this->subtitle_id)->orderBy('subtitle_id','asc')->first();

    }
    public function next(){
        // get previous  user
        return self::where('film_id', $this->film_id)->where('subtitle_id', '<', $this->subtitle_id)->orderBy('subtitle_id','desc')->first();

    }

//    protected static function boot()
//    {
//
//        // Order by created_at DESC
//        static::addGlobalScope('order', function (Builder $builder) {
//            $builder->orderByDesc('subtitle_id');
//        });
//    }
}
