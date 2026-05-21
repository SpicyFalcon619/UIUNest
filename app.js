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
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

function calculateCompatibility(listing) {
  const user = window.mockData.currentUser;
  if (!user || !isLoggedIn() || user.role !== 'student') {
    return { score: 100, label: 'N/A', matched: [], unmatched: [] };
  }

  // If gender preference is violated (unless genderPref is 'any' or listing has no pref)
  if (listing.genderPref && listing.genderPref !== 'any' && listing.genderPref !== user.gender) {
    return { score: 0, label: 'Low Match', matched: [], unmatched: ['Gender Preference'] };
  }

  // Resident preferences vs current user preferences
  const up = user.preferences || {
    sleep: "Flexible",
    study: 4,
    diet: "Non-Vegetarian",
    guest: "Restricted (weekends only)",
    smoking: true,
    fg: "Male",
    cleanliness: 4,
    noise: "Quiet"
  };

  const lp = listing.residentPreferences || {
    sleep: "Flexible",
    diet: "Non-Vegetarian",
    guest: "Restricted (weekends only)",
    smoking: true,
    noise: "Quiet",
    cleanliness: 4
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
  if (score >= 80) return { color: '#1A7A4A', label: 'High Match',    bg: '#D1FAE5' };
  if (score >= 60) return { color: '#0055AA', label: 'Good Match',    bg: '#DBEAFE' };
  if (score >= 40) return { color: '#B45309', label: 'Partial Match', bg: '#FEF3C7' };
  return { color: '#6B7280', label: 'Low Match', bg: '#F3F4F6' };
}
function getQueryId() {
  const p = new URLSearchParams(window.location.search);
  return parseInt(p.get('id'));
}

// Watchlist (localStorage)
function getWatchlist() {
  try { return JSON.parse(localStorage.getItem('uiunest_watchlist_v4') || '[]'); }
  catch { return []; }
}
function toggleWatchlist(id) {
  const w = getWatchlist();
  const i = w.indexOf(id);
  if (i >= 0) w.splice(i, 1); else w.push(id);
  localStorage.setItem('uiunest_watchlist_v4', JSON.stringify(w));
  return w.includes(id);
}

// ============ Nav ============
function renderNav(active) {
  const logged = isLoggedIn();
  const user = window.mockData.currentUser;
  
  const links = [
    { href: 'listings.html', label: 'Listings', key: 'listings' },
    { href: 'exchange.html', label: 'Exchange', key: 'exchange' },
    { href: 'seeking.html',  label: 'Looking For', key: 'seeking' }
  ];
  const linkHTML = links.map(l =>
    `<a href="${l.href}" class="${active === l.key ? 'active' : ''}">${l.label}</a>`).join('');

  const initials = user && user.name
    ? user.name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2)
    : 'US';

  const rightHTML = logged
    ? `<button class="icon-btn" title="Notifications"><i data-lucide="bell" class="lucide-sm"></i></button>
       <a class="icon-btn" href="dashboard.html" title="Watchlist"><i data-lucide="heart" class="lucide-sm"></i></a>
       <div class="avatar" id="avatarBtn" onclick="document.getElementById('avatarMenu').classList.toggle('open')">${initials}
         <div class="avatar-menu" id="avatarMenu">
           <a href="dashboard.html">Dashboard</a>
           <a href="profile.html">Profile</a>
           <a href="bills.html">Bills</a>
           ${user.role === 'admin' ? '<a href="admin.html">Admin Panel</a>' : ''}
           <a href="#" onclick="event.preventDefault();localStorage.removeItem('uiunest_logged_in_v4');localStorage.removeItem('uiunest_current_user_v4');location.href='index.html';">Logout</a>
         </div>
       </div>`
    : `<a class="btn btn-outline" href="login.html">Login</a>
       <a class="btn btn-primary" href="register.html">Register</a>`;

  return `
    <nav class="navbar">
      <div class="nav-inner">
        <a href="index.html" class="nav-logo"><span style="color:var(--navy)">UIU</span><span style="color:var(--gold)">Nest</span></a>
        <div class="nav-links">${linkHTML}</div>
        <div class="nav-right">${rightHTML}</div>
      </div>
    </nav>`;
}

