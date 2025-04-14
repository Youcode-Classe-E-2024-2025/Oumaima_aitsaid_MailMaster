import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import NewsletterList from './pages/newsletters/NewsletterList';
import NewsletterCreate from './pages/newsletters/NewsletterCreate';
import NewsletterEdit from './pages/newsletters/NewsletterEdit';
import SubscriberList from './pages/subscribers/SubscriberList';
import SubscriberCreate from './pages/subscribers/SubscriberCreate';
import SubscriberEdit from './pages/subscribers/SubscriberEdit';
import CampaignList from './pages/campaigns/CampaignList';
import CampaignCreate from './pages/campaigns/CampaignCreate';
import CampaignEdit from './pages/campaigns/CampaignEdit';
import CampaignStats from './pages/campaigns/CampaignStats';
import PrivateRoute from './components/PrivateRoute';
import './App.css';

function App() {
  return (
    <Router>
      <ToastContainer />
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        
        <Route path="/dashboard" element={<PrivateRoute><Dashboard /></PrivateRoute>} />
        
        <Route path="/newsletters" element={<PrivateRoute><NewsletterList /></PrivateRoute>} />
        <Route path="/newsletters/create" element={<PrivateRoute><NewsletterCreate /></PrivateRoute>} />
        <Route path="/newsletters/edit/:id" element={<PrivateRoute><NewsletterEdit /></PrivateRoute>} />
        
        <Route path="/subscribers" element={<PrivateRoute><SubscriberList /></PrivateRoute>} />
        <Route path="/subscribers/create" element={<PrivateRoute><SubscriberCreate /></PrivateRoute>} />
        <Route path="/subscribers/edit/:id" element={<PrivateRoute><SubscriberEdit /></PrivateRoute>} />
        
        <Route path="/campaigns" element={<PrivateRoute><CampaignList /></PrivateRoute>} />
        <Route path="/campaigns/create" element={<PrivateRoute><CampaignCreate /></PrivateRoute>} />
        <Route path="/campaigns/edit/:id" element={<PrivateRoute><CampaignEdit /></PrivateRoute>} />
        <Route path="/campaigns/stats/:id" element={<PrivateRoute><CampaignStats /></PrivateRoute>} />
        
        <Route path="/" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </Router>
  );
}

export default App;