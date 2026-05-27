function recalculateAdminStats() {
  if (!window.mockData) return;
  if (!window.mockData.adminStats) {
    window.mockData.adminStats = {};
  }
  
  window.mockData.adminStats.totalListings = window.mockData.listings.length;
  window.mockData.adminStats.totalUsers = window.mockData.users.length;
  window.mockData.adminStats.activeExchangeItems = window.mockData.exchangeItems.filter(it => it.status !== 'sold').length;
  window.mockData.adminStats.openComplaints = window.mockData.complaints.filter(c => c.status !== 'Resolved').length;
  window.mockData.adminStats.pendingVerifications = window.mockData.verifs.filter(v => !v.approved).length;
  
  window.mockData.adminStats.avgRentByZone = window.mockData.zones.map(z => {
    const zoneListings = window.mockData.listings.filter(l => l.zoneId === z.id);
    const avgRent = zoneListings.length > 0
      ? Math.round(zoneListings.reduce((sum, l) => sum + (Number(l.costs.baseRent) || 0), 0) / zoneListings.length)
      : 0;
    return { zone: z.name, avg: avgRent };
  });
  
  window.mockData.adminStats.listingsByZone = window.mockData.zones.map(z => {
    return window.mockData.listings.filter(l => l.zoneId === z.id).length;
  });
  
  window.mockData.adminStats.seekingVsListings = window.mockData.zones.map(z => {
    const seeking = window.mockData.seekingPosts.filter(p => p.zone === z.name).length;
    const listings = window.mockData.listings.filter(l => l.zoneId === z.id).length;
    return { zone: z.name, seeking, listings };
  });
}

let currentMode = localStorage.getItem('uiunest_data_mode') === 'clean' ? false : true;

// Initialize database in localStorage if not exists
if (!localStorage.getItem('uiunest_db_v4')) {
  window.mockData = window.getInitialData(currentMode);
  recalculateAdminStats();
  localStorage.setItem('uiunest_db_v4', JSON.stringify(window.mockData));
} else {
  window.mockData = JSON.parse(localStorage.getItem('uiunest_db_v4'));
}

function toggleDataMode(populated) {
  const data = window.getInitialData(populated);
  localStorage.setItem('uiunest_db_v4', JSON.stringify(data));
  localStorage.setItem('uiunest_data_mode', populated ? 'populated' : 'clean');
  
  // also reset current user
  localStorage.removeItem('uiunest_logged_in_v4');
  localStorage.removeItem('uiunest_current_user_v4');
  location.href = 'index.html';
}

function isUserVerified(email) {
  return window.mockData.verifs.some(v => v.email === email && v.approved);
}

// Session currentUser initialization
if (localStorage.getItem('uiunest_current_user_v4')) {
  window.mockData.currentUser = JSON.parse(localStorage.getItem('uiunest_current_user_v4'));
} else if (window.mockData && window.mockData.users) {
  // Default to student user
  window.mockData.currentUser = window.mockData.users[0];
  localStorage.setItem('uiunest_current_user_v4', JSON.stringify(window.mockData.currentUser));
}

function saveMockData() {
  recalculateAdminStats();
  localStorage.setItem('uiunest_db_v4', JSON.stringify(window.mockData));
  if (window.mockData.currentUser) {
    localStorage.setItem('uiunest_current_user_v4', JSON.stringify(window.mockData.currentUser));
  }
}

function setCurrentUser(user) {
  window.mockData.currentUser = user;
  localStorage.setItem('uiunest_current_user_v4', JSON.stringify(user));
  localStorage.setItem('uiunest_logged_in_v4', 'true');
  saveMockData();
}


