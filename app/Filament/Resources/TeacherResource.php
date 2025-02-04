<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TeacherImporter;
use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\ImportAction;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\CreateAction;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Profesores';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('Create Teacher')
                ->label('Nuevo Profesor'),
            CreateAction::make()
                ->label('Subir Profesores en .csv')
                ->importer(TeacherImporter::class),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('alias')
                    ->label('Alias')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Department Name')
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->default(fn() => Hash::make('password'))
                    ->hiddenOn('edit')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state)),

                Forms\Components\Toggle::make('default_password')
                    ->label('Default Password')
                    ->default(true)
                    ->hiddenOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alias')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label('Profesores en .csv')
                    ->importer(TeacherImporter::class)
            ]);
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
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
