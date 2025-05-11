function toggleProject(header) {
            const card = header.parentElement;
            card.classList.toggle('active');
            
            // Fermer les autres projets ouverts
            document.querySelectorAll('.project-card').forEach(otherCard => {
                if (otherCard !== card && otherCard.classList.contains('active')) {
                    otherCard.classList.remove('active');
                }
            });
        }
        
        // Option: Ouvrir le premier projet par défaut
        document.addEventListener('DOMContentLoaded', function() {
            const firstProject = document.querySelector('.project-card');
            if (firstProject) {
                firstProject.classList.add('active');
            }
});