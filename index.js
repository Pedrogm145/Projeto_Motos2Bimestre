  // Profile Dropdown Menu
 const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        // Toggle dropdown when clicking on profile image
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        // Close dropdown when clicking on a menu item
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                profileDropdown.classList.remove('active');
            });
        });

        // Remover alerta de exclusão de conta após 4 segundos
        const alertaDeletado = document.getElementById('alertaDeletado');
        if (alertaDeletado) {
            setTimeout(function() {
                alertaDeletado.style.opacity = '0';
                alertaDeletado.style.maxHeight = '0';
                alertaDeletado.style.padding = '0';
                setTimeout(function() {
                    alertaDeletado.remove();
                }, 500);
            }, 4000);
        }

        // Redirecionar para gerenciar serviços ao clicar em um card
        const servicoCards = document.querySelectorAll('.servico-card');
        servicoCards.forEach(card => {
            card.addEventListener('click', function() {
                window.location.href = 'servicos.php';
            });
        });