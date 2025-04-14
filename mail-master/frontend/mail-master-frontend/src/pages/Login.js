import React, { useState } from 'react';
import { Container, Row, Col, Card, Form, Button, Alert } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { Formik } from 'formik';
import * as Yup from 'yup';
import { authService } from '../services/api';
import { toast } from 'react-toastify';

// Schéma de validation
const loginSchema = Yup.object().shape({
  email: Yup.string()
    .email('Email invalide')
    .required('Email requis'),
  password: Yup.string()
    .required('Mot de passe requis')
});

const Login = () => {
  const navigate = useNavigate();
  const [error, setError] = useState('');

  const handleSubmit = async (values, { setSubmitting }) => {
    try {
      const response = await authService.login(values);
      // Sauvegarder le token et les infos utilisateur
      localStorage.setItem('token', response.data.access_token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      toast.success('Connexion réussie');
      navigate('/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Erreur lors de la connexion');
      console.error('Erreur de connexion:', err);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Container className="mt-5">
      <Row className="justify-content-center">
        <Col md={6}>
          <Card>
            <Card.Header as="h4" className="text-center bg-primary text-white">Connexion</Card.Header>
            <Card.Body>
              {error && <Alert variant="danger">{error}</Alert>}
              
              <Formik
                initialValues={{ email: '', password: '' }}
                validationSchema={loginSchema}
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

                    <div className="d-grid gap-2">
                      <Button 
                        variant="primary" 
                        type="submit" 
                        disabled={isSubmitting}
                      >
                        {isSubmitting ? 'Connexion...' : 'Se connecter'}
                      </Button>
                    </div>
                  </Form>
                )}
              </Formik>
              
              <div className="text-center mt-3">
                <p>Pas encore de compte? <Link to="/register">S'inscrire</Link></p>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
};

export default Login;