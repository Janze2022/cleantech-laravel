@extends('layouts.app')

@section('title', 'CleanTech - Provider Pre-Registration Terms')

@section('content')
<style>
    .ct-page{
    padding-top: calc(var(--nav-h, 72px) + 16px) !important;
}
    :root{
        --ct-bg:#0b1220;
        --ct-surface:#0f172a;
        --ct-border:#1f2a44;
        --ct-text:#e5e7eb;
        --ct-muted:#94a3b8;
        --ct-primary:#3b82f6;
        --ct-primary2:#2563eb;
        --ct-danger:#ef4444;
        --ct-radius:14px;
        --ct-shadow:0 12px 30px rgba(0,0,0,.35);
        --ct-focus:rgba(59,130,246,.22);
    }

    /* PAGE */
    .ct-page{
        min-height:100vh;
        padding: clamp(14px, 3.5vw, 22px) clamp(12px, 3.5vw, 18px) 28px;
        background:
            radial-gradient(1000px 500px at 15% 0%, rgba(59,130,246,.18), transparent 60%),
            radial-gradient(900px 450px at 85% 10%, rgba(34,197,94,.10), transparent 60%),
            var(--ct-bg);
    }
    .ct-wrap{ max-width:1100px; margin:0 auto; }

    /* TOP BAR (mobile-first) */
    .ct-top{
        display:flex;
        flex-direction: column;
        gap:10px;
        margin-bottom:14px;
        color:var(--ct-text);
    }
    .ct-top-left{
        font-weight:900;
        letter-spacing:.2px;
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        gap:10px;
        line-height:1.2;
    }
    .ct-chip,
    .ct-breadcrumb{
        font-size:12px;
        color:var(--ct-muted);
        border:1px solid var(--ct-border);
        background:rgba(255,255,255,.03);
        padding:6px 10px;
        border-radius:999px;
        width: fit-content;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* CARD */
    .ct-box{
        border:1px solid var(--ct-border);
        border-radius:var(--ct-radius);
        background:linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01));
        box-shadow:var(--ct-shadow);
        overflow:hidden;
    }

    .ct-bar{
        padding:12px 14px;
        display:flex;
        flex-direction: column;
        gap:10px;
        align-items:flex-start;
        justify-content:space-between;
        background:rgba(255,255,255,.03);
        border-bottom:1px solid var(--ct-border);
        color:var(--ct-text);
        font-weight:900;
        letter-spacing:.3px;
    }
    .ct-step{
        font-weight:800;
        font-size:12px;
        color:var(--ct-muted);
        border:1px solid var(--ct-border);
        background:rgba(0,0,0,.15);
        padding:6px 10px;
        border-radius:999px;
    }

    .ct-body{ padding: clamp(12px, 3vw, 16px); color:var(--ct-text); }

    /* TERMS SCROLLER (mobile-friendly height) */
    .ct-scroll{
        height: min(62vh, 520px);
        overflow:auto;
        -webkit-overflow-scrolling: touch;
        border:1px solid var(--ct-border);
        border-radius:12px;
        padding: 14px 14px;
        background:var(--ct-surface);
        scroll-behavior: smooth;
    }
    .ct-scroll:focus-within{
        box-shadow: 0 0 0 3px var(--ct-focus);
        border-color: rgba(59,130,246,.35);
    }

    /* “Legit terms” typography */
    .ct-terms{ font-size:12.8px; line-height:1.8; color:var(--ct-muted); }
    .ct-terms h2{
        font-size:13px;
        color:var(--ct-text);
        margin:14px 0 6px;
        font-weight:900;
        letter-spacing:.2px;
    }
    .ct-terms h2:first-child{ margin-top:0; }
    .ct-terms p{ margin:0 0 10px; }
    .ct-terms ul, .ct-terms ol{ margin:6px 0 12px; padding-left:18px; }
    .ct-terms li{ margin:4px 0; }

    .ct-terms .ct-note{
        border:1px solid var(--ct-border);
        background:rgba(255,255,255,.03);
        border-radius:12px;
        padding:12px 12px;
        color:var(--ct-muted);
        margin-bottom:12px;
    }
    .ct-terms .ct-note strong{ color:var(--ct-text); }

    .ct-pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:4px 10px;
        border-radius:999px;
        border:1px solid var(--ct-border);
        background:rgba(255,255,255,.03);
        color:var(--ct-muted);
        font-size:11px;
        vertical-align: middle;
    }

    /* AGREEMENT ROW (mobile-first stack) */
    .ct-formrow{
        display:flex;
        flex-direction: column;
        gap:12px;
        margin-top:12px;
        padding:12px;
        border:1px solid var(--ct-border);
        border-radius:12px;
        background:rgba(255,255,255,.02);
    }

    .ct-check{
        display:flex;
        align-items:flex-start;
        gap:10px;
        margin:0;
    }

    .ct-check .form-check-input{
        margin-top: 3px;
        width: 18px;
        height: 18px;
        background-color: rgba(255,255,255,.04);
        border: 1px solid var(--ct-border);
        box-shadow: none !important;
    }
    .ct-check .form-check-input:checked{
        background-color: rgba(59,130,246,.35);
        border-color: rgba(59,130,246,.6);
    }
    .ct-check .form-check-input:focus{
        border-color: rgba(59,130,246,.7);
        box-shadow: 0 0 0 3px var(--ct-focus) !important;
    }
    .ct-check label{
        font-size:12px;
        color:var(--ct-muted);
        user-select:none;
        line-height:1.4;
    }

    .ct-actions{
        display:grid;
        grid-template-columns: 1fr;
        gap:10px;
        width:100%;
    }

    /* Buttons (bigger tap targets) */
    .btn{
        border-radius:12px !important;
        font-weight:900 !important;
        letter-spacing:.2px;
        padding:12px 14px !important;
        border:1px solid transparent !important;
        width:100%;
        min-height: 44px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
    }
    .btn-danger{
        background:rgba(239,68,68,.12) !important;
        border-color:rgba(239,68,68,.35) !important;
        color:#fecaca !important;
    }
    .btn-danger:hover{ background:rgba(239,68,68,.18) !important; }

    .btn-primary{
        background:linear-gradient(180deg, var(--ct-primary), var(--ct-primary2)) !important;
        border-color:rgba(59,130,246,.35) !important;
        color:#fff !important;
    }
    .btn-primary:hover{ filter:brightness(1.05); }

    .alert{ border-radius:12px !important; }

    /* Desktop refinements */
    @media (min-width: 768px){
        .ct-top{
            flex-direction: row;
            align-items:center;
            justify-content:space-between;
            gap:14px;
        }
        .ct-bar{
            flex-direction: row;
            align-items:center;
            justify-content:space-between;
            padding:14px 16px;
        }
        .ct-formrow{
            flex-direction: row;
            align-items:center;
            justify-content:space-between;
            gap:12px;
        }
        .ct-actions{
            grid-template-columns: auto auto;
            width:auto;
        }
        .btn{
            width:auto;
            padding:10px 14px !important;
        }
        .ct-scroll{
            height:520px;
            padding:16px 16px;
        }
    }
