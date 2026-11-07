@if ($operationalDate['showWatermark'] ?? false)
<div aria-hidden="true" class="uat-simulation-watermark">UAT SIMULATION</div>
<style>
    .uat-simulation-watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 3rem;
        font-weight: 700;
        color: rgba(255, 193, 7, 0.12);
        pointer-events: none;
        z-index: 1030;
        white-space: nowrap;
        user-select: none;
    }
</style>
@endif
