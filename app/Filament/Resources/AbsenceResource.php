<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsenceResource\Pages;
use App\Models\Absence;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Actions\ImportAction;
use App\Filament\Widgets\CalendarWidget;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Ausencias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'name')
                    ->required(),

                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->reactive(), // Triggers update when date changes

                Select::make('hour')
                    ->label('Hour')
                    ->options(fn($get) => self::getHourOptions($get('date')))
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $date = Carbon::parse($get('date'));
                        $times = self::getHourTimeRange($state, $date);
                        $set('starts_at', $times['starts_at']);
                        $set('ends_at', $times['ends_at']);
                    }),

                Textarea::make('comment')
                    ->label('Reason')
                    ->required(),

                Forms\Components\Hidden::make('starts_at'),
                Forms\Components\Hidden::make('ends_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Profesor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Motivo')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('teacher_id')
                    ->label('Filtrar por profesor')
                    ->options(Teacher::all()->pluck('name', 'id'))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn(Absence $absence) => self::preventLateDeletion($absence)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public static function getHourOptions($date)
    {
        if (!$date) return [];

        $isTuesday = Carbon::parse($date)->isTuesday();

        return $isTuesday ? [
            1 => '08:00 - 08:55',
            2 => '08:55 - 09:50',
            3 => '09:50 - 10:45',
            4 => '10:45 - 11:15',
            5 => '11:15 - 12:10',
            6 => '12:10 - 13:05',
            7 => '13:05 - 14:00',
            8 => '15:00 - 15:45',
            9 => '15:45 - 16:30',
            10 => '16:30 - 17:15',
            11 => '17:15 - 17:45',
            12 => '17:45 - 18:30',
            13 => '18:30 - 19:15',
            14 => '19:15 - 20:00',
        ] : [
            1 => '08:00 - 08:55',
            2 => '08:55 - 09:50',
            3 => '09:50 - 10:45',
            4 => '10:45 - 11:15',
            5 => '11:15 - 12:10',
            6 => '12:10 - 13:05',
            7 => '13:05 - 14:00',
            8 => '14:00 - 14:55',
            9 => '14:55 - 15:50',
            10 => '15:50 - 16:45',
            11 => '16:45 - 17:15',
            12 => '17:15 - 18:10',
            13 => '18:10 - 19:05',
            14 => '19:05 - 20:00',
        ];
    }


    public static function getHourTimeRange($hour, $date)
    {
        $times = [
            1 => ['start' => '08:00', 'end' => '08:55'],
            2 => ['start' => '08:55', 'end' => '09:50'],
            3 => ['start' => '09:50', 'end' => '10:45'],
            4 => ['start' => '11:15', 'end' => '12:10'],
            5 => ['start' => '12:10', 'end' => '13:05'],
            6 => ['start' => '13:05', 'end' => '14:00'],
            7 => ['start' => '14:00', 'end' => '14:55'],
            8 => ['start' => '14:55', 'end' => '15:50'],
            9 => ['start' => '15:50', 'end' => '16:45'],
            10 => ['start' => '17:15', 'end' => '18:10'],
            11 => ['start' => '18:10', 'end' => '19:05'],
            12 => ['start' => '19:05', 'end' => '20:00'],
        ];

        if ($date->isTuesday()) {
            $times = [
                1 => ['start' => '15:00', 'end' => '15:45'],
                2 => ['start' => '15:45', 'end' => '16:30'],
                3 => ['start' => '16:30', 'end' => '17:15'],
                4 => ['start' => '17:45', 'end' => '18:30'],
                5 => ['start' => '18:30', 'end' => '19:15'],
                6 => ['start' => '19:15', 'end' => '20:00'],
            ];
        }

        return [
            'starts_at' => Carbon::parse($date->format('Y-m-d') . ' ' . $times[$hour]['start']),
            'ends_at' => Carbon::parse($date->format('Y-m-d') . ' ' . $times[$hour]['end']),
        ];
    }

    private static function preventLateDeletion(Absence $absence)
    {
        if ($absence->created_at->diffInMinutes(now()) > 10) {
            throw new \Exception('La ausencia no se puede borrar pasados 10 minutos.');
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
        ];
    }
}
