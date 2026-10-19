@props(['weather' => null])

@if($weather)
    <div {{ $attributes->class(['card', 'h-100', 'mb-0']) }}>
        <div class="card-body py-3">

            {{-- Location --}}
            @if(!empty($weather['location']))
                <div class="small text-muted mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $weather['location'] }}
                </div>
            @endif

            <div class="row align-items-center g-3">

                {{-- Current weather --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi {{ $weather['icon'] }} text-warning" style="font-size: 2.5rem;"></i>
                        <div>
                            <div class="fs-2 fw-bold lh-1">{{ $weather['temp'] }}°C</div>
                            <div class="text-muted small">{{ $weather['label'] }}</div>
                        </div>
                        <div class="small text-muted border-start ps-3 d-none d-xl-block">
                            <div><i class="bi bi-thermometer-half me-1"></i>Feels {{ $weather['feels_like'] }}°C</div>
                            <div><i class="bi bi-droplet me-1"></i>{{ $weather['humidity'] }}%</div>
                            <div><i class="bi bi-wind me-1"></i>{{ $weather['wind'] }} km/h</div>
                        </div>
                    </div>
                </div>

                {{-- Forecast --}}
                <div class="col-md-8">
                    <div class="d-flex justify-content-between text-center">
                        @foreach($weather['forecast'] as $day)
                            <div class="px-1">
                                <div class="small fw-semibold">{{ $day['day'] }}</div>
                                <i class="bi {{ $day['icon'] }} fs-5" title="{{ $day['label'] }}"></i>
                                <div class="small">
                                    {{ $day['max'] }}°
                                    <span class="text-muted">{{ $day['min'] }}°</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif
