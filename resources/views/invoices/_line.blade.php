<tr class="line">
    <td>
        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
        <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] }}" placeholder="What did you do?" aria-label="Description" required>
    </td>
    <td><input type="number" class="qty" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" step="0.01" min="0.01" aria-label="Quantity" required></td>
    <td><input type="number" class="price" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" step="0.01" min="0" aria-label="Unit price" required></td>
    <td><input type="number" class="discount" name="items[{{ $index }}][discount_percent]" value="{{ $item['discount_percent'] ?? 0 }}" step="0.01" min="0" max="100" aria-label="Discount percent"></td>
    <td class="num line-total">₦0.00</td>
    <td><button type="button" class="link remove-line" aria-label="Remove line">Remove</button></td>
</tr>
