<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AbsenceResource;
use App\Models\Absence;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Saade\FilamentFullCalendar\Actions;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\CreateAction;


class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Absence::class;

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridMonth,dayGridWeek',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'selectable' => true,
        ];
    }

    protected function headerActions(): array
    {
        return [];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Absence::query()
            ->whereBetween('date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(
                fn(Absence $absence) => EventData::make()
                    ->id($absence->id)
                    ->title($absence->teacher->name . ' - ' . $this->formatHour($absence->hour) . ' - ' . $absence->reason)
                    ->start($absence->date)
            )
            ->toArray();
    }

    public function onDateClick(array $info): void
    {
        $date = $info['dateStr'];
        $hour = $this->getHourSlotFromTime(Carbon::parse($info['date'])->format('H:i'));
        $absenceId = null; // Default for new absence

        // Check if there's an existing absence for the selected date and hour
        $existingAbsence = Absence::where('date', $date)->where('hour', $hour)->first();
        if ($existingAbsence) {
            $absenceId = $existingAbsence->id; // Set ID for editing
        }

        // Dispatch event to open the modal
        $this->dispatchBrowserEvent('open-modal', [
            'date' => $date,
            'hour' => $hour,
            'absenceId' => $absenceId,
        ]);
    }

    private function getHourSlotFromTime($time)
    {
        $hourMap = AbsenceResource::getHourOptions(null);
        foreach ($hourMap as $key => $range) {
            [$start, $end] = explode(' - ', $range);
            if ($time >= $start && $time <= $end) {
                return $key;
            }
        }
        return null;
    }

    private function formatHour($hour)
    {
        return AbsenceResource::getHourOptions(null)[$hour] ?? 'Unknown';
    }

    public function eventDidMount(): string
    {
        return <<<JS
    function({ event, el }) {
        const tooltipContent = 'Teacher: ' + event.extendedProps.teacherName + '<br>Hour: ' + event.extendedProps.hour + '<br>Reason: ' + event.extendedProps.reason;
        el.setAttribute("x-tooltip", "tooltip");
        el.setAttribute("x-data", "{ tooltip: '" + tooltipContent + "' }");

        el.addEventListener('click', () => {
            Livewire.emit('editAbsence', event.id);
        });
    }
    JS;
    }
}
