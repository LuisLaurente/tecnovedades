document.addEventListener('DOMContentLoaded', function() {
    const minPriceInput = document.getElementById('min_price');
    const maxPriceInput = document.getElementById('max_price');
    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const loading = document.getElementById('loading');
    const productosContainer = document.getElementById('productosContainer');
    const errorFiltros = document.getElementById('errorFiltros');
    const filtrosActivos = document.getElementById('filtrosActivos');
    const infoFiltros = document.getElementById('infoFiltros');
    const listaErrores = document.getElementById('listaErrores');

    // Función para mostrar/ocultar loading
    function toggleLoading(show) {
        loading.style.display = show ? 'inline' : 'none';
        btnFiltrar.disabled = show;
        btnLimpiar.disabled = show;
    }

    // Función para mostrar errores
    function mostrarErrores(errores) {
        if (errores && errores.length > 0) {
            listaErrores.innerHTML = '';
            errores.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                listaErrores.appendChild(li);
            });
            errorFiltros.style.display = 'block';
        } else {
            errorFiltros.style.display = 'none';
        }
    }

    // Función para mostrar filtros activos
    function mostrarFiltrosActivos(filtros, total) {
        if (filtros.min_price || filtros.max_price) {
            let info = '';
            if (filtros.min_price) {
                info += `<span class="filtro-tag">
                    Mínimo: S/ ${parseFloat(filtros.min_price).toFixed(2)}
                </span>`;
            }
            if (filtros.max_price) {
                info += `<span class="filtro-tag">
                    Máximo: S/ ${parseFloat(filtros.max_price).toFixed(2)}
                </span>`;
            }
            if (total !== undefined) {
                info += `<span class="filtro-tag total">
                    ${total} producto(s) encontrado(s)
                </span>`;
            }
            infoFiltros.innerHTML = info;
            filtrosActivos.style.display = 'block';
        } else {
            filtrosActivos.style.display = 'none';
        }
    }

    // Función para renderizar productos
    function renderizarProductos(productos) {
        if (!productos || productos.length === 0) {
            productosContainer.innerHTML = '<p>No hay productos disponibles.</p>';
            return;
        }

        let html = '';
        productos.forEach(producto => {
            html += `
                <div class="producto-card">
                    <strong>${producto.nombre}</strong><br>
                    ${producto.descripcion}<br>
                    Precio: S/ ${parseFloat(producto.precio).toFixed(2)}<br>
                    Visible: ${producto.visible ? 'Sí' : 'No'}<br>
                    
                    ${producto.categorias && producto.categorias.length > 0 ? 
                        `<div class="categoria-lista">Categorías: ${producto.categorias.join(', ')}</div>` : 
                        '<div class="categoria-lista">Sin categoría</div>'
                    }

                    <div class="acciones">
                        <a href="/producto/editar/${producto.id}">Editar</a> |
                        <a href="/producto/eliminar/${producto.id}" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</a>
                    </div>
                </div>
            `;
        });
        productosContainer.innerHTML = html;
    }

    // Función para realizar filtrado AJAX
    function filtrarProductos() {
        const minPrice = minPriceInput.value.trim();
        const maxPrice = maxPriceInput.value.trim();

        toggleLoading(true);
        mostrarErrores([]);

        // Construir parámetros
        const params = new URLSearchParams();
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        params.append('ajax', '1');

        // Realizar petición AJAX
        fetch('/producto/index?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            toggleLoading(false);
            
            if (data.success) {
                renderizarProductos(data.productos);
                mostrarFiltrosActivos(data.filtros, data.total);
                mostrarErrores([]);
            } else {
                mostrarErrores(data.errores || ['Error al filtrar productos']);
            }
        })
        .catch(error => {
            toggleLoading(false);
            console.error('Error:', error);
            mostrarErrores(['Error de conexión. Intenta nuevamente.']);
        });
    }

    // Función para limpiar filtros
    function limpiarFiltros() {
        minPriceInput.value = '';
        maxPriceInput.value = '';
        
        toggleLoading(true);
        mostrarErrores([]);
        mostrarFiltrosActivos({}, 0);

        fetch('/producto/index?ajax=1', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            toggleLoading(false);
            if (data.success) {
                renderizarProductos(data.productos);
            }
        })
        .catch(error => {
            toggleLoading(false);
            console.error('Error:', error);
        });
    }

    // Event listeners
    btnFiltrar.addEventListener('click', filtrarProductos);
    btnLimpiar.addEventListener('click', limpiarFiltros);

    // Filtrado en tiempo real mientras se escribe (opcional)
    let timeout;
    function filtrarEnTiempoReal() {
        clearTimeout(timeout);
        timeout = setTimeout(filtrarProductos, 500); // Espera 500ms después de que el usuario deje de escribir
    }

    minPriceInput.addEventListener('input', filtrarEnTiempoReal);
    maxPriceInput.addEventListener('input', filtrarEnTiempoReal);

    // Permitir filtrar con Enter
    minPriceInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filtrarProductos();
        }
    });

    maxPriceInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filtrarProductos();
        }
    });
});
