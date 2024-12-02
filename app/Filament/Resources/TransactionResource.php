<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->title;
    }

    // public static function getWidgets(): array
    // {
    //     return [
    //         // Total Transactions
    //         Card::make('Total Transactions', Transaction::count()),
    //         // Total Income
    //         Card::make('Total Income', 'Rp ' . number_format(Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount'), 0, ',', '.')),
    //         // Total Expense
    //         Card::make('Total Expense', 'Rp ' . number_format(Transaction::whereHas('category', fn($query) => $query->where('type', 'expense'))->sum('amount'), 0, ',', '.')),
    //         // Net Balance (Income - Expense)
    //         Card::make('Net Balance', 'Rp ' . number_format(
    //             Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount')
    //                 - Transaction::whereHas('category', fn($query) => $query->where('type', 'expense'))->sum('amount'),
    //             0, ',', '.'
    //         )),
    //     ];
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('desc'),
                        Select::make('type')
                            ->options([
                                'expense' => 'Expense',
                                'income' => 'Income'
                            ]),
                    ]),
                TextInput::make('amount')
                    ->numeric()
                    ->required(),
                FileUpload::make('image')
                    ->directory('public/transaction/img')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ImageColumn::make('category.image')
                //     ->label('Type'),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('title'),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('category.type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger'
                    })
                    ->sortable(),
                // TextColumn::make('total_expense')
                //     ->label('Total Expense')
                //     ->getStateUsing(fn() => Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount'))
                //     ->money('IDR')
                //     ->alignRight(),
                // TextColumn::make('total_expense')
                //     ->label('Total Expense')
                //     ->getStateUsing(fn() => Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount'))
                //     ->money('IDR')
                //     ->alignRight(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->summarize(Sum::make()->money('IDR')),
                // ->summarize(Sum::make()->query(fn(Builder $query) => $query->whereRelation('category', 'type', 'income'))),
                // ImageColumn::make('image')
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date_start'),
                        DatePicker::make('date_end')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_start'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_start'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
            ])
            ->groups([
                Tables\Grouping\Group::make('category.type')
                    ->collapsible(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
