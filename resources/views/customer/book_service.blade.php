@extends('customer.layouts.app')

@section('title', 'Book a Service')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

<style>
:root {
    --bg-main:#020617;
    --bg-card:#020b1f;
    --border-soft:rgba(255,255,255,.08);
    --text-muted:rgba(255,255,255,.55);
    --accent:#38bdf8;
    --danger:#ef4444;
}

.booking-page { padding:1rem .75rem 1.5rem; }

.booking-container {
    width:min(1180px, 100%);
    margin:0;
    display:grid;
    grid-template-columns:280px minmax(0, 820px);
    gap:1rem;
    align-items:start;
}

.booking-summary {
    position:sticky;
    top:96px;
    background:linear-gradient(180deg,#020b1f,#020617);
    border:1px solid var(--border-soft);
    border-radius:16px;
    padding:1.15rem;
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
    font-size:1.55rem;
    color:var(--accent);
    font-weight:800;
    margin-top:.35rem;
}

.booking-card {
    background:linear-gradient(180deg,#020b1f,#020617);
    border-radius:16px;
    border:1px solid var(--border-soft);
    padding:1.35rem;
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
    padding:.85rem;
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

.location-shell{
    display:grid;
    gap:1rem;
}

.location-search-wrap{
    display:grid;
    gap:.65rem;
}

.location-results{
    display:grid;
    gap:.4rem;
    padding:.65rem;
    border-radius:14px;
    border:1px solid var(--border-soft);
    background:#020b1f;
    box-shadow:0 24px 50px rgba(0,0,0,.42);
    max-height:200px;
    overflow:auto;
}

.location-result-btn{
    width:100%;
    text-align:left;
    border:1px solid rgba(255,255,255,.06);
    border-radius:12px;
    padding:.7rem .8rem;
    background:rgba(255,255,255,.03);
    color:#fff;
}

.location-result-btn:hover{
    background:rgba(56,189,248,.10);
    border-color:rgba(56,189,248,.22);
}

.location-result-main{
    display:block;
    font-weight:700;
}

.location-result-sub{
    display:block;
    font-size:.82rem;
    color:var(--text-muted);
    margin-top:.2rem;
}

.location-map{
    width:100%;
    height:230px;
    border-radius:16px;
    overflow:hidden;
    border:1px solid var(--border-soft);
}

.location-top-grid{
    display:grid;
    grid-template-columns:minmax(0, 1.35fr) minmax(220px, .65fr);
    gap:.85rem;
    align-items:start;
}

.location-card{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    border-radius:16px;
    padding:.85rem;
}

.location-card-label{
    display:block;
    margin-bottom:.45rem;
    color:var(--text-muted);
    font-size:.72rem;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
}

.location-readonly{
    min-height:44px;
    display:flex;
    align-items:center;
    padding:.7rem .85rem;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.08);
    background:#020617;
    color:#fff;
    font-weight:700;
    line-height:1.45;
}

.location-readonly.is-empty{
    color:var(--text-muted);
}

.location-meta{
    display:grid;
    gap:.55rem;
}

.location-meta-card{
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    border-radius:14px;
    padding:.8rem .9rem;
}

.location-note{
    color:var(--text-muted);
    font-size:.82rem;
    line-height:1.45;
}

.location-status{
    min-height:1.2rem;
    font-size:.82rem;
    color:var(--text-muted);
}

.location-status.error{
    color:#fca5a5;
}

.location-preview-label{
    font-size:.72rem;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:rgba(56,189,248,.95);
    font-weight:800;
}

.location-preview-value{
    margin-top:.3rem;
    color:#fff;
    font-weight:700;
    word-break:break-word;
}

.location-coords{
    display:none;
}

.location-helper{
    margin-top:.3rem;
    color:var(--text-muted);
    font-size:.74rem;
    line-height:1.4;
}

.hidden{
    display:none !important;
}

@media (max-width: 991px){
    .booking-container{
        grid-template-columns:1fr;
        width:100%;
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

    .location-top-grid,
    .location-meta-grid{
        grid-template-columns:1fr;
    }

    .location-map{
        height:210px;
    }

    .location-card,
    .location-meta-card{
        padding:.75rem;
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
            <p class="text-muted mb-3">Available on {{ $selectedDateLabel ?? 'today' }}.</p>

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

                <div class="mb-3">
                    <label>Pin Service Location</label>
                    <div class="location-shell">
                        <div class="location-top-grid">
                            <div class="location-card">
                                <span class="location-card-label">Search place</span>
                                <div class="location-search-wrap">
                                    <input
                                        type="text"
                                        id="locationSearch"
                                        class="form-control"
                                        placeholder="Search place or barangay"
                                        autocomplete="off"
                                    >
                                    <div id="locationResults" class="location-results hidden"></div>
                                </div>
                            </div>

                            <div class="location-card">
                                <span class="location-card-label">Barangay</span>
                                <div id="barangayDisplay" class="location-readonly {{ old('barangay') ? '' : 'is-empty' }}">
                                    {{ old('barangay') ?: 'Will fill in after you pin the map.' }}
                                </div>
                                <div class="location-helper">
                                    Filled automatically from the map pin.
                                </div>
                            </div>
                        </div>

                        <div id="locationMap" class="location-map"></div>

                        <div class="location-meta">
                            <div class="location-note">
                                Search, tap the map, or drag the pin.
                            </div>

                            <div id="locationStatus" class="location-status">
                                Choose your service location.
                            </div>

                            <div class="location-meta-card">
                                <div class="location-preview-label">Pinned Place</div>
                                <div class="location-preview-value" id="locationPreviewText">No pinned location yet.</div>
                                <div class="location-coords" aria-hidden="true">
                                    <span>Lat: <strong id="latitudePreview">—</strong></span>
                                    <span>Lng: <strong id="longitudePreview">—</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="region" id="regionInput" value="Region XIII">
                    <input type="hidden" name="province" id="provinceInput" value="Agusan del Norte">
                    <input type="hidden" name="city" id="cityInput" value="Butuan City">
                    <input type="hidden" name="barangay" id="barangay_text" value="{{ old('barangay') }}">
                    <input type="hidden" name="customer_latitude" id="customerLatitude" value="{{ old('customer_latitude') }}">
                    <input type="hidden" name="customer_longitude" id="customerLongitude" value="{{ old('customer_longitude') }}">
                    <input type="hidden" name="formatted_address" id="formattedAddress" value="{{ old('formatted_address') }}">
                </div>

                <div class="mb-3">
                    <label>House No. / Landmark</label>
                    <textarea name="address" class="form-control" required placeholder="Example: near school, yellow gate">{{ old('address') }}</textarea>
                </div>

                {{-- AVAILABILITY --}}
                <div class="mb-4">
                    <label>Choose Time</label>

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

                    @if($availability->isEmpty())
                        <small class="text-muted d-block mb-2">No active time slots for {{ $selectedDateLabel ?? 'today' }}.</small>
                    @endif

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
const OLD_CUSTOMER_LATITUDE = @json(old('customer_latitude'));
const OLD_CUSTOMER_LONGITUDE = @json(old('customer_longitude'));
const OLD_FORMATTED_ADDRESS = @json(old('formatted_address'));

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
    const locationLatEl = document.getElementById('customerLatitude');
    const locationLngEl = document.getElementById('customerLongitude');

    if(!locationLatEl?.value || !locationLngEl?.value){
        e.preventDefault();
        alert('Please pin the service location on the map before confirming the booking.');
        document.getElementById('locationMap')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

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

</script>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
<script>
(() => {
    const searchEl = document.getElementById('locationSearch');
    const resultsEl = document.getElementById('locationResults');
    const mapEl = document.getElementById('locationMap');
    const statusEl = document.getElementById('locationStatus');
    const previewEl = document.getElementById('locationPreviewText');
    const latitudeEl = document.getElementById('customerLatitude');
    const longitudeEl = document.getElementById('customerLongitude');
    const formattedAddressEl = document.getElementById('formattedAddress');
    const latitudePreviewEl = document.getElementById('latitudePreview');
    const longitudePreviewEl = document.getElementById('longitudePreview');
    const cityInputEl = document.getElementById('cityInput');
    const provinceInputEl = document.getElementById('provinceInput');
    const barangayTextEl = document.getElementById('barangay_text');
    const barangayDisplayEl = document.getElementById('barangayDisplay');

    if (!searchEl || !resultsEl || !mapEl || !statusEl || !previewEl || !window.L) {
        return;
    }

    const autocompleteUrl = @json(route('customer.booking.location.autocomplete'));
    const reverseUrl = @json(route('customer.booking.location.reverse'));
    const defaultCenter = [8.9475, 125.5436];

    let map = null;
    let marker = null;
    let searchTimer = null;
    let activeSearchRequest = 0;
    let activeReverseRequest = 0;
    let barangayCatalog = [];

    function setStatus(message, isError = false) {
        statusEl.textContent = message;
        statusEl.classList.toggle('error', isError);
    }

    function updateLocationPreview(lat, lng, formattedAddress) {
        latitudePreviewEl.textContent = lat ? Number(lat).toFixed(6) : '—';
        longitudePreviewEl.textContent = lng ? Number(lng).toFixed(6) : '—';
        previewEl.textContent = formattedAddress || 'Your pinned place will appear here.';
    }

    function updateLocationFields(lat, lng, formattedAddress = '') {
        latitudeEl.value = lat ? Number(lat).toFixed(7) : '';
        longitudeEl.value = lng ? Number(lng).toFixed(7) : '';
        formattedAddressEl.value = formattedAddress || '';
        updateLocationPreview(lat, lng, formattedAddress);
    }

    function updateBarangayDisplay(name) {
        if (!barangayDisplayEl) {
            return;
        }

        const value = String(name || '').trim();
        barangayDisplayEl.textContent = value || 'Barangay will fill in automatically from the pinned location.';
        barangayDisplayEl.classList.toggle('is-empty', value === '');
    }

    function clearResults() {
        resultsEl.innerHTML = '';
        resultsEl.classList.add('hidden');
    }

    function syncBarangaySelection(candidate) {
        const rawCandidate = String(candidate || '').trim();
        const normalizedCandidate = rawCandidate.toLowerCase();

        if (!normalizedCandidate || !barangayTextEl) {
            return;
        }

        if (!barangayCatalog.length) {
            barangayTextEl.value = rawCandidate;
            updateBarangayDisplay(rawCandidate);
            return;
        }

        const matchedOption = barangayCatalog.find((name) => {
            return String(name).trim().toLowerCase() === normalizedCandidate;
        });

        if (!matchedOption) {
            barangayTextEl.value = rawCandidate;
            updateBarangayDisplay(rawCandidate);
            return;
        }

        barangayTextEl.value = matchedOption;
        updateBarangayDisplay(matchedOption);
    }

    function syncBarangayFromFormattedAddress(formattedAddress) {
        const normalizedAddress = String(formattedAddress || '').trim().toLowerCase();

        if (!normalizedAddress || !barangayTextEl || !barangayCatalog.length) {
            return;
        }

        const matchedOption = barangayCatalog.find((name) => {
            const optionText = String(name).trim().toLowerCase();

            if (!optionText) {
                return false;
            }

            return normalizedAddress.includes(optionText);
        });

        if (!matchedOption) {
            return;
        }

        barangayTextEl.value = matchedOption;
        updateBarangayDisplay(matchedOption);
    }

    function syncAdministrativeFields(result) {
        if (cityInputEl && result.city) {
            cityInputEl.value = result.city;
        }

        if (provinceInputEl && result.state) {
            provinceInputEl.value = result.state;
        }

        syncBarangaySelection(
            result.suburb
            || result.district
            || result.neighbourhood
            || result.quarter
            || result.hamlet
            || result.village
            || result.county
            || ''
        );

        syncBarangayFromFormattedAddress(result.formatted || '');
    }

    function ensureMarker(lat, lng) {
        if (!marker) {
            marker = L.marker([lat, lng], {
                draggable: true,
            }).addTo(map);

            marker.on('dragend', () => {
                const next = marker.getLatLng();
                applyPinnedLocation({
                    latitude: next.lat,
                    longitude: next.lng,
                    center: false,
                });
            });

            return;
        }

        marker.setLatLng([lat, lng]);
    }

    // Keep the readable address in sync with the pinned map coordinates.
    async function reverseGeocode(lat, lng) {
        const requestId = ++activeReverseRequest;
        setStatus('Checking pinned place...', false);

        try {
            const response = await fetch(`${reverseUrl}?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();

            if (requestId !== activeReverseRequest) {
                return;
            }

            if (!response.ok || !payload.result) {
                setStatus(payload.message || 'Pin saved, but the place name is not ready yet.', true);
                return;
            }

            const result = payload.result;
            const formatted = result.formatted || formattedAddressEl.value;

            updateLocationFields(lat, lng, formatted);
            searchEl.value = formatted || searchEl.value;
            syncAdministrativeFields(result);
            setStatus('Location saved.', false);
        } catch (error) {
            setStatus('Pin saved, but the place name could not be updated.', true);
        }
    }

    function applyPinnedLocation({
        latitude,
        longitude,
        formattedAddress = '',
        center = true,
        skipReverse = false,
        adminResult = null,
    }) {
        updateLocationFields(latitude, longitude, formattedAddress);
        ensureMarker(latitude, longitude);

        if (center) {
            map.setView([latitude, longitude], Math.max(map.getZoom(), 16));
        }

        if (adminResult) {
            syncAdministrativeFields(adminResult);
        }

        if (!skipReverse) {
            reverseGeocode(latitude, longitude);
            return;
        }

        setStatus('Location saved.', false);
    }

    function renderResults(results) {
        if (!results.length) {
            clearResults();
            setStatus('No match found. Try another search or tap the map.', true);
            return;
        }

        resultsEl.innerHTML = '';

        results.forEach((result, index) => {
            const formatted = result.formatted || 'Selected result';
            const sub = [result.city, result.state, result.country].filter(Boolean).join(', ');

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'location-result-btn';
            button.dataset.index = String(index);

            const main = document.createElement('span');
            main.className = 'location-result-main';
            main.textContent = formatted;

            const subText = document.createElement('span');
            subText.className = 'location-result-sub';
            subText.textContent = sub || 'Tap to use this result';

            button.appendChild(main);
            button.appendChild(subText);
            resultsEl.appendChild(button);
        });

        resultsEl.classList.remove('hidden');

        resultsEl.querySelectorAll('.location-result-btn').forEach((button, index) => {
            button.addEventListener('click', () => {
                const result = results[index];

                clearResults();
                searchEl.value = result.formatted || '';
                applyPinnedLocation({
                    latitude: Number(result.latitude),
                    longitude: Number(result.longitude),
                    formattedAddress: result.formatted || '',
                    center: true,
                    skipReverse: false,
                    adminResult: result,
                });
            });
        });
    }

    async function searchLocation(query) {
        const requestId = ++activeSearchRequest;
        setStatus('Searching...', false);

        try {
            const response = await fetch(`${autocompleteUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();

            if (requestId !== activeSearchRequest) {
                return;
            }

            if (!response.ok) {
                clearResults();
                setStatus(payload.message || 'Search is not available right now.', true);
                return;
            }

            renderResults(payload.results || []);
        } catch (error) {
            clearResults();
            setStatus('Search is not available right now.', true);
        }
    }

    map = L.map(mapEl, {
        zoomControl: true,
    }).setView(defaultCenter, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    map.on('click', (event) => {
        clearResults();
        applyPinnedLocation({
            latitude: event.latlng.lat,
            longitude: event.latlng.lng,
            center: false,
        });
    });

    searchEl.addEventListener('input', () => {
        const query = searchEl.value.trim();

        clearTimeout(searchTimer);

        if (query.length < 3) {
            clearResults();
            setStatus('Type 3 letters or tap the map.', false);
            return;
        }

        searchTimer = setTimeout(() => {
            searchLocation(query);
        }, 320);
    });

    document.addEventListener('click', (event) => {
        if (!resultsEl.contains(event.target) && event.target !== searchEl) {
            clearResults();
        }
    });

    fetch('https://psgc.gitlab.io/api/cities-municipalities/160202000/barangays/')
        .then((response) => response.json())
        .then((rows) => {
            barangayCatalog = Array.isArray(rows)
                ? rows.map((row) => String(row.name || '').trim()).filter(Boolean)
                : [];

            if (barangayTextEl?.value) {
                updateBarangayDisplay(barangayTextEl.value);
                syncBarangaySelection(barangayTextEl.value);
            }
        })
        .catch(() => {
            barangayCatalog = [];

            if (barangayTextEl?.value) {
                updateBarangayDisplay(barangayTextEl.value);
            }
        });

    const oldLatitude = Number(OLD_CUSTOMER_LATITUDE || 0);
    const oldLongitude = Number(OLD_CUSTOMER_LONGITUDE || 0);
    const oldFormattedAddress = String(OLD_FORMATTED_ADDRESS || '').trim();

    updateBarangayDisplay(barangayTextEl?.value || '');

    if (oldLatitude && oldLongitude) {
        searchEl.value = oldFormattedAddress;
        applyPinnedLocation({
            latitude: oldLatitude,
            longitude: oldLongitude,
            formattedAddress: oldFormattedAddress,
            center: true,
            skipReverse: oldFormattedAddress !== '',
        });
    } else {
        updateLocationPreview('', '', '');
    }
})();
</script>

@endsection
