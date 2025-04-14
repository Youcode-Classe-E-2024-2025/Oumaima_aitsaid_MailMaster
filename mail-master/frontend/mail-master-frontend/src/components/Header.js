import React from 'react';
import { Navbar, Nav, Container, Button } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { authService } from '../services/api';
import { toast } from 'react-toastify';

const Header = () => {
  const navigate = useNavigate();
  const user = JSON.parse(localStorage.getItem('user') || '{}');

  const handleLogout = async () => {
    try {
      await authService.logout();
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      toast.success('Déconnexion réussie');
      navigate('/login');
    } catch (error) {
      console.error('Erreur lors de la déconnexion', error);
      toast.error('Erreur lors de la déconnexion');
    }
  };

  return (
    <Navbar bg="dark" variant="dark" expand="lg" className="mb-4">
      <Container>
        <Navbar.Brand as={Link} to="/dashboard">MailMaster</Navbar.Brand>
        <Navbar.Toggle aria-controls="basic-navbar-nav" />
        <Navbar.Collapse id="basic-navbar-nav">
          <Nav className="me-auto">
            <Nav.Link as={Link} to="/newsletters">Newsletters</Nav.Link>
            <Nav.Link as={Link} to="/subscribers">Abonnés</Nav.Link>
            <Nav.Link as={Link} to="/campaigns">Campagnes</Nav.Link>
          </Nav>
          <Nav>
            <Navbar.Text className="me-3">
              Connecté en tant que: {user.name}
            </Navbar.Text>
            <Button variant="outline-light" onClick={handleLogout}>Déconnexion</Button>
          </Nav>
        </Navbar.Collapse>
      </Container>
    </Navbar>
  );
};

export default Header;