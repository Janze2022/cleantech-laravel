@extends('admin.layouts.app')

@section('title', 'User Accounts')

@section('content')

<style>
:root{
    --bg:#0b0f19;
    --card:#0f172a;
    --card2:#0b1220;
    --border:rgba(255,255,255,.08);
    --muted:#94a3b8;
    --text:#e5e7eb;

    --accent:#38bdf8;
    --success:#22c55e;
    --danger:#ef4444;
    --violet:#c084fc;

    --r:16px;
}

.wrap{ padding: 14px 12px 22px; }

.head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom: 12px;
}
.head h4{ margin:0; color:var(--text); font-weight:950; }
.search{
    background: rgba(2,6,23,.8);
    border: 1px solid rgba(255,255,255,.10);
    color: var(--text);
    border-radius: 12px;
    padding: .6rem .9rem;
    min-width: 260px;
    outline:none;
}
.search:focus{
    border-color: rgba(56,189,248,.55);
    box-shadow: 0 0 0 3px rgba(56,189,248,.12);
}
.search::placeholder{ color: rgba(203,213,245,.45); }

.panel{
    background: linear-gradient(180deg, rgba(15,23,42,.92), rgba(11,15,25,.96));
    border: 1px solid rgba(255,255,255,.06);
    border-radius: var(--r);
    overflow:hidden;
}

/* Desktop table */
.table, .table > :not(caption) > * > *{ background: transparent !important; }
.table thead th{
    background: rgba(2,6,23,.88) !important;
    color: rgba(203,213,245,.55);
    border-bottom: 1px solid rgba(255,255,255,.06);
    font-size: .72rem;
    letter-spacing:.10em;
    text-transform: uppercase;
    padding: .95rem 1rem;
}
.table td{
    color: rgba(229,231,235,.92);
    border-bottom: 1px solid rgba(255,255,255,.06);
    padding: .95rem 1rem;
    vertical-align: middle;
}
.table tbody tr:hover td{ background: rgba(56,189,248,.06); }

.role{
    display:inline-flex;
    align-items:center;
    padding:.33rem .7rem;
    border-radius:999px;
    font-size:.72rem;
    font-weight:950;
    letter-spacing:.06em;
    text-transform: uppercase;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(2,6,23,.45);
}
.role.customer{ color: var(--accent); border-color: rgba(56,189,248,.25); }
.role.provider{ color: var(--success); border-color: rgba(34,197,94,.25); }
.role.admin{ color: var(--violet); border-color: rgba(192,132,252,.25); }

.btnx{
    border-radius: 12px !important;
    font-weight: 850 !important;
    padding: .45rem .75rem !important;
    min-height: 38px;
}
.btn-outline-info.btnx{
    border-color: rgba(56,189,248,.55) !important;
    color: #7dd3fc !important;
}
.btn-outline-info.btnx:hover{ background: rgba(56,189,248,.12) !important; }
.btn-outline-danger.btnx{
    border-color: rgba(239,68,68,.55) !important;
    color: #fecaca !important;
}
.btn-outline-danger.btnx:hover{ background: rgba(239,68,68,.12) !important; }

/* Mobile simple list */
.mobile-list{ display:none; }
.user-item{
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.user-top{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap: 10px;
}
.user-name{
    color: var(--text);
    font-weight: 900;
    line-height:1.2;
}
.user-email{
    color: rgba(255,255,255,.65);
    font-size: .9rem;
    margin-top: 2px;
    word-break: break-word;
}
.user-actions{
    display:flex;
    gap: 8px;
    flex-wrap:wrap;
    margin-top: 10px;
}

@media (max-width: 768px){
    .search{ width:100%; min-width: unset; }
    .table-responsive{ display:none; }
    .mobile-list{ display:block; }
}

/* Modal styling (keep yours, simplified) */
#editUserModal .modal-content{
    background: radial-gradient(120% 120% at top left, #0f172a, #020617);
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 18px;
}
#editUserModal label{
    color: var(--muted);
    font-size: .78rem;
    letter-spacing:.08em;
    text-transform: uppercase;
    font-weight: 900;
}
#editUserModal input.form-control{
    background: rgba(2,6,23,.78);
    color: var(--text);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    padding: .62rem .8rem;
}
#editUserModal input.form-control:focus{
    border-color: rgba(56,189,248,.55);
    box-shadow: 0 0 0 3px rgba(56,189,248,.12);
}
#editUserModal .btn-close{ filter: invert(1); opacity:.75; }
#editUserModal .btn-close:hover{ opacity:1; }
</style>

