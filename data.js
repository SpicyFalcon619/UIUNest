window.getInitialData = function(populated = true) {
  const zones = [
    { id: 1, name: "UIU Campus Area", lat: 23.7979, lng: 90.4497 },
    { id: 2, name: "Sayed Nagar",     lat: 23.7950, lng: 90.4440 },
    { id: 3, name: "Shatarkul",       lat: 23.7910, lng: 90.4350 },
    { id: 4, name: "Nurer Chala",     lat: 23.8050, lng: 90.4380 },
    { id: 5, name: "Aftabnagar",      lat: 23.7660, lng: 90.4340 },
    { id: 6, name: "Notun Bazar",     lat: 23.7970, lng: 90.4220 }
  ];

  const emptyStats = {
    totalListings: 0, totalUsers: 3, activeExchangeItems: 0, openComplaints: 0, pendingVerifications: 0,
    avgRentByZone: zones.map(z => ({ zone: z.name, avg: 0 })),
    listingsByZone: zones.map(z => 0),
    seekingVsListings: zones.map(z => ({ zone: z.name, seeking: 0, listings: 0 }))
  };

  const users = [
    { id: 1, name: "Student User", email: "student@uiu.ac.bd", password: "student123", role: "student", gender: "male", universityId: "011202001" },
    { id: 4, name: "Student Two", email: "student2@uiu.ac.bd", password: "student123", role: "student", gender: "female", universityId: "011202002" },
    { id: 2, name: "Landlord User", email: "landlord@uiu.ac.bd", password: "landlord123", role: "landlord", gender: "male" },
    { id: 3, name: "Master Admin", email: "admin@uiu.ac.bd", password: "admin123", role: "admin", gender: "male" }
  ];

  let listings = [];
  let exchangeItems = [];
  let seekingPosts = [];
  let reviews = [];
  let verifs = [];

    let applications = [];

  if (populated) {
    // Seed some mock verification
    verifs.push({ id: 1, name: "Landlord User", email: "landlord@uiu.ac.bd", nidType: "NID", desc: "My actual NID", date: new Date().toLocaleDateString(), approved: true });

    // Seed mock data using the new zones
    listings.push({
      id: 101,
      title: "Spacious Room near UIU gate",
      zoneId: 1,
      zone: "UIU Campus Area",
      propertyType: "single_room",
      totalRooms: 1,
      currentOccupancy: 0,
      genderPref: "any",
      ownerEmail: "landlord@uiu.ac.bd",
      ownerName: "Landlord User",
      isVerified: true,
      status: "available",
      lat: 23.7980,
      lng: 90.4490,
      costs: {
        baseRent: 8000,
        electricityAmount: 500,
        gasBill: 300,
        waterBill: 200,
        internetCost: 500,
        maintenanceFee: 500,
        caretakerFee: 0,
        customFees: [{ name: "Cooking Fee", amount: 500 }],
        totalMonthly: 10500
      },
      depositAmount: 8000,
      depositTerms: "Refundable",
      rules: { smoking: "No", pets: "No", guests: "Yes", curfew: "None" },
      rentHistory: [],
      amenities: { attachedBathroom: true, attachedKitchen: false, isFurnished: true, rooftopAccess: true, powerBackup: true, liftAccess: false, parking: true },
      photos: ["data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400'><rect width='600' height='400' fill='%23e2e8f0'/><text x='50%' y='50%' font-family='sans-serif' font-size='20' fill='%2394a3b8' text-anchor='middle' dominant-baseline='middle'>No Photo Available</text></svg>"]
    });

    listings.push({
      id: 102,
      title: "Shared mess for female students",
      zoneId: 6,
      zone: "Notun Bazar",
      propertyType: "shared_room",
      totalRooms: 3,
      currentOccupancy: 2,
      genderPref: "female",
      ownerEmail: "landlord@uiu.ac.bd",
      ownerName: "Landlord User",
      isVerified: true,
      status: "available",
      lat: 23.7972,
      lng: 90.4225,
      costs: {
        baseRent: 4000,
        electricityAmount: 400,
        gasBill: 300,
        waterBill: 200,
        internetCost: 200,
        maintenanceFee: 200,
        caretakerFee: 100,
        customFees: [],
        totalMonthly: 5400
      },
      depositAmount: 4000,
      depositTerms: "Refundable",
      rules: { smoking: "No", pets: "No", guests: "No", curfew: "10 PM" },
      rentHistory: [],
      amenities: { attachedBathroom: false, attachedKitchen: true, isFurnished: false, rooftopAccess: true, powerBackup: false, liftAccess: true, parking: false },
      photos: ["data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400'><rect width='600' height='400' fill='%23e2e8f0'/><text x='50%' y='50%' font-family='sans-serif' font-size='20' fill='%2394a3b8' text-anchor='middle' dominant-baseline='middle'>No Photo Available</text></svg>"]
    });

    exchangeItems.push({
      id: 201, title: "Study Table", category: "furniture", condition: "good", price: 1500, zone: "UIU Campus Area", seller: "Student User", sellerEmail: "student@uiu.ac.bd", status: "available",
      photo: "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='400'><rect width='600' height='400' fill='%23e2e8f0'/><text x='50%' y='50%' font-family='sans-serif' font-size='20' fill='%2394a3b8' text-anchor='middle' dominant-baseline='middle'>No Photo Available</text></svg>", description: "Used for 1 year, good condition."
    });

    seekingPosts.push({
      id: 301,
      poster: "Student User",
      avatar: "SU",
      zone: "Sayed Nagar",
      budgetMin: 4000,
      budgetMax: 6000,
      roomType: "single",
      preferredGender: "male",
      moveIn: "Next Month",
      requirements: "I am a CSE student looking for a quiet place with a fast internet connection.",
      date: new Date().toLocaleDateString('en-US'),
      status: "active"
    });
    
    reviews.push({ id: 1, listingId: 101, reviewer: "Student User", rating: 5, comment: "Great place, very close to campus." });

    applications.push({
      id: 1, listingId: 101, listingTitle: "Spacious Room near UIU gate", applicantName: "Student User", applicantEmail: "student@uiu.ac.bd",
      ownerEmail: "landlord@uiu.ac.bd", message: "Hi, I am interested in renting this room.", status: "pending", date: new Date().toLocaleDateString()
    });
  }

  return {
    currentUser: null,
    users,
    zones,
    listings,
    reviews,
    seekingPosts,
    exchangeItems,
    offers: [],
    monthlyBills: [],
    complaints: [],
    verifs,
    applications,
    adminStats: emptyStats
  };
};

window.mockData = window.getInitialData(true);