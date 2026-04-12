// ==================== DATA ====================
let recipes = [];
let ingredients = [];
let users = [];
let favorites = [];
let dashUsers = [];  // dashlogen table
let contacts = [];   // contact table
let sineUsers = [];  // sine table
let aboutContent = []; // about_page table

// State
let currentSection = "";
let selectedRecipeIngredients = [];
let editingRecipeId = null;
let editingDashUserId = null;
let deleteCallback = null;

async function fetchData() {
  try {
    const res = await fetch('api.php?action=getAllData');
    const json = await res.json();
    if (json.status === 'success') {
      recipes     = json.data.recipes     || [];
      ingredients = json.data.ingredients || [];
      users       = json.data.users       || [];
      favorites   = json.data.favorites   || [];
      renderAll();
    } else {
      showToast('خطأ في جلب البيانات: ' + json.message, "❌");
    }
  } catch (err) {
    showToast('خطأ في الاتصال بالخادم', "❌");
    console.error(err);
  }
  // Fetch secondary tables
  fetchDashUsers();
  fetchContacts();
  fetchSineUsers();
  fetchAboutContent();
}

async function fetchDashUsers() {
  try {
    const res  = await fetch('api.php?action=getDashUsers');
    const json = await res.json();
    if (json.status === 'success') {
      dashUsers = json.data || [];
      renderDashUsersTable();
    }
  } catch (err) {
    console.error('fetchDashUsers error:', err);
  }
}

async function fetchContacts() {
  try {
    const res  = await fetch('api.php?action=getContacts');
    const json = await res.json();
    if (json.status === 'success') {
      contacts = json.data || [];
      renderContactsTable();
    }
  } catch (err) {
    console.error('fetchContacts error:', err);
  }
}

async function fetchSineUsers() {
  try {
    const res  = await fetch('api.php?action=getSineUsers');
    const json = await res.json();
    if (json.status === 'success') {
      sineUsers = json.data || [];
      renderSineTable();
    }
  } catch (err) {
    console.error('fetchSineUsers error:', err);
  }
}

async function fetchAboutContent() {
  try {
    const res  = await fetch('api.php?action=getAboutContent');
    const json = await res.json();
    if (json.status === 'success') {
      aboutContent = json.data || [];
      renderAboutContent();
    }
  } catch (err) {
    console.error('fetchAboutContent error:', err);
  }
}

// ==================== INITIALIZATION ====================
document.addEventListener("DOMContentLoaded", () => {
  initNavigation();
  initTheme();
  initModals();
  initSearch();
  initFilters();
  loadUserInfo();   // ← inject real name from localStorage
  initLanguage();
  initBiDiInputs(); // ← make inputs write AR/EN flawlessly
  fetchData();
});

// ==================== BIDI & TRANSLATE GUARD ====================
function initBiDiInputs() {
  // Finds every single input, textarea and makes sure they accept and align Arabic AND English perfectly
  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.setAttribute('dir', 'auto'); // Auto-detect typing language direction!
    el.setAttribute('translate', 'no'); // Don't let Google Translate break User Data
  });
}

// ==================== USER INFO ====================
function loadUserInfo() {
  const name = localStorage.getItem("user_name") || "Admin";
  const title = document.getElementById("adminNameTitle");
  const sidebar = document.getElementById("sidebarUserName");
  if (title)   title.textContent   = name;
  if (sidebar) sidebar.textContent = name;
}

// ==================== NAVIGATION ====================
function initNavigation() {
  const navLinks = document.querySelectorAll(".nav-link[data-section]");

  navLinks.forEach(link => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const section = link.dataset.section;
      navigateTo(section);
    });
  });

  // Mobile menu toggle
  const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.getElementById("sidebar");

  if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
    });
  }

  // Logout
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
    logoutBtn.onclick = (e) => {
      e.preventDefault();

      // Clear Auth Session ONLY (keep theme and other preferences)
      localStorage.removeItem("bitesight_session");
      localStorage.removeItem("user_email");
      localStorage.removeItem("user_name");
      localStorage.removeItem("user_role");

      showToast("تم تسجيل الخروج... جاري التحويل", "👋");

      // Instant Redirect
      setTimeout(() => {
        window.location.replace("index.html");
      }, 800);
    };
  }

  // Restore last visited page on startup
  const savedSection = localStorage.getItem("bitesight_last_section") || "overview";
  navigateTo(savedSection);
}

function navigateTo(section) {
  if (currentSection === section) return;
  currentSection = section;
  localStorage.setItem("bitesight_last_section", section); // Save state

  // Update nav
  document.querySelectorAll(".nav-link").forEach(link => {
    link.classList.remove("active");
    if (link.dataset.section === section) {
      link.classList.add("active");
    }
  });

  // Smoothly update sections
  const sections = document.querySelectorAll(".section");
  sections.forEach(sec => {
    if (sec.id === `section-${section}`) {
      sec.style.display = "block";
      setTimeout(() => sec.classList.add("active"), 10);
    } else {
      sec.classList.remove("active");
      setTimeout(() => {
        if (!sec.classList.contains("active")) sec.style.display = "none";
      }, 300);
    }
  });

  // Update breadcrumb
  const titles = {
    overview:    "نظرة عامة",
    recipes:     "مكتبة الوصفات",
    ingredients: "قاعدة المكونات",
    users:       "المجتمع",
    favorites:   "المحفوظات",
    settings:    "التفضيلات",
    dashusers:   "حسابات الدخول",
    contacts:    "رسائل العملاء",
    sine:        "سجل الدخول",
    site:        "عن الموقع"
  };

  const breadcrumb = document.getElementById("breadcrumbCurrent");
  if (breadcrumb) {
    breadcrumb.textContent = titles[section] || section;
  }

  // Close mobile sidebar
  const sidebar = document.getElementById("sidebar");
  if (sidebar) {
    sidebar.classList.remove("open");
  }
}

