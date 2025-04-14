import React from 'react';
import { Container, Card, Form, Button } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
import { Formik } from 'formik';
import * as Yup from 'yup';
import Header from '../../components/Header';
import { newsletterService } from '../../services/api';
import { toast } from 'react-toastify';

// Schéma de validation
const newsletterSchema = Yup.object().shape({
  name: Yup.string()
    .required('Le nom est requis')
    .max(255, 'Le nom ne peut pas dépasser 255 caractères'),
  description: Yup.string()
    .nullable()
});

const NewsletterCreate = () => {
  const navigate = useNavigate();

  const handleSubmit = async (values, { setSubmitting }) => {
    try {
      await newsletterService.create(values);
      toast.success('Newsletter créée avec succès');
      navigate('/newsletters');
    } catch (error) {
      console.error('Erreur lors de la création de la newsletter', error);
      toast.error('Erreur lors de la création de la newsletter');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <Header />
      <Container>
        <h1 className="mb-4">Créer une newsletter</h1>
        
        <Card>
          <Card.Body>
            <Formik
              initialValues={{ name: '', description: '' }}
              validationSchema={newsletterSchema}
              onSubmit={handleSubmit}
            >
              {({
                values,
                errors,
                touched,
                handleChange,
                handleBlur,
                handleSubmit,
                isSubmitting
              }) => (
                <Form onSubmit={handleSubmit}>
                  <Form.Group className="mb-3">
                    <Form.Label>Nom de la newsletter *</Form.Label>
                    <Form.Control
                      type="text"
                      name="name"
                      value={values.name}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.name && errors.name}
                    />
                    <Form.Control.Feedback type="invalid">
                      {errors.name}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <Form.Group className="mb-3">
                    <Form.Label>Description</Form.Label>
                    <Form.Control
                      as="textarea"
                      rows={4}
                      name="description"
                      value={values.description}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      isInvalid={touched.description && errors.description}
                    />
                    <Form.Control.Feedback type="invalid">
                      {errors.description}
                    </Form.Control.Feedback>
                  </Form.Group>

                  <div className="d-flex justify-content-between">
                    <Button 
                      variant="secondary" 
                      onClick={() => navigate('/newsletters')}
                    >
                      Annuler
                    </Button>
                    <Button 
                      variant="primary" 
                      type="submit" 
                      disabled={isSubmitting}
                    >
                      {isSubmitting ? 'Création...' : 'Créer la newsletter'}
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

export default NewsletterCreate;