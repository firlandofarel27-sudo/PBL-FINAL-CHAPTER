console.log("TITIPIN AUTH BERHASIL DIMUAT!");
alert("JavaScript Login Berhasil Dimuat!");
document.addEventListener("DOMContentLoaded", function () {

    // Tujuan halaman berdasarkan jenis akun
    const dashboardRoutes = {
        user: "pages/user.html",
        admin: "pages/admin.html"
    };
    const loginForm = document.getElementById("loginForm");
    const demoLogin = document.getElementById("demoLogin");
    const togglePass = document.getElementById("togglePass");
    const sso = document.getElementById("sso");
    const forgotPassword = document.getElementById("forgotPassword");

    // Fungsi masuk dashboard
    function loginSuccess(role) {

        const destination = dashboardRoutes[role];

        if (!destination) {
            alert("Role akun tidak ditemukan!");
            return;
        }

        // Simpan informasi sesi demo
        sessionStorage.setItem("titipin_logged_in", "true");
        sessionStorage.setItem("titipin_role", role);

        // Pindah ke halaman dashboard
        window.location.assign(destination);
    }

    // Login menggunakan tombol form
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const role = document.getElementById("demoRole").value;

            if (!email || !password) {
                alert("Email dan password harus diisi!");
                return;
            }

            loginSuccess(role);
        });
    }

    // Login demo
    if (demoLogin) {
        demoLogin.addEventListener("click", function () {

            const role = document.getElementById("demoRole").value;

            loginSuccess(role);

        });
    }

    // Tampilkan atau sembunyikan password
    if (togglePass) {
        togglePass.addEventListener("click", function () {

            const password = document.getElementById("password");

            if (password.type === "password") {
                password.type = "text";
                this.textContent = "Sembunyikan";
            } else {
                password.type = "password";
                this.textContent = "Lihat";
            }

        });
    }

    // SSO sementara
    if (sso) {
        sso.addEventListener("click", function () {
            alert("Fitur SSO belum diaktifkan.");
        });
    }

    // Lupa password
    if (forgotPassword) {
        forgotPassword.addEventListener("click", function (e) {
            e.preventDefault();
            alert("Silakan hubungi administrator.");
        });
    }

});
