{{--
    Vista unificada para crear y visualizar movimientos de inventario.

    Usada por CreateMovement (mode=create) y ViewMovement (mode=view).
    La lógica dinámica se maneja con vanilla JS consumiendo la API interna.

    Variables esperadas:
    - $mode: 'create' | 'view'
    - $movement: Movement|null (solo en modo view)
    - $listUrl: URL para redirección tras éxito / botón cancelar
--}}

<x-filament-panels::page>
    <style>
        .mv-form { max-width: 960px; margin: 0 auto; }
        .mv-type-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
        .mv-type-btn {
            display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
            padding: 1rem; border: 2px solid var(--mv-border, #e5e7eb); border-radius: 0.75rem;
            cursor: pointer; transition: all 0.15s ease; background: transparent;
            font-size: 0.875rem; font-weight: 500; color: inherit;
        }
        .mv-type-btn:hover { border-color: var(--mv-hover, #9ca3af); }
        .mv-type-btn.active { border-color: var(--mv-active); background: var(--mv-bg); }
        .mv-type-btn[data-type="input"] { --mv-active: #16a34a; --mv-bg: rgba(22,163,74,0.08); }
        .mv-type-btn[data-type="output"] { --mv-active: #dc2626; --mv-bg: rgba(220,38,38,0.08); }
        .mv-type-btn[data-type="transfer"] { --mv-active: #2563eb; --mv-bg: rgba(37,99,235,0.08); }
        .mv-type-btn[data-type="adjustment"] { --mv-active: #d97706; --mv-bg: rgba(217,119,6,0.08); }
        .mv-type-btn .mv-icon { font-size: 1.5rem; }
        .mv-section { display: none; }
        .mv-section.active { display: block; }
        .mv-field { margin-bottom: 1rem; }
        .mv-field label { display: block; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.375rem; }
        .mv-field select, .mv-field input, .mv-field textarea {
            width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; background: var(--mv-input-bg, #fff); color: inherit;
        }
        .mv-field select:focus, .mv-field input:focus, .mv-field textarea:focus { outline: 2px solid #f59e0b; outline-offset: 1px; }
        .mv-field select:disabled, .mv-field input:disabled, .mv-field textarea:disabled { opacity: 0.6; cursor: not-allowed; }
        .mv-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto; gap: 0.75rem; align-items: end; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-bottom: 0.5rem; }
        .mv-row-remove { padding: 0.5rem; background: none; border: none; cursor: pointer; color: #dc2626; font-size: 1.25rem; border-radius: 0.375rem; }
        .mv-row-remove:hover { background: rgba(220,38,38,0.1); }
        .mv-add-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; border: 1px dashed #9ca3af; border-radius: 0.5rem; background: transparent; cursor: pointer; font-size: 0.8125rem; color: inherit; margin-top: 0.5rem; }
        .mv-add-btn:hover { border-color: #6b7280; background: rgba(0,0,0,0.02); }
        .mv-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; }
        .mv-btn { padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: none; transition: background 0.15s; text-align: center; }
        .mv-btn-cancel { background: #f3f4f6; color: #374151; }
        .mv-btn-cancel:hover { background: #e5e7eb; }
        .mv-btn-submit { background: #f59e0b; color: #fff; }
        .mv-btn-submit:hover { background: #d97706; }
        .mv-btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
        .mv-stock-badge { display: inline-block; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #dbeafe; color: #1d4ed8; }
        .mv-notification { position: fixed; top: 1rem; right: 1rem; padding: 1rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; z-index: 50; animation: mvSlideIn 0.3s ease; }
        .mv-notification.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .mv-notification.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        @keyframes mvSlideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .mv-loading { opacity: 0.5; pointer-events: none; }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .mv-type-grid { grid-template-columns: repeat(2, 1fr); }
            .mv-row { grid-template-columns: 1fr; position: relative; padding-top: 2rem; }
            .mv-row-remove { position: absolute; top: 0.5rem; right: 0.5rem; }
            .mv-actions { flex-direction: column-reverse; }
            .mv-btn { width: 100%; }
        }

        /* Dark mode */
        .dark .mv-type-btn { --mv-border: #374151; --mv-hover: #4b5563; }
        .dark .mv-field select, .dark .mv-field input, .dark .mv-field textarea { background: #1f2937; border-color: #374151; color: #f9fafb; }
        .dark .mv-row { border-color: #374151; }
        .dark .mv-add-btn { border-color: #4b5563; }
        .dark .mv-actions { border-color: #374151; }
        .dark .mv-btn-cancel { background: #374151; color: #d1d5db; }
        .dark .mv-btn-cancel:hover { background: #4b5563; }
    </style>

    <div class="mv-form" id="movementForm"
         data-mode="{{ $mode }}"
         data-csrf="{{ csrf_token() }}"
         data-list-url="{{ $listUrl }}"
         data-api-base="{{ url('/internal/inventory') }}"
         data-api-movements="{{ url('/api/movements') }}"
         @if($mode === 'view' && $movement)
             data-movement="{{ json_encode([
                 'id' => $movement->id,
                 'type' => $movement->type->value,
                 'item_id' => $movement->item_id,
                 'item_name' => $movement->item->name ?? '',
                 'location_id' => $movement->location_id,
                 'location_name' => $movement->location->name ?? '',
                 'quantity' => (float) $movement->quantity,
                 'signed_quantity' => $movement->getSignedQuantity(),
                 'notes' => $movement->notes,
                 'user_name' => $movement->user->name ?? '',
                 'created_at' => $movement->created_at?->format('d/m/Y H:i'),
             ]) }}"
         @endif
    >
        {{-- Selector de tipo --}}
        <div class="mv-type-grid">
            <button type="button" class="mv-type-btn" data-type="input" @if($mode === 'view') disabled @endif>
                <span class="mv-icon">📥</span>
                <span>Ingreso</span>
            </button>
            <button type="button" class="mv-type-btn" data-type="output" @if($mode === 'view') disabled @endif>
                <span class="mv-icon">📤</span>
                <span>Salida</span>
            </button>
            <button type="button" class="mv-type-btn" data-type="transfer" @if($mode === 'view') disabled @endif>
                <span class="mv-icon">🔄</span>
                <span>Intercambio</span>
            </button>
            <button type="button" class="mv-type-btn" data-type="adjustment" @if($mode === 'view') disabled @endif>
                <span class="mv-icon">📋</span>
                <span>Ajuste</span>
            </button>
        </div>

        {{-- ============================================================ --}}
        {{-- INGRESO: Un artículo → múltiples ubicaciones                 --}}
        {{-- ============================================================ --}}
        <div class="mv-section" id="section-input">
            <div class="mv-field">
                <label for="input-item">Artículo</label>
                <select id="input-item" @if($mode === 'view') disabled @endif>
                    <option value="">Seleccionar artículo...</option>
                </select>
            </div>

            <label style="font-size:0.8125rem;font-weight:500;margin-bottom:0.375rem;display:block;">Distribuciones por ubicación</label>
            <div id="input-distributions">
                {{-- Filas dinámicas insertadas por JS --}}
            </div>
            @if($mode === 'create')
                <button type="button" class="mv-add-btn" id="input-add-row">+ Agregar ubicación</button>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- SALIDA: Un artículo de una ubicación                         --}}
        {{-- ============================================================ --}}
        <div class="mv-section" id="section-output">
            <div class="mv-field">
                <label for="output-item">Artículo</label>
                <select id="output-item" @if($mode === 'view') disabled @endif>
                    <option value="">Seleccionar artículo...</option>
                </select>
            </div>
            <div class="mv-field">
                <label for="output-location">Ubicación <span id="output-stock-badge" class="mv-stock-badge" style="display:none;"></span></label>
                <select id="output-location" @if($mode === 'view') disabled @endif>
                    <option value="">Seleccione un artículo primero...</option>
                </select>
            </div>
            <div class="mv-field">
                <label for="output-quantity">Cantidad</label>
                <input type="number" id="output-quantity" min="0.01" step="0.01" value="1" @if($mode === 'view') disabled @endif>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- INTERCAMBIO: Múltiples artículos entre ubicaciones           --}}
        {{-- ============================================================ --}}
        <div class="mv-section" id="section-transfer">
            <label style="font-size:0.8125rem;font-weight:500;margin-bottom:0.375rem;display:block;">Transferencias</label>
            <div id="transfer-rows">
                {{-- Filas dinámicas insertadas por JS --}}
            </div>
            @if($mode === 'create')
                <button type="button" class="mv-add-btn" id="transfer-add-row">+ Agregar transferencia</button>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- AJUSTE: Múltiples artículos en diferentes ubicaciones        --}}
        {{-- ============================================================ --}}
        <div class="mv-section" id="section-adjustment">
            <label style="font-size:0.8125rem;font-weight:500;margin-bottom:0.375rem;display:block;">Ajustes de inventario</label>
            <div id="adjustment-rows">
                {{-- Filas dinámicas insertadas por JS --}}
            </div>
            @if($mode === 'create')
                <button type="button" class="mv-add-btn" id="adjustment-add-row">+ Agregar ajuste</button>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- OBSERVACIONES (común a todos los tipos)                      --}}
        {{-- ============================================================ --}}
        <div class="mv-field" id="notes-section" style="display:none; margin-top:1rem;">
            <label for="movement-notes">Observaciones</label>
            <textarea id="movement-notes" rows="3" placeholder="Ej. Compra factura #001, ajuste por daño, etc." @if($mode === 'view') disabled @endif>{{ $mode === 'view' && $movement ? $movement->notes : '' }}</textarea>
        </div>

        {{-- ============================================================ --}}
        {{-- INFORMACIÓN DE REGISTRO (solo modo view)                     --}}
        {{-- ============================================================ --}}
        @if($mode === 'view' && $movement)
            <div style="margin-top:1rem; padding:1rem; background:rgba(0,0,0,0.03); border-radius:0.5rem; font-size:0.8125rem;">
                <p><strong>Registrado por:</strong> {{ $movement->user->name ?? 'N/A' }}</p>
                <p><strong>Fecha:</strong> {{ $movement->created_at?->format('d/m/Y H:i') }}</p>
                <p><strong>ID:</strong> #{{ $movement->id }}</p>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- ACCIONES                                                     --}}
        {{-- ============================================================ --}}
        @if($mode === 'create')
            <div class="mv-actions">
                <a href="{{ $listUrl }}" class="mv-btn mv-btn-cancel">Cancelar</a>
                <button type="button" class="mv-btn mv-btn-submit" id="submit-movement" disabled>
                    Registrar Movimiento
                </button>
            </div>
        @endif
    </div>

    {{-- Templates para filas de repeater (ocultos, clonados por JS) --}}
    <template id="tpl-input-row">
        <div class="mv-row" data-row>
            <div class="mv-field">
                <label>Ubicación</label>
                <select data-field="location_id">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Cantidad</label>
                <input type="number" data-field="quantity" min="0.01" step="0.01" value="1">
            </div>
            <button type="button" class="mv-row-remove" title="Eliminar">&times;</button>
        </div>
    </template>

    <template id="tpl-transfer-row">
        <div class="mv-row" data-row>
            <div class="mv-field">
                <label>Artículo</label>
                <select data-field="item_id">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Origen <span data-stock-badge class="mv-stock-badge" style="display:none;"></span></label>
                <select data-field="origin_location_id">
                    <option value="">Seleccione artículo...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Destino</label>
                <select data-field="destination_location_id">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Cantidad</label>
                <input type="number" data-field="quantity" min="0.01" step="0.01" value="1">
            </div>
            <button type="button" class="mv-row-remove" title="Eliminar">&times;</button>
        </div>
    </template>

    <template id="tpl-adjustment-row">
        <div class="mv-row" data-row>
            <div class="mv-field">
                <label>Artículo</label>
                <select data-field="item_id">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Ubicación</label>
                <select data-field="location_id">
                    <option value="">Seleccione artículo...</option>
                </select>
            </div>
            <div class="mv-field">
                <label>Stock actual</label>
                <input type="text" data-field="current_stock" disabled value="—">
            </div>
            <div class="mv-field">
                <label>Cantidad real</label>
                <input type="number" data-field="new_quantity" min="0" step="0.01" value="0">
            </div>
            <button type="button" class="mv-row-remove" title="Eliminar">&times;</button>
        </div>
    </template>

    @push('scripts')
        <script src="{{ asset('js/movement-form.js') }}"></script>
    @endpush
</x-filament-panels::page>
