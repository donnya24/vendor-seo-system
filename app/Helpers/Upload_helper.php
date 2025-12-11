<?php
// app/Helpers/upload_helper.php

use CodeIgniter\Files\File;

if (! function_exists('commissions_upload_dir')) {
    /** Direktori upload bukti komisi — konsisten di FCPATH/uploads/commissions */
    function commissions_upload_dir(): string
    {
        return rtrim(FCPATH, '/\\') . '/uploads/commissions';
    }
}

if (! function_exists('ensure_dir')) {
    /** Pastikan direktori ada & bisa ditulis */
    function ensure_dir(string $dir): bool
    {
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return is_dir($dir) && is_writable($dir);
    }
}

if (! function_exists('allowed_commission_exts')) {
    /** Ekstensi yang diizinkan */
    function allowed_commission_exts(): array
    {
        return ['pdf','jpg','jpeg','png'];
    }
}

if (! function_exists('is_allowed_upload')) {
    /** Cek mime/ext dan ukuran file */
    function is_allowed_upload(\CodeIgniter\HTTP\Files\UploadedFile $file, int $maxBytes = 10_485_760): bool // 10MB
    {
        if (! $file->isValid()) return false;
        if ($file->getSize() > $maxBytes) return false;

        $ext  = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
        if (! in_array($ext, allowed_commission_exts(), true)) return false;

        // Optional: perketat mime
        $mime = $file->getMimeType();
        $okMime = [
            'application/pdf','image/jpeg','image/png','image/pjpeg'
        ];
        return in_array($mime, $okMime, true);
    }
}

if (! function_exists('safe_random_filename')) {
    /** Nama file acak yang aman (dengan ekstensi) */
    function safe_random_filename(string $origName): string
    {
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $hash = bin2hex(random_bytes(8));
        return $hash . ($ext ? '.' . $ext : '');
    }
}

if (! function_exists('get_commission_proof_path')) {
    /** Cari path file bukti (cek kandidat di FCPATH) */
    function get_commission_proof_path(?string $filename): ?string
    {
        if (! $filename) return null;
        $p = commissions_upload_dir() . '/' . $filename;
        return is_file($p) ? $p : null;
    }
}

if (! function_exists('remove_commission_proof')) {
    /** Hapus file bukti secara aman + log error jika gagal */
    function remove_commission_proof(?string $filename): bool
    {
        $path = get_commission_proof_path($filename);
        if (! $path) return false;
        if (! is_writable($path)) {
            log_message('error', 'Proof not writable: {path}', ['path' => $path]);
            return false;
        }
        if (! @unlink($path)) {
            log_message('error', 'Failed unlink proof: {path}', ['path' => $path]);
            return false;
        }
        return true;
    }
}

if (! function_exists('save_commission_upload')) {
    /**
     * Simpan upload bukti komisi, kembalikan [boolean success, string|null filename, string|null error]
     */
    function save_commission_upload(\CodeIgniter\HTTP\Files\UploadedFile $file): array
    {
        $dir = commissions_upload_dir();
        if (! ensure_dir($dir)) {
            return [false, null, 'Folder upload tidak dapat dibuat/diakses.'];
        }
        if (! is_allowed_upload($file)) {
            return [false, null, 'Tipe/ukuran file tidak diizinkan.'];
        }

        $name = safe_random_filename($file->getName());
        try {
            $file->move($dir, $name);
            return [true, $name, null];
        } catch (\Throwable $e) {
            log_message('error', 'Move upload failed: {err}', ['err' => $e->getMessage()]);
            return [false, null, 'Gagal menyimpan file.'];
        }
    }
}