// ============ Utilities ============
function fmt(n) { return '৳' + Number(n).toLocaleString(); }
function stars(score) {
  const full = Math.round(score || 0);
  return '<i data-lucide="star" style="fill: currentColor" class="lucide-sm"></i>'.repeat(full) + '<i data-lucide="star" class="lucide-sm"></i>'.repeat(5 - full);
}
function isLoggedIn() { return localStorage.getItem('uiunest_logged_in_v4') === 'true'; }
function toggleDemoLogin() {
  const c = isLoggedIn();
  localStorage.setItem('uiunest_logged_in_v4', String(!c));
  if (!c) {
    // If logging in, make sure we have a session
    localStorage.setItem('uiunest_current_user_v4', JSON.stringify(window.mockData.users[0]));
  }
  location.reload();
}
function showToast(msg, type = 'success') {
  document.querySelectorAll('.toast').forEach(el => el.remove());
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

function calculateCompatibility(listing) {
  const user = window.user || window.mockData.currentUser;
  if (!user || user.role !== 'student') {
    return { score: null, label: 'N/A', matched: [], unmatched: [] };
  }

  // If gender preference is violated (unless genderPref is 'any' or listing has no pref)
  if (listing.genderPref && listing.genderPref !== 'any' && listing.genderPref !== user.gender) {
    return { score: 0, label: 'Low Match', matched: [], unmatched: ['Gender Preference'] };
  }

  // Resident preferences vs current user preferences
  const up = user.preferences || {
    sleep: user.sleep || "flexible",
    diet: user.diet || "non_veg",
    guest: user.guest || "restricted",
    smoking: user.smoking || 0,
    cleanliness: user.cleanliness || 3,
    noise: user.noise || "moderate"
  };

  const lp = listing.residentPreferences || {
    sleep: listing.sleep || "flexible",
    diet: listing.diet || "non_veg",
    guest: listing.guest || "restricted",
    smoking: listing.smoking || 0,
    noise: listing.noise || "moderate",
    cleanliness: listing.cleanliness || 3
  };

  let score = 0;
  const matched = [];
  const unmatched = [];

  // Sleep
  if (up.sleep === lp.sleep) { score += 16; matched.push('sleep schedule'); } else { unmatched.push('sleep schedule'); }
  // Diet
  if (up.diet === lp.diet) { score += 16; matched.push('diet'); } else { unmatched.push('diet'); }
  // Guest
  if (up.guest === lp.guest) { score += 16; matched.push('guest policy'); } else { unmatched.push('guest policy'); }
  // Smoking
  if (up.smoking === lp.smoking) { score += 16; matched.push('smoking preference'); } else { unmatched.push('smoking preference'); }
  // Noise
  if (up.noise === lp.noise) { score += 16; matched.push('noise tolerance'); } else { unmatched.push('noise tolerance'); }
  // Cleanliness
  const diff = Math.abs(parseInt(up.cleanliness || 4) - parseInt(lp.cleanliness || 4));
  if (diff <= 1) { score += 20; matched.push('cleanliness'); } else if (diff === 2) { score += 10; matched.push('cleanliness'); } else { unmatched.push('cleanliness'); }

  return {
    score: score,
    matched,
    unmatched
  };
}

function getCompatibilityColor(score) {
  if (score >= 80) return { bg: 'linear-gradient(135deg, #34d399, #059669)', color: '#ffffff', label: 'High Match' };
  if (score >= 50) return { bg: 'linear-gradient(135deg, #60a5fa, #2563eb)', color: '#ffffff', label: 'Good Match' };
  return { bg: 'linear-gradient(135deg, #fbbf24, #d97706)', color: '#ffffff', label: 'Low Match' };
}
function getQueryId() {
  const p = new URLSearchParams(window.location.search);
  return parseInt(p.get('id'));
}

window.userWatchlist = [];

async function loadCloudWatchlist() {
  if (!isLoggedIn()) return;
  try {
    const res = await fetch('api/watchlist.php');
    const data = await res.json();
    if (data.success) {
      window.userWatchlist = data.watchlist;
      // Sync initial state of existing hearts (if any render synchronously)
      document.querySelectorAll('.heart').forEach(h => {
        const id = parseInt(h.getAttribute('data-wl'));
        const icon = h.querySelector('i, svg');
        if (window.userWatchlist.includes(id)) {
          h.classList.add('active'); if (icon) icon.style.fill = 'currentColor';
        } else {
          h.classList.remove('active'); if (icon) icon.style.fill = 'none';
        }
      });
    }
  } catch (err) { console.error(err); }
}

function getWatchlist() {
  return window.userWatchlist || [];
}

// Global Event Delegation for Wishlist Hearts
document.body.addEventListener('click', e => {
  const h = e.target.closest('.heart');
  if (h) {
    e.preventDefault();
    const id = parseInt(h.getAttribute('data-wl'));
    toggleWatchlist(id).then(active => {
      const icon = h.querySelector('i, svg');
      if (active) { h.classList.add('active'); if (icon) icon.style.fill = 'currentColor'; }
      else { h.classList.remove('active'); if (icon) icon.style.fill = 'none'; }
      showToast(active ? 'Saved to watchlist' : 'Removed from watchlist');
    });
  }
});

async function toggleWatchlist(id) {
  if (!isLoggedIn()) {
    showToast("Please log in to save properties.", "error");
    return false;
  }
  
  const w = window.userWatchlist;
  const i = w.indexOf(id);
  const willBeActive = (i < 0);
  if (willBeActive) w.push(id); else w.splice(i, 1);
  
  try {
    fetch('api/watchlist.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({listingId: id})
    });
  } catch(e) { console.error(e); }
  
  return willBeActive;
}

