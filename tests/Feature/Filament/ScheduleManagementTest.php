<?php

use App\Filament\Admin\Resources\Doctors\Pages\ManageDoctorAvailability;
use App\Filament\Admin\Resources\Doctors\Pages\ManageDoctorBlocked;
use App\Models\Doctor;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use Filament\Facades\Filament;
use Zap\Enums\ScheduleTypes;
use Zap\Models\Schedule;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

// ═══════════════════════════════════════════════════════════════════════════════
// ScheduleRepository – Availability
// ═══════════════════════════════════════════════════════════════════════════════

it('creates an availability rule via repository', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createAvailability(
        $doctor,
        'Regular Hours',
        [1, 2, 3, 4, 5],       // Mon–Fri
        '09:00',
        '17:00',
        today()->toDateString(),
        null,
        'Main working hours',
        true
    );

    expect($schedule)->toBeInstanceOf(Schedule::class);
    expect($schedule->schedule_type)->toBe(ScheduleTypes::AVAILABILITY);
    expect($schedule->name)->toBe('Regular Hours');
    expect($schedule->is_active)->toBeTrue();
    expect($schedule->periods()->count())->toBeGreaterThan(0);
});

it('returns availability form data for pre-filling edit form', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createAvailability(
        $doctor,
        'Morning Shift',
        [1, 3, 5],  // Mon, Wed, Fri
        '08:00',
        '12:00',
        today()->toDateString(),
    );

    $data = $repo->availabilityFormData($schedule);

    expect($data)->toHaveKeys(['name', 'days_of_week', 'start_time', 'end_time', 'effective_from', 'is_active']);
    expect($data['name'])->toBe('Morning Shift');
    expect($data['start_time'])->toBe('08:00');
    expect($data['end_time'])->toBe('12:00');
    expect($data['days_of_week'])->toContain(1);
});

it('updates an availability rule via repository', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $original = $repo->createAvailability(
        $doctor,
        'Old Name',
        [1, 2],
        '09:00',
        '17:00',
        today()->toDateString()
    );

    $updated = $repo->updateAvailability(
        $original,
        'New Name',
        [3, 4, 5],
        '10:00',
        '18:00',
        today()->toDateString()
    );

    expect($updated->name)->toBe('New Name');
    expect($updated->schedule_type)->toBe(ScheduleTypes::AVAILABILITY);

    // Old record deleted
    expect(Schedule::find($original->id))->toBeNull();
});

it('deletes a schedule via repository', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $schedule = $repo->createAvailability(
        $doctor,
        'To Delete',
        [1],
        '09:00',
        '17:00',
        today()->toDateString()
    );

    $id = $schedule->id;
    $repo->delete($schedule);

    expect(Schedule::find($id))->toBeNull();
});

// ═══════════════════════════════════════════════════════════════════════════════
// ScheduleRepository – Blocked
// ═══════════════════════════════════════════════════════════════════════════════

it('creates a blocked time via repository', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'Holiday',
        today()->toDateString(),
        today()->addDays(3)->toDateString(),
        null,
        null,
        'National holiday',
        true
    );

    expect($schedule->schedule_type)->toBe(ScheduleTypes::BLOCKED);
    expect($schedule->name)->toBe('Holiday');
    expect($schedule->periods()->count())->toBeGreaterThan(0);
});

it('creates a blocked time with specific hours', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'Conference',
        today()->toDateString(),
        today()->toDateString(),
        '14:00',
        '16:00',
    );

    $period = $schedule->periods()->first();
    expect($period->start_time)->toBe('14:00');
    expect($period->end_time)->toBe('16:00');
});

it('returns blocked form data for pre-filling edit form', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'Meeting',
        today()->toDateString(),
        today()->toDateString(),
        '10:00',
        '11:00',
    );

    $data = $repo->blockedFormData($schedule);

    expect($data)->toHaveKeys(['reason', 'start_date', 'end_date', 'has_time_restriction']);
    expect($data['reason'])->toBe('Meeting');
    expect($data['has_time_restriction'])->toBeTrue();
    expect($data['block_start_time'])->toBe('10:00');
});

it('returns all-day blocked form data correctly', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'Day Off',
        today()->toDateString(),
        today()->toDateString()
    );

    $data = $repo->blockedFormData($schedule);
    expect($data['has_time_restriction'])->toBeFalse();
});

it('updates a blocked schedule via repository', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $original = $repo->createBlocked(
        $doctor,
        'Old Reason',
        today()->toDateString(),
        today()->addDay()->toDateString()
    );

    $updated = $repo->updateBlocked(
        $original,
        'New Reason',
        today()->toDateString(),
        today()->addDays(5)->toDateString()
    );

    expect($updated->name)->toBe('New Reason');
    expect(Schedule::find($original->id))->toBeNull();
});

// ═══════════════════════════════════════════════════════════════════════════════
// ManageDoctorAvailability – Filament page
// ═══════════════════════════════════════════════════════════════════════════════

