-- CEK-KOLOM.sql — jalankan di DB prod: mysql -u USER -p hris < CEK-KOLOM.sql
-- Memastikan ALTER kolom TAHAP 1–6 sudah kena (syarat pakai deploy-produksi-HR-lanjutan.sql).
-- Semua baris HARUS "ADA". Kalau ada yang "HILANG" → TAHAP itu belum lengkap,
-- pakai file per-TAHAP (tahap-01..06) untuk kolom yang hilang saja.

SELECT 'TAHAP 1  employees.religion_id'        AS cek,
       IF(COUNT(*)>0,'ADA','HILANG') AS status
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='employees' AND column_name='religion_id'
UNION ALL
SELECT 'TAHAP 1  employees.marital_status_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='employees' AND column_name='marital_status_id'
UNION ALL
SELECT 'TAHAP 2  employees.division_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='employees' AND column_name='division_id'
UNION ALL
SELECT 'TAHAP 2  departments.division_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='departments' AND column_name='division_id'
UNION ALL
SELECT 'TAHAP 2  positions.section_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='positions' AND column_name='section_id'
UNION ALL
SELECT 'TAHAP 3  levels.rank',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='levels' AND column_name='rank'
UNION ALL
SELECT 'TAHAP 3  employees.branch_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='employees' AND column_name='branch_id'
UNION ALL
SELECT 'TAHAP 3  positions.branch_id',
       IF(COUNT(*)>0,'ADA','HILANG')
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='positions' AND column_name='branch_id';

-- Info tambahan: cek apakah TAHAP 18 (enkripsi) sudah pernah jalan.
-- ktp_number 'text' = TAHAP 18 SUDAH jalan; 'varchar' = BELUM (normal, ada di bundle).
SELECT 'TAHAP 18 employees.ktp_number tipe' AS info,
       COALESCE(data_type,'(kolom tidak ada)') AS nilai
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='employees' AND column_name='ktp_number';

-- leave_requests.status: 'varchar' = TAHAP 19 sudah; 'enum' = belum (ada di bundle).
SELECT 'TAHAP 19 leave_requests.status tipe' AS info,
       COALESCE(column_type,'(kolom tidak ada)') AS nilai
FROM information_schema.columns
WHERE table_schema=DATABASE() AND table_name='leave_requests' AND column_name='status';