// Automatically load watchlist when file is imported
loadCloudWatchlist();

// ============ Nav ============
function renderNav(active) {
  const logged = isLoggedIn();
  const user = window.mockData.currentUser;
  
  let links = [
    { href: 'index.html',    label: 'Home',     key: 'home',     icon: 'home' },
    { href: 'listings.html', label: 'Listings', key: 'listings', icon: 'building-2' },
    { href: 'exchange.html', label: 'Market',   key: 'exchange', icon: 'shopping-bag' },
    { href: 'seeking.html',  label: 'Seeking',  key: 'seeking',  icon: 'search' }
  ];
  if (user && user.role === 'landlord') {
    links = links.filter(l => l.key !== 'seeking');
  }
  // Create the mobile account tab (or login button)
  const accountTab = logged ? `
    <button class="mobile-nav-extra mobile-account-btn" onclick="toggleMobileAccountSheet()">
      <span style="position:relative">
        <i data-lucide="user-circle" class="nav-icon"></i>
        <span id="notifBadgeMob" style="display:none;position:absolute;top:2px;right:2px;background:var(--danger);width:8px;height:8px;border-radius:50%;"></span>
      </span>
      <span class="nav-label">Account</span>
    </button>
    <!-- Account mini-sheet (shown above the tab bar) -->
    <div id="mobileAccountSheet" class="mobile-account-sheet">
      <div class="mas-header">
        <span class="mas-name">${user.name || 'Account'}</span>
        <span class="mas-role">${user.role || 'student'}</span>
      </div>
      <a href="dashboard.html" class="mas-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="profile.html" class="mas-item"><i data-lucide="user"></i> Profile</a>
      ${user.role !== 'admin' ? '<a href="dashboard.html?tab=watch" class="mas-item"><i data-lucide="heart"></i> Watchlist</a>' : ''}
      ${user.role !== 'admin' ? '<a href="bills.html" class="mas-item"><i data-lucide="receipt"></i> Bills</a>' : ''}
      <button class="mas-item mas-notif" onclick="event.stopPropagation(); toggleNotifs(); toggleMobileAccountSheet()">
        <i data-lucide="bell"></i> Notifications
        <span id="notifBadge" style="display:none;margin-left:auto;background:var(--danger);color:white;font-size:10px;font-weight:bold;width:18px;height:18px;border-radius:50%;line-height:18px;text-align:center"></span>
      </button>
      <button class="mas-item mas-logout" onclick="doLogout()"><i data-lucide="log-out"></i> Logout</button>
    </div>` :
    `<a href="${active === 'register' ? 'register.html' : 'login.html'}" class="mobile-nav-extra mobile-account-btn ${['login', 'register'].includes(active) ? 'active' : ''}"><i data-lucide="${active === 'register' ? 'user-plus' : 'log-in'}" class="nav-icon"></i><span class="nav-label">${active === 'register' ? 'Register' : 'Login'}</span></a>`;

  // Insert the account tab in the middle of the links (index 2)
  const linkHTMLElements = links.map(l =>
    `<a href="${l.href}" class="${active === l.key ? 'active' : ''}"><i data-lucide="${l.icon}" class="nav-icon"></i><span class="nav-label">${l.label}</span></a>`);
  linkHTMLElements.splice(2, 0, accountTab);
  const linkHTML = linkHTMLElements.join('');

  const avatarDisplay = (user && user.profile_pic)
    ? `<img src="${user.profile_pic}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`
    : (user && user.name ? user.name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2) : 'US');

  const rightHTML = logged
    ? `<div style="position:relative;display:inline-block">
         ${user.role === 'admin' ? '' : `
         <button id="notifBtn" class="icon-btn" title="Notifications" onclick="toggleNotifs()"><i data-lucide="bell" class="lucide-sm"></i><span id="notifBadgeDesk" style="display:none;position:absolute;top:-2px;right:-2px;background:var(--danger);color:white;font-size:10px;font-weight:bold;width:16px;height:16px;border-radius:50%;align-items:center;justify-content:center"></span></button>
         `}
       </div>
       ${user.role === 'admin' ? '' : `<a class="icon-btn" href="dashboard.html?tab=watch" title="Watchlist"><i data-lucide="heart" class="lucide-sm"></i></a>`}
       <div class="avatar" id="avatarBtn" onclick="document.getElementById('avatarMenu').classList.toggle('open')">${avatarDisplay}
         <div class="avatar-menu" id="avatarMenu">
           <a href="dashboard.html">Dashboard</a>
           <a href="profile.html">Profile</a>
           ${user.role === 'admin' ? '' : '<a href="bills.html">Bills</a>'}
           ${user.role === 'admin' ? '<a href="admin.html">Admin Panel</a>' : ''}
           <a href="#" onclick="event.preventDefault();doLogout()">Logout</a>
         </div>
       </div>`
    : `<a class="btn btn-outline" href="login.html">Login</a>
       <a class="btn btn-primary" href="register.html">Register</a>`;

  return `
    <nav class="navbar">
      <div class="nav-inner" style="position:relative">
        <button class="mobile-menu-btn" onclick="document.querySelector('.nav-links').classList.toggle('open')" style="display:none;background:none;border:none;cursor:pointer;padding:8px;margin-right:8px"><i data-lucide="menu" class="lucide-lg"></i></button>
        <a href="index.html" class="nav-logo"><span class="logo-uiu">UIU</span><span class="logo-nest">Nest</span></a>
        <div class="nav-links">${linkHTML}</div>
        <div class="nav-right">${rightHTML}</div>
      </div>
    </nav>
    ${logged && user.role !== 'admin' ? `
    <div class="avatar-menu notif-menu-global" id="notifMenu">
      <div style="font-weight:600;border-bottom:1px solid var(--border);padding-bottom:8px;margin-bottom:8px">Notifications</div>
      <div id="notifList" style="max-height:300px;overflow-y:auto;font-size:13px;color:var(--gray);margin-bottom:8px;">Loading...</div>
      <button class="btn btn-outline btn-block btn-sm" onclick="markNotifRead('all', '', event)">Mark all as read</button>
    </div>` : ''}`;
}

