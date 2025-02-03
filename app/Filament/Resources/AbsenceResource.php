<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsenceResource\Pages;
use App\Filament\Resources\AbsenceResource\RelationManagers;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use App\Filament\Imports\ProductImporter;
use App\Filament\Widgets\CalendarWidget;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Actions\ImportAction;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

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
                    ->required(),

                Select::make('hour')
                    ->label('Hour')
                    ->options(fn($get) => self::getHourOptions($get('date')))
                    ->required(),

                Textarea::make('comment')
                    ->label('Reason')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Profesor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hour')
                    ->label('Falta')
                    ->sortable()
                    ->formatStateUsing(fn($state) => self::formatHour($state)),
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
        $isTuesdayEvening = Carbon::parse($date)->isTuesday();
        return $isTuesdayEvening ? [
            1 => '15:00 - 15:45',
            2 => '15:45 - 16:30',
            3 => '16:30 - 17:15',
            4 => '17:45 - 18:30',
            5 => '18:30 - 19:15',
            6 => '19:15 - 20:00',
        ] : [
            1 => '8:00 - 8:55',
            2 => '8:55 - 9:50',
            3 => '9:50 - 10:45',
            4 => '11:15 - 12:10',
            5 => '12:10 - 13:05',
            6 => '13:05 - 14:00',
            7 => '14:00 - 14:55',
            8 => '14:55 - 15:50',
            9 => '15:50 - 16:45',
            10 => '17:15 - 18:10',
            11 => '18:10 - 19:05',
            12 => '19:05 - 20:00',
        ];
    }

    private static function formatHour($hour)
    {
        return self::getHourOptions(null)[$hour] ?? 'Unknown';
    }

    private static function preventLateDeletion(Absence $absence)
    {
        if ($absence->created_at->diffInMinutes(now()) > 10) {
            throw new \Exception('La ausencia no se puede borrar pasados 10 minutos.');
        }
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