it('can render the manage doctor availability page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ManageDoctorAvailability::class, ['record' => $doctor->id])
        ->assertOk();
});

it('availability table shows existing rules', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $repo->createAvailability(
        $doctor,
        'Weekly Hours',
        [1, 2, 3],
        '09:00',
        '17:00',
        today()->toDateString()
    );

    livewire(ManageDoctorAvailability::class, ['record' => $doctor->id])
        ->assertOk()
        ->assertSee('Weekly Hours');
});

it('can create an availability rule from the page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ManageDoctorAvailability::class, ['record' => $doctor->id])
        ->callTableAction('create', data: [
            'name'           => 'Test Hours',
            'days_of_week'   => [1, 2, 3, 4, 5],
            'start_time'     => '09:00',
            'end_time'       => '17:00',
            'effective_from' => today()->toDateString(),
            'is_active'      => true,
        ])
        ->assertHasNoTableActionErrors();

    expect(
        $doctor->availabilitySchedules()->where('name', 'Test Hours')->exists()
    )->toBeTrue();
});

it('can edit an availability rule from the page', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $schedule = $repo->createAvailability(
        $doctor,
        'Original',
        [1],
        '09:00',
        '17:00',
        today()->toDateString()
    );

    livewire(ManageDoctorAvailability::class, ['record' => $doctor->id])
        ->callTableAction('edit', record: $schedule, data: [
            'name'           => 'Updated Hours',
            'days_of_week'   => [1, 3, 5],
            'start_time'     => '10:00',
            'end_time'       => '16:00',
            'effective_from' => today()->toDateString(),
            'is_active'      => true,
        ])
        ->assertHasNoTableActionErrors();

    expect(
        $doctor->availabilitySchedules()->where('name', 'Updated Hours')->exists()
    )->toBeTrue();
});

it('can delete an availability rule from the page', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $schedule = $repo->createAvailability(
        $doctor,
        'To Delete',
        [1],
        '09:00',
        '17:00',
        today()->toDateString()
    );

    livewire(ManageDoctorAvailability::class, ['record' => $doctor->id])
        ->callTableAction('delete', record: $schedule)
        ->assertHasNoTableActionErrors();

    expect(Schedule::find($schedule->id))->toBeNull();
});

// ═══════════════════════════════════════════════════════════════════════════════
// ManageDoctorBlocked – Filament page
// ═══════════════════════════════════════════════════════════════════════════════

it('can render the manage doctor blocked page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->assertOk();
});

it('blocked table shows existing blocked times', function () {
    $doctor = Doctor::factory()->create();
    $repo   = app(ScheduleRepository::class);

    $repo->createBlocked(
        $doctor,
        'Annual Leave',
        today()->toDateString(),
        today()->addWeek()->toDateString()
    );

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->assertOk()
        ->assertSee('Annual Leave');
});

it('can create a blocked time from the page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->callTableAction('create', data: [
            'reason'               => 'Holiday',
            'start_date'           => today()->toDateString(),
            'end_date'             => today()->addDays(2)->toDateString(),
            'has_time_restriction' => false,
            'is_active'            => true,
        ])
        ->assertHasNoTableActionErrors();

    expect(
        $doctor->blockedSchedules()->where('name', 'Holiday')->exists()
    )->toBeTrue();
});

it('can create a blocked time with specific hours from the page', function () {
    $doctor = Doctor::factory()->create();

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->callTableAction('create', data: [
            'reason'               => 'Conference',
            'start_date'           => today()->toDateString(),
            'end_date'             => today()->toDateString(),
            'has_time_restriction' => true,
            'block_start_time'     => '14:00',
            'block_end_time'       => '16:00',
            'is_active'            => true,
        ])
        ->assertHasNoTableActionErrors();

    $schedule = $doctor->blockedSchedules()->where('name', 'Conference')->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->periods()->first()->start_time)->toBe('14:00');
});

it('can edit a blocked time from the page', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'Old Reason',
        today()->toDateString(),
        today()->addDay()->toDateString()
    );

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->callTableAction('edit', record: $schedule, data: [
            'reason'               => 'New Reason',
            'start_date'           => today()->toDateString(),
            'end_date'             => today()->addDays(3)->toDateString(),
            'has_time_restriction' => false,
            'is_active'            => true,
        ])
        ->assertHasNoTableActionErrors();

    expect(
        $doctor->blockedSchedules()->where('name', 'New Reason')->exists()
    )->toBeTrue();
});

it('can delete a blocked time from the page', function () {
    $doctor   = Doctor::factory()->create();
    $repo     = app(ScheduleRepository::class);

    $schedule = $repo->createBlocked(
        $doctor,
        'To Delete',
        today()->toDateString(),
        today()->addDay()->toDateString()
    );

    livewire(ManageDoctorBlocked::class, ['record' => $doctor->id])
        ->callTableAction('delete', record: $schedule)
        ->assertHasNoTableActionErrors();

    expect(Schedule::find($schedule->id))->toBeNull();
});