function toggleMobileAccountSheet() {
  const sheet = document.getElementById('mobileAccountSheet');
  if (sheet) {
    sheet.classList.toggle('open');
    if (!sheet.classList.contains('open') && window._resetMobileNav) {
      window._resetMobileNav();
    }
  }
}
// Close sheet when tapping outside
document.addEventListener('click', (e) => {
  const sheet = document.getElementById('mobileAccountSheet');
  const btn = document.querySelector('.mobile-account-btn');
  if (sheet && sheet.classList.contains('open') && !sheet.contains(e.target) && e.target !== btn && !btn?.contains(e.target)) {
    sheet.classList.remove('open');
    if (window._resetMobileNav) window._resetMobileNav();
  }
});

async function doLogout() {
  try { await fetch('api/logout.php'); } catch(e) {}
  localStorage.removeItem('uiunest_logged_in_v4');
  localStorage.removeItem('uiunest_current_user_v4');
  location.href = 'index.html';
}

function toggleNotifs() {
  const m = document.getElementById('notifMenu');
  const btn = document.getElementById('notifBtn');
  if (m) {
    if (!m.classList.contains('open')) {
      if (window.innerWidth > 1024 && btn) {
        const rect = btn.getBoundingClientRect();
        // Position directly under the bell icon
        m.style.top = (rect.bottom + 14) + 'px';
        m.style.left = (rect.right - 280) + 'px'; // 280 is menu width
        m.style.right = 'auto';
        m.style.bottom = 'auto';
      } else {
        // Reset inline styles on mobile so CSS handles the centering
        m.style.top = ''; m.style.left = ''; m.style.right = ''; m.style.bottom = '';
      }
      m.classList.add('open');
      fetchNotifications();
    } else {
      m.classList.remove('open');
    }
  }
}

