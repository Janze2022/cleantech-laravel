@extends('customer.layouts.app')

@section('title', 'Book a Service')

@section('content')

<style>
:root {
    --bg-main:#020617;
    --bg-card:#020b1f;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --danger:#ef4444;
}

.booking-page { padding:2rem 1rem; }

.booking-container {
    max-width:1100px;
    margin:auto;
    display:grid;
    grid-template-columns:340px 1fr;
    gap:2rem;
}

.booking-summary {
    position:sticky;
    top:96px;
    background:linear-gradient(180deg,#020b1f,#020617);
    border:1px solid var(--border-soft);
    border-radius:16px;
    padding:1.5rem;
}

.summary-row {
    display:flex;
    justify-content:space-between;
    color:var(--text-muted);
    margin-bottom:.5rem;
    gap:1rem;
}

.summary-row span:last-child{
    text-align:right;
}

.summary-total {
    margin-top:1rem;
    padding-top:1rem;
    border-top:1px solid var(--border-soft);
    text-align:center;
}

.summary-total span {
    display:block;
    font-size:1.8rem;
    color:var(--accent);
    font-weight:800;
    margin-top:.35rem;
}

.booking-card {
    background:linear-gradient(180deg,#020b1f,#020617);
    border-radius:16px;
    border:1px solid var(--border-soft);
    padding:2rem;
}

.form-control {
    background:#020617;
    border:1px solid var(--border-soft);
    color:#fff;
}

.form-control:focus {
    background:#020617;
    color:#fff;
    border-color:rgba(56,189,248,.55);
    box-shadow:0 0 0 .2rem rgba(56,189,248,.12);
}

.form-control option {
    background:#020617;
    color:#fff;
}

.btn-primary {
    background:linear-gradient(180deg,#0ea5e9,#38bdf8);
    border:none;
    font-weight:700;
}

.provider-preview {
    display:flex;
    gap:1rem;
    align-items:center;
    padding:1rem;
    border:1px solid var(--border-soft);
    border-radius:12px;
    margin-bottom:1rem;
}

.provider-preview img {
    width:56px;
    height:56px;
    border-radius:50%;
    object-fit:cover;
}

.alert{
    border-radius:12px;
    padding:1rem;
    margin-bottom:1rem;
}
.alert-danger{
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.25);
    color:#fecaca;
}
.alert-success{
    background:rgba(34,197,94,.10);
    border:1px solid rgba(34,197,94,.25);
    color:#bbf7d0;
}
.alert ul{ margin:0; padding-left:1.25rem; }

.text-muted {
    color: var(--text-muted) !important;
}

.inline-error{
    display:none;
    margin-top:.5rem;
    color:#fca5a5;
    font-size:.9rem;
}

.time-help{
    margin-top:.5rem;
    font-size:.9rem;
    color:var(--text-muted);
}

.option-check-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:.65rem;
    margin-top:.75rem;
}

.option-check-item{
    border:1px solid var(--border-soft);
    border-radius:12px;
    padding:.75rem .85rem;
    background:#020617;
}

.option-check-item label{
    display:flex;
    align-items:flex-start;
    gap:.6rem;
    cursor:pointer;
    margin:0;
    width:100%;
}

.option-check-item input[type="checkbox"]{
    margin-top:.2rem;
    transform:scale(1.1);
}

.option-check-main{
    display:flex;
    flex-direction:column;
    gap:.15rem;
}

.option-check-name{
    color:#fff;
    font-weight:600;
    line-height:1.2;
}

.option-check-price{
    color:var(--text-muted);
    font-size:.85rem;
}

.multi-help{
    display:block;
    margin-top:.5rem;
    font-size:.85rem;
    color:var(--text-muted);
}

.hidden{
    display:none !important;
}

@media (max-width: 991px){
    .booking-container{
        grid-template-columns:1fr;
    }

    .booking-summary{
        position:static;
        order:2;
    }

    .booking-card{
        order:1;
    }
}

@media (max-width: 768px){
    .option-check-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="booking-page">
    <div class="booking-container">

        {{-- SUMMARY --}}
        <aside class="booking-summary">
            <h6>Booking Summary</h6>

            <div class="summary-row">
                <span>Service</span>
                <span id="summaryService">—</span>
            </div>

            <div class="summary-row">
                <span id="summaryOptionLabel">Option</span>
                <span id="summaryOption">—</span>
            </div>

            <div class="summary-row">
                <span>Schedule</span>
                <span id="summarySchedule">—</span>
            </div>

            <div class="summary-total">
                Total
                <span id="totalPrice">₱0.00</span>
            </div>
        </aside>

        {{-- FORM --}}
        <div class="booking-card">
            <h4>Book {{ $providerData->first_name }} {{ $providerData->last_name }}</h4>
            <p class="text-muted">{{ $providerData->city }}, {{ $providerData->province }}</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Fix these errors:</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="providerPreview" class="provider-preview" style="display:none">
                <img id="providerAvatar" src="" alt="Provider avatar" onerror="this.style.display='none'">
                <div>
                    <div id="providerName" class="fw-semibold"></div>
                    <div class="text-muted">Available Provider</div>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.book.submit') }}" id="bookingForm">
                @csrf
                <input type="hidden" name="provider_id" value="{{ $providerData->id }}">

                {{-- SERVICE --}}
                <div class="mb-3">
                    <label>Service</label>
                    <select id="service" name="service_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($services as $s)
                            <option
                                value="{{ $s->id }}"
                                data-price="{{ $s->base_price }}"
                                {{ old('service_id') == $s->id ? 'selected' : '' }}
                            >
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SINGLE OPTION --}}
                <div class="mb-3" id="singleOptionWrap">
                    <label id="optionLabel">Option</label>
                    <select id="optionSingle" name="service_option_id" class="form-control" disabled>
                        <option value="">Select service first</option>
                    </select>
                </div>

                {{-- MULTI OPTION FOR SPECIFIC AREA CLEANING --}}
                <div class="mb-3 hidden" id="multiOptionWrap">
                    <label id="multiOptionLabel">Specific Areas to Clean</label>
                    <div id="optionMultiList" class="option-check-grid"></div>
                    <small class="multi-help">You can choose multiple areas.</small>
                </div>

                {{-- PHONE --}}
                <div class="mb-3">
                    <label>Contact Phone</label>
                    <input
                        type="tel"
                        name="phone"
                        class="form-control"
                        maxlength="11"
                        placeholder="09XXXXXXXXX"
                        required
                        oninput="phPhone(this)"
                        value="{{ old('phone') }}"
                    >
                </div>

                {{-- BARANGAY --}}
                <div class="mb-3">
                    <label>Barangay (Butuan City)</label>
                    <select id="barangay" class="form-control"></select>

                    <input type="hidden" name="region" value="Region XIII">
                    <input type="hidden" name="province" value="Agusan del Norte">
                    <input type="hidden" name="city" value="Butuan City">
                    <input type="hidden" name="barangay" id="barangay_text" value="{{ old('barangay') }}">
                </div>

                <div class="mb-3">
                    <label>House No / Street</label>
                    <textarea name="address" class="form-control" required>{{ old('address') }}</textarea>
                </div>

                {{-- AVAILABILITY --}}
                <div class="mb-4">
                    <label>Available Schedule</label>

                    <select id="slot" name="slot_id" class="form-control mb-2" required>
                        <option value="">Select available time</option>
                        @foreach($availability as $a)
                            <option
                                value="{{ $a->id }}"
                                data-date="{{ $a->date }}"
                                data-start="{{ $a->time_start }}"
                                data-end="{{ $a->time_end }}"
                                data-name="{{ $a->first_name }} {{ $a->last_name }}"
                                data-avatar="{{ !empty($a->profile_image) ? route('provider.image.public', ['filename' => $a->profile_image]) : '' }}"
                                {{ old('slot_id') == $a->id ? 'selected' : '' }}
                            >
                                {{ \Carbon\Carbon::parse($a->date)->format('Y-m-d') }}
                                |
                                {{ \Carbon\Carbon::parse($a->time_start)->format('h:i A') }}
                                –
                                {{ \Carbon\Carbon::parse($a->time_end)->format('h:i A') }}
                                ({{ $a->first_name }})
                            </option>
                        @endforeach
                    </select>

                    <div class="mb-2">
                        <small class="text-muted" id="slotPreviewText">Choose a provider availability first.</small>
                    </div>

                    <label class="mt-2">Preferred Start Time</label>
                    <input
                        type="time"
                        id="preferred_start_time"
                        name="preferred_start_time"
                        class="form-control"
                        required
                        disabled
                        step="1"
                        inputmode="numeric"
                        value="{{ old('preferred_start_time') ? substr(old('preferred_start_time'), 0, 5) : '' }}"
                    >

                    <div id="preferredStartError" class="inline-error"></div>

                    <small class="time-help d-block" id="preferredTimeHelp">
                        You can manually choose any time within the provider's available schedule.
                    </small>
                </div>

                <button class="btn btn-primary w-100">Confirm Booking</button>
            </form>
        </div>
    </div>
