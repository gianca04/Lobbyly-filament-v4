{{-- Partial for the Item OUTPUT form --}}
<div class="mv-section" id="section-output">
    <div class="mv-table-container">
        <table class="mv-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Location</th>
                    <th style="width: 120px; text-align: center;">Current Stock</th>
                    <th style="width: 120px;">Quantity</th>
                    <th style="width: 80px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="mv-field">
                            <select id="output-item" @if($mode === 'view') disabled @endif>
                                <option value="">Select item...</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="mv-field">
                            <select id="output-location" @if($mode === 'view') disabled @endif>
                                <option value="">Select item first...</option>
                            </select>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span id="output-current-stock" class="mv-stock-display">—</span>
                    </td>
                    <td>
                        <div class="mv-field">
                            <input type="number" id="output-quantity" min="0.01" step="0.01" value="1" @if($mode === 'view') disabled @endif>
                        </div>
                    </td>
                    <td style="text-align: center; width: 80px;">
                        {{-- No actions for single output row --}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
