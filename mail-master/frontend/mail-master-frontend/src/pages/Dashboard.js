import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import Header from '../components/Header';
import { newsletterService, subscriberService, campaignService } from '../services/api';

const Dashboard = () => {
  const [stats, setStats] = useState({
    newsletters: 0,
    subscribers: 0,
    campaigns: {
      total: 0,
      draft: 0,
      scheduled: 0,
      sent: 0
    }
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        // Charger les statistiques de base
        const [newsletters, subscribers, campaigns] = await Promise.all([
          newsletterService.getAll(),
          subscriberService.getAll(),
          campaignService.getAll()
        ]);
        
        // Calculer les statistiques
        const campaignStats = {
          total: campaigns.data.data.length,
          draft: campaigns.data.data.filter(c => c.status === 'draft').length,
          scheduled: campaigns.data.data.filter(c => c.status === 'scheduled').length,
          sent: campaigns.data.data.filter(c => c.status === 'sent').length
        };
        
        setStats({
          newsletters: newsletters.data.data.length,
          subscribers: subscribers.data.data.length,
          campaigns: campaignStats
        });
      } catch (error) {
        console.error('Erreur lors du chargement des statistiques', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  return (
    <>
      <Header />
      <Container>
        <h1 className="mb-4">Tableau de bord</h1>
        
        {loading ? (
          <p>Chargement des statistiques...</p>
        ) : (
          <Row>
            <Col md={4}>
              <Card className="mb-4">
                <Card.Body>
                  <Card.Title>Newsletters</Card.Title>
                  <Card.Text className="display-4">{stats.newsletters}</Card.Text>
                  <Link to="/newsletters" className="btn btn-primary">Gérer les newsletters</Link>
                </Card.Body>
              </Card>
            </Col>
            
            <Col md={4}>
              <Card className="mb-4">
                <Card.Body>
                  <Card.Title>Abonnés</Card.Title>
                  <Card.Text className="display-4">{stats.subscribers}</Card.Text>
                  <Link to="/subscribers" className="btn btn-primary">Gérer les abonnés</Link>
                </Card.Body>
              </Card>
            </Col>
            
            <Col md={4}>
              <Card className="mb-4">
                <Card.Body>
                  <Card.Title>Campagnes</Card.Title>
                  <Card.Text className="display-4">{stats.campaigns.total}</Card.Text>
                  <Link to="/campaigns" className="btn btn-primary">Gérer les campagnes</Link>
                </Card.Body>
              </Card>
            </Col>
          </Row>
        )}
        
        <Row>
          <Col>
            <Card>
              <Card.Header>Statut des campagnes</Card.Header>
              <Card.Body>
                <Row>
                  <Col md={4}>
                    <div className="text-center">
                      <h5>Brouillons</h5>
                      <p className="display-6">{stats.campaigns.draft}</p>
                    </div>
                  </Col>
                  <Col md={4}>
                    <div className="text-center">
                      <h5>Programmées</h5>
                      <p className="display-6">{stats.campaigns.scheduled}</p>
                    </div>
                  </Col>
                  <Col md={4}>
                    <div className="text-center">
                      <h5>Envoyées</h5>
                      <p className="display-6">{stats.campaigns.sent}</p>
                    </div>
                  </Col>
                </Row>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default Dashboard;