import React, { useState } from 'react';
import { Container, Row, Col, Card, Form, Button, Alert } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { Formik } from 'formik';
import * as Yup from 'yup';
import { authService } from '../services/api';
import { toast } from 'react-toastify';

// Schéma de validation
const registerSchema = Yup.object().shape({
  name: Yup.string()
    .required('Nom requis'),
  email: Yup.string()
    .email('Email invalide')
    .required('Email requis'),
  password: Yup.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .required('Mot de passe requis'),
  password_confirmation: Yup.string()
    .oneOf([Yup.ref('password'), null], 'Les mots de passe doivent correspondre')
    .required('Confirmation du mot de passe requise')
});

const Register = () => {
  const navigate = useNavigate();
  const [error, setError] = useState('');

  const handleSubmit = async (values, { setSubmitting }) => {
    try {
      const response = await authService.register(values);
      // Sauvegarder le token et les infos utilisateur
      localStorage.setItem('token', response.data.access_token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      toast.success('Inscription réussie');
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Erreur lors de l\'inscription');
      console.error('Erreur d\'inscription:', err);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Container className="mt-5">
      <Row className="justify-content-center">
        <Col md={6}>
          <Card>
            <Card.Header as="h4" className="text-center bg-primary text-white">Inscription</Card.Header>
            <Card.Body>
              {error && <Alert variant="danger">{error}</Alert>}
              
              <Formik
                initialValues={{ name: '', email: '', password: '', password_confirmation: '' }}
                validationSchema={registerSchema}
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
                      <Form.Label>Nom</Form.Label>
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
                      <Form.Label>Email</Form.Label>
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
                      <Form.Label>Mot de passe</Form.Label>
                      <Form.Control
                        type="password"
                        name="password"
                        value={values.password}
                        onChange={handleChange}
                        onBlur={handleBlur}
                        isInvalid={touched.password && errors.password}
                      />
                      <Form.Control.Feedback type="invalid">
                        {errors.password}
                      </Form.Control.Feedback>
                    </Form.Group>

                    <Form.Group className="mb-3">
                      <Form.Label>Confirmer le mot de passe</Form.Label>
                      <Form.Control
                        type="password"
                        name="password_confirmation"
                        value={values.password_confirmation}
                        onChange={handleChange}
                        onBlur={handleBlur}
                        isInvalid={touched.password_confirmation && errors.password_confirmation}
                      />
                      <Form.Control.Feedback type="invalid">
                        {errors.password_confirmation}
                      </Form.Control.Feedback>
                    </Form.Group>

                    <div className="d-grid gap-2">
                      <Button 
                        variant="primary" 
                        type="submit" 
                        disabled={isSubmitting}
                      >
                        {isSubmitting ? 'Inscription...' : 'S\'inscrire'}
                      </Button>
                    </div>
                  </Form>
                )}
              </Formik>
              
              <div className="text-center mt-3">
                <p>Déjà un compte? <Link to="/login">Se connecter</Link></p>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default Register;