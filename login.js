// ==================== LOGIN LOGIC ====================

const loginForm     = document.getElementById("loginForm");
const loginBtn      = document.getElementById("loginBtn");
const emailInput    = document.getElementById("email");
const passwordInput = document.getElementById("password");
const toast         = document.getElementById("toast");
const toastMessage  = document.getElementById("toastMessage");

// ── If already logged-in, go straight to dashboard ──────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem("bitesight_session") === "active") {
        window.location.href = "dashboard.html";
    }
});

// ── Form submit → call login.php ─────────────────────────────────────────────
if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const email    = emailInput.value.trim();
        const password = passwordInput.value.trim();

        if (!email || !password) {
            showToast("يرجى إدخال البريد الإلكتروني وكلمة المرور", "⚠️");
            return;
        }

        // Visual loading state
        const btnText = loginBtn.querySelector(".btn-text");
        if (btnText) btnText.textContent = "جاري التحقق... ⏳";
        loginBtn.disabled = true;

        try {
            const res  = await fetch("login.php", {
                method:  "POST",
                headers: { "Content-Type": "application/json" },
                body:    JSON.stringify({ email, password })
            });

            const json = await res.json();

            if (json.status === "success") {
                // ── Save session ──────────────────────────────────────────
                localStorage.setItem("bitesight_session", "active");
                localStorage.setItem("user_name",  json.user?.name  || "Admin");
                localStorage.setItem("user_email", json.user?.email || email);
                localStorage.setItem("user_role",  json.user?.role  || "admin");

                // ── Success feedback then redirect ────────────────────────
                if (btnText) btnText.textContent = "تم الدخول بنجاح! ✅";
                loginBtn.style.background = "linear-gradient(135deg,#22c55e,#16a34a)";

                showToast("مرحباً " + (json.user?.name || ""), "✅");

                setTimeout(() => {
                    window.location.href = "dashboard.html";
                }, 900);

            } else {
                // ── Error feedback ────────────────────────────────────────
                showToast(json.message || "بيانات الدخول غير صحيحة", "❌");
                if (btnText) btnText.textContent = "دخول";
                loginBtn.disabled = false;

                // Shake animation on card
                const card = document.querySelector(".login-card");
                if (card) {
                    card.style.animation = "none";
                    card.offsetHeight;   // trigger reflow
                    card.style.animation = "shake 0.4s ease-in-out";
                }
            }

        } catch (err) {
            showToast("خطأ في الاتصال بالخادم — تأكد من تشغيل XAMPP", "❌");
            if (btnText) btnText.textContent = "دخول";
            loginBtn.disabled = false;
            console.error(err);
        }
    });
}

// ── Toast helper ─────────────────────────────────────────────────────────────
function showToast(message, icon = "⚠️") {
    if (!toast || !toastMessage) return;
    const iconEl = toast.querySelector(".toast-icon");
    toastMessage.textContent = message;
    if (iconEl) iconEl.textContent = icon;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}

// ── Shake keyframe (injected dynamically) ────────────────────────────────────
const style = document.createElement("style");
style.innerHTML = `
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25%      { transform: translateX(-10px); }
  75%      { transform: translateX(10px);  }
}`;
document.head.appendChild(style);
