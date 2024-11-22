<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkshopResource\Pages;
use App\Filament\Resources\WorkshopResource\RelationManagers;
use App\Models\Workshop;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Workshop Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make('Details')
                ->schema([
                    TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                    Textarea::make('address')
                    ->rows(3)
                    ->required()
                    ->maxLength(255),

                    FileUpload::make('thumbnail')
                    ->maxSize(5120)
                    ->image()
                    ->disk('public')
                    ->directory('workshopThumbnail')
                    ->required(),

                    FileUpload::make('venue_thumbnail')
                    ->maxSize(5120)
                    ->image()
                    ->disk('public')
                    ->directory('workshopVenueThumbnail')
                    ->required(),

                    FileUpload::make('bg_map')
                    ->maxSize(5120)
                    ->image()
                    ->disk('public')
                    ->directory('workshopBgMap')
                    ->required(),

                    Repeater::make('benefits')
                    ->relationship('benefits')
                    ->schema([
                        TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                    ])
                ]),

                Fieldset::make('Additional')
                ->schema([
                    Textarea::make('about')
                    ->required(),

                    TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR'),

                    Select::make('is_open')
                    ->options([
                        true => 'Open',
                        false => 'Not Available'
                    ])
                    ->required(),

                    Select::make('has_started')
                    ->options([
                        true => 'Started',
                        false => 'Not Started Yet'
                    ])
                    ->required(),

                    Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),


                    Select::make('workshop_instructor_id')
                    ->relationship('instructor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                    DatePicker::make('started_at')
                    ->required(),

                    TimePicker::make('time_at')
                    ->required()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail'),
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('category.name'),
                TextColumn::make('instructor.name'),
                IconColumn::make('has_started')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Started'),
                TextColumn::make('participants_count')
                ->label('Participants')
                ->counts('participants'),

            ])
            ->filters([
                SelectFilter::make('category_id')
                ->label('category')
                ->relationship('category', 'name'),

                SelectFilter::make('workshop_instructor_id')
                ->label('workshop_instructor')
                ->relationship('instructor', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->label('Actions')
                ->size(ActionSize::Small)
                ->color('primary')
                ->button()
                ->icon('heroicon-m-bars-3')
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
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }
}
