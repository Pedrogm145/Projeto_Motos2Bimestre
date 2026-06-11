function toggleSenha(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target.closest('.senha-toggle').querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function formatarTelefone(valor) {
    const digitos = valor.replace(/\D/g, '').slice(0, 11);

    if (digitos.length === 0) {
        return '';
    }

    if (digitos.length <= 2) {
        return `(${digitos}`;
    }

    if (digitos.length <= 6) {
        return `(${digitos.slice(0, 2)}) ${digitos.slice(2)}`;
    }

    if (digitos.length <= 10) {
        return `(${digitos.slice(0, 2)}) ${digitos.slice(2, 6)}-${digitos.slice(6)}`;
    }

    return `(${digitos.slice(0, 2)}) ${digitos.slice(2, 7)}-${digitos.slice(7)}`;
}

function validarFormulario() {
    const nome = document.getElementById('nome').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefone = document.getElementById('telefone').value.trim();
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    const digitosTelefone = telefone.replace(/\D/g, '');

    if (!nome || !email || !telefone || !senha || !confirmarSenha) {
        alert('Todos os campos são obrigatórios.');
        return false;
    }

    if (digitosTelefone.length !== 11) {
        alert('Informe um telefone válido com 11 números.');
        return false;
    }

    if (senha.length < 6) {
        alert('A senha deve ter no mínimo 6 caracteres.');
        return false;
    }

    if (senha !== confirmarSenha) {
        alert('As senhas não coincidem.');
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const telefoneInput = document.getElementById('telefone');

    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            this.value = formatarTelefone(this.value);
        });
    }
});
