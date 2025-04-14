import React, { useState, useEffect } from 'react';
import { Container, Card, Form, Button, Spinner } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
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
  newsletter_ids: Yup.array()
    .of(Yup.number())
});

const SubscriberCreate = () => {
  const navigate = useNavigate();
  const [newsletters, setNewsletters] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchNewsletters = async () => {
      try {
        setLoading(true);
        const response = await newsletterService.getAll();
        setNewsletters(response.data.data || []);
      } catch (error) {
        console.error('Erreur lors du chargement des newsletters', error);
        toast.error('Erreur lors du chargement des newsletters');
      } finally {
        setLoading(false);
      }
    };

    fetchNewsletters();
  }, []);

  const handleSubmit = async (values, { setSubmitting }) => {
    try {
      await subscriberService.create({
        ...values,
        newsletter_ids: values.newsletter_ids.map(id => parseInt(id)),
      });
      toast.success('Abonné créé avec succès');
      navigate('/subscribers');
    } catch (error) {
      console.error('Erreur lors de la création de l\'abonné', error);
      toast.error('Erreur lors de la création de l\'abonné');
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

  return (
    <>
      <Header />
      <Container>
        <h1 className="mb-4">Ajouter un abonné</h1>
        
        <Card>
          <Card.Body>
            <Formik
              initialValues={{
                email: '',
                first_name: '',
                last_name: '',
                newsletter_ids: []
              }}
              validationSchema={subscriberSchema}
              onSubmit={handleSubmit}
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
                    <Form.Label>Newsletters</Form.Label>
                    {newsletters.length > 0 ? (
                      newsletters.map(newsletter => (
                        <Form.Check
                          key={newsletter.id}
                          type="checkbox"
                          id={`newsletter-${newsletter.id}`}
                          label={newsletter.name}
                          name="newsletter_ids"
                          value={newsletter.id}
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
                      {isSubmitting ? 'Création...' : 'Créer l\'abonné'}
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

export default SubscriberCreate;