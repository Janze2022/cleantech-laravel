<style>
.site-footer{
    position:relative;
    margin-top:10px;
    padding:0 0 28px;
}

.site-footer-shell{
    background:linear-gradient(180deg, rgba(9,18,36,.96), rgba(4,11,24,.98));
    border:1px solid rgba(255,255,255,.08);
    border-radius:26px;
    box-shadow:0 24px 60px rgba(0,0,0,.26);
    padding:1.2rem 1.2rem 1rem;
}

.site-footer-grid{
    display:grid;
    grid-template-columns:minmax(0, 1.2fr) repeat(2, minmax(180px, .8fr));
    gap:1rem;
    align-items:start;
}

.site-footer-brand{
    display:flex;
    align-items:flex-start;
    gap:.8rem;
}

.site-footer-mark{
    width:42px;
    height:42px;
    border-radius:14px;
    display:grid;
    place-items:center;
    background:linear-gradient(135deg, rgba(56,189,248,.18), rgba(37,99,235,.18));
    border:1px solid rgba(56,189,248,.22);
    color:#dff7ff;
    flex:0 0 auto;
}

.site-footer-mark svg{
    width:18px;
    height:18px;
    stroke:currentColor;
}

.site-footer-brand strong{
    display:block;
    color:#fff;
    font-size:1rem;
    font-weight:900;
}

.site-footer-brand p{
    margin:.3rem 0 0;
    color:#9eb0ca;
    font-size:.9rem;
    line-height:1.55;
}

.site-footer-title{
    display:block;
    margin-bottom:.55rem;
    color:#fff;
    font-size:.78rem;
    font-weight:900;
    letter-spacing:.1em;
    text-transform:uppercase;
}

.site-footer-links{
    display:grid;
    gap:.45rem;
}

.site-footer-links a{
    color:#cfe0f8;
    text-decoration:none;
    font-weight:700;
    font-size:.9rem;
}

.site-footer-links a:hover{
    color:#8fd8ff;
}

.site-footer-bottom{
    margin-top:1rem;
    padding-top:.85rem;
    border-top:1px solid rgba(255,255,255,.07);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap;
    color:#8ea2c5;
    font-size:.84rem;
    font-weight:700;
}

@media (max-width: 991px){
    .site-footer-grid{
        grid-template-columns:1fr 1fr;
    }
}

@media (max-width: 767.98px){
    .site-footer{
        padding:0 0 18px;
    }

    .site-footer-shell{
        border-radius:22px;
        padding:1rem;
    }

    .site-footer-grid{
        grid-template-columns:1fr;
    }
}
</style>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer-shell">
            <div class="site-footer-grid">
                <div class="site-footer-brand">
                    <div class="site-footer-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2.75C12 2.75 6 9.28 6 13.25A6 6 0 0 0 18 13.25C18 9.28 12 2.75 12 2.75Z"></path>
                            <path d="M9.5 14.25A2.5 2.5 0 0 0 12 16.75"></path>
                        </svg>
                    </div>
                    <div>
                        <strong>CleanTech</strong>
                        <p>Easy booking for home services, with a cleaner, more guided experience for customers and providers.</p>
                    </div>
                </div>

                <div>
                    <span class="site-footer-title">Explore</span>
                    <div class="site-footer-links">
                        <a href="{{ route('services') }}">Services</a>
                        <a href="{{ route('how.it.works') }}">How It Works</a>
                        <a href="{{ route('pricing') }}">Pricing</a>
                        <a href="{{ route('about') }}">About</a>
                    </div>
                </div>

                <div>
                    <span class="site-footer-title">Support</span>
                    <div class="site-footer-links">
                        <a href="{{ route('faq') }}">FAQ</a>
                        <a href="{{ route('contact') }}">Contact</a>
                        <a href="mailto:janzedoysabas@gmail.com">janzedoysabas@gmail.com</a>
                    </div>
                </div>
            </div>

            <div class="site-footer-bottom">
                <span>&copy; {{ now()->year }} CleanTech. All rights reserved.</span>
                <span>Made for simpler service booking.</span>
            </div>
        </div>
    </div>
</footer>
