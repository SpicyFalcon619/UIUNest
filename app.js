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
    ? `<div style="position:relative;display:inline-block">
         <button class="icon-btn" title="Notifications" onclick="toggleNotifs()"><i data-lucide="bell" class="lucide-sm"></i><span id="notifBadge" style="display:none;position:absolute;top:-2px;right:-2px;background:var(--danger);color:white;font-size:10px;font-weight:bold;width:16px;height:16px;border-radius:50%;align-items:center;justify-content:center"></span></button>
         <div class="avatar-menu" id="notifMenu" style="width:280px;right:-10px;padding:12px;z-index:2000">
           <div style="font-weight:600;border-bottom:1px solid var(--border);padding-bottom:8px;margin-bottom:8px">Notifications</div>
           <div id="notifList" style="max-height:300px;overflow-y:auto;font-size:13px;color:var(--gray)">Loading...</div>
         </div>
       </div>
       <a class="icon-btn" href="dashboard.html?tab=watch" title="Watchlist"><i data-lucide="heart" class="lucide-sm"></i></a>
       <div class="avatar" id="avatarBtn" onclick="document.getElementById('avatarMenu').classList.toggle('open')">${initials}
         <div class="avatar-menu" id="avatarMenu">
           <a href="dashboard.html">Dashboard</a>
           <a href="profile.html">Profile</a>
           <a href="bills.html">Bills</a>
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
        <a href="index.html" class="nav-logo"><span style="color:var(--navy)">UIU</span><span style="color:var(--gold)">Nest</span></a>
        <div class="nav-links">${linkHTML}</div>
        <div class="nav-right">${rightHTML}</div>
      </div>
    </nav>`;
}

async function doLogout() {
  try { await fetch('api/logout.php'); } catch(e) {}
  localStorage.removeItem('uiunest_logged_in_v4');
  localStorage.removeItem('uiunest_current_user_v4');
  location.href = 'index.html';
}

function toggleNotifs() {
  const m = document.getElementById('notifMenu');
  if (m) {
    m.classList.toggle('open');
    if (m.classList.contains('open')) fetchNotifications();
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
        badge.style.display = 'flex';
        badge.textContent = data.unread_count;
      } else {
        badge.style.display = 'none';
      }
      
      list.innerHTML = data.notifications.length === 0 ? '<div style="padding:10px 0;text-align:center">No notifications</div>' : data.notifications.map(n => 
        `<div style="padding:10px;border-bottom:1px solid #eee;cursor:pointer;background:${n.is_read ? 'transparent' : '#f0f9ff'}" onclick="markNotifRead(${n.notif_id}, '${n.link || 'dashboard.html'}', event)">
           <div style="font-weight:${n.is_read ? 'normal' : 'bold'};color:var(--navy)">${n.message}</div>
           <div style="font-size:11px;color:#888;margin-top:4px">${new Date(n.created_at).toLocaleString()}</div>
         </div>`
      ).join('');
    }
  } catch(e) {
    list.innerHTML = 'Error loading notifications.';
  }
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
    
    // Auto-hide navbar on scroll down (except on index page)
    const p = window.location.pathname;
    const isIndex = p.endsWith('index.html') || p.endsWith('/') || p.endsWith('/UIU-Nest');
    
    if (!isIndex) {
      let lastScrollY = window.scrollY;
      window.addEventListener('scroll', () => {
        if (window.scrollY > 80 && window.scrollY > lastScrollY) {
          nav.classList.add('nav-hidden'); // scrolling down
        } else {
          nav.classList.remove('nav-hidden'); // scrolling up or at top
        }
        lastScrollY = window.scrollY;
      }, { passive: true });
    }
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
      <img src="${l.photos && l.photos[0] ? l.photos[0] : 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'600\' height=\'400\'><rect width=\'600\' height=\'400\' fill=\'%23e2e8f0\'/><text x=\'50%\' y=\'50%\' font-family=\'sans-serif\' font-size=\'20\' fill=\'%2394a3b8\' text-anchor=\'middle\' dominant-baseline=\'middle\'>No Photo Available</text></svg>'}" alt="${l.title}" class="listing-photo" loading="lazy">
      <div class="listing-body">
        <div class="badges">
          <span class="badge badge-navy">${l.zone}</span>
          ${typeBadge}
          ${verifBadge}
          ${compatBadge}
        </div>
        <div class="listing-title">${l.title}</div>
        <div class="price">${fmt(l.costs.totalMonthly)}<span style="font-size:12px;color:var(--gray);font-weight:500"> /month</span></div>
        <div style="font-size:12px;color:var(--gray);margin-top:6px;">${gender} · ${l.currentOccupancy}/${l.totalRooms} occupied</div>
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

// Call on load
loadListingsFromDB();