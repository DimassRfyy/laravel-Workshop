<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkshopInstructorResource\Pages;
use App\Filament\Resources\WorkshopInstructorResource\RelationManagers;
use App\Models\WorkshopInstructor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkshopInstructorResource extends Resource
{
    protected static ?string $model = WorkshopInstructor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Workshop Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required()
                ->maxLength(255),

                TextInput::make('occupation')
                ->required()
                ->maxLength(255),

                FileUpload::make('avatar')
                ->image()
                ->disk('public')
                ->directory('workshopInstructorAvatar')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('occupation'),
                ImageColumn::make('avatar')
                ->circular(),
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
            'index' => Pages\ListWorkshopInstructors::route('/'),
            'create' => Pages\CreateWorkshopInstructor::route('/create'),
            'edit' => Pages\EditWorkshopInstructor::route('/{record}/edit'),
        ];
    }
}
