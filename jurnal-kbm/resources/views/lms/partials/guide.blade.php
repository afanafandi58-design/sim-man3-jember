@php
    $guidePage = $page ?? null;
    $authUser = auth()->user();
    if (! $guidePage || ! $authUser) {
        return;
    }
    $cfg = config("lms_guides.pages.{$guidePage}");
    if (! is_array($cfg)) {
        return;
    }
    $role = $authUser->role ?? '';
    $steps = array_merge(
        $cfg['steps']['common'] ?? [],
        $cfg['steps'][$role] ?? []
    );
    $steps = array_values(array_filter($steps, fn ($s) => is_string($s) && trim($s) !== ''));
@endphp
@if (count($steps) > 0)
    <div class="card card-outline card-info mb-3 lms-guide-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-book-reader mr-1"></i>{{ $cfg['title'] ?? 'Tata cara pengisian' }}
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Tutup/buka">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if (! empty($cfg['intro']))
                <p class="text-muted mb-2">{{ $cfg['intro'] }}</p>
            @endif
            <ol class="mb-0 pl-3">
                @foreach ($steps as $step)
                    <li class="mb-1">{{ $step }}</li>
                @endforeach
            </ol>
        </div>
    </div>
@endif
