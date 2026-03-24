<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('jobTitle.name')
                    ->label('Job Title')
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
                TextColumn::make('employment_type')
                    ->label('Employment Type')
                    ->badge(),
                TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('salary')
                    ->money('USD')
                    ->sortable(),
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
                SelectFilter::make('job_title_id')
                    ->label('Job Title')
                    ->relationship('jobTitle', 'name'),
                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options(\App\Enums\EmploymentTypeEnum::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                   EditAction::make()
                        ->visible(fn(Employee $record): bool => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->visible(fn(Employee $record): bool => Gate::allows('delete', $record)),
                ])
                    ->button()
                    ->color('gray')
                    ->label('Actions')
                    ->icon(Heroicon::ChevronDown)
                    ->iconPosition(IconPosition::After)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn(): bool => Gate::allows('deleteAny', Employee::class)),
                    RestoreBulkAction::make()
                        ->visible(fn(): bool => Gate::allows('restoreAny', Employee::class)),
                    ForceDeleteBulkAction::make()
                        ->visible(fn(): bool => Gate::allows('forceDeleteAny', Employee::class)),
                ]),
            ]);
    }
}
