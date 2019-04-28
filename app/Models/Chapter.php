<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
  public function subject() {
    return $this->belongsTo(Subject::class, 'subject_id', 'id');
  }

  public function questions() {
    return $this->hasMany(Question::class, 'chapter_id', 'id');
  }

  public function scopeActive($query, $status = true)
  {
    return $query->where('is_actived', $status);
  }

}
