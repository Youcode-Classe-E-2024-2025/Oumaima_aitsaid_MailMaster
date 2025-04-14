import React, { useState, useEffect } from 'react';
import { Container, Card, Form, Button, Spinner } from 'react-bootstrap';
import { useNavigate, useParams } from 'react-router-dom';
import { Formik } from 'formik';
import * as Yup from 'yup';
import Header from '../../components/Header';
import { subscriberService, newsletterService } from '../../services/api';
import { toast } from 'react-toastify';

// Schéma de validation
const subscriberSchema = Yup.object().shape({
  email: Yup.string()
    .email('Email invalide')
    .required('Email requis'),
  first_name: Yup.string()
    .max(255, 'Le prénom ne peut pas dépasser 255 caractères'),
  last_name: Yup.string()
    .max(255, 'Le nom ne peut pas dépasser 255 caractères'),
  status: Yup.string()
    .oneOf(['active', 'unsubscribed'], 'Statut invalide')
    .required('Statut requis'),
  newsletter_ids: Yup.array()
    .of(Yup.number())
});

const SubscriberEdit = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [subscriber, setSubscriber] = useState(null);
  const [newsletters, setNewsletters] = useState([]);
  const [subscriberNewsletters, setSubscriberNewsletters] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [subscriberResponse, newslettersResponse] = await Promise.all([
          subscriberService.get(id),
          newsletterService.getAll()
        ]);
        
        setSubscriber(subscriberResponse.data);
        setNewsletters(newslettersResponse.data.data || []);
        
        // Extract the newsletter IDs the subscriber is subscribed to
        const subscriberNewsletterIds = subscriberResponse.data.newsletters?.map(n => n.id.toString()) || [];
        setSubscriberNewsletters(subscriberNewsletterIds);
        
        setError('');
      } catch (err) {
        console.error('Erreur lors du chargement des données', err);
        setError('Impossible de charger les données. Veuillez réessayer.');
        toast.error('Erreur lors du chargement des données');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [id]);

  const handleSubmit = async (values, { setSubmitting }) => {
    try {
      await subscriberService.update(id, {
        ...values,
        newsletter_ids: values.newsletter_ids.map(id => parseInt(id)),
      });
      toast.success('Abonné mis à jour avec succès');
      navigate('/subscribers');
    } catch (err) {
      console.error('Erreur lors de la mise à jour de l\'abonné', err);
      toast.error('Erreur lors de la mise à jour de l\'abonné');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <>
        <Header />
        <Container className="text-center my-5">
          <Spinner animation="border" role="status">
            <span className="visually-hidden">Chargement...</span>
          </Spinner>
        </Container>
      </>
    );
  }

  if (error) {
    return (
      <>
        <Header />
        <Container className="text-center my-5">
          <div className="alert alert-danger" role="alert">
            {error}
          </div>
          <Button variant="primary" onClick={() => navigate('/subscribers')}>
            Retour à la liste
          </Button>
        </Container>
      </>
    );
  }

  return (
    <>
      <Header />
      <Container>
        <h1 className="mb-4">Modifier l'abonné</h1>
        
        <Card>
          <Card.Body>
            <Formik
              initialValues={{
                email: subscriber?.email || '',
                first_name: subscriber?.first_name || '',
                last_name: subscriber?.last_name || '',
                status: subscriber?.status || 'active',
                newsletter_ids: subscriberNewsletters || []
              }}
              validationSchema={subscriberSchema}
              onSubmit={handleSubmit}
              enableReinitialize
            >
              {({
                values,
                errors,
                touched,
                handleChange,
                handleBlur,
                handleSubmit,
                isSubmitting,
                setFieldValue
              }) => (
                <Form onSubmit={handleSubmit}>
                  <Form.Group className="mb-3">
                    <Form.Label>Email *</Form.Label>
                    <Form.Control
                      type="email"
                      name="email"
                      value={values.email}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.email && errors.email}
                    />
                    <Form.Control.Feedback type="invalid">
                      {errors.email}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Prénom</Form.Label>
                    <Form.Control
                      type="text"
                      name="first_name"
                      value={values.first_name}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.first_name && errors.first_name}
                    />
                    <Form.Control.Feedback type="invalid">
                      {errors.first_name}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Nom</Form.Label>
                    <Form.Control
                      type="text"
                      name="last_name"
                      value={values.last_name}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.last_name && errors.last_name}
                    />
                    <Form.Control.Feedback type="invalid">
                      {errors.last_name}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Statut *</Form.Label>
                    <Form.Select
                      name="status"
                      value={values.status}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.status && errors.status}
                    >
                      <option value="active">Actif</option>
                      <option value="unsubscribed">Désabonné</option>
                    </Form.Select>
                    <Form.Control.Feedback type="invalid">
                      {errors.status}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Newsletters</Form.Label>
                    {newsletters.length > 0 ? (
                      newsletters.map(newsletter => (
                        <Form.Check
                          key={newsletter.id}
                          type="checkbox"
                          id={`newsletter-${newsletter.id}`}
                          label={newsletter.name}
                          name="newsletter_ids"
                          value={newsletter.id.toString()}
                          checked={values.newsletter_ids.includes(newsletter.id.toString())}
                          onChange={(e) => {
                            if (e.target.checked) {
                              setFieldValue('newsletter_ids', [...values.newsletter_ids, e.target.value]);
                            } else {
                              setFieldValue(
                                'newsletter_ids',
                                values.newsletter_ids.filter(id => id !== e.target.value)
                              );
                            }
                          }}
                        />
                      ))
                    ) : (
                      <p className="text-muted">Aucune newsletter disponible</p>
                    )}
                  </Form.Group>

                  <div className="d-flex justify-content-between">
                    <Button 
                      variant="secondary" 
                      onClick={() => navigate('/subscribers')}
                    >
                      Annuler
                    </Button>
                    <Button 
                      variant="primary" 
                      type="submit" 
                      disabled={isSubmitting}
                    >
                      {isSubmitting ? 'Enregistrement...' : 'Enregistrer les modifications'}
                    </Button>
                  </div>
                </Form>
              )}
            </Formik>
          </Card.Body>
        </Card>
      </Container>
    </>
  );
};

export default SubscriberEdit;