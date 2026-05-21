window.mockData = {
  currentUser: {
    id: 1, name: "Student User", email: "student@uiu.ac.bd",
    role: "student", gender: "male", universityId: "011202001", isLoggedIn: true
  },
  users: [
    { id: 1, name: "Student User", email: "student@uiu.ac.bd", password: "student123", role: "student", gender: "male", universityId: "011202001" },
    { id: 2, name: "Landlord User", email: "landlord@uiu.ac.bd", password: "landlord123", role: "landlord", gender: "male" },
    { id: 3, name: "Master Admin", email: "admin@uiu.ac.bd", password: "admin123", role: "admin", gender: "male" }
  ],
  zones: [
    { id: 1, name: "UIU Campus Zone", lat: 23.7805, lng: 90.4200 },
    { id: 2, name: "Dhanmondi",       lat: 23.7461, lng: 90.3742 },
    { id: 3, name: "Mirpur",          lat: 23.8041, lng: 90.3674 },
    { id: 4, name: "Mohammadpur",     lat: 23.7632, lng: 90.3587 },
    { id: 5, name: "Lalmatia",        lat: 23.7558, lng: 90.3714 },
    { id: 6, name: "Shyamoli",        lat: 23.7710, lng: 90.3608 },
    { id: 7, name: "Rayer Bazar",     lat: 23.7489, lng: 90.3561 },
    { id: 8, name: "Adabor",          lat: 23.7680, lng: 90.3480 },
  ],
  listings: [],
  reviews: [],
  seekingPosts: [],
  exchangeItems: [],
  offers: [],
  monthlyBills: [],
  complaints: [],
  verifs: [],
  adminStats: {
    totalListings: 0, totalUsers: 3, activeExchangeItems: 0, openComplaints: 0, pendingVerifications: 0,
    avgRentByZone: [
      { zone: "UIU Campus Zone", avg: 0 }, { zone: "Dhanmondi", avg: 0 },
      { zone: "Mirpur", avg: 0 }, { zone: "Mohammadpur", avg: 0 },
      { zone: "Lalmatia", avg: 0 }, { zone: "Shyamoli", avg: 0 }
    ],
    listingsByZone: [0, 0, 0, 0, 0, 0],
    seekingVsListings: [
      { zone: "UIU Campus Zone", seeking: 0, listings: 0 },
      { zone: "Dhanmondi",       seeking: 0,  listings: 0  },
      { zone: "Mirpur",          seeking: 0, listings: 0 },
      { zone: "Mohammadpur",     seeking: 0,  listings: 0  },
      { zone: "Shyamoli",        seeking: 0,  listings: 0  }
    ]
  }
};