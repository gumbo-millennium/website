<div class="col-sm-6 col-md-4 col-lg-3">
    <div class="number-card number-card--{{ $color ?? 'brand' }}-outline">
        <div class="number-card__number">
            {{ $number }}
            @if (isset($change) && $change > 0)
            <span class="number-card__indicator number-card__indicator--positive">{{ sprintf('%.0f%%', $change) }}</span>
            @elseif (isset($change) && $change < 0)
            <span class="number-card__indicator number-card__indicator--negative">{{ sprintf('%.0f%%', $change) }}</span>
            @endif
        </div>
        <p class="number-card__description">{{ $label }}</p>
    </div>
</div>
