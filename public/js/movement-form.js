/**
 * Módulo de formulario de movimientos de inventario.
 *
 * Gestiona la lógica dinámica del formulario unificado de movimientos:
 * selección de tipo, repeaters, consultas API en tiempo real y envío.
 *
 * Principio: cada función tiene una sola responsabilidad.
 * No se usa ningún framework — vanilla JS puro.
 */
(function () {
    'use strict';

    /* ================================================================
     * CONFIGURACIÓN Y ESTADO
     * ================================================================ */

    const form = document.getElementById('movementForm');
    if (!form) return;

    const CONFIG = Object.freeze({
        mode: form.dataset.mode,
        csrf: form.dataset.csrf,
        apiBase: form.dataset.apiBase,
        apiMovements: form.dataset.apiMovements,
        listUrl: form.dataset.listUrl,
    });

    /** Cache de datos para evitar consultas repetidas */
    const cache = { items: null, locations: null };

    /* ================================================================
     * UTILIDADES
     * ================================================================ */

    /**
     * Realiza una petición GET a la API interna.
     *
     * @param {string} endpoint - Ruta relativa al apiBase.
     * @param {Object} params - Query params opcionales.
     * @returns {Promise<any>} Datos JSON de la respuesta.
     */
    async function apiGet(endpoint, params = {}) {
        const url = new URL(`${CONFIG.apiBase}${endpoint}`, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        });

        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }

        return response.json();
    }

    /**
     * Realiza una petición POST a la API de movimientos.
     *
     * @param {string} endpoint - Ruta relativa al apiMovements.
     * @param {Object} body - Cuerpo de la petición.
     * @returns {Promise<any>} Datos JSON de la respuesta.
     */
    async function apiPost(endpoint, body) {
        const response = await fetch(`${CONFIG.apiMovements}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrf,
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });

        const data = await response.json();

        if (!response.ok) {
            const message = data.message || extractValidationErrors(data);
            throw new Error(message);
        }

        return data;
    }

    /**
     * Extrae mensajes de error de una respuesta de validación Laravel.
     *
     * @param {Object} data - Respuesta JSON con errores.
     * @returns {string} Mensajes concatenados.
     */
    function extractValidationErrors(data) {
        if (!data.errors) return 'Error desconocido.';
        return Object.values(data.errors).flat().join('\n');
    }

    /**
     * Muestra una notificación temporal en pantalla.
     *
     * @param {'success'|'error'} type - Tipo de notificación.
     * @param {string} message - Mensaje a mostrar.
     */
    function showNotification(type, message) {
        const existing = document.querySelector('.mv-notification');
        if (existing) existing.remove();

        const el = document.createElement('div');
        el.className = `mv-notification ${type}`;
        el.textContent = message;
        document.body.appendChild(el);

        setTimeout(() => el.remove(), 4000);
    }

    /**
     * Puebla un elemento <select> con opciones.
     *
     * @param {HTMLSelectElement} select - Elemento select.
     * @param {Array<{value: string|number, label: string}>} options - Opciones a insertar.
     * @param {string} placeholder - Texto del primer option vacío.
     */
    function populateSelect(select, options, placeholder = 'Seleccionar...') {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.label;
            select.appendChild(option);
        });
    }

    /* ================================================================
     * CARGA DE DATOS
     * ================================================================ */

    /** Carga y cachea la lista de artículos */
    async function loadItems() {
        if (!cache.items) {
            const data = await apiGet('/items');
            cache.items = data.map(item => ({
                value: item.id,
                label: `${item.name}${item.sku ? ` (${item.sku})` : ''} — Stock: ${item.current_stock}`,
            }));
        }
        return cache.items;
    }

    /** Carga y cachea la lista de ubicaciones */
    async function loadLocations() {
        if (!cache.locations) {
            const data = await apiGet('/locations');
            cache.locations = data.map(loc => ({
                value: loc.id,
                label: loc.name,
            }));
        }
        return cache.locations;
    }

    /**
     * Carga las ubicaciones con stock de un artículo.
     *
     * @param {number} itemId - ID del artículo.
     * @returns {Promise<Array>} Ubicaciones con stock.
     */
    async function loadItemLocations(itemId) {
        const data = await apiGet('/item-locations', { item_id: itemId });
        return data.map(il => ({
            value: il.location_id,
            label: `${il.location_name} (Stock: ${il.quantity})`,
            stock: il.quantity,
        }));
    }

    /**
     * Consulta el stock actual de un artículo en una ubicación.
     *
     * @param {number} itemId - ID del artículo.
     * @param {number} locationId - ID de la ubicación.
     * @returns {Promise<number>} Stock actual.
     */
    async function fetchStock(itemId, locationId) {
        const data = await apiGet('/stock', { item_id: itemId, location_id: locationId });
        return data.stock;
    }

    /* ================================================================
     * SELECTOR DE TIPO
     * ================================================================ */

    let currentType = null;

    function initTypeSelector() {
        const buttons = form.querySelectorAll('.mv-type-btn');
        const sections = form.querySelectorAll('.mv-section');
        const notesSection = document.getElementById('notes-section');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (CONFIG.mode === 'view') return;

                const type = btn.dataset.type;
                currentType = type;

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                sections.forEach(s => s.classList.remove('active'));
                const section = document.getElementById(`section-${type}`);
                if (section) section.classList.add('active');

                notesSection.style.display = 'block';
                updateSubmitState();
            });
        });
    }

    /* ================================================================
     * REPEATER GENÉRICO
     * ================================================================ */

    /**
     * Inicializa un repeater: botón agregar, delegación de evento remover.
     *
     * @param {string} containerId - ID del contenedor de filas.
     * @param {string} templateId - ID del <template> a clonar.
     * @param {string} addBtnId - ID del botón agregar.
     * @param {Function} onRowAdded - Callback al agregar fila.
     */
    function initRepeater(containerId, templateId, addBtnId, onRowAdded) {
        const container = document.getElementById(containerId);
        const template = document.getElementById(templateId);
        const addBtn = document.getElementById(addBtnId);

        if (!container || !template) return;

        function addRow() {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('[data-row]');
            container.appendChild(clone);
            if (onRowAdded) onRowAdded(row);
            updateSubmitState();
        }

        if (addBtn) {
            addBtn.addEventListener('click', addRow);
        }

        container.addEventListener('click', (e) => {
            if (e.target.closest('.mv-row-remove')) {
                const row = e.target.closest('[data-row]');
                if (row && container.querySelectorAll('[data-row]').length > 1) {
                    row.remove();
                    updateSubmitState();
                }
            }
        });

        // Agregar primera fila por defecto
        if (CONFIG.mode === 'create') {
            addRow();
        }
    }

    /* ================================================================
     * SECCIÓN: INGRESO
     * ================================================================ */

    async function initInputSection() {
        const itemSelect = document.getElementById('input-item');
        const items = await loadItems();
        populateSelect(itemSelect, items, 'Seleccionar artículo...');

        initRepeater('input-distributions', 'tpl-input-row', 'input-add-row', async (row) => {
            const locationSelect = row.querySelector('[data-field="location_id"]');
            const locations = await loadLocations();
            populateSelect(locationSelect, locations, 'Seleccionar ubicación...');
        });
    }

    /* ================================================================
     * SECCIÓN: SALIDA
     * ================================================================ */

    async function initOutputSection() {
        const itemSelect = document.getElementById('output-item');
        const locationSelect = document.getElementById('output-location');
        const stockBadge = document.getElementById('output-stock-badge');
        const items = await loadItems();
        populateSelect(itemSelect, items, 'Seleccionar artículo...');

        itemSelect.addEventListener('change', async () => {
            const itemId = itemSelect.value;
            stockBadge.style.display = 'none';

            if (!itemId) {
                populateSelect(locationSelect, [], 'Seleccione un artículo primero...');
                return;
            }

            locationSelect.innerHTML = '<option value="">Cargando...</option>';
            const itemLocations = await loadItemLocations(itemId);
            populateSelect(locationSelect, itemLocations, 'Seleccionar ubicación...');
            updateSubmitState();
        });

        locationSelect.addEventListener('change', async () => {
            const itemId = itemSelect.value;
            const locationId = locationSelect.value;

            if (itemId && locationId) {
                const stock = await fetchStock(itemId, locationId);
                stockBadge.textContent = `Stock: ${stock}`;
                stockBadge.style.display = 'inline-block';
            } else {
                stockBadge.style.display = 'none';
            }
            updateSubmitState();
        });
    }

    /* ================================================================
     * SECCIÓN: INTERCAMBIO (TRANSFER)
     * ================================================================ */

    async function initTransferSection() {
        const items = await loadItems();
        const locations = await loadLocations();

        initRepeater('transfer-rows', 'tpl-transfer-row', 'transfer-add-row', (row) => {
            const itemSelect = row.querySelector('[data-field="item_id"]');
            const originSelect = row.querySelector('[data-field="origin_location_id"]');
            const destSelect = row.querySelector('[data-field="destination_location_id"]');
            const stockBadge = row.querySelector('[data-stock-badge]');

            populateSelect(itemSelect, items, 'Seleccionar artículo...');
            populateSelect(destSelect, locations, 'Seleccionar destino...');

            itemSelect.addEventListener('change', async () => {
                const itemId = itemSelect.value;
                if (stockBadge) stockBadge.style.display = 'none';

                if (!itemId) {
                    populateSelect(originSelect, [], 'Seleccione artículo...');
                    return;
                }

                originSelect.innerHTML = '<option value="">Cargando...</option>';
                const itemLocations = await loadItemLocations(itemId);
                populateSelect(originSelect, itemLocations, 'Seleccionar origen...');
                updateSubmitState();
            });

            originSelect.addEventListener('change', async () => {
                const itemId = itemSelect.value;
                const locationId = originSelect.value;
                if (itemId && locationId && stockBadge) {
                    const stock = await fetchStock(itemId, locationId);
                    stockBadge.textContent = `Stock: ${stock}`;
                    stockBadge.style.display = 'inline-block';
                } else if (stockBadge) {
                    stockBadge.style.display = 'none';
                }
                updateSubmitState();
            });
        });
    }

    /* ================================================================
     * SECCIÓN: AJUSTE
     * ================================================================ */

    async function initAdjustmentSection() {
        const items = await loadItems();

        initRepeater('adjustment-rows', 'tpl-adjustment-row', 'adjustment-add-row', (row) => {
            const itemSelect = row.querySelector('[data-field="item_id"]');
            const locationSelect = row.querySelector('[data-field="location_id"]');
            const currentStockInput = row.querySelector('[data-field="current_stock"]');

            populateSelect(itemSelect, items, 'Seleccionar artículo...');

            itemSelect.addEventListener('change', async () => {
                const itemId = itemSelect.value;
                currentStockInput.value = '—';

                if (!itemId) {
                    populateSelect(locationSelect, [], 'Seleccione artículo...');
                    return;
                }

                locationSelect.innerHTML = '<option value="">Cargando...</option>';
                const itemLocations = await loadItemLocations(itemId);

                /** En ajuste mostramos TODAS las ubicaciones con stock (incluso 0) */
                const locations = await loadLocations();
                const enriched = locations.map(loc => {
                    const found = itemLocations.find(il => String(il.value) === String(loc.value));
                    return {
                        value: loc.value,
                        label: found ? `${loc.label} (Stock: ${found.stock})` : loc.label,
                    };
                });
                populateSelect(locationSelect, enriched, 'Seleccionar ubicación...');
                updateSubmitState();
            });

            locationSelect.addEventListener('change', async () => {
                const itemId = itemSelect.value;
                const locationId = locationSelect.value;

                if (itemId && locationId) {
                    const stock = await fetchStock(itemId, locationId);
                    currentStockInput.value = stock;
                } else {
                    currentStockInput.value = '—';
                }
                updateSubmitState();
            });
        });
    }

    /* ================================================================
     * VALIDACIÓN Y ESTADO DEL SUBMIT
     * ================================================================ */

    function updateSubmitState() {
        const submitBtn = document.getElementById('submit-movement');
        if (!submitBtn) return;

        submitBtn.disabled = !isFormValid();
    }

    /**
     * Valida que el formulario tenga los datos mínimos según el tipo.
     *
     * @returns {boolean} True si el formulario es válido.
     */
    function isFormValid() {
        if (!currentType) return false;

        switch (currentType) {
            case 'input':
                return isInputValid();
            case 'output':
                return isOutputValid();
            case 'transfer':
                return isTransferValid();
            case 'adjustment':
                return isAdjustmentValid();
            default:
                return false;
        }
    }

    function isInputValid() {
        const itemId = document.getElementById('input-item').value;
        if (!itemId) return false;

        const rows = document.querySelectorAll('#input-distributions [data-row]');
        if (rows.length === 0) return false;

        return Array.from(rows).every(row => {
            const locationId = row.querySelector('[data-field="location_id"]').value;
            const quantity = parseFloat(row.querySelector('[data-field="quantity"]').value);
            return locationId && quantity > 0;
        });
    }

    function isOutputValid() {
        const itemId = document.getElementById('output-item').value;
        const locationId = document.getElementById('output-location').value;
        const quantity = parseFloat(document.getElementById('output-quantity').value);
        return itemId && locationId && quantity > 0;
    }

    function isTransferValid() {
        const rows = document.querySelectorAll('#transfer-rows [data-row]');
        if (rows.length === 0) return false;

        return Array.from(rows).every(row => {
            const itemId = row.querySelector('[data-field="item_id"]').value;
            const originId = row.querySelector('[data-field="origin_location_id"]').value;
            const destId = row.querySelector('[data-field="destination_location_id"]').value;
            const quantity = parseFloat(row.querySelector('[data-field="quantity"]').value);
            return itemId && originId && destId && originId !== destId && quantity > 0;
        });
    }

    function isAdjustmentValid() {
        const rows = document.querySelectorAll('#adjustment-rows [data-row]');
        if (rows.length === 0) return false;

        return Array.from(rows).every(row => {
            const itemId = row.querySelector('[data-field="item_id"]').value;
            const locationId = row.querySelector('[data-field="location_id"]').value;
            const newQuantity = row.querySelector('[data-field="new_quantity"]').value;
            return itemId && locationId && newQuantity !== '';
        });
    }

    /* ================================================================
     * ENVÍO DEL FORMULARIO
     * ================================================================ */

    async function handleSubmit() {
        const submitBtn = document.getElementById('submit-movement');
        if (!submitBtn || submitBtn.disabled) return;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
        form.classList.add('mv-loading');

        try {
            switch (currentType) {
                case 'input':
                    await submitInput();
                    break;
                case 'output':
                    await submitOutput();
                    break;
                case 'transfer':
                    await submitTransfer();
                    break;
                case 'adjustment':
                    await submitAdjustment();
                    break;
            }
            showNotification('success', 'Movimiento registrado exitosamente.');
            setTimeout(() => { window.location.href = CONFIG.listUrl; }, 1500);
        } catch (error) {
            showNotification('error', error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Registrar Movimiento';
            form.classList.remove('mv-loading');
        }
    }

    async function submitInput() {
        const itemId = parseInt(document.getElementById('input-item').value);
        const rows = document.querySelectorAll('#input-distributions [data-row]');
        const distributions = Array.from(rows).map(row => ({
            location_id: parseInt(row.querySelector('[data-field="location_id"]').value),
            quantity: parseFloat(row.querySelector('[data-field="quantity"]').value),
        }));
        const notes = document.getElementById('movement-notes').value || null;

        await apiPost('/input', { item_id: itemId, distributions, notes });
    }

    async function submitOutput() {
        const body = {
            item_id: parseInt(document.getElementById('output-item').value),
            location_id: parseInt(document.getElementById('output-location').value),
            quantity: parseFloat(document.getElementById('output-quantity').value),
            notes: document.getElementById('movement-notes').value || null,
        };

        await apiPost('/output', body);
    }

    async function submitTransfer() {
        const rows = document.querySelectorAll('#transfer-rows [data-row]');
        const transfers = Array.from(rows).map(row => ({
            item_id: parseInt(row.querySelector('[data-field="item_id"]').value),
            origin_location_id: parseInt(row.querySelector('[data-field="origin_location_id"]').value),
            destination_location_id: parseInt(row.querySelector('[data-field="destination_location_id"]').value),
            quantity: parseFloat(row.querySelector('[data-field="quantity"]').value),
        }));
        const notes = document.getElementById('movement-notes').value || null;

        await apiPost('/batch-transfer', { transfers, notes });
    }

    async function submitAdjustment() {
        const rows = document.querySelectorAll('#adjustment-rows [data-row]');
        const adjustments = Array.from(rows).map(row => ({
            item_id: parseInt(row.querySelector('[data-field="item_id"]').value),
            location_id: parseInt(row.querySelector('[data-field="location_id"]').value),
            new_quantity: parseFloat(row.querySelector('[data-field="new_quantity"]').value),
        }));
        const notes = document.getElementById('movement-notes').value || null;

        await apiPost('/batch-adjustment', { adjustments, notes });
    }

    /* ================================================================
     * MODO VIEW: Renderizar datos del movimiento existente
     * ================================================================ */

    function initViewMode() {
        const movementData = form.dataset.movement;
        if (!movementData) return;

        const movement = JSON.parse(movementData);

        // Activar el botón de tipo correspondiente
        const typeBtn = form.querySelector(`.mv-type-btn[data-type="${movement.type}"]`);
        if (typeBtn) typeBtn.classList.add('active');

        // Mostrar la sección correspondiente
        const section = document.getElementById(`section-${movement.type}`);
        if (section) section.classList.add('active');

        const notesSection = document.getElementById('notes-section');
        notesSection.style.display = 'block';

        renderViewData(movement);
    }

    /**
     * Renderiza los datos del movimiento en modo solo lectura.
     * Crea campos disabled con los valores del registro.
     */
    function renderViewData(movement) {
        const sectionId = `section-${movement.type}`;
        const section = document.getElementById(sectionId);
        if (!section) return;

        section.innerHTML = '';

        const fieldsHtml = `
            <div class="mv-field">
                <label>Artículo</label>
                <input type="text" value="${movement.item_name}" disabled>
            </div>
            <div class="mv-field">
                <label>Ubicación</label>
                <input type="text" value="${movement.location_name}" disabled>
            </div>
            <div class="mv-field">
                <label>Cantidad</label>
                <input type="text" value="${movement.signed_quantity}" disabled>
            </div>
        `;

        section.innerHTML = fieldsHtml;
    }

    /* ================================================================
     * ESCUCHA DE CAMBIOS PARA VALIDACIÓN EN TIEMPO REAL
     * ================================================================ */

    function initChangeListeners() {
        form.addEventListener('input', () => updateSubmitState());
        form.addEventListener('change', () => updateSubmitState());
    }

    /* ================================================================
     * INICIALIZACIÓN
     * ================================================================ */

    async function init() {
        if (CONFIG.mode === 'view') {
            initTypeSelector();
            initViewMode();
            return;
        }

        initTypeSelector();
        initChangeListeners();

        // Inicializar todas las secciones en paralelo
        await Promise.all([
            initInputSection(),
            initOutputSection(),
            initTransferSection(),
            initAdjustmentSection(),
        ]);

        // Listener del submit
        const submitBtn = document.getElementById('submit-movement');
        if (submitBtn) {
            submitBtn.addEventListener('click', handleSubmit);
        }
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
