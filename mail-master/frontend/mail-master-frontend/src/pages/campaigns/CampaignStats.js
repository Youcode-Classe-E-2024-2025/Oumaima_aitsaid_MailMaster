// src/pages/campaigns/CampaignStats.js
import React from 'react';
import { Container } from 'react-bootstrap';
import { useParams } from 'react-router-dom';
import Header from '../../components/Header';

const CampaignStats = () => {
  const { id } = useParams();
  
  return (
    <>
      <Header />
      <Container>
        <h1>Statistiques de la campagne</h1>
        <p>Cette page affichera les statistiques de la campagne avec l'ID: {id}</p>
      </Container>
    </>
  );
};

export default CampaignStats;