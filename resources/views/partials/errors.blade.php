@if ($errors->any())
    <div class="flash flash-error" role="alert">
        <strong>Please fix the following:</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
