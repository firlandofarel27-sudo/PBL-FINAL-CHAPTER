
/* ======================================
   TITIPIN - AUTHENTICATION / LOGIN
====================================== */

// Tujuan dashboard berdasarkan role
const dashboardRoutes = {
    user: "pages/user.html",
    admin: "pages/admin.html",
    superadmin: "pages/super-admin.html"
};


// Toggle password
const togglePass = document.getElementById("togglePass");

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


// Proses login
const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", function (e) {

    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    if (!email || !password) {
        alert("Email dan password wajib diisi!");
        return;
    }

    // Login demo: role dipilih melalui dropdown
    const role = document.getElementById("demoRole").value;

    loginSuccess(role, email);

});


// Login demo berdasarkan role
const demoLogin = document.getElementById("demoLogin");

demoLogin.addEventListener("click", function () {

    const role = document.getElementById("demoRole").value;

    loginSuccess(role, "");

});


// Fungsi setelah login
function loginSuccess(role, email) {

    const destination = dashboardRoutes[role];

    if (!destination) {
        alert("Jenis akun tidak ditemukan!");
        return;
    }

    // Simpan sesi demo
    sessionStorage.setItem("titipin_logged_in", "true");
    sessionStorage.setItem("titipin_role", role);
    sessionStorage.setItem("titipin_email", email);

    // Arahkan ke dashboard
    window.location.href = destination;

}


// SSO Kampus
document.getElementById("sso").addEventListener("click", function () {

    alert(
        "Fitur SSO Kampus belum aktif. " +
        "Hubungkan terlebih dahulu dengan sistem autentikasi kampus."
    );

});


// Lupa password
document.getElementById("forgotPassword")
    .addEventListener("click", function (e) {

        e.preventDefault();

        alert(
            "Silakan hubungi administrator untuk " +
            "melakukan reset password."
        );

    });