</div>

<script>
const OPTIONS_BY_SERVICE = @json($optionsByService);
const OLD_SERVICE_ID = @json(old('service_id'));
const OLD_OPTION_ID  = @json(old('service_option_id'));
const OLD_MULTI_OPTION_IDS = @json(old('service_option_ids', []));
const OLD_PREFERRED_START = @json(old('preferred_start_time') ? substr(old('preferred_start_time'), 0, 5) : '');

const SPECIFIC_AREA_SERVICE_ID = @json($specificAreaServiceId);

function phPhone(i){
    let v = i.value.replace(/\D/g,'');
    if(v.length >= 2 && !v.startsWith('09')) v = '09';
    i.value = v.slice(0,11);
}

const bookingForm = document.getElementById('bookingForm');

const serviceEl = document.getElementById('service');
const optionSingleEl = document.getElementById('optionSingle');
const singleOptionWrapEl = document.getElementById('singleOptionWrap');
const multiOptionWrapEl = document.getElementById('multiOptionWrap');
const optionMultiListEl = document.getElementById('optionMultiList');

const optionLabelEl = document.getElementById('optionLabel');
const summaryOptionLabelEl = document.getElementById('summaryOptionLabel');

const summaryServiceEl = document.getElementById('summaryService');
const summaryOptionEl  = document.getElementById('summaryOption');
const summaryScheduleEl = document.getElementById('summarySchedule');
const totalPriceEl     = document.getElementById('totalPrice');

