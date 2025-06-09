<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Editar Usuario';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->action(function () {
                    $data = $this->form->getState();

                    if (!empty($data['password'])) {
                        // Si el campo password tiene valor → lo hasheamos
                        $data['password'] = Hash::make($data['password']);
                    } else {
                        // Si el campo password viene vacío → lo quitamos del update
                        unset($data['password']);
                    }

                    $this->record->update($data);

                    Notification::make()
                        ->title('Usuario actualizado correctamente')
                        ->success()
                        ->send();

                    $this->redirect(UserResource::getUrl('index'));
                }),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url(UserResource::getUrl('index')),
        ];
    }
}