<div class="wrap container-fluid">

    <div class="head">
        <div>
            <h4>User Accounts</h4>
        </div>
        <input type="text" id="userSearch" class="search" placeholder="Search user...">
    </div>

    <div class="panel">

        {{-- DESKTOP TABLE --}}
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th style="width:140px;">Role</th>
                    <th class="text-end" style="width:220px;">Actions</th>
                </tr>
                </thead>

                <tbody id="userTableDesktop">
                @forelse($users as $u)
                    @php $role = strtolower((string)($u->role ?? 'customer')); @endphp
                    <tr class="user-row">
                        <td class="text-white fw-semibold">{{ $u->name }}</td>
                        <td class="text-white">{{ $u->email }}</td>
                        <td>
                            <span class="role {{ $role }}">{{ ucfirst($role) }}</span>
                        </td>
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-outline-info btn-sm btnx btn-edit"
                                    data-id="{{ $u->id }}"
                                    data-name="{{ $u->name }}"
                                    data-email="{{ $u->email }}"
                                    data-role="{{ $role }}">
                                Edit
                            </button>

                            @if($role !== 'admin')
                                <form method="POST"
                                      action="{{ route('admin.customers.delete', $u->id) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this account?')">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ $role }}">
                                    <button type="submit" class="btn btn-outline-danger btn-sm btnx">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-5 text-white-50">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE LIST --}}
        <div class="mobile-list" id="userListMobile">
            @forelse($users as $u)
                @php $role = strtolower((string)($u->role ?? 'customer')); @endphp
                <div class="user-item user-row">
                    <div class="user-top">
                        <div style="min-width:0;">
                            <div class="user-name">{{ $u->name }}</div>
                            <div class="user-email">{{ $u->email }}</div>
                        </div>
                        <div>
                            <span class="role {{ $role }}">{{ ucfirst($role) }}</span>
                        </div>
                    </div>

                    <div class="user-actions">
                        <button type="button"
                                class="btn btn-outline-info btn-sm btnx btn-edit"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->name }}"
                                data-email="{{ $u->email }}"
                                data-role="{{ $role }}">
                            Edit
                        </button>

                        @if($role !== 'admin')
                            <form method="POST"
                                  action="{{ route('admin.customers.delete', $u->id) }}"
                                  onsubmit="return confirm('Delete this account?')">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm btnx">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="user-item text-white-50 text-center py-4">No users found.</div>
            @endforelse
        </div>

    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-white mb-0" style="font-weight:950;">Edit User</h5>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editUserForm">
                @csrf
                <input type="hidden" name="role" id="edit_role">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Name</label>
                        <input class="form-control" name="name" id="edit_name" required>
                    </div>

                    <div class="col-md-6">
                        <label>Email</label>
                        <input class="form-control" name="email" id="edit_email" required>
                    </div>

                    <div class="col-md-6">
                        <label>New Password</label>
                        <input type="password" class="form-control" name="password"
                               placeholder="Leave blank to keep current">
                    </div>

                    <div class="col-md-6">
                        <label>Role</label>
                        <input class="form-control" id="edit_role_label" disabled>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-outline-info btnx">
                        Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
/* Ensure bootstrap bundle exists (modal needs it) */
(function(){
    if (window.bootstrap && window.bootstrap.Modal) return;
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
    document.body.appendChild(s);
})();

/* ✅ Modal open (no broken quotes, uses data-* attributes) */
document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-edit');
    if(!btn) return;

    const id = btn.dataset.id;
    const name = btn.dataset.name || '';
    const email = btn.dataset.email || '';
    const role = btn.dataset.role || '';

    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_role_label').value =
        role.charAt(0).toUpperCase() + role.slice(1);

    document.getElementById('editUserForm').action = `/admin/customers/${id}/update`;

    const modalEl = document.getElementById('editUserModal');
    const show = () => {
        if(window.bootstrap && window.bootstrap.Modal){
            new bootstrap.Modal(modalEl).show();
        } else {
            setTimeout(show, 50);
        }
    };
    show();
});

/* Search filter (works for both desktop + mobile) */
document.getElementById('userSearch')?.addEventListener('keyup', function(){
    const v = (this.value || '').toLowerCase();
    document.querySelectorAll('.user-row').forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none';
    });
});
</script>

@endsection
