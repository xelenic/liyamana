@php
    $types = $envelopeTypes ?? collect();
@endphp
@forelse($types as $env)
    <option value="{{ $env->slug }}" data-price="{{ $env->price_per_letter }}">{{ $env->name }}</option>
@empty
    <option value="">No envelopes in stock — none will be deducted</option>
@endforelse
