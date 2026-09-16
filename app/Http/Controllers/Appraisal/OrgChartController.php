<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Section;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrgChartController extends Controller
{
    public function index(Request $request)
    {
        return view('appraisal.org-chart.index', $this->build($request));
    }

    public function pdf(Request $request)
    {
        $data = $this->build($request);

        abort_if(! $data['selectedCompany'], 404);

        $data['pdfGroups'] = $this->flattenForPdf($data['tree'], $data['showBranchTier'], $data['showDivisionTier']);

        // Landscape — diagram struktur lebih lebar daripada tinggi.
        $pdf = Pdf::loadView('appraisal.org-chart.pdf', $data)->setPaper('a4', 'landscape');

        $filename = 'struktur-organisasi-' . str($data['selectedCompany']->short_name ?: $data['selectedCompany']->name)->slug() . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Bangun data bagan: Company -> [Divisi ->] Departemen -> [Section ->] Level -> Jabatan -> Karyawan,
     * plus jabatan kosong (vacant) per level. Tingkat Divisi & Section otomatis
     * disembunyikan kalau company/departemen itu belum punya data Divisi/Section
     * (supaya bagan tetap ringkas selama HR belum melengkapi struktur baru).
     */
    private function build(Request $request): array
    {
        $companies = Company::where('is_active', true)->orderBy('id')->get();

        $selectedCompany = $request->filled('company')
            ? $companies->firstWhere('id', (int) $request->get('company'))
            : $companies->first();

        $branches    = collect();
        $divisions   = collect();
        $departments = collect();
        $sections    = collect();
        $tree        = collect();
        $reportsByManager = collect();
        $showDivisionTier  = false;
        $showBranchTier    = false;

        $filters = [
            'branch'     => $request->filled('branch') ? (int) $request->branch : null,
            'division'   => $request->filled('division') ? (int) $request->division : null,
            'department' => $request->filled('department') ? (int) $request->department : null,
            'section'    => $request->filled('section') ? (int) $request->section : null,
        ];

        if ($selectedCompany) {
            // company_id null di departments/positions berarti "berlaku lintas
            // perusahaan" (konvensi existing — lihat menu Departemen/Jabatan),
            // jadi ikut disertakan, bukan cuma yang company_id-nya persis sama.
            $departments = Department::where(fn ($q) => $q->where('company_id', $selectedCompany->id)->orWhereNull('company_id'))
                ->where('is_active', true)->orderBy('name')->get();
            $divisions   = Division::where('company_id', $selectedCompany->id)->where('is_active', true)->orderBy('name')->get();
            $sections    = Section::whereIn('department_id', $departments->pluck('id'))->where('is_active', true)->orderBy('name')->get();
            $branches    = Branch::where('company_id', $selectedCompany->id)->where('is_active', true)->orderBy('name')->get();

            $employees = Employee::where('company_id', $selectedCompany->id)
                ->where('is_active', true)
                ->when($filters['branch'],     fn ($q, $v) => $q->where('branch_id', $v))
                ->when($filters['division'],   fn ($q, $v) => $q->where('division_id', $v))
                ->when($filters['department'], fn ($q, $v) => $q->where('department_id', $v))
                ->when($filters['section'],    fn ($q, $v) => $q->where('section_id', $v))
                ->with(['branchLocation', 'division', 'department', 'section', 'position', 'level'])
                ->orderBy('name')
                ->get();

            $positions = Position::where(fn ($q) => $q->where('company_id', $selectedCompany->id)->orWhereNull('company_id'))
                ->where('is_active', true)
                ->when($filters['division'], function ($q, $v) use ($departments) {
                    $q->whereIn('department_id', $departments->where('division_id', $v)->pluck('id'));
                })
                ->when($filters['department'], fn ($q, $v) => $q->where('department_id', $v))
                ->when($filters['section'],    fn ($q, $v) => $q->where('section_id', $v))
                ->with('level')
                ->get();

            // Filter Cabang eksplisit (bukan tier Cabang otomatis) -> posisi
            // tanpa Departemen tetap perlu di-scope ke Cabang terpilih saja,
            // pola sama dgn buildBranches() (lihat komentar di sana), supaya
            // "Kepala Cabang X" milik Cabang lain tidak nyasar jadi vacant.
            if ($filters['branch']) {
                $positions = $positions->filter(
                    fn ($p) => $p->department_id || ! $p->branch_id || $p->branch_id === $filters['branch']
                );
            }

            // Karyawan yang "Atasan Langsung"-nya sudah diisi (dan atasannya
            // juga aktif & tampil di bagan ini) digambar menempel di bawah
            // kotak atasannya langsung, bukan dikelompokkan generik per Level/Jabatan
            // — KECUALI atasannya seorang Direksi (CEO/CFO/dst): supaya kotak
            // Departemen (Procurement, IT, dst) tidak lenyap gara-gara Manager-nya
            // kini lapor ke Direksi, dept itu tetap jadi "akar" dept-nya sendiri
            // dan digambar bercabang di bawah kotak Direksi (lihat buildDepartmentsOrDireksi()).
            $direksiIds = $employees->filter(fn ($e) => $e->level?->name === 'Direksi')->pluck('id')->flip();
            $activeIds = $employees->pluck('id')->flip();
            $reportsByManager = $employees
                ->filter(fn ($e) => $e->manager_id && $activeIds->has($e->manager_id))
                ->groupBy('manager_id');
            $linkedIds = $reportsByManager
                ->filter(fn ($reports, $managerId) => ! $direksiIds->has((int) $managerId))
                ->flatten(1)->pluck('id')->flip();
            $groupedEmployees = $employees->reject(fn ($e) => $linkedIds->has($e->id));

            // ">1" (bukan isNotEmpty) supaya 1 kotak Cabang/Divisi tunggal tidak
            // dibungkus tier semu — pola sama fix Section (lihat buildDepartments()).
            $showBranchTier   = $groupedEmployees->pluck('branch_id')->filter()->unique()->count() > 1;
            $showDivisionTier = $employees->pluck('division_id')->filter()->isNotEmpty();

            // Divisi lintas-Direksi: kalau seorang Direksi (mis. "Direktur Utama" di
            // Divisi BOD) punya bawahan yang divisinya BEDA (mis. Manager Commercial
            // di Divisi Commercial), Divisi si bawahan digambar bercabang di bawah
            // kotak Direksi itu (bukan sejajar sbg divisi sendiri) — supaya struktur
            // "Commercial/Logistik lapor ke BOD" tampil sesuai org chart, bukan cuma
            // tercatat di data Jabatan. Peta ini dihitung sekali di sini (butuh
            // pandangan lintas-divisi penuh), dipakai berulang oleh buildDivisions().
            $divisionParentDireksi = $this->mapDivisionsToParentDireksi($employees);

            // Cabang lintas-Direksi: pola SAMA PERSIS dengan Divisi lintas-Direksi
            // di atas, tapi per Cabang — mis. "Kepala Cabang Jakarta" & "Kepala
            // Cabang Palembang" yang Atasan Langsung-nya seorang Direksi di HO
            // digambar bercabang di bawah kotak Direksi itu, bukan sejajar sbg
            // Cabang sendiri.
            $branchParentDireksi = $this->mapBranchesToParentDireksi($employees);

            $tree = $showBranchTier
                ? $this->buildBranches($groupedEmployees, $employees, $positions, $departments, $divisionParentDireksi, $branchParentDireksi, $groupedEmployees)
                : ($showDivisionTier
                    ? $this->buildDivisions($groupedEmployees, $employees, $positions, $departments, $divisionParentDireksi, $groupedEmployees, $branchParentDireksi)
                    : $this->buildDepartmentsOrDireksi($groupedEmployees, $employees, $positions));
        }

        $canEditOrg = $request->user()?->can('org-structure.edit') ?? false;

        return compact(
            'companies', 'selectedCompany', 'branches', 'divisions', 'departments', 'sections',
            'filters', 'tree', 'reportsByManager', 'showBranchTier', 'showDivisionTier', 'canEditOrg'
        );
    }

    /**
     * Petakan division_id -> employee Direksi "induk"-nya, kalau ada bawahan
     * (manager_id) divisi itu yang atasannya seorang Direksi di divisi LAIN.
     * Dipakai buildDivisions()/buildDireksiNode() supaya divisi seperti itu
     * digambar bercabang di bawah kotak Direksi tsb, bukan sejajar sbg divisi
     * sendiri. Kalau lebih dari satu Direksi "menarik" divisi yang sama, yang
     * pertama ketemu yang dipakai (kasus jarang, org chart tetap harus punya
     * satu induk saja per divisi).
     *
     * SENGAJA skip kalau Cabang bawahan itu JUGA beda dari Cabang Direksi-nya
     * — itu tandanya seluruh Cabang (bukan cuma 1 Divisi di dalamnya) yang
     * lapor ke Direksi ini, jadi biar mapBranchesToParentDireksi() saja yang
     * menangani (Cabang otomatis membawa semua Divisi di dalamnya lewat
     * buildDivisions() bersarang). Tanpa ini, Divisi seperti itu ditarik LEPAS
     * langsung ke Direksi (skip kotak Cabang-nya) SEKALIGUS Cabang-nya ditarik
     * terpisah juga — dobel & salah susun (mis. "Commercial" di Cabang CRS
     * nempel langsung ke BOD, padahal harusnya bersarang di dalam kotak CRS).
     */
    private function mapDivisionsToParentDireksi(Collection $employees): Collection
    {
        $direksiById = $employees->filter(fn ($e) => $e->level?->name === 'Direksi')->keyBy('id');

        $map = collect();
        foreach ($employees as $e) {
            if (! $e->manager_id || $map->has($e->division_id ?? 0)) {
                continue;
            }
            $manager = $direksiById->get($e->manager_id);
            if (! $manager || (int) $manager->division_id === (int) $e->division_id) {
                continue;
            }
            if ((int) $manager->branch_id !== (int) $e->branch_id) {
                continue;
            }
            $map->put($e->division_id ?? 0, $manager);
        }

        return $map;
    }

    /**
     * Petakan branch_id -> employee Direksi "induk"-nya — pola SAMA PERSIS
     * dengan mapDivisionsToParentDireksi(), cuma per Cabang. Dipakai
     * buildBranches()/buildDireksiNode() supaya Cabang seperti itu digambar
     * bercabang di bawah kotak Direksi tsb (mis. "Kepala Cabang Jakarta" &
     * "Kepala Cabang Palembang" yang Atasan Langsung-nya seorang Direksi di
     * HO), bukan sejajar sbg Cabang sendiri.
     */
    private function mapBranchesToParentDireksi(Collection $employees): Collection
    {
        $direksiById = $employees->filter(fn ($e) => $e->level?->name === 'Direksi')->keyBy('id');

        $map = collect();
        foreach ($employees as $e) {
            if (! $e->manager_id || $map->has($e->branch_id ?? 0)) {
                continue;
            }
            $manager = $direksiById->get($e->manager_id);
            if ($manager && (int) $manager->branch_id !== (int) $e->branch_id) {
                $map->put($e->branch_id ?? 0, $manager);
            }
        }

        return $map;
    }

    /**
     * Tier Cabang (lokasi) — di ATAS Divisi/Departemen (Company -> Cabang ->
     * [Divisi ->] Departemen -> ...). Tiap cabang menyusun ulang tier di
     * bawahnya sendiri (Divisi kalau ada, atau langsung Departemen/Direksi)
     * supaya cabang HO (yang biasanya menyimpan struktur Direksi lengkap PT.
     * Pro Energi) tidak tercampur dengan tim kecil di cabang lain.
     *
     * $divisionParentDireksi/$branchParentDireksi diteruskan APA ADANYA ke
     * tier di bawahnya (bukan dihitung ulang per-Cabang) — peta ini butuh
     * pandangan lintas-Cabang penuh (dihitung sekali di build()), supaya
     * Divisi/Cabang lain yang "ditarik" ke seorang Direksi tetap kebaca
     * walau Direksi & bawahannya beda Cabang.
     */
    private function buildBranches(
        Collection $scopeEmployees, Collection $allEmployees, Collection $positions, Collection $allDepartments,
        ?Collection $divisionParentDireksi = null, ?Collection $branchParentDireksi = null,
        ?Collection $fullRootEmployees = null, ?Collection $companyEmployees = null
    ): Collection {
        $branchParentDireksi ??= collect();
        // $allEmployees bisa sudah DIPERSEMPIT ke 1 Cabang/Divisi lain (dipanggil
        // dari buildDireksiNode() childBranches, lewat buildDivisions() di
        // tengah) — utk hitung 'total' Cabang yang DITARIK ke sini (mis. Jakarta/
        // Palembang) butuh pool company PENUH, bukan yg sudah dipersempit itu.
        $companyEmployees ??= $allEmployees;

        // Cabang yang "ditarik" jadi anak Direksi di Cabang lain TIDAK dirender
        // sbg sibling top-level di sini — nanti muncul bercabang lewat
        // buildDireksiNode() (lihat childBranches di sana).
        $topLevelEmployees = $scopeEmployees->reject(
            fn ($e) => $branchParentDireksi->has($e->branch_id ?? 0)
        );

        return $topLevelEmployees->groupBy('branch_id')
            ->map(function (Collection $branchEmployees, $branchKey) use ($allEmployees, $positions, $allDepartments, $divisionParentDireksi, $branchParentDireksi, $fullRootEmployees, $companyEmployees) {
                $branchId = $branchKey !== '' ? (int) $branchKey : null;
                $branch   = $branchEmployees->first()->branchLocation;

                $branchAllEmployees = $branchId
                    ? $companyEmployees->where('branch_id', $branchId)
                    : $allEmployees->whereNull('branch_id');

                $showDivisionTier = $branchEmployees->pluck('division_id')->filter()->unique()->count() > 1;

                // Posisi TANPA Departemen (mis. CEO/CFO, "Kepala Cabang X")
                // ditautkan ke Cabang lewat positions.branch_id — jadi
                // pool "jabatan kosong" per Cabang tidak ikut memuat posisi
                // serupa milik Cabang lain. Posisi yang sudah terikat
                // Departemen tetap terlihat di semua Cabang (di-scope lewat
                // Departemen seperti biasa, bukan Cabang).
                $branchPositions = $positions->filter(
                    fn ($p) => $p->department_id || ! $p->branch_id || $p->branch_id === $branchId
                );

                return [
                    'branch'           => $branch,
                    'total'            => $branchAllEmployees->count(),
                    'showDivisionTier' => $showDivisionTier,
                    'departments'      => $showDivisionTier
                        ? $this->buildDivisions($branchEmployees, $branchAllEmployees, $branchPositions, $allDepartments, $divisionParentDireksi, $fullRootEmployees, $branchParentDireksi, $companyEmployees)
                        : $this->buildDepartmentsOrDireksi(
                            $branchEmployees, $branchAllEmployees, $branchPositions,
                            $allEmployees, $positions, $allDepartments, $divisionParentDireksi, $fullRootEmployees, $branchParentDireksi, $companyEmployees
                        ),
                ];
            })
            ->sortBy(fn ($b) => ($b['branch']->name ?? 'zzz') === 'HO' ? '' : ($b['branch']->name ?? 'zzz'))
            ->values();
    }

    private function buildDivisions(Collection $scopeEmployees, Collection $allEmployees, Collection $positions, Collection $allDepartments, ?Collection $divisionParentDireksi = null, ?Collection $fullRootEmployees = null, ?Collection $branchParentDireksi = null, ?Collection $companyEmployees = null): Collection
    {
        $divisionParentDireksi ??= collect();
        $companyEmployees ??= $allEmployees;

        // Divisi yang "ditarik" jadi anak Direksi di divisi lain TIDAK dirender
        // sbg sibling top-level di sini — nanti muncul bercabang lewat
        // buildDireksiNode() (lihat childDivisions di sana).
        $topLevelEmployees = $scopeEmployees->reject(
            fn ($e) => $divisionParentDireksi->has($e->division_id ?? 0)
        );

        return $topLevelEmployees->groupBy('division_id')
            ->map(function (Collection $divEmployees, $divKey) use ($allEmployees, $positions, $allDepartments, $divisionParentDireksi, $fullRootEmployees, $branchParentDireksi, $companyEmployees) {
                $divId    = $divKey !== '' ? (int) $divKey : null;
                $division = $divEmployees->first()->division;

                $divAllEmployees = $divId
                    ? $allEmployees->where('division_id', $divId)
                    : $allEmployees->whereNull('division_id');

                $deptIds = $divId
                    ? $allDepartments->where('division_id', $divId)->pluck('id')
                    : $allDepartments->whereNull('division_id')->pluck('id');
                $divPositions = $positions->whereIn('department_id', $deptIds);

                return [
                    'division'    => $division,
                    'total'       => $divAllEmployees->count(),
                    'departments' => $this->buildDepartmentsOrDireksi(
                        $divEmployees, $divAllEmployees, $divPositions,
                        $allEmployees, $positions, $allDepartments, $divisionParentDireksi, $fullRootEmployees, $branchParentDireksi, $companyEmployees
                    ),
                ];
            })
            ->sortBy(fn ($d) => $d['division']->name ?? 'zzz')
            ->values();
    }

    /**
     * Bungkus buildDepartments() supaya Direksi (CEO/CFO/dst, Level "Direksi")
     * digambar sebagai tier TERSENDIRI di atas Departemen: departemen yang
     * "akar"-nya (Manager tanpa atasan lain yg tampil, lihat pengecualian
     * $linkedIds di build()) lapor ke seorang Direksi dicabangkan di bawah
     * kotak Direksi itu, bukan sejajar departemen lain. Generik — dipakai di
     * SEMUA cakupan (company, per-Cabang, per-Divisi) supaya perlakuannya
     * konsisten di seluruh bagan, bukan cuma satu tempat.
     */
    private function buildDepartmentsOrDireksi(
        Collection $scopeEmployees, Collection $allEmployees, Collection $positions,
        ?Collection $fullEmployees = null, ?Collection $fullPositions = null,
        ?Collection $fullDepartments = null, ?Collection $divisionParentDireksi = null,
        ?Collection $fullRootEmployees = null, ?Collection $branchParentDireksi = null,
        ?Collection $companyEmployees = null
    ): Collection {
        $direksiIds = $scopeEmployees->filter(fn ($e) => $e->level?->name === 'Direksi')->pluck('id')->flip();

        if ($direksiIds->isEmpty()) {
            return $this->buildDepartments($scopeEmployees, $allEmployees, $positions)
                ->map(fn ($d) => ['type' => 'department'] + $d)
                ->values();
        }

        $direksiLinkedRoots = $scopeEmployees->filter(
            fn ($e) => $e->manager_id && $direksiIds->has($e->manager_id) && ! $direksiIds->has($e->id)
        );
        $direksiRoots = $scopeEmployees->filter(
            fn ($e) => $direksiIds->has($e->id) && (! $e->manager_id || ! $direksiIds->has($e->manager_id))
        );
        $plainRoots = $scopeEmployees->reject(
            fn ($e) => $direksiIds->has($e->id) || ($e->manager_id && $direksiIds->has($e->manager_id))
        );

        $direksiNodes = $direksiRoots
            ->map(fn ($d) => ['type' => 'direksi'] + $this->buildDireksiNode(
                $d, $scopeEmployees, $direksiLinkedRoots, $allEmployees, $positions, $direksiIds,
                $fullEmployees, $fullPositions, $fullDepartments, $divisionParentDireksi, $fullRootEmployees,
                $branchParentDireksi, $companyEmployees
            ))
            ->values();

        $plainNodes = $this->buildDepartments($plainRoots, $allEmployees, $positions)
            ->map(fn ($d) => ['type' => 'department'] + $d);

        return $direksiNodes->concat($plainNodes)->values();
    }

    /**
     * 1 kotak Direksi: departemen yang manager root-nya lapor ke dia (dibangun
     * lewat buildDepartments() biasa, tak berubah), + Direksi lain yg lapor ke
     * dia (mis. CFO -> CEO), dibangun rekursif dgn pola sama, + Divisi LAIN yang
     * "ditarik" ke sini (lihat mapDivisionsToParentDireksi()) — mis. Divisi
     * Commercial/Logistik bercabang di bawah Direktur Utama (Divisi BOD) — +
     * Cabang LAIN yang "ditarik" ke sini (mapBranchesToParentDireksi()) — mis.
     * Kepala Cabang Jakarta/Palembang bercabang di bawah Direksi di HO.
     */
    private function buildDireksiNode(
        Employee $direksi, Collection $scopeEmployees, Collection $direksiLinkedRoots, Collection $allEmployees, Collection $positions, Collection $direksiIds,
        ?Collection $fullEmployees = null, ?Collection $fullPositions = null,
        ?Collection $fullDepartments = null, ?Collection $divisionParentDireksi = null,
        ?Collection $fullRootEmployees = null, ?Collection $branchParentDireksi = null,
        ?Collection $companyEmployees = null
    ): array {
        $deptRoots    = $direksiLinkedRoots->where('manager_id', $direksi->id);
        $childDireksi = $scopeEmployees->filter(fn ($e) => $direksiIds->has($e->id) && $e->manager_id === $direksi->id);

        // Divisi lain yang "ditarik" ke Direksi ini (mapDivisionsToParentDireksi) —
        // dibangun dari cakupan PENUH (bukan $scopeEmployees, yg sudah dipersempit
        // ke 1 divisi oleh buildDivisions() pemanggil), supaya bawahan lintas-divisi
        // ikut kebaca.
        $childDivisionIds = ($divisionParentDireksi ?? collect())
            ->filter(fn ($d) => $d->id === $direksi->id)
            ->keys();
        $childDivisions = collect();
        if ($childDivisionIds->isNotEmpty() && $fullEmployees && $fullPositions && $fullDepartments) {
            // PENTING: ambil dari $fullRootEmployees (sudah dikecualikan bawahan yg
            // "Atasan Langsung"-nya non-Direksi — sama seperti $groupedEmployees di
            // build()), BUKAN $fullEmployees mentah — supaya karyawan yang sudah
            // digambar rekursif di bawah manager-nya (lihat _position-node.blade.php)
            // tidak IKUT dobel di panel Level/Jabatan datar (buildLevelPanel()).
            $rootPool = $fullRootEmployees ?? $fullEmployees;
            $childScopeEmployees = $rootPool->filter(
                fn ($e) => $childDivisionIds->contains((int) ($e->division_id ?? 0))
            );
            // Divisi2 ini SUDAH "diresolve" jadi anak Direksi ini — hapus dari peta
            // sebelum rekursi, supaya buildDivisions() di bawah tidak menganggapnya
            // masih perlu ditarik lagi (groupBy-nya jadi kosong kalau tidak dihapus).
            $remainingMap = ($divisionParentDireksi ?? collect())->except($childDivisionIds->all());
            $childDivisions = $this->buildDivisions(
                $childScopeEmployees, $fullEmployees, $fullPositions, $fullDepartments, $remainingMap, $fullRootEmployees, $branchParentDireksi, $companyEmployees
            );
        }

        // Cabang lain yang "ditarik" ke Direksi ini (mapBranchesToParentDireksi) —
        // pola sama persis childDivisions di atas.
        $childBranchIds = ($branchParentDireksi ?? collect())
            ->filter(fn ($d) => $d->id === $direksi->id)
            ->keys();
        $childBranches = collect();
        if ($childBranchIds->isNotEmpty() && $fullEmployees && $fullPositions && $fullDepartments) {
            $rootPool = $fullRootEmployees ?? $fullEmployees;
            $childBranchScopeEmployees = $rootPool->filter(
                fn ($e) => $childBranchIds->contains((int) ($e->branch_id ?? 0))
            );
            $remainingBranchMap = ($branchParentDireksi ?? collect())->except($childBranchIds->all());
            // PENTING: $companyEmployees (pool company PENUH, tak pernah
            // dipersempit) dipakai sbg $allEmployees di sini — BUKAN
            // $fullEmployees, yg di titik ini bisa sudah dipersempit ke 1
            // Cabang/Divisi lain oleh pemanggil (lihat catatan di buildBranches())
            // — supaya hitungan 'total' Cabang yg ditarik ke sini tidak nol.
            $childBranches = $this->buildBranches(
                $childBranchScopeEmployees, $companyEmployees ?? $fullEmployees, $fullPositions, $fullDepartments,
                $divisionParentDireksi, $remainingBranchMap, $fullRootEmployees, $companyEmployees
            );
        }

        return [
            'employee'       => $direksi,
            'departments'    => $this->buildDepartments($deptRoots, $allEmployees, $positions),
            'childDivisions' => $childDivisions,
            'childBranches'  => $childBranches,
            'children'       => $childDireksi
                ->map(fn ($cd) => ['type' => 'direksi'] + $this->buildDireksiNode(
                    $cd, $scopeEmployees, $direksiLinkedRoots, $allEmployees, $positions, $direksiIds,
                    $fullEmployees, $fullPositions, $fullDepartments, $divisionParentDireksi, $fullRootEmployees,
                    $branchParentDireksi, $companyEmployees
                ))
                ->values(),
        ];
    }

    private function buildDepartments(Collection $scopeEmployees, Collection $allEmployees, Collection $positions): Collection
    {
        return $scopeEmployees->groupBy('department_id')
            ->map(function (Collection $deptEmployees, $deptKey) use ($allEmployees, $positions) {
                $deptId     = $deptKey !== '' ? (int) $deptKey : null;
                $department = $deptEmployees->first()->department;

                $deptAllEmployees = $deptId
                    ? $allEmployees->where('department_id', $deptId)
                    : $allEmployees->whereNull('department_id');
                $deptPositions = $deptId
                    ? $positions->where('department_id', $deptId)
                    : $positions->whereNull('department_id');

                // Tier Section di sini cuma utk karyawan level TERATAS departemen (tanpa
                // atasan langsung yang aktif) — bawahan yang tergantung "Atasan Langsung"
                // dikelompokkan section-nya sendiri secara rekursif oleh _employee.blade.php.
                // Jadi cek section HANYA pada $deptEmployees (cakupan akar), bukan seluruh
                // departemen — supaya 1 Manager tunggal tanpa section tidak dibungkus kotak
                // "Tanpa Section" semu gara-gara section dipakai jauh di bawahnya.
                $showSectionTier = $deptEmployees->pluck('section_id')->filter()->unique()->count() > 1;

                $node = [
                    'department' => $department,
                    'total'      => $deptAllEmployees->count(),
                    'sections'   => null,
                    'levels'     => null,
                    'vacant'     => collect(),
                ];

                if ($showSectionTier) {
                    $node['sections'] = $this->buildSections($deptEmployees, $deptAllEmployees, $deptPositions);
                } else {
                    $panel = $this->buildLevelPanel($deptEmployees, $deptAllEmployees, $deptPositions);
                    $node['levels'] = $panel['levels'];
                    $node['vacant'] = $panel['vacant'];
                }

                return $node;
            })
            ->sortBy(fn ($d) => $d['department']->name ?? 'zzz')
            ->values();
    }

    private function buildSections(Collection $scopeEmployees, Collection $deptAllEmployees, Collection $deptPositions): Collection
    {
        return $scopeEmployees->groupBy('section_id')
            ->map(function (Collection $secEmployees, $secKey) use ($deptAllEmployees, $deptPositions) {
                $secId   = $secKey !== '' ? (int) $secKey : null;
                $section = $secEmployees->first()->section;

                $secAllEmployees = $secId
                    ? $deptAllEmployees->where('section_id', $secId)
                    : $deptAllEmployees->whereNull('section_id');
                $secPositions = $secId
                    ? $deptPositions->where('section_id', $secId)
                    : $deptPositions->whereNull('section_id');

                $panel = $this->buildLevelPanel($secEmployees, $secAllEmployees, $secPositions);

                return [
                    'section' => $section,
                    'total'   => $secAllEmployees->count(),
                    'levels'  => $panel['levels'],
                    'vacant'  => $panel['vacant'],
                ];
            })
            ->sortBy(fn ($s) => $s['section']->name ?? 'zzz')
            ->values();
    }

    /**
     * @return array{levels: Collection, vacant: Collection<Position>}
     */
    private function buildLevelPanel(Collection $scopeEmployees, Collection $scopeAllEmployees, Collection $scopePositions): array
    {
        $filledPositionIds = $scopeAllEmployees->pluck('position_id')->filter()->unique();
        $vacant = $scopePositions->whereNotIn('id', $filledPositionIds)
            ->sortBy('name')->values();

        $levels = $scopeEmployees->groupBy('level_id')
            ->map(fn (Collection $levelEmployees) => [
                'level'     => $levelEmployees->first()->level,
                'positions' => $levelEmployees->groupBy('position_id')
                    ->map(fn (Collection $posEmployees) => [
                        'position'  => $posEmployees->first()->position,
                        'employees' => $posEmployees->values(),
                    ])
                    ->sortBy(fn ($p) => $p['position']->name ?? 'zzz')
                    ->values(),
            ])
            ->sortBy(fn ($lvl) => $lvl['level']->rank ?? 99)
            ->values();

        return ['levels' => $levels, 'vacant' => $vacant];
    }

    // ── Perataan struktur untuk PDF (DomPDF tidak dukung nesting sedalam versi web) ──

    /**
     * DomPDF tidak dukung flexbox/nesting sedalam versi web, jadi bagan
     * diratakan jadi daftar "grup" (1 grup = 1 Cabang, atau 1 Divisi, atau
     * seluruh perusahaan). Tiap grup berisi blok-blok; tiap blok = 1 baris
     * tabel departemen dgn label Direksi (CEO/CFO/dst) opsional di atasnya —
     * jadi hierarki Cabang -> Direksi -> Departemen tetap terbaca walau datar.
     *
     * @return Collection<array{label: ?string, total: int, blocks: Collection}>
     */
    private function flattenForPdf(Collection $tree, bool $showBranchTier, bool $showDivisionTier): Collection
    {
        if ($showBranchTier) {
            return $tree->map(fn ($branch) => [
                'label'  => 'Cabang ' . ($branch['branch']->name ?? 'HO'),
                'total'  => $branch['total'],
                'blocks' => $this->pdfBlocks(
                    $branch['showDivisionTier']
                        ? collect($branch['departments'])->flatMap(fn ($div) => collect($div['departments']))
                        : collect($branch['departments'])
                ),
            ])->values();
        }

        if ($showDivisionTier) {
            return $tree->map(fn ($div) => [
                'label'  => $div['division']->name ?? 'Tanpa Divisi',
                'total'  => $div['total'],
                'blocks' => $this->pdfBlocks(collect($div['departments'])),
            ])->values();
        }

        return collect([[
            'label'  => null,
            'total'  => $tree->sum('total'),
            'blocks' => $this->pdfBlocks($tree),
        ]]);
    }

    /**
     * Ubah daftar node ber-'type' (department|direksi) jadi blok-blok PDF.
     * Departemen "lepas" (tanpa Direksi) digabung jadi 1 blok tanpa label;
     * tiap Direksi jadi blok sendiri (anak Direksi mis. CFO menyusul setelah
     * induknya).
     */
    private function pdfBlocks(Collection $items): Collection
    {
        $blocks     = collect();
        $looseDepts = collect();

        foreach ($items as $item) {
            if (($item['type'] ?? 'department') === 'direksi') {
                $this->pdfDireksiBlocks($item, $blocks);
            } else {
                $looseDepts->push($this->pdfDept($item));
            }
        }

        if ($looseDepts->isNotEmpty()) {
            $blocks->prepend(['direksi' => null, 'departments' => $looseDepts->values()]);
        }

        return $blocks->values();
    }

    private function pdfDireksiBlocks(array $node, Collection $blocks): void
    {
        $emp   = $node['employee'];
        $label = ($emp->position->name ?? 'Direksi') . ' — ' . $emp->name;

        $depts = collect($node['departments'])->map(fn ($d) => $this->pdfDept($d))->values();

        $blocks->push([
            'direksi'     => $label,
            'departments' => $depts->isNotEmpty() ? $depts : collect([[
                'department' => (object) ['name' => $emp->name],
                'total'      => 1,
                'levels'     => collect(),
            ]]),
        ]);

        foreach ($node['children'] as $child) {
            $this->pdfDireksiBlocks($child, $blocks);
        }
    }

    private function pdfDept(array $dept): array
    {
        return [
            'department' => $dept['department'],
            'total'      => $dept['total'],
            'levels'     => $this->flattenDeptLevelsForPdf($dept),
        ];
    }

    private function flattenDeptLevelsForPdf(array $dept): Collection
    {
        if ($dept['sections']) {
            $blocks = collect();
            foreach ($dept['sections'] as $sec) {
                foreach ($sec['levels'] as $lvl) {
                    $blocks->push([
                        'label'     => ($lvl['level']->name ?? 'Lainnya') . ' — ' . ($sec['section']->name ?? 'Tanpa Section'),
                        'positions' => $lvl['positions'],
                    ]);
                }
                if ($sec['vacant']->isNotEmpty()) {
                    $blocks->push([
                        'label'     => 'Jabatan Kosong — ' . ($sec['section']->name ?? 'Tanpa Section'),
                        'positions' => $sec['vacant']->map(fn ($p) => ['position' => $p, 'employees' => collect()]),
                    ]);
                }
            }

            return $blocks;
        }

        $blocks = collect($dept['levels'])->map(fn ($lvl) => [
            'label'     => $lvl['level']->name ?? 'Lainnya',
            'positions' => $lvl['positions'],
        ]);

        if ($dept['vacant']->isNotEmpty()) {
            $blocks->push([
                'label'     => 'Jabatan Kosong',
                'positions' => $dept['vacant']->map(fn ($p) => ['position' => $p, 'employees' => collect()]),
            ]);
        }

        return $blocks->values();
    }
}
