const URL = 'http://localhost/Salon_Eventos_SyL/Salon_Eventos_SyL/backend/get_reservas.php?mes=9&anio=2026';

fetch(URL)
  .then(response => response.json())
  .then(res => {
    if (res.status === 'success') {
      console.log('Reservas recibidas:', res.data);
      // Aquí iteramos res.data para llenar la tabla HTML o el calendario
    }
  })
  .catch(error => console.error('Error al obtener reservas:', error));