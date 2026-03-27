<?php

namespace App\Filament\Resources\ClockIns\Tables;

use App\Enums\RolesEnum;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ClockInsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->visible($user->hasRole(RolesEnum::Admin->value)),
                TextColumn::make('started_date')
                    ->label('Date')
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Clock In')
                    ->dateTime()
                    ->formatStateUsing(function ($state) use ($user): string {
                        return $state instanceof Carbon
                            ? $state->setTimezone($user->timezone)->format('h:i A')
                            : '';
                    }),
                TextColumn::make('note')
                    ->label('Clock In Note')
                    ->searchable(),
                TextColumn::make('clockOut.ended_at')
                    ->label('Clock Out')
                    ->formatStateUsing(function ($state) use ($user): string {
                        return $state instanceof Carbon
                            ? $state->setTimezone($user->timezone)->format('h:i A')
                            : '';
                    }),
                TextColumn::make('clockOut.note')
                    ->label('Clock Out Note')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->timezone(config('app.timezone'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->timezone(config('app.timezone'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->button()
                    ->color('gray')
                    ->label('Actions')
                    ->icon(Heroicon::EllipsisVertical)
                    ->iconPosition(IconPosition::Before)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
