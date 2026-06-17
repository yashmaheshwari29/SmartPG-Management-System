// ============================================
// Smart PG Management System
// AngularJS App - app.js
// All controllers are in this single file
// ============================================

// Define the AngularJS module
var app = angular.module('smartPGApp', []);

// ============================================
// BASE URL - Change this to your server path
// ============================================
app.constant('API_BASE', 'smartpg_api/');


// ============================================
// LOGIN CONTROLLER
// Handles login form & role-based redirect
// ============================================
app.controller('LoginController', function($scope, $window, API_BASE, $http) {

    $scope.credentials = { username: '', password: '', role: 'admin' };
    $scope.errorMsg = '';
    $scope.loading = false;

    $scope.login = function() {
        $scope.errorMsg = '';
        $scope.loading = true;

        // Send login request to PHP API
        $http.post(API_BASE + 'login.php', $scope.credentials)
            .then(function(response) {
                $scope.loading = false;
                var data = response.data;

                if (data.success) {
                    // Store user info in sessionStorage
                    sessionStorage.setItem('pg_user', JSON.stringify(data.user));

                    // Redirect based on role
                    if (data.user.role === 'admin') {
                        $window.location.href = 'admin_dashboard.html';
                    } else {
                        $window.location.href = 'user_dashboard.html';
                    }
                } else {
                    $scope.errorMsg = data.message || 'Invalid username or password.';
                }
            }, function() {
                $scope.loading = false;
                $scope.errorMsg = 'Server error. Please check your connection.';
            });
    };
});


// ============================================
// ADMIN DASHBOARD CONTROLLER
// Shows summary stats: tenants, rooms, payments
// ============================================
app.controller('AdminDashboardController', function($scope, $http, API_BASE, $window) {

    // Check if user is logged in as admin
    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user || user.role !== 'admin') {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;

    // Initialize stats object
    $scope.stats = {
        totalTenants: 0,
        totalRooms: 0,
        availableRooms: 0,
        pendingPayments: 0,
        openComplaints: 0,
        totalRevenue: 0
    };

    $scope.recentTenants = [];
    $scope.recentComplaints = [];

    // Fetch dashboard summary from API
    $http.get(API_BASE + 'getDashboard.php')
        .then(function(res) {
            if (res.data.success) {
                $scope.stats = res.data.stats;
                $scope.recentTenants = res.data.recentTenants;
                $scope.recentComplaints = res.data.recentComplaints;
            }
        });

    // Logout function
    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };
});


// ============================================
// TENANTS CONTROLLER
// Fetch, Add, Delete tenants
// ============================================
app.controller('TenantsController', function($scope, $http, API_BASE, $window) {

    // Auth check
    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user || user.role !== 'admin') {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;

    $scope.tenants = [];
    $scope.rooms = [];       // For dropdown in add form
    $scope.newTenant = {};   // Model for add tenant form
    $scope.message = '';
    $scope.messageType = '';
    $scope.showForm = false;

    // Load all tenants
    $scope.loadTenants = function() {
        $http.get(API_BASE + 'getTenants.php')
            .then(function(res) {
                if (res.data.success) {
                    $scope.tenants = res.data.tenants;
                }
            });
    };

    // Load available rooms for dropdown
    $scope.loadRooms = function() {
        $http.get(API_BASE + 'getRooms.php')
            .then(function(res) {
                if (res.data.success) {
                    $scope.rooms = res.data.rooms;
                }
            });
    };

    // Add a new tenant
    $scope.addTenant = function() {
        if (!$scope.newTenant.name || !$scope.newTenant.phone || !$scope.newTenant.room_id) {
            $scope.message = 'Please fill all required fields.';
            $scope.messageType = 'error';
            return;
        }

        $http.post(API_BASE + 'addTenant.php', $scope.newTenant)
            .then(function(res) {
                if (res.data.success) {
                    $scope.message = 'Tenant added successfully!';
                    $scope.messageType = 'success';
                    $scope.newTenant = {};
                    $scope.showForm = false;
                    $scope.loadTenants(); // Refresh list
                } else {
                    $scope.message = res.data.message || 'Failed to add tenant.';
                    $scope.messageType = 'error';
                }
            });
    };

    // Delete a tenant
    $scope.deleteTenant = function(tenantId) {
        if (!confirm('Are you sure you want to remove this tenant?')) return;

        $http.post(API_BASE + 'deleteTenant.php', { tenant_id: tenantId })
            .then(function(res) {
                if (res.data.success) {
                    $scope.message = 'Tenant removed successfully.';
                    $scope.messageType = 'success';
                    $scope.loadTenants();
                } else {
                    $scope.message = 'Failed to delete tenant.';
                    $scope.messageType = 'error';
                }
            });
    };

    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };

    // Load data on page load
    $scope.loadTenants();
    $scope.loadRooms();
});


