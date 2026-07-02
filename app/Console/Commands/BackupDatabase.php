<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Cadangkan basis data MySQL ke storage/app/backups menggunakan mysqldump.
 *
 * Kredensial diberikan lewat berkas --defaults-extra-file sementara (bukan
 * argumen baris perintah) agar kata sandi tidak terlihat di daftar proses.
 * Dijadwalkan otomatis di routes/console.php (harian 23.00).
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep= : Jumlah berkas backup terbaru yang dipertahankan}';

    protected $description = 'Cadangkan basis data MySQL ke storage/app/backups (mysqldump).';

    public function handle(): int
    {
        $koneksi = config('database.default');
        $db = config("database.connections.{$koneksi}");

        if (($db['driver'] ?? null) !== 'mysql') {
            $this->error('Perintah ini hanya mendukung koneksi MySQL.');

            return self::FAILURE;
        }

        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'backups');

        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error('Gagal membuat folder backup: '.$dir);

            return self::FAILURE;
        }

        $namaFile = 'backup-'.$db['database'].'-'.now()->format('Ymd-His').'.sql';
        $tujuan = $dir.DIRECTORY_SEPARATOR.$namaFile;

        // Kredensial via berkas sementara (chmod 600) — tidak lewat argv.
        $cnf = tempnam(sys_get_temp_dir(), 'mydump');
        @chmod($cnf, 0600);
        file_put_contents($cnf, implode("\n", [
            '[client]',
            'host="'.($db['host'] ?? '127.0.0.1').'"',
            'port="'.($db['port'] ?? '3306').'"',
            'user="'.($db['username'] ?? 'root').'"',
            'password="'.($db['password'] ?? '').'"',
            '',
        ]));

        $proses = new Process([
            (string) config('arsip.backup.mysqldump_path', 'mysqldump'),
            '--defaults-extra-file='.$cnf,
            '--single-transaction',
            '--quick',
            '--default-character-set='.($db['charset'] ?? 'utf8mb4'),
            $db['database'],
        ], timeout: (int) config('arsip.backup.timeout', 600));

        $fh = fopen($tujuan, 'wb');

        try {
            $proses->run(function (string $type, string $buffer) use ($fh) {
                if ($type === Process::OUT) {
                    fwrite($fh, $buffer);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Backup DB gagal dijalankan: '.$e->getMessage());
            $this->error('mysqldump tidak dapat dijalankan (proc_open aktif? path benar?): '.$e->getMessage());
            $proses = null;
        } finally {
            fclose($fh);
            @unlink($cnf);
        }

        if ($proses === null || ! $proses->isSuccessful() || ! is_file($tujuan) || filesize($tujuan) === 0) {
            @unlink($tujuan);
            $galat = $proses?->getErrorOutput() ?? '';
            Log::warning('Backup DB gagal: '.$galat);
            $this->error('Backup gagal. '.trim($galat));

            return self::FAILURE;
        }

        $this->pangkasLama($dir, (int) ($this->option('keep') ?? config('arsip.backup.keep', 14)));

        $ukuran = number_format(filesize($tujuan) / 1024, 1);
        $this->info("Backup tersimpan: {$namaFile} ({$ukuran} KB)");

        return self::SUCCESS;
    }

    /**
     * Sisakan $keep berkas backup terbaru; hapus selebihnya.
     */
    private function pangkasLama(string $dir, int $keep): void
    {
        $keep = max($keep, 1);
        $files = glob($dir.DIRECTORY_SEPARATOR.'backup-*.sql') ?: [];

        // Nama memuat timestamp → urut menurun = terbaru dulu.
        rsort($files);

        foreach (array_slice($files, $keep) as $lama) {
            @unlink($lama);
        }
    }
}
