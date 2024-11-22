<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingTransactionResource\Pages;
use App\Filament\Resources\BookingTransactionResource\RelationManagers;
use App\Models\BookingTransaction;
use App\Models\Workshop;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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

class BookingTransactionResource extends Resource
{
    protected static ?string $model = BookingTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
{
    return static::$model::where('is_paid', false)->count();
}

    protected static ?string $navigationGroup = 'Transaction & Participant';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Product and Price')
                        ->schema([
                            Select::make('workshop_id')
                                ->relationship('workshop', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $workshop = Workshop::find($state);
                                    $set('price', $workshop ? $workshop->price : 0);
                                })
                                ->afterStateHydrated(function ($state, callable $get, callable $set) {
                                    $workshop = Workshop::find($state);
                                    $set('price', $workshop ? $workshop->price : 0);
                                }),

                            TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->prefix('Qty People')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $price = $get('price');
                                    $subTotal = $price * $state;
                                    $totalPpn = $subTotal * 0.11;
                                    $totalAmount = $subTotal + $totalPpn;

                                    $set('total_amount', $totalAmount);

                                    $participants = $get('partisipants') ?? [];
                                    $currentCount = count($participants);

                                    if ($state > $currentCount) {
                                        for ($i = $currentCount; $i < $state; $i++) {
                                            $participants[] = ['name' => '', 'occupation' => '', 'email' => ''];
                                        }
                                    } else {
                                        $participants = array_slice($participants, 0, $state);
                                    }

                                    $set('participants', $participants);
                                })
                                ->afterStateHydrated(function ($state, callable $get, callable $set) {
                                    $price = $get('price');
                                    $subTotal = $price * $state;
                                    $totalPpn = $subTotal * 0.11;
                                    $totalAmount = $subTotal + $totalPpn;

                                    $set('total_amount', $totalAmount);
                                }),

                            TextInput::make('total_amount')
                                ->required()
                                ->numeric()
                                ->prefix('IDR')
                                ->readOnly()
                                ->helperText('Harga udah include PPN 11%'),

                            Repeater::make('participants')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Participant Name')
                                                ->required(),

                                            TextInput::make('occupation')
                                                ->label('Occupation')
                                                ->required(),

                                            TextInput::make('email')
                                                ->label('Email')
                                                ->required(),
                                        ])
                                ])
                                ->columns(1)
                                ->label('Participant Details')
                        ]),

                    Step::make('Customer Information')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('customer_bank_name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('customer_bank_account')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('customer_bank_number')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('booking_trx_id')
                                ->required()
                                ->maxLength(255),
                        ]),

                    Step::make('Payment Information')
                        ->schema([
                            ToggleButtons::make('is_paid')
                                ->label('Apakah sudah membayar?')
                                ->boolean()
                                ->grouped()
                                ->icons([
                                    true => 'heroicon-o-pencil',
                                    false => 'heroicon-o-clock'
                                ])
                                ->required(),

                            FileUpload::make('proof')
                                ->image()
                                ->required(),
                        ])

                ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('workshop.thumbnail'),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('booking_trx_id')
                    ->searchable(),
                
                TextColumn::make('total_amount')
                    ->formatStateUsing(function ($state) {
                        return 'Rp. ' . number_format($state, 0, ',', '.');
                    }),

                IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Terverifikasi'),
            ])
            ->filters([
                SelectFilter::make('workshop_id')
                    ->label('workshop')
                    ->relationship('workshop', 'name')
            ])
            ->actions([
                ActionGroup::make([
                    ActionGroup::make([
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                    ])
                        ->dropdown(false),
                        Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->action(function (BookingTransaction $record) {
                            $record->is_paid = true;
                            $record->save();
    
                            Notification::make()
                                ->title('Order Approved')
                                ->success()
                                ->body('The order has been successfully approved.')
                                ->send();
                        })
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(BookingTransaction $record) => !$record->is_paid),
                    ])
                    ->icon('heroicon-m-bars-3')
                        ->label('Actions')
                        ->size(ActionSize::Small)
                        ->color('primary')
                        ->button()
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
            'index' => Pages\ListBookingTransactions::route('/'),
            'create' => Pages\CreateBookingTransaction::route('/create'),
            'edit' => Pages\EditBookingTransaction::route('/{record}/edit'),
        ];
    }
}
