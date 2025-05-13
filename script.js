document.getElementById('loginForm').addEventListener('submit', function(e) {
    const nomUnite = document.getElementById('nom_unite').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (nomUnite === '') {
        e.preventDefault();
        alert('Veuillez entrer le nom de l\'unité');
        return;
    }
    
    if (password === '') {
        e.preventDefault();
        alert('Veuillez entrer votre mot de passe');
        return;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 6 caractères');
        return;
    }
}); 