// ==================== THEME ====================
function initTheme() {
  const themeBtn = document.getElementById("themeBtn");
  const darkToggle = document.getElementById("darkModeToggle");

  // Load saved preference
  const saved = localStorage.getItem("bitesight_theme");
  const prefersDark = saved === "dark" || (!saved && window.matchMedia("(prefers-color-scheme: dark)").matches);

  if (prefersDark) {
    document.documentElement.classList.add("dark-mode");
    if (themeBtn) themeBtn.textContent = "☀️";
    if (darkToggle) darkToggle.checked = true;
  }

  function applyTheme(isDark) {
    document.documentElement.classList.toggle("dark-mode", isDark);
    localStorage.setItem("bitesight_theme", isDark ? "dark" : "light");
    if (themeBtn) themeBtn.textContent = isDark ? "☀️" : "🌙";
    if (darkToggle) darkToggle.checked = isDark;
  }

  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      applyTheme(!document.body.classList.contains("dark-mode"));
    });
  }

  if (darkToggle) {
    darkToggle.addEventListener("change", () => {
      applyTheme(darkToggle.checked);
    });
  }
}

// ==================== MODALS ====================
function initModals() {
  // Close on escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeAllModals();
    }
  });

  // Handle Image Upload to Base64
  const fileInput = document.getElementById("recipeImageFile");
  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById("recipeImageBase64").value = e.target.result;
          document.getElementById("recipeImagePreview").src = e.target.result;
          document.getElementById("recipeImagePreviewContainer").style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });
  }
}

function openModal(type) {
  const modal = document.getElementById(`${type}Modal`);
  if (modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    if (type === "recipe") {
      renderIngredientSelect();
    }
  }
}

function closeModal(type) {
  const modal = document.getElementById(`${type}Modal`);
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "";

    if (type === "recipe") {
      resetRecipeForm();
    }
    if (type === "ingredient") {
      const nameInput = document.getElementById("ingredientName");
      if (nameInput) { nameInput.value = ""; nameInput.dataset.editId = ""; }
      const emojiInput = document.getElementById("ingredientEmoji");
      if (emojiInput) emojiInput.value = "";
    }
    if (type === "dashuser") {
      editingDashUserId = null;
      const t = document.getElementById("dashuserModalTitle");
      if (t) t.textContent = "إضافة حساب جديد";
      ["dashuserName", "dashuserEmail", "dashuserPassword"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
      });
    }
  }
}

function closeAllModals() {
  document.querySelectorAll(".modal").forEach(modal => {
    modal.classList.remove("active");
  });
  document.body.style.overflow = "";
}

// ==================== RENDER ALL ====================
function renderAll() {
  updateKPIs();
  renderOverviewRecipes();
  renderOverviewTopRecipes();
  renderRecipesTable();
  renderIngredientsGrid();
  renderUsersTable();
  renderFavoritesGrid();
}

// ==================== KPIs ====================
function updateKPIs() {
  document.getElementById("kpiRecipes").textContent = recipes.length;
  document.getElementById("kpiIngredients").textContent = ingredients.length;
  document.getElementById("kpiUsers").textContent = users.length;
  document.getElementById("kpiFavorites").textContent = favorites.length;

  // Stats
  document.getElementById("totalIngredients").textContent = ingredients.length;
  document.getElementById("activeIngredients").textContent = ingredients.filter(i => i.usedIn > 0).length;
  document.getElementById("usedIngredients").textContent = ingredients.filter(i => i.usedIn > 0).length;

  document.getElementById("totalUsers").textContent = users.length;
  document.getElementById("activeUsers").textContent = users.filter(u => u.status === "نشط").length;
  document.getElementById("newUsersToday").textContent = users.filter(u => u.status === "جديد").length;

  document.getElementById("totalFavorites").textContent = favorites.length;
  document.getElementById("todayFavorites").textContent = Math.floor(favorites.length * 0.2);
}

// ==================== OVERVIEW ====================
function renderOverviewRecipes() {
  const container = document.getElementById("overviewRecipes");
  if (!container) return;

  const recent = recipes.slice(0, 4);

  container.innerHTML = recent.map(r => `
    <div class="mini-card">
      <img src="${r.image}" alt="${r.name}">
      <div class="mini-card-info">
        <h4>${r.name}</h4>
        <p>${r.category} • ${r.time}</p>
      </div>
      <span class="badge badge-${r.status === 'نشطة' ? 'active' : r.status === 'مراجعة' ? 'review' : 'hidden'}">${r.status}</span>
    </div>
  `).join("");
}