const slotEl = document.getElementById('slot');
const preferredStartEl = document.getElementById('preferred_start_time');
const slotPreviewTextEl = document.getElementById('slotPreviewText');
const preferredStartErrorEl = document.getElementById('preferredStartError');
const preferredTimeHelpEl = document.getElementById('preferredTimeHelp');

const providerPreviewEl = document.getElementById('providerPreview');
const providerNameEl = document.getElementById('providerName');
const providerAvatarEl = document.getElementById('providerAvatar');

function peso(n){
    return '₱' + Number(n || 0).toFixed(2);
}

function normalizeTimeToHHMM(timeStr){
    if(!timeStr) return '';
    return String(timeStr).trim().slice(0, 5);
}

function timeToMinutes(timeStr){
    if(!timeStr || !/^\d{2}:\d{2}$/.test(timeStr)) return null;
    const [h, m] = timeStr.split(':').map(Number);
    return (h * 60) + m;
}

function to12Hour(timeStr){
    if(!timeStr) return '';

    const clean = timeStr.length === 5 ? timeStr + ':00' : timeStr;
    const parts = clean.split(':');

    let h = parseInt(parts[0] || '0', 10);
    const m = parts[1] || '00';
    const ampm = h >= 12 ? 'PM' : 'AM';

    h = h % 12;
    if(h === 0) h = 12;

    return `${String(h).padStart(2,'0')}:${m} ${ampm}`;
}

