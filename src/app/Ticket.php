<?php


namespace App;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function reserve(): void
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function release(): void
    {
        $this->update(['reserved_at' => null]);
    }

    public function concert(): Relation
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute(): int
    {
        return $this->concert->ticket_price;
    }
}