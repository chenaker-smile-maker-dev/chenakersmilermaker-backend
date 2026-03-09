<?php

namespace Database\Seeders;

use App\Enums\Appointment\AppointmentStatus;
use App\Enums\Appointment\ChangeRequestStatus;
use App\Enums\Patient\Gender;
use App\Enums\Service\ServiceAvailability;
use App\Enums\UrgentBookingStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Event;
use App\Models\Patient;
use App\Models\PatientNotification;
use App\Models\Review;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Training;
use App\Models\UrgentBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the demo database seeds.
     * Populates a realistic dental clinic demo environment.
     */
    public function run(): void
    {
        $this->call(CreateAdminUserSeeder::class);

        $services = $this->seedServices();
        $doctors  = $this->seedDoctors($services);
        $patients = $this->seedPatients();

        $this->seedAppointments($patients, $doctors, $services);
        $this->seedUrgentBookings($patients);
        $this->seedEvents();
        $trainings = $this->seedTrainings();
        $this->seedReviews($trainings, $patients);
        $this->seedTestimonials($patients);
        $this->seedPatientNotifications($patients);
    }

    // ─── Admin user ──────────────────────────────────────────────────────────

    private function seedAdminUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@clinic.dz'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
    }

    // ─── Services ────────────────────────────────────────────────────────────

    private function seedServices(): array
    {
        $data = [
            [
                'name' => ['en' => 'Dental Cleaning',          'ar' => 'تنظيف الأسنان',         'fr' => 'Détartrage dentaire'],
                'price' => 3000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 45,
            ],
            [
                'name' => ['en' => 'Teeth Whitening',          'ar' => 'تبييض الأسنان',          'fr' => 'Blanchiment dentaire'],
                'price' => 12000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 60,
            ],
            [
                'name' => ['en' => 'Dental Implant',           'ar' => 'زراعة الأسنان',           'fr' => 'Implant dentaire'],
                'price' => 45000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 90,
            ],
            [
                'name' => ['en' => 'Root Canal Treatment',     'ar' => 'علاج قناة الجذر',         'fr' => 'Traitement de canal'],
                'price' => 18000, 'availability' => ServiceAvailability::BOTH, 'active' => true, 'duration' => 75,
            ],
            [
                'name' => ['en' => 'Tooth Extraction',         'ar' => 'خلع الأسنان',             'fr' => 'Extraction dentaire'],
                'price' => 4000, 'availability' => ServiceAvailability::BOTH, 'active' => true, 'duration' => 30,
            ],
            [
                'name' => ['en' => 'Dental Braces',            'ar' => 'تقويم الأسنان',            'fr' => 'Appareil dentaire'],
                'price' => 80000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 60,
            ],
            [
                'name' => ['en' => 'Ceramic Crown',            'ar' => 'تاج سيراميك',              'fr' => 'Couronne céramique'],
                'price' => 25000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 60,
            ],
            [
                'name' => ['en' => 'Emergency Consultation',   'ar' => 'استشارة طارئة',            'fr' => 'Consultation urgente'],
                'price' => 2000, 'availability' => ServiceAvailability::NIGHTTIME, 'active' => true, 'duration' => 20,
            ],
            [
                'name' => ['en' => 'Pediatric Dentistry',      'ar' => 'طب أسنان الأطفال',         'fr' => 'Dentisterie pédiatrique'],
                'price' => 5000, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 45,
            ],
            [
                'name' => ['en' => 'Dental X-Ray',             'ar' => 'أشعة الأسنان',             'fr' => 'Radiographie dentaire'],
                'price' => 1500, 'availability' => ServiceAvailability::DAYTIME, 'active' => true, 'duration' => 15,
            ],
        ];

        $services = [];
        foreach ($data as $row) {
            $services[] = Service::create($row);
        }
        return $services;
    }

    // ─── Doctors ─────────────────────────────────────────────────────────────

    private function seedDoctors(array $services): array
    {
        $doctorData = [
            [
                'name'      => ['en' => 'Dr. Amina Belhocine',  'ar' => 'د. أمينة بلحسين',    'fr' => 'Dr. Amina Belhocine'],
                'specialty' => ['en' => 'General Dentistry',    'ar' => 'طب الأسنان العام',   'fr' => 'Dentisterie générale'],
                'diplomas'  => ['DDS – University of Algiers', 'Master in Dental Surgery'],
                'email'     => 'amina.belhocine@clinic.dz',
                'phone'     => '0551001001',
                'address'   => '12 Rue Didouche Mourad, Algiers',
                'services'  => [0, 1, 4, 8, 9], // indices into $services
            ],
            [
                'name'      => ['en' => 'Dr. Karim Ouahrani',   'ar' => 'د. كريم وهراني',     'fr' => 'Dr. Karim Ouahrani'],
                'specialty' => ['en' => 'Orthodontist',         'ar' => 'أخصائي تقويم الأسنان', 'fr' => 'Orthodontiste'],
                'diplomas'  => ['DDS – Université d\'Oran', 'Specialist in Orthodontics'],
                'email'     => 'karim.ouahrani@clinic.dz',
                'phone'     => '0551002002',
                'address'   => '45 Cité des Orangers, Oran',
                'services'  => [5, 6, 1],
            ],
            [
                'name'      => ['en' => 'Dr. Fatima Ziani',     'ar' => 'د. فاطمة زياني',     'fr' => 'Dr. Fatima Ziani'],
                'specialty' => ['en' => 'Endodontist',          'ar' => 'أخصائي علاج الجذور', 'fr' => 'Endodontiste'],
                'diplomas'  => ['DDS – University of Constantine', 'PhD in Endodontics'],
                'email'     => 'fatima.ziani@clinic.dz',
                'phone'     => '0551003003',
                'address'   => '3 Avenue de l\'ALN, Constantine',
                'services'  => [3, 2, 7],
            ],
            [
                'name'      => ['en' => 'Dr. Younes Meziane',   'ar' => 'د. يونس مزيان',      'fr' => 'Dr. Younes Meziane'],
                'specialty' => ['en' => 'Implantologist',       'ar' => 'أخصائي زراعة الأسنان', 'fr' => 'Implantologiste'],
                'diplomas'  => ['DDS – Université de Tlemcen', 'Implantology Fellowship – Paris'],
                'email'     => 'younes.meziane@clinic.dz',
                'phone'     => '0551004004',
                'address'   => '22 Rue des Frères Bouadou, Birmandreis',
                'services'  => [2, 6, 7],
            ],
            [
                'name'      => ['en' => 'Dr. Sara Bencherif',   'ar' => 'د. سارة بن شريف',    'fr' => 'Dr. Sara Bencherif'],
                'specialty' => ['en' => 'Pediatric Dentist',    'ar' => 'طبيبة أسنان أطفال',  'fr' => 'Dentiste pédiatrique'],
                'diplomas'  => ['DDS – University of Annaba', 'Specialty in Pedodontics'],
                'email'     => 'sara.bencherif@clinic.dz',
                'phone'     => '0551005005',
                'address'   => '8 Boulevard Colonel Amirouche, Algiers',
                'services'  => [8, 0, 4, 9],
            ],
        ];

        $doctors = [];
        foreach ($doctorData as $row) {
            $serviceIndices = $row['services'];
            unset($row['services']);
            $doctor     = Doctor::create($row);
            $serviceIds = array_map(fn ($i) => $services[$i]->id, $serviceIndices);
            $doctor->services()->attach($serviceIds);
            $doctors[] = $doctor;
        }
        return $doctors;
    }

    // ─── Patients ────────────────────────────────────────────────────────────

    private function seedPatients(): array
    {
        $patientData = [
            // Known demo patient – password = "password"
            ['first_name' => 'Mohamed',   'last_name' => 'Amrani',      'email' => 'patient@demo.dz',   'phone' => '0661000001', 'age' => 34, 'gender' => Gender::MALE,   'email_verified_at' => now()],
            ['first_name' => 'Nadia',     'last_name' => 'Boutaleb',    'email' => 'nadia@demo.dz',     'phone' => '0661000002', 'age' => 28, 'gender' => Gender::FEMALE, 'email_verified_at' => now()],
            ['first_name' => 'Hicham',    'last_name' => 'Rekkal',      'email' => 'hicham@demo.dz',    'phone' => '0661000003', 'age' => 45, 'gender' => Gender::MALE,   'email_verified_at' => now()],
            ['first_name' => 'Amira',     'last_name' => 'Djebbar',     'email' => 'amira@demo.dz',     'phone' => '0661000004', 'age' => 31, 'gender' => Gender::FEMALE, 'email_verified_at' => now()],
            ['first_name' => 'Nassim',    'last_name' => 'Belarbi',     'email' => 'nassim@demo.dz',    'phone' => '0661000005', 'age' => 52, 'gender' => Gender::MALE,   'email_verified_at' => now()],
            ['first_name' => 'Soraya',    'last_name' => 'Hamdani',     'email' => 'soraya@demo.dz',    'phone' => '0661000006', 'age' => 39, 'gender' => Gender::FEMALE, 'email_verified_at' => now()],
            ['first_name' => 'Bilal',     'last_name' => 'Cherif',      'email' => 'bilal@demo.dz',     'phone' => '0661000007', 'age' => 23, 'gender' => Gender::MALE,   'email_verified_at' => now()],
            ['first_name' => 'Meriem',    'last_name' => 'Ouled Ali',   'email' => 'meriem@demo.dz',    'phone' => '0661000008', 'age' => 42, 'gender' => Gender::FEMALE, 'email_verified_at' => null],
            ['first_name' => 'Rachid',    'last_name' => 'Aouadi',      'email' => 'rachid@demo.dz',    'phone' => '0661000009', 'age' => 60, 'gender' => Gender::MALE,   'email_verified_at' => now()],
            ['first_name' => 'Leila',     'last_name' => 'Khelifi',     'email' => 'leila@demo.dz',     'phone' => '0661000010', 'age' => 27, 'gender' => Gender::FEMALE, 'email_verified_at' => now()],
        ];

        $patients = [];
        foreach ($patientData as $row) {
            $patients[] = Patient::create(array_merge($row, ['password' => Hash::make('password')]));
        }
        return $patients;
    }

    // ─── Appointments ────────────────────────────────────────────────────────

    private function seedAppointments(array $patients, array $doctors, array $services): void
    {
        $now = Carbon::now();

        $scenarios = [
            // Today appointments (for TodayAppointmentsWidget)
            ['patient' => 0, 'doctor' => 0, 'service' => 0, 'from' => $now->copy()->setHour(9)->setMinute(0),  'status' => AppointmentStatus::CONFIRMED],
            ['patient' => 1, 'doctor' => 0, 'service' => 1, 'from' => $now->copy()->setHour(10)->setMinute(30), 'status' => AppointmentStatus::CONFIRMED],
            ['patient' => 2, 'doctor' => 2, 'service' => 3, 'from' => $now->copy()->setHour(11)->setMinute(0),  'status' => AppointmentStatus::PENDING],
            ['patient' => 3, 'doctor' => 1, 'service' => 5, 'from' => $now->copy()->setHour(14)->setMinute(0),  'status' => AppointmentStatus::COMPLETED],
            ['patient' => 4, 'doctor' => 3, 'service' => 2, 'from' => $now->copy()->setHour(16)->setMinute(0),  'status' => AppointmentStatus::CONFIRMED],

            // Tomorrow
            ['patient' => 5, 'doctor' => 0, 'service' => 4, 'from' => $now->copy()->addDay()->setHour(9)->setMinute(0),  'status' => AppointmentStatus::PENDING],
            ['patient' => 6, 'doctor' => 4, 'service' => 8, 'from' => $now->copy()->addDay()->setHour(10)->setMinute(0), 'status' => AppointmentStatus::CONFIRMED],
            ['patient' => 7, 'doctor' => 2, 'service' => 3, 'from' => $now->copy()->addDay()->setHour(11)->setMinute(0), 'status' => AppointmentStatus::PENDING],

            // Upcoming (next week)
            ['patient' => 8, 'doctor' => 1, 'service' => 6, 'from' => $now->copy()->addDays(7)->setHour(9)->setMinute(0),  'status' => AppointmentStatus::CONFIRMED],
            ['patient' => 9, 'doctor' => 3, 'service' => 2, 'from' => $now->copy()->addDays(7)->setHour(14)->setMinute(0), 'status' => AppointmentStatus::PENDING],

            // Past – completed
            ['patient' => 0, 'doctor' => 0, 'service' => 0, 'from' => $now->copy()->subDays(3)->setHour(9)->setMinute(0),  'status' => AppointmentStatus::COMPLETED],
            ['patient' => 1, 'doctor' => 2, 'service' => 3, 'from' => $now->copy()->subDays(5)->setHour(11)->setMinute(0), 'status' => AppointmentStatus::COMPLETED],
            ['patient' => 2, 'doctor' => 4, 'service' => 8, 'from' => $now->copy()->subDays(7)->setHour(10)->setMinute(0), 'status' => AppointmentStatus::COMPLETED],
            ['patient' => 3, 'doctor' => 1, 'service' => 5, 'from' => $now->copy()->subDays(10)->setHour(14)->setMinute(0),'status' => AppointmentStatus::COMPLETED],
            ['patient' => 4, 'doctor' => 0, 'service' => 1, 'from' => $now->copy()->subDays(14)->setHour(9)->setMinute(0), 'status' => AppointmentStatus::COMPLETED],

            // Past – cancelled / rejected
            ['patient' => 5, 'doctor' => 3, 'service' => 2, 'from' => $now->copy()->subDays(2)->setHour(10)->setMinute(0),  'status' => AppointmentStatus::CANCELLED, 'cancellation_reason' => 'Patient could not attend due to work obligations.'],
            ['patient' => 6, 'doctor' => 2, 'service' => 3, 'from' => $now->copy()->subDays(4)->setHour(15)->setMinute(0),  'status' => AppointmentStatus::REJECTED,  'admin_notes' => 'Slot already taken.'],

            // Pending cancellation request
            ['patient' => 7, 'doctor' => 0, 'service' => 0, 'from' => $now->copy()->addDays(3)->setHour(9)->setMinute(0),
             'status' => AppointmentStatus::CONFIRMED,
             'change_request_status' => ChangeRequestStatus::PENDING_CANCELLATION,
             'cancellation_reason' => 'Patient travelling abroad.'],

            // Pending reschedule request
            ['patient' => 8, 'doctor' => 1, 'service' => 5, 'from' => $now->copy()->addDays(5)->setHour(11)->setMinute(0),
             'status' => AppointmentStatus::CONFIRMED,
             'change_request_status' => ChangeRequestStatus::PENDING_RESCHEDULE,
             'reschedule_reason' => 'Requesting an earlier slot if possible.',
             'requested_new_from' => $now->copy()->addDays(2)->setHour(10)->setMinute(0),
             'requested_new_to'   => $now->copy()->addDays(2)->setHour(11)->setMinute(0)],
        ];

        foreach ($scenarios as $s) {
            $service  = $services[$s['service']];
            $duration = $service->duration ?? 60;
            $from     = $s['from'];
            $to       = $from->copy()->addMinutes($duration);

            Appointment::create(array_filter([
                'patient_id'           => $patients[$s['patient']]->id,
                'doctor_id'            => $doctors[$s['doctor']]->id,
                'service_id'           => $service->id,
                'from'                 => $from,
                'to'                   => $to,
                'price'                => $service->price,
                'status'               => $s['status'],
                'admin_notes'          => $s['admin_notes'] ?? null,
                'cancellation_reason'  => $s['cancellation_reason'] ?? null,
                'reschedule_reason'    => $s['reschedule_reason'] ?? null,
                'change_request_status'=> $s['change_request_status'] ?? null,
                'requested_new_from'   => $s['requested_new_from'] ?? null,
                'requested_new_to'     => $s['requested_new_to'] ?? null,
                'metadata'             => ['notes' => 'Demo appointment', 'duration_minutes' => $duration],
            ], fn ($v) => $v !== null));
        }
    }

    // ─── Urgent Bookings ─────────────────────────────────────────────────────

    private function seedUrgentBookings(array $patients): void
    {
        $bookings = [
            [
                'patient_id'    => $patients[0]->id,
                'patient_name'  => $patients[0]->full_name,
                'patient_phone' => $patients[0]->phone,
                'patient_email' => $patients[0]->email,
                'reason'        => 'Severe toothache since last night, cannot sleep.',
                'description'   => 'The pain is in the upper left molar, very sharp pain when touching.',
                'status'        => UrgentBookingStatus::PENDING,
            ],
            [
                'patient_id'    => $patients[1]->id,
                'patient_name'  => $patients[1]->full_name,
                'patient_phone' => $patients[1]->phone,
                'patient_email' => $patients[1]->email,
                'reason'        => 'Broken tooth after accident.',
                'description'   => 'Front tooth cracked after falling. Need urgent repair.',
                'status'        => UrgentBookingStatus::ACCEPTED,
                'preferred_datetime' => Carbon::now()->addHours(4),
                'scheduled_datetime' => Carbon::now()->addHours(5),
            ],
            [
                'patient_id'    => null,
                'patient_name'  => 'Kamel Benali',
                'patient_phone' => '0770123456',
                'patient_email' => 'kamel.benali@email.com',
                'reason'        => 'Gum bleeding and swelling since 3 days.',
                'status'        => UrgentBookingStatus::PENDING,
            ],
            [
                'patient_id'    => $patients[2]->id,
                'patient_name'  => $patients[2]->full_name,
                'patient_phone' => $patients[2]->phone,
                'patient_email' => $patients[2]->email,
                'reason'        => 'Dental abscess — very painful.',
                'description'   => 'Swelling on the jaw with fever started yesterday.',
                'status'        => UrgentBookingStatus::COMPLETED,
                'admin_notes'   => 'Treated with antibiotics and drainage. Follow-up in 7 days.',
            ],
            [
                'patient_id'    => null,
                'patient_name'  => 'Rania Mezroua',
                'patient_phone' => '0550987654',
                'patient_email' => 'rania.m@email.com',
                'reason'        => 'Lost filling — tooth is very sensitive to cold.',
                'status'        => UrgentBookingStatus::REJECTED,
                'admin_notes'   => 'Slot not available. Patient advised to book regular appointment.',
            ],
        ];

        foreach ($bookings as $booking) {
            UrgentBooking::create($booking);
        }
    }

    // ─── Events ──────────────────────────────────────────────────────────────

    private function seedEvents(): void
    {
        $events = [
            [
                'title'          => ['en' => 'Oral Health Awareness Day',         'ar' => 'يوم الوعي بصحة الفم',             'fr' => 'Journée de sensibilisation à la santé bucco-dentaire'],
                'description'    => ['en' => 'A free public event raising awareness about dental hygiene and preventive care for all ages.', 'ar' => 'فعالية عامة مجانية لتوعية المجتمع بأهمية نظافة الأسنان والرعاية الوقائية.', 'fr' => 'Un événement public gratuit pour sensibiliser à l\'hygiène dentaire.'],
                'date'           => Carbon::now()->addDays(10)->toDateString(),
                'time'           => '09:00',
                'location'       => ['en' => 'Palais de la Culture, Algiers', 'ar' => 'قصر الثقافة، الجزائر العاصمة', 'fr' => 'Palais de la Culture, Alger'],
                'speakers'       => ['en' => 'Dr. Amina Belhocine, Dr. Sara Bencherif', 'ar' => 'د. أمينة بلحسين، د. سارة بن شريف', 'fr' => 'Dr. Amina Belhocine, Dr. Sara Bencherif'],
                'about_event'    => ['en' => 'Learn about proper brushing techniques, diet tips, and early signs of dental disease from our expert panel.', 'ar' => 'تعرف على تقنيات التفريش الصحيحة ونصائح التغذية والعلامات المبكرة لأمراض الأسنان.', 'fr' => 'Apprenez les techniques de brossage, les conseils nutritionnels et les signes précoces des maladies dentaires.'],
                'what_to_expect' => ['en' => 'Free dental screening, Q&A session with doctors, children\'s dental hygiene workshop.', 'ar' => 'فحص الأسنان المجاني، جلسة أسئلة وأجوبة مع الأطباء، ورشة عمل لنظافة أسنان الأطفال.', 'fr' => 'Dépistage dentaire gratuit, séance de questions-réponses, atelier d\'hygiène dentaire pour enfants.'],
                'is_archived'    => false,
            ],
            [
                'title'          => ['en' => 'Modern Implantology Workshop',       'ar' => 'ورشة عمل في زراعة الأسنان الحديثة', 'fr' => 'Atelier d\'implantologie moderne'],
                'description'    => ['en' => 'Hands-on workshop for dental professionals covering the latest techniques in dental implant placement.', 'ar' => 'ورشة عملية للمتخصصين في طب الأسنان تغطي أحدث تقنيات زراعة الأسنان.', 'fr' => 'Atelier pratique pour les professionnels dentaires sur les dernières techniques d\'implants.'],
                'date'           => Carbon::now()->addDays(21)->toDateString(),
                'time'           => '08:00',
                'location'       => ['en' => 'Hotel Sofitel, Algiers', 'ar' => 'فندق سوفيتيل، الجزائر', 'fr' => 'Hôtel Sofitel, Alger'],
                'speakers'       => ['en' => 'Dr. Younes Meziane, Prof. Laurent Dubois (France)', 'ar' => 'د. يونس مزيان، أ. لوران دوبوا (فرنسا)', 'fr' => 'Dr. Younes Meziane, Prof. Laurent Dubois (France)'],
                'about_event'    => ['en' => 'A professional development event for licensed dentists and oral surgeons.', 'ar' => 'فعالية للتطوير المهني لأطباء الأسنان المرخصين والجراحين.', 'fr' => 'Un événement de développement professionnel pour les dentistes et chirurgiens.'],
                'what_to_expect' => ['en' => 'Live demonstrations, case presentations, networking lunch, CPD certificate.', 'ar' => 'عروض حية، عرض الحالات، غداء للتواصل، شهادة تطوير مهني.', 'fr' => 'Démonstrations en direct, présentations de cas, déjeuner networking, certificat CPD.'],
                'is_archived'    => false,
            ],
            [
                'title'          => ['en' => 'Children\'s Smile Day',              'ar' => 'يوم ابتسامة الأطفال',              'fr' => 'Journée du sourire des enfants'],
                'description'    => ['en' => 'A fun-filled day dedicated to children\'s oral health with games, prizes, and free dental check-ups.', 'ar' => 'يوم مليء بالمرح مخصص لصحة أفواه الأطفال مع ألعاب وجوائز وفحوصات مجانية.', 'fr' => 'Une journée amusante dédiée à la santé bucco-dentaire des enfants.'],
                'date'           => Carbon::now()->toDateString(),
                'time'           => '10:00',
                'location'       => ['en' => 'Clinic Main Hall, Algiers', 'ar' => 'القاعة الرئيسية للعيادة، الجزائر', 'fr' => 'Hall principal de la clinique, Alger'],
                'speakers'       => ['en' => 'Dr. Sara Bencherif', 'ar' => 'د. سارة بن شريف', 'fr' => 'Dr. Sara Bencherif'],
                'about_event'    => ['en' => 'Teaching children ages 5–12 about the importance of brushing, flossing, and healthy eating.', 'ar' => 'تعليم الأطفال من 5 إلى 12 سنة أهمية تفريش الأسنان والخيط والتغذية الصحية.', 'fr' => 'Enseigner aux enfants de 5 à 12 ans l\'importance du brossage, du fil dentaire et d\'une alimentation saine.'],
                'what_to_expect' => ['en' => 'Puppet show about dental hygiene, free toothbrush kits, face painting, free check-ups.', 'ar' => 'عرض دمى حول نظافة الأسنان، أطقم فرشاة أسنان مجانية، رسم الوجه، فحوصات مجانية.', 'fr' => 'Spectacle de marionnettes sur l\'hygiène dentaire, kits de brosse à dents gratuits, peinture sur visage.'],
                'is_archived'    => false,
            ],
            [
                'title'          => ['en' => 'Annual Dental Conference 2025',       'ar' => 'المؤتمر السنوي لطب الأسنان 2025',  'fr' => 'Conférence dentaire annuelle 2025'],
                'description'    => ['en' => 'Our annual conference gathering top dental professionals from across Algeria.', 'ar' => 'مؤتمرنا السنوي الذي يجمع كبار متخصصي طب الأسنان من مختلف أنحاء الجزائر.', 'fr' => 'Notre conférence annuelle réunissant les meilleurs professionnels dentaires d\'Algérie.'],
                'date'           => Carbon::now()->subDays(30)->toDateString(),
                'time'           => '08:30',
                'location'       => ['en' => 'Centre International de Conférences, Algiers', 'ar' => 'المركز الدولي للمؤتمرات، الجزائر', 'fr' => 'Centre International de Conférences, Alger'],
                'speakers'       => ['en' => 'Multiple speakers', 'ar' => 'متحدثون متعددون', 'fr' => 'Plusieurs intervenants'],
                'about_event'    => ['en' => 'Three-day event covering advances in orthodontics, implantology, and cosmetic dentistry.', 'ar' => 'فعالية لمدة ثلاثة أيام تغطي التطورات في تقويم الأسنان وزراعة الأسنان وطب الأسنان التجميلي.', 'fr' => 'Événement de trois jours couvrant les avancées en orthodontie, implantologie et dentisterie esthétique.'],
                'what_to_expect' => ['en' => 'Keynote speeches, workshops, poster presentations, exhibition hall.', 'ar' => 'خطابات رئيسية، ورش عمل، عروض ملصقات، قاعة عرض.', 'fr' => 'Discours liminaires, ateliers, présentations d\'affiches, hall d\'exposition.'],
                'is_archived'    => true,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }

    // ─── Trainings ───────────────────────────────────────────────────────────

    private function seedTrainings(): array
    {
        $trainingsData = [
            [
                'title'        => ['en' => 'Advanced Root Canal Techniques',  'ar' => 'تقنيات علاج قناة الجذر المتقدمة', 'fr' => 'Techniques avancées de traitement de canal'],
                'description'  => ['en' => 'A comprehensive online course covering advanced endodontic techniques including rotary instrumentation, warm vertical compaction, and retreatment cases.', 'ar' => 'دورة شاملة عبر الإنترنت تغطي تقنيات علم الأسنان الداخلية المتقدمة بما في ذلك الأجهزة الدوارة والرأب الرأسي الدافئ وحالات إعادة العلاج.', 'fr' => 'Un cours en ligne complet couvrant les techniques d\'endodontie avancées, y compris l\'instrumentation rotative et les cas de retraitement.'],
                'trainer_name' => 'Dr. Fatima Ziani',
                'duration'     => '16 hours',
                'price'        => 25000,
                'video_url'    => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'title'        => ['en' => 'Orthodontic Essentials for General Dentists', 'ar' => 'أساسيات تقويم الأسنان للأطباء العامين', 'fr' => 'Fondamentaux de l\'orthodontie pour les dentistes généralistes'],
                'description'  => ['en' => 'This course provides general practitioners with the foundational knowledge and clinical skills to handle basic orthodontic cases and identify complex cases for referral.', 'ar' => 'تزود هذه الدورة الممارسين العامين بالمعرفة الأساسية والمهارات السريرية للتعامل مع حالات تقويم الأسنان الأساسية.', 'fr' => 'Ce cours fournit aux praticiens généralistes les connaissances fondamentales pour traiter les cas orthodontiques de base.'],
                'trainer_name' => 'Dr. Karim Ouahrani',
                'duration'     => '24 hours',
                'price'        => 35000,
                'video_url'    => null,
            ],
            [
                'title'        => ['en' => 'Pediatric Dentistry: Behavior Management', 'ar' => 'طب أسنان الأطفال: إدارة السلوك', 'fr' => 'Dentisterie pédiatrique : gestion du comportement'],
                'description'  => ['en' => 'Learn evidence-based techniques for managing child behavior in the dental clinic, including tell-show-do, positive reinforcement, and sedation considerations.', 'ar' => 'تعلم تقنيات قائمة على الأدلة لإدارة سلوك الطفل في عيادة الأسنان، بما في ذلك اخبر-أرِ-افعل والتعزيز الإيجابي.', 'fr' => 'Apprenez des techniques fondées sur des preuves pour gérer le comportement des enfants en cabinet dentaire.'],
                'trainer_name' => 'Dr. Sara Bencherif',
                'duration'     => '8 hours',
                'price'        => 15000,
                'video_url'    => 'https://vimeo.com/123456789',
            ],
            [
                'title'        => ['en' => 'Digital Dentistry & CAD/CAM Systems',  'ar' => 'طب الأسنان الرقمي وأنظمة CAD/CAM', 'fr' => 'Dentisterie numérique et systèmes CAD/CAM'],
                'description'  => ['en' => 'Explore the world of digital dentistry, including intraoral scanning, computer-aided design/manufacturing, and same-day crown fabrication.', 'ar' => 'استكشف عالم طب الأسنان الرقمي، بما في ذلك المسح داخل الفم والتصميم بمساعدة الكمبيوتر وصنع التيجان في نفس اليوم.', 'fr' => 'Explorez le monde de la dentisterie numérique, y compris la numérisation intra-orale et la fabrication de couronnes le jour même.'],
                'trainer_name' => 'Dr. Younes Meziane',
                'duration'     => '12 hours',
                'price'        => 45000,
                'video_url'    => null,
            ],
        ];

        $trainings = [];
        foreach ($trainingsData as $row) {
            $trainings[] = Training::create($row);
        }
        return $trainings;
    }

    // ─── Reviews ─────────────────────────────────────────────────────────────

    private function seedReviews(array $trainings, array $patients): void
    {
        $reviews = [
            ['training' => 0, 'patient' => 0, 'rating' => 5, 'content' => 'Excellent course! Dr. Ziani explained complex concepts in a very clear way. I immediately applied the techniques in my practice.', 'is_approved' => true],
            ['training' => 0, 'patient' => 1, 'rating' => 4, 'content' => 'Very informative. The rotary instrumentation section was the best. Would love more hands-on cases.', 'is_approved' => true],
            ['training' => 0, 'patient' => 2, 'rating' => 5, 'content' => 'Best endodontic course I\'ve attended. Highly recommended for all practitioners.', 'is_approved' => true],
            ['training' => 1, 'patient' => 3, 'rating' => 4, 'content' => 'Great introduction to orthodontics. The case studies were very helpful.', 'is_approved' => true],
            ['training' => 1, 'patient' => 4, 'rating' => 3, 'content' => 'Good content but could be more detailed on wire bending techniques.', 'is_approved' => true],
            ['training' => 1, 'patient' => 5, 'rating' => 5, 'content' => 'Dr. Ouahrani is an amazing teacher. I learned so much in just a few hours.', 'is_approved' => false], // pending approval
            ['training' => 2, 'patient' => 6, 'rating' => 5, 'content' => 'The behavior management techniques are a game changer. My young patients are much more relaxed now.', 'is_approved' => true],
            ['training' => 3, 'patient' => 7, 'rating' => 4, 'content' => 'Fascinating introduction to digital dentistry. The CAD/CAM demo was impressive.', 'is_approved' => true],
            ['training' => 3, 'patient' => 8, 'rating' => 5, 'content' => 'Transformed how I approach crown fabrication. Worth every dinar!', 'is_approved' => true],
        ];

        foreach ($reviews as $r) {
            Review::create([
                'reviewable_type' => Training::class,
                'reviewable_id'   => $trainings[$r['training']]->id,
                'patient_id'      => $patients[$r['patient']]->id,
                'rating'          => $r['rating'],
                'content'         => $r['content'],
                'is_approved'     => $r['is_approved'],
            ]);
        }
    }

    // ─── Testimonials ────────────────────────────────────────────────────────

    private function seedTestimonials(array $patients): void
    {
        $testimonials = [
            ['patient' => 0, 'rating' => 5, 'is_published' => true,  'content' => 'The best dental clinic I have ever visited. The staff is professional and the results are amazing. My teeth whitening turned out perfect!'],
            ['patient' => 1, 'rating' => 5, 'is_published' => true,  'content' => 'Dr. Bencherif is fantastic with children. My daughter was terrified of dentists before, but now she actually enjoys her appointments!'],
            ['patient' => 2, 'rating' => 4, 'is_published' => true,  'content' => 'Great experience overall. The clinic is modern and clean. Dr. Meziane did an excellent job with my implant.'],
            ['patient' => 3, 'rating' => 5, 'is_published' => true,  'content' => 'I had my braces done by Dr. Ouahrani. After 18 months, I have the smile I always dreamed of. Highly recommend!'],
            ['patient' => 4, 'rating' => 4, 'is_published' => true,  'content' => 'Very professional team. The booking system is easy to use and the reminders are helpful. The root canal was painless.'],
            ['patient' => 5, 'rating' => 5, 'is_published' => true,  'content' => 'I was nervous about my first dental implant, but Dr. Meziane made me feel at ease throughout the entire process.'],
            ['patient' => 6, 'rating' => 3, 'is_published' => true,  'content' => 'Good clinic but the waiting time could be improved. The quality of treatment is excellent though.'],
            ['patient' => 7, 'rating' => 5, 'is_published' => false, 'content' => 'Amazing experience! Dr. Belhocine is incredibly thorough and explains everything clearly. 10/10 would recommend.'],
            ['patient' => 8, 'rating' => 4, 'is_published' => true,  'content' => 'Professional and caring staff. The clinic uses the latest equipment which is reassuring. Prices are fair for the quality.'],
            ['patient' => 9, 'rating' => 5, 'is_published' => true,  'content' => 'I\'ve been coming to this clinic for 3 years. Consistent high-quality care every time. Dr. Ziani saved my molar!'],
        ];

        foreach ($testimonials as $t) {
            Testimonial::create([
                'patient_id'   => $patients[$t['patient']]->id,
                'patient_name' => $patients[$t['patient']]->full_name,
                'content'      => $t['content'],
                'rating'       => $t['rating'],
                'is_published' => $t['is_published'],
            ]);
        }
    }

    // ─── Patient Notifications ────────────────────────────────────────────────

    private function seedPatientNotifications(array $patients): void
    {
        $notifications = [
            [
                'patient_id' => $patients[0]->id,
                'type'       => 'appointment_confirmed',
                'title'      => ['en' => 'Appointment Confirmed', 'ar' => 'تم تأكيد الموعد', 'fr' => 'Rendez-vous confirmé'],
                'body'       => ['en' => 'Your appointment for Dental Cleaning on ' . Carbon::now()->addDay()->format('d/m/Y') . ' has been confirmed.', 'ar' => 'تم تأكيد موعدك لتنظيف الأسنان في ' . Carbon::now()->addDay()->format('d/m/Y') . '.', 'fr' => 'Votre rendez-vous pour un détartrage le ' . Carbon::now()->addDay()->format('d/m/Y') . ' a été confirmé.'],
                'read_at'    => null,
            ],
            [
                'patient_id' => $patients[0]->id,
                'type'       => 'appointment_reminder',
                'title'      => ['en' => 'Appointment Reminder', 'ar' => 'تذكير بالموعد', 'fr' => 'Rappel de rendez-vous'],
                'body'       => ['en' => 'Reminder: You have an appointment tomorrow at 9:00 AM. Please arrive 10 minutes early.', 'ar' => 'تذكير: لديك موعد غداً في الساعة 9:00 صباحاً. يرجى الحضور قبل 10 دقائق.', 'fr' => 'Rappel : Vous avez un rendez-vous demain à 9h00. Veuillez arriver 10 minutes en avance.'],
                'read_at'    => Carbon::now()->subHour(),
            ],
            [
                'patient_id' => $patients[1]->id,
                'type'       => 'appointment_confirmed',
                'title'      => ['en' => 'Appointment Confirmed', 'ar' => 'تم تأكيد الموعد', 'fr' => 'Rendez-vous confirmé'],
                'body'       => ['en' => 'Your appointment for Teeth Whitening has been confirmed.', 'ar' => 'تم تأكيد موعدك لتبييض الأسنان.', 'fr' => 'Votre rendez-vous pour le blanchiment dentaire a été confirmé.'],
                'read_at'    => null,
            ],
            [
                'patient_id' => $patients[2]->id,
                'type'       => 'change_request_approved',
                'title'      => ['en' => 'Cancellation Approved', 'ar' => 'تمت الموافقة على الإلغاء', 'fr' => 'Annulation approuvée'],
                'body'       => ['en' => 'Your cancellation request has been approved. We hope to see you again soon.', 'ar' => 'تمت الموافقة على طلب الإلغاء الخاص بك. نأمل أن نراك مرة أخرى قريباً.', 'fr' => 'Votre demande d\'annulation a été approuvée. Nous espérons vous revoir bientôt.'],
                'read_at'    => Carbon::now()->subDays(2),
            ],
            [
                'patient_id' => $patients[3]->id,
                'type'       => 'appointment_completed',
                'title'      => ['en' => 'Treatment Completed', 'ar' => 'اكتمل العلاج', 'fr' => 'Traitement terminé'],
                'body'       => ['en' => 'Your Dental Braces appointment has been marked as completed. Thank you for trusting us!', 'ar' => 'تم وضع علامة "مكتمل" على موعد تقويم الأسنان الخاص بك. شكراً لثقتك بنا!', 'fr' => 'Votre rendez-vous pour l\'appareil dentaire a été marqué comme terminé. Merci de nous faire confiance !'],
                'read_at'    => null,
            ],
        ];

        foreach ($notifications as $n) {
            PatientNotification::create($n);
        }
    }
}
