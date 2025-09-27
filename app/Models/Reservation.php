<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Reservation extends Model implements Eventable
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'from',
        'to',
    ];

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make()
            ->action('edit')
            ->title($this->title)
            ->backgroundColor(color: '#34D399') // ✅ Tailwind green-400
            ->start($this->from)
            ->end($this->to);
    }
}