async function fetchNotifications() {
  const list = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge');
  if (!list) return;
  list.innerHTML = 'Loading...';
  try {
    const res = await fetch('api/notifications.php');
    const data = await res.json();
    if (data.success) {
      if (data.unread_count > 0) {
        if (badge) { badge.style.display = 'flex'; badge.textContent = data.unread_count; }
        const bMob = document.getElementById('notifBadgeMob');
        const bDesk = document.getElementById('notifBadgeDesk');
        if (bMob) bMob.style.display = 'block';
        if (bDesk) { bDesk.style.display = 'flex'; bDesk.textContent = data.unread_count; }
      } else {
        if (badge) badge.style.display = 'none';
        const bMob = document.getElementById('notifBadgeMob');
        const bDesk = document.getElementById('notifBadgeDesk');
        if (bMob) bMob.style.display = 'none';
        if (bDesk) bDesk.style.display = 'none';
      }
      
      list.innerHTML = data.notifications.length === 0 ? '<div style="padding:10px 0;text-align:center">No notifications</div>' : data.notifications.map(n => 
        `<div style="padding:10px;border-bottom:1px solid #eee;cursor:pointer;background:${n.is_read ? 'transparent' : '#f0f9ff'}" onclick="markNotifRead(${n.notif_id}, '${n.link || 'dashboard.html'}', event)">
           <div style="font-weight:${n.is_read ? 'normal' : 'bold'};color:var(--navy);display:flex;justify-content:space-between;">
             <span>${n.message}</span>
             ${!n.is_read ? `<button class="btn btn-outline btn-sm" style="padding:2px 6px;font-size:10px;border:none;background:transparent" onclick="markNotifRead(${n.notif_id}, '', event)" title="Mark as read">✓</button>` : ''}
           </div>
           <div style="font-size:11px;color:#888;margin-top:4px">${new Date(n.created_at).toLocaleString()}</div>
         </div>`
      ).join('');
    }
  } catch(e) {
    list.innerHTML = 'Error loading notifications.';
  }
}

async function updateNotificationBadges() {
  if (!isLoggedIn()) return;
  try {
    const res = await fetch('api/notifications.php');
    const data = await res.json();
    if (data.success) {
      const bMob = document.getElementById('notifBadgeMob');
      const bDesk = document.getElementById('notifBadgeDesk');
      const bSheet = document.getElementById('notifBadge');
      
      if (data.unread_count > 0) {
        if (bMob) bMob.style.display = 'block';
        if (bDesk) { bDesk.style.display = 'flex'; bDesk.textContent = data.unread_count; }
        if (bSheet) { bSheet.style.display = 'flex'; bSheet.textContent = data.unread_count; }
      } else {
        if (bMob) bMob.style.display = 'none';
        if (bDesk) bDesk.style.display = 'none';
        if (bSheet) bSheet.style.display = 'none';
      }
    }
  } catch(e) {}
}

async function markNotifRead(id, link, event) {
  if (event) {
    event.stopPropagation();
    event.preventDefault();
  }
  
  console.log("markNotifRead called with id:", id, "link:", link);

  try {
    await fetch('api/notifications.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({id})
    });
  } catch(e) {
    console.error('Failed to mark notification as read:', e);
  }
  
  if (link === '') {
    fetchNotifications();
    return;
  }

  let target = 'dashboard.html';
  if (link && link !== 'null' && link !== 'undefined') {
    target = link;
  }
  
  console.log("Navigating to target:", target);
  window.location.href = target;
}

function renderFooter() {
  return `
    <footer>
      <div>
        <a href="index.html">Home</a> ·
        <a href="listings.html">Listings</a> ·
        <a href="exchange.html">Exchange</a> ·
        <a href="seeking.html">Looking For</a> ·
        <a href="admin.html">Admin</a>
      </div>
      <div style="margin-top:10px">© 2025 UIUNest - United International University</div>
    </footer>`;
}

function mountChrome(active) {
  const nav = document.getElementById('nav-mount');
  const ft  = document.getElementById('footer-mount');
  
  if (nav) {
    nav.innerHTML = renderNav(active);
    
    let lastScrollY = window.scrollY;
    
    // Helper: check if page is scrollable and update nav state
    function updateNavScrollState() {
      const currentScrollY = window.scrollY;
      const navLinks = nav.querySelector('.nav-links');
      const pageIsScrollable = document.documentElement.scrollHeight > window.innerHeight + 2;
      const isHomepage = window.location.pathname.endsWith('index.html') || window.location.pathname === '/' || window.location.pathname.endsWith('/');
      
      // Bottom pill styling for mobile (full width when scrolled or unscrollable)
      if (navLinks) {
        if (!pageIsScrollable || currentScrollY > 60) {
          navLinks.classList.add('nav-scrolled');
        } else {
          navLinks.classList.remove('nav-scrolled');
        }
      }
      // Hide top navbar when scrolling down, show when scrolling up (skip on homepage and mobile)
      if (!isHomepage && window.innerWidth > 560) {
        if (pageIsScrollable) {
          if (currentScrollY > lastScrollY && currentScrollY > 60) {
            if (nav) nav.classList.add('nav-hidden');
          } else {
            if (nav) nav.classList.remove('nav-hidden');
          }
        }
      }
      
      lastScrollY = currentScrollY;
    }

    // Run immediately on mount (catches unscrollable pages)
    updateNavScrollState();
    if (window.innerWidth <= 900) {
      setTimeout(() => initMobileNavSlide(), 50);
    }
    // Also run after images/content may have loaded
    window.addEventListener('load', updateNavScrollState);
    window.addEventListener('resize', updateNavScrollState, { passive: true });
    window.addEventListener('scroll', updateNavScrollState, { passive: true });
  }
  
  if (ft)  ft.innerHTML  = renderFooter();
  if (window.lucide) lucide.createIcons();
  
  if (isLoggedIn()) {
    setTimeout(fetchNotifications, 500); // give DOM a moment to mount
  }
}

