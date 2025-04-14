import React, { useState, useEffect } from 'react';
import { Container, Table, Button, Card, Row, Col, Form, Spinner } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import Header from '../../components/Header';
import { newsletterService } from '../../services/api';
import { toast } from 'react-toastify';

const NewsletterList = () => {
  const [newsletters, setNewsletters] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');

  useEffect(() => {
    loadNewsletters();
  }, []);

  const loadNewsletters = async () => {
    try {
      setLoading(true);
      const response = await newsletterService.getAll({ search });
      setNewsletters(response.data.data || []);
    } catch (error) {
      console.error('Erreur lors du chargement des newsletters', error);
      toast.error('Erreur lors du chargement des newsletters');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    setSearch(e.target.value);
  };

  const handleSubmitSearch = (e) => {
    e.preventDefault();
    loadNewsletters();
  };

  const handleDelete = async (id) => {
    if (window.confirm('Êtes-vous sûr de vouloir supprimer cette newsletter ?')) {
      try {
        await newsletterService.delete(id);
        toast.success('Newsletter supprimée avec succès');
        loadNewsletters();
      } catch (error) {
        console.error('Erreur lors de la suppression', error);
        toast.error('Erreur lors de la suppression de la newsletter');
      }
    }
  };

  return (
    <>
      <Header />
      <Container>
        <div className="d-flex justify-content-between align-items-center mb-4">
          <h1>Liste des newsletters</h1>
          <Link to="/newsletters/create" className="btn btn-primary">
            Créer une newsletter
          </Link>
        </div>

        <Card className="mb-4">
          <Card.Body>
            <Form onSubmit={handleSubmitSearch}>
              <Row className="align-items-center">
                <Col md={9}>
                  <Form.Control
                    type="text"
                    placeholder="Rechercher par nom..."
                    value={search}
                    onChange={handleSearch}
                  />
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
        ) : newsletters.length > 0 ? (
          <Table striped bordered hover responsive>
            <thead>
              <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Date de création</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {newsletters.map((newsletter) => (
                <tr key={newsletter.id}>
                  <td>{newsletter.id}</td>
                  <td>{newsletter.name}</td>
                  <td>{newsletter.description || '-'}</td>
                  <td>{new Date(newsletter.created_at).toLocaleDateString()}</td>
                  <td>
                    <Link
                      to={`/newsletters/edit/${newsletter.id}`}
                      className="btn btn-sm btn-warning me-2"
                    >
                      Modifier
                    </Link>
                    <Button
                      variant="danger"
                      size="sm"
                      onClick={() => handleDelete(newsletter.id)}
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
            <p>Aucune newsletter trouvée.</p>
            <p>
              <Link to="/newsletters/create" className="btn btn-primary">
                Créer votre première newsletter
              </Link>
            </p>
          </div>
        )}
      </Container>
    </>
  );
};

export default NewsletterList;