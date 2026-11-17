
document.addEventListener("DOMContentLoaded", function () {

    console.log("TITIPIN LOGIN AKTIF");

    // Mengambil elemen HTML
    const loginForm = document.getElementById("loginForm");
    const demoLogin = document.getElementById("demoLogin");
    const demoRole = document.getElementById("demoRole");

    // Daftar halaman dashboard
    const dashboardRoutes = {
        user: "./pages/user.html",
        admin: "./pages/admin.html"
    };

    // Fungsi masuk dashboard
    function loginSuccess(role) {

        console.log("Role dipilih:", role);

        if (!dashboardRoutes[role]) {
            alert("Jenis akun tidak ditemukan!");
            return;
        }

        // Menyimpan sesi login demo
        sessionStorage.setItem("titipin_logged_in", "true");
        sessionStorage.setItem("titipin_role", role);

        // Mengarahkan ke dashboard
        window.location.assign(dashboardRoutes[role]);
    }

    // Tombol login demo
    if (demoLogin) {

        demoLogin.addEventListener("click", function () {

            if (!demoRole) {
                alert("Pilihan akun tidak ditemukan!");
                return;
            }

            loginSuccess(demoRole.value);

        });

    } else {
        console.error("Tombol demoLogin tidak ditemukan!");
    }

    // Tombol login utama
    if (loginForm) {

        loginForm.addEventListener("submit", function (event) {

            event.preventDefault();

            const email = document.getElementById("email");
            const password = document.getElementById("password");

            if (!email || !password || !demoRole) {
                alert("Elemen form login tidak lengkap!");
                return;
            }

            if (!email.value.trim() || !password.value) {
                alert("Email dan password harus diisi!");
                return;
            }

            loginSuccess(demoRole.value);

        });

    } else {
        console.error("Form login tidak ditemukan!");
    }

});
