
document.addEventListener("DOMContentLoaded", function () {

    // Tujuan dashboard
    const dashboardRoutes = {
        user: "./pages/user.html",
        admin: "./pages/admin.html"
    };

    const demoLogin = document.getElementById("demoLogin");
    const demoRole = document.getElementById("demoRole");

    const loginForm = document.getElementById("loginForm");

    // Fungsi masuk dashboard
    function loginSuccess(role) {

        const destination = dashboardRoutes[role];

        if (!destination) {
            alert("Jenis akun tidak ditemukan!");
            return;
        }

        sessionStorage.setItem("titipin_logged_in", "true");
        sessionStorage.setItem("titipin_role", role);

        window.location.href = destination;
    }

    // Login demo
    if (demoLogin) {
        demoLogin.addEventListener("click", function () {

            loginSuccess(demoRole.value);

        });
    }

    // Login menggunakan form
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {

            e.preventDefault();

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            if (!email || !password) {
                alert("Email dan password wajib diisi!");
                return;
            }

            loginSuccess(demoRole.value);

        });
    }

});
