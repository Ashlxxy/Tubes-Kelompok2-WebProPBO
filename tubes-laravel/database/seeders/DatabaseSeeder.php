<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Song;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@ukmband.telkom',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create User
        User::create([
            'name' => 'User Demo',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Seed Songs
        $songs = [
            [
                'title' => 'Lust',
                'artist' => "Bachelor's Thrill",
                'description' => "Energi eksplosif dan riff cepat yang menggambarkan kebebasan mahasiswa.",
                'cover_path' => 'assets/img/c1.jpg',
                'file_path' => 'assets/songs/Lust.wav',
                'plays' => 120,
                'likes' => 45,
            ],
            [
                'title' => 'Form',
                'artist' => 'Coral',
                'description' => "Eksperimen suara yang menggambarkan bentuk dan warna bawah laut.",
                'cover_path' => 'assets/img/c2.jpg',
                'file_path' => 'assets/songs/coral_form.wav',
                'plays' => 85,
                'likes' => 30,
            ],
            [
                'title' => 'Strangled',
                'artist' => 'Dystopia',
                'description' => "Nuansa gelap yang menggambarkan kekacauan batin dan tekanan sosial.",
                'cover_path' => 'assets/img/c3.jpg',
                'file_path' => 'assets/songs/Strangled.wav',
                'plays' => 200,
                'likes' => 90,
            ],
            [
                'title' => 'Revoir',
                'artist' => 'Elisya_au',
                'description' => "Balada melankolis tentang perpisahan dan kenangan yang tak terlupakan.",
                'cover_path' => 'assets/img/c4.jpg',
                'file_path' => 'assets/songs/revoir.wav',
                'plays' => 150,
                'likes' => 60,
            ],
            [
                'title' => 'Prisoner',
                'artist' => 'Secrets.',
                'description' => "Karya eksperimental dengan pesan tentang kebebasan dan rahasia terdalam.",
                'cover_path' => 'assets/img/c5.jpg',
                'file_path' => 'assets/songs/Prisoner.wav',
                'plays' => 95,
                'likes' => 40,
            ],
            [
                'title' => 'Langit Kelabu',
                'artist' => 'The Harper',
                'description' => "Harmoni lembut dengan lirik puitis tentang hujan dan harapan.",
                'cover_path' => 'assets/img/c6.jpg',
                'file_path' => 'assets/songs/Langit Kelabu.wav',
                'plays' => 110,
                'likes' => 55,
            ],
            [
                'title' => 'The Overtrain - New World',
                'artist' => 'The Overtrain',
                'description' => "Irama cepat dengan semangat membangun dunia baru yang lebih baik.",
                'cover_path' => 'assets/img/c7.jpg',
                'file_path' => 'assets/songs/NewWorld.wav',
                'plays' => 180,
                'likes' => 75,
            ],
        ];

        foreach ($songs as $song) {
            Song::create($song);
        }
    }
}
