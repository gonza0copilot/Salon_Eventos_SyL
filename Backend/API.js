// URL del endpoint PHP encargado de consultar las reservas filtradas por mes y año
const URL = 'http://localhost/Salon_Eventos_SyL/Salon_Eventos_SyL/backend/get_reservas.php?mes=9&anio=2026';

// Realiza una petición HTTP GET asíncrona hacia el backend para obtener las reservas
fetch(URL)
  // Convierte la respuesta cruda del servidor a un objeto JSON
  .then(response => response.json())
  // Procesa el objeto JSON recibido si la consulta fue exitosa
  .then(res => {
    if (res.status === 'success') {
      console.log('Reservas recibidas:', res.data);
      // Aquí iteramos res.data para llenar la tabla HTML o el calendario
    }
  })
  // Captura y muestra en consola cualquier error de red o de respuesta del servidor
  .catch(error => console.error('Error al obtener reservas:', error));


 