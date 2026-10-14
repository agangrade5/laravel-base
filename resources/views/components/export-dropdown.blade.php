@props([
    'route',                  // route name to export (required) e.g. 'activity-logs.export', 'admin.activity-logs.export', etc
    'params' => null,         // extra query params; null = current filters (page/format removed) )
    'permission' => null,     // optional permission, e.g. 'activity-logs.export'
    'label' => 'Export',
])

@php
    $formats = [
        'xlsx' => ['label' => 'Excel (.xlsx)', 'icon' => 'bi-file-earmark-excel text-success'],
        'csv'  => ['label' => 'CSV (.csv)',    'icon' => 'bi-filetype-csv text-primary'],
    ];

    // Use current filters (page/format removed)
    $params = $params ?? request()->except('page', 'format');
@endphp

@if(!$permission || auth()->user()?->can($permission))
    <div {{ $attributes->class(['btn-group']) }}>
        <button
            type="button"
            class="btn btn-outline-primary dropdown-toggle"
            data-bs-toggle="dropdown"
            data-bs-boundary="viewport"
            aria-expanded="false"
        >
            <i class="bi bi-download me-1"></i> {{ $label }}
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
            @foreach($formats as $format => $meta)
                <li>
                    <a
                        class="dropdown-item"
                        href="{{ route($route, array_merge($params, ['format' => $format])) }}"
                        data-no-loader
                    >
                        <i class="bi {{ $meta['icon'] }} me-2"></i>{{ $meta['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
