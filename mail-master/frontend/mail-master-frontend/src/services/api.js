import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

// Créer une instance axios avec la configuration de base
const axiosInstance = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Ajouter un intercepteur pour les requêtes
axiosInstance.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Ajouter un intercepteur pour les réponses
axiosInstance.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    if (error.response && error.response.status === 401) {
      // Si non autorisé, déconnecter l'utilisateur
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Services d'authentification
export const authService = {
  register: (userData) => axiosInstance.post('/register', userData),
  login: (credentials) => axiosInstance.post('/login', credentials),
  logout: () => axiosInstance.post('/logout'),
  getUser: () => axiosInstance.get('/user')
};

// Services des newsletters
export const newsletterService = {
  getAll: (params) => axiosInstance.get('/newsletters', { params }),
  get: (id) => axiosInstance.get(`/newsletters/${id}`),
  create: (data) => axiosInstance.post('/newsletters', data),
  update: (id, data) => axiosInstance.put(`/newsletters/${id}`, data),
  delete: (id) => axiosInstance.delete(`/newsletters/${id}`)
};

// Services des abonnés
export const subscriberService = {
  getAll: (params) => axiosInstance.get('/subscribers', { params }),
  get: (id) => axiosInstance.get(`/subscribers/${id}`),
  create: (data) => axiosInstance.post('/subscribers', data),
  update: (id, data) => axiosInstance.put(`/subscribers/${id}`, data),
  delete: (id) => axiosInstance.delete(`/subscribers/${id}`),
  addToNewsletter: (data) => axiosInstance.post('/subscribers/add-to-newsletter', data),
  removeFromNewsletter: (data) => axiosInstance.post('/subscribers/remove-from-newsletter', data)
};

// Services des campagnes
export const campaignService = {
  getAll: (params) => axiosInstance.get('/campaigns', { params }),
  get: (id) => axiosInstance.get(`/campaigns/${id}`),
  create: (data) => axiosInstance.post('/campaigns', data),
  update: (id, data) => axiosInstance.put(`/campaigns/${id}`, data),
  delete: (id) => axiosInstance.delete(`/campaigns/${id}`),
  preview: (id) => axiosInstance.get(`/campaigns/${id}/preview`),
  schedule: (id, data) => axiosInstance.post(`/campaigns/${id}/schedule`, data),
  send: (id) => axiosInstance.post(`/campaigns/${id}/send`),
  getStats: (id) => axiosInstance.get(`/campaigns/${id}/stats`)
};

export default {
  auth: authService,
  newsletters: newsletterService,
  subscribers: subscriberService,
  campaigns: campaignService
};