function getServiceOptions(serviceId){
    return OPTIONS_BY_SERVICE[serviceId] || [];
}

function getCheckedMultiOptions(){
    return Array.from(document.querySelectorAll('input[name="service_option_ids[]"]:checked'));
}

function rebuildOptions(serviceId){
    const options = getServiceOptions(serviceId);

    optionSingleEl.innerHTML = '';
    optionMultiListEl.innerHTML = '';

    if(!serviceId || !options.length){
        singleOptionWrapEl.classList.remove('hidden');
        multiOptionWrapEl.classList.add('hidden');

        optionSingleEl.disabled = true;
        optionSingleEl.required = false;
        optionSingleEl.innerHTML = `<option value="">Select service first</option>`;

        optionLabelEl.textContent = 'Option';
        summaryOptionLabelEl.textContent = 'Option';
        summaryOptionEl.textContent = '—';
        updatePrice();
        return;
    }

    if(Number(serviceId) === SPECIFIC_AREA_SERVICE_ID){
        singleOptionWrapEl.classList.add('hidden');
        multiOptionWrapEl.classList.remove('hidden');

        optionSingleEl.disabled = true;
        optionSingleEl.required = false;
        optionSingleEl.innerHTML = `<option value="">Not used</option>`;

        summaryOptionLabelEl.textContent = 'Areas';

        options.forEach(o => {
            const wrapper = document.createElement('div');
            wrapper.className = 'option-check-item';

            const checked = OLD_MULTI_OPTION_IDS.map(String).includes(String(o.id)) ? 'checked' : '';

            wrapper.innerHTML = `
                <label>
                    <input type="checkbox" name="service_option_ids[]" value="${o.id}" data-price="${o.price_addition}" ${checked}>
                    <div class="option-check-main">
                        <div class="option-check-name">${o.label}</div>
                        <div class="option-check-price">+ ${peso(o.price_addition)}</div>
                    </div>
                </label>
            `;

            optionMultiListEl.appendChild(wrapper);
        });

        document.querySelectorAll('input[name="service_option_ids[]"]').forEach(cb => {
            cb.addEventListener('change', updatePrice);
        });
    } else {
        singleOptionWrapEl.classList.remove('hidden');
        multiOptionWrapEl.classList.add('hidden');

        optionSingleEl.disabled = false;
        optionSingleEl.required = true;

        if(Number(serviceId) === 2){
            optionLabelEl.textContent = 'House Type';
            summaryOptionLabelEl.textContent = 'House Type';
        } else {
            optionLabelEl.textContent = 'Option';
            summaryOptionLabelEl.textContent = 'Option';
        }

        optionSingleEl.innerHTML = `<option value="">Select</option>`;

        options.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.id;
            opt.textContent = o.label;
            opt.dataset.price = o.price_addition;

            if(String(OLD_OPTION_ID) === String(o.id)){
                opt.selected = true;
            }

            optionSingleEl.appendChild(opt);
        });
    }

    updatePrice();
}

