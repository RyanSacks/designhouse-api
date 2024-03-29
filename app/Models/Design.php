<?php

namespace App\Models;

use App\Models\Traits\Likeable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentTaggable\Taggable;
use Storage;

class Design extends Model
{
    use HasFactory;
    use Taggable;
    use Likeable;

    protected $fillable=[
      'user_id',
      'team_id',
      'image',
      'title',
      'description',
      'slug',
      'close_to_comment',
      'is_live',
      'upload_successful',
      'disk'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'asc');
    }

    public function getImagesAttribute()
    {
        return [
            'thumbnail' => $this->getImagePath('thumbnail'),
            'large' => $this->getImagePath('large'),
            'original' => $this->getImagePath('original')
        ];
    }

    protected function getImagePath($size)
    {
        return Storage::disk($this->disk)
            ->url("uploads/designs/{$size}/".$this->image);
    }
}