// ============================================
// ROOMS CONTROLLER
// View rooms with status and rent info
// ============================================
app.controller('RoomsController', function($scope, $http, API_BASE, $window) {

    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user || user.role !== 'admin') {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;

    $scope.rooms = [];
    $scope.filterStatus = 'all';  // Filter: all / occupied / available

    // Load all rooms
    $scope.loadRooms = function() {
        $http.get(API_BASE + 'getRooms.php')
            .then(function(res) {
                if (res.data.success) {
                    $scope.rooms = res.data.rooms;
                }
            });
    };

    // Filter rooms by status
    $scope.filteredRooms = function() {
        if ($scope.filterStatus === 'all') return $scope.rooms;
        return $scope.rooms.filter(function(r) {
            return r.status === $scope.filterStatus;
        });
    };

    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };

    $scope.loadRooms();
});


// ============================================
// COMPLAINTS CONTROLLER
// Admin can view; Tenant can submit complaint
// ============================================
app.controller('ComplaintsController', function($scope, $http, API_BASE, $window) {

    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user) {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;
    $scope.isAdmin = (user.role === 'admin');

    $scope.complaints = [];
    $scope.newComplaint = {};
    $scope.message = '';
    $scope.messageType = '';

    // Load all complaints (admin view)
    $scope.loadComplaints = function() {
        $http.get(API_BASE + 'getComplaints.php')
            .then(function(res) {
                if (res.data.success) {
                    $scope.complaints = res.data.complaints;
                }
            });
    };

    // Submit a new complaint (tenant)
    $scope.submitComplaint = function() {
        if (!$scope.newComplaint.description) {
            $scope.message = 'Please describe your complaint.';
            $scope.messageType = 'error';
            return;
        }

        // Attach tenant_id from session
        $scope.newComplaint.tenant_id = user.user_id;

        $http.post(API_BASE + 'addComplaint.php', $scope.newComplaint)
            .then(function(res) {
                if (res.data.success) {
                    $scope.message = 'Complaint submitted successfully!';
                    $scope.messageType = 'success';
                    $scope.newComplaint = {};
                    $scope.loadComplaints();
                } else {
                    $scope.message = 'Failed to submit complaint.';
                    $scope.messageType = 'error';
                }
            });
    };

    // Mark complaint as closed (admin only)
    $scope.closeComplaint = function(complaintId) {
        $http.post(API_BASE + 'updateComplaint.php', {
            complaint_id: complaintId,
            status: 'closed'
        }).then(function(res) {
            if (res.data.success) {
                $scope.loadComplaints();
            }
        });
    };

    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };

    $scope.loadComplaints();
});


// ============================================
// PAYMENTS CONTROLLER (Admin View)
// ============================================
app.controller('PaymentsController', function($scope, $http, API_BASE, $window) {

    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user || user.role !== 'admin') {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;

    $scope.payments = [];
    $scope.filterStatus = 'all';

    $scope.loadPayments = function() {
        $http.get(API_BASE + 'getPayments.php')
            .then(function(res) {
                if (res.data.success) {
                    $scope.payments = res.data.payments;
                }
            });
    };

    $scope.filteredPayments = function() {
        if ($scope.filterStatus === 'all') return $scope.payments;
        return $scope.payments.filter(function(p) {
            return p.status === $scope.filterStatus;
        });
    };

    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };

    $scope.loadPayments();
});


// ============================================
// USER DASHBOARD CONTROLLER
// Tenant's own view: room info, complaints
// ============================================
app.controller('UserDashboardController', function($scope, $http, API_BASE, $window) {

    var user = JSON.parse(sessionStorage.getItem('pg_user') || 'null');
    if (!user) {
        $window.location.href = 'index.html';
        return;
    }
    $scope.user = user;

    $scope.myComplaints = [];
    $scope.myPayments  = [];
    $scope.newComplaint = {};
    $scope.message = '';
    $scope.messageType = '';

    // Load this user's complaints
    $http.get(API_BASE + 'getComplaints.php?user_id=' + user.user_id)
        .then(function(res) {
            if (res.data.success) $scope.myComplaints = res.data.complaints;
        });

    // Load this user's payments
    $http.get(API_BASE + 'getPayments.php?user_id=' + user.user_id)
        .then(function(res) {
            if (res.data.success) $scope.myPayments = res.data.payments;
        });

    // Submit a complaint
    $scope.submitComplaint = function() {
        if (!$scope.newComplaint.description) {
            $scope.message = 'Please enter your complaint.';
            $scope.messageType = 'error';
            return;
        }
        $scope.newComplaint.tenant_id = user.user_id;

        $http.post(API_BASE + 'addComplaint.php', $scope.newComplaint)
            .then(function(res) {
                if (res.data.success) {
                    $scope.message = 'Complaint submitted!';
                    $scope.messageType = 'success';
                    $scope.newComplaint = {};
                    // Reload complaints
                    $http.get(API_BASE + 'getComplaints.php?user_id=' + user.user_id)
                        .then(function(r) {
                            if (r.data.success) $scope.myComplaints = r.data.complaints;
                        });
                }
            });
    };

    $scope.logout = function() {
        sessionStorage.clear();
        $window.location.href = 'index.html';
    };
});
