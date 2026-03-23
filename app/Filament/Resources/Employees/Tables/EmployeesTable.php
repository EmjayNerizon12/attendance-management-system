<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->sortable(['fname', 'lname'])
                    ->searchable(['fname', 'lname']),
                TextColumn::make('department.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('role')
                    ->searchable()
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ->icon(fn($state) => $state->getIcon()),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('lname', direction: 'asc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(\App\Enums\EmployeeRoleEnum::class),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn (Employee $record): bool => auth()->user()?->can('view', $record) ?? false),
                EditAction::make()
                    ->visible(fn (Employee $record): bool => auth()->user()?->can('update', $record) ?? false),
                DeleteAction::make()
                    ->visible(fn (Employee $record): bool => auth()->user()?->can('delete', $record) ?? false),
                RestoreAction::make()
                    ->visible(fn (Employee $record): bool => auth()->user()?->can('restore', $record) ?? false),
                ForceDeleteAction::make()
                    ->visible(fn (Employee $record): bool => auth()->user()?->can('forceDelete', $record) ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('deleteAny', Employee::class) ?? false),
                    RestoreBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('restoreAny', Employee::class) ?? false),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('forceDeleteAny', Employee::class) ?? false),
                ]),
            ]);
    }
}
