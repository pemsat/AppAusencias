<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AbsenceResource;
use App\Models\Absence;
use Carbon\Carbon;
use Filament\Forms\Form;
use Saade\FilamentFullCalendar\Actions;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Absence::class;

    public function getFormSchema(): array
    {
        return [
            TextInput::make('comment')
                ->label('Motivo')
                ->required(),

            DatePicker::make('starts_at')
                ->label('Fecha de ausencia')
                ->required()
                ->native(false)
                ->reactive(),

            Select::make('ends_at')
                ->label('Hora de ausencia')
                ->options(fn ($get) => $this->getHourSlotFromTime(null, $get('starts_at')) ?? [])
                ->required()
                ->reactive(),
        ];
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridWeek',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'selectable' => true,
            'hiddenDays' => [0, 6], // Hide Sunday & Saturday
        ];
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(
                    fn(Form $form, array $arguments) =>
                    $form->fill([
                        'starts_at' => $arguments['start'] ?? null,
                        'ends_at' => $arguments['end'] ?? null,
                    ])
                ),
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Absence::query()
            ->whereBetween('starts_at', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(
                fn(Absence $absence) => EventData::make()
                    ->id($absence->id) // Changed from UUID to ID
                    ->title($absence->teacher->name . ' - ' . Carbon::parse($absence->starts_at)->format('H:i') . ' - ' . $absence->comment)
                    ->start($absence->starts_at->toIso8601String())
                    ->end($absence->ends_at->toIso8601String())
                    ->url(
                        url: AbsenceResource::getUrl(name: 'view', parameters: ['record' => $absence]),
                        shouldOpenUrlInNewTab: true
                    )
            )->toArray();
    }

    public function onDateClick(array $info): void
    {
        $date = $info['dateStr'];
        $hour = $this->getHourSlotFromTime($date);
        $absenceId = Absence::whereDate('starts_at', $date)
            ->whereTime('starts_at', '>=', $hour)
            ->value('id'); // Directly get ID instead of fetching model

        $this->dispatchBrowserEvent('open-modal', [
            'absenceId' => $absenceId,
            'date' => $date,
            'hour' => $hour,
        ]);
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        $hour = $this->getHourSlotFromTime($startDate->format('H:i'));

        $this->mountAction('create', [
            'type' => 'select',
            'starts_at' => $startDate->toDateTimeString(),
            'ends_at' => $endDate->toDateTimeString(),
            'hour' => $hour,
        ]);
    }

    private function getHourSlotFromTime($time = null, $date = null)
    {
        $hourMap = [
            'normal' => [
                '08:00' => '08:00 - 08:55',
                '08:55' => '08:55 - 09:50',
                '09:50' => '09:50 - 10:45',
                '11:15' => '11:15 - 12:10',
                '12:10' => '12:10 - 13:05',
                '13:05' => '13:05 - 14:00',
                '14:00' => '14:00 - 14:55',
                '14:55' => '14:55 - 15:50',
                '15:50' => '15:50 - 16:45',
                '17:15' => '17:15 - 18:10',
                '18:10' => '18:10 - 19:05',
                '19:05' => '19:05 - 20:00',
            ],
            'tuesday' => [
                '08:00' => '08:00 - 08:55',
                '08:55' => '08:55 - 09:50',
                '09:50' => '09:50 - 10:45',
                '11:15' => '11:15 - 12:10',
                '12:10' => '12:10 - 13:05',
                '13:05' => '13:05 - 14:00',
                '15:00' => '15:00 - 15:45',
                '15:45' => '15:45 - 16:30',
                '16:30' => '16:30 - 17:15',
                '17:15' => '17:15 - 17:45',
                '17:45' => '17:45 - 18:30',
                '18:30' => '18:30 - 19:15',
                '19:15' => '19:15 - 20:00',
            ]
        ];

       if ($date) {
            $isTuesday = Carbon::parse($date)->isTuesday();
            return $hourMap[$isTuesday ? 'tuesday' : 'normal'];
        }

       if ($time) {
            foreach (array_merge($hourMap['normal'], $hourMap['tuesday']) as $start => $range) {
                [$startHour, $endHour] = explode(' - ', $range);
                if ($time >= $startHour && $time <= $endHour) {
                    return $start;
                }
            }
        }

        return null;
    }



    public function refreshRecords(): void
    {
        $this->dispatch('filament-fullcalendar--refresh');
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
