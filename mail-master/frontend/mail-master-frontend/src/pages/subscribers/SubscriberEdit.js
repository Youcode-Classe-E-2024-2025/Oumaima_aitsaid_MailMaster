// src/pages/subscribers/SubscriberEdit.js
import React from 'react';
import { Container } from 'react-bootstrap';
import { useParams } from 'react-router-dom';
import Header from '../../components/Header';

const SubscriberEdit = () => {
  const { id } = useParams();
  
  return (
    <>
      <Header />
      <Container>
        <h1>Modifier l'abonné</h1>
        <p>Cette page permettra de modifier l'abonné avec l'ID: {id}</p>
      </Container>
    </>
  );
};

export default SubscriberEdit;