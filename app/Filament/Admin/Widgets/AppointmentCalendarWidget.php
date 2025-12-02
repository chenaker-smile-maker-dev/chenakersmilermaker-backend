<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

#[\Filament\Widgets\Concerns\InteractsWithPageTable\Title('Appointment Calendar')]
class AppointmentCalendarWidget extends Widget implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected string $view = 'panels.widgets.appointment-calendar-wrapper';

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Appointment Calendar';

    /**
     * Doctor color palette - colors selected for good contrast with both white and black text
     */
    private const DOCTOR_COLORS = [
        '#E74C3C', // Red - good contrast
        '#3498DB', // Blue - good contrast
        '#2ECC71', // Green - good contrast
        '#F39C12', // Orange - good contrast
        '#9B59B6', // Purple - good contrast
        '#1ABC9C', // Turquoise - good contrast
        '#E91E63', // Pink - good contrast
        '#00BCD4', // Cyan - good contrast
        '#FF5722', // Deep Orange - good contrast
        '#4CAF50', // Light Green - good contrast
    ];

    public string $currentView = 'dayGridMonth';

    public ?int $selectedAppointmentId = null;

    public string $modalMode = 'view'; // 'view' or 'edit'

    // Edit form data
    public ?string $editFromDate = null;
    public ?string $editToDate = null;
    public ?int $editDoctorId = null;
    public ?int $editServiceId = null;
    public ?int $editPatientId = null;
    public ?int $editPrice = null;
    public ?string $editStatus = null;

    public function changeView(string $view): void
    {
        $this->currentView = $view;
    }

    #[On('appointmentClicked')]
    public function handleAppointmentClick(int $appointmentId): void
    {
        $this->openAppointmentModal($appointmentId, 'view');
    }

    #[On('switchToEdit')]
    public function switchToEditMode(int $appointmentId): void
    {
        $this->openAppointmentModal($appointmentId, 'edit');
    }

    public function openAppointmentModal(int $appointmentId, string $mode = 'view'): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $this->selectedAppointmentId = $appointmentId;
        $this->modalMode = $mode;

        if ($mode === 'edit') {
            $this->editFromDate = $appointment->from->format('Y-m-d\TH:i');
            $this->editToDate = $appointment->to->format('Y-m-d\TH:i');
            $this->editDoctorId = $appointment->doctor_id;
            $this->editServiceId = $appointment->service_id;
            $this->editPatientId = $appointment->patient_id;
            $this->editPrice = $appointment->price;
            $this->editStatus = $appointment->status->value;
        }
    }

    public function closeAppointmentModal(): void
    {
        $this->selectedAppointmentId = null;
        $this->modalMode = 'view';
        $this->resetEditForm();
    }

    private function resetEditForm(): void
    {
        $this->editFromDate = null;
        $this->editToDate = null;
        $this->editDoctorId = null;
        $this->editServiceId = null;
        $this->editPatientId = null;
        $this->editPrice = null;
        $this->editStatus = null;
    }

    public function goToAppointment(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Redirect to the appointment resource view page
        redirect()->route('filament.admin.resources.appointments.view', ['record' => $appointment])->send();
    }

    public function saveAppointment(): void
    {
        if (!$this->selectedAppointmentId) {
            return;
        }

        $appointment = Appointment::findOrFail($this->selectedAppointmentId);

        try {
            $appointment->update([
                'from' => $this->editFromDate,
                'to' => $this->editToDate,
                'doctor_id' => $this->editDoctorId,
                'service_id' => $this->editServiceId,
                'patient_id' => $this->editPatientId,
                'price' => $this->editPrice,
                'status' => $this->editStatus,
            ]);

            Notification::make()
                ->success()
                ->title('Appointment Updated')
                ->send();

            $this->closeAppointmentModal();
            $this->dispatch('appointmentUpdated');
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getDoctorColors(): array
    {
        return self::DOCTOR_COLORS;
    }

    public function getSelectedAppointment(): ?Appointment
    {
        return $this->selectedAppointmentId ? Appointment::find($this->selectedAppointmentId) : null;
    }
}