function renderFooter() {
  return `
    <footer>
      <div>
        <a href="index.html">Home</a> ·
        <a href="listings.html">Listings</a> ·
        <a href="exchange.html">Exchange</a> ·
        <a href="seeking.html">Looking For</a> ·
        ${window.mockData.currentUser && window.mockData.currentUser.role === 'admin' ? '<a href="admin.html">Admin</a>' : ''}
      </div>
      <div style="margin-top:10px">© 2025 UIUNest — United International University</div>
      <div style="margin-top:12px;display:flex;justify-content:center;flex-wrap:wrap;gap:8px">
        <button class="demo-toggle" onclick="toggleDemoLogin()">Toggle Login (${isLoggedIn() ? 'logged in' : 'logged out'})</button>
        <button class="demo-toggle" style="background:#800;border-color:#800" onclick="toggleDataMode(false)">Reset to Clean Data</button>
        <button class="demo-toggle" style="background:#0055AA;border-color:#0055AA" onclick="toggleDataMode(true)">Reset to Mock Data</button>
      </div>
    </footer>`;
}

function mountChrome(active) {
  const nav = document.getElementById('nav-mount');
  const ft  = document.getElementById('footer-mount');
  if (nav) nav.innerHTML = renderNav(active);
  if (ft)  ft.innerHTML  = renderFooter();
  if (window.lucide) lucide.createIcons();
}

// ============ Listing Card ============
function renderListingCard(l) {
  const wl = getWatchlist().includes(l.id);
  const verifBadge = l.isVerified ? `<span class="badge badge-gold"><i data-lucide="check" class="lucide-sm"></i> Verified</span>` : '';
  
  // Calculate dynamic compatibility
  const compat = calculateCompatibility(l);
  const compatColor = getCompatibilityColor(compat.score);
  const compatClass = (isLoggedIn() && window.mockData.currentUser.role === 'student' && compat.score < 40) ? 'low-match' : '';
  
  const typeBadge = l.listingType === 'Student Listed'
    ? `<span class="badge badge-blue">Student Listed</span>`
    : `<span class="badge badge-navy">Landlord Listed</span>`;
  const gender = l.genderPref === 'female' ? '<i data-lucide="user" class="lucide-sm"></i> Female' : l.genderPref === 'male' ? '<i data-lucide="user" class="lucide-sm"></i> Male' : 'Any';
  
  // Render match badge if user is logged in
  const compatBadge = isLoggedIn() && window.mockData.currentUser.role === 'student'
    ? `<span class="badge" style="background:${compatColor.bg};color:${compatColor.color}">${compatColor.label} (${compat.score}%)</span>`
    : '';

  return `
    <div class="listing-card ${compatClass}">
      <img src="${l.photos && l.photos[0] ? l.photos[0] : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600'}" alt="${l.title}" class="listing-photo" loading="lazy">
      <div class="listing-body">
        <div class="badges">
          <span class="badge badge-navy">${l.zone}</span>
          ${typeBadge}
          ${verifBadge}
          ${compatBadge}
        </div>
        <div class="listing-title">${l.title}</div>
        <div class="price">${fmt(l.costs.totalMonthly)}<span style="font-size:12px;color:var(--gray);font-weight:500"> /month</span></div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--gray)">
          <span class="stars">${stars(l.compositeScore)}</span> ${l.compositeScore || 0} (${l.reviewCount || 0})
        </div>
        <div style="font-size:12px;color:var(--gray)">${gender} · ${l.currentOccupancy}/${l.totalRooms} occupied</div>
        <div class="listing-footer">
          <button class="heart ${wl ? 'active' : ''}" data-wl="${l.id}" title="Save"><i data-lucide="heart" style="${wl ? 'fill: currentColor' : ''}"></i></button>
          <a class="btn btn-primary btn-sm" href="listing-detail.html?id=${l.id}">View Details</a>
        </div>
      </div>
    </div>`;
}

function attachWatchlistHandlers(root) {
  (root || document).querySelectorAll('[data-wl]').forEach(btn => {
    btn.addEventListener('click', e => {
      const id = parseInt(btn.getAttribute('data-wl'));
      const active = toggleWatchlist(id);
      btn.classList.toggle('active', active);
      const icon = btn.querySelector('i');
      if (icon) icon.style.fill = active ? 'currentColor' : 'none';
      showToast(active ? 'Saved to watchlist' : 'Removed from watchlist');
    });
  });
}

// ============ Map helper ============
function createNavyIcon() {
  return L.divIcon({
    className: '',
    html: `<div style="width:32px;height:32px;background:#003366;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3)"></div>`,
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
        <span style="color:#888;font-size:12px">${stars(l.compositeScore)} · ${l.reviewCount || 0} reviews</span><br>
        <a href="listing-detail.html?id=${l.id}" style="color:#0055AA;font-size:12px">View details →</a>
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