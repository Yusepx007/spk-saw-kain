/**
 * SPK SAW Pemilihan Desain & Bahan Kain — main.js
 * Vanilla JS untuk interaksi ringan (validasi, konfirmasi hapus, sidebar mobile)
 */

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // 1. Sidebar Mobile Toggle
    // =====================================================
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar?.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // =====================================================
    // 2. Konfirmasi Hapus (semua form/link dengan class confirm-delete)
    // =====================================================
    document.querySelectorAll('.confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const nama = el.dataset.nama || 'data ini';
            const ok = confirm(`Apakah Anda yakin ingin menghapus "${nama}"?\nAksi ini tidak bisa dibatalkan.`);
            if (!ok) {
                e.preventDefault();
                return false;
            }
        });
    });

    // =====================================================
    // 3. Live Total Bobot Kriteria
    // Dipanggil di halaman create/edit kriteria
    // =====================================================
    const bobotInput     = document.getElementById('bobot');
    const totalDisplay   = document.getElementById('total-bobot-display');
    const sisaDisplay    = document.getElementById('sisa-bobot-display');
    const bobotLainBase  = parseFloat(document.getElementById('bobot-lain')?.value ?? '0');

    if (bobotInput && totalDisplay) {
        function updateTotalBobot() {
            const bobot  = parseFloat(bobotInput.value) || 0;
            const total  = Math.round((bobotLainBase + bobot) * 1000) / 1000;
            const sisa   = Math.round((1.0 - total) * 1000) / 1000;

            totalDisplay.textContent = 'Total bobot: ' + total.toFixed(3);
            totalDisplay.className   = '';

            if (Math.abs(total - 1.0) <= 0.001) {
                totalDisplay.classList.add('valid');
            } else {
                totalDisplay.classList.add('invalid');
            }

            if (sisaDisplay) {
                sisaDisplay.textContent = sisa >= 0
                    ? `(sisa: ${sisa.toFixed(3)})`
                    : `(kelebihan: ${Math.abs(sisa).toFixed(3)})`;
                sisaDisplay.style.color = Math.abs(sisa) <= 0.001 ? 'var(--success)' : 'var(--danger)';
            }
        }

        bobotInput.addEventListener('input', updateTotalBobot);
        updateTotalBobot(); // Jalankan sekali saat load
    }

    // =====================================================
    // 4. Preview Foto Desain sebelum upload
    // =====================================================
    const fotoInput   = document.getElementById('foto');
    const fotoPreview = document.getElementById('foto-preview');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                    fotoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // =====================================================
    // 5. Auto-dismiss flash alert setelah 4 detik
    // =====================================================
    document.querySelectorAll('.alert.alert-success, .alert.alert-info').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 4000);
    });

    // =====================================================
    // 6. Validasi form nilai bahan (range 1-5)
    // =====================================================
    const nilaiInputs = document.querySelectorAll('.nilai-bahan-input');
    nilaiInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const val = parseFloat(this.value);
            if (isNaN(val) || val < 1 || val > 5) {
                this.classList.add('is-invalid');
                this.setCustomValidity('Nilai harus antara 1 dan 5');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                this.setCustomValidity('');
            }
        });
    });

    // =====================================================
    // 7. Toggle detail normalisasi di Hasil Rekomendasi
    // =====================================================
    document.querySelectorAll('.btn-toggle-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.dataset.target;
            const detail   = document.getElementById(targetId);
            if (detail) {
                const isShown = detail.style.display !== 'none';
                detail.style.display = isShown ? 'none' : 'block';
                btn.innerHTML = isShown
                    ? '<i class="bi bi-chevron-down"></i> Detail'
                    : '<i class="bi bi-chevron-up"></i> Sembunyikan';
            }
        });
    });

});
