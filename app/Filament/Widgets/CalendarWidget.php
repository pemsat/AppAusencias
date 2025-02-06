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
use Filament\Forms\Get;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Absence::class;

    public function getFormSchema(): array
    {
        return [
            Select::make('user_id')
                ->label('Profesor')
                ->relationship('user', 'name')
                ->required(),

            TextInput::make('comment')
                ->label('Motivo')
                ->required(),

            DatePicker::make('starts_at')
                ->label('Fecha de ausencia')
                ->required()
                ->reactive()
                ->live()
                ->readOnly(fn(Get $get) => !empty($get('starts_at'))),

            Select::make('ends_at')
                ->label('Hora de ausencia')
                ->options(fn(Get $get) => $this->getHourSlotFromTime(null, $get('starts_at')) ?? [])
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
            'hiddenDays' => [0, 6],
        ];
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(
                    fn(Form $form, array $arguments) =>
                    $form->fill([
                        'user_id' => null,
                        'starts_at' => $arguments['starts_at'] ?? now()->toDateString(), // Pre-fill with selected date
                        'ends_at' => null,
                    ])
                )
                ->action(function (array $data) {

                    $startsAt = Carbon::parse($data['starts_at'] . ' ' . $data['ends_at']);

                    Absence::create([
                        'user_id' => $data['user_id'],
                        'starts_at' => $startsAt,
                        'ends_at' => $startsAt->copy()->addMinutes(55),
                        'comment' => $data['comment'],
                    ]);

                    $this->refreshRecords();
                }),
        ];
    }


    public function fetchEvents(array $fetchInfo): array
    {
        return Absence::query()
            ->whereBetween('starts_at', [$fetchInfo['start'], $fetchInfo['end']])
            ->with('user')
            ->get()
            ->map(
                fn(Absence $absence) => EventData::make()
                    ->id($absence->id)
                    ->title($absence->user->name . ' - ' . Carbon::parse($absence->starts_at)->format('H:i') . ' - ' . $absence->comment)
                    ->start($absence->starts_at->toIso8601String())
                    ->end($absence->ends_at->toIso8601String())
                    ->extendedProps([
                        'userName' => $absence->user->name,
                        'hour' => Carbon::parse($absence->starts_at)->format('H:i') . ' - ' . Carbon::parse($absence->ends_at)->format('H:i'),
                        'reason' => $absence->comment,
                    ])
            )->toArray();
    }

    public function onDateClick(array $info): void
    {
        $date = $info['dateStr'];
        $hour = $this->getHourSlotFromTime($date);
        $absenceId = Absence::whereDate('starts_at', $date)
            ->whereTime('starts_at', '>=', $hour)
            ->value('id');

        $this->dispatchBrowserEvent('open-modal', [
            'absenceId' => $absenceId,
            'date' => $date,
            'hour' => $hour,
        ]);
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {

        $selectedDate = Carbon::parse($start)->toDateString();

        $this->mountAction('create', [
            'starts_at' => $selectedDate,
        ]);
    }

    private function getHourSlotFromTime($time = null, $date = null)
    {
        $hourMap = [
            'normal' => [
                '08:00' => '08:00 - 08:55',
                '08:55' => '08:55 - 09:50',
                '09:50' => '09:50 - 10:45',
                '10:45' => '10:45 - 11:15',
                '11:15' => '11:15 - 12:10',
                '12:10' => '12:10 - 13:05',
                '13:05' => '13:05 - 14:00',
                '14:00' => '14:00 - 14:55',
                '14:55' => '14:55 - 15:50',
                '15:50' => '15:50 - 16:45',
                '16:45' => '16:45 - 17:15',
                '17:15' => '17:15 - 18:10',
                '18:10' => '18:10 - 19:05',
                '19:05' => '19:05 - 20:00',
            ],
            'tuesday' => [
                '08:00' => '08:00 - 08:55',
                '08:55' => '08:55 - 09:50',
                '09:50' => '09:50 - 10:45',
                '10:45' => '10:45 - 11:15',
                '11:15' => '11:15 - 12:10',
                '12:10' => '12:10 - 13:05',
                '13:05' => '13:05 - 14:00',
                '15:00' => '15:00 - 15:45',
                '15:45' => '15:45 - 16:30',
                '16:30' => '16:30 - 17:15',
                '17:15' => '15:15 - 17:45',
                '17:45' => '17:45 - 18:30',
                '18:30' => '18:30 - 19:15',
                '19:15' => '19:15 - 20:00',
            ]
        ];

        // If getting options for Select field
        if ($date) {
            $isTuesday = Carbon::parse($date)->isTuesday();
            return $hourMap[$isTuesday ? 'tuesday' : 'normal'];
        }

        // If looking for a specific hour slot
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
            const tooltipContent = 'Profesor: ' + event.extendedProps.userName + 'Hora: ' + event.extendedProps.hour + 'Motivo: ' + event.extendedProps.reason;
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '" + tooltipContent + "' }");

            el.addEventListener('click', () => {
                Livewire.emit('editAbsence', event.id, event.hour);
            });
        }
        JS;
    }
}
