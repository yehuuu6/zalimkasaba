<?php

namespace Database\Seeders;

use App\Models\ZalimKasaba\Lobby;
use App\Models\User;
use App\Models\ZalimKasaba\GameRole;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Eren Aydın',
            'username' => 'yehuuu6',
            'gender' => 'erkek',
            'email' => 'eren@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Melek Aydın',
            'username' => 'melekisnotthere',
            'gender' => 'kadın',
            'email' => 'melek@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Mehmet Aydın',
            'username' => 'mehmetaydin',
            'gender' => 'erkek',
            'email' => 'test@example.com'
        ]);

        $godfather = GameRole::create([
            'icon' => '⚜️',
            'name' => 'Baron',
            'enum' => 'godfather',
        ]);

        $mafioso = GameRole::create([
            'icon' => '💀',
            'name' => 'Tetikçi',
            'enum' => 'mafioso',
        ]);

        $janitor = GameRole::create([
            'icon' => '🧽',
            'name' => 'Temizlikçi',
            'enum' => 'janitor',
            'ability_limit' => 3,
        ]);

        $doctor = GameRole::create([
            'icon' => '🩺',
            'name' => 'Doktor',
            'enum' => 'doctor',
        ]);

        $lookout = GameRole::create([
            'icon' => '👀',
            'name' => 'Dikizci',
            'enum' => 'lookout',
        ]);

        $guard = GameRole::create([
            'icon' => '🔦',
            'name' => 'Bekçi',
            'enum' => 'guard',
        ]);

        $hunter = GameRole::create([
            'icon' => '🏹',
            'name' => 'Avcı',
            'enum' => 'hunter',
            'ability_limit' => 3,
        ]);

        $witch = GameRole::create([
            'icon' => '🔮',
            'name' => 'Cadı',
            'enum' => 'witch',
            'ability_limit' => 2,
        ]);

        $angel = GameRole::create([
            'icon' => '🌟',
            'name' => 'Melek',
            'enum' => 'angel',
            'ability_limit' => 3,
        ]);

        $jester = GameRole::create([
            'icon' => '🤡',
            'name' => 'Zibidi',
            'enum' => 'jester',
            'ability_limit' => 1,
        ]);

        $roles = GameRole::all();

        $lobbies = Lobby::factory(10)->create();

        // Attach random roles to each lobby (one lobby can have more than one same role)
        $lobbies->each(function ($lobby) use ($roles) {
            $roleCount = rand(6, 15);
            for ($i = 0; $i < $roleCount; $i++) {
                $lobby->roles()->attach($roles->random());
            }

            $lobby->update(['max_players' => $roleCount]);
        });
    }
}
