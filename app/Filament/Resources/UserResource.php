<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('role_id')
                    ->label('Rol')
                    ->required()
                    ->relationship('role', 'name'),

                TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->required(fn(string $context): bool => $context === 'create')
                    ->label('Contraseña (dejar vacío para no cambiar)'),

                Toggle::make('estado')
                    ->label('Activo')
                    ->default(true)
                    ->disabled(fn($record) => $record && $record->email === 'admin@gmail.com'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role.name')
                    ->label('Rol')
                    ->sortable(),

                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        true => 'Activos',
                        false => 'Inactivos',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->visible(function ($record) {
                        $loggedUser = Auth::user();
                        $adminRoleId = Role::where('name', 'Admin')->value('id');
                        $propietarioRoleId = Role::where('name', 'Propietario')->value('id');

                        // No eliminar admin principal
                        if ($record->email === 'admin@gmail.com') {
                            return false;
                        }

                        // Si el logueado es Propietario y el target también es Propietario → no puede eliminarlo
                        if (
                            $loggedUser->role_id === $propietarioRoleId &&
                            $record->role_id === $propietarioRoleId
                        ) {
                            return false;
                        }

                        return true;
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn($records) => !collect($records)->contains(fn($record) => $record->email === 'admin@gmail.com')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        $adminRoleId = Role::where('name', 'Admin')->value('id');
        $propietarioRoleId = Role::where('name', 'Propietario')->value('id');

        // Si el usuario logueado es Propietario → no mostrar usuarios con rol Admin
        if ($user->role_id === $propietarioRoleId) {
            $query->where('role_id', '!=', $adminRoleId);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        $adminRoleId = Role::where('name', 'Admin')->value('id');
        $propietarioRoleId = Role::where('name', 'Propietario')->value('id');

        return $user->role_id === $adminRoleId || $user->role_id === $propietarioRoleId;
    }
}
