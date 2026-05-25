document.addEventListener('DOMContentLoaded', function() {
  const formContacto = document.querySelector('#formContacto');
  if (!formContacto) return;

  formContacto.addEventListener('submit', function(event) {
    const nombre = formContacto.nombre.value.trim();
    const email = formContacto.email.value.trim();
    const consulta = formContacto.consulta.value.trim();

    if (!nombre || !email || !consulta) {
      event.preventDefault();
      alert('Por favor completa todos los campos antes de enviar.');
    }
  });
});
