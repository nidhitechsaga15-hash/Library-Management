import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Update this with your Laravel backend URL
const API_BASE_URL = 'http://192.168.1.12:8000'; // Change to your server IP/domain

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000, // 20 seconds timeout (reduced from 30)
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
    config.headers['X-Requested-With'] = 'XMLHttpRequest';
  }
  console.log('API Request:', config.method?.toUpperCase(), config.url);
  return config;
}, (error) => {
  console.error('Request error:', error);
  return Promise.reject(error);
});

// Response interceptor for better error handling
api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error('API Error:', error.message, error.response?.status, error.config?.url);
    return Promise.reject(error);
  }
);

// Test API connection
export const testConnection = async () => {
  try {
    // Test with a simple POST request
    const response = await api.post('/api/mobile/login', {
      email: 'test@test.com',
      password: 'test',
      role: 'admin'
    }, { timeout: 10000 });
    // If we get any response (even error), connection works
    return true;
  } catch (error) {
    // If it's a connection error, throw
    if (error.code === 'ECONNREFUSED' || 
        error.code === 'ECONNABORTED' ||
        error.message.includes('Network Error') ||
        error.message.includes('Failed to fetch') ||
        error.message.includes('timeout')) {
      throw new Error(`Cannot connect to server at ${API_BASE_URL}.\n\nPlease check:\n1. Server is running: php artisan serve --host=0.0.0.0\n2. IP address is correct (192.168.0.152)\n3. Both devices are on same network\n4. Firewall allows port 8000`);
    }
    // If it's a validation/authentication error, connection is working
    return true;
  }
};

