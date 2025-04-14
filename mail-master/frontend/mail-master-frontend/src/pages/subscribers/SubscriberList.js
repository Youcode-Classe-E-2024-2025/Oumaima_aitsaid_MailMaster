import React, { useState, useEffect } from 'react';
import { Container, Table, Button, Card, Row, Col, Form, Spinner, Badge } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import Header from '../../components/Header';
import { subscriberService } from '../../services/api';
import { toast } from 'react-toastify';

const SubscriberList = () => {
  const [subscribers, setSubscribers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    loadSubscribers();
  }, []);

  const loadSubscribers = async () => {
    try {
      setLoading(true);
      const response = await subscriberService.getAll({ search, status });
      setSubscribers(response.data.data || []);
    } catch (error) {
      console.error('Erreur lors du chargement des abonnés', error);
      toast.error('Erreur lors du chargement des abonnés');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    setSearch(e.target.value);
  };

  const handleStatusChange = (e) => {
    setStatus(e.target.value);
  };

  const handleSubmitSearch = (e) => {
    e.preventDefault();
    loadSubscribers();
  };

  const handleDelete = async (id) => {
    if (window.confirm('Êtes-vous sûr de vouloir supprimer cet abonné ?')) {
      try {
        await subscriberService.delete(id);
        toast.success('Abonné supprimé avec succès');
        loadSubscribers();
      } catch (error) {
        console.error('Erreur lors de la suppression', error);
        toast.error('Erreur lors de la suppression de l\'abonné');
      }
    }
  };

  return (
    <>
      <Header />
      <Container>
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h1>Liste des abonnés</h1>
          <Link to="/subscribers/create" className="btn btn-primary">
            Ajouter un abonné
          </Link>
        </div>

        <Card className="mb-4">
          <Card.Body>
            <Form onSubmit={handleSubmitSearch}>
              <Row className="align-items-end">
                <Col md={5}>
                  <Form.Group>
                    <Form.Label>Rechercher</Form.Label>
                    <Form.Control
                      type="text"
                      placeholder="Rechercher par email, nom..."
                      value={search}
                      onChange={handleSearch}
                    />
                  </Form.Group>
                </Col>
                <Col md={4}>
                  <Form.Group>
                    <Form.Label>Statut</Form.Label>
                    <Form.Select value={status} onChange={handleStatusChange}>
                      <option value="">Tous les statuts</option>
                      <option value="active">Actif</option>
                      <option value="unsubscribed">Désabonné</option>
                    </Form.Select>
                  </Form.Group>
                </Col>
                <Col md={3}>
                  <Button type="submit" variant="outline-primary" className="w-100">
                    Rechercher
                  </Button>
                </Col>
              </Row>
            </Form>
          </Card.Body>
        </Card>

        {loading ? (
          <div className="text-center my-5">
            <Spinner animation="border" role="status">
              <span className="visually-hidden">Chargement...</span>
            </Spinner>
          </div>
        ) : subscribers.length > 0 ? (
          <Table striped bordered hover responsive>
            <thead>
              <tr>
                <th>#</th>
                <th>Email</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Statut</th>
                <th>Date d'inscription</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {subscribers.map((subscriber) => (
                <tr key={subscriber.id}>
                  <td>{subscriber.id}</td>
                  <td>{subscriber.email}</td>
                  <td>{subscriber.last_name || '-'}</td>
                  <td>{subscriber.first_name || '-'}</td>
                  <td>
                    <Badge bg={subscriber.status === 'active' ? 'success' : 'danger'}>
                      {subscriber.status === 'active' ? 'Actif' : 'Désabonné'}
                    </Badge>
                  </td>
                  <td>{new Date(subscriber.created_at).toLocaleDateString()}</td>
                  <td>
                    <Link
                      to={`/subscribers/edit/${subscriber.id}`}
                      className="btn btn-sm btn-warning me-2"
                    >
                      Modifier
                    </Link>
                    <Button
                      variant="danger"
                      size="sm"
                      onClick={() => handleDelete(subscriber.id)}
                    >
                      Supprimer
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </Table>
        ) : (
          <div className="text-center my-5">
            <p>Aucun abonné trouvé.</p>
            <p>
              <Link to="/subscribers/create" className="btn btn-primary">
                Ajouter votre premier abonné
              </Link>
            </p>
          </div>
        )}
      </Container>
    </>
  );
};

export default SubscriberList;