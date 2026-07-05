{{--
    Vista unificada para crear y visualizar movimientos de inventario.

    Usada por CreateMovement (mode=create) y ViewMovement (mode=view).
    La lógica dinámica se maneja con vanilla JS consumiendo la API interna.

    Variables esperadas:
    - $mode: 'create' | 'view'
    - $movement: Movement|null (solo en modo view)
    - $listUrl: URL para redirección tras éxito / botón cancelar
--}}
@vite('resources/css/app.css')
<x-filament-panels::page>

    <style>
        .mv-form {
            max-width: 960px;
            margin: 0 auto;
        }

        .mv-type-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .mv-type-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 2px solid var(--mv-border, #e5e7eb);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.15s ease;
            background: transparent;
            font-size: 0.875rem;
            font-weight: 500;
            color: inherit;
        }

        .mv-type-btn:hover {
            border-color: var(--mv-hover, #9ca3af);
        }

        .mv-type-btn.active {
            border-color: var(--mv-active);
            background: var(--mv-bg);
        }

        .mv-type-btn[data-type="input"] {
            --mv-active: #16a34a;
            --mv-bg: rgba(22, 163, 74, 0.08);
        }

        .mv-type-btn[data-type="output"] {
            --mv-active: #dc2626;
            --mv-bg: rgba(220, 38, 38, 0.08);
        }

        .mv-type-btn[data-type="transfer"] {
            --mv-active: #2563eb;
            --mv-bg: rgba(37, 99, 235, 0.08);
        }

        .mv-type-btn[data-type="adjustment"] {
            --mv-active: #d97706;
            --mv-bg: rgba(217, 119, 6, 0.08);
        }

        .mv-type-btn .mv-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            color: #6b7280;
        }

        .mv-type-btn.active .mv-icon {
            color: var(--mv-active);
        }

        .mv-type-btn:hover .mv-icon {
            transform: translateY(-2px);
        }

        .mv-section {
            display: none;
        }

        .mv-section.active {
            display: block;
        }

        .mv-field {
            margin-bottom: 1rem;
        }

        .mv-field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        .mv-field select,
        .mv-field input,
        .mv-field textarea {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: var(--mv-input-bg, #fff);
            color: inherit;
        }

        .mv-field select:focus,
        .mv-field input:focus,
        .mv-field textarea:focus {
            outline: 2px solid #f59e0b;
            outline-offset: 1px;
        }

        .mv-field select:disabled,
        .mv-field input:disabled,
        .mv-field textarea:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .mv-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) auto;
            gap: 0.75rem;
            align-items: end;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .mv-row-remove {
            padding: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #dc2626;
            font-size: 1.25rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            margin: 0 auto;
        }

        .mv-row-remove:hover {
            background: rgba(220, 38, 38, 0.1);
        }

        /* Dynamic Tables */
        .mv-table-container {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: #fff;
            margin-bottom: 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .mv-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            table-layout: fixed;
        }

        .mv-table th {
            background: #f9fafb;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .mv-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .mv-table tr:last-child td {
            border-bottom: none;
        }

        .mv-table .mv-field {
            margin-bottom: 0 !important;
        }

        .mv-table select,
        .mv-table input {
            padding: 0.4rem 0.6rem;
            font-size: 0.8125rem;
            border-color: #e5e7eb;
            background: #fcfcfc;
        }

        .mv-table select:focus,
        .mv-table input:focus {
            background: #fff;
        }

        .mv-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px dashed #d1d5db;
            border-radius: 0.5rem;
            background: transparent;
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s;
        }

        .mv-add-btn:hover {
            border-color: #9ca3af;
            color: #374151;
            background: #f9fafb;
            transform: translateY(-1px);
        }

        .mv-add-btn span {
            font-size: 1.125rem;
            font-weight: 400;
        }

        .mv-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .mv-btn {
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background 0.15s;
            text-align: center;
        }

        .mv-btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .mv-btn-cancel:hover {
            background: #e5e7eb;
        }

        .mv-btn-submit {
            background: #f59e0b;
            color: #fff;
        }

        .mv-btn-submit:hover {
            background: #d97706;
        }

        .mv-btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .mv-stock-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .mv-notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            z-index: 50;
            animation: mvSlideIn 0.3s ease;
        }

        .mv-notification.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .mv-notification.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes mvSlideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .mv-stock-display {
            font-weight: 600;
            color: #2563eb;
            font-family: monospace;
            font-size: 1rem;
        }

        :is(.dark .mv-stock-display) {
            color: #60a5fa;
        }

        .mv-loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .mv-type-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .mv-row {
                grid-template-columns: 1fr;
                position: relative;
                padding-top: 2rem;
            }

            .mv-row-remove {
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
            }

            .mv-actions {
                flex-direction: column-reverse;
            }

            .mv-btn {
                width: 100%;
            }
        }

        /* Dark mode */
        .dark .mv-type-btn {
            --mv-border: #374151;
            --mv-hover: #4b5563;
        }

        .dark .mv-field select,
        .dark .mv-field input,
        .dark .mv-field textarea {
            background: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }

        .dark .mv-row {
            border-color: #374151;
        }

        .dark .mv-table-container {
            border-color: #374151;
            background: #111827;
            box-shadow: none;
        }

        .dark .mv-table th {
            background: #1f2937;
            border-color: #374151;
            color: #9ca3af;
        }

        .dark .mv-table td {
            border-color: #374151;
        }

        .dark .mv-table select,
        .dark .mv-table input {
            background: #111827;
            border-color: #374151;
            color: #f9fafb;
        }

        .dark .mv-add-btn {
            border-color: #4b5563;
            color: #9ca3af;
        }

        .dark .mv-add-btn:hover {
            background: #1f2937;
            color: #d1d5db;
            border-color: #6b7280;
        }

        .dark .mv-actions {
            border-color: #374151;
        }

        .dark .mv-btn-cancel {
            background: #374151;
            color: #d1d5db;
        }

        .dark .mv-btn-cancel:hover {
            background: #4b5563;
        }
    </style>

    <div class="mv-form" id="movementForm" data-mode="{{ $mode }}" data-csrf="{{ csrf_token() }}"
        data-list-url="{{ $listUrl }}" data-api-base="{{ url('/internal/inventory') }}"
        data-api-movements="{{ url('/internal/movements') }}"
        @if ($mode === 'view' && $movement) data-movement="{{ json_encode([
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
        ]) }}" @endif>
        {{-- Selector de tipo --}}
        <div class="mv-type-grid">
            <button type="button" class="mv-type-btn" data-type="transfer"
                @if ($mode === 'view') disabled @endif>
                <div class="mv-icon">
                    <x-heroicon-o-arrows-right-left class="w-6 h-6" />
                </div>
                <span>Intercambio</span>
            </button>
            <button type="button" class="mv-type-btn" data-type="adjustment"
                @if ($mode === 'view') disabled @endif>
                <div class="mv-icon">
                    <x-heroicon-o-document-check class="w-6 h-6" />
                </div>
                <span>Ajuste</span>
            </button>
        </div>

        {{-- Formularios específicos por tipo --}}
        @include('filament.resources.movements.partials.input-form')
        @include('filament.resources.movements.partials.output-form')
        @include('filament.resources.movements.partials.transfer-form')
        @include('filament.resources.movements.partials.adjustment-form')

        {{-- ============================================================ --}}
        {{-- OBSERVACIONES (común a todos los tipos)                      --}}
        {{-- ============================================================ --}}
        <div class="mv-field" id="notes-section" style="display:none; margin-top:1rem;">
            <label for="movement-notes">Observaciones</label>
            <textarea id="movement-notes" rows="3" placeholder="Ej. Compra factura #001, ajuste por daño, etc."
                @if ($mode === 'view') disabled @endif>{{ $mode === 'view' && $movement ? $movement->notes : '' }}</textarea>
        </div>

        {{-- ============================================================ --}}
        {{-- INFORMACIÓN DE REGISTRO (solo modo view)                     --}}
        {{-- ============================================================ --}}
        @if ($mode === 'view' && $movement)
            <div
                style="margin-top:1rem; padding:1rem; background:rgba(0,0,0,0.03); border-radius:0.5rem; font-size:0.8125rem;">
                <p><strong>Registrado por:</strong> {{ $movement->user->name ?? 'N/A' }}</p>
                <p><strong>Fecha:</strong> {{ $movement->created_at?->format('d/m/Y H:i') }}</p>
                <p><strong>ID:</strong> #{{ $movement->id }}</p>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- ACCIONES                                                     --}}
        {{-- ============================================================ --}}
        @if ($mode === 'create')
            <div class="mv-actions">
                <a href="{{ $listUrl }}" class="mv-btn mv-btn-cancel">Cancelar</a>
                <button type="button" class="mv-btn mv-btn-submit" id="submit-movement" disabled>
                    Registrar Movimiento
                </button>
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('js/movements/core.js') }}"></script>
        <script src="{{ asset('js/movements/input.js') }}"></script>
        <script src="{{ asset('js/movements/output.js') }}"></script>
        <script src="{{ asset('js/movements/transfer.js') }}"></script>
        <script src="{{ asset('js/movements/adjustment.js') }}"></script>
        <script src="{{ asset('js/movement-form.js') }}"></script>
    @endpush

    {{-- Modal de validación dinámica --}}
    <x-filament::modal id="mv-validation-modal" width="md" icon="heroicon-o-exclamation-triangle"
        icon-color="danger">
        {{-- Slot: Encabezado --}}
        <x-slot name="heading">
            <span id="mv-modal-heading">
                Validación de Movimiento
            </span>
        </x-slot>

        {{-- Contenido: Sin definiciones de color de texto o fondo personalizado --}}
        <div class="py-2">
            <div id="mv-modal-content"
                class="text-sm leading-relaxed p-4 rounded-xl border border-gray-200 dark:border-white/10">
                {{-- Mensaje dinámico --}}
                Cargando detalles del error...
            </div>
        </div>

        {{-- Footer: Solo el botón define color --}}
        <x-slot name="footerActions">
            <x-filament::button color="danger" tag="button" x-on:click="close" class="w-full sm:w-auto">
                Entendido
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
