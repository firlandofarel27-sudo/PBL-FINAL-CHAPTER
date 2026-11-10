alert("TITIPIN AUTH BERHASIL DIMUAT!");

console.log("AUTH JS BERHASIL!");

document.addEventListener("DOMContentLoaded", function () {

    const demoLogin = document.getElementById("demoLogin");
    const demoRole = document.getElementById("demoRole");

    if (!demoLogin || !demoRole) {
        alert("Tombol login atau dropdown tidak ditemukan!");
        return;
    }

    demoLogin.addEventListener("click", function () {

        const role = demoRole.value;

        if (role === "user") {
            window.location.href = "./pages/user.html";
        }

        else if (role === "admin") {
            window.location.href = "./pages/admin.html";
        }

    });

});