// ============ Listing Card ============
function renderListingCard(l) {
  const wl = getWatchlist().includes(l.id);
  const verifBadge = l.isVerified ? `<span class="badge badge-gold"><i data-lucide="check-circle" class="lucide-sm"></i> Verified</span>` : '';
  
  // Calculate dynamic compatibility
  const compat = calculateCompatibility(l);
  const compatColor = compat.score !== null ? getCompatibilityColor(compat.score) : {dot:'transparent', label:''};
  
  const currentUser = window.user || window.mockData.currentUser;
  const isStudent = currentUser && currentUser.role === 'student';
  const compatClass = (isStudent && compat.score !== null && compat.score < 40) ? 'low-match' : '';
  
  const typeBadge = l.listingType === 'Student Listed'
    ? `<span class="badge badge-blue">Student Listed</span>`
    : `<span class="badge badge-navy">Landlord Listed</span>`;
  const gender = l.genderPref === 'female' ? '<i data-lucide="user" class="lucide-sm"></i> Female' : l.genderPref === 'male' ? '<i data-lucide="user" class="lucide-sm"></i> Male' : 'Any';
  
  // Render match badge if user is logged in
  const compatBadge = isStudent && compat.score !== null
    ? `<span class="badge" style="background:${compatColor.bg};color:${compatColor.color};border:none;box-shadow:0 2px 4px rgba(0,0,0,0.1);font-weight:600;">${compatColor.label} ${compat.score}%</span>`
    : '';

  const photoSrc = l.photos && l.photos[0] ? l.photos[0] : 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'600\' height=\'400\'><rect width=\'600\' height=\'400\' fill=\'%23EEF7F2\'/><text x=\'50%\' y=\'50%\' font-family=\'sans-serif\' font-size=\'18\' fill=\'%231A5C45\' text-anchor=\'middle\' dominant-baseline=\'middle\'>No Photo</text></svg>';
  return `
    <div class="listing-card ${compatClass}">
      <div class="listing-photo-wrap">
        <img src="${photoSrc}" alt="${l.title}" class="listing-photo" loading="lazy">
        <div class="listing-photo-badges">
          ${verifBadge}
          ${compatBadge}
        </div>
      </div>
      <div class="listing-body">
        <div class="badges">
          <span class="badge badge-navy">${l.zone}</span>
          ${typeBadge}
        </div>
        <div class="listing-title">${l.title}</div>
        <div class="price">${fmt(l.costs.totalMonthly)}<span> /month</span></div>
        <div class="listing-meta">${gender} &nbsp;·&nbsp; ${l.currentOccupancy}/${l.totalRooms} occupied</div>
        <div class="listing-footer">
          <button class="heart ${wl ? 'active' : ''}" data-wl="${l.id}" title="Save"><i data-lucide="heart" style="${wl ? 'fill: currentColor' : ''}"></i></button>
          <a class="btn btn-primary btn-sm" href="listing-detail.html?id=${l.id}">View Details</a>
        </div>
      </div>
    </div>`;
}

function attachWatchlistHandlers(root) {
  // Deprecated: Clicks are now handled by global event delegation on document.body
}

// ============ Map helper ============
function createNavyIcon() {
  return L.divIcon({
    className: '',
    html: `<div style="width:32px;height:32px;background:#1A5C45;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2.5px solid white;box-shadow:0 3px 10px rgba(26,92,69,0.4)"></div>`,
    iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -34]
  });
}

function renderListingsOnMap(map, listings) {
  if (window._listingMarkers) window._listingMarkers.forEach(m => map.removeLayer(m));
  window._listingMarkers = [];
  const icon = createNavyIcon();
  listings.forEach(l => {
    const m = L.marker([l.lat || 23.7805, l.lng || 90.4200], { icon }).addTo(map).bindPopup(`
      <div style="min-width:180px;font-family:Inter,sans-serif">
        <strong style="font-size:13px">${l.title}</strong><br>
        <span style="color:#003366;font-size:15px;font-weight:600">${fmt(l.costs.totalMonthly)}/mo</span><br>
        <a href="listing-detail.html?id=${l.id}" style="color:#0055AA;font-size:12px;margin-top:4px;display:inline-block">View details →</a>
      </div>`);
    window._listingMarkers.push(m);
  });
}

