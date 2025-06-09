<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Nuevo Usuario';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->action(function () {
                    $data = $this->form->getState();

                    // Si se llenó el campo password, lo hasheamos
                    if (!empty($data['password'])) {
                        $data['password'] = Hash::make($data['password']);
                    }

                    $this->record = User::create($data);

                    Notification::make()
                        ->title('Usuario creado correctamente')
                        ->success()
                        ->send();

                    $this->redirect(UserResource::getUrl('index'));
                }),

            Action::make('save_create_another')
                ->label('Guardar y crear otro')
                ->action(function () {
                    $data = $this->form->getState();

                    if (!empty($data['password'])) {
                        $data['password'] = Hash::make($data['password']);
                    }

                    User::create($data);

                    Notification::make()
                        ->title('Usuario creado, puedes registrar otro')
                        ->success()
                        ->send();

                    $this->fillForm(); // limpiar el formulario
                }),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url(UserResource::getUrl('index')),
        ];
    }
}