function updatePrice(){
    const sOpt = serviceEl.selectedOptions[0];
    const base = parseFloat(sOpt?.dataset.price || 0);

    summaryServiceEl.textContent = sOpt?.value ? sOpt.text : '—';

    if(Number(serviceEl.value) === SPECIFIC_AREA_SERVICE_ID){
        const checked = getCheckedMultiOptions();
        const add = checked.reduce((sum, cb) => sum + parseFloat(cb.dataset.price || 0), 0);

        if(checked.length){
            const names = checked.map(cb => cb.closest('label').querySelector('.option-check-name')?.textContent || '').filter(Boolean);
            summaryOptionEl.textContent = names.join(', ');
        } else {
            summaryOptionEl.textContent = '—';
        }

        totalPriceEl.textContent = peso(base + add);
    } else {
        const oOpt = optionSingleEl.selectedOptions[0];
        const add  = parseFloat(oOpt?.dataset.price || 0);

        summaryOptionEl.textContent  = oOpt?.value ? oOpt.text : '—';
        totalPriceEl.textContent     = peso(base + add);
    }
}

function setTimeError(message){
    if(message){
        preferredStartErrorEl.style.display = 'block';
        preferredStartErrorEl.textContent = message;
        preferredStartEl.setCustomValidity(message);
    } else {
        preferredStartErrorEl.style.display = 'none';
        preferredStartErrorEl.textContent = '';
        preferredStartEl.setCustomValidity('');
    }
}

function validatePreferredTime(){
    const slotOpt = slotEl.selectedOptions[0];

    if(!slotOpt || !slotOpt.value){
        setTimeError('');
        return true;
    }

    const start = normalizeTimeToHHMM(slotOpt.dataset.start || '');
    const end   = normalizeTimeToHHMM(slotOpt.dataset.end || '');
    const chosen = normalizeTimeToHHMM(preferredStartEl.value || '');

    if(!chosen){
        setTimeError('Please choose a preferred start time.');
        return false;
    }

    const startMin = timeToMinutes(start);
    const endMin = timeToMinutes(end);
    const chosenMin = timeToMinutes(chosen);

    if(startMin === null || endMin === null || chosenMin === null){
        setTimeError('Invalid time format.');
        return false;
    }

    if(chosenMin < startMin || chosenMin >= endMin){
        setTimeError(`Preferred start time must be between ${to12Hour(start)} and before ${to12Hour(end)}.`);
        return false;
    }

    setTimeError('');
    return true;
}

function rebuildPreferredStartInput(slotOption){
    if(!slotOption || !slotOption.value){
        preferredStartEl.disabled = true;
        preferredStartEl.value = '';
        preferredStartEl.removeAttribute('min');
        preferredStartEl.removeAttribute('max');
        preferredStartEl.removeAttribute('placeholder');
        slotPreviewTextEl.textContent = 'Choose a provider availability first.';
        preferredTimeHelpEl.textContent = "You can manually choose any time within the provider's available schedule.";
        summaryScheduleEl.textContent = '—';
        setTimeError('');
        return;
    }

    const date  = slotOption.dataset.date || '';
    const start = normalizeTimeToHHMM(slotOption.dataset.start || '');
    const end   = normalizeTimeToHHMM(slotOption.dataset.end || '');

    preferredStartEl.disabled = false;
    preferredStartEl.min = start;
    preferredStartEl.max = '23:59';

    if(OLD_PREFERRED_START){
        preferredStartEl.value = OLD_PREFERRED_START;
    } else if(!preferredStartEl.value) {
        preferredStartEl.value = start;
    }

    slotPreviewTextEl.textContent =
        `Available on ${date} from ${to12Hour(start)} to ${to12Hour(end)}`;

    preferredTimeHelpEl.textContent =
        `You may enter any start time from ${to12Hour(start)} up to before ${to12Hour(end)}.`;

    validatePreferredTime();
    updateScheduleSummary();
}

function updateScheduleSummary(){
    const slotOpt = slotEl.selectedOptions[0];

    if(!slotOpt || !slotOpt.value){
        summaryScheduleEl.textContent = '—';
        return;
    }

    const date = slotOpt.dataset.date || '';
    const start = normalizeTimeToHHMM(slotOpt.dataset.start || '');
    const end = normalizeTimeToHHMM(slotOpt.dataset.end || '');
    const preferred = normalizeTimeToHHMM(preferredStartEl.value || '');

    if(preferred){
        summaryScheduleEl.textContent =
            `${date} | Preferred: ${to12Hour(preferred)} | Available: ${to12Hour(start)} – ${to12Hour(end)}`;
    } else {
        summaryScheduleEl.textContent =
            `${date} | Available: ${to12Hour(start)} – ${to12Hour(end)}`;
    }
}

