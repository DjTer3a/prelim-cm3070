<?php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\Context;
use App\Models\ContextValue;
use App\Models\ProfileAttribute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private array $attributes = [];

    public function run(): void
    {
        $this->attributes = $this->createProfileAttributes();

        // Create admin user
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('admin') . '?d=identicon&s=200',
        ]);

        $users = $this->createUsers();

        foreach ($users as $userData) {
            $user = $userData['user'];
            $this->createContextsForUser($user, $userData['profiles']);
        }

        $this->createAccessLogs($users);
    }

    private function createUsers(): array
    {
        return [
            [
                'user' => User::factory()->create([
                    'name' => 'Imhotep',
                    'username' => 'imhotep',
                    'email' => 'imhotep@example.com',
                    'password' => Hash::make('password'),
                    'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('imhotep') . '?d=identicon&s=200',
                ]),
                'profiles' => [
                    'work' => [
                        'display_name' => 'Imhotep, Son of Ptah',
                        'bio' => 'High Priest of Ra, Royal Architect, and Chancellor to the Pharaoh. Built the Step Pyramid before it was cool.',
                        'job_title' => 'High Priest & Royal Architect',
                        'company' => 'Kingdom of Pharaoh Djoser',
                        'location' => 'Temple of Ptah, Memphis',
                        'website' => 'https://linkedin.com/in/imhotep-architect',
                        'email' => 'imhotep@royal-court.eg',
                        'phone' => '+20 100 123 4567',
                    ],
                    'personal' => [
                        'display_name' => 'Imhotep',
                        'bio' => 'Stargazing, herbal medicine, and long walks along the Nile. Also a pretty decent poet.',
                        'location' => 'Private villa near Saqqara',
                        'website' => 'https://instagram.com/imhotep_life',
                        'email' => 'imhotep.personal@papyrus.eg',
                    ],
                    'gaming' => [
                        'display_name' => 'xX_MummySlayer_Xx',
                        'bio' => 'I build pyramids by day and destroy noobs by night. Rank: Divine Pharaoh.',
                        'website' => 'https://twitch.tv/mummyslayer',
                    ],
                ],
            ],
            [
                'user' => User::factory()->create([
                    'name' => 'Nefertiti',
                    'username' => 'nefertiti',
                    'email' => 'nefertiti@example.com',
                    'password' => Hash::make('password'),
                    'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('nefertiti') . '?d=identicon&s=200',
                ]),
                'profiles' => [
                    'work' => [
                        'display_name' => 'Nefertiti, The Beautiful One',
                        'bio' => 'Queen of Egypt, co-regent with Akhenaten. Pioneering religious reform and artistic revolution.',
                        'job_title' => 'Queen & Co-Regent',
                        'company' => 'Kingdom of Akhenaten',
                        'location' => 'Royal Palace, Amarna',
                        'website' => 'https://linkedin.com/in/queen-nefertiti',
                        'email' => 'nefertiti@amarna-palace.eg',
                        'phone' => '+20 111 234 5678',
                    ],
                    'personal' => [
                        'display_name' => 'Nefer',
                        'bio' => 'Mother of six, art collector, sun worshipper. Living my best eternal life.',
                        'location' => 'Private gardens, Amarna',
                        'website' => 'https://instagram.com/nefertiti_beauty',
                        'email' => 'nefer.private@papyrus.eg',
                    ],
                    'gaming' => [
                        'display_name' => 'QueenOfQueens',
                        'bio' => 'Crowned queen of strategy games. Building empires since 1370 BCE.',
                        'website' => 'https://twitch.tv/queenofqueens',
                    ],
                ],
            ],
            [
                'user' => User::factory()->create([
                    'name' => 'Tutankhamun',
                    'username' => 'tutankhamun',
                    'email' => 'tutankhamun@example.com',
                    'password' => Hash::make('password'),
                    'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('tutankhamun') . '?d=identicon&s=200',
                ]),
                'profiles' => [
                    'work' => [
                        'display_name' => 'Tutankhamun, The Living Image of Amun',
                        'bio' => 'Boy King of Egypt. Restored the old gods, fixed the economy. Golden mask enthusiast.',
                        'job_title' => 'Pharaoh of Egypt',
                        'company' => 'New Kingdom of Egypt',
                        'location' => 'Throne Room, Thebes',
                        'website' => 'https://linkedin.com/in/king-tut',
                        'email' => 'tut@pharaoh-office.eg',
                        'phone' => '+20 122 345 6789',
                    ],
                    'personal' => [
                        'display_name' => 'Tut',
                        'bio' => 'Young ruler trying to make Egypt great again. Love chariots and hunting.',
                        'location' => 'Valley of the Kings (future address)',
                        'website' => 'https://instagram.com/king_tut_official',
                        'email' => 'tut.chill@papyrus.eg',
                    ],
                    'gaming' => [
                        'display_name' => 'GoldenMask_Gamer',
                        'bio' => 'My tomb has the best loot. Speedrunning life since age 9.',
                        'website' => 'https://twitch.tv/goldenmask',
                    ],
                ],
            ],
            [
                'user' => User::factory()->create([
                    'name' => 'Cleopatra',
                    'username' => 'cleopatra',
                    'email' => 'cleopatra@example.com',
                    'password' => Hash::make('password'),
                    'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('cleopatra') . '?d=identicon&s=200',
                ]),
                'profiles' => [
                    'work' => [
                        'display_name' => 'Cleopatra VII Philopator',
                        'bio' => 'Last active pharaoh of Ptolemaic Egypt. Fluent in 9 languages. Expert diplomat and naval strategist.',
                        'job_title' => 'Pharaoh & Queen',
                        'company' => 'Ptolemaic Kingdom',
                        'location' => 'Royal Palace, Alexandria',
                        'website' => 'https://linkedin.com/in/cleopatra-diplomat',
                        'email' => 'cleopatra@alexandria-palace.eg',
                        'phone' => '+20 150 456 7890',
                    ],
                    'personal' => [
                        'display_name' => 'Cleo',
                        'bio' => 'Intellectual, polyglot, mother. Enjoy sailing the Mediterranean and reading at the Library of Alexandria.',
                        'location' => 'Summer villa, Canopus',
                        'website' => 'https://instagram.com/cleopatra_vii',
                        'email' => 'cleo.private@papyrus.eg',
                    ],
                    'gaming' => [
                        'display_name' => 'AspQueen',
                        'bio' => 'Dangerous and beautiful. Will charm your army then destroy it.',
                        'website' => 'https://twitch.tv/aspqueen',
                    ],
                ],
            ],
            [
                'user' => User::factory()->create([
                    'name' => 'Ramesses II',
                    'username' => 'ramesses',
                    'email' => 'ramesses@example.com',
                    'password' => Hash::make('password'),
                    'profile_photo' => 'https://www.gravatar.com/avatar/' . md5('ramesses') . '?d=identicon&s=200',
                ]),
                'profiles' => [
                    'work' => [
                        'display_name' => 'Ramesses the Great',
                        'bio' => 'Greatest pharaoh of all time. Built Abu Simbel, won Battle of Kadesh (sort of). 100+ children.',
                        'job_title' => 'Pharaoh & Military Commander',
                        'company' => 'New Kingdom of Egypt',
                        'location' => 'Pi-Ramesses, Delta',
                        'website' => 'https://linkedin.com/in/ramesses-great',
                        'email' => 'ramesses@war-council.eg',
                        'phone' => '+20 101 567 8901',
                    ],
                    'personal' => [
                        'display_name' => 'Ramesses',
                        'bio' => 'Family man with many wives and 100+ kids. Building monuments to myself is my hobby.',
                        'location' => 'Harem Palace, Pi-Ramesses',
                        'website' => 'https://instagram.com/ramesses_ii',
                        'email' => 'ramesses.family@papyrus.eg',
                    ],
                    'gaming' => [
                        'display_name' => 'WarGod_Ramesses',
                        'bio' => 'Conquered more lands than you have hours played. Undefeated (in my official records).',
                        'website' => 'https://twitch.tv/wargod_ramesses',
                    ],
                ],
            ],
        ];
    }

    private function createContextsForUser(User $user, array $profiles): void
    {
        foreach ($profiles as $slug => $values) {
            $context = Context::factory()->create([
                'user_id' => $user->id,
                'name' => ucfirst($slug),
                'slug' => $slug,
                'is_default' => $slug === 'work',
                'is_active' => true,
            ]);

            $this->createContextValues($context, $values);
        }
    }

    private function createContextValues(Context $context, array $values): void
    {
        // Visibility mapping
        $visibilityMap = [
            'display_name' => 'public',
            'bio' => 'public',
            'job_title' => 'public',
            'company' => 'public',
            'location' => 'public',
            'website' => 'protected',
            'email' => 'protected',
            'phone' => 'private',
        ];

        foreach ($values as $key => $value) {
            if (isset($this->attributes[$key])) {
                ContextValue::factory()->create([
                    'context_id' => $context->id,
                    'profile_attribute_id' => $this->attributes[$key]->id,
                    'value' => $value,
                    'visibility' => $visibilityMap[$key] ?? 'public',
                ]);
            }
        }
    }

    private function createProfileAttributes(): array
    {
        return [
            'display_name' => ProfileAttribute::factory()->create([
                'key' => 'display_name',
                'name' => 'Display Name',
                'translations' => ['ar' => 'الاسم المعروض', 'fr' => 'Nom affiché', 'es' => 'Nombre mostrado', 'de' => 'Anzeigename', 'zh' => '显示名称', 'ja' => '表示名'],
                'data_type' => 'string',
                'schema_type' => 'https://schema.org/name',
                'is_system' => true,
            ]),
            'email' => ProfileAttribute::factory()->create([
                'key' => 'email',
                'name' => 'Email',
                'translations' => ['ar' => 'البريد الإلكتروني', 'fr' => 'E-mail', 'es' => 'Correo', 'de' => 'E-Mail', 'zh' => '电子邮件', 'ja' => 'メール'],
                'data_type' => 'email',
                'schema_type' => 'https://schema.org/email',
                'is_system' => true,
            ]),
            'bio' => ProfileAttribute::factory()->create([
                'key' => 'bio',
                'name' => 'Bio',
                'translations' => ['ar' => 'السيرة الذاتية', 'fr' => 'Biographie', 'es' => 'Biografía', 'de' => 'Biografie', 'zh' => '简介', 'ja' => '自己紹介'],
                'data_type' => 'text',
                'schema_type' => null,
                'is_system' => true,
            ]),
            'website' => ProfileAttribute::factory()->create([
                'key' => 'website',
                'name' => 'Website',
                'translations' => ['ar' => 'الموقع الإلكتروني', 'fr' => 'Site web', 'es' => 'Sitio web', 'de' => 'Webseite', 'zh' => '网站', 'ja' => 'ウェブサイト'],
                'data_type' => 'url',
                'schema_type' => 'https://schema.org/url',
                'is_system' => true,
            ]),
            'job_title' => ProfileAttribute::factory()->create([
                'key' => 'job_title',
                'name' => 'Job Title',
                'translations' => ['ar' => 'المسمى الوظيفي', 'fr' => 'Poste', 'es' => 'Cargo', 'de' => 'Berufsbezeichnung', 'zh' => '职位', 'ja' => '役職'],
                'data_type' => 'string',
                'schema_type' => 'https://schema.org/jobTitle',
                'is_system' => true,
            ]),
            'company' => ProfileAttribute::factory()->create([
                'key' => 'company',
                'name' => 'Company',
                'translations' => ['ar' => 'الشركة', 'fr' => 'Entreprise', 'es' => 'Empresa', 'de' => 'Unternehmen', 'zh' => '公司', 'ja' => '会社'],
                'data_type' => 'string',
                'schema_type' => 'https://schema.org/worksFor',
                'is_system' => true,
            ]),
            'location' => ProfileAttribute::factory()->create([
                'key' => 'location',
                'name' => 'Location',
                'translations' => ['ar' => 'الموقع', 'fr' => 'Lieu', 'es' => 'Ubicación', 'de' => 'Standort', 'zh' => '位置', 'ja' => '所在地'],
                'data_type' => 'string',
                'schema_type' => 'https://schema.org/location',
                'is_system' => true,
            ]),
            'phone' => ProfileAttribute::factory()->create([
                'key' => 'phone',
                'name' => 'Phone',
                'translations' => ['ar' => 'الهاتف', 'fr' => 'Téléphone', 'es' => 'Teléfono', 'de' => 'Telefon', 'zh' => '电话', 'ja' => '電話'],
                'data_type' => 'string',
                'schema_type' => 'https://schema.org/telephone',
                'is_system' => true,
            ]),
        ];
    }

    private function createAccessLogs(array $users): void
    {
        $imhotep = $users[0]['user'];
        $nefertiti = $users[1]['user'];
        $cleopatra = $users[3]['user'];

        // Imhotep's logs
        AccessLog::create([
            'user_id' => $imhotep->id,
            'context_slug' => 'work',
            'requester' => 'anonymous',
            'status_code' => 200,
            'created_at' => now()->subDays(2),
        ]);

        AccessLog::create([
            'user_id' => $imhotep->id,
            'context_slug' => 'work',
            'requester' => $nefertiti->email,
            'status_code' => 200,
            'created_at' => now()->subDay(),
        ]);

        // Nefertiti's logs
        AccessLog::create([
            'user_id' => $nefertiti->id,
            'context_slug' => 'personal',
            'requester' => $cleopatra->email,
            'status_code' => 200,
            'created_at' => now()->subHours(5),
        ]);

        // Cleopatra's logs
        AccessLog::create([
            'user_id' => $cleopatra->id,
            'context_slug' => 'work',
            'requester' => 'anonymous',
            'status_code' => 200,
            'created_at' => now()->subHours(3),
        ]);

        // Failed access attempt
        AccessLog::create([
            'user_id' => $imhotep->id,
            'context_slug' => 'nonexistent',
            'requester' => 'anonymous',
            'status_code' => 404,
            'created_at' => now()->subHours(1),
        ]);
    }
}