function renderOverviewTopRecipes() {
  const container = document.getElementById("overviewTopRecipes");
  if (!container) return;

  const top = [...recipes].sort((a, b) => b.saves - a.saves).slice(0, 3);

  container.innerHTML = top.map((r, i) => `
    <div class="top-item">
      <span class="rank rank-${i + 1}">${i + 1}</span>
      <img src="${r.image}" alt="${r.name}">
      <div class="top-item-info">
        <h4>${r.name}</h4>
        <small>${r.saves} محفوظة</small>
      </div>
    </div>
  `).join("");
}

// ==================== RECIPES TABLE ====================
function renderRecipesTable() {
  const tbody = document.getElementById("recipesTableBody");
  if (!tbody) return;

  let filtered = [...recipes];

  // Apply filters
  const category = document.getElementById("filterCategory")?.value;
  const difficulty = document.getElementById("filterDifficulty")?.value;
  const status = document.getElementById("filterStatus")?.value;
  const search = document.getElementById("searchRecipes")?.value.toLowerCase();

  if (category) filtered = filtered.filter(r => r.category === category);
  if (difficulty) filtered = filtered.filter(r => r.difficulty === difficulty);
  if (status) filtered = filtered.filter(r => r.status === status);
  if (search) filtered = filtered.filter(r => r.name.toLowerCase().includes(search));

  tbody.innerHTML = filtered.map(r => `
    <tr data-id="${r.id}">
      <td><input type="checkbox" class="recipe-checkbox" data-id="${r.id}"></td>
      <td>
        <div class="recipe-cell">
          <img src="${r.image}" alt="${r.name}">
          <span>${r.name}</span>
        </div>
      </td>
      <td><span class="tag tag-${getCategoryColor(r.category)}">${r.category}</span></td>
      <td>${r.time}</td>
      <td><span class="tag tag-${getDifficultyColor(r.difficulty)}">${r.difficulty}</span></td>
      <td><span class="badge badge-${getStatusClass(r.status)}">${r.status}</span></td>
      <td>
        <div style="font-size:12px;color:var(--text-muted)">
          <span>❤️ ${r.saves}</span> · 
          <span>👀 ${r.views}</span>
        </div>
      </td>
      <td>
        <div class="row-actions">
          <button class="row-btn edit" onclick="editRecipe(${r.id})">✏️</button>
          <button class="row-btn delete" onclick="confirmDelete('recipe', ${r.id})">🗑️</button>
        </div>
      </td>
    </tr>
  `).join("");

  document.getElementById("recipesCount").textContent = `عرض ${filtered.length} وصفة`;
}

function getCategoryColor(cat) {
  const colors = { "فطار": "purple", "غداء": "orange", "عشاء": "teal", "سلطات": "green", "حلويات": "rose" };
  return colors[cat] || "purple";
}

function getDifficultyColor(diff) {
  const colors = { 
    "سهل جداً": "green", 
    "سهل": "green", 
    "متوسط": "yellow", 
    "صعب": "rose", 
    "صعب جداً": "rose" 
  };
  return colors[diff] || "purple";
}

function getStatusClass(status) {
  const classes = { "نشطة": "active", "مراجعة": "review", "مخفية": "hidden" };
  return classes[status] || "active";
}

// ==================== INGREDIENTS ====================
function renderIngredientsGrid() {
  const grid = document.getElementById("ingredientsGrid");
  if (!grid) return;

  const search = document.getElementById("searchIngredients")?.value.toLowerCase();
  let filtered = [...ingredients];

  if (search) {
    filtered = filtered.filter(i => i.name.includes(search) || i.category.includes(search));
  }

  grid.innerHTML = filtered.map(ing => `
    <div class="ingredient-card" data-id="${ing.id}">
      <div class="emoji">${ing.emoji}</div>
      <div class="name">${ing.name}</div>
      <div class="count">مستخدم في ${ing.usedIn} وصفة</div>
      <div class="actions">
        <button class="ing-btn edit" onclick="editIngredient('${ing.id}')">✏️</button>
        <button class="ing-btn delete" onclick="confirmDelete('ingredient', '${ing.id}')">🗑️</button>
      </div>
    </div>
  `).join("");
}

function renderIngredientSelect() {
  const container = document.getElementById("recipeIngredientsSelect");
  if (!container) return;

  container.innerHTML = ingredients.map(ing => `
    <span class="ing-select-item ${selectedRecipeIngredients.includes(ing.id) ? 'selected' : ''}" 
          onclick="toggleIngredientSelect('${ing.id}')">
      ${ing.emoji} ${ing.name}
    </span>
  `).join("");
}

function toggleIngredientSelect(id) {
  const idx = selectedRecipeIngredients.indexOf(id);
  if (idx > -1) {
    selectedRecipeIngredients.splice(idx, 1);
  } else {
    selectedRecipeIngredients.push(id);
  }
  renderIngredientSelect();
}

