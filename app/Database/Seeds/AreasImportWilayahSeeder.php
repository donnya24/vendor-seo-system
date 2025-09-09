<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreasImportWilayahSeeder extends Seeder
{
    /** Base folder JSON EMSIFA (di-isi di run()) */
    private string $baseApiPath = '';

    public function run()
    {
        // Inisialisasi path (tanpa override constructor)
        $this->baseApiPath = rtrim(WRITEPATH, "/\\") . '/wilayah-json/api-wilayah-indonesia/api';

        // (opsional) keamanan: hentikan jika folder tidak ditemukan
        if (!is_dir($this->baseApiPath)) {
            echo "Folder JSON tidak ditemukan: {$this->baseApiPath}\n";
            echo "Pastikan submodule/ZIP ada di lokasi tersebut.\n";
            return;
        }

        // (opsional) untuk import besar
        @set_time_limit(0);
        // @ini_set('memory_limit', '1024M');

        $this->ensureRegions();      // 8 region + "Seluruh Indonesia"
        $this->importAllProvinces(); // prov → kab/kota → kec → desa/kel
    }

    /* --------------------------------------------------------
     * Utilities
     * ------------------------------------------------------*/
    private function readJson(string $path): array
    {
        if (!is_file($path)) return [];
        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function cleanKabKotaName(string $name): string
    {
        // Konsistensi: "Surabaya" (bukan "Kota Surabaya"), "Bandung" (bukan "Kabupaten Bandung")
        $name = preg_replace('/^\s*(Kabupaten|Kota Administrasi|Kota)\s+/iu', '', $name);
        return trim($name);
    }

    private function getOrCreateByCode(?string $code, string $name, string $type, ?int $parentId): int
    {
        // Cari berdasar code (jika ada)
        if ($code) {
            $row = $this->db->table('areas')->select('id')->where('code', $code)->get()->getRowArray();
            if ($row) return (int) $row['id'];
        }

        // Fallback by unique (parent_id + name + type)
        $b = $this->db->table('areas')->select('id')
            ->where('name', $name)
            ->where('type', $type);

        $parentId === null ? $b->where('parent_id', null) : $b->where('parent_id', $parentId);

        $row = $b->get()->getRowArray();
        if ($row) return (int) $row['id'];

        // Insert baru
        $this->db->table('areas')->insert([
            'name'       => $name,
            'type'       => $type,
            'code'       => $code,
            'parent_id'  => $parentId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);
        return (int) $this->db->insertID();
    }

    private function idOf(string $name, string $type, ?int $parentId = null): ?int
    {
        $b = $this->db->table('areas')->select('id')
            ->where('name', $name)
            ->where('type', $type);

        $parentId === null ? $b->where('parent_id', null) : $b->where('parent_id', $parentId);

        $row = $b->get()->getRowArray();
        return $row ? (int) $row['id'] : null;
    }

    /* --------------------------------------------------------
     * Step 1: Pastikan REGION
     * ------------------------------------------------------*/
    private function ensureRegions(): void
    {
        $regions = [
            'Seluruh Indonesia',
            'Sumatera',
            'Jawa',
            'Bali & Nusa Tenggara',
            'Kalimantan',
            'Sulawesi',
            'Maluku',
            'Papua',
        ];
        foreach ($regions as $rgn) {
            $this->getOrCreateByCode(null, $rgn, 'region', null);
        }
    }

    /* Pemetaan provinsi → region */
    private function regionForProvince(string $prov): string
    {
        $prov = mb_strtolower(trim($prov));

        $SUMATERA = [
            'aceh','sumatera utara','sumatera barat','riau','kepulauan riau','jambi','bengkulu',
            'sumatera selatan','kepulauan bangka belitung','lampung'
        ];
        $JAWA = [
            'banten','dki jakarta','daerah khusus ibu kota jakarta','jakarta',
            'jawa barat','jawa tengah','daerah istimewa yogyakarta','di yogyakarta','jawa timur'
        ];
        $BALNUS = ['bali','nusa tenggara barat','nusa tenggara timur'];
        $KALIM = ['kalimantan barat','kalimantan tengah','kalimantan selatan','kalimantan timur','kalimantan utara'];
        $SULAW = ['sulawesi utara','sulawesi tengah','sulawesi selatan','sulawesi tenggara','gorontalo','sulawesi barat'];
        $MALUK = ['maluku','maluku utara'];
        $PAPUA = ['papua','papua barat','papua barat daya','papua selatan','papua tengah','papua pegunungan'];

        if (in_array($prov, $SUMATERA, true)) return 'Sumatera';
        if (in_array($prov, $JAWA, true))     return 'Jawa';
        if (in_array($prov, $BALNUS, true))   return 'Bali & Nusa Tenggara';
        if (in_array($prov, $KALIM, true))    return 'Kalimantan';
        if (in_array($prov, $SULAW, true))    return 'Sulawesi';
        if (in_array($prov, $MALUK, true))    return 'Maluku';
        if (in_array($prov, $PAPUA, true))    return 'Papua';

        return 'Seluruh Indonesia';
    }

    /* --------------------------------------------------------
     * Step 2: Import seluruh provinsi dan turunannya
     * ------------------------------------------------------*/
    private function importAllProvinces(): void
    {
        $provincesPath = $this->baseApiPath . '/provinces.json';
        $provinces = $this->readJson($provincesPath);

        if (!$provinces) {
            echo "Tidak menemukan provinces.json di {$provincesPath}\n";
            return;
        }

        foreach ($provinces as $p) {
            $provCode = (string) $p['id'];
            $provName = (string) $p['name'];
            $regionName = $this->regionForProvince($provName);
            $regionId   = $this->idOf($regionName, 'region', null);

            // simpan provinsi (parent = region)
            $provId = $this->getOrCreateByCode($provCode, $provName, 'province', $regionId);

            // --- kabupaten/kota
            $this->importRegenciesForProvince($provId, $provCode);

            echo "Provinsi OK: {$provName}\n";
        }
    }

    private function importRegenciesForProvince(int $provId, string $provCode): void
    {
        $regencies = $this->readJson($this->baseApiPath . "/regencies/{$provCode}.json");
        foreach ($regencies as $r) {
            $rawName   = (string) $r['name'];
            $regCode   = (string) $r['id'];
            $isKab     = (bool) preg_match('/^\s*Kabupaten\b/i', $rawName);
            $isKota    = (bool) preg_match('/^\s*Kota\b/i', $rawName);

            $type      = $isKab ? 'regency' : ($isKota ? 'city' : 'regency');
            $name      = $this->cleanKabKotaName($rawName);

            $regId = $this->getOrCreateByCode($regCode, $name, $type, $provId);

            // --- kecamatan
            $this->importDistrictsForRegency($regId, $regCode);
        }
    }

    private function importDistrictsForRegency(int $regId, string $regCode): void
    {
        $districts = $this->readJson($this->baseApiPath . "/districts/{$regCode}.json");
        foreach ($districts as $d) {
            $distCode = (string) $d['id'];
            $distName = (string) $d['name'];

            $distId = $this->getOrCreateByCode($distCode, $distName, 'district', $regId);

            // --- desa/kelurahan
            $this->importVillagesForDistrict($distId, $distCode);
        }
    }

    private function importVillagesForDistrict(int $distId, string $distCode): void
    {
        $villages = $this->readJson($this->baseApiPath . "/villages/{$distCode}.json");
        foreach ($villages as $v) {
            $vilCode = (string) $v['id'];
            $vilName = (string) $v['name'];
            $this->getOrCreateByCode($vilCode, $vilName, 'village', $distId);
        }
    }
}
