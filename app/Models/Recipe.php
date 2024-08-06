<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Recipe extends Model
{
    use HasFactory;
    use Searchable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description','ingredients', 'steps', 'user_id','category_id','preparation_time'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_id','category_id' // Hide the user_id if necessary
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];
    protected $casts = [
        'ingredients' => 'array',
        'steps' => 'array',
        
    ];


    /**
     * Get the user that owns the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
{
    return $this->belongsTo(Category::class);
}
public function comments()
{
    return $this->hasMany(Comment::class);
}

   
 /**
 * Get the indexable data array for the model.
 *
 * @return array<string, mixed>
 */
public function toSearchableArray(): array
{
    return [
        'id' => (string) $this->id,
        'title' => $this->title,
        'ingredients' => $this->ingredients,
        'category' => $this->category->name ?? null,
        'preparation_time' => $this->preparation_time,
        'created_at' => $this->created_at->timestamp,
    ];
}
}