// ==================== USERS ====================
function renderUsersTable() {
  const tbody = document.getElementById("usersTableBody");
  if (!tbody) return;

  tbody.innerHTML = users.map(u => `
    <tr>
      <td>
        <div class="user-cell">
          <img src="${u.avatar}" alt="${u.name}">
          <span>${u.name}</span>
        </div>
      </td>
      <td>${u.joinDate}</td>
      <td>${u.saves}</td>
      <td>
        <span class="badge badge-${u.status === 'جديد' ? 'review' : 'active'}">${u.status}</span>
      </td>
      <td>
        <div class="row-actions">
          <button class="row-btn edit">✏️</button>
          <button class="row-btn delete" onclick="confirmDelete('user', ${u.id})">🗑️</button>
        </div>
      </td>
    </tr>
  `).join("");
}

// ==================== CONTACTS ====================
function renderContactsTable() {
  const tbody = document.getElementById("contactsTableBody");
  if (!tbody) return;

  const search = document.getElementById("searchContacts")?.value.toLowerCase();
  let filtered = [...contacts];
  if (search) {
    filtered = filtered.filter(c => 
      (c.name || "").toLowerCase().includes(search) || 
      (c.email || "").toLowerCase().includes(search) || 
      (c.message || "").toLowerCase().includes(search)
    );
  }

  tbody.innerHTML = filtered.map(c => `
    <tr>
      <td>${c.id}</td>
      <td><strong>${c.name || '-'}</strong></td>
      <td>${c.email || '-'}</td>
      <td><div style="max-width:400px; font-size:13px; color:var(--text-muted)">${c.message || '-'}</div></td>
      <td>${c.created_at || '-'}</td>
      <td>
        <div class="row-actions">
           <button class="row-btn delete" onclick="confirmDelete('contact', ${c.id})">🗑️</button>
        </div>
      </td>
    </tr>
  `).join("");
  
  const countEl = document.getElementById("contactsCount");
  if (countEl) countEl.textContent = `عرض ${filtered.length} رسالة`;
}

// ==================== SINE (LOGIN LOG) ====================
function renderSineTable() {
  const tbody = document.getElementById("sineTableBody");
  if (!tbody) return;

  const search = document.getElementById("searchSine")?.value.toLowerCase();
  let filtered = [...sineUsers];
  if (search) {
    filtered = filtered.filter(s => 
      (s.full_name || "").toLowerCase().includes(search) || 
      (s.email || "").toLowerCase().includes(search)
    );
  }

  tbody.innerHTML = filtered.map(s => `
    <tr>
      <td>${s.id}</td>
      <td>
        <div class="user-cell">
          <div class="search-icon-box" style="width:32px; height:32px; font-size:14px;">👤</div>
          <span>${s.full_name || '-'}</span>
        </div>
      </td>
      <td>${s.email || '-'}</td>
      <td><span style="color:var(--purple); font-weight:500;">${s.created_at || '-'}</span></td>
    </tr>
  `).join("");
  
  const countEl = document.getElementById("sineCount");
  if (countEl) countEl.textContent = `عرض ${filtered.length} سجل`;
}

// ==================== SITE CONTENT (ABOUT US) ====================
function renderAboutContent() {
  if (!aboutContent || aboutContent.length === 0) return;
  
  aboutContent.forEach(row => {
    const lang = row.lang;
    ['badge', 'title', 'vision_title', 'vision_text', 'mission_title', 'mission_text'].forEach(col => {
      const el = document.getElementById(`about_${lang}_${col}`);
      if (el) el.value = row[col] || '';
    });
  });
}

async function saveAboutContent() {
  const data = {};
  const langs = ['ar', 'en'];
  const cols = ['badge', 'title', 'vision_title', 'vision_text', 'mission_title', 'mission_text'];

  langs.forEach(lang => {
    data[lang] = {};
    cols.forEach(col => {
      const el = document.getElementById(`about_${lang}_${col}`);
      if (el) data[lang][col] = el.value;
    });
  });

  try {
    showToast("جاري الحفظ...", "⏳");
    const res = await fetch("api.php?action=updateAboutContent", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ contents: data })
    });
    const json = await res.json();

    if (json.status === 'success') {
      showToast("تم حفظ محتوى صفحة عنّا بنجاح", "✅");
      fetchAboutContent();
    } else {
      showToast("خطأ: " + json.message, "❌");
    }
  } catch (err) {
    console.error("Fetch Error:", err);
    showToast("خطأ في الاتصال بالسيرفر", "❌");
  }
}

// ==================== FAVORITES ====================
function renderFavoritesGrid() {
  const grid = document.getElementById("favoritesGrid");
  if (!grid) return;

  const favRecipes = favorites.map(f => {
    const recipe = recipes.find(r => r.id === f.recipeId);
    const user = users.find(u => u.id === f.userId);
    return { ...recipe, userName: user?.name, date: f.date };
  });

  grid.innerHTML = favRecipes.map(r => `
    <div class="favorite-card">
      <img src="${r.image}" alt="${r.name}">
      <div class="favorite-card-body">
        <h4>${r.name}</h4>
        <p>${r.category} • ${r.time}</p>
      </div>
      <div class="favorite-card-foot">
        <small>بواسطة ${r.userName}</small>
        <small>${r.date}</small>
      </div>
    </div>
  `).join("");
}