// ============ Offer state machine ============
const offerStates = {
  pending:   { next: ['accepted','countered','rejected'] },
  countered: { next: ['accepted','rejected','withdrawn'] },
  accepted:  { next: [] },
  rejected:  { next: [] },
  withdrawn: { next: [] }
};

function formatPropertyType(type) {
  const map = {
    'single_room': 'Single Room',
    'shared_room': 'Shared Room',
    'full_mess': 'Full Mess',
    'sublet': 'Sub-let',
    'any': 'Any'
  };
  return map[type] || type;
}

function statusBadge(status) {
  const map = {
    pending: 'badge-amber', countered: 'badge-light-blue',
    accepted: 'badge-green', rejected: 'badge-red', withdrawn: 'badge-gray',
    active: 'badge-green', fulfilled: 'badge-gray',
    available: 'badge-green', soon_vacant: 'badge-amber', occupied: 'badge-gray', sold: 'badge-gray',
    paid: 'badge-green', unpaid: 'badge-amber',
    Submitted: 'badge-red', 'Under Review': 'badge-amber', Resolved: 'badge-green'
  };
  return `<span class="badge ${map[status] || 'badge-gray'}">${status}</span>`;
}

function conditionBadge(c) {
  const m = { new: 'badge-green', like_new: 'badge-light-blue', good: 'badge-amber', fair: 'badge-gray' };
  const label = { new: 'New', like_new: 'Like New', good: 'Good', fair: 'Fair' }[c] || c;
  return `<span class="badge ${m[c] || 'badge-gray'}">${label}</span>`;
}

async function loadListingsFromDB() {
  try {
    const res = await fetch('api/listings.php');
    const data = await res.json();
    if (data.success) {
      window.mockData.listings = data.listings;
      recalculateAdminStats();
      
      if (typeof applyFilters === 'function') {
        applyFilters();
      }
      
      const ls = document.getElementById('latestListings');
      if (ls && typeof renderListingCard === 'function') {
        ls.innerHTML = window.mockData.listings.slice(0, 4).map(renderListingCard).join('');
      }
    }
  } catch(e) {
    console.error('Failed to load listings from DB', e);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => fetchNotifications(), 500);
});

window.addEventListener('click', e => {
  // Close modals when clicking the modal-bg backdrop
  if (e.target.classList && e.target.classList.contains('modal-bg')) {
    e.target.classList.remove('open');
    if (e.target.style.display === 'flex' || e.target.style.display === 'block') {
      e.target.style.display = 'none';
    }
  }

  // Close notification dropdown when clicking outside
  const notifMenu = document.getElementById('notifMenu');
  const notifBtn = document.getElementById('notifBtn');
  if (notifMenu && notifMenu.classList.contains('open')) {
    if (!notifMenu.contains(e.target) && (!notifBtn || !notifBtn.contains(e.target))) {
      notifMenu.classList.remove('open');
    }
  }

  // Close avatar dropdown when clicking outside
  const avatarMenu = document.getElementById('avatarMenu');
  const avatarBtn = document.getElementById('avatarBtn');
  if (avatarMenu && avatarMenu.classList.contains('open')) {
    if (!avatarMenu.contains(e.target) && (!avatarBtn || !avatarBtn.contains(e.target))) {
      avatarMenu.classList.remove('open');
    }
  }
});

// Call on load
loadListingsFromDB();

