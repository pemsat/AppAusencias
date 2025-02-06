<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'starts_at',
        'ends_at',
        'comment',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function isValidSchedule(): bool
    {
        $start = Carbon::parse($this->starts_at);
        $end = Carbon::parse($this->ends_at);
        $dayOfWeek = $start->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        $allowedHours = [
            'morning' => ['start' => '08:00', 'end' => '14:00'],
            'evening' => ['start' => '14:00', 'end' => '20:00'],
            'tuesday' => ['start' => '15:00', 'end' => '20:00']
        ];

        $classStart = ($dayOfWeek === 2) ? $allowedHours['tuesday']['start'] : $allowedHours['morning']['start'];
        $classEnd = ($dayOfWeek === 2) ? $allowedHours['tuesday']['end'] : $allowedHours['evening']['end'];

        return ($start->format('H:i') >= $classStart && $end->format('H:i') <= $classEnd);
    }


    public function scopeOverlaps(Builder $query, $teacherId, $startsAt, $endsAt): Builder
    {
        return $query->where('teacher_id', $teacherId)
            ->where(function ($query) use ($startsAt, $endsAt) {
                $query->whereBetween('starts_at', [$startsAt, $endsAt])
                      ->orWhereBetween('ends_at', [$startsAt, $endsAt]);
            });
    }

    public function overlapsWithExisting(): bool
    {
        return self::overlaps($this->teacher_id, $this->starts_at, $this->ends_at)->exists();
    }
}