// ==================== ANALYTICS ====================
function renderAnalyticsCharts() {
  const charts = ["viewsChart", "searchChart", "likesChart", "sharesChart"];
  const heights = [70, 45, 60, 35];

  charts.forEach((id, idx) => {
    const container = document.getElementById(id);
    if (!container) return;

    container.innerHTML = Array(7).fill(0).map(() => {
      const h = Math.random() * heights[idx] + 20;
      return `<div class="bar" style="height:${h}%"></div>`;
    }).join("");
  });
}

function renderTopViewedRecipes() {
  const container = document.getElementById("topViewedRecipes");
  if (!container) return;

  const top = [...recipes].sort((a, b) => b.views - a.views).slice(0, 3);

  container.innerHTML = top.map((r, i) => `
    <div class="top-item">
      <span class="rank rank-${i + 1}">${i + 1}</span>
      <img src="${r.image}" alt="${r.name}">
      <div class="top-item-info">
        <h4>${r.name}</h4>
        <small>${r.views} مشاهدة</small>
      </div>
    </div>
  `).join("");
}

function renderTopSearches() {
  const container = document.getElementById("topSearches");
  if (!container) return;

  const searches = [
    { term: "مكرونة", count: 245 },
    { term: "بطاطس", count: 198 },
    { term: "فراخ", count: 167 },
    { term: "بيض", count: 134 }
  ];

  container.innerHTML = searches.map((s, i) => `
    <div class="top-item">
      <span class="rank rank-${i + 1}">${i + 1}</span>
      <div class="top-item-info">
        <h4>${s.term}</h4>
        <small>${s.count} بحث</small>
      </div>
    </div>
  `).join("");
}

// ==================== FILTERS ====================
function initFilters() {
  ["filterCategory", "filterDifficulty", "filterStatus", "searchRecipes"].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("input", renderRecipesTable);
      el.addEventListener("change", renderRecipesTable);
    }
  });

  const ingSearch = document.getElementById("searchIngredients");
  if (ingSearch) ingSearch.addEventListener("input", renderIngredientsGrid);

  const duSearch = document.getElementById("searchDashUsers");
  if (duSearch) duSearch.addEventListener("input", renderDashUsersTable);

  const contactSearch = document.getElementById("searchContacts");
  if (contactSearch) contactSearch.addEventListener("input", renderContactsTable);

  const sineSearch = document.getElementById("searchSine");
  if (sineSearch) sineSearch.addEventListener("input", renderSineTable);
}

// ==================== SMART GLOBAL SEARCH ====================
function initSearch() {
  const globalSearch = document.getElementById("globalSearch");
  const dropdown = document.getElementById("searchDropdown");
  if (!globalSearch || !dropdown) return;

  globalSearch.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase();

    if (document.activeElement !== this && query.includes('@')) {
      dropdown.classList.remove("active");
      return;
    }

    if (query.length < 1) {
      dropdown.classList.remove("active");
      return;
    }

    const matchedRecipes = recipes.filter(r => r.name.toLowerCase().includes(query) || r.category.toLowerCase().includes(query));
    const matchedIngredients = ingredients.filter(i => i.name.toLowerCase().includes(query));
    const matchedUsers = [
      ...users.filter(u => u.name.toLowerCase().includes(query)),
      ...dashUsers.filter(u => u.name.toLowerCase().includes(query) || u.email.toLowerCase().includes(query)),
      ...sineUsers.filter(u => (u.full_name || "").toLowerCase().includes(query) || (u.email || "").toLowerCase().includes(query))
    ];

    const matchedContacts = contacts.filter(c => 
      (c.name || "").toLowerCase().includes(query) || 
      (c.email || "").toLowerCase().includes(query) || 
      (c.message || "").toLowerCase().includes(query)
    );

    if (matchedRecipes.length === 0 && matchedIngredients.length === 0 && matchedUsers.length === 0 && matchedContacts.length === 0) {
      dropdown.innerHTML = `<div class="search-empty">لا توجد نتائج لـ "${query}"</div>`;
    } else {
      let html = "";

      if (matchedRecipes.length > 0) {
        html += `<div class="search-cat">🍲 الوصفات</div>`;
        matchedRecipes.slice(0, 3).forEach(r => {
          html += `
            <div class="search-item" onclick="navigateTo('recipes'); searchHighlight('${r.name}');">
              <img src="${r.image}">
              <div>
                <strong>${r.name}</strong>
                <small>${r.category}</small>
              </div>
            </div>
          `;
        });
      }

      if (matchedIngredients.length > 0) {
        html += `<div class="search-cat">🧂 المكونات</div>`;
        matchedIngredients.slice(0, 3).forEach(i => {
          html += `
            <div class="search-item" onclick="navigateTo('ingredients'); searchHighlight('${i.name}');">
              <div class="search-icon-box">🍅</div>
              <div>
                <strong>${i.name}</strong>
                <small>مكوّن أساسي</small>
              </div>
            </div>
          `;
        });
      }

      if (matchedUsers.length > 0) {
        html += `<div class="search-cat">👥 المستخدمين</div>`;
        matchedUsers.slice(0, 3).forEach(u => {
          html += `
            <div class="search-item" onclick="navigateTo('users');">
              <img src="${u.avatar}">
              <div>
                <strong>${u.name}</strong>
                <small>تم الانضمام: ${u.joinDate}</small>
              </div>
            </div>
          `;
        });
      }

      if (matchedContacts.length > 0) {
        html += `<div class="search-cat">📧 الرسائل</div>`;
        matchedContacts.slice(0, 3).forEach(c => {
          html += `
            <div class="search-item" onclick="navigateTo('contacts'); searchHighlight('${c.name}');">
              <div class="search-icon-box">✉️</div>
              <div>
                <strong>${c.name}</strong>
                <small>${c.email}</small>
              </div>
            </div>
          `;
        });
      }

      dropdown.innerHTML = html;
    }
    dropdown.classList.add("active");
  });

  document.addEventListener("click", (e) => {
    if (!globalSearch.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove("active");
    }
  });
}