// ============ Custom Select Engine ============
function initCustomSelects() {
  document.querySelectorAll('select:not(.custom-select-hidden)').forEach(select => {
    select.classList.add('custom-select-hidden');

    const wrapper = document.createElement('div');
    wrapper.className = 'custom-select-wrapper';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const trigger = document.createElement('div');
    trigger.className = 'custom-select-trigger';
    wrapper.appendChild(trigger);

    const optionsContainer = document.createElement('div');
    optionsContainer.className = 'custom-select-options';
    wrapper.appendChild(optionsContainer);

    function renderOptions() {
      optionsContainer.innerHTML = '';
      let selectedText = '';
      let longestText = '';
      Array.from(select.options).forEach((option, index) => {
        if (option.selected) selectedText = option.text;
        if (option.text.length > longestText.length) longestText = option.text;
        const optEl = document.createElement('div');
        optEl.className = 'custom-option';
        if (option.selected) optEl.classList.add('selected');
        optEl.textContent = option.text;
        optEl.addEventListener('click', (e) => {
          e.stopPropagation();
          if (select.selectedIndex !== index) {
            Array.from(optionsContainer.children).forEach(c => c.classList.remove('selected'));
            optEl.classList.add('selected');
            trigger.querySelector('span').textContent = option.text;
            select.selectedIndex = index;
            select.dispatchEvent(new Event('change', { bubbles: true }));
          }
          optionsContainer.classList.remove('open');
          trigger.classList.remove('open');
        });
        optionsContainer.appendChild(optEl);
      });
      wrapper.setAttribute('data-longest', longestText);
      trigger.innerHTML = `<span>${selectedText}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>`;
    }
    
    renderOptions();

    const observer = new MutationObserver(renderOptions);
    observer.observe(select, { childList: true });

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      document.querySelectorAll('.custom-select-options.open').forEach(el => {
        if (el !== optionsContainer) {
          el.classList.remove('open');
          el.previousElementSibling.classList.remove('open');
        }
      });
      optionsContainer.classList.toggle('open');
      trigger.classList.toggle('open');
    });

    document.addEventListener('click', () => {
      optionsContainer.classList.remove('open');
      trigger.classList.remove('open');
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initCustomSelects();
  
  // Bulletproof fallback: watch the entire DOM for new selects being injected dynamically
  // so they automatically get the beautiful custom styling
  const domObserver = new MutationObserver((mutations) => {
    let shouldInit = false;
    mutations.forEach(m => {
      if (m.addedNodes.length > 0) {
        m.addedNodes.forEach(n => {
          if (n.nodeType === 1) { // Element node
            if (n.tagName === 'SELECT' && !n.classList.contains('custom-select-hidden')) shouldInit = true;
            else if (n.querySelector && n.querySelector('select:not(.custom-select-hidden)')) shouldInit = true;
          }
        });
      }
    });
    if (shouldInit) initCustomSelects();
  });
  domObserver.observe(document.body, { childList: true, subtree: true });
});

// ============ Mobile Nav Sliding Bubble Animation ============
function initMobileNavSlide() {
  const navLinks = document.querySelector('.nav-links');
  if (!navLinks) return;
  
  // Create indicator bubble if it doesn't exist
  let indicator = navLinks.querySelector('.nav-indicator');
  if (!indicator) {
    indicator = document.createElement('div');
    indicator.className = 'nav-indicator';
    navLinks.appendChild(indicator);
  }

  const items = navLinks.querySelectorAll('a, .mobile-nav-extra');
  let animFrame;
  function updateIndicator(el) {
    if (!el) return;
    cancelAnimationFrame(animFrame);
    const start = performance.now();
    function step(timestamp) {
      indicator.style.width = el.offsetWidth + 'px';
      indicator.style.left = el.offsetLeft + 'px';
      if (timestamp - start < 350) {
        animFrame = requestAnimationFrame(step);
      }
    }
    animFrame = requestAnimationFrame(step);
  }

  // Initial position
  let activeEl = navLinks.querySelector('.active');
  const initialActiveEl = activeEl; // Store the real active page
  
  if (activeEl) {
    // Small delay to ensure flex layout has settled
    setTimeout(() => updateIndicator(activeEl), 100);
  }

  // Globally accessible reset for when the account sheet closes
  window._resetMobileNav = () => {
    if (window._isNavigating) return; // Prevent bounce if we are already sliding to a new page
    items.forEach(i => i.classList.remove('active'));
    if (initialActiveEl) {
      initialActiveEl.classList.add('active');
      updateIndicator(initialActiveEl);
    } else {
      indicator.style.width = '0px';
    }
  };

  window.addEventListener('resize', () => {
    if (window.innerWidth <= 900) {
      let act = navLinks.querySelector('.active');
      if (act) updateIndicator(act);
    }
  });

  items.forEach(item => {
    item.addEventListener('click', function(e) {
      // If it's the account sheet toggle (button), don't navigate, just animate
      if (this.tagName === 'BUTTON') {
        items.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        updateIndicator(this);
        return;
      }
      
      const href = this.getAttribute('href');
      if (href && !href.startsWith('#')) {
        window._isNavigating = true; // Flag to stop reset bounce
        e.preventDefault();
        items.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        updateIndicator(this);
        // Wait for bubble to slide before navigating (adjusted delay for smoothness)
        setTimeout(() => { window.location.href = href; }, 280);
      }
    });
  });
}