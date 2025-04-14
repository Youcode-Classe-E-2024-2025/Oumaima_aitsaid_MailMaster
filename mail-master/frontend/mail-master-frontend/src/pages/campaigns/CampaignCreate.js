// src/pages/campaigns/CampaignCreate.js
import React from 'react';
import { Container } from 'react-bootstrap';
import Header from '../../components/Header';

const CampaignCreate = () => {
  return (
    <>
      <Header />
      <Container>
        <h1>Créer une campagne</h1>
        <p>Cette page permettra de créer une nouvelle campagne.</p>
      </Container>
    </>
  );
};

export default CampaignCreate;