function searchHighlight(query) {
  const dropdown = document.getElementById("searchDropdown");
  if (dropdown) dropdown.classList.remove("active");
  // Set filter in target page
  const sectionInput = document.querySelector(".section.active .search-box input");
  if (sectionInput) {
    sectionInput.value = query;
    sectionInput.dispatchEvent(new Event('input'));
  }
}

// ==================== CRUD OPERATIONS ====================
function renderIngredientSelect() {
  const container = document.getElementById("recipeIngredientsSelect");
  if (!container) return;

  if (ingredients.length === 0) {
    container.innerHTML = "<p style='color:var(--text-muted); font-size:14px; padding:10px;'>لا توجد مكونات مسجلة بعد، أضف بعض المكونات أولاً.</p>";
    return;
  }

  container.innerHTML = ingredients.map(ing => {
    const isSelected = selectedRecipeIngredients.includes(ing.id);
    return `
      <div class="ingredient-badge ${isSelected ? 'selected' : ''}" 
           onclick="toggleIngredientSelect('${ing.id}')"
           style="cursor:pointer; display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background: ${isSelected ? 'var(--purple-light)' : 'var(--card-bg)'}; border: 1px solid ${isSelected ? 'var(--purple)' : 'var(--border)'}; border-radius: 20px; margin: 0 4px 8px 0; transition: 0.2s; box-shadow: ${isSelected ? '0 0 8px rgba(124, 92, 252, 0.4)' : 'none'}; color: ${isSelected ? 'var(--purple)' : 'var(--text-primary)'}">
        <span>${ing.emoji}</span>
        <span style="font-weight: 600;">${ing.name}</span>
      </div>
    `;
  }).join('');
}

function toggleIngredientSelect(id) {
  const numId = Number(id) || id; // Parse if it is numeric DB id
  const index = selectedRecipeIngredients.indexOf(numId);
  const strIndex = selectedRecipeIngredients.indexOf(String(id));
  
  if (index === -1 && strIndex === -1) {
    selectedRecipeIngredients.push(numId);
  } else {
    selectedRecipeIngredients.splice(index !== -1 ? index : strIndex, 1);
  }
  renderIngredientSelect();
}

async function saveRecipe() {
  const name = document.getElementById("recipeName").value.trim();
  const category = document.getElementById("recipeCategory").value;
  const time = document.getElementById("recipeTime").value.trim();
  const difficulty = document.getElementById("recipeDifficulty").value;
  const image = document.getElementById("recipeImageBase64").value.trim() || 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe';
  const steps = document.getElementById("recipeSteps").value.trim();
  const status = document.getElementById("recipeStatus").value;

  if (!name || !category) {
    showToast("يرجى ملء الحقول المطلوبة", "⚠️");
    return;
  }

  const actionObj = {
    action: "saveRecipe",
    id: editingRecipeId,
    name, category, time, difficulty, image,
    steps: steps.split("\n").filter(s => s.trim()),
    status,
    ingredients: selectedRecipeIngredients
  };

  try {
    const res = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(actionObj)
    });
    const json = await res.json();
    if (json.status === 'success') {
      showToast("تم حفظ الوصفة بنجاح", "✅");
      closeModal("recipe");
      fetchData();
    } else {
      showToast("خطأ: " + json.message, "❌");
    }
  } catch (err) {
    showToast("خطأ في الاتصال", "❌");
  }
}

function editRecipe(id) {
  const recipe = recipes.find(r => r.id === id);
  if (!recipe) return;

  editingRecipeId = id;
  document.getElementById("recipeModalTitle").textContent = "تعديل الوصفة";
  document.getElementById("recipeName").value = recipe.name;
  document.getElementById("recipeCategory").value = recipe.category;
  document.getElementById("recipeTime").value = recipe.time;
  document.getElementById("recipeDifficulty").value = recipe.difficulty;
  document.getElementById("recipeImageBase64").value = recipe.image;
  
  const previewContainer = document.getElementById("recipeImagePreviewContainer");
  const previewImg = document.getElementById("recipeImagePreview");
  if (recipe.image) {
    previewImg.src = recipe.image;
    previewContainer.style.display = "block";
  } else {
    previewContainer.style.display = "none";
  }

  document.getElementById("recipeSteps").value = recipe.steps.join("\n");
  document.getElementById("recipeStatus").value = recipe.status;
  selectedRecipeIngredients = [...recipe.ingredients];

  openModal("recipe");
}