serviceEl.addEventListener('change', () => {
    rebuildOptions(serviceEl.value);
    updatePrice();
});

optionSingleEl.addEventListener('change', updatePrice);

slotEl.addEventListener('change', function(){
    const opt = this.selectedOptions[0];

    if(!opt || !opt.value){
        rebuildPreferredStartInput(null);
        providerPreviewEl.style.display = 'none';
        providerAvatarEl.src = '';
        providerNameEl.textContent = '';
        updateScheduleSummary();
        return;
    }

    rebuildPreferredStartInput(opt);

    providerPreviewEl.style.display = 'flex';
    providerNameEl.textContent = opt.dataset.name || '';

    const avatar = opt.dataset.avatar || '';
    if(avatar){
        providerAvatarEl.style.display = 'block';
        providerAvatarEl.src = avatar;
    } else {
        providerAvatarEl.style.display = 'none';
        providerAvatarEl.src = '';
    }

    updateScheduleSummary();
});

preferredStartEl.addEventListener('input', () => {
    validatePreferredTime();
    updateScheduleSummary();
});

preferredStartEl.addEventListener('change', () => {
    validatePreferredTime();
    updateScheduleSummary();
});

bookingForm.addEventListener('submit', function(e){
    if(Number(serviceEl.value) === SPECIFIC_AREA_SERVICE_ID){
        const checked = getCheckedMultiOptions();
        if(!checked.length){
            e.preventDefault();
            alert('Please select at least one area to clean.');
            return;
        }
    } else {
        if(!optionSingleEl.value){
            e.preventDefault();
            alert('Please select an option.');
            return;
        }
    }

    if(!validatePreferredTime()){
        e.preventDefault();
        preferredStartEl.focus();
    }
});

window.addEventListener('DOMContentLoaded', () => {
    if(OLD_SERVICE_ID){
        rebuildOptions(String(OLD_SERVICE_ID));
    } else {
        updatePrice();
    }

    if(slotEl.value){
        const selectedSlot = slotEl.selectedOptions[0];
        rebuildPreferredStartInput(selectedSlot);

        if(OLD_PREFERRED_START){
            preferredStartEl.value = OLD_PREFERRED_START;
        }

        slotEl.dispatchEvent(new Event('change'));
    } else {
        updateScheduleSummary();
    }
});

/* PSGC BARANGAYS (BUTUAN ONLY) */
fetch('https://psgc.gitlab.io/api/cities-municipalities/160202000/barangays/')
.then(r => r.json())
.then(d => {
    const barangayEl = document.getElementById('barangay');
    const barangayTextEl = document.getElementById('barangay_text');

    barangayEl.innerHTML = '<option value="">Select barangay</option>';
    d.forEach(b => {
        barangayEl.innerHTML += `<option value="${b.code}">${b.name}</option>`;
    });

    const oldBarangayText = barangayTextEl.value;
    if(oldBarangayText){
        [...barangayEl.options].forEach(opt => {
            if(opt.text === oldBarangayText) opt.selected = true;
        });
    }

    barangayEl.addEventListener('change', () => {
        barangayTextEl.value = barangayEl.selectedOptions[0]?.text || '';
    });
})
.catch(() => {
    const barangayEl = document.getElementById('barangay');
    const barangayTextEl = document.getElementById('barangay_text');
    if (barangayEl) {
        barangayEl.innerHTML = '<option value="">Unable to load barangays, you can continue without it</option>';
    }
    if (barangayTextEl && !barangayTextEl.value) {
        barangayTextEl.value = '';
    }
});
</script>

@endsection
