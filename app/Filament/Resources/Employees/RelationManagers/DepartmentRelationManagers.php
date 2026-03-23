<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DissociateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DepartmentRelationManagers extends RelationManager
{
    protected static string $relationship = 'department';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                   Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')->label('Department Name')
            ])
            ->recordActions([
                DissociateAction::make(),
                DeleteAction::make(),    
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->searchable(false)
            ->paginated(false);
    }
}
