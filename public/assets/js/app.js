/* -------------------------------------------------------
   TITIPIN — perilaku antarmuka yang tetap butuh JavaScript.

   Empat hal saja:
   1. tampilkan / sembunyikan password
   2. pratinjau foto sebelum diunggah
   3. sidebar di layar kecil
   4. popup detail barang, filter lantai -> ruangan,
      penyesuaian label formulir, dan panel notifikasi

   Seluruh logika data tetap di PHP.
------------------------------------------------------- */

(function () {
    'use strict';

    /* --- 1. Tampilkan / sembunyikan password --- */
    var toggle = document.getElementById('togglePass');

    if (toggle) {
        toggle.addEventListener('click', function () {
            var input = document.getElementById('password');

            if (!input) {
                return;
            }

            var terbuka = input.type === 'text';

            input.type = terbuka ? 'password' : 'text';
            toggle.textContent = terbuka ? 'Lihat' : 'Sembunyikan';
        });
    }

    /* --- 2. Pratinjau foto sebelum dikirim --- */
    var inputFoto = document.getElementById('foto');
    var wadah = document.getElementById('photoPreviewContainer');

    if (inputFoto && wadah) {
        inputFoto.addEventListener('change', function () {
            wadah.innerHTML = '';

            var berkas = inputFoto.files && inputFoto.files[0];

            if (!berkas) {
                return;
            }

            if (berkas.size > 2 * 1024 * 1024) {
                wadah.innerHTML = '<p class="field-error">Ukuran foto melebihi 2 MB.</p>';
                inputFoto.value = '';

                return;
            }

            var pembaca = new FileReader();

            pembaca.addEventListener('load', function (event) {
                var bungkus = document.createElement('div');
                bungkus.className = 'photo-preview-wrap';

                var gambar = document.createElement('img');
                gambar.className = 'photo-preview';
                gambar.alt = 'Pratinjau foto';
                gambar.src = String(event.target.result);

                var hapus = document.createElement('button');
                hapus.type = 'button';
                hapus.className = 'photo-preview-remove';
                hapus.textContent = '✕';
                hapus.title = 'Hapus foto';

                hapus.addEventListener('click', function () {
                    inputFoto.value = '';
                    wadah.innerHTML = '';
                });

                bungkus.appendChild(gambar);
                bungkus.appendChild(hapus);
                wadah.appendChild(bungkus);
            });

            pembaca.readAsDataURL(berkas);
        });
    }

    /* --- 3. Sidebar di layar kecil --- */
    var tombolMenu = document.getElementById('menuBtn');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');

    function tutupSidebar() {
        if (sidebar) {
            sidebar.classList.remove('open');
        }

        if (overlay) {
            overlay.classList.remove('open');
        }
    }

    if (tombolMenu && sidebar) {
        tombolMenu.addEventListener('click', function () {
            sidebar.classList.toggle('open');

            if (overlay) {
                overlay.classList.toggle('open');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', tutupSidebar);
    }

    /* --- 4a. Popup detail barang --- */
    function buatElemen(tag, kelas, teks) {
        var el = document.createElement(tag);

        if (kelas) {
            el.className = kelas;
        }

        if (teks !== undefined && teks !== null) {
            el.textContent = teks;
        }

        return el;
    }

    function bukaDetail(data) {
        var lama = document.querySelector('.modal-backdrop[data-detail-modal]');

        if (lama) {
            lama.remove();
        }

        var back = buatElemen('div', 'modal-backdrop');
        back.setAttribute('data-detail-modal', '1');
        back.setAttribute('role', 'dialog');
        back.setAttribute('aria-modal', 'true');

        var modal = buatElemen('div', 'modal detail-modal');

        var tutup = buatElemen('button', 'modal-close', '✕');
        tutup.type = 'button';
        tutup.setAttribute('aria-label', 'Tutup');
        tutup.addEventListener('click', function () { back.remove(); });

        var kepala = buatElemen('div', 'detail-head');
        kepala.appendChild(buatElemen('p', 'muted detail-kode', data.kode + ' · ' + data.tanggal));

        var judul = buatElemen('h2', null, data.judul);
        judul.style.margin = '0 0 8px';
        kepala.appendChild(judul);

        var barisBadge = buatElemen('div', 'detail-badges');
        var badgeJenis = buatElemen('span', 'status ' + (data.jenis === 'Barang hilang' ? 'red' : (data.jenis === 'Barang temuan' ? 'blue' : '')));
        badgeJenis.textContent = data.jenis;
        var badgeStatus = buatElemen('span', 'status ' + (data.statusKls || ''));
        badgeStatus.textContent = data.status;
        barisBadge.appendChild(badgeJenis);
        barisBadge.appendChild(badgeStatus);
        kepala.appendChild(barisBadge);

        modal.appendChild(tutup);
        modal.appendChild(kepala);

        if (data.foto) {
            var gambar = buatElemen('img', 'detail-photo');
            gambar.src = data.foto;
            gambar.alt = data.judul;
            modal.appendChild(gambar);
        }

        var info = buatElemen('div', 'detail-info');
        info.appendChild(buatElemen('b', null, 'Lokasi'));
        info.appendChild(buatElemen('span', null, data.tempat));
        modal.appendChild(info);

        if (data.deskripsi) {
            var deskripsi = buatElemen('p', 'detail-deskripsi', data.deskripsi);
            modal.appendChild(deskripsi);
        }

        if (data.pelapor) {
            var pelapor = buatElemen('p', 'muted detail-pelapor', 'Dilaporkan oleh: ' + data.pelapor);
            modal.appendChild(pelapor);
        }

        var aksi = buatElemen('div', 'modal-actions');
        var tautan = buatElemen('a', 'btn outline', 'Buka halaman detail');
        tautan.href = data.tautan;
        aksi.appendChild(tautan);

        if (data.klaim) {
            var klaim = buatElemen('a', 'btn primary', data.klaimLabel);
            klaim.href = data.klaim;
            aksi.appendChild(klaim);
        } else {
            var tutupBawah = buatElemen('button', 'btn primary', 'Tutup');
            tutupBawah.type = 'button';
            tutupBawah.addEventListener('click', function () { back.remove(); });
            aksi.appendChild(tutupBawah);
        }

        modal.appendChild(aksi);
        back.appendChild(modal);
        document.body.appendChild(back);

        back.addEventListener('click', function (event) {
            if (event.target === back) {
                back.remove();
            }
        });
    }

    document.addEventListener('click', function (event) {
        var pemicu = event.target.closest('[data-detail]');

        if (!pemicu) {
            return;
        }

        event.preventDefault();

        try {
            bukaDetail(JSON.parse(pemicu.getAttribute('data-detail')));
        } catch (error) {
            /* Data rusak: pakai halaman detail sebagai cadangan. */
            window.location.href = pemicu.getAttribute('data-fallback') || window.location.href;
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        var modal = document.querySelector('.modal-backdrop[data-detail-modal]');

        if (modal) {
            modal.remove();
        }

        var panel = document.getElementById('notifPanel');

        if (panel && !panel.hidden) {
            panel.hidden = true;
        }
    });

    /* --- 4b. Filter lantai -> ruangan ---
       Dropdown ruangan selalu bisa ditekan. Saat lantai belum dipilih,
       seluruh ruangan ditampilkan; setelah lantai dipilih, daftarnya
       disaring. Opsi dibangun ulang dari data awal agar tidak ada opsi
       yang "hilang" ketika lantai diganti-ganti. */
    var pilihLantai = document.getElementById('lantai');

    if (pilihLantai) {
        var idRuangan = pilihLantai.getAttribute('data-ruangan-target');
        var pilihRuangan = idRuangan ? document.getElementById(idRuangan) : null;
        var petunjuk = document.getElementById('ruanganHint');

        if (pilihRuangan) {
            /* Simpan seluruh opsi beserta lantainya sekali saja. */
            var semuaRuangan = [];

            Array.prototype.forEach.call(
                pilihRuangan.querySelectorAll('optgroup'),
                function (grup) {
                    Array.prototype.forEach.call(grup.querySelectorAll('option'), function (opsi) {
                        semuaRuangan.push({
                            nilai: opsi.value,
                            teks: opsi.textContent,
                            lantai: grup.getAttribute('data-lantai') || grup.label
                        });
                    });
                }
            );

            var saringRuangan = function () {
                var lantai = pilihLantai.value;
                var terpilih = pilihRuangan.value;

                pilihRuangan.innerHTML = '';

                var kosong = document.createElement('option');
                kosong.value = '';
                kosong.textContent = lantai === ''
                    ? 'Pilih lantai dulu, atau pilih langsung ruangan'
                    : 'Pilih ruangan';
                pilihRuangan.appendChild(kosong);

                var grupAktif = null;
                var labelAktif = null;

                semuaRuangan.forEach(function (data) {
                    if (lantai !== '' && data.lantai !== lantai) {
                        return;
                    }

                    if (data.lantai !== labelAktif) {
                        grupAktif = document.createElement('optgroup');
                        grupAktif.label = data.lantai;
                        pilihRuangan.appendChild(grupAktif);
                        labelAktif = data.lantai;
                    }

                    var opsi = document.createElement('option');
                    opsi.value = data.nilai;
                    opsi.textContent = data.teks;

                    if (data.nilai === terpilih) {
                        opsi.selected = true;
                    }

                    grupAktif.appendChild(opsi);
                });

                /* Nilai lama tidak valid untuk lantai baru: kosongkan. */
                if (terpilih !== '' && pilihRuangan.value !== terpilih) {
                    pilihRuangan.value = '';
                }

                if (petunjuk) {
                    petunjuk.textContent = lantai === ''
                        ? 'Pilih lantai untuk mempersempit daftar ruangan.'
                        : 'Menampilkan ruangan di ' + lantai + '.';
                }
            };

            pilihLantai.addEventListener('change', saringRuangan);
            saringRuangan();
        }
    }

    /* --- 4c. Label formulir menyesuaikan jenis laporan --- */
    var pilihJenis = document.getElementById('jenis');
    var labelNama = document.getElementById('namaLabel');
    var inputNama = document.getElementById('nama');

    if (pilihJenis && labelNama && inputNama) {
        var sesuaikanLabel = function () {
            var fasilitas = pilihJenis.value === 'Rusak';

            labelNama.textContent = fasilitas ? 'Nama fasilitas *' : 'Nama barang *';
            inputNama.placeholder = fasilitas
                ? 'Contoh: AC Lab 5A'
                : 'Contoh: Dompet kulit hitam';
        };

        pilihJenis.addEventListener('change', sesuaikanLabel);
        sesuaikanLabel();
    }

    /* --- 4d. Panel notifikasi --- */
    var tombolNotif = document.getElementById('notifBtn');
    var panelNotif = document.getElementById('notifPanel');

    if (tombolNotif && panelNotif) {
        tombolNotif.addEventListener('click', function (event) {
            event.stopPropagation();

            panelNotif.hidden = !panelNotif.hidden;
            tombolNotif.setAttribute('aria-expanded', panelNotif.hidden ? 'false' : 'true');
        });

        document.addEventListener('click', function (event) {
            if (panelNotif.hidden) {
                return;
            }

            if (!event.target.closest('#notifWrap')) {
                panelNotif.hidden = true;
                tombolNotif.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
