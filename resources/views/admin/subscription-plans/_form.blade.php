
@php
    $plan = $plan ?? null;
@endphp

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label">Plan Name</label>

        <input
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $plan?->name) }}"
            maxlength="100"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug</label>

        <input
            type="text"
            name="slug"
            class="form-control"
            value="{{ old('slug', $plan?->slug) }}"
            maxlength="120"
            pattern="[A-Za-z0-9_-]+"
            required
        >

        <small class="text-muted">
            Example: subscription-1
        </small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Price (₹)</label>

        <input
            type="number"
            name="price"
            class="form-control"
            value="{{ old('price', $plan?->price) }}"
            min="0.01"
            step="0.01"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Daily Income (₹)</label>

        <input
            type="number"
            name="daily_income"
            class="form-control"
            value="{{ old('daily_income', $plan?->daily_income) }}"
            min="0"
            step="0.01"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">Duration (Days)</label>

        <input
            type="number"
            name="duration_days"
            class="form-control"
            value="{{ old('duration_days', $plan?->duration_days) }}"
            min="1"
            step="1"
            required
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">VIP Plan?</label>

        <select name="is_vip" class="form-select" required>
            <option
                value="0"
                @selected((string) old('is_vip', (int) ($plan?->is_vip ?? false)) === '0')
            >
                No — Normal Plan
            </option>

            <option
                value="1"
                @selected((string) old('is_vip', (int) ($plan?->is_vip ?? false)) === '1')
            >
                Yes — VIP Plan
            </option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>

        <select name="is_active" class="form-select" required>
            <option
                value="1"
                @selected((string) old('is_active', (int) ($plan?->is_active ?? true)) === '1')
            >
                Active
            </option>

            <option
                value="0"
                @selected((string) old('is_active', (int) ($plan?->is_active ?? true)) === '0')
            >
                Inactive
            </option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Display Order</label>

        <input
            type="number"
            name="sort_order"
            class="form-control"
            value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
            min="0"
            required
        >
    </div>

</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        {{ $plan ? 'Update Plan' : 'Create Plan' }}
    </button>

    <a
        href="{{ route('admin.subscription-plans.index') }}"
        class="btn btn-outline-secondary"
    >
        Cancel
    </a>
</div>
