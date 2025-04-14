// src/pages/campaigns/CampaignList.js
import React from 'react';
import { Container } from 'react-bootstrap';
import Header from '../../components/Header';

const CampaignList = () => {
  return (
    <>
      <Header />
      <Container>
        <h1>Liste des campagnes</h1>
        <p>Cette page affichera la liste des campagnes.</p>
      </Container>
    </>
  );
};

export default CampaignList;