function resetRecipeForm() {
  editingRecipeId = null;
  selectedRecipeIngredients = [];
  document.getElementById("recipeModalTitle").textContent = "إضافة وصفة جديدة";
  document.getElementById("recipeName").value = "";
  document.getElementById("recipeCategory").value = "";
  document.getElementById("recipeTime").value = "";
  document.getElementById("recipeDifficulty").value = "سهل";
  document.getElementById("recipeImageFile").value = "";
  document.getElementById("recipeImageBase64").value = "";
  document.getElementById("recipeImagePreviewContainer").style.display = "none";
  document.getElementById("recipeSteps").value = "";
  document.getElementById("recipeStatus").value = "نشطة";
}

async function saveIngredient() {
  const nameInput = document.getElementById("ingredientName");
  const name = nameInput.value.trim();
  const emoji = document.getElementById("ingredientEmoji").value.trim();
  const category = document.getElementById("ingredientCategory").value;
  const editId = nameInput.dataset.editId || null;

  if (!name) {
    showToast("يرجى إدخال اسم المكوّن", "⚠️");
    return;
  }

  const actionObj = {
    action: "saveIngredient",
    id: editId ? editId : name.toLowerCase().replace(/\s/g, "-"),
    name, emoji: emoji || "🍽️", category
  };

  try {
    const res = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(actionObj)
    });
    const json = await res.json();
    if (json.status === 'success') {
      showToast(editId ? "تم تعديل المكوّن بنجاح" : "تمت إضافة المكوّن بنجاح", "✅");
      // Reset edit ID
      nameInput.dataset.editId = "";
      closeModal("ingredient");
      fetchData();
    } else {
      showToast("خطأ: " + json.message, "❌");
    }
  } catch (err) {
    showToast("خطأ في الاتصال", "❌");
  }
}

async function saveUser() {
  const name = document.getElementById("userName").value.trim();
  const email = document.getElementById("userEmail").value.trim();
  const role = document.getElementById("userRole").value;

  if (!name || !email) {
    showToast("يرجى ملء الحقول المطلوبة", "⚠️");
    return;
  }

  const actionObj = {
    action: "saveUser",
    name, email, role,
    avatar: `https://i.pravatar.cc/100?img=${Math.floor(Math.random() * 70)}`,
    joinDate: new Date().toISOString().split("T")[0],
    status: "جديد"
  };

  try {
    const res = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(actionObj)
    });
    const json = await res.json();
    if (json.status === 'success') {
      showToast("تمت إضافة المستخدم بنجاح", "✅");
      closeModal("user");
      fetchData();
    } else {
      showToast("خطأ: " + json.message, "❌");
    }
  } catch (err) {
    showToast("خطأ في الاتصال", "❌");
  }
}

// ==================== DELETE ====================
function confirmDelete(type, id) {
  let actionName = "";
  if      (type === "recipe")     actionName = "deleteRecipe";
  else if (type === "ingredient") actionName = "deleteIngredient";
  else if (type === "user")       actionName = "deleteUser";
  else if (type === "dashuser")   actionName = "deleteDashUser";
  else if (type === "contact")    actionName = "deleteContact";

  deleteCallback = async () => {
    try {
      const res = await fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: actionName, id: id })
      });
      const json = await res.json();
      if (json.status === 'success') {
        showToast("تم الحذف بنجاح", "✅");
        if (type === "dashuser") fetchDashUsers();
        else if (type === "contact") fetchContacts();
        else fetchData();
      } else {
        showToast("خطأ: " + json.message, "❌");
      }
    } catch (err) {
      showToast("خطأ في الاتصال", "❌");
    }
  };

  openModal("delete");

  const confirmBtn = document.getElementById("confirmDeleteBtn");
  if (confirmBtn) {
    confirmBtn.onclick = () => {
      if (deleteCallback) deleteCallback();
      closeModal("delete");
      deleteCallback = null;
    };
  }
}

// ==================== SETTINGS ====================
function saveSettings() {
  const name = document.getElementById("settingName").value;
  const email = document.getElementById("settingEmail").value;
  const phone = document.getElementById("settingPhone").value;

  showToast("تم حفظ الإعدادات بنجاح", "✅");
}

// ==================== TOAST ====================
function showToast(message, icon = "✅") {
  const toast = document.getElementById("toast");
  const msgEl = document.getElementById("toastMessage");
  const iconEl = toast?.querySelector(".toast-icon");

  if (toast && msgEl) {
    msgEl.textContent = message;
    if (iconEl) iconEl.textContent = icon;

    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
    }, 3000);
  }
}

// ==================== NOTIFICATIONS ====================
document.getElementById("notifBtn")?.addEventListener("click", () => {
  const badge = document.querySelector(".notif-badge");
  if (badge) badge.style.display = "none";
  showToast("لا توجد إشعارات جديدة", "🔔");
});

// ==================== EDIT INGREDIENT ====================
function editIngredient(id) {
  const ing = ingredients.find(i => i.id == id);
  if (!ing) return;

  document.getElementById("ingredientName").value = ing.name;
  document.getElementById("ingredientEmoji").value = ing.emoji;
  document.getElementById("ingredientCategory").value = ing.category;
  document.getElementById("ingredientName").dataset.editId = ing.id;

  openModal("ingredient");
}

