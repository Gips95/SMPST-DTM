fetch('cart_count.php')
  .then(r => {
    if (!r.ok) throw new Error("Error en la respuesta");
    return r.json();
  })
  .then(d => {
    const b = document.getElementById('badge-cart');
    if(b != null){
      if (d.count > 0) {
        b.style.display = 'inline-block';
        b.textContent = d.count;
      } else {
        b.style.display = 'none'; // 👈 Oculta el badge si no hay items
      }
    }
  })
  .catch(error => console.error("Error al obtener el carrito:", error));