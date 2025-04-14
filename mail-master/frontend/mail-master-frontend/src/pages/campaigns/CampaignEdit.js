// src/pages/campaigns/CampaignEdit.js
import React from 'react';
import { Container } from 'react-bootstrap';
import { useParams } from 'react-router-dom';
import Header from '../../components/Header';

const CampaignEdit = () => {
  const { id } = useParams();
  
  return (
    <>
      <Header />
      <Container>
        <h1>Modifier la campagne</h1>
        <p>Cette page permettra de modifier la campagne avec l'ID: {id}</p>
      </Container>
    </>
  );
};

export default CampaignEdit;