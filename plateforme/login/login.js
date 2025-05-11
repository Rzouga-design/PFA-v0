document.addEventListener('DOMContentLoaded', function() {
    const uniteSelect = document.getElementById('unite');
    uniteSelect.addEventListener('change', updateRoleOptions);
    
    // Initialiser l'état du champ rôle au chargement
    updateRoleOptions();
  });
  
  function updateRoleOptions() {
    const uniteSelect = document.getElementById('unite');
    const roleSelect = document.getElementById('role');
    
    // Réinitialiser le champ rôle
    roleSelect.innerHTML = '';
    
    if (!uniteSelect.value) {
      // Cas 1: Aucune unité sélectionnée
      roleSelect.disabled = true;
      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = 'Sélectionnez d\'abord une unité';
      roleSelect.appendChild(defaultOption);
    } else if (uniteSelect.value === 'academie') {
      // Cas 2: Académie militaire sélectionnée
      roleSelect.disabled = false;
      
      // Option par défaut
      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = 'Sélectionnez un rôle';
      roleSelect.appendChild(defaultOption);
      
      // Ajouter les options spécifiques à l'académie
      addRoleOption(roleSelect, 'eleve', 'Élève officier');
      addRoleOption(roleSelect, 'encadrant', 'Encadrant');
      addRoleOption(roleSelect, 'admin', 'Administrateur');
    } else {
      // Cas 3: Autre unité sélectionnée
      roleSelect.disabled = false;
      
      // Option par défaut
      const defaultOption = document.createElement('option');
      defaultOption.value = '';
      defaultOption.textContent = 'Sélectionnez un rôle';
      roleSelect.appendChild(defaultOption);
      
      // Seul le rôle encadrant est disponible
      addRoleOption(roleSelect, 'encadrant', 'Encadrant');
    }
  }
  
  function addRoleOption(selectElement, value, text) {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = text;
    selectElement.appendChild(option);
  }