@include('partials.errors')

<div class="form-grid">
    <label>
        Contact name
        <input type="text" name="name" value="{{ old('name', $client->name) }}" required>
    </label>
    <label>
        Company
        <input type="text" name="company" value="{{ old('company', $client->company) }}">
    </label>
    <label>
        Email
        <input type="email" name="email" value="{{ old('email', $client->email) }}">
    </label>
    <label>
        Phone
        <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}">
    </label>
    <label class="span-2">
        Address
        <input type="text" name="address" value="{{ old('address', $client->address) }}">
    </label>
    <label>
        Payment terms (days)
        <input type="number" name="payment_terms_days" min="0" max="120" value="{{ old('payment_terms_days', $client->payment_terms_days) }}" required>
    </label>
</div>
