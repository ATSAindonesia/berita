<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreatePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:create 
                            {password? : Password yang ingin disimpan ke database} 
                            {--replace : Hapus semua password lama dan simpan password baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat dan menyimpan password baru ke database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $password = $this->argument('password');

        if (! $password) {
            $password = $this->secret('Masukkan password: ');

            if (empty($password)) {
                $this->error('Password tidak boleh kosong!');

                return Command::FAILURE;
            }

            $confirm = $this->secret('Konfirmasi password: ');

            if ($password !== $confirm) {
                $this->error('Konfirmasi password tidak cocok!');

                return Command::FAILURE;
            }
        }

        $existingCount = User::count();

        if ($this->option('replace')) {
            User::truncate();
            $this->warn('Password lama telah dihapus (--replace).');
        } elseif ($existingCount > 0 && $this->input->isInteractive()) {
            if ($this->confirm("Sudah ada {$existingCount} password di database. Apakah ingin mengganti password lama?", true)) {
                User::truncate();
                $this->warn('Password lama telah dibersihkan.');
            }
        }

        // Simpan ke database (di-hash otomatis oleh casts 'password' => 'hashed' di User.php)
        $user = User::create([
            'password' => $password,
        ]);

        $this->newLine();
        $this->info('✓ Password berhasil disimpan ke database!');
        $this->line("  Tabel   : <comment>{$user->getTable()}</comment>");
        $this->line('  Status  : <info>Aktif & Terenkripsi (Hashed)</info>');
        $this->newLine();

        return Command::SUCCESS;
    }
}