// ==================== DASH USERS ====================

function renderDashUsersTable() {
  const tbody = document.getElementById("dashUsersTableBody");
  if (!tbody) return;

  const search = document.getElementById("searchDashUsers")?.value.toLowerCase() || "";
  const filtered = dashUsers.filter(u =>
    u.name.toLowerCase().includes(search) ||
    u.email.toLowerCase().includes(search)
  );

  // Stats
  const totalEl  = document.getElementById("totalDashUsers");
  const adminEl  = document.getElementById("adminDashUsers");
  const editorEl = document.getElementById("editorDashUsers");
  if (totalEl)  totalEl.textContent  = dashUsers.length;
  if (adminEl)  adminEl.textContent  = dashUsers.filter(u => u.role === "admin").length;
  if (editorEl) editorEl.textContent = dashUsers.filter(u => u.role === "editor").length;

  const roleLabel = { admin: "مدير", editor: "محرر", viewer: "مشاهد" };
  const roleClass = { admin: "active", editor: "review", viewer: "hidden" };

  tbody.innerHTML = filtered.map((u, i) => `
    <tr>
      <td style="color:var(--text-muted);font-weight:700">${i + 1}</td>
      <td>
        <div class="user-cell">
          <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--purple),#a78bfa);display:grid;place-items:center;color:#fff;font-weight:800;font-size:14px;flex-shrink:0">
            ${u.name.charAt(0).toUpperCase()}
          </div>
          <span style="font-weight:600">${u.name}</span>
        </div>
      </td>
      <td style="color:var(--text-secondary)">${u.email}</td>
      <td><span class="badge badge-${roleClass[u.role] || 'review'}">${roleLabel[u.role] || u.role}</span></td>
      <td style="color:var(--text-muted);font-size:13px">${u.created_at ? u.created_at.split(' ')[0] : '—'}</td>
      <td>
        <div class="row-actions">
          <button class="row-btn edit" onclick="editDashUser(${u.id})" title="تعديل">✏️</button>
          <button class="row-btn delete" onclick="confirmDelete('dashuser', ${u.id})" title="حذف">🗑️</button>
        </div>
      </td>
    </tr>
  `).join("");

  const countEl = document.getElementById("dashUsersCount");
  if (countEl) countEl.textContent = `عرض ${filtered.length} حساب`;
}

function editDashUser(id) {
  const user = dashUsers.find(u => u.id == id);
  if (!user) return;

  editingDashUserId = id;
  document.getElementById("dashuserModalTitle").textContent = "تعديل الحساب";
  document.getElementById("dashuserName").value     = user.name;
  document.getElementById("dashuserEmail").value    = user.email;
  document.getElementById("dashuserPassword").value = "";   // never pre-fill password
  document.getElementById("dashuserRole").value     = user.role;

  openModal("dashuser");
}

async function saveDashUser() {
  const name     = document.getElementById("dashuserName").value.trim();
  const email    = document.getElementById("dashuserEmail").value.trim();
  const password = document.getElementById("dashuserPassword").value.trim();
  const role     = document.getElementById("dashuserRole").value;

  if (!name || !email) {
    showToast("يرجى ملء الاسم والبريد الإلكتروني", "⚠️");
    return;
  }
  if (!editingDashUserId && !password) {
    showToast("كلمة المرور مطلوبة عند إضافة حساب جديد", "⚠️");
    return;
  }

  const payload = {
    action:   "saveDashUser",
    id:       editingDashUserId || null,
    name, email, password, role
  };

  try {
    const res  = await fetch("api.php", {
      method:  "POST",
      headers: { "Content-Type": "application/json" },
      body:    JSON.stringify(payload)
    });
    const json = await res.json();

    if (json.status === "success") {
      showToast(json.message || "تم حفظ الحساب بنجاح", "✅");
      closeModal("dashuser");
      fetchDashUsers();
    } else {
      showToast("خطأ: " + json.message, "❌");
    }
  } catch (err) {
    showToast("خطأ في الاتصال", "❌");
    console.error(err);
  }
}

// ==================== TRANSLATION ====================
function initLanguage() {
  const langBtn = document.getElementById("langBtn");
  if (!langBtn) return;

  let currentLang = localStorage.getItem("bitesight_lang") || "ar";

  // Initial Sync (already partially done by Head Script, but here for consistency)
  if (currentLang === "en") {
    langBtn.textContent = "🇦🇪 AR";
    document.documentElement.dir = "ltr";
  } else {
    langBtn.textContent = "🇺🇸 EN";
    document.documentElement.dir = "rtl";
  }

  langBtn.addEventListener("click", () => {
    if (currentLang === "ar") {
      localStorage.setItem("bitesight_lang", "en");
      // Use both domain variants for max compatibility
      document.cookie = "googtrans=/auto/en; path=/";
      document.cookie = "googtrans=/auto/en; path=/; domain=" + window.location.hostname;
    } else {
      localStorage.setItem("bitesight_lang", "ar");
      document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      document.cookie = "googtrans=/auto/ar; path=/; domain=" + window.location.hostname;
    }
    
    window.location.reload();
  });
}
