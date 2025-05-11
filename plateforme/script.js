// Données simulées (à remplacer par des appels API)
const utilisateurs = [
    { unite: 'academie', role: 'encadrant', password: 'encadrant123' },
    { unite: 'academie', role: 'eleve', password: 'eleve123' },
    { unite: 'academie', role: 'admin', password: 'admin123' },
    { unite: 'autre', password: 'unite123' },
  ];
  
 // Gestion de la sélection de l'unité
document.getElementById('unite').addEventListener('change', function () {
    const roleField = document.getElementById('roleField');
    if (this.value === 'academie') {
      roleField.style.display = 'block';
    } else {
      roleField.style.display = 'none';
    }
  });
  
  // Gestion de la connexion
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
  
    const unite = document.getElementById('unite').value;
    const role = document.getElementById('role').value;
    const password = document.getElementById('password').value;
  
    // Vérifier les informations de connexion (simulé)
    if (unite === 'academie' && role && password === 'password123') {
      switch (role) {
        case 'encadrant':
          window.location.href = 'encadrant.html';
          break;
        case 'eleve':
          window.location.href = 'eleve.html';
          break;
        case 'admin':
          window.location.href = 'admin.html';
          break;
      }
    } else if (unite === 'autre' && password === 'password123') {
      window.location.href = 'encadrant.html'; // Redirection pour les autres unités
    } else {
      alert('Nom d\'unité, rôle ou mot de passe incorrect');
    }
  });
// Données simulées (à remplacer par des appels API)
const sujets = [
    { id: 1, titre: 'Sujet 1', description: 'Description du sujet 1', competences: 'Compétences 1', duree: '3 mois', etat: 'disponible' },
    { id: 2, titre: 'Sujet 2', description: 'Description du sujet 2', competences: 'Compétences 2', duree: '6 mois', etat: 'réservé' },
];

// Afficher les sujets
function afficherSujets() {
  const sujetsList = document.getElementById('sujetsList');
  sujetsList.innerHTML = '';

  sujets.forEach((sujet) => {
    const sujetDiv = document.createElement('div');
    sujetDiv.classList.add('collapsible');
    sujetDiv.innerHTML = `
      <strong>${sujet.titre}</strong> (${sujet.etat})
      <div class="content">
        <p><strong>Description :</strong> ${sujet.description}</p>
        <p><strong>Compétences requises :</strong> ${sujet.competences}</p>
        <p><strong>Durée :</strong> ${sujet.duree}</p>
      </div>
    `;
    sujetsList.appendChild(sujetDiv);
  });

  // Gestion des collapsibles
  const collapsibles = document.querySelectorAll('.collapsible');
  collapsibles.forEach((collapsible) => {
    collapsible.addEventListener('click', function () {
      this.classList.toggle('active');
    });
  });
}

// Gestion du formulaire de proposition
document.getElementById('sujetForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const titre = document.getElementById('titre').value;
  const description = document.getElementById('description').value;
  const competences = document.getElementById('competences').value;
  const duree = document.getElementById('duree').value;

  const nouveauSujet = {
    id: sujets.length + 1,
    titre,
    description,
    competences,
    duree,
    etat: 'en attente',
  };

  sujets.push(nouveauSujet);
  afficherSujets();
  alert('Sujet proposé avec succès !');
  e.target.reset();
});
// Données simulées (à remplacer par des appels API)

  
  // Afficher les sujets
  function afficherSujets() {
    const sujetsList = document.getElementById('sujetsList');
    sujetsList.innerHTML = '';
  
    sujets.forEach((sujet) => {
      const sujetDiv = document.createElement('div');
      sujetDiv.classList.add('collapsible');
      sujetDiv.style.backgroundColor = sujet.etat === 'réservé' ? '#d4edda' : '#ffffff'; // Couleur verte si réservé
      sujetDiv.innerHTML = `
        <strong>${sujet.titre}</strong> (${sujet.etat})
        <div class="content">
          <p><strong>Description :</strong> ${sujet.description}</p>
          <p><strong>Compétences requises :</strong> ${sujet.competences}</p>
          <p><strong>Durée :</strong> ${sujet.duree}</p>
          ${sujet.etat === 'disponible' ? '<button onclick="postuler(' + sujet.id + ')">Postuler</button>' : ''}
        </div>
      `;
      sujetsList.appendChild(sujetDiv);
    });
  
    // Gestion des collapsibles
    const collapsibles = document.querySelectorAll('.collapsible');
    collapsibles.forEach((collapsible) => {
      collapsible.addEventListener('click', function () {
        this.classList.toggle('active');
      });
    });
  }
  
  // Postuler à un sujet
  function postuler(sujetId) {
    document.getElementById('candidature').style.display = 'block';
    document.getElementById('candidatureForm').onsubmit = function (e) {
      e.preventDefault();
  
      const nom = document.getElementById('nom').value;
      const prenom = document.getElementById('prenom').value;
      const telephone = document.getElementById('telephone').value;
      const classe = document.getElementById('classe').value;
      const email = document.getElementById('email').value;
  
      // Mettre à jour l'état du sujet
      const sujet = sujets.find((s) => s.id === sujetId);
      if (sujet) {
        sujet.etat = 'réservé';
        afficherSujets();
        alert('Candidature envoyée avec succès !');
        document.getElementById('candidature').style.display = 'none';
      }
    };
  }
  
  // Initialisation
  afficherSujets();
  // Données simulées (à remplacer par des appels API)

  
  // Afficher les sujets
  function afficherSujets() {
    const sujetsList = document.getElementById('sujetsList');
    sujetsList.innerHTML = '';
  
    sujets.forEach((sujet) => {
      const sujetDiv = document.createElement('div');
      sujetDiv.classList.add('collapsible');
      sujetDiv.style.backgroundColor = sujet.etat === 'réservé' ? '#d4edda' : '#ffffff'; // Couleur verte si réservé
      sujetDiv.innerHTML = `
        <strong>${sujet.titre}</strong> (${sujet.etat})
        <div class="content">
          <p><strong>Description :</strong> ${sujet.description}</p>
          <p><strong>Compétences requises :</strong> ${sujet.competences}</p>
          <p><strong>Durée :</strong> ${sujet.duree}</p>
        </div>
      `;
      sujetsList.appendChild(sujetDiv);
    });
  
    // Gestion des collapsibles
    const collapsibles = document.querySelectorAll('.collapsible');
    collapsibles.forEach((collapsible) => {
      collapsible.addEventListener('click', function () {
        this.classList.toggle('active');
      });
    });
  
    // Mettre à jour les statistiques
    document.getElementById('nombreSujets').textContent = sujets.length;
    document.getElementById('nombreReserves').textContent = sujets.filter((s) => s.etat === 'réservé').length;
  }
  
  // Initialisation
  afficherSujets();