</style>

<div class="ct-page">
    <div class="ct-wrap">
        <div class="ct-top">
            <div class="ct-top-left">
                <span>⚙️ Provider Onboarding</span>
                <span class="ct-chip">Terms Acceptance</span>
            </div>
            <div class="ct-breadcrumb">Home / Provider / Pre-Register / Terms</div>
        </div>

        <div class="ct-box">
            <div class="ct-bar">
                <div>☰ TERMS AND CONDITIONS</div>
                <div class="ct-step">Step 0 of 2</div>
            </div>

            <div class="ct-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
                @endif

                <div class="ct-scroll">
                    <div class="ct-terms">
                        <div class="ct-note">
                            <strong>Important Notice.</strong>
                            These Terms and Conditions (“Terms”) govern your use of the CleanTech provider portal and your participation as a service provider on the CleanTech platform.
                            By clicking <span class="ct-pill">I Agree</span>, you confirm that you have read, understood, and agree to be bound by these Terms.
                            If you do not agree, do not proceed with registration.
                        </div>

                        <h2>1. Definitions</h2>
                        <p>
                            “CleanTech”, “we”, “us”, and “our” refer to the CleanTech platform and its administrators.
                            “Provider”, “you”, and “your” refer to any individual or entity applying to offer professional cleaning services through CleanTech.
                            “Client” refers to customers who book services through the platform.
                            “Services” refer to professional cleaning and related eco-friendly services made available through CleanTech.
                        </p>

                        <h2>2. Eligibility and Provider Account</h2>
                        <p>
                            You may register as a Provider only if you are legally able to enter into binding agreements and you can perform Services in your service area.
                            You agree to provide accurate, complete, and up-to-date information during onboarding, including identity and contact details.
                            Your account may remain <b>Pending</b> until reviewed and approved by CleanTech. We may accept or reject applications at our discretion to protect service quality and safety.
                        </p>
                        <ul>
                            <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                            <li>You must immediately notify CleanTech if you suspect unauthorized access to your account.</li>
                            <li>You may not transfer, sell, or share your Provider account with another person.</li>
                        </ul>

                        <h2>3. Verification and Compliance</h2>
                        <p>
                            To support trust and safety, CleanTech may request verification documents such as a valid government ID, proof of address, business permits, or other documents reasonably required for onboarding.
                            You authorize CleanTech to review submitted documents for verification purposes.
                            If you provide false, misleading, or incomplete information, CleanTech may deny approval, suspend access, or deactivate your Provider account.
                        </p>

                        <h2>4. Provider Responsibilities and Service Standards</h2>
                        <p>
                            Providers are expected to deliver professional, respectful, and safe Services consistent with industry-standard practices and CleanTech quality guidelines.
                            You agree to conduct yourself professionally with Clients, respect their property, and follow booking instructions and reasonable site rules.
                        </p>
                        <ul>
                            <li><b>Safety:</b> Use cleaning methods and handling practices that prioritize safety for people, pets, and property.</li>
                            <li><b>Eco-Friendly Practices:</b> Where applicable, use environmentally responsible products and methods aligned with the service listing.</li>
                            <li><b>Equipment & Supplies:</b> Provide the tools, supplies, and protective equipment necessary to perform Services unless a listing states otherwise.</li>
                            <li><b>Accuracy:</b> Provide truthful descriptions of your skills, service scope, and availability.</li>
                            <li><b>Communication:</b> Respond to bookings and client messages within a reasonable time.</li>
                        </ul>

                        <h2>5. Bookings, Scheduling, and Cancellations</h2>
                        <p>
                            Bookings are arranged through the CleanTech platform. You agree to honor confirmed bookings and arrive on time.
                            If an emergency prevents you from attending, you must notify the Client (and/or via the platform) as soon as possible.
                            CleanTech may apply platform rules for repeated late arrivals, cancellations, or no-shows to protect Clients and service reliability.
                        </p>
                        <p>
                            CleanTech may update booking procedures, service categories, or scheduling policies from time to time, and you agree to comply with those updates when posted.
                        </p>

                        <h2>6. Prohibited Conduct</h2>
                        <p>You agree not to misuse the platform. Prohibited conduct includes, but is not limited to:</p>
                        <ul>
                            <li>Illegal activity, harassment, threats, discrimination, or harmful behavior.</li>
                            <li>Misrepresentation of identity, credentials, pricing, or service coverage.</li>
                            <li>Attempting to bypass platform processes in ways that undermine safety, transparency, or compliance.</li>
                            <li>Uploading malware, attempting unauthorized access, or interfering with platform operation.</li>
                        </ul>

                        <h2>7. Fees, Payouts, and Taxes</h2>
                        <p>
                            If the platform supports payouts or fees, you agree that CleanTech may apply service fees, processing fees, or platform charges as disclosed in the Provider portal or applicable policies.
                            You are responsible for determining and paying any taxes, permits, and statutory contributions related to your Services, unless local law requires otherwise.
                        </p>

                        <h2>8. Privacy, Data, and Uploaded Documents</h2>
                        <p>
                            CleanTech collects and processes personal data for onboarding, verification, service operations, dispute handling, fraud prevention, and platform improvement.
                            Uploaded verification documents (e.g., ID images) are stored securely and accessed only by authorized personnel for legitimate operational needs.
                            CleanTech does not publicly display your ID images.
                        </p>
                        <ul>
                            <li>You must not collect or store Client personal data beyond what is necessary to complete a booking.</li>
                            <li>You must keep Client information confidential and use it only for providing the booked Service.</li>
                            <li>If you experience a data incident (e.g., lost device or unauthorized disclosure), you must inform CleanTech promptly.</li>
                        </ul>

                        <h2>9. Reviews, Quality Monitoring, and Disputes</h2>
                        <p>
                            Clients may leave reviews based on their experience. CleanTech may monitor service quality and investigate complaints to maintain platform standards.
                            You agree to cooperate in good faith with dispute resolution processes, including providing reasonable documentation such as photos or service notes when requested.
                        </p>

                        <h2>10. Intellectual Property</h2>
                        <p>
                            The CleanTech name, logo, and portal content are owned by CleanTech or its licensors and may not be copied, modified, or used without permission.
                            You may reference CleanTech only as necessary to participate on the platform and not in a way that implies endorsement beyond platform participation.
                        </p>

                        <h2>11. Suspension and Termination</h2>
                        <p>
                            CleanTech may suspend or terminate your Provider account if we believe you violated these Terms, harmed Clients, compromised safety, or created significant platform risk.
                            We may also suspend access during investigations.
                            If your account is terminated, you must stop using the provider portal and may not attempt to re-register without permission.
                        </p>

                        <h2>12. Disclaimers and Limitation of Liability</h2>
                        <p>
                            The platform is provided on an “as is” and “as available” basis.
                            CleanTech does not guarantee uninterrupted access to the portal, nor does it guarantee the volume of bookings.
                            To the maximum extent permitted by law, CleanTech will not be liable for indirect, incidental, special, or consequential damages arising from your use of the platform.
                        </p>

                        <h2>13. Changes to These Terms</h2>
                        <p>
                            CleanTech may update these Terms to reflect operational, legal, or safety requirements.
                            Updated Terms will be posted in the portal, and continued use of the platform after updates constitutes acceptance of the revised Terms.
                        </p>

                        <h2>14. Contact</h2>
                        <p style="margin-bottom:0;">
                            For questions about these Terms or your application, contact CleanTech support through the portal’s help channel or the official contact information provided in your Provider dashboard.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('provider.pre_register.terms.submit') }}" class="mt-3">
                    @csrf

                    <div class="ct-formrow">
                        <div class="form-check ct-check m-0">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
                            <label class="form-check-label" for="agree">
                                I have read and agree to the CleanTech Provider Terms & Conditions.
                            </label>
                        </div>

                        <div class="ct-actions">
                            <a class="btn btn-danger" href="{{ route('home') }}">Decline ✖</a>
                            <button class="btn btn-primary" type="submit">I Agree ✔</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