export const apiService = {
  // Login
  login: async (email, password, role) => {
    try {
      console.log('Logging in...', email);
      const response = await api.post('/api/mobile/login', {
        email,
        password,
        role,
      }, {
        timeout: 20000,
      });
      console.log('Login response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Login error:', error);
      
      if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
        throw new Error('Request timeout. Please check your internet connection and try again.');
      }
      
      if (error.code === 'ECONNREFUSED' || error.message.includes('Network Error')) {
        throw new Error('Cannot connect to server. Please check if server is running and IP address is correct.');
      }
      
      const errorMessage = error.response?.data?.message || 
                          error.response?.data?.error || 
                          error.message || 
                          'Login failed. Please check your connection.';
      throw new Error(errorMessage);
    }
  },

  // Register
  register: async (userData) => {
    try {
      console.log('Registering user...', userData.email);
      const response = await api.post('/api/mobile/register', userData, {
        timeout: 20000,
      });
      console.log('Registration response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Registration error:', error);
      
      if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
        throw new Error('Request timeout. Please check your internet connection and try again.');
      }
      
      if (error.code === 'ECONNREFUSED' || error.message.includes('Network Error')) {
        throw new Error('Cannot connect to server. Please check if server is running and IP address is correct.');
      }
      
      const errorMessage = error.response?.data?.message || 
                          error.response?.data?.error || 
                          (error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : null) ||
                          error.message || 
                          'Registration failed. Please check your connection.';
      throw new Error(errorMessage);
    }
  },

  // Get user profile
  getProfile: async () => {
    try {
      const response = await api.get('/api/mobile/profile');
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch profile');
    }
  },

  // Update Profile
  updateProfile: async (data) => {
    const response = await api.put('/api/mobile/profile', data);
    return response.data;
  },

  // Change Password
  changePassword: async (currentPassword, password, passwordConfirmation) => {
    const response = await api.post('/api/mobile/change-password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    });
    return response.data;
  },

  // Admin APIs
  admin: {
    getDashboard: async () => {
      try {
        const response = await api.get('/api/mobile/admin/dashboard');
        return {
          stats: response.data.stats,
          recent_borrows: response.data.recent_borrows || [],
        };
      } catch (error) {
        console.error('Dashboard error:', error);
        
        if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
          throw new Error('Request timeout. Please check your internet connection and try again.');
        }
        
        if (error.code === 'ECONNREFUSED' || error.message.includes('Network Error')) {
          throw new Error('Cannot connect to server. Please check if server is running and IP address is correct.');
        }
        
        const errorMessage = error.response?.data?.message || 
                            error.response?.data?.error || 
                            error.message || 
                            'Failed to load dashboard. Please check your connection.';
        throw new Error(errorMessage);
      }
    },
    // Books
    getBooks: async (page = 1, perPage = 100, search = '') => {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: perPage.toString(),
      });
      if (search) {
        params.append('search', search);
      }
      const response = await api.get(`/api/mobile/admin/books?${params.toString()}`);
      return {
        books: response.data.books,
        pagination: response.data.pagination,
      };
    },
    getBook: async (id) => {
      const response = await api.get(`/api/mobile/admin/books/${id}`);
      return response.data.book;
    },
    createBook: async (data) => {
      const response = await api.post('/api/mobile/admin/books', data);
      if (response.data.success && response.data.book) {
        return response.data.book;
      }
      throw new Error(response.data.message || 'Failed to create book');
    },
    updateBook: async (id, data) => {
      const response = await api.put(`/api/mobile/admin/books/${id}`, data);
      return response.data.book;
    },
    deleteBook: async (id) => {
      const response = await api.delete(`/api/mobile/admin/books/${id}`);
      return response.data;
    },
    // Authors
    getAuthors: async () => {
      const response = await api.get('/api/mobile/admin/authors');
      return response.data.authors;
    },
    getAuthor: async (id) => {
      const response = await api.get(`/api/mobile/admin/authors/${id}`);
      return response.data.author;
    },
    createAuthor: async (data) => {
      const response = await api.post('/api/mobile/admin/authors', data);
      return response.data.author;
    },
    updateAuthor: async (id, data) => {
      const response = await api.put(`/api/mobile/admin/authors/${id}`, data);
      return response.data.author;
    },
    deleteAuthor: async (id) => {
      const response = await api.delete(`/api/mobile/admin/authors/${id}`);
      return response.data;
    },
    // Categories
    getCategories: async () => {
      const response = await api.get('/api/mobile/admin/categories');
      return response.data.categories;
    },
    getCategory: async (id) => {
      const response = await api.get(`/api/mobile/admin/categories/${id}`);
      return response.data.category;
    },
    createCategory: async (data) => {
      const response = await api.post('/api/mobile/admin/categories', data);
      return response.data.category;
    },
    updateCategory: async (id, data) => {
      const response = await api.put(`/api/mobile/admin/categories/${id}`, data);
      return response.data.category;
    },
    deleteCategory: async (id) => {
      const response = await api.delete(`/api/mobile/admin/categories/${id}`);
      return response.data;
    },
    // Users
    getUsers: async () => {
      const response = await api.get('/api/mobile/admin/users');
      return response.data.users;
    },
    getUser: async (id) => {
      const response = await api.get(`/api/mobile/admin/users/${id}`);
      return response.data.user;
    },
    createUser: async (data) => {
      const response = await api.post('/api/mobile/admin/users', data);
      return response.data.user;
    },
    updateUser: async (id, data) => {
      const response = await api.put(`/api/mobile/admin/users/${id}`, data);
      return response.data.user;
    },
    deleteUser: async (id) => {
      const response = await api.delete(`/api/mobile/admin/users/${id}`);
      return response.data;
    },
    // Borrows
    getBorrows: async () => {
      const response = await api.get('/api/mobile/admin/borrows');
      return response.data.borrows;
    },
    issueBook: async (data) => {
      const response = await api.post('/api/mobile/admin/borrows/issue', data);
      return response.data.borrow;
    },
    returnBook: async (id) => {
      const response = await api.post(`/api/mobile/admin/borrows/${id}/return`);
      return response.data.borrow;
    },
    extendBorrow: async (id, additionalDays) => {
      const response = await api.post(`/api/mobile/admin/borrows/${id}/extend`, {
        additional_days: additionalDays,
      });
      return response.data.borrow;
    },
    // Fines
    getFines: async () => {
      const response = await api.get('/api/mobile/admin/fines');
      return response.data.fines;
    },
    updateFineStatus: async (id, status) => {
      const response = await api.put(`/api/mobile/admin/fines/${id}/payment-status`, { status });
      return response.data.fine;
    },
    // Book Requests
    getBookRequests: async () => {
      const response = await api.get('/api/mobile/admin/book-requests');
      return {
        requests: response.data.requests,
        stats: response.data.stats || {
          pending_count: 0,
          approved_count: 0,
          total_requests: 0,
          issued_count: 0,
        },
      };
    },
    approveRequest: async (id) => {
      const response = await api.post(`/api/mobile/admin/book-requests/${id}/approve`);
      return response.data.request;
    },
    rejectRequest: async (id) => {
      const response = await api.post(`/api/mobile/admin/book-requests/${id}/reject`);
      return response.data.request;
    },
    issueRequest: async (id, dueDate) => {
      const response = await api.post(`/api/mobile/admin/book-requests/${id}/issue`, { due_date: dueDate });
      return response.data.borrow;
    },
    // Reports
    getReports: async () => {
      const response = await api.get('/api/mobile/admin/reports');
      return response.data.reports;
    },
    getTotalBooksReport: async () => {
      const response = await api.get('/api/mobile/admin/reports/total-books');
      return response.data;
    },
    getBookIssueReport: async () => {
      const response = await api.get('/api/mobile/admin/reports/book-issue');
      return response.data;
    },
    getOverdueReport: async () => {
      const response = await api.get('/api/mobile/admin/reports/overdue');
      return response.data;
    },
    getFinesReport: async () => {
      const response = await api.get('/api/mobile/admin/reports/fines');
      return response.data;
    },
    getStudentWiseReport: async () => {
      const response = await api.get('/api/mobile/admin/reports/student-wise');
      return response.data;
    },
    getStudentDetailReport: async (id) => {
      const response = await api.get(`/api/mobile/admin/reports/student/${id}`);
      return response.data;
    },
    // Library Cards
    getLibraryCards: async () => {
      const response = await api.get('/api/mobile/admin/library-cards');
      return response.data.cards;
    },
    createLibraryCard: async (data) => {
      const response = await api.post('/api/mobile/admin/library-cards', data);
      return response.data.card;
    },
    blockLibraryCard: async (id) => {
      const response = await api.post(`/api/mobile/admin/library-cards/${id}/block`);
      return response.data.card;
    },
    unblockLibraryCard: async (id) => {
      const response = await api.post(`/api/mobile/admin/library-cards/${id}/unblock`);
      return response.data.card;
    },
  },

  // Staff APIs
  staff: {
    getDashboard: async () => {
      const response = await api.get('/api/mobile/staff/dashboard');
      return response.data.stats;
    },
    getStudents: async () => {
      const response = await api.get('/api/mobile/staff/students');
      return response.data.students;
    },
    getBooks: async () => {
      const response = await api.get('/api/mobile/staff/books');
      return response.data.books;
    },
    getBorrows: async () => {
      const response = await api.get('/api/mobile/staff/borrows');
      return response.data.borrows;
    },
    issueBook: async (data) => {
      const response = await api.post('/api/mobile/staff/borrows/issue', data);
      return response.data.borrow;
    },
    returnBook: async (id) => {
      const response = await api.post(`/api/mobile/staff/borrows/${id}/return`);
      return response.data.borrow;
    },
    extendBorrow: async (id, additionalDays) => {
      const response = await api.post(`/api/mobile/staff/borrows/${id}/extend`, {
        additional_days: additionalDays,
      });
      return response.data.borrow;
    },
    getFines: async () => {
      const response = await api.get('/api/mobile/staff/fines');
      return response.data.fines;
    },
    updateFineStatus: async (id, status) => {
      const response = await api.put(`/api/mobile/staff/fines/${id}/status`, { status });
      return response.data.fine;
    },
    getBookRequests: async () => {
      const response = await api.get('/api/mobile/staff/book-requests');
      return response.data.requests;
    },
    getLibraryCards: async () => {
      const response = await api.get('/api/mobile/staff/library-cards');
      return response.data.cards;
    },
    getBooks: async () => {
      const response = await api.get('/api/mobile/staff/books');
      return response.data.books;
    },
    getBorrows: async () => {
      const response = await api.get('/api/mobile/staff/borrows');
      return response.data.borrows;
    },
    issueBook: async (data) => {
      const response = await api.post('/api/mobile/staff/borrows/issue', data);
      return response.data.borrow;
    },
    returnBook: async (id) => {
      const response = await api.post(`/api/mobile/staff/borrows/${id}/return`);
      return response.data.borrow;
    },
    getFines: async () => {
      const response = await api.get('/api/mobile/staff/fines');
      return response.data.fines;
    },
    updateFineStatus: async (id, status) => {
      const response = await api.put(`/api/mobile/staff/fines/${id}/status`, { status });
      return response.data.fine;
    },
    getBookRequests: async () => {
      const response = await api.get('/api/mobile/staff/book-requests');
      return response.data.requests;
    },
    getLibraryCards: async () => {
      const response = await api.get('/api/mobile/staff/library-cards');
      return response.data.cards;
    },
  },

  // Student APIs
  student: {
    getDashboard: async () => {
      const response = await api.get('/api/mobile/student/dashboard');
      return response.data.stats;
    },
    getBooks: async () => {
      const response = await api.get('/api/mobile/student/books');
      return response.data.books;
    },
    getMyBooks: async () => {
      const response = await api.get('/api/mobile/student/my-books');
      return response.data.borrows;
    },
    requestBook: async (bookId) => {
      const response = await api.post(`/api/mobile/student/books/${bookId}/request`);
      return response.data.request;
    },
    getFines: async () => {
      const response = await api.get('/api/mobile/student/fines');
      return response.data.fines;
    },
    getLibraryCard: async () => {
      const response = await api.get('/api/mobile/student/library-card');
      return response.data.card;
    },
    getReservations: async () => {
      const response = await api.get('/api/mobile/student/reservations');
      return response.data.reservations;
    },
    getMyBooks: async () => {
      const response = await api.get('/api/mobile/student/my-books');
      return response.data.borrows;
    },
    requestBook: async (bookId) => {
      const response = await api.post(`/api/mobile/student/books/${bookId}/request`);
      return response.data.request;
    },
    getFines: async () => {
      const response = await api.get('/api/mobile/student/fines');
      return response.data.fines;
    },
    getLibraryCard: async () => {
      const response = await api.get('/api/mobile/student/library-card');
      return response.data.card;
    },
    getReservations: async () => {
      const response = await api.get('/api/mobile/student/reservations');
      return response.data.reservations;
    },
  },

  // Shared APIs (Chat, Notifications)
  chat: {
    getUsers: async () => {
      const response = await api.get('/api/mobile/chat/users');
      return response.data.users;
    },
    getConversations: async () => {
      const response = await api.get('/api/mobile/chat/conversations');
      return response.data.conversations;
    },
    getMessages: async (conversationId) => {
      const response = await api.get(`/api/mobile/chat/conversations/${conversationId}/messages`);
      return response.data.messages;
    },
    sendMessage: async (conversationId, message) => {
      const response = await api.post(`/api/mobile/chat/conversations/${conversationId}/messages`, { message });
      return response.data.message;
    },
  },

  notifications: {
    getAll: async () => {
      const response = await api.get('/api/mobile/notifications');
      return response.data.notifications;
    },
    markAsRead: async (id) => {
      const response = await api.post(`/api/mobile/notifications/${id}/read`);
      return response.data;
    },
  },
};

