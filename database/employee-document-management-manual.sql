-- =============================================================================
-- Modul: Employee Document Management (Employee Digital File) + Exit Process
--
-- Setara dengan migration:
--   2026_08_31_140000_add_group_to_employee_documents
--   2026_08_31_150000_add_exit_process_to_termination_requests
--
-- WAJIB backup dulu. Jalankan SETELAH tabel `employee_documents` (baseline lama,
-- lihat employee-master-data-manual.sql) dan `termination_requests`
-- (approval-engine-manual.sql) sudah ada.
--
-- Katalog doc_type baru (KK, Bank Rekening, Job Description, Amandemen Kontrak,
-- Surat Promosi/Mutasi/Peringatan, Sertifikat Training/Kompetensi, Hasil Assessment,
-- Surat Pengunduran Diri, Berita Acara Clearance, Final Settlement) HANYA di kode
-- (App\Models\EmployeeDocument::$docTypes) — tidak perlu tabel referensi, doc_type
-- tetap varchar bebas seperti sebelumnya.
-- =============================================================================

-- ── Employee Digital File — kelompokkan dokumen ke 5 folder ─────────────────
ALTER TABLE `employee_documents`
  ADD COLUMN `group` varchar(20) NOT NULL DEFAULT 'lainnya' AFTER `doc_type`;

UPDATE `employee_documents` SET `group` = 'personal'
  WHERE `doc_type` IN ('KTP','KK','NPWP','BPJS Kesehatan','BPJS TK','Bank Rekening','SIM','Paspor','KITAS','IMTA','CV');
UPDATE `employee_documents` SET `group` = 'employment'
  WHERE `doc_type` IN ('Kontrak Kerja','Job Description','Amandemen Kontrak','SK Pengangkatan','SK Perpanjangan');
UPDATE `employee_documents` SET `group` = 'movement'
  WHERE `doc_type` IN ('Surat Promosi','Surat Mutasi','Surat Peringatan');
UPDATE `employee_documents` SET `group` = 'development'
  WHERE `doc_type` IN ('Ijazah','Transkrip','Sertifikasi','Sertifikat Training','Sertifikat Kompetensi','Hasil Assessment');
UPDATE `employee_documents` SET `group` = 'exit'
  WHERE `doc_type` IN ('Surat Pengunduran Diri','Berita Acara Clearance','Final Settlement');

-- ── Exit Process — Exit Interview & Final Settlement per proses keluar ───────
ALTER TABLE `termination_requests`
  ADD COLUMN `exit_interview_date` date DEFAULT NULL AFTER `effective_date`,
  ADD COLUMN `exit_interview_notes` text DEFAULT NULL AFTER `exit_interview_date`,
  ADD COLUMN `exit_interview_by_user_id` bigint(20) unsigned DEFAULT NULL AFTER `exit_interview_notes`,
  ADD COLUMN `final_settlement_amount` bigint(20) DEFAULT NULL AFTER `exit_interview_by_user_id`,
  ADD COLUMN `final_settlement_date` date DEFAULT NULL AFTER `final_settlement_amount`,
  ADD COLUMN `final_settlement_notes` text DEFAULT NULL AFTER `final_settlement_date`,
  ADD CONSTRAINT `termination_requests_exit_interview_by_user_id_foreign`
      FOREIGN KEY (`exit_interview_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- Catatan: document expiry alert (command `documents:remind`, kolom
-- employee_documents.expires_at) SUDAH ADA sejak sebelumnya — tidak berubah.
--
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_140000_add_group_to_employee_documents', <BATCH>),
--   ('2026_08_31_150000_add_exit_process_to_termination_requests', <BATCH>);
-- =